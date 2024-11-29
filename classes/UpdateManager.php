<?php

namespace System\Classes;

use Carbon\Carbon;
use Cms\Classes\ThemeManager;
use Exception;
use Illuminate\Console\OutputStyle;
use Illuminate\Console\View\Components\Error;
use Illuminate\Console\View\Components\Info;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use System\Classes\Core\MarketPlaceApi;
use System\Classes\Core\UpdateManagerHelperTrait;
use System\Helpers\Cache as CacheHelper;
use System\Models\Parameter;
use System\Models\PluginVersion;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Filesystem\Zip;
use Winter\Storm\Support\Facades\Config;
use Winter\Storm\Support\Facades\File;
use Winter\Storm\Support\Facades\Http;
use Winter\Storm\Support\Facades\Schema;

/**
 * Update manager
 *
 * Handles the CMS install and update process.
 *
 * @package winter\wn-system-module
 * @author Alexey Bobkov, Samuel Georges
 */
class UpdateManager
{
    use \Winter\Storm\Support\Traits\Singleton;
    use UpdateManagerHelperTrait;

    protected ?OutputStyle $notesOutput = null;

    protected string $baseDirectory;

    protected PluginManager $pluginManager;

    protected ThemeManager $themeManager;

    protected VersionManager $versionManager;

    protected MarketPlaceApi $api;

    /**
     * If set to true, core updates will not be downloaded or extracted.
     */
    protected bool $disableCoreUpdates = false;

    protected Migrator $migrator;

    protected DatabaseMigrationRepository $repository;

    /**
     * Array of messages returned by migrations / seeders. Returned at the end of the update process.
     */
    protected array $messages = [];

    /**
     * Initialize this singleton.
     */
    protected function init()
    {
        $this->api = MarketPlaceApi::instance();
        $this->pluginManager = PluginManager::instance();
        $this->themeManager = class_exists(ThemeManager::class) ? ThemeManager::instance() : null;
        $this->versionManager = VersionManager::instance();
        $this->tempDirectory = temp_path();
        $this->baseDirectory = base_path();
        $this->disableCoreUpdates = Config::get('cms.disableCoreUpdates', false);
        $this->bindContainerObjects();

        /*
         * Ensure temp directory exists
         */
        if (!File::isDirectory($this->tempDirectory) && File::isWritable($this->tempDirectory)) {
            File::makeDirectory($this->tempDirectory, 0777, true);
        }
    }

    /**
     * These objects are "soft singletons" and may be lost when
     * the IoC container reboots. This provides a way to rebuild
     * for the purposes of unit testing.
     */
    public function bindContainerObjects(): void
    {
        $this->migrator = App::make('migrator');
        $this->repository = App::make('migration.repository');
    }

    /**
     * Creates the migration table and updates
     */
    public function update(): static
    {
        try {
            $firstUp = !Schema::hasTable($this->getMigrationTableName());
            if ($firstUp) {
                $this->repository->createRepository();
                $this->out('', true);
                $this->write(Info::class, 'Migration table created');
            }

            /*
            * Update modules
            */
            $modules = Config::get('cms.loadModules', []);
            foreach ($modules as $module) {
                $this->migrateModule($module);
            }

            $plugins = $this->pluginManager->getPlugins();

            /*
            * Replace plugins
            */
            foreach ($plugins as $code => $plugin) {
                if (!$replaces = $plugin->getReplaces()) {
                    continue;
                }
                // TODO: add full support for plugins replacing multiple plugins
                if (count($replaces) > 1) {
                    throw new ApplicationException(Lang::get('system::lang.plugins.replace.multi_install_error'));
                }
                foreach ($replaces as $replace) {
                    $this->versionManager->replacePlugin($plugin, $replace);
                }
            }

            /*
            * Seed modules
            */
            if ($firstUp) {
                $modules = Config::get('cms.loadModules', []);
                foreach ($modules as $module) {
                    $this->seedModule($module);
                }
            }

            /*
            * Update plugins
            */
            foreach ($plugins as $code => $plugin) {
                $this->updatePlugin($code);
            }

            Parameter::set('system::update.count', 0);
            CacheHelper::clear();

            // Set replacement warning messages
            foreach ($this->pluginManager->getReplacementMap() as $alias => $plugin) {
                if ($this->pluginManager->getActiveReplacementMap($alias)) {
                    $this->addMessage($plugin, Lang::get('system::lang.updates.update_warnings_plugin_replace_cli', [
                        'alias' => '<info>' . $alias . '</info>'
                    ]));
                }
            }

            $this->out('', true);
            $this->write(Info::class, 'Migration complete.');
        } catch (\Throwable $ex) {
            throw $ex;
        } finally {
            // Print messages returned by migrations / seeders
            $this->printMessages();
        }

        return $this;
    }

    /**
     * Checks for new updates and returns the amount of unapplied updates.
     * Only requests from the server at a set interval (retry timer).
     * @param bool $force Ignore the retry timer.
     */
    public function check(bool $force = false): int
    {
        /*
         * Already know about updates, never retry.
         */
        $oldCount = Parameter::get('system::update.count');
        if ($oldCount > 0) {
            return $oldCount;
        }

        /*
         * Retry period not passed, skipping.
         */
        if (!$force
            && ($retryTimestamp = Parameter::get('system::update.retry'))
            && Carbon::createFromTimeStamp($retryTimestamp)->isFuture()
        ) {
            return $oldCount;
        }

        try {
            $result = $this->requestUpdateList();
            $newCount = array_get($result, 'update', 0);
        } catch (Exception $ex) {
            $newCount = 0;
        }

        /*
         * Remember update count, set retry date
         */
        Parameter::set('system::update.count', $newCount);
        Parameter::set('system::update.retry', Carbon::now()->addHours(24)->timestamp);

        return $newCount;
    }

    /**
     * Requests an update list used for checking for new updates.
     * @param bool $force Request application and plugins hash list regardless of version.
     */
    public function requestUpdateList(bool $force = false): array
    {
        $installed = PluginVersion::all();
        $versions = $installed->lists('version', 'code');
        $names = $installed->lists('name', 'code');
        $icons = $installed->lists('icon', 'code');
        $frozen = $installed->lists('is_frozen', 'code');
        $updatable = $installed->lists('is_updatable', 'code');
        $build = Parameter::get('system::core.build');
        $themes = [];

        if ($this->themeManager) {
            $themes = array_keys($this->themeManager->getInstalled());
        }

        $params = [
            'core'    => $this->getHash(),
            'plugins' => serialize($versions),
            'themes'  => serialize($themes),
            'build'   => $build,
            'force'   => $force
        ];

        $result = $this->api->requestServerData('core/update', $params);
        $updateCount = (int) array_get($result, 'update', 0);

        /*
         * Inject known core build
         */
        if ($core = array_get($result, 'core')) {
            $core['old_build'] = Parameter::get('system::core.build');
            $result['core'] = $core;
        }

        /*
         * Inject the application's known plugin name and version
         */
        $plugins = [];
        foreach (array_get($result, 'plugins', []) as $code => $info) {
            $info['name'] = $names[$code] ?? $code;
            $info['old_version'] = $versions[$code] ?? false;
            $info['icon'] = $icons[$code] ?? false;

            /*
             * If a plugin has updates frozen, or cannot be updated,
             * do not add to the list and discount an update unit.
             */
            if (
                (isset($frozen[$code]) && $frozen[$code]) ||
                (isset($updatable[$code]) && !$updatable[$code])
            ) {
                $updateCount = max(0, --$updateCount);
            } else {
                $plugins[$code] = $info;
            }
        }
        $result['plugins'] = $plugins;

        /*
         * Strip out themes that have been installed before
         */
        if ($this->themeManager) {
            $themes = [];
            foreach (array_get($result, 'themes', []) as $code => $info) {
                if (!$this->themeManager->isInstalled($code)) {
                    $themes[$code] = $info;
                }
            }
            $result['themes'] = $themes;
        }

        /*
         * If there is a core update and core updates are disabled,
         * remove the entry and discount an update unit.
         */
        if (array_get($result, 'core') && $this->disableCoreUpdates) {
            $updateCount = max(0, --$updateCount);
            unset($result['core']);
        }

        /*
         * Recalculate the update counter
         */
        $updateCount += count($themes);
        $result['hasUpdates'] = $updateCount > 0;
        $result['update'] = $updateCount;
        Parameter::set('system::update.count', $updateCount);

        return $result;
    }

    /**
     * Requests details about a project based on its identifier.
     */
    public function requestProjectDetails(string $projectId): array
    {
        return $this->api->requestServerData('project/detail', ['id' => $projectId]);
    }

    /**
     * Roll back all modules and plugins.
     */
    public function uninstall(): static
    {
        /*
         * Rollback plugins
         */
        $plugins = array_reverse($this->pluginManager->getAllPlugins());
        foreach ($plugins as $name => $plugin) {
            $this->rollbackPlugin($name);
        }

        /*
         * Register module migration files
         */
        $paths = [];
        $modules = Config::get('cms.loadModules', []);

        foreach ($modules as $module) {
            $paths[] = $path = base_path() . '/modules/' . strtolower($module) . '/database/migrations';
        }

        /*
         * Rollback modules
         */
        if (isset($this->notesOutput)) {
            $this->migrator->setOutput($this->notesOutput);
        }

        while (true) {
            $rolledBack = $this->migrator->rollback($paths, ['pretend' => false]);

            if (count($rolledBack) == 0) {
                break;
            }
        }

        Schema::dropIfExists($this->getMigrationTableName());

        return $this;
    }

    /**
     * Determines build number from source manifest.
     *
     * This will return an array with the following information:
     *  - `build`: The build number we determined was most likely the build installed.
     *  - `modified`: Whether we detected any modifications between the installed build and the manifest.
     *  - `confident`: Whether we are at least 60% sure that this is the installed build. More modifications to
     *                  to the code = less confidence.
     *  - `changes`: If $detailed is true, this will include the list of files modified, created and deleted.
     *
     * @param bool $detailed If true, the list of files modified, added and deleted will be included in the result.
     */
    public function getBuildNumberManually(bool $detailed = false): array
    {
        $source = new SourceManifest();
        $manifest = new FileManifest(null, null, true);

        // Find build by comparing with source manifest
        return $source->compare($manifest, $detailed);
    }

    /**
     * Sets the build number in the database.
     *
     * @param bool $detailed If true, the list of files modified, added and deleted will be included in the result.
     */
    public function setBuildNumberManually(bool $detailed = false): array
    {
        $build = $this->getBuildNumberManually($detailed);

        if ($build['confident']) {
            $this->setBuild($build['build'], null, $build['modified']);
        }

        return $build;
    }

    //
    // Modules
    //

    /**
     * Returns the currently installed system hash.
     */
    public function getHash(): string
    {
        return Parameter::get('system::core.hash', md5('NULL'));
    }

    /**
     * Run migrations on a single module
     */
    public function migrateModule(string $module): static
    {
        if (isset($this->notesOutput)) {
            $this->migrator->setOutput($this->notesOutput);
        }

        $this->out('', true);
        $this->out(sprintf('<info>Migrating %s module...</info>', $module), true);
        $this->out('', true);

        $this->migrator->run(base_path() . '/modules/'.strtolower($module).'/database/migrations');

        return $this;
    }

    /**
     * Run seeds on a module
     */
    public function seedModule(string $module): static
    {
        $className = '\\' . $module . '\Database\Seeds\DatabaseSeeder';
        if (!class_exists($className)) {
            return $this;
        }

        $this->out('', true);
        $this->out(sprintf('<info>Seeding %s module...</info>', $module), true);
        $this->out('', true);

        $seeder = App::make($className);
        $return = $seeder->run();

        if (isset($return) && (is_string($return) || is_array($return))) {
            $this->addMessage($className, $return);
        }

        $this->write(Info::class, sprintf('Seeded %s', $module));

        return $this;
    }

    /**
     * Downloads the core from the update server.
     * @param string $hash Expected file hash.
     */
    public function downloadCore(string $hash): void
    {
        $this->api->requestServerFile('core/get', 'core', $hash, ['type' => 'update']);
    }

    /**
     * Extracts the core after it has been downloaded.
     */
    public function extractCore(): void
    {
        $filePath = $this->getFilePath('core');

        $this->extractArchive($filePath, $this->baseDirectory);
    }

    /**
     * Sets the build number and hash
     */
    public function setBuild(string $build, ?string $hash = null, bool $modified = false): void
    {
        $params = [
            'system::core.build' => $build,
            'system::core.modified' => $modified,
        ];

        if ($hash) {
            $params['system::core.hash'] = $hash;
        }

        Parameter::set($params);
    }

    //
    // Plugins
    //

    /**
     * Looks up a plugin from the update server.
     */
    public function requestPluginDetails(string $name): array
    {
        return $this->api->requestServerData('plugin/detail', ['name' => $name]);
    }

    /**
     * Looks up content for a plugin from the update server.
     */
    public function requestPluginContent(string $name): array
    {
        return $this->api->requestServerData('plugin/content', ['name' => $name]);
    }

    /**
     * Runs update on a single plugin
     */
    public function updatePlugin(string $name): static
    {
        /*
         * Update the plugin database and version
         */
        if (!($plugin = $this->pluginManager->findByIdentifier($name))) {
            $this->write(Error::class, sprintf('Unable to find plugin %s', $name));
            return $this;
        }

        $this->out(sprintf('<info>Migrating %s (%s) plugin...</info>', Lang::get($plugin->pluginDetails()['name']), $name));
        $this->out('', true);

        $this->versionManager->setNotesOutput($this->notesOutput);

        $this->versionManager->updatePlugin($plugin);

        return $this;
    }

    /**
     * Rollback an existing plugin
     *
     * @param string|null $stopOnVersion If this parameter is specified, the process stops once the provided version number is reached
     * @throws ApplicationException if the provided stopOnVersion cannot be found in the database
     */
    public function rollbackPlugin(string $name, string $stopOnVersion = null): static
    {
        /*
         * Remove the plugin database and version
         */
        if (!($plugin = $this->pluginManager->findByIdentifier($name))
            && $this->versionManager->purgePlugin($name)
        ) {
            $this->write(Info::class, sprintf('%s purged from database', $name));
            return $this;
        }

        if ($stopOnVersion && !$this->versionManager->hasDatabaseVersion($plugin, $stopOnVersion)) {
            throw new ApplicationException(Lang::get('system::lang.updates.plugin_version_not_found'));
        }

        if ($this->versionManager->removePlugin($plugin, $stopOnVersion, true)) {
            $this->write(Info::class, sprintf('%s rolled back', $name));

            if ($currentVersion = $this->versionManager->getCurrentVersion($plugin)) {
                $this->write(Info::class, sprintf(
                    'Current Version: %s (%s)',
                    $currentVersion,
                    $this->versionManager->getCurrentVersionNote($plugin)
                ));
            }

            return $this;
        }

        $this->write(Error::class, sprintf('Unable to find plugin %s', $name));

        return $this;
    }

    /**
     * Downloads a plugin from the update server.
     * @param bool $installation Indicates whether this is a plugin installation request.
     */
    public function downloadPlugin(string $name, string $hash, bool $installation = false): static
    {
        $fileCode = $name . $hash;
        $this->api->requestServerFile('plugin/get', $fileCode, $hash, [
            'name'         => $name,
            'installation' => $installation ? 1 : 0
        ]);
        return $this;
    }

    /**
     * Extracts a plugin after it has been downloaded.
     */
    public function extractPlugin(string $name, string $hash): void
    {
        $fileCode = $name . $hash;
        $filePath = $this->getFilePath($fileCode);

        $this->extractArchive($filePath, plugins_path());
    }

    //
    // Themes
    //

    /**
     * Looks up a theme from the update server.
     */
    public function requestThemeDetails(string $name): array
    {
        return $this->api->requestServerData('theme/detail', ['name' => $name]);
    }

    /**
     * Downloads a theme from the update server.
     */
    public function downloadTheme(string $name, string $hash): static
    {
        $fileCode = $name . $hash;
        $this->api->requestServerFile('theme/get', $fileCode, $hash, ['name' => $name]);
        return $this;
    }

    /**
     * Extracts a theme after it has been downloaded.
     */
    public function extractTheme(string $name, string $hash): void
    {
        $fileCode = $name . $hash;
        $filePath = $this->getFilePath($fileCode);

        $this->extractArchive($filePath, themes_path());

        if ($this->themeManager) {
            $this->themeManager->setInstalled($name);
        }
    }

    //
    // Notes
    //

    /**
     * Writes output to the console using a Laravel CLI View component.
     * @param string $component Class extending \Illuminate\Console\View\Components\Component to be used to render the message
     */
    protected function write(string $component, ...$arguments): static
    {
        if ($this->notesOutput !== null) {
            with(new $component($this->notesOutput))->render(...$arguments);
        }

        return $this;
    }

    /**
     * Writes output to the console.
     */
    protected function out(string $message, bool $newline = false): static
    {
        if ($this->notesOutput !== null) {
            $this->notesOutput->write($message, $newline);
        }

        return $this;
    }

    /**
     * Sets an output stream for writing notes.
     */
    public function setNotesOutput(OutputStyle $output): static
    {
        $this->notesOutput = $output;

        return $this;
    }

    public function getMigrationTableName(): string
    {
        return Config::get('database.migrations', 'migrations');
    }

    /**
     * Adds a message from a specific migration or seeder.
     */
    protected function addMessage(string|object $class, string|array $message): void
    {
        if (empty($message)) {
            return;
        }

        if (is_object($class)) {
            $class = get_class($class);
        }
        if (!isset($this->messages[$class])) {
            $this->messages[$class] = [];
        }

        if (is_string($message)) {
            $this->messages[$class][] = $message;
        } elseif (is_array($message)) {
            $this->messages[$class] = array_merge($this->messages[$class], $message);
        }
    }

    /**
     * Prints collated messages from the migrations and seeders
     */
    protected function printMessages(): void
    {
        if (!count($this->messages)) {
            return;
        }

        foreach ($this->messages as $class => $messages) {
            $this->write(Info::class, sprintf('%s reported the following:', $class));

            foreach ($messages as $message) {
                $this->out('    - ' . $message, true);
            }

            $this->out('', true);
        }
    }

    /**
     * Extract the provided archive
     *
     * @throws ApplicationException if the archive failed to extract
     */
    public function extractArchive(string $archive, string $destination): void
    {
        if (!Zip::extract($archive, $destination)) {
            throw new ApplicationException(Lang::get('system::lang.zip.extract_failed', ['file' => $archive]));
        }

        @unlink($archive);
    }

    /**
     * Finds all plugins in a given path by looking for valid Plugin.php files
     */
    public function findPluginsInPath(string $path): array
    {
        $pluginFiles = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === 'Plugin.php') {
                // Attempt to extract the plugin's code
                if (!preg_match('/namespace (.+?);/', file_get_contents($file->getRealPath()), $match)) {
                    continue;
                }

                $code = str_replace('\\', '.', $match[1]);

                if (str_contains($code, '.')) {
                    $pluginFiles[$code] = $file->getPathname();
                }
            }
        }

        return $pluginFiles;
    }
}

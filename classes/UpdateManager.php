<?php

namespace System\Classes;

use Carbon\Carbon;
use Cms\Classes\ThemeManager;
use Exception;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\App;
use System\Classes\Core\MarketPlaceApi;
use System\Classes\Extensions\PluginManager;
use System\Helpers\Cache as CacheHelper;
use System\Models\Parameter;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Support\Facades\Config;
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
    use \System\Classes\Core\UpdateManagerFileSystemTrait;
    use \System\Classes\Core\UpdateManagerCoreManagerTrait;
    use \System\Classes\Core\UpdateManagerModuleManagerTrait;
    use \System\Classes\Core\UpdateManagerPluginInstallerTrait;
    use \System\Classes\Core\UpdateManagerThemeInstallerTrait;

    protected PluginManager $pluginManager;
    protected ThemeManager $themeManager;
    protected MarketPlaceApi $api;
    protected Migrator $migrator;
    protected DatabaseMigrationRepository $repository;

    /**
     * If set to true, core updates will not be downloaded or extracted.
     */
    protected bool $disableCoreUpdates = false;

    /**
     * Array of messages returned by migrations / seeders. Returned at the end of the update process.
     */
    protected array $messages = [];

    /**
     * Initialize this singleton.
     */
    protected function init()
    {
        $this->disableCoreUpdates = Config::get('cms.disableCoreUpdates', false);

        $this->bindContainerObjects()
            ->setTempDirectory(temp_path())
            ->setBaseDirectory(base_path());
    }

    /**
     * These objects are "soft singletons" and may be lost when
     * the IoC container reboots. This provides a way to rebuild
     * for the purposes of unit testing.
     */
    public function bindContainerObjects(bool $refresh = false): static
    {
        $this->api = isset($this->api) && !$refresh
            ? $this->api
            : MarketPlaceApi::instance();

        $this->pluginManager = isset($this->pluginManager) && !$refresh
            ? $this->pluginManager
            : PluginManager::instance();

        $this->themeManager = isset($this->themeManager) && !$refresh
            ? $this->themeManager
            : (class_exists(ThemeManager::class) ? ThemeManager::instance() : null);

        $this->migrator = App::make('migrator');
        $this->repository = App::make('migration.repository');

        return $this;
    }

    public function isSystemSetup(): bool
    {
        return !Schema::hasTable($this->getMigrationTableName());
    }

    public function getMigrationTableName(): string
    {
        return Config::get('database.migrations', 'migrations');
    }

    /**
     * Creates the migration table and updates
     * @throws ApplicationException
     */
    public function update(): static
    {
        $firstUp = $this->isSystemSetup();

        $modules = Config::get('cms.loadModules', []);

        if ($firstUp) {
            $this->setupMigrations();
        }

        $this->migrateModules($modules);
        $plugins = $this->mapPluginReplacements();

        if ($firstUp) {
            $this->seedModules($modules);
        }

        $this->updatePlugins($plugins);

        Parameter::set('system::update.count', 0);
        CacheHelper::clear();

        $this->generatePluginReplacementNotices();

        return $this;
    }

    /**
     * Checks for new updates and returns the amount of unapplied updates.
     * Only requests from the server at a set interval (retry timer).
     * @param bool $force Ignore the retry timer.
     */
    public function check(bool $force = false): int
    {
        // Already know about updates, never retry.
        if (($oldCount = Parameter::get('system::update.count')) > 0) {
            return $oldCount;
        }

        // Retry period not passed, skipping.
        if (
            !$force
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
     * Roll back all modules and plugins.
     */
    public function tearDownTheSystem(): static
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
            $paths[] = base_path() . '/modules/' . strtolower($module) . '/database/migrations';
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
        // Find build by comparing with source manifest
        return App::make(SourceManifest::class)->compare(App::make(FileManifest::class), $detailed);
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

    /**
     * Returns the currently installed system hash.
     */
    public function getHash(): string
    {
        return Parameter::get('system::core.hash', md5('NULL'));
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

    protected function message(string|object $class, string $format, mixed ...$args): static
    {
        $this->messages[] = [
            'class' => is_object($class) ? get_class($class) : $class,
            'message' => sprintf($format, ...$args),
            'type' => 'info',
        ];

        return $this;
    }

    protected function error(string|object $class, string $format, mixed ...$args): static
    {
        $this->messages[] = [
            'class' => is_object($class) ? get_class($class) : $class,
            'message' => sprintf($format, ...$args),
            'type' => 'error',
        ];

        return $this;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}

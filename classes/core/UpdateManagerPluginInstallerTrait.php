<?php

namespace System\Classes\Core;

use Illuminate\Console\View\Components\Error;
use Illuminate\Console\View\Components\Info;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Support\Facades\Lang;
use Winter\Storm\Exception\ApplicationException;

trait UpdateManagerPluginInstallerTrait
{
    public function mapPluginReplacements(): array
    {
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
                $this->pluginManager->getVersionManager()->replacePlugin($plugin, $replace);
            }
        }

        return $plugins;
    }

    public function updatePlugins(array $plugins): static
    {
        foreach ($plugins as $code => $plugin) {
            $this->updatePlugin($code);
        }

        return $this;
    }

    public function generatePluginReplacementNotices(): static
    {
        foreach ($this->pluginManager->getReplacementMap() as $alias => $plugin) {
            if ($this->pluginManager->getActiveReplacementMap($alias)) {
                $this->message($plugin, Lang::get('system::lang.updates.update_warnings_plugin_replace_cli', [
                    'alias' => '<info>' . $alias . '</info>'
                ]));
            }
        }

        return $this;
    }


    /**
     * Runs update on a single plugin
     */
    public function updatePlugin(string $name): static
    {
        // Update the plugin database and version
        if (!($plugin = $this->pluginManager->findByIdentifier($name))) {
            $this->message($this, sprintf('Unable to find plugin %s', $name));
            return $this;
        }

        $this->message($this, sprintf('<info>Migrating %s (%s) plugin...</info>', Lang::get($plugin->pluginDetails()['name']), $name));

        $this->pluginManager->getVersionManager()->updatePlugin($plugin);

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
        // Remove the plugin database and version
        if (!($plugin = $this->pluginManager->findByIdentifier($name))
            && $this->pluginManager->getVersionManager()->purgePlugin($name)
        ) {
            $this->message($this, '%s purged from database', $name);
            return $this;
        }

        if ($stopOnVersion && !$this->pluginManager->getVersionManager()->hasDatabaseVersion($plugin, $stopOnVersion)) {
            throw new ApplicationException(Lang::get('system::lang.updates.plugin_version_not_found'));
        }

        if ($this->pluginManager->getVersionManager()->removePlugin($plugin, $stopOnVersion, true)) {
            $this->message($this, '%s rolled back', $name);

            if ($currentVersion = $this->pluginManager->getVersionManager()->getCurrentVersion($plugin)) {
                $this->message(
                    $this,
                    'Current Version: %s (%s)',
                    $currentVersion,
                    $this->pluginManager->getVersionManager()->getCurrentVersionNote($plugin)
                );
            }

            return $this;
        }

        $this->error($this, sprintf('Unable to find plugin %s', $name));

        return $this;
    }

    /**
     * Looks up a plugin from the update server.
     */
    public function requestPluginDetails(string $name): array
    {
        return $this->api->fetch('plugin/detail', ['name' => $name]);
    }

    public function request(string $type, string $info, string $name)
    {
        if (!in_array($type, ['plugin'])) {
            throw new ApplicationException('Invalid request type.');
        }

        if (!in_array($info, ['detail', 'content'])) {
            throw new ApplicationException('Invalid request info.');
        }

        return $this->api->fetch($type . '/' . $info, ['name' => $name]);
    }


    /**
     * Downloads a plugin from the update server.
     * @param bool $installation Indicates whether this is a plugin installation request.
     */
    public function downloadPlugin(string $name, string $hash, bool $installation = false): static
    {
        $fileCode = $name . $hash;
        $this->api->fetchFile('plugin/get', $fileCode, $hash, [
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
}

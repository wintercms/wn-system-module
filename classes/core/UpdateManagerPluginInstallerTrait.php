<?php

namespace System\Classes\Core;

use Illuminate\Console\View\Components\Error;
use Illuminate\Console\View\Components\Info;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Support\Facades\Lang;
use Winter\Storm\Exception\ApplicationException;

trait UpdateManagerPluginInstallerTrait
{
    public function updatePlugins(array $plugins): static
    {
        foreach ($plugins as $code => $plugin) {
            $this->updatePlugin($code);
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
            $this->pluginManager->getOutput()->info(sprintf('Unable to find plugin %s', $name));
            return $this;
        }

        $this->pluginManager->getOutput()->info(sprintf('<info>Migrating %s (%s) plugin...</info>', Lang::get($plugin->pluginDetails()['name']), $name));

        $this->pluginManager->versionManager()->updatePlugin($plugin);

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
            && $this->pluginManager->versionManager()->purgePlugin($name)
        ) {
            $this->pluginManager->getOutput()->info(sprintf('%s purged from database', $name));
            return $this;
        }

        if ($stopOnVersion && !$this->pluginManager->versionManager()->hasDatabaseVersion($plugin, $stopOnVersion)) {
            throw new ApplicationException(Lang::get('system::lang.updates.plugin_version_not_found'));
        }

        if ($this->pluginManager->versionManager()->removePlugin($plugin, $stopOnVersion, true)) {
            $this->pluginManager->getOutput()->info(sprintf('%s rolled back', $name));

            if ($currentVersion = $this->pluginManager->versionManager()->getCurrentVersion($plugin)) {
                $this->message(
                    $this,
                    'Current Version: %s (%s)',
                    $currentVersion,
                    $this->pluginManager->versionManager()->getCurrentVersionNote($plugin)
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



}

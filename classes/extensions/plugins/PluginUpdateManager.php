<?php

namespace System\Classes\Extensions\Plugins;

use Illuminate\Support\Facades\Lang;
use System\Classes\Extensions\ExtensionUpdateManager;
use System\Classes\Extensions\ExtensionUpdateManagerInterface;
use System\Classes\Extensions\PluginManager;
use System\Classes\Extensions\WinterExtension;
use System\Classes\Packager\Composer;
use Winter\Storm\Exception\ApplicationException;

class PluginUpdateManager extends ExtensionUpdateManager implements ExtensionUpdateManagerInterface
{
    protected PluginManager $pluginManager;

    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    public function update(): array
    {
        return $this->updateExtensions($this->pluginManager->list());
    }

    public function updateExtension(WinterExtension|string $extension): ?WinterExtension
    {
        // Update the plugin database and version
        if (!($plugin = $this->pluginManager->findByIdentifier($extension))) {
            $this->pluginManager->getOutput()->info(sprintf('Unable to find plugin %s', $extension));
            return null;
        }

        $this->pluginManager->getOutput()->info(sprintf('<info>Migrating %s (%s) plugin...</info>', Lang::get($plugin->pluginDetails()['name']), $name));

        $this->pluginManager->versionManager()->updatePlugin($plugin);

        return $plugin;
    }

    public function updateExtensions(array $extensions): array
    {
        $updated = [];
        foreach ($extensions as $extension) {
            $updated[] = $this->updateExtension($extension);
        }

        return $updated;
    }



    /**
     * @throws ApplicationException
     * @throws \Exception
     */
    public function rollbackExtension(WinterExtension|string $extension, ?string $stopOnVersion = null): WinterExtension
    {
        $name = is_string($extension) ? $extension : $this->pluginManager->resolveExtensionCode($extension);

        // Remove the plugin database and version
        if (
            !($plugin = $this->pluginManager->findByIdentifier($name))
            && $this->pluginManager->versionManager()->purgePlugin($name)
        ) {
            $this->pluginManager->getOutput()->info(sprintf('%s purged from database', $name));
            return $plugin;
        }

        if ($stopOnVersion && !$this->pluginManager->versionManager()->hasDatabaseVersion($plugin, $stopOnVersion)) {
            throw new ApplicationException(Lang::get('system::lang.updates.plugin_version_not_found'));
        }

        if ($this->pluginManager->versionManager()->removePlugin($plugin, $stopOnVersion, true)) {
            $this->pluginManager->getOutput()->info(sprintf('%s rolled back', $name));

            if ($currentVersion = $this->pluginManager->versionManager()->getCurrentVersion($plugin)) {
                $this->pluginManager->getOutput()->info(sprintf(
                    'Current Version: %s (%s)',
                    $currentVersion,
                    $this->pluginManager->versionManager()->getCurrentVersionNote($plugin)
                ));
            }

            return $plugin;
        }

        $this->pluginManager->getOutput()->error(sprintf('Unable to find plugin %s', $name));

        return $plugin;
    }

    /**
     * @throws ApplicationException
     */
    public function uninstall(): static
    {
        /*
        * Rollback plugins
        */
        $plugins = array_reverse($this->pluginManager->getAllPlugins());
        foreach ($plugins as $name => $plugin) {
            $this->rollbackExtension($name);
        }

        return $this;
    }
}

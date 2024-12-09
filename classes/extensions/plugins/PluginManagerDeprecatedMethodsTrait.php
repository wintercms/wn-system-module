<?php

namespace System\Classes\Extensions\Plugins;

use System\Classes\Extensions\PluginBase;

trait PluginManagerDeprecatedMethodsTrait
{
    /**
     * Returns an array with all enabled plugins
     *
     * @return array [$code => $pluginObj]
     * @deprecated
     */
    public function getPlugins(): array
    {
        return $this->list();
    }

    /**
     * Tears down a plugin's database tables and rebuilds them.
     * @deprecated
     */
    public function refreshPlugin(string $id): void
    {
        $this->refresh($id);
    }

    /**
     * Completely roll back and delete a plugin from the system.
     * @deprecated
     */
    public function deletePlugin(string $id): void
    {
        $this->uninstall($id);
    }

    /**
     * Disables the provided plugin using the provided flag (defaults to static::DISABLED_BY_USER)
     * @deprecated
     */
    public function disablePlugin(PluginBase|string $plugin, string|bool $flag = self::DISABLED_BY_USER): bool
    {
        return $this->disable($plugin, $flag);
    }

    /**
     * Enables the provided plugin using the provided flag (defaults to static::DISABLED_BY_USER)
     * @deprecated
     */
    public function enablePlugin(PluginBase|string $plugin, $flag = self::DISABLED_BY_USER): bool
    {
        return $this->enable($plugin, $flag);
    }
}

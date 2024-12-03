<?php

namespace System\Classes\Extensions;

/////////////////////////////////////
/// @TODO: Move this back to storm.
/////////////////////////////////////

use Winter\Storm\Support\ClassLoader;
use Winter\Storm\Support\Str;
use Winter\Storm\Support\Facades\File;
use Illuminate\Support\ServiceProvider as ServiceProviderBase;

abstract class ModuleServiceProvider extends ServiceProviderBase implements WinterExtension
{
    /**
     * @var \Winter\Storm\Foundation\Application The application instance.
     */
    protected $app;

    /**
     * Bootstrap the application events.
     * @return void
     */
    public function boot()
    {
        $module = strtolower($this->getModule());
        $modulePath = base_path("modules/$module");

        // Register paths for: config, translator, view
        $this->loadViewsFrom($modulePath . '/views', $module);
        $this->loadTranslationsFrom($modulePath . '/lang', $module);
        $this->loadConfigFrom($modulePath . '/config', $module);

        // Register routes if present
        $routesFile = "$modulePath/routes.php";
        if (File::isFile($routesFile)) {
            $this->loadRoutesFrom($routesFile);
        }
    }

    /**
     * Registers the Module service provider.
     * @return void
     */
    public function register()
    {
        // Register this module with the application's ClassLoader for autoloading
        $module = $this->getModule();
        $this->app->make(ClassLoader::class)->autoloadPackage($module . '\\', "modules/" . strtolower($module) . '/');
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Gets the name of this module
     */
    public function getModule(): string
    {
        return Str::before(get_class($this), '\\');
    }

    /**
     * Registers a new console (artisan) command
     * @param string $key The command name
     * @param string $class The command class
     * @return void
     */
    public function registerConsoleCommand($key, $class)
    {
        $key = 'command.'.$key;

        $this->app->singleton($key, function ($app) use ($class) {
            return new $class;
        });

        $this->commands($key);
    }

    /**
     * Register a config file namespace.
     * @param  string  $path
     * @param  string  $namespace
     * @return void
     */
    protected function loadConfigFrom($path, $namespace)
    {
        /** @var \Winter\Storm\Config\Repository */
        $config = $this->app['config'];
        $config->package($namespace, $path);
    }

    public function install(): static
    {
        // TODO: Implement install() method.
    }

    public function uninstall(): static
    {
        // TODO: Implement uninstall() method.
    }

    public function enable(): static
    {
        // TODO: Implement enable() method.
    }

    public function disable(): static
    {
        // TODO: Implement disable() method.
    }

    public function rollback(): static
    {
        // TODO: Implement rollback() method.
    }

    public function refresh(): static
    {
        // TODO: Implement refresh() method.
    }

    public function update(): static
    {
        // TODO: Implement update() method.
    }
}

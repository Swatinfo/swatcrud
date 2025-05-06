<?php

namespace Ibex\CrudGenerator;

use Ibex\CrudGenerator\Commands\CrudGenerator;
use Illuminate\Support\ServiceProvider;

/**
 * Class CrudServiceProvider.
 */
class CrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {


        if ($this->app->runningInConsole()) {
            $this->commands([
                CrudGenerator::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/config/crud.php' => config_path('crud.php'),
        ], 'crud');

        $this->publishes([
            __DIR__ . '/../src/stubs' => resource_path('stubs/crud/'),
        ], 'stubs-crud');

        // Register module namespace
        $modulesPath = base_path('Modules');
        if (is_dir($modulesPath)) {
            $modules = array_map('basename', glob($modulesPath . '/*', GLOB_ONLYDIR));
            foreach ($modules as $module) {
                $this->loadModuleProviders($module);
            }
        }
    }

    /**
     * Load module providers.
     *
     * @param string $module
     *
     * @return void
     */
    protected function loadModuleProviders(string $module): void
    {
        $providerPath = base_path("Modules/$module/Providers/{$module}ServiceProvider.php");
        if (file_exists($providerPath)) {
            $this->app->register("Modules\\{$module}\\Providers\\{$module}ServiceProvider");
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RouteGenerator::class, function ($app) {
            return new RouteGenerator();
        });
    }
}

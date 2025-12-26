<?php

namespace Webkul\Reel\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Nuwave\Lighthouse\Events\BuildSchemaString;

class ReelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin-routes.php');

        $this->loadRoutesFrom(__DIR__ . '/../Routes/shop-routes.php');

        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'reel');

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'reel');

        Event::listen('bagisto.admin.layout.head', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('reel::admin.layouts.style');
        });

        $this->app->concord->registerModel(
            \Webkul\Product\Contracts\Product::class,
            \Webkul\Reel\Models\Product::class
        );

        // Add to the boot() method in RMAServiceProvider
        $this->publishes([
            __DIR__ . '/../Resources/lang' => resource_path('lang/vendor/reel'),
        ], 'reel-translations');

        $this->app['events']->listen(BuildSchemaString::class, function (BuildSchemaString $event) {
            $schemaPath = __DIR__ . '/../graphql/schema.graphql';

            if (file_exists($schemaPath)) {
                return file_get_contents($schemaPath);
            }

            return '';
        });
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/admin-menu.php',
            'menu.admin'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/acl.php',
            'acl'
        );
    }
}
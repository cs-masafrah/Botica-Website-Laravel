<?php

namespace Webkul\Product\Providers;

use Illuminate\Support\ServiceProvider;

class GraphQLNamespaceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Manually add the GraphQL directory to the autoloader
        $loader = require base_path('vendor/autoload.php');

        // Add GraphQL namespace
        $loader->addPsr4('Webkul\\Product\\GraphQL\\', __DIR__ . '/../../GraphQL');

        // Also add Queries sub-namespace
        $loader->addPsr4('Webkul\\Product\\GraphQL\\Queries\\', __DIR__ . '/../../GraphQL/Queries');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

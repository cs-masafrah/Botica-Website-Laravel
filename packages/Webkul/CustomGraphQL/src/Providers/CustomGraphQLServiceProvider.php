<?php

namespace Webkul\CustomGraphQL\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\GraphQLAPI\Queries\Shop\Common\HomePageQuery as BaseHomePageQuery;
use Webkul\CustomGraphQL\Queries\Shop\Common\HomePageQuery as CustomHomePageQuery;

class CustomGraphQLServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Debug: Log that provider is loading
        \Log::info('CustomGraphQLServiceProvider is loading');

        $this->app->bind(BaseHomePageQuery::class, function ($app) {
            return $app->make(CustomHomePageQuery::class);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Debug: Log boot method
        \Log::info('CustomGraphQLServiceProvider boot method called');
    }
}
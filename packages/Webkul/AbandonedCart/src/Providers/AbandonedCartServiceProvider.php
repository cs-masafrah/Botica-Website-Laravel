<?php

namespace Webkul\AbandonedCart\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Console\Scheduling\Schedule;

class AbandonedCartServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register config FIRST
        $this->registerConfig();

        $this->registerRepositories();
        $this->registerCommands();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin-routes.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/shop-routes.php');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'abandonedcart');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'abandonedcart');

        Event::listen('bagisto.admin.layout.head', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('abandonedcart::admin.layouts.style');
        });

        $this->registerEventListeners();

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('abandoned-cart:track')->everyFiveMinutes();
            $schedule->command('abandoned-cart:send-emails')->hourly();

            // $schedule->command('abandoned-cart:track')->everyMinute();
            // $schedule->command('abandoned-cart:send-emails')->everyMinute();

            // $schedule->command('abandoned-cart:track')->at('7:11');
            // $schedule->command('abandoned-cart:send-emails')->at('7:11');
        });

        // // Publish assets if nee
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/system.php',
            'core'
        );
        // $this->publishes([
        //     __DIR__ . '/../Resources/lang' => resource_path('lang/vendor/admin'),
        // ], 'abandoned-cart-lang');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'abandonedcart');
    }

    /**
     * Register package config.
     */
    protected function registerConfig()
    {
        // Merge configs
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/admin-menu.php',
            'menu.admin'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/acl.php',
            'acl'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/system.php',
            'core'
        );
    }

    /**
     * Register event listeners.
     */
    protected function registerEventListeners()
    {
        Event::listen('checkout.cart.add.after', function ($cart) {
            if ($cart->abandonedCart) {
                $cart->abandonedCart->update(['is_converted' => true]);
            }
        });

        Event::listen('sales.order.save.after', function ($order) {
            if ($order->cart && $order->cart->abandonedCart) {
                $order->cart->abandonedCart->update([
                    'is_converted' => true,
                    'converted_at' => now()
                ]);
            }
        });

        Event::listen('customer.registration.after', function ($customer) {
            \Webkul\AbandonedCart\Models\AbandonedCart::where('customer_email', $customer->email)
                ->update(['customer_id' => $customer->id]);
        });
    }

    /**
     * Register repositories.
     */
    protected function registerRepositories()
    {
        $this->app->bind(
            \Webkul\AbandonedCart\Repositories\AbandonedCartRepository::class,
            \Webkul\AbandonedCart\Repositories\AbandonedCartRepository::class
        );

        $this->app->bind(
            \Webkul\AbandonedCart\Repositories\AbandonedCartRuleRepository::class,
            \Webkul\AbandonedCart\Repositories\AbandonedCartRuleRepository::class
        );
    }

    /**
     * Register console commands.
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Webkul\AbandonedCart\Console\Commands\TrackAbandonedCarts::class,
                \Webkul\AbandonedCart\Console\Commands\SendAbandonedCartEmails::class,
            ]);
        }
    }
}
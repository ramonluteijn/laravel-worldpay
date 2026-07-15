<?php

namespace RamonLuteijn\LaravelWorldpay;

use Illuminate\Support\ServiceProvider;

class WorldpayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/worldpay.php', 'worldpay');

        $this->app->singleton(WorldpayClient::class, fn ($app) => new WorldpayClient($app['config']['worldpay']));

        $this->app->singleton(WorldpayManager::class, fn ($app) => new WorldpayManager($app->make(WorldpayClient::class)));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/worldpay.php' => config_path('worldpay.php'),
        ], 'worldpay-config');

        if (config('worldpay.webhooks.enabled')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/webhooks.php');
        }
    }
}

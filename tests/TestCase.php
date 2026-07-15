<?php

namespace RamonLuteijn\LaravelWorldpay\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RamonLuteijn\LaravelWorldpay\WorldpayServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [WorldpayServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('worldpay.environment', 'try');
        $app['config']->set('worldpay.entity', 'default');
        $app['config']->set('worldpay.default_currency', 'EUR');
        $app['config']->set('worldpay.credentials.try', [
            'username' => 'test-username',
            'password' => 'test-password',
        ]);
        $app['config']->set('worldpay.payment_methods.card.enabled', true);
        $app['config']->set('worldpay.payment_methods.applepay.enabled', true);
        $app['config']->set('worldpay.payment_methods.googlepay.enabled', true);
        $app['config']->set('worldpay.apms.ideal.enabled', true);
    }
}

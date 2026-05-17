<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Tests;

use Am2tec\PaymentApiSdk\Providers\PaymentApiServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [PaymentApiServiceProvider::class];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('payment-api.url', 'https://api.payment.test');
        $app['config']->set('payment-api.key', 'test-bearer-token');
        $app['config']->set('payment-api.webhook_secret', 'test-secret-exactly-32-chars!!!');
        $app['config']->set('payment-api.timeout', 5);
        $app['config']->set('payment-api.retry_times', 0);
    }
}

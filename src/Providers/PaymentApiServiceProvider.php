<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Providers;

use Am2tec\PaymentApiSdk\Http\Middleware\ValidatePaymentApiWebhook;
use Am2tec\PaymentApiSdk\PaymentApi;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class PaymentApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/payment-api.php', 'payment-api');

        $this->app->singleton('payment-api', function ($app) {
            $config = $app['config']['payment-api'];

            return new PaymentApi(
                baseUrl: rtrim((string) ($config['url'] ?? ''), '/'),
                apiKey: (string) ($config['key'] ?? ''),
                webhookSecret: (string) ($config['webhook_secret'] ?? ''),
                timeout: (int) ($config['timeout'] ?? 15),
                retryTimes: (int) ($config['retry_times'] ?? 3),
                retryDelayMs: (int) ($config['retry_delay_ms'] ?? 500),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/payment-api.php' => config_path('payment-api.php'),
            ], 'payment-api-config');
        }

        if ($this->app->bound('router')) {
            /** @var Router $router */
            $router = $this->app->make('router');
            $router->aliasMiddleware('payment-api.webhook', ValidatePaymentApiWebhook::class);
        }
    }
}

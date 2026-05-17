<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Payment API URL
    |--------------------------------------------------------------------------
    */
    'url' => env('PAYMENT_API_URL', 'https://api.payment.am2tec.com'),

    /*
    |--------------------------------------------------------------------------
    | Bearer token issued by payment_api for this tenant.
    |--------------------------------------------------------------------------
    */
    'key' => env('PAYMENT_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | HMAC secret used to verify inbound webhooks from payment_api.
    |--------------------------------------------------------------------------
    */
    'webhook_secret' => env('PAYMENT_API_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | HTTP timeout in seconds for outbound API calls.
    |--------------------------------------------------------------------------
    */
    'timeout' => env('PAYMENT_API_TIMEOUT', 15),

    /*
    |--------------------------------------------------------------------------
    | Retry configuration for failed HTTP requests.
    |--------------------------------------------------------------------------
    */
    'retry_times' => env('PAYMENT_API_RETRY_TIMES', 3),
    'retry_delay_ms' => env('PAYMENT_API_RETRY_DELAY_MS', 500),
];

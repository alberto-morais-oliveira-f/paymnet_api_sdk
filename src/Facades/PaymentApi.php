<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Facades;

use Am2tec\PaymentApiSdk\Resources\ChargeResource;
use Am2tec\PaymentApiSdk\Resources\PlanResource;
use Am2tec\PaymentApiSdk\Resources\ProviderResource;
use Am2tec\PaymentApiSdk\Resources\SubscriptionResource;
use Am2tec\PaymentApiSdk\Webhook\WebhookValidator;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ChargeResource charge()
 * @method static PlanResource plan()
 * @method static ProviderResource provider()
 * @method static SubscriptionResource subscription()
 * @method static WebhookValidator webhook()
 *
 * @see \Am2tec\PaymentApiSdk\PaymentApi
 */
class PaymentApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'payment-api';
    }
}

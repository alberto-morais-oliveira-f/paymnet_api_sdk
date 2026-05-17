<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk;

use Am2tec\PaymentApiSdk\Resources\ChargeResource;
use Am2tec\PaymentApiSdk\Resources\PlanResource;
use Am2tec\PaymentApiSdk\Resources\ProviderResource;
use Am2tec\PaymentApiSdk\Resources\SubscriptionResource;
use Am2tec\PaymentApiSdk\Webhook\WebhookValidator;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class PaymentApi
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly string $webhookSecret,
        private readonly int $timeout,
        private readonly int $retryTimes,
        private readonly int $retryDelayMs,
    ) {}

    public function charge(): ChargeResource
    {
        return new ChargeResource($this->client());
    }

    public function plan(): PlanResource
    {
        return new PlanResource($this->client());
    }

    public function subscription(): SubscriptionResource
    {
        return new SubscriptionResource($this->client());
    }

    public function provider(): ProviderResource
    {
        return new ProviderResource($this->client());
    }

    public function webhook(): WebhookValidator
    {
        return new WebhookValidator($this->webhookSecret);
    }

    private function client(): PendingRequest
    {
        $pending = Http::baseUrl($this->baseUrl)
            ->withToken($this->apiKey)
            ->acceptJson()
            ->timeout($this->timeout);

        if ($this->retryTimes > 0) {
            $pending = $pending->retry($this->retryTimes, $this->retryDelayMs);
        }

        return $pending;
    }
}

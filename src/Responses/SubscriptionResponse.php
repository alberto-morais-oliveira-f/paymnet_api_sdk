<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Responses;

readonly class SubscriptionResponse
{
    public function __construct(
        public string $id,
        public string $status,
        public ?string $providerAlias,
        public ?string $providerSubscriptionId,
        public ?string $referenceId,
        public ?string $startedAt,
        public ?string $cancelledAt,
        public ?string $trialEndsAt,
        public ?string $pausedAt,
        public ?string $nextBillingDate,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: $data['status'],
            providerAlias: $data['provider_alias'] ?? null,
            providerSubscriptionId: $data['provider_subscription_id'] ?? null,
            referenceId: $data['reference_id'] ?? null,
            startedAt: $data['started_at'] ?? null,
            cancelledAt: $data['cancelled_at'] ?? null,
            trialEndsAt: $data['trial_ends_at'] ?? null,
            pausedAt: $data['paused_at'] ?? null,
            nextBillingDate: $data['next_billing_date'] ?? null,
        );
    }
}

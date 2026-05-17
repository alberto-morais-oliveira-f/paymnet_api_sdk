<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Responses;

readonly class SubscriptionResponse
{
    public function __construct(
        public string $id,
        public string $status,
        public ?string $providerSubscriptionId,
        public ?string $referenceId,
        public ?string $startedAt,
        public ?string $cancelledAt,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: $data['status'],
            providerSubscriptionId: $data['provider_subscription_id'] ?? null,
            referenceId: $data['reference_id'] ?? null,
            startedAt: $data['started_at'] ?? null,
            cancelledAt: $data['cancelled_at'] ?? null,
        );
    }
}

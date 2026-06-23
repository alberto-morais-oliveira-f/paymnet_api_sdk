<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Responses;

readonly class CardTokenResponse
{
    public function __construct(
        public string $id,
        public string $provider,
        public ?string $providerAlias,
        public ?string $brand,
        public ?string $last4,
        public ?string $expMonth,
        public ?string $expYear,
        public bool $isDefault,
        public ?string $lastUsedAt,
        public ?string $createdAt,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            provider: $data['provider'],
            providerAlias: $data['provider_alias'] ?? null,
            brand: $data['brand'] ?? null,
            last4: $data['last4'] ?? null,
            expMonth: $data['exp_month'] ?? null,
            expYear: $data['exp_year'] ?? null,
            isDefault: (bool) ($data['is_default'] ?? false),
            lastUsedAt: $data['last_used_at'] ?? null,
            createdAt: $data['created_at'] ?? null,
        );
    }
}

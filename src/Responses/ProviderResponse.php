<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Responses;

readonly class ProviderResponse
{
    public function __construct(
        public string $id,
        public string $provider,
        public bool $active,
        public string $createdAt,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            provider: $data['provider'],
            active: (bool) $data['active'],
            createdAt: $data['created_at'],
        );
    }
}

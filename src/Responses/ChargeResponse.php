<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Responses;

readonly class ChargeResponse
{
    public function __construct(
        public string $id,
        public string $status,
        public ?string $checkoutUrl,
        public ?string $pixCode,
        public ?int $amountCents,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: $data['status'],
            checkoutUrl: $data['checkout_url'] ?? null,
            pixCode: $data['pix_code'] ?? null,
            amountCents: isset($data['amount']) ? (int) $data['amount'] : null,
        );
    }
}

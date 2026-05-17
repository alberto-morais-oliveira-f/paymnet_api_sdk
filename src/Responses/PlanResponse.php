<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Responses;

readonly class PlanResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public int $amountCents,
        public string $interval,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            amountCents: (int) $data['amount_cents'],
            interval: $data['interval'],
        );
    }
}

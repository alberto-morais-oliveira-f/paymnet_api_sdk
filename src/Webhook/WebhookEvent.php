<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Webhook;

readonly class WebhookEvent
{
    public function __construct(
        public string $event,
        public string $referenceId,
        public ?int $amountCents,
        public ?string $paidAt,
        public string $provider,
        public ?string $chargeId,
        public array $metadata,
    ) {}

    /** @param array<string, mixed> $payload */
    public static function fromPayload(array $payload): self
    {
        return new self(
            event: $payload['event'],
            referenceId: $payload['reference_id'],
            amountCents: isset($payload['amount_cents']) ? (int) $payload['amount_cents'] : null,
            paidAt: $payload['paid_at'] ?? null,
            provider: $payload['provider'],
            chargeId: $payload['charge_id'] ?? null,
            metadata: $payload['metadata'] ?? [],
        );
    }

    public function is(string $event): bool
    {
        return $this->event === $event;
    }
}

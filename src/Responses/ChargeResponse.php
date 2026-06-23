<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Responses;

readonly class ChargeResponse
{
    /**
     * @param array<string, mixed>|null $split  Snapshot do split enviado ao gateway (recipient_account_id, fee_cents).
     */
    public function __construct(
        public string $id,
        public string $status,
        public ?string $providerAlias,
        public ?string $checkoutUrl,
        public ?string $pixCode,
        public ?int $amountCents,
        public ?string $cardTokenId = null,
        public ?array $split = null,
        public ?int $platformFeeCents = null,
        public ?string $splitStatus = null,
        public ?string $boletoCode = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: $data['status'],
            providerAlias: $data['provider_alias'] ?? null,
            checkoutUrl: $data['checkout_url'] ?? null,
            pixCode: $data['pix_code'] ?? null,
            amountCents: isset($data['amount']) ? (int) $data['amount'] : null,
            cardTokenId: $data['card_token_id'] ?? null,
            split: $data['split'] ?? null,
            platformFeeCents: isset($data['platform_fee_cents']) ? (int) $data['platform_fee_cents'] : null,
            splitStatus: $data['split_status'] ?? null,
            boletoCode: $data['boleto_code'] ?? null,
        );
    }
}

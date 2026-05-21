<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Responses;

readonly class ExtensionRequestResponse
{
    public function __construct(
        public string $id,
        public string $status,
        public string $message,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: $data['status'],
            message: $data['message'] ?? '',
        );
    }
}

<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Resources;

use Am2tec\PaymentApiSdk\Responses\CardTokenResponse;
use Illuminate\Http\Client\PendingRequest;

class CardTokenResource
{
    public function __construct(private readonly PendingRequest $client) {}

    /**
     * Saved cards (tokenized) available for one-click charges and local recurrence.
     * The raw gateway token is never exposed.
     *
     * @return CardTokenResponse[]
     */
    public function list(): array
    {
        $response = $this->client->get('/api/v1/card-tokens');
        $response->throw();

        return array_map(
            fn (array $item) => CardTokenResponse::fromArray($item),
            $response->json('data') ?? [],
        );
    }

    public function delete(string $id): bool
    {
        $response = $this->client->delete("/api/v1/card-tokens/{$id}");
        $response->throw();

        return true;
    }
}

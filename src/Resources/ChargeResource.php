<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Resources;

use Am2tec\PaymentApiSdk\Responses\ChargeResponse;
use Illuminate\Http\Client\PendingRequest;

class ChargeResource
{
    public function __construct(private readonly PendingRequest $client) {}

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): ChargeResponse
    {
        $response = $this->client->post('/api/v1/charges', $data);
        $response->throw();

        return ChargeResponse::fromArray($response->json('data'));
    }

    public function find(string $id): ChargeResponse
    {
        $response = $this->client->get("/api/v1/charges/{$id}");
        $response->throw();

        return ChargeResponse::fromArray($response->json('data'));
    }

    public function cancel(string $id): bool
    {
        $response = $this->client->delete("/api/v1/charges/{$id}");
        $response->throw();

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function refund(string $id, array $data = []): ChargeResponse
    {
        $response = $this->client->post("/api/v1/charges/{$id}/refund", $data);
        $response->throw();

        return ChargeResponse::fromArray($response->json('data'));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return ChargeResponse[]
     */
    public function list(array $filters = []): array
    {
        $response = $this->client->get('/api/v1/charges', $filters);
        $response->throw();

        return array_map(
            fn (array $item) => ChargeResponse::fromArray($item),
            $response->json('data') ?? [],
        );
    }
}

<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Resources;

use Am2tec\PaymentApiSdk\Responses\PlanResponse;
use Illuminate\Http\Client\PendingRequest;

class PlanResource
{
    public function __construct(private readonly PendingRequest $client) {}

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): PlanResponse
    {
        $response = $this->client->post('/api/v1/plans', $data);
        $response->throw();

        return PlanResponse::fromArray($response->json('data'));
    }

    /**
     * @return PlanResponse[]
     */
    public function list(): array
    {
        $response = $this->client->get('/api/v1/plans');
        $response->throw();

        return array_map(
            fn (array $item) => PlanResponse::fromArray($item),
            $response->json('data') ?? [],
        );
    }

    public function delete(string $id): bool
    {
        $response = $this->client->delete("/api/v1/plans/{$id}");
        $response->throw();

        return true;
    }
}

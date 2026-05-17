<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Resources;

use Am2tec\PaymentApiSdk\Responses\ProviderResponse;
use Illuminate\Http\Client\PendingRequest;

class ProviderResource
{
    public function __construct(private readonly PendingRequest $client) {}

    /**
     * @param array<string, mixed> $credentials
     */
    public function store(string $provider, array $credentials): ProviderResponse
    {
        $response = $this->client->post('/api/v1/providers', [
            'provider'    => $provider,
            'credentials' => $credentials,
        ]);
        $response->throw();

        return ProviderResponse::fromArray($response->json('data'));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ProviderResponse
    {
        $response = $this->client->put("/api/v1/providers/{$id}", $data);
        $response->throw();

        return ProviderResponse::fromArray($response->json('data'));
    }

    public function delete(string $id): bool
    {
        $response = $this->client->delete("/api/v1/providers/{$id}");
        $response->throw();

        return true;
    }

    /**
     * @return ProviderResponse[]
     */
    public function list(): array
    {
        $response = $this->client->get('/api/v1/providers');
        $response->throw();

        return array_map(
            fn (array $item) => ProviderResponse::fromArray($item),
            $response->json('data') ?? [],
        );
    }
}

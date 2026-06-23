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
    public function store(string $provider, array $credentials, ?string $alias = null): ProviderResponse
    {
        $response = $this->client->post('/api/v1/providers', array_filter([
            'provider'    => $provider,
            'alias'       => $alias,
            'credentials' => $credentials,
        ], fn ($v) => $v !== null));
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

    /**
     * Provider-neutral capability flags (pix, boleto, transparent_card,
     * native_subscriptions, tokenization_js, ...) used to render the right
     * checkout for a gateway.
     *
     * @return array<string, mixed>
     */
    public function capabilities(string $provider): array
    {
        $response = $this->client->get("/api/v1/providers/{$provider}/capabilities");
        $response->throw();

        return $response->json('capabilities') ?? [];
    }

    /**
     * C6 transparent-checkout SDK session: the public key the browser uses to
     * encrypt the card before it reaches the API.
     *
     * @return array<string, mixed>
     */
    public function c6PublicKey(?string $alias = null): array
    {
        $response = $this->client->get('/api/v1/providers/c6/public-key', array_filter([
            'provider_alias' => $alias,
        ], fn ($v) => $v !== null));
        $response->throw();

        return $response->json() ?? [];
    }
}

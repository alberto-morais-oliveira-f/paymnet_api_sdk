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

    /**
     * MercadoPago marketplace onboarding: URL de consentimento OAuth para o
     * vendedor (seller) conectar a conta. `returnUrl` = tela do tenant para onde
     * o callback redireciona após conectar.
     */
    public function mercadoPagoAuthorizeUrl(string $sellerRef, ?string $returnUrl = null): string
    {
        $response = $this->client->post('/api/v1/providers/mercadopago/oauth/authorize-url', array_filter([
            'seller_ref' => $sellerRef,
            'return_url' => $returnUrl,
        ], fn ($v) => $v !== null));
        $response->throw();

        return $response->json('url');
    }

    /**
     * Status de conexão do vendedor MercadoPago. Inclui `public_key` (usada no
     * browser para inicializar o MercadoPago.js / checkout transparente).
     *
     * @return array{connected: bool, mp_user_id: ?string, public_key: ?string}
     */
    public function mercadoPagoSellerStatus(string $sellerRef): array
    {
        $response = $this->client->get("/api/v1/providers/mercadopago/seller/{$sellerRef}/status");
        $response->throw();

        return $response->json() ?? [];
    }

    /**
     * Desconecta o vendedor MercadoPago (remove tokens OAuth).
     */
    public function mercadoPagoDisconnect(string $sellerRef): bool
    {
        $response = $this->client->delete("/api/v1/providers/mercadopago/seller/{$sellerRef}");
        $response->throw();

        return true;
    }
}

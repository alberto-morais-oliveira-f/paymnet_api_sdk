<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Tests\Feature;

use Am2tec\PaymentApiSdk\Facades\PaymentApi;
use Am2tec\PaymentApiSdk\Responses\ProviderResponse;
use Am2tec\PaymentApiSdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ProviderResourceTest extends TestCase
{
    private array $providerPayload = [
        'data' => [
            'id'         => 'provider_uuid_001',
            'provider'   => 'asaas',
            'active'     => true,
            'created_at' => '2026-05-17T10:00:00.000000Z',
        ],
    ];

    public function test_store_returns_typed_response(): void
    {
        Http::fake(['https://api.payment.test/api/v1/providers' => Http::response($this->providerPayload, 201)]);

        $provider = PaymentApi::provider()->store('asaas', ['api_key' => 'live_key_123']);

        $this->assertInstanceOf(ProviderResponse::class, $provider);
        $this->assertSame('provider_uuid_001', $provider->id);
        $this->assertSame('asaas', $provider->provider);
        $this->assertTrue($provider->active);
    }

    public function test_update_returns_typed_response(): void
    {
        Http::fake(['https://api.payment.test/api/v1/providers/provider_uuid_001' => Http::response($this->providerPayload, 200)]);

        $provider = PaymentApi::provider()->update('provider_uuid_001', ['active' => true]);

        $this->assertInstanceOf(ProviderResponse::class, $provider);
        $this->assertSame('asaas', $provider->provider);
    }

    public function test_delete_returns_true(): void
    {
        Http::fake(['https://api.payment.test/api/v1/providers/provider_uuid_001' => Http::response(null, 204)]);

        $this->assertTrue(PaymentApi::provider()->delete('provider_uuid_001'));
    }

    public function test_list_returns_array_of_typed_responses(): void
    {
        Http::fake([
            'https://api.payment.test/api/v1/providers' => Http::response([
                'data' => [
                    $this->providerPayload['data'],
                    array_merge($this->providerPayload['data'], ['id' => 'provider_uuid_002', 'provider' => 'mercadopago']),
                ],
            ], 200),
        ]);

        $providers = PaymentApi::provider()->list();

        $this->assertCount(2, $providers);
        $this->assertInstanceOf(ProviderResponse::class, $providers[0]);
        $this->assertSame('mercadopago', $providers[1]->provider);
    }

    public function test_store_throws_on_http_error(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Unprocessable'], 422)]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        PaymentApi::provider()->store('stripe', ['api_key' => 'invalid']);
    }
}

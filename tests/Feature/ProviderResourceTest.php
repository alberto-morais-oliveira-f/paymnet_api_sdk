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

    public function test_store_sends_alias_and_exposes_it(): void
    {
        Http::fake(['https://api.payment.test/api/v1/providers' => Http::response([
            'data' => array_merge($this->providerPayload['data'], ['provider' => 'mercadopago', 'alias' => 'academia-x']),
        ], 201)]);

        $provider = PaymentApi::provider()->store('mercadopago', ['access_token' => 'gym_token'], 'academia-x');

        Http::assertSent(fn ($request) => $request['alias'] === 'academia-x' && $request['provider'] === 'mercadopago');
        $this->assertSame('academia-x', $provider->alias);
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

    public function test_capabilities_returns_flags_array(): void
    {
        Http::fake(['https://api.payment.test/api/v1/providers/c6bank/capabilities' => Http::response([
            'provider'     => 'c6bank',
            'capabilities' => [
                'pix'                  => true,
                'transparent_card'     => true,
                'tokenization_js'      => 'c6',
                'native_subscriptions' => false,
            ],
        ], 200)]);

        $caps = PaymentApi::provider()->capabilities('c6bank');

        $this->assertTrue($caps['transparent_card']);
        $this->assertSame('c6', $caps['tokenization_js']);
        $this->assertFalse($caps['native_subscriptions']);
    }

    public function test_mercadopago_authorize_url_returns_url(): void
    {
        Http::fake(['https://api.payment.test/api/v1/providers/mercadopago/oauth/authorize-url' => Http::response([
            'url' => 'https://auth.mercadopago.com/authorization?client_id=1',
        ], 200)]);

        $url = PaymentApi::provider()->mercadoPagoAuthorizeUrl('academia-x', 'https://app.test/return');

        $this->assertStringContainsString('auth.mercadopago.com', $url);
        Http::assertSent(fn ($req) => $req['seller_ref'] === 'academia-x' && $req['return_url'] === 'https://app.test/return');
    }

    public function test_mercadopago_seller_status_returns_public_key(): void
    {
        Http::fake(['https://api.payment.test/api/v1/providers/mercadopago/seller/academia-x/status' => Http::response([
            'connected'  => true,
            'mp_user_id' => '123',
            'public_key' => 'APP_USR-public-key',
        ], 200)]);

        $status = PaymentApi::provider()->mercadoPagoSellerStatus('academia-x');

        $this->assertTrue($status['connected']);
        $this->assertSame('APP_USR-public-key', $status['public_key']);
    }

    public function test_mercadopago_disconnect_returns_true(): void
    {
        Http::fake(['https://api.payment.test/api/v1/providers/mercadopago/seller/academia-x' => Http::response(null, 200)]);

        $this->assertTrue(PaymentApi::provider()->mercadoPagoDisconnect('academia-x'));
    }

    public function test_c6_public_key_returns_session(): void
    {
        Http::fake(['https://api.payment.test/api/v1/providers/c6/public-key*' => Http::response([
            'public_key'  => '-----BEGIN PUBLIC KEY-----...',
            'session_key' => 'sess_123',
            'expires_in'  => '600',
        ], 200)]);

        $session = PaymentApi::provider()->c6PublicKey('academia-x');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'provider_alias=academia-x'));
        $this->assertSame('sess_123', $session['session_key']);
        $this->assertArrayHasKey('public_key', $session);
    }

    public function test_mercado_pago_public_key_returns_key(): void
    {
        Http::fake(['https://api.payment.test/api/v1/providers/mercadopago/public-key*' => Http::response([
            'public_key' => 'APP_USR-public-key',
        ], 200)]);

        $result = PaymentApi::provider()->mercadoPagoPublicKey();

        $this->assertSame('APP_USR-public-key', $result['public_key']);
        $this->assertArrayNotHasKey('access_token', $result);
    }

    public function test_mercado_pago_public_key_sends_alias(): void
    {
        Http::fake(['https://api.payment.test/api/v1/providers/mercadopago/public-key*' => Http::response([
            'public_key' => 'gym_public_key',
        ], 200)]);

        $result = PaymentApi::provider()->mercadoPagoPublicKey('academia-x');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'provider_alias=academia-x'));
        $this->assertSame('gym_public_key', $result['public_key']);
    }

    public function test_mercado_pago_public_key_throws_on_http_error(): void
    {
        Http::fake(['https://api.payment.test/api/v1/providers/mercadopago/public-key*' => Http::response([
            'message' => 'MercadoPago public key not configured for this provider.',
        ], 422)]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        PaymentApi::provider()->mercadoPagoPublicKey();
    }
}

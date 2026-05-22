<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Tests\Feature;

use Am2tec\PaymentApiSdk\Facades\PaymentApi;
use Am2tec\PaymentApiSdk\Responses\ChargeResponse;
use Am2tec\PaymentApiSdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ChargeResourceTest extends TestCase
{
    private array $chargePayload = [
        'data' => [
            'id'           => 'charge_uuid_123',
            'status'       => 'pending',
            'checkout_url' => 'https://checkout.asaas.com/pay/abc',
            'pix_code'     => null,
            'amount'       => 15000,
        ],
    ];

    public function test_create_returns_typed_response(): void
    {
        Http::fake(['https://api.payment.test/api/v1/charges' => Http::response($this->chargePayload, 201)]);

        $charge = PaymentApi::charge()->create([
            'provider'     => 'asaas',
            'amount'       => 15000,
            'reference_id' => 'order_99',
            'callback_url' => 'https://app.test/webhooks/payment_api',
            'due_date'     => '2026-05-20',
            'billing_type' => 'PIX',
            'customer'     => ['name' => 'John', 'email' => 'john@test.com', 'cpf' => '123.456.789-00'],
        ]);

        $this->assertInstanceOf(ChargeResponse::class, $charge);
        $this->assertSame('charge_uuid_123', $charge->id);
        $this->assertSame('pending', $charge->status);
        $this->assertSame('https://checkout.asaas.com/pay/abc', $charge->checkoutUrl);
        $this->assertSame(15000, $charge->amountCents);
    }

    public function test_create_sends_bearer_token(): void
    {
        Http::fake(['*' => Http::response($this->chargePayload, 201)]);

        PaymentApi::charge()->create([
            'provider' => 'asaas', 'amount' => 15000, 'reference_id' => 'ref',
            'callback_url' => 'https://app.test/cb', 'due_date' => '2026-05-20',
            'billing_type' => 'PIX', 'customer' => [],
        ]);

        Http::assertSent(fn ($req) => $req->header('Authorization')[0] === 'Bearer test-bearer-token');
    }

    public function test_find_returns_typed_response(): void
    {
        Http::fake(['https://api.payment.test/api/v1/charges/charge_uuid_123' => Http::response($this->chargePayload, 200)]);

        $charge = PaymentApi::charge()->find('charge_uuid_123');

        $this->assertInstanceOf(ChargeResponse::class, $charge);
        $this->assertSame('charge_uuid_123', $charge->id);
    }

    public function test_cancel_returns_true_on_success(): void
    {
        Http::fake(['https://api.payment.test/api/v1/charges/charge_uuid_123' => Http::response(null, 204)]);

        $result = PaymentApi::charge()->cancel('charge_uuid_123');

        $this->assertTrue($result);
    }

    public function test_list_returns_array_of_typed_responses(): void
    {
        Http::fake([
            'https://api.payment.test/api/v1/charges' => Http::response([
                'data' => [
                    $this->chargePayload['data'],
                    array_merge($this->chargePayload['data'], ['id' => 'charge_uuid_456']),
                ],
            ], 200),
        ]);

        $charges = PaymentApi::charge()->list();

        $this->assertCount(2, $charges);
        $this->assertInstanceOf(ChargeResponse::class, $charges[0]);
        $this->assertSame('charge_uuid_456', $charges[1]->id);
    }

    public function test_capture_returns_typed_response(): void
    {
        $captured = array_merge($this->chargePayload['data'], ['status' => 'CONFIRMED']);
        Http::fake(['https://api.payment.test/api/v1/charges/charge_uuid_123/capture' => Http::response(['data' => $captured], 200)]);

        $charge = PaymentApi::charge()->capture('charge_uuid_123');

        $this->assertInstanceOf(ChargeResponse::class, $charge);
        $this->assertSame('CONFIRMED', $charge->status);
    }

    public function test_create_throws_on_http_error(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Unprocessable'], 422)]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        PaymentApi::charge()->create([
            'provider' => 'asaas', 'amount' => 0, 'reference_id' => 'bad',
            'callback_url' => '', 'due_date' => '', 'billing_type' => '', 'customer' => [],
        ]);
    }
}

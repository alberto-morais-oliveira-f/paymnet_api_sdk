<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Tests\Feature;

use Am2tec\PaymentApiSdk\Facades\PaymentApi;
use Am2tec\PaymentApiSdk\Responses\ExtensionRequestResponse;
use Am2tec\PaymentApiSdk\Responses\SubscriptionResponse;
use Am2tec\PaymentApiSdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class SubscriptionResourceTest extends TestCase
{
    private array $subscriptionPayload = [
        'data' => [
            'id'                       => 'sub_uuid_abc',
            'status'                   => 'pending',
            'provider_subscription_id' => 'sub_asaas_xyz',
            'reference_id'             => 'sub_1_42',
            'started_at'               => null,
            'cancelled_at'             => null,
        ],
    ];

    public function test_create_returns_typed_response(): void
    {
        Http::fake(['https://api.payment.test/api/v1/subscriptions' => Http::response($this->subscriptionPayload, 201)]);

        $sub = PaymentApi::subscription()->create([
            'plan_id'      => 'plan_uuid_001',
            'reference_id' => 'sub_1_42',
            'callback_url' => 'https://app.test/webhooks/payment_api',
            'customer'     => ['name' => 'Maria', 'email' => 'maria@test.com', 'cpf' => '987.654.321-00'],
        ]);

        $this->assertInstanceOf(SubscriptionResponse::class, $sub);
        $this->assertSame('sub_uuid_abc', $sub->id);
        $this->assertSame('pending', $sub->status);
        $this->assertSame('sub_asaas_xyz', $sub->providerSubscriptionId);
        $this->assertSame('sub_1_42', $sub->referenceId);
    }

    public function test_cancel_returns_cancelled_response(): void
    {
        $cancelledPayload = [
            'data' => array_merge($this->subscriptionPayload['data'], [
                'status'       => 'cancelled',
                'cancelled_at' => '2026-05-17T10:00:00.000000Z',
            ]),
        ];

        Http::fake(['https://api.payment.test/api/v1/subscriptions/sub_uuid_abc' => Http::response($cancelledPayload, 200)]);

        $sub = PaymentApi::subscription()->cancel('sub_uuid_abc');

        $this->assertInstanceOf(SubscriptionResponse::class, $sub);
        $this->assertSame('cancelled', $sub->status);
        $this->assertNotNull($sub->cancelledAt);
    }

    public function test_find_returns_typed_response(): void
    {
        Http::fake(['https://api.payment.test/api/v1/subscriptions/sub_uuid_abc' => Http::response($this->subscriptionPayload, 200)]);

        $sub = PaymentApi::subscription()->find('sub_uuid_abc');

        $this->assertInstanceOf(SubscriptionResponse::class, $sub);
        $this->assertSame('sub_uuid_abc', $sub->id);
        $this->assertSame('sub_asaas_xyz', $sub->providerSubscriptionId);
    }

    public function test_request_extension_returns_typed_response(): void
    {
        Http::fake([
            'https://api.payment.test/api/v1/subscriptions/sub_uuid_abc/extension-request' => Http::response([
                'id' => 'ext_req_uuid_001',
                'status' => 'pending',
                'message' => 'Pedido de extensão enviado com sucesso.',
            ], 201),
        ]);

        $result = PaymentApi::subscription()->requestExtension('sub_uuid_abc', 'Preciso de mais tempo.');

        $this->assertInstanceOf(ExtensionRequestResponse::class, $result);
        $this->assertSame('ext_req_uuid_001', $result->id);
        $this->assertSame('pending', $result->status);
    }

    public function test_request_extension_without_reason(): void
    {
        Http::fake([
            'https://api.payment.test/api/v1/subscriptions/sub_uuid_abc/extension-request' => Http::response([
                'id' => 'ext_req_uuid_002',
                'status' => 'pending',
                'message' => 'Pedido de extensão enviado com sucesso.',
            ], 201),
        ]);

        $result = PaymentApi::subscription()->requestExtension('sub_uuid_abc');

        $this->assertInstanceOf(ExtensionRequestResponse::class, $result);
        $this->assertSame('pending', $result->status);
    }

    public function test_list_returns_array_of_typed_responses(): void
    {
        Http::fake([
            'https://api.payment.test/api/v1/subscriptions' => Http::response([
                'data' => [
                    $this->subscriptionPayload['data'],
                    array_merge($this->subscriptionPayload['data'], ['id' => 'sub_uuid_def']),
                ],
            ], 200),
        ]);

        $subs = PaymentApi::subscription()->list();

        $this->assertCount(2, $subs);
        $this->assertInstanceOf(SubscriptionResponse::class, $subs[0]);
        $this->assertSame('sub_uuid_def', $subs[1]->id);
    }
}

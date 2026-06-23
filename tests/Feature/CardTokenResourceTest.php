<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Tests\Feature;

use Am2tec\PaymentApiSdk\Facades\PaymentApi;
use Am2tec\PaymentApiSdk\Responses\CardTokenResponse;
use Am2tec\PaymentApiSdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class CardTokenResourceTest extends TestCase
{
    private array $cardTokenPayload = [
        'data' => [
            [
                'id'             => 'ct_uuid_001',
                'provider'       => 'c6bank',
                'provider_alias' => null,
                'brand'          => 'VISA',
                'last4'          => '1111',
                'exp_month'      => '12',
                'exp_year'       => '2030',
                'is_default'     => true,
                'last_used_at'   => '2026-06-23T10:00:00.000000Z',
                'created_at'     => '2026-06-23T09:00:00.000000Z',
            ],
        ],
    ];

    public function test_list_returns_typed_responses_without_raw_token(): void
    {
        Http::fake(['https://api.payment.test/api/v1/card-tokens' => Http::response($this->cardTokenPayload, 200)]);

        $tokens = PaymentApi::cardToken()->list();

        $this->assertCount(1, $tokens);
        $this->assertInstanceOf(CardTokenResponse::class, $tokens[0]);
        $this->assertSame('ct_uuid_001', $tokens[0]->id);
        $this->assertSame('VISA', $tokens[0]->brand);
        $this->assertSame('1111', $tokens[0]->last4);
        $this->assertTrue($tokens[0]->isDefault);
    }

    public function test_delete_returns_true(): void
    {
        Http::fake(['https://api.payment.test/api/v1/card-tokens/ct_uuid_001' => Http::response(null, 204)]);

        $this->assertTrue(PaymentApi::cardToken()->delete('ct_uuid_001'));
    }
}

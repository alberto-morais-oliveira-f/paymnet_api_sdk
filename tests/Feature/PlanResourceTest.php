<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Tests\Feature;

use Am2tec\PaymentApiSdk\Facades\PaymentApi;
use Am2tec\PaymentApiSdk\Responses\PlanResponse;
use Am2tec\PaymentApiSdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class PlanResourceTest extends TestCase
{
    private array $planPayload = [
        'data' => [
            'id'           => 'plan_uuid_001',
            'name'         => 'Mensal Basic',
            'amount_cents' => 9900,
            'interval'     => 'monthly',
        ],
    ];

    public function test_create_returns_typed_response(): void
    {
        Http::fake(['https://api.payment.test/api/v1/plans' => Http::response($this->planPayload, 201)]);

        $plan = PaymentApi::plan()->create([
            'name'     => 'Mensal Basic',
            'amount'   => 9900,
            'interval' => 'monthly',
            'provider' => 'asaas',
        ]);

        $this->assertInstanceOf(PlanResponse::class, $plan);
        $this->assertSame('plan_uuid_001', $plan->id);
        $this->assertSame(9900, $plan->amountCents);
        $this->assertSame('monthly', $plan->interval);
    }

    public function test_delete_returns_true(): void
    {
        Http::fake(['https://api.payment.test/api/v1/plans/plan_uuid_001' => Http::response(null, 204)]);

        $this->assertTrue(PaymentApi::plan()->delete('plan_uuid_001'));
    }

    public function test_list_returns_typed_array(): void
    {
        Http::fake([
            'https://api.payment.test/api/v1/plans' => Http::response([
                'data' => [$this->planPayload['data']],
            ], 200),
        ]);

        $plans = PaymentApi::plan()->list();

        $this->assertCount(1, $plans);
        $this->assertInstanceOf(PlanResponse::class, $plans[0]);
    }
}

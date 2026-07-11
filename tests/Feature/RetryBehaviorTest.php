<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Tests\Feature;

use Am2tec\PaymentApiSdk\Facades\PaymentApi;
use Am2tec\PaymentApiSdk\Tests\TestCase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * O cliente só reenvia em falhas transientes (conexão/timeout ou 5xx da
 * payment_api). Um 4xx é determinístico — o gateway rejeitou o payload (ex.: MP
 * "payer and collector must be real or test users") — então reenviar apenas
 * multiplica a latência sem chance de sucesso e atrasa o erro real.
 */
class RetryBehaviorTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('payment-api.retry_times', 2);
        $app['config']->set('payment-api.retry_delay_ms', 0);
    }

    public function test_4xx_is_not_retried(): void
    {
        Http::fake([
            'https://api.payment.test/api/v1/subscriptions' => Http::response([
                'message' => 'O provedor de pagamento rejeitou a requisição.',
                'gateway' => ['status' => 400, 'message' => 'Both payer and collector must be real or test users'],
            ], 422),
        ]);

        try {
            PaymentApi::subscription()->create(['reference_id' => 'x', 'callback_url' => 'https://app.test/cb']);
            $this->fail('Esperava RequestException.');
        } catch (RequestException $e) {
            $this->assertSame(422, $e->response->status());
            $this->assertSame(
                'Both payer and collector must be real or test users',
                $e->response->json('gateway.message'),
            );
        }

        // Uma única tentativa — nenhum reenvio.
        Http::assertSentCount(1);
    }

    public function test_5xx_is_retried(): void
    {
        Http::fake([
            'https://api.payment.test/api/v1/subscriptions' => Http::response(['message' => 'erro interno'], 500),
        ]);

        try {
            PaymentApi::subscription()->create(['reference_id' => 'x', 'callback_url' => 'https://app.test/cb']);
            $this->fail('Esperava RequestException.');
        } catch (RequestException $e) {
            $this->assertSame(500, $e->response->status());
        }

        // retry_times define o total de tentativas: 2 → 1 inicial + 1 reenvio.
        Http::assertSentCount(2);
    }
}

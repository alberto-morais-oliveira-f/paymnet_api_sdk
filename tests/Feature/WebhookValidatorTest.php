<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Tests\Feature;

use Am2tec\PaymentApiSdk\Facades\PaymentApi;
use Am2tec\PaymentApiSdk\Tests\TestCase;
use Am2tec\PaymentApiSdk\Webhook\WebhookEvent;
use Illuminate\Http\Request;

class WebhookValidatorTest extends TestCase
{
    private string $secret = 'test-secret-exactly-32-chars!!!';

    private array $payload = [
        'event'        => 'payment.confirmed',
        'reference_id' => 'order_99',
        'amount_cents' => 15000,
        'paid_at'      => '2026-05-16',
        'provider'     => 'asaas',
        'charge_id'    => 'charge_uuid_123',
        'metadata'     => [],
    ];

    private function makeRequest(string $body, string $signature): Request
    {
        $request = Request::create('/webhooks/payment_api', 'POST', [], [], [], [], $body);
        $request->headers->set('X-Payment-Signature', $signature);
        $request->headers->set('Content-Type', 'application/json');

        return $request;
    }

    public function test_valid_signature_returns_webhook_event(): void
    {
        $body = json_encode($this->payload);
        $sig = hash_hmac('sha256', $body, $this->secret);
        $request = $this->makeRequest($body, $sig);

        $event = PaymentApi::webhook()->validate($request);

        $this->assertInstanceOf(WebhookEvent::class, $event);
        $this->assertSame('payment.confirmed', $event->event);
        $this->assertSame('order_99', $event->referenceId);
        $this->assertSame(15000, $event->amountCents);
        $this->assertSame('2026-05-16', $event->paidAt);
        $this->assertSame('asaas', $event->provider);
        $this->assertSame('charge_uuid_123', $event->chargeId);
    }

    public function test_invalid_signature_aborts_401(): void
    {
        $body = json_encode($this->payload);
        $request = $this->makeRequest($body, 'bad-signature');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        PaymentApi::webhook()->validate($request);
    }

    public function test_missing_signature_aborts_401(): void
    {
        $body = json_encode($this->payload);
        $request = Request::create('/webhooks/payment_api', 'POST', [], [], [], [], $body);
        $request->headers->set('Content-Type', 'application/json');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        PaymentApi::webhook()->validate($request);
    }

    public function test_tampered_body_aborts_401(): void
    {
        $body = json_encode($this->payload);
        $sig = hash_hmac('sha256', $body, $this->secret);

        $tampered = json_encode(array_merge($this->payload, ['amount_cents' => 1]));
        $request = $this->makeRequest($tampered, $sig);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        PaymentApi::webhook()->validate($request);
    }

    public function test_webhook_event_is_method(): void
    {
        $body = json_encode($this->payload);
        $sig = hash_hmac('sha256', $body, $this->secret);
        $event = PaymentApi::webhook()->validate($this->makeRequest($body, $sig));

        $this->assertTrue($event->is('payment.confirmed'));
        $this->assertFalse($event->is('payment.failed'));
    }
}

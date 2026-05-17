<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Webhook;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WebhookValidator
{
    public function __construct(private readonly string $secret) {}

    /**
     * Validate the inbound HMAC signature and return a typed event DTO.
     *
     * @throws HttpException 401 if signature is missing or invalid.
     */
    public function validate(Request $request): WebhookEvent
    {
        $signature = $request->header('X-Payment-Signature', '');
        $expected = hash_hmac('sha256', $request->getContent(), $this->secret);

        if (! hash_equals($expected, (string) $signature)) {
            abort(401, 'Invalid webhook signature.');
        }

        return WebhookEvent::fromPayload($request->json()->all());
    }
}

<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatePaymentApiWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('payment-api.webhook_secret', '');
        $signature = (string) $request->header('X-Payment-Signature', '');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}

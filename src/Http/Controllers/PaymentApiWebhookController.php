<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Http\Controllers;

use Am2tec\PaymentApiSdk\Facades\PaymentApi;
use Am2tec\PaymentApiSdk\Webhook\WebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

abstract class PaymentApiWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $event = PaymentApi::webhook()->validate($request);

        $this->handle($event);

        return response()->json(['ok' => true]);
    }

    abstract protected function handle(WebhookEvent $event): void;
}

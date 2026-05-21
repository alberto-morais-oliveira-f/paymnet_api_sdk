<?php

declare(strict_types=1);

namespace Am2tec\PaymentApiSdk\Resources;

use Am2tec\PaymentApiSdk\Responses\ExtensionRequestResponse;
use Am2tec\PaymentApiSdk\Responses\SubscriptionResponse;
use Illuminate\Http\Client\PendingRequest;

class SubscriptionResource
{
    public function __construct(private readonly PendingRequest $client) {}

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): SubscriptionResponse
    {
        $response = $this->client->post('/api/v1/subscriptions', $data);
        $response->throw();

        return SubscriptionResponse::fromArray($response->json('data'));
    }

    public function find(string $id): SubscriptionResponse
    {
        $response = $this->client->get("/api/v1/subscriptions/{$id}");
        $response->throw();

        return SubscriptionResponse::fromArray($response->json('data'));
    }

    public function cancel(string $id): SubscriptionResponse
    {
        $response = $this->client->delete("/api/v1/subscriptions/{$id}");
        $response->throw();

        return SubscriptionResponse::fromArray($response->json('data'));
    }

    public function requestExtension(string $id, ?string $reason = null): ExtensionRequestResponse
    {
        $response = $this->client->post("/api/v1/subscriptions/{$id}/extension-request", array_filter([
            'reason' => $reason,
        ]));
        $response->throw();

        return ExtensionRequestResponse::fromArray($response->json());
    }

    /**
     * @return SubscriptionResponse[]
     */
    public function list(): array
    {
        $response = $this->client->get('/api/v1/subscriptions');
        $response->throw();

        return array_map(
            fn (array $item) => SubscriptionResponse::fromArray($item),
            $response->json('data') ?? [],
        );
    }
}

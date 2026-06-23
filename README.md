# Payment API SDK

PHP SDK para integração com a Payment API (`am2tec/payment-api`).

## Instalação

```bash
composer require am2tec/payment-api-sdk
```

Publicar configuração:

```bash
php artisan vendor:publish --tag=payment-api-config
```

## Configuração

```env
PAYMENT_API_URL=https://api.seudominio.com
PAYMENT_API_KEY=sk_live_seu_token_aqui
PAYMENT_API_WEBHOOK_SECRET=seu_webhook_secret
PAYMENT_API_TIMEOUT=30
PAYMENT_API_RETRY_TIMES=3
PAYMENT_API_RETRY_DELAY_MS=500
```

---

## Uso

### Cobranças únicas

```php
use Am2tec\PaymentApiSdk\Facades\PaymentApi;

// Criar cobrança
$charge = PaymentApi::charge()->create([
    'reference_id'  => 'pedido_123',
    'callback_url'  => 'https://app.com/webhooks/payment',
    'amount'        => 9900, // centavos
    'billing_type'  => 'PIX', // ou CREDIT_CARD, BOLETO
    'customer'      => [
        'name'  => 'João Silva',
        'email' => 'joao@email.com',
        'cpf'   => '123.456.789-00',
    ],
]);

echo $charge->id;          // uuid
echo $charge->status;      // pending | confirmed | failed
echo $charge->checkoutUrl; // link de pagamento
echo $charge->pixCode;     // código PIX se billing_type=PIX

// Buscar
$charge = PaymentApi::charge()->find('uuid');

// Cancelar
$charge = PaymentApi::charge()->cancel('uuid');

// Reembolsar
PaymentApi::charge()->refund('uuid', ['amount' => 9900, 'reason' => 'Cancelamento']);

// Listar
$charges = PaymentApi::charge()->list(['status' => 'confirmed']);
```

### Cartão transparente (checkout transparente)

O cartão é criptografado no browser pelo SDK do gateway (PAN nunca chega ao servidor).
Envie o hash/token em `billing_type=CARD`:

```php
// Cobrança com cartão criptografado (hash do SDK) + salvar para reuso
$charge = PaymentApi::charge()->create([
    'provider'       => 'c6bank',
    'amount'         => 5000,
    'reference_id'   => 'pedido_card',
    'callback_url'   => 'https://app.com/webhooks/payment',
    'due_date'       => '2026-06-23',
    'billing_type'   => 'CARD',
    'encrypted_card' => $cardHashDoSdk, // OU 'card_token' => 'tok_salvo'
    'save_card'      => true,           // tokeniza p/ 1-clique / recorrência
    'authenticate'   => 'NOT_REQUIRED', // NOT_REQUIRED | OPTIONAL | REQUIRED
    'card_type'      => 'CREDIT',       // CREDIT | DEBIT
    'installments'   => 1,
    'customer'       => ['name' => 'João', 'email' => 'joao@email.com', 'cpf' => '123.456.789-00'],
]);

echo $charge->status;       // confirmed | failed | authorized
echo $charge->cardTokenId;  // uuid do cartão salvo (se save_card=true)
```

Para o checkout transparente do C6, busque a chave pública do SDK:

```php
$session = PaymentApi::provider()->c6PublicKey(); // ['public_key' => ..., 'session_key' => ..., 'expires_in' => ...]
```

### Cartões salvos (tokenizados)

```php
// Listar cartões salvos do tenant (nunca expõe o token cru)
$cards = PaymentApi::cardToken()->list();
foreach ($cards as $card) {
    echo "{$card->brand} •••• {$card->last4} ({$card->id})";
}

// Remover
PaymentApi::cardToken()->delete('ct_uuid');
```

### Assinaturas recorrentes

```php
// Criar plano
$plan = PaymentApi::plan()->create([
    'name'     => 'Plano Mensal',
    'amount'   => 9900,
    'interval' => 'monthly', // monthly | yearly | weekly
    'provider' => 'asaas',
]);

// Criar assinatura
$sub = PaymentApi::subscription()->create([
    'plan_id'      => $plan->id,
    'reference_id' => 'cliente_456',
    'callback_url' => 'https://app.com/webhooks/payment',
    'customer'     => [
        'name'  => 'Maria Santos',
        'email' => 'maria@email.com',
        'cpf'   => '987.654.321-00',
    ],
]);

echo $sub->id;             // uuid
echo $sub->status;         // pending | active | trial | paused | cancelled
echo $sub->trialEndsAt;    // ISO8601 ou null
echo $sub->nextBillingDate;// YYYY-MM-DD ou null

// Assinatura cobrada por cartão salvo (gateways sem assinatura nativa, ex.: C6 —
// a recorrência é cobrada localmente reutilizando o cartão tokenizado)
$sub = PaymentApi::subscription()->create([
    'provider'      => 'c6bank',
    'amount_cents'  => 9900,
    'interval'      => 'monthly',
    'reference_id'  => 'aluno_789',
    'callback_url'  => 'https://app.com/webhooks/payment',
    'card_token_id' => $charge->cardTokenId, // OU 'card_token' => 'tok'
    'customer'      => ['name' => 'Maria', 'email' => 'maria@email.com', 'cpf' => '987.654.321-00'],
]);

// Buscar / cancelar / listar
$sub  = PaymentApi::subscription()->find('uuid');
$sub  = PaymentApi::subscription()->cancel('uuid');
$subs = PaymentApi::subscription()->list();

// Solicitar extensão de trial (enviado para fila de aprovação do admin)
$req = PaymentApi::subscription()->requestExtension(
    id: $sub->id,
    reason: 'Precisamos de mais tempo para avaliar o produto.' // opcional
);

echo $req->id;      // uuid do pedido
echo $req->status;  // pending
echo $req->message; // confirmação textual
```

### Gateways de pagamento

```php
// Configurar Asaas
PaymentApi::provider()->store('asaas', [
    'api_key'  => 'sua_chave_asaas',
    'base_url' => 'https://sandbox.asaas.com/api/v3',
]);

// Listar providers ativos
$providers = PaymentApi::provider()->list();

// Capabilities do gateway (renderizar o checkout correto no front)
$caps = PaymentApi::provider()->capabilities('c6bank');
// ['pix'=>true, 'transparent_card'=>true, 'tokenization_js'=>'c6', 'native_subscriptions'=>false, ...]
```

### Webhooks

Configure a rota no seu `routes/web.php`:

```php
use App\Http\Controllers\PaymentWebhookController;

Route::post('/webhooks/payment', PaymentWebhookController::class)
    ->middleware('payment-api.webhook'); // valida HMAC-SHA256
```

Crie o controller estendendo a base:

```php
use Am2tec\PaymentApiSdk\Http\Controllers\PaymentApiWebhookController;
use Am2tec\PaymentApiSdk\Webhook\WebhookEvent;

class PaymentWebhookController extends PaymentApiWebhookController
{
    public function handle(WebhookEvent $event): void
    {
        if ($event->is('PAYMENT_RECEIVED')) {
            // cobrança confirmada
            $referenceId  = $event->referenceId;
            $amountCents  = $event->amountCents;
            $paidAt       = $event->paidAt;
        }

        if ($event->is('PAYMENT_OVERDUE')) {
            // cobrança vencida
        }
    }
}
```

Validação manual (sem middleware):

```php
$event = PaymentApi::webhook()->validate(
    payload: $request->getContent(),
    signature: $request->header('X-Payment-Signature'),
);
```

---

## Respostas

### `ChargeResponse`

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | `string` | UUID da cobrança |
| `status` | `string` | `pending` \| `authorized` \| `confirmed` \| `failed` |
| `providerAlias` | `?string` | Conta do gateway (multi-conta) |
| `checkoutUrl` | `?string` | Link de pagamento |
| `pixCode` | `?string` | Copia-e-cola PIX |
| `amountCents` | `?int` | Valor em centavos |
| `cardTokenId` | `?string` | UUID do cartão salvo (se `save_card=true`) |

### `CardTokenResponse`

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | `string` | UUID do cartão salvo |
| `provider` | `string` | Gateway de origem |
| `brand` | `?string` | Bandeira (VISA, ...) |
| `last4` | `?string` | Últimos 4 dígitos |
| `expMonth` / `expYear` | `?string` | Validade |
| `isDefault` | `bool` | Cartão padrão |
| `lastUsedAt` | `?string` | ISO8601 |

### `SubscriptionResponse`

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | `string` | UUID da assinatura |
| `status` | `string` | `pending` \| `active` \| `trial` \| `paused` \| `dunning` \| `past_due` \| `cancelled` |
| `providerSubscriptionId` | `?string` | ID externo no gateway |
| `referenceId` | `?string` | Seu ID de referência |
| `startedAt` | `?string` | ISO8601 |
| `cancelledAt` | `?string` | ISO8601 |
| `trialEndsAt` | `?string` | ISO8601 — fim do trial |
| `pausedAt` | `?string` | ISO8601 — quando foi pausada |
| `nextBillingDate` | `?string` | YYYY-MM-DD — próxima cobrança |

### `ExtensionRequestResponse`

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | `string` | UUID do pedido |
| `status` | `string` | `pending` — aguarda análise admin |
| `message` | `string` | Confirmação textual |

> **Erro 422:** já existe pedido pendente para esta assinatura. Aguarde aprovação ou negação antes de criar novo.

---

## Fluxo de Trial

```
SDK: subscription()->create()         → status = pending
API: webhook SUBSCRIPTION_ACTIVATED   → status = active ou trial
SDK: subscription()->requestExtension() → cria pedido na fila do admin
Admin: aprova → trial_ends_at += N dias
Admin: nega   → pedido.status = denied
```

---

## Tratamento de Erros

O SDK lança `Illuminate\Http\Client\RequestException` em respostas 4xx/5xx.

```php
use Illuminate\Http\Client\RequestException;

try {
    $sub = PaymentApi::subscription()->cancel('uuid');
} catch (RequestException $e) {
    $status = $e->response->status(); // 404, 422, etc.
    $body   = $e->response->json();
}
```

---

## Testes

```bash
vendor/bin/phpunit
```

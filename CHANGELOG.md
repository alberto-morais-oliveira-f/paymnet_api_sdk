# Changelog

All notable changes to this project are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.5.0] - 2026-06-23

### Added
- `ChargeResponse`: campos de split — `split` (snapshot recipient/fee), `platformFeeCents`,
  `splitStatus` — e `boletoCode`.
- `ProviderResource`: onboarding MercadoPago (marketplace) — `mercadoPagoAuthorizeUrl()`,
  `mercadoPagoSellerStatus()` (inclui `public_key` p/ MercadoPago.js) e `mercadoPagoDisconnect()`.

## [1.4.1] - 2026-06-23

### Docs
- README: nota sobre `billing_type` por provider (PIX/BOLETO/CREDIT_CARD/CARD) e como
  descobrir o suportado via `provider()->capabilities()`.

## [1.4.0] - 2026-06-23

### Added
- `CardTokenResource` (`PaymentApi::cardToken()`) with `list()` and `delete()` for saved cards (one-click charges / local recurrence). Never exposes the raw gateway token.
- `CardTokenResponse` DTO.
- `ProviderResource::capabilities(string $provider)` — fetches provider capability flags (`transparent_card`, `tokenization_js`, `native_subscriptions`, ...).
- `ProviderResource::c6PublicKey(?string $alias)` — C6 transparent-checkout SDK session (public key + session key).
- `cardTokenId` field on `ChargeResponse`, mapped from the API's `card_token_id`.

### Notes
- Transparent card charges and card-backed subscriptions need no new methods: pass the
  new fields (`billing_type=CARD`, `encrypted_card`/`card_token`, `save_card`,
  `authenticate`, `card_type`, `installments`; subscription `card_token_id`/`card_token`)
  straight into `charge()->create()` / `subscription()->create()` — they are forwarded as-is.

## [1.3.0] - 2026-06-22

### Added
- Optional `alias` argument on `ProviderResource::store()`, sent to the API when provided.
- `alias` field on `ProviderResponse`.

## [1.2.0] - 2026-06-21

### Added
- `capture()` method on the charge resource for capturing pre-authorized payments.
- `providerAlias` field on `ChargeResponse` and `SubscriptionResponse`.
- `checkoutUrl` field on `SubscriptionResponse`, mapped from the API's `checkout_url` payload (e.g. Mercado Pago init link).

## [1.1.0] - 2026-05-20

### Added
- Subscription extension request support.
- Trial and pause fields on the subscription resource.

[1.4.1]: https://github.com/alberto-morais-oliveira-f/paymnet_api_sdk/compare/v1.4.0...v1.4.1
[1.4.0]: https://github.com/alberto-morais-oliveira-f/paymnet_api_sdk/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/alberto-morais-oliveira-f/paymnet_api_sdk/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/alberto-morais-oliveira-f/paymnet_api_sdk/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/alberto-morais-oliveira-f/paymnet_api_sdk/releases/tag/v1.1.0

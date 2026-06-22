# Changelog

All notable changes to this project are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

[1.3.0]: https://github.com/alberto-morais-oliveira-f/paymnet_api_sdk/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/alberto-morais-oliveira-f/paymnet_api_sdk/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/alberto-morais-oliveira-f/paymnet_api_sdk/releases/tag/v1.1.0

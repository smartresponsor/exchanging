# Exchanging entity-first migration retirement

## Scope

This pass retires Exchanging schema-first migrations as the source of truth and keeps the canonical model in PHP Doctrine entities.

## Retired schema-first sources

- `Exchanging/migrations/Version20260503080000.php`
- `Exchanging/migrations/Version20260518090000.php`

## Entity coverage

The retired migrations are covered by existing entity-first models:

- `exchange_exchange` -> `App\Exchanging\Entity\ExchangeEntity`
- `exchange_rate` -> `App\Exchanging\Entity\ExchangeRateEntity`

The second migration converted UUID IDs to integer primary keys; the current entities already use integer generated IDs, so no additional field migration was required.

## Legacy monolith reconciliation

Old `Entity-src(6).zip` contained:

- `Entity/Exchange/Exchange.php`
- `Entity/Exchange/ExchangeRate.php`
- `EntityInterface/Exchange/ExchangeInterface.php`
- `EntityInterface/Exchange/ExchangeRateInterface.php`

The old model linked exchange records directly to Currency entities. In the componentized Exchanging boundary, these links are represented as ISO currency-code boundary references instead of cross-component Doctrine relations:

- `baseCurrencyCode` / `sourceCurrencyReference()`
- `quoteCurrencyCode` / `targetCurrencyReference()`
- legacy `ExchangeRateInterface::getRatio()` maps to `ExchangeRate::rateValue()`

## Objecting decision

The current model delegates generic object audit state for `Exchange` to Objecting via `ObjectAuditEmbeddableTrait`: `created_at`, `modified_at`, `created_by`, and `modified_by` are platform audit fields. `ExchangeRate.capturedAtImmutable` remains owned by Exchanging because it records the domain-specific instant at which a rate snapshot was captured.

## Repository contracts

Repository interfaces were added for the two entity-first repositories:

- `ExchangeRepositoryInterface`
- `ExchangeRateRepositoryInterface`

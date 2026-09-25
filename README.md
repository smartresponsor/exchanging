# Exchanging

Exchanging is the currency conversion and exchange rate management component of the multi-domain SaaS platform. It retrieves live/historical rates from providers (such as the National Bank of Ukraine - NBU), caches rate maps, and produces conversion quotes.

This module is **not** a payment gateway, currency settlement network, or multi-currency wallet manager. It handles rate lookup, caching, and currency conversion arithmetic.

## Current Posture

### What the component already does
- Integrates with external currency exchange rate providers (NBU integration).
- Stores historical rates and maintains local rate matrices.
- Computes currency conversion quotes.
- Implements a dual-mode runtime environment with strict configuration isolation.
- Fully compatible with Symfony 8 and DBAL 4.

### What this repository does not claim yet
- Managing merchant ledgers or funds transfers.
- Real-time arbitrage calculations.

## Runtime Surface & Entrypoints

The component exposes CLI tools, services, and hooks:
- `src/Service/` - Exchange rate providers, historical parsers, and conversion calculators.
- `src/Command/` - Console commands for updating local exchange rates.
- `App\Exchanging\ExchangingBundle` - Bundle DI wiring.

## Local Setup

Install dependencies:
```bash
composer install
```

## Local Composer Path Installation

To require Exchanging as a path repository within your Symfony application:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../Exchanging",
      "options": {
        "symlink": true
      }
    }
  ],
  "require": {
    "exchanging/exchange": "*@dev"
  }
}
```

## Documentation Map

- [Documentation Index](docs/index.adoc)
- [Output Template Context Contract](docs/contract/exchanging-output-template-context-contract.adoc)
- [Interfacing Bridge Contract](docs/bridge/exchanging-interfacing-bridge-contract.adoc)
- [Dual-Mode Runtime Guide](docs/runtime/exchanging-dual-mode-runtime.adoc)

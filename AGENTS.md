= Exchanging Agent Guide

== First files to read

. `README.adoc`
. `docs/index.adoc`
. `docs/manifest/exchanging-component-manifest.json`
. `docs/canon/exchanging-canon-manifest.json`
. `tools/gate/exchanging-rc-gate.ps1`

== Canon

* namespace: `App\Exchanging`
* package: `exchanging/exchange`
* table prefix: `exchange_`
* no `/src/Domain/`
* no ports-and-adapters architecture

== Patch policy

Use touched-file overlays. Do not delete or rewrite the repository wholesale.

== Component boundary

Exchanging owns exchange rates, quotes, provider fetch/capture, freshness policy, and applied-rate audit payload construction.

Exchanging does not own currency metadata, pricing, orders, payments, or tax calculation.

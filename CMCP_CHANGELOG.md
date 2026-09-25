# CMCP_CHANGELOG

## 2026-09-24 — Canon052 consumer-artifact and documentation closure

### Reconnaissance baseline

- Re-read Exchanging agent guidance, repository READMEs, Composer manifests, component/canon manifests, contract docs, RC gate, quality configuration, and the existing CMCP journal.
- Re-read the authoritative Canonization material required by the current findings, including Canon018, Canon052, and Canon053, plus the current Canonization agent projection.
- Re-read the mandatory dependency contour for Objecting, Cruding, Viewing, and Interfacing and the Gating owner contract; verified Exchanging's development Composer dependency/path wiring remains present for Objecting, Cruding, Viewing, and Interfacing.
- Market benchmark reconfirmed that mature FX systems preserve source/provenance, publication timestamps, historical snapshots, deterministic decimal behavior, and provider validation/fallback semantics while leaving settlement, wallets, pricing, tax, and currency metadata outside the Exchanging boundary.
- Initial Gating run passed the canonical topology, identity, dependency, schema-parity, coverage, and sibling-symlink rules but failed Canon052 because consumer `.gating/` contained a copied Gating owner repository.

### Target-to-canon mapping

- Canon018: `exchanging/exchange` maps to `App\\Exchanging\\` and `Exchange*`; current PHP source satisfies this.
- Canon052: consumer `.gating/` is artifact-only. The copied owner tree was preserved outside the source surface under ignored `var/cmcp-preserved-gating-owner-copy/`, and `.gating/README.md` was rebuilt as a non-executable artifact-boundary note.
- Canon053: Exchanging is not the App host; its sibling symlink contour remains limited to canonical helper/foundation exceptions and currently passes Gating.
- Documentation/runtime parity: component manifest and neighbor-contract documentation were synchronized to the already-canonical flat `DTO`, `ProviderInterface`, and `ServiceInterface` topology.

### Selected work

- RC-critical: close the hard Canon052 failure without destroying unrelated local state, remove stale documentation references left by the topology migration, then rerun gate and quality verification.
- Growth (post-RC): deterministic multi-provider priority/failover, triangulation, explicit manual/commercial spread semantics, provider observability/benchmarking, and richer applied-rate provenance UX.

### Material risks

- The repository still has a largely untracked initial Git baseline. No broad staging or fabricated initial commit is permitted without a trustworthy repository-history boundary.
- The preserved Gating owner copy under `var/` is intentionally non-source, non-normative local state and must not be promoted into Git.

### Verification and acceptance

- `composer validate --strict --check-lock`: PASS.
- `composer validate:prod`: PASS.
- `composer audit`: PASS, no security advisories.
- `composer cs:check`: PASS after the repository formatter corrected ten deterministic import-order/line-ending drifts.
- `composer phpstan`: PASS, 0 errors.
- `composer test`: PASS, 28 tests / 202 assertions.
- `composer test:coverage`: PASS; refreshed Canon040 evidence is lines 95.9%, methods 82.5%, branches 87.2%.
- `composer test:behavioral-coverage`: PASS; Canon042 evidence is functional 5/5, behavioral 2/2, UI 1/1, critical 1/1.
- `npm test`: PASS, Playwright 1/1 against the standalone health endpoint.
- `npm audit --audit-level=high`: PASS, 0 vulnerabilities.
- `composer schema:parity`: PASS against a disposable database; mapping and physical schema are in sync after migrations.
- `composer schema:validate`: PASS for Doctrine mapping.
- Symfony `lint:container`: PASS; `lint:yaml config --parse-tags`: PASS for 13 YAML files.
- `exchanging:health` and `exchanging:status`: PASS.
- Final `composer gate`: PASS with 0 failures. Canon031 remains the sole warning because meaningful PHPDoc coverage is below its 70% advisory threshold; it is intentionally not masked with generated boilerplate.
- RC diagnostic: green with no readiness blockers.
- Git integration is not safe to fabricate: current `master` has no upstream or configured remote and the worktree contains a pre-existing largely untracked repository baseline. No broad staging, commit, push, PR, or merge was performed.

### RC disposition

The implemented Exchanging RC-critical work is complete for the current local repository state. Hard canonical gates, functional/runtime verification, Doctrine parity, executable coverage, behavioral/UI evidence, and dependency/security validation pass. Remaining Canon031 PHPDoc coverage is documented non-blocking semantic documentation debt; provider failover, triangulation, spread/manual-rate maturity, and richer provenance observability remain post-RC growth work.

## 2026-09-23 — Current Canonization RC pass

### Reconnaissance baseline

- Read Exchanging AGENTS/README/README.adoc, component/canon manifests, RC gate, Composer manifests, source inventory, tests/config references, and the existing CMCP journal.
- Read required dependency contracts from Objecting, Cruding, Viewing, and Interfacing and verified direct development dependencies/path-symlink wiring in composer.json.
- Read Gating as executable enforcement companion and the materialized Canonization rules used for this pass: Canon001, Canon003, Canon004, Canon006, Canon009, Canon010, Canon018, Canon019, Canon020, Canon022, Canon025, Canon026, Canon030, Canon031, Canon032, Canon033, Canon038, Canon039, and Canon041.
- Market benchmark: mature FX layers emphasize provider abstraction, caching/freshness, historical rates, deterministic fallback, precise decimal arithmetic, provenance, and later triangulation; payment settlement, ledgers, pricing, tax, and currency metadata remain outside Exchanging.
- Initial gates: Composer validate, CS, PHPStan, and PHPUnit passed (28 tests / 202 assertions). Gating initially could not start because gating/gate was locked but not installed; composer install restored the local path package and vendor binary.
- Current Gating findings identify canonical topology debt: DTO casing, premature Exchange subject folders, Provider/Normalizer placement, subject-prefix drift, Symfony contracts declaration, Doctrine schema-parity tooling, and PHPDoc coverage warning.

### Target-to-canon mapping

- Canon001/004: flatten redundant src/<Role>/Exchange trees; keep technical role first and do not add filler folders.
- Canon003: use src/DTO and DTO terminal suffix/casing.
- Canon006/020: move Provider and Normalizer types to their dominant role roots, with mirrored interface roots where contracts exist.
- Canon018: composer identity exchanging/exchange maps to App\\Exchanging and Exchange* subject vocabulary; rename non-prefixed component-owned PHP types.
- Canon009/010/019: preserve standalone boundary, update all callers/config/tests/docs with the migration, and introduce no Domain/Application/Infrastructure/Port/Adapter taxonomy.
- Canon022/025/032/033: preserve the existing standalone + bundle runtime and development/production identity parity.
- Canon026: PHP 8.4 / Symfony >=8.1 <9; remove the unnecessary direct symfony/http-client-contracts declaration rather than weaken the baseline.
- Canon030: add an executable Doctrine schema-parity contract consistent with current Entity metadata ownership.
- Canon038/039/041: preserve subject-prefixed component YAML and the existing PHPUnit/Panther/Playwright tooling contract.

### Selected work

- RC-critical: complete the current Canonization topology/dependency/schema-verification migration, keep exact decimal FX behavior intact, rerun all executable gates, and integrate the resulting repository state.
- Growth (post-RC): deterministic multi-provider priority/failover policy, triangulation/cross-rate strategy, richer manual/commercial spread semantics, provider benchmark/observability, and expanded provenance UX.

### Material risks

- This is a cross-tree rename/move wave; every PHP/config/test/doc reference must move atomically to avoid a parallel old/new model.
- Existing .gating content contains an accidental copy of the Gating owner repository; only the tracked artifact README is canonical for Exchanging and generated artifact state must remain non-normative.
- Doctrine parity must not move persistence ownership out of Exchanging or invent payment/currency metadata responsibilities.

## 2026-09-14 — RC hardening baseline

### Reconnaissance read

- `AGENTS.md`, `README.md`, `README.adoc`, `composer.json`.
- Exchanging runtime/bootstrap, DI, route, Doctrine/provider config, API/OpenAPI, schema, health/status/read contracts, component/canon manifests, and local RC gate.
- Canonization normative rules: Canon001, Canon009, Canon010, Canon018, Canon019, Canon020, Canon022, Canon025, Canon026, Canon032, Canon033, Canon038, Canon039, Canon041.
- Gating root `AGENTS.md`, `README.md`, and `composer.json` as executable-enforcement reference.
- Required dependency contour: Objecting, Cruding, Viewing, Interfacing root `AGENTS.md`, `README.md`, and `composer.json`; Objecting audit embeddable/interface implementation.
- Local codebase-memory presence was confirmed under `.codebase-memory/`.
- Market/competitor reconnaissance covered enterprise FX APIs and mature provider-neutral PHP exchange-rate tooling.

### Current repository state

- Workspace is not currently a Git worktree (`git status` reports no `.git`). No Git initialization is performed because repository identity/history must not be invented.
- Exchanging is a dual-mode Symfony component: reusable bundle plus standalone runtime (`bin/console`, `config/bundles.php`, runtime kernel).
- `composer validate --strict --check-lock` fails because `composer.lock` is stale and still resolves Symfony 8.0.x while the manifest already requires several Symfony 8.1 packages.
- Composer platform dependencies are inconsistent: several Symfony packages still allow Symfony 7, contrary to Canon026 and repository documentation.
- Canon022 standalone dependency baseline is incomplete: Cruding, Collectioning, Tabling, Viewing, Interfacing, and EasyAdmin are not direct runtime dependencies.
- Canon039/Canon041 root testing contracts are incomplete.
- Quote multiplication falls back to binary floating-point when `ext-bcmath` is unavailable, which is not acceptable for deterministic FX arithmetic.
- `Exchange.createdAtImmutable` is generic creation audit state despite an older local document classifying it as domain-specific; this conflicts with the current Objecting audit-field canon. `ExchangeRate.capturedAtImmutable` remains a domain capture fact.

### RC-critical work selected

1. Normalize Composer to PHP 8.4 / Symfony 8.1+ and the canonical standalone dependency contour.
2. Replace float-based quote arithmetic with deterministic decimal arithmetic and add precision regression tests.
3. Materialize executable PHPUnit/coverage and browser-test tooling contracts required by Canon039/Canon041 without expanding Exchanging business responsibility.
4. Normalize generic creation audit state to Objecting while preserving capture-specific timestamps.
5. Synchronize repository docs/manifests with the implemented architecture and re-run Composer/runtime/test/security gates.

### Material risks

- Dependency graph expansion may expose incompatible local sibling constraints; resolve only inside Exchanging manifest/lock unless a sibling defect is proven and explicitly in scope.
- Objecting audit normalization changes Doctrine column shape from `created_at_immutable` to canonical `created_at`; current project policy is entity-first, so no historical migration is invented here.
- Browser tooling may be installable while no meaningful UI workflow exists in Exchanging; the contract should remain a minimal runtime-health proof rather than presentation ownership.

### RC implementation outcome

- Composer normalized to PHP 8.4 / Symfony 8.1+, canonical standalone dependencies, path-repository sibling wiring, exact `brick/math` decimal arithmetic, PHPUnit/PHPStan/Panther test tooling, and a synchronized lock file.
- Repaired quote-path DTO contract violations across service, HTTP controller, CLI command, and Interfacing template-context bridge.
- Removed the Host-only fixture base dependency and made demo fixtures deterministic.
- Adopted Objecting `object_audit` for `Exchange`; `capturedAtImmutable` remains Exchanging-owned domain capture state. Added the Objecting Doctrine consumer contract and runtime/host metadata mapping.
- Canon038 component-owned config files now use the `exchange_` subject prefix; runtime, bundle extension, gates, current integration docs, Jira delivery instructions, NBU helpers, and proof tooling use the new paths.
- Canon039 is executable through PHPUnit 12 with persistent path/branch coverage summary at `var/coverage/summary.txt`.
- Canon041 is executable through repository-local Playwright; a real Chrome health smoke test is present under `tests/browser/`.
- Health diagnostics now perform provider inventory, freshness-policy, and operational-identity probes instead of tautological typed `instanceof` checks.

### Final verification

- `composer validate --strict --check-lock`: PASS.
- `composer audit`: PASS, no vulnerability advisories.
- `composer test`: PASS, 3 tests / 36 assertions.
- `composer test:coverage`: PASS with Xdebug 3.5.1; persistent summary includes classes, methods, paths, branches, and lines.
- `composer phpstan`: PASS, level 8, 0 errors.
- `npm ci --ignore-scripts`: PASS from `package-lock.json`.
- `npm test`: PASS, 1 Playwright/Chrome browser test.
- `npm audit --audit-level=moderate`: PASS, 0 vulnerabilities.
- `bin/console cache:clear`, `list exchanging`, `exchanging:status`, `exchanging:health`, and `lint:container`: PASS.
- `lint:yaml config --parse-tags`: PASS, all 12 YAML files valid.
- `doctrine:schema:validate --skip-sync`: PASS; full validation reports only that the existing generated local SQLite dev database predates the new Objecting audit columns. Doctrine mapping itself is valid. The repository's entity-first policy intentionally does not reintroduce schema-first migrations.
- Local `tools/gate/exchanging-rc-gate.ps1` was synchronized but direct execution remains blocked by Console MCP policy because scripts are executable only from `tool/` or `bin/`; equivalent constituent gates above were executed directly.
- Workspace still has no `.git`; Git initialization, commit, push, PR, and merge are therefore intentionally not fabricated.

### RC disposition

Source/package RC criteria are satisfied. Remaining items are environment/integration concerns only: production provider certification, host deployment proof, and regeneration of the disposable local standalone SQLite schema when a fresh local database is needed. Growth work remains post-RC.

## 2026-09-20 — RC-critical coverage and canon closure

### What changed

- Added `tests/ExchangeCriticalServicesTest.php` covering freshness boundary/stale rejection, rate-capture validation and aggregation, deterministic applied-rate audit, local provider fallback, and remote provider selection/failure.
- Canon001 bootstrap structure normalized from `src/ExchangingRuntimeKernel.php` to conventional `src/Kernel.php`; `bin/console`, `public/index.php`, runtime docs, and historical path references were synchronized.
- Canon038 runtime configuration normalized to `config/packages/exchange_doctrine_runtime.yaml` and `config/services/exchange_output_contracts.yaml`.
- Generic CRUD scan found no Exchanging-owned generic CRUD controllers/routes; matches exist only inside the read-only local Gating mirror.
- No sibling component was modified.

### Verification

- PHPUnit: PASS — 12 tests / 70 assertions.
- PHPStan: PASS — 0 errors.
- PHP CS Fixer dry-run: PASS — 0 fixable files.
- Composer validate strict/check-lock: PASS.
- Composer audit: PASS — no advisories.
- Symfony container lint and YAML lint: PASS; 12 YAML files valid.
- `exchanging:health`, `exchanging:status`, router inspection, and Doctrine mapping validation: PASS.
- Playwright standalone health smoke: PASS — 1/1.
- npm audit high: PASS — 0 vulnerabilities.
- Coverage after RC-critical tests: Classes 13.16%, Methods 15.00%, Paths 23.68%, Branches 70.69%, Lines 15.59%.
- Compared with the prior baseline, branch coverage rose from 54.24% to 70.69% and line coverage from 4.23% to 15.59%.

### RC disposition

The selected option started as RC-critical coverage and was subsequently extended to close the full Canon040 thresholds.

## 2026-09-20 — Canon040 coverage closure

- Added controller/console surface coverage across success and failure behavior.
- Added real standalone Symfony + Doctrine SQLite in-memory integration coverage for capture -> persist -> read -> quote, HTTP routes, repositories, and entity lifecycle behavior.
- Added provider/service coverage for static, manual, HTTP JSON, NBU normalization/provider behavior, remote fetch, fetch-and-capture orchestration, demo seeding, template context, neighbor hooks, and exchange-rate lifecycle policy.
- Final PHPUnit result: 28 tests / 202 assertions, PASS.
- Final Canon040 metrics: Lines 94.59% (1050/1110), Methods 81.00% (162/200), Branches 87.28% (453/519).
- Canon040 thresholds (Lines >=80%, Methods >=80%, Branches >=70%) are now satisfied.
- PHPStan: PASS, 0 errors.
- PHP CS Fixer dry-run: PASS, 0 fixable files.

### Git disposition

The workspace is now a Git repository on `master`, but it has no commits, no upstream, and no configured remote. The entire repository tree is still untracked (26 top-level entries). An initial commit is intentionally not fabricated because there is no established tracked baseline proving whether local support trees such as `.gating/` belong in the first commit; push/PR/merge are impossible without a remote.


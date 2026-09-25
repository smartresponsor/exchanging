# Exchanging Gating Artifacts

This directory is an artifact-only consumer surface for Gating.

Generated reports, evidence, cache data, checksums, and other non-normative artifacts may be written here.
Executable policy, canonical rules, Symfony configuration, Composer metadata, source code, and copied Gating owner trees do not belong here.

The executable gate is provided by the `gating/gate` Composer development dependency and is invoked through:

```text
composer gate
```

The normative architecture rules are owned by the sibling `Canonization` repository; executable enforcement is owned by `Gating`.

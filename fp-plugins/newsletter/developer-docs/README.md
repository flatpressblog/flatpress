# Newsletter Plugin Developer Documentation

This directory documents implementation-level behavior for the FlatPress Newsletter plugin.

It is written for maintainers who need to change subscription, confirmation, dispatch, validation, DNS-cache, or storage logic without introducing duplicate subscribers, broken double opt-in flows, or incompatible Smarty/PHP behavior.

## Reading order

1. [`01-Double-Opt-In-Deduplication.md`](01-Double-Opt-In-Deduplication.md) — duplicate-prevention model for `pending.txt` and `subscribers.txt`.
2. [`02-State-And-Events.md`](02-State-And-Events.md) — state files, event triggers, and lifecycle overview.
3. [`03-Email-Validation-And-DNS-Cache.md`](04-Email-Validation-And-DNS-Cache.md) — EAI-aware validation, IDN/domain normalization, and DNS-cache cleanup.
4. [`04-Request-Ordering-And-Blocklist-Bootstrap.md`](05-Request-Ordering-And-Blocklist-Bootstrap.md) — request-order guarantees for blocklist bootstrap and admin send-now CSRF.
5. [`05-Regression-Test-Matrix.md`](03-Regression-Test-Matrix.md) — simulation coverage and manual checks.
6. [`06-Simulation-Harness-And-Coverage.md`](06-Simulation-Harness-And-Coverage.md) — regression-harness design, child-process request simulation, and current coverage limits.

## Compatibility target

The plugin code should remain compatible with:

- PHP 7.2 through PHP 8.5.
- Smarty 5.8.0 for the admin template.
- FlatPress flat-file installations on shared hosting, including hosts without cron access or restricted DNS APIs.

## Regression harness

The dedicated simulation script lives in the blog root:

```bash
php simulate_newsletter_plugin.php
```

The script boots a minimal FlatPress-like environment, includes the real plugin file, and tests the actual helper functions and isolated request-order behavior used by subscription, confirmation, DNS-cache cleanup, EAI-aware e-mail validation, blocklist bootstrap, admin send-now CSRF handling, front-end abuse protection, unsubscribe handling, blocklist monthly cleanup, header sanitizing, and invalid-recipient cleanup during dispatch.

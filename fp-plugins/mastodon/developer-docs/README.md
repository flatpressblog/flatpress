# Mastodon Plugin Developer Documentation

This directory is an implementation-oriented documentation set for the FlatPress Mastodon plugin.

It is written for developers who need to make safe changes without breaking synchronization state, Mastodon API compatibility, or regression coverage.

## Reading order

1. [`00-Mental-Model.md`](00-Mental-Model.md) — one-page model and vocabulary.
2. [`01-Process-Map.md`](01-Process-Map.md) — main process map and change-oriented process descriptions.
3. [`02-State-Model.md`](02-State-Model.md) — state files, JSON fields, lifecycle and invariants.
4. [`03-Function-Process-Matrix.md`](03-Function-Process-Matrix.md) — which functions implement which processes.
5. [`04-API-Compatibility.md`](04-API-Compatibility.md) — Mastodon endpoints, versions, limits, fallbacks and budgets.
6. [`05-Regression-Test-Matrix.md`](05-Regression-Test-Matrix.md) — what the simulation protects and what remains outside the harness.
7. [`06-Process-Flow.md`](06-Process-Flow.md) — Process Flows.
8. [`07-Function-Organigram.md`](07-Function-Organigram.md) — Function Organization Chart.

## Change-oriented entry points

Use these starting points when the requested change is framed by behavior rather than by function name:

| Requested change                           | Start reading                   | Then inspect                                         |
|--------------------------------------------|---------------------------------|------------------------------------------------------|
| "Posts do not appear on Mastodon"          | `01-Process-Map.md` P5/P7       | `04-API-Compatibility.md`, local export tests        |
| "Imported replies are wrong or duplicated" | `01-Process-Map.md` P4          | `02-State-Model.md` tombstones/rechecks, reply tests |
| "Deletion is unsafe or incomplete"         | `01-Process-Map.md` P9          | Delete endpoint fallback, deletion sync tests        |
| "Admin page reports stale or wrong state"  | P12 and scheduler state         | State summary and admin assignment tests             |
| "Large sites are slow"                     | P1, budgets and scheduler state | Large-state and rate-limit tests                     |
| "Mastodon API behavior changed"            | `04-API-Compatibility.md`       | endpoint matrix, capability helpers, targeted tests  |

## Compatibility target for maintainers

The plugin code should remain compatible with:

- PHP 7.2 through PHP 8.5.
- Smarty 4.x/5.x admin template usage, including Smarty 5.8.0.
- PHPStan Level 5 style expectations: normalize mixed arrays, avoid ambiguous return shapes where practical, and keep side effects explicit.
- Shared hosting constraints: non-blocking locks, finite request budgets, finite media/delete windows, no unbounded background loops.

## Documentation consistency check

After changing the plugin, the simulation harness or this documentation set, run:

```bash
php fp-plugins/mastodon/developer-docs/check-consistency.php
```

The checker compares the regression matrix with `simulate_mastodon_plugin.php`, the function organigram with `plugin.mastodon.php`, and the documented OAuth helper names with the current implementation.

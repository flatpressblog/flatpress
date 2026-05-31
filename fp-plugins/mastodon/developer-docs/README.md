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

| Requested change                             | Start reading                            | Then inspect                                           |
| -------------------------------------------- | ---------------------------------------- | ------------------------------------------------------ |
| "Posts do not appear on Mastodon"            | `01-Process-Map.md` P5/P7                | `04-API-Compatibility.md`, local export tests          |
| "Imported replies are wrong or duplicated"   | `01-Process-Map.md` P4                   | `02-State-Model.md` tombstones/rechecks, reply tests   |
| "Deletion is unsafe or incomplete"           | `01-Process-Map.md` P9                   | Delete endpoint fallback, deletion sync tests          |
| "State repair or diagnostics are needed"     | `02-State-Model.md` maintenance sections | Admin maintenance fieldset, `mastodon-state-cli.php`   |
| "Admin page reports stale or wrong state"    | P12 and scheduler state                  | State summary and admin assignment tests               |
| "Large sites are slow"                       | P1, budgets and scheduler state          | Large-state and rate-limit tests                       |
| "Mastodon API behavior changed"              | `04-API-Compatibility.md`                | endpoint matrix, capability helpers, targeted tests    |

## Compatibility target for maintainers

The plugin code should remain compatible with:

- PHP 7.2 through PHP 8.5.
- Smarty 4.x/5.x admin template usage, including Smarty 5.8.0.
- PHPStan Level 5 style expectations: normalize mixed arrays, avoid ambiguous return shapes where practical, and keep side effects explicit.
- Shared hosting constraints: non-blocking locks, finite request budgets, finite media/delete windows, no unbounded background loops.

## Simulation harness quick reference

Run the full deterministic regression harness from the FlatPress root:

```bash
php simulate_mastodon_plugin.php
```

The harness copies the current FlatPress tree into an isolated temporary sandbox, excludes live `fp-content/content` by default, seeds deterministic fixtures, mocks Mastodon HTTP calls unless live auth is explicitly requested, and ends with a counter block containing `Exit-code`, `[OK]`, `[FAIL]`, `[WARN]`, and `[SKIP]`.

### Simulation parameters

| Parameter or query string                               | Environment variable                         | Description                                                                                                                |
| ------------------------------------------------------- | -------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| `--summary` or `?summary=1`                             | `SIMULATE_MASTODON_SUMMARY=1`                | Prints compact per-test details for browser/shared-hosting runs while keeping all assertions.                              |
| `--include-live-content` or `?include-live-content=1`   | `SIMULATE_MASTODON_INCLUDE_LIVE_CONTENT=1`   | Copies the source `fp-content/content` tree into the sandbox for explicit live-content smoke tests.                        |
| `--live-auth` or `?live-auth=1`                         | —                                            | Enables the optional read-only credential smoke test and allows the harness to contact the configured Mastodon instance.   |
| —                                                       | `SIMULATE_MASTODON_DISABLE_MEMORY_RAISE=1`   | Prevents CI memory-limit raising so the large-state `[SKIP]` branch can be tested deterministically.                       |

### Recommended commands

| Use case                          | Command                                                                                                           |
| --------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| Full local regression             | `php simulate_mastodon_plugin.php`                                                                                |
| Browser/shared-hosting output     | `php simulate_mastodon_plugin.php --summary`                                                                      |
| Environment-driven summary mode   | `SIMULATE_MASTODON_SUMMARY=1 php simulate_mastodon_plugin.php`                                                    |
| Explicit live-content smoke run   | `php simulate_mastodon_plugin.php --include-live-content --summary`                                               |
| Optional live credential smoke    | `php simulate_mastodon_plugin.php --live-auth --summary`                                                          |
| CI large-state skip branch        | `CI=1 SIMULATE_MASTODON_DISABLE_MEMORY_RAISE=1 php -d memory_limit=128M simulate_mastodon_plugin.php --summary`   |

### Memory and CI behavior

The small `300x10` state regression always runs. The heavy `3000x10` state regression checks memory before building its large synthetic state. Non-CI low-memory runs emit `[WARN]`; CI runs first try to raise `memory_limit` to 384M and emit `[SKIP]` if that is not possible. CI detection accepts common variables such as `CI`, `GITHUB_ACTIONS`, `GITLAB_CI`, `JENKINS_URL`, `BUILDKITE`, `CIRCLECI`, `TRAVIS`, `APPVEYOR`, `TEAMCITY_VERSION`, and `TF_BUILD`.

## Documentation consistency check

After changing the plugin, the simulation harness or this documentation set, run:

```bash
php fp-plugins/mastodon/developer-docs/check-consistency.php
```

The checker compares the regression matrix with `simulate_mastodon_plugin.php`, the function organigram with `plugin.mastodon.php`, and the documented OAuth helper names with the current implementation.

## Current split-state guardrails

The Mastodon plugin documentation now covers the per-entry comment-shard model, migration backup files, shard diagnostics/repair helpers and the 128-MB simulation workflow. The most relevant entry points are:

| Topic                         | Document                                                       |
| ----------------------------- | -------------------------------------------------------------- |
| State files and repair policy | [`02-State-Model.md`](02-State-Model.md)                       |
| Regression coverage           | [`05-Regression-Test-Matrix.md`](05-Regression-Test-Matrix.md) |
| Mermaid process flows         | [`06-Process-Flow.md`](06-Process-Flow.md)                     |
| Function ownership            | [`07-Function-Organigram.md`](07-Function-Organigram.md)       |

The maintenance helpers are reachable from both the FlatPress admin panel and `fp-plugins/mastodon/mastodon-state-cli.php`.

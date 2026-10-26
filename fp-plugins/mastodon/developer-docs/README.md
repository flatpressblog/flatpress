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

| Requested change                                                       | Start reading                                     | Then inspect                                                                                                                                                                                                                                           |
| ---------------------------------------------------------------------- | ------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| "Posts do not appear on Mastodon"                                      | `01-Process-Map.md` P5/P7                         | `04-API-Compatibility.md`, local export tests                                                                                                                                                                                                          |
| "Mastodon rejects a post as over 500 characters when URLs are present" | `04-API-Compatibility.md` status limits           | `plugin_mastodon_status_text_url_spans()`, `plugin_mastodon_status_text_length()`, URL punctuation-budget regression test                                                                                                                              |
| "Imported replies are wrong or duplicated"                             | `01-Process-Map.md` P4                            | `02-State-Model.md` tombstones/rechecks, reply tests                                                                                                                                                                                                   |
| "Disable comment/reply synchronization"                                | `01-Process-Map.md` P2b/P4/P4a/P6/P9/P10          | `02-State-Model.md` comment/reply gate lifecycle, `06-Process-Flow.md` gate diagrams, regression tests                                                                                                                                                 |
| "Require visitor approval before exporting comments to Mastodon"       | `01-Process-Map.md` P2c/P6/P10                    | `02-State-Model.md` `comment_reply_optins`, direct path without CommentCenter, moderated pending-file lifecycle, rejection/orphan cleanup, authenticated grants, credential-deferred opt-ins, `06-Process-Flow.md` opt-in diagram and regression tests |
| "Deletion is unsafe or incomplete"                                     | `01-Process-Map.md` P4/P9                         | Immediate tombstones, import-boundary fallback, remote-ID ownership, delete endpoint fallback and partial-sync tests                                                                                                                                   |
| "State repair or diagnostics are needed"                               | `02-State-Model.md` maintenance sections          | Dedicated admin maintenance page, `mastodon-state-cli.php`                                                                                                                                                                                             |
| "Admin page reports stale or wrong state"                              | P12 and scheduler state                           | State summary and admin assignment tests                                                                                                                                                                                                               |
| "Large sites are slow"                                                 | P1, budgets and scheduler state                   | Large-state and rate-limit tests                                                                                                                                                                                                                       |
| "Mastodon API behavior changed"                                        | `04-API-Compatibility.md`                         | endpoint matrix, capability helpers, targeted tests                                                                                                                                                                                                    |
| "Comment author links open new tabs only for external URLs"            | `01-Process-Map.md` P14                           | `comments.tpl`, `modifier.is_external_url.php`, target-decision test including root-install same-host paths                                                                                                                                            |
| "Imported status source links open the single toot in a new tab"       | `01-Process-Map.md` P3, `04-API-Compatibility.md` | `plugin_mastodon_imported_status_footer_bbcode()`, status-footer target tests                                                                                                                                                                          |

## Deleted entry import protection

A mapped FlatPress entry that no longer exists locally belongs to deletion reconciliation.
The entry import boundary rejects its remote status before HTML/media conversion, hash comparison
or local writes. It preserves the mapping and source for the later deletion run. This covers
normal/full manual runs, automatic runs and file removals that bypassed the delete hook.
See [the state invariant](02-State-Model.md#locally-deleted-mapped-entries) and
[the regression cases](05-Regression-Test-Matrix.md#locally-deleted-mapped-entries).

The normal manual action calls <code>plugin_mastodon_run_sync(true, false)</code>; the full
manual action uses <code>plugin_mastodon_run_sync(true, true)</code>, and the scheduler uses
<code>plugin_mastodon_run_sync(false)</code>. All three share the same entry import guard.
Deletion is a separate follow-up, not part of the content import itself. A successful content
run schedules that follow-up only when deletion synchronization is enabled.

## Compatibility target for maintainers

The plugin code should remain compatible with:

- PHP 7.2 through PHP 8.5.
- Smarty 4.x/5.x admin template usage, including Smarty 5.8.5.
- PHPStan Level 5 style expectations: normalize mixed arrays, avoid ambiguous return shapes where practical, and keep side effects explicit.
- Shared hosting constraints: non-blocking locks, finite request budgets, finite media/delete windows, no unbounded background loops.

## Static analysis

Run the combined Mastodon plugin and simulator analysis from the FlatPress root:

```bash
php -d memory_limit=4800M .dist/phpstan.phar analyse -c .dist/phpstan.neon.dist --level=5 --memory-limit=4800M fp-plugins/mastodon/plugin.mastodon.php fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_
```

The combined analysis exceeded 3200 MB in the verified PHP 8.5.5 Windows run;
4800 MB was sufficient. `--debug --no-progress` may be added for serial analysis.

The distributed configuration checks the declared PHP 7.2 through 8.5 range and scans Smarty 5.8.5 types without analyzing Smarty core itself.

## Simulation harness quick reference

Run the distributed deterministic regression harness directly from the FlatPress root:

```bash
php fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_
```

The harness resolves the blog root three directories above its distributed location.
An optional content-identical root copy named `simulate_mastodon_plugin.php` is
supported, but is not shipped in this snapshot and is not required for analysis
or the documentation checker. For browser use on servers that do not execute
`.ph_` files, create that root `.php` copy temporarily and remove it afterwards.
CLI commands below use the distributed file and require no copy.

The harness copies the current FlatPress tree into an isolated temporary sandbox, excludes live `fp-content/content` by default, seeds deterministic fixtures, freezes the plugin clock around date-window-sensitive fixture groups, snapshots and restores mutable option fixtures, mocks Mastodon HTTP calls unless live auth is explicitly requested, and ends with a counter block containing `Exit-code`, `[OK]`, `[FAIL]`, `[WARN]`, and `[SKIP]`.

### Simulation parameters

| Parameter or query string                             | Environment variable                       | Description                                                                                                              |
| ----------------------------------------------------- | ------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------ |
| `--summary` or `?summary=1`                           | `SIMULATE_MASTODON_SUMMARY=1`              | Prints compact per-test details for browser/shared-hosting runs while keeping all assertions.                            |
| `--include-live-content` or `?include-live-content=1` | `SIMULATE_MASTODON_INCLUDE_LIVE_CONTENT=1` | Copies the source `fp-content/content` tree into the sandbox for explicit live-content smoke tests.                      |
| `--live-auth` or `?live-auth=1`                       | —                                          | Enables the optional read-only credential smoke test and allows the harness to contact the configured Mastodon instance. |
| —                                                     | `SIMULATE_MASTODON_DISABLE_MEMORY_RAISE=1` | Prevents CI memory-limit raising so the large-state `[SKIP]` branch can be tested deterministically.                     |

### Recommended commands

| Use case                        | Command                                                                                                                                             |
| ------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------- |
| Full local regression           | `php fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_`                                                                              |
| Browser/shared-hosting output   | `php fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_ --summary`                                                                    |
| Environment-driven summary mode | `SIMULATE_MASTODON_SUMMARY=1 php fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_`                                                  |
| Explicit live-content smoke run | `php fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_ --include-live-content --summary`                                             |
| Optional live credential smoke  | `php fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_ --live-auth --summary`                                                        |
| CI large-state skip branch      | `CI=1 SIMULATE_MASTODON_DISABLE_MEMORY_RAISE=1 php -d memory_limit=128M fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_ --summary` |

### Memory and CI behavior

The small `300x10` state regression always runs. The heavy `3000x10` state regression checks memory before building its large synthetic state. Non-CI low-memory runs emit `[WARN]`; CI runs first try to raise `memory_limit` to 384M and emit `[SKIP]` if that is not possible. CI detection accepts common variables such as `CI`, `GITHUB_ACTIONS`, `GITLAB_CI`, `JENKINS_URL`, `BUILDKITE`, `CIRCLECI`, `TRAVIS`, `APPVEYOR`, `TEAMCITY_VERSION`, and `TF_BUILD`.

## Documentation consistency check

After changing the plugin, the simulation harness or this documentation set, run:

```bash
php fp-plugins/mastodon/developer-docs/check-consistency.php
```

The checker validates harness summary/category counts, regression catalog entries, physical function-line references, local Markdown links, More coverage, URL-budget documentation and plugin helper names across this documentation set. It compares optional executable harness copies with the distributed `.ph_` source and accepts a snapshot without a root copy. Mermaid parsing/rendering requires a separate Mermaid runtime; this PHP checker does not validate Mermaid syntax.

## Current split-state guardrails

The Mastodon plugin documentation now covers the per-entry comment-shard model, migration backup files, shard diagnostics/repair helpers, the exported-comment deletion invariant and the 128-MB simulation workflow. The most relevant entry points are:

| Topic                         | Document                                                       |
| ----------------------------- | -------------------------------------------------------------- |
| State files and repair policy | [`02-State-Model.md`](02-State-Model.md)                       |
| Regression coverage           | [`05-Regression-Test-Matrix.md`](05-Regression-Test-Matrix.md) |
| Mermaid process flows         | [`06-Process-Flow.md`](06-Process-Flow.md)                     |
| Function ownership            | [`07-Function-Organigram.md`](07-Function-Organigram.md)       |

The maintenance helpers are reachable from both the FlatPress admin panel and `fp-plugins/mastodon/mastodon-state-cli.php`.

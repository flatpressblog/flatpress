# Mastodon Plugin Developer Documentation

This directory is an implementation-oriented documentation set for the FlatPress Mastodon plugin.

It is written for developers who need to make safe changes without breaking synchronization state, Mastodon API compatibility, or regression coverage.

## Reading order

1. [`00-Mental-Model.md`](00-Mental-Model.md) — one-page model and vocabulary.
2. [`01-Process-Map.md`](01-Process-Map.md) — main process map and change-oriented process descriptions.
3. [`02-State-Model.md`](02-State-Model.md) — state files, JSON fields, lifecycle and invariants.
4. [`03-Function-Process-Matrix.md`](03-Function-Process-Matrix.md) — Functions, processes, module responsibilities and the loading contract.
5. [`04-API-Compatibility.md`](04-API-Compatibility.md) — Mastodon endpoints, versions, limits, budgets, safe media transfers and hosting fallbacks.
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

## Deletion safety

Read [mapped-entry protection](02-State-Model.md#locally-deleted-mapped-entries)
and [fixed entry deadlines](02-State-Model.md#fixed-local-entry-deletion-deadlines)
before changing deletion behavior. [P1/P2](01-Process-Map.md#p1p2--scheduled-and-manual-content-synchronization)
explains manual and automatic call variants; [regressions](05-Regression-Test-Matrix.md#locally-deleted-mapped-entries)
protect the import/deletion boundary.

## Compatibility target for maintainers

The plugin code should remain compatible with:

- PHP 7.2 through PHP 8.5.
- Smarty 4.x/5.x admin template usage, including Smarty 5.8.5.
- PHPStan Level 5 style expectations: normalize mixed arrays, avoid ambiguous return shapes where practical, and keep side effects explicit.
- Shared hosting constraints: non-blocking locks, finite request budgets, finite media/delete windows, no unbounded background loops.

## Static analysis

Run the combined Mastodon plugin and simulator analysis from the FlatPress root:

```bash
php -d memory_limit=4800M .dist/phpstan.phar analyse -c .dist/phpstan.neon.dist --level=5 --memory-limit=4800M fp-plugins/mastodon/plugin.mastodon.php fp-plugins/mastodon/inc fp-plugins/mastodon/developer-docs/check-consistency.php fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_
```

The combined analysis exceeded 3200 MB in the verified PHP 8.5.5 Windows run;
4800 MB was sufficient. `--debug --no-progress` may be added for serial analysis.

The distributed configuration checks the declared PHP 7.2 through 8.5 range and scans Smarty 5.8.5 types without analyzing Smarty core itself.

For installed instances with `.setup/`, use the local override below.

### PHPStan on an installed instance

After installation, `admin.php` attempts to rename `setup.php` to `.setup.php`
and `setup/` to `.setup/`. Because `.setup/` is excluded from analysis, PHPStan
can then report two `function.notFound` diagnostics for `setup_path_mode()` in
`core.fileio.php` and `core.filesystem.php`. These are symbol-discovery diagnostics,
not demonstrated PHP runtime defects. A full scan of the uninstalled repository
can discover the function in the visible `setup/` directory.

Keep the installed-only `scanFiles` entry commented out in the shared
`phpstan.neon.dist`. For an installed instance, use a local `.dist/phpstan.neon`
that is kept out of version control:

```neon
includes:
    - phpstan.neon.dist

parameters:
    scanFiles:
        - ../.setup/lib/permissions.lib.php
```

Run PHPStan with `-c .dist/phpstan.neon` for that instance. The earlier full
analysis passed after scanning this same hidden helper; it does
not require a permanent installed-only path in the shared configuration. No
plugin function-body changes or diagnostic suppressions are needed. This
follows PHPStan's [local configuration override workflow](https://phpstan.org/config-reference#multiple-files).

## Simulation harness quick reference

From the FlatPress root, run the distributed deterministic harness:

```bash
php fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_ --summary
```

See [simulation parameters](05-Regression-Test-Matrix.md#simulation-parameters),
[recommended commands](05-Regression-Test-Matrix.md#recommended-commands)
and [memory/CI policy](05-Regression-Test-Matrix.md#large-state-memory-policy).
The distributed `.ph_` entry point is sufficient; an executable root copy is optional.

## Documentation consistency check

After changing the plugin, the simulation harness or this documentation set, run:

```bash
php fp-plugins/mastodon/developer-docs/check-consistency.php
```

The checker validates harness summary/category counts, regression catalog entries, physical function-file/line references and the explicit module inventory, local Markdown files and section anchors, canonical topic ownership, media-helper process mappings, More coverage, URL-budget documentation and plugin helper names across this documentation set. It compares optional executable harness copies with the distributed `.ph_` source and accepts a snapshot without a root copy. The module inventory is read without executing plugin code; missing/unlisted modules are rejected, and LF/CRLF inputs are normalized. Mermaid parsing/rendering requires a separate Mermaid runtime; this PHP checker does not validate Mermaid syntax.

## Current split-state guardrails

For split-state storage, the exported-comment deletion invariant, migration
backups and diagnostic/repair semantics, read
[the state model](02-State-Model.md#split-comment-shard-model).
[Process flows](06-Process-Flow.md#9-admin-maintenance-page-separation) show the
admin and CLI entry points; [regression coverage](05-Regression-Test-Matrix.md)
describes the protected cases.

## Media validation and bounded downloads

[04-API-Compatibility.md](04-API-Compatibility.md#media-safety-responsibilities)
describes import budgets, poster and video validation, instance-limit refresh
and the multipart memory fallback. Import retry and cursor rules are in
[the state model](02-State-Model.md#media-retries-and-instance-freshness);
verification commands and limits are in
[the regression matrix](05-Regression-Test-Matrix.md#media-verification-and-evidence-limitations).
The additional local transport test is available as
`regression-test/media-transport-test.py`. It exercises both real download
transports without connecting to a Mastodon instance.

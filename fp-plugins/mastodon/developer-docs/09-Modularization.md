# Mastodon Plugin Modularization

## Entry point and loading behavior

[`plugin.mastodon.php`](../plugin.mastodon.php) continues to define all existing
plugin constants conditionally in its opening block. An explicit list of local
`require_once` statements then loads the internal function modules. Hooks,
the widget, and admin panel actions are registered only in the main file.

The modules provide declarations. Loading them does not start synchronization,
refresh caches, migrate state, or issue HTTP requests. Both admin classes are
loaded through `inc/admin-actions.php` inside the original
`class_exists('AdminPanelAction')` guard. This check remains after widget
registration and does not introduce an additional autoload lookup. The CLI
maintenance function remains available without the admin classes. No new
autoloader or feature-specific lazy loading has been introduced.

## Module responsibilities

| File                                                | Responsibility                                                                                                    |
| --------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| [`inc/text.php`](../inc/text.php)                   | Text, BBCode/HTML, URLs, tags, emoticons, character budgets, and status text builders.                            |
| [`inc/profile.php`](../inc/profile.php)             | Head output, profile/avatar cache, and the widget. Rendering uses local data only.                                |
| [`inc/admin.php`](../inc/admin.php)                 | Admin input handling, language strings, instance diagnostics, and Smarty assignments.                             |
| [`inc/admin-actions.php`](../inc/admin-actions.php) | Both action classes, loaded conditionally at the original registration point.                                     |
| [`inc/runtime.php`](../inc/runtime.php)             | Request cache, FlatPress configuration/time conversion, file I/O, APCu, logging, guards, and budgets.             |
| [`inc/options.php`](../inc/options.php)             | Options, normalization, secrets, feature gates, and companion plugin detection.                                   |
| [`inc/api.php`](../inc/api.php)                     | HTTP/multipart transport, OAuth, instance information, capabilities, and the status API.                          |
| [`inc/media-safety.php`](../inc/media-safety.php)   | Existing bounded downloads, multipart memory checks, video metadata, and instance refresh. Retained unchanged.    |
| [`inc/media.php`](../inc/media.php)                 | Media selection, validation, signatures, upload/polling, import, and cleanup.                                     |
| [`inc/state.php`](../inc/state.php)                 | State, shards, mappings, write lock, migration, scheduler summary, and CLI maintenance.                           |
| [`inc/local-events.php`](../inc/local-events.php)   | Local events, dirty tracking, feedback-loop protection, and comment opt-in/CommentCenter handling.                |
| [`inc/deletion.php`](../inc/deletion.php)           | Deletion reconciliation and reply-descendant rechecks.                                                            |
| [`inc/sync.php`](../inc/sync.php)                   | Content windows, local candidates, import/export, notifications, context rotation, and the scheduler entry point. |

Function names and signatures are preserved. At the modularization stages,
all 476 existing named function and method declarations, including those in
`media-safety.php`, matched the original source by SHA-256. The CRLF follow-up
confirmed the same comparison after line-ending normalization. Subsequent
formatting edits are outside that historical byte-for-byte comparison. The
main organigram still catalogs 463 declarations; the 13 media-safety functions
retain their separate catalog in `08-Media-Safety.md`. Each organigram entry
identifies its current source file and physical line.

## Preserved behavior boundaries

- `[more]` limits entry text; media, tags, and the change hash continue to use the complete entry.
- When no synchronization is due, the scheduler uses the compact scheduler state. Modularization adds no full-state read to that path.
- The state-write lock, sync lock, budgets, caches, and reference parameters remain shared and unchanged.
- One-way mode, comment export grants, tombstones, and fixed deletion deadlines retain their existing call sites.
- The cURL/stream fallback, optional extensions, and ffprobe behavior are preserved.
- Splitting files alone does not demonstrate a performance improvement. OPcache remains optional.

## Regressions and developer tools

The baseline and each of the three successive stages were checked with the full
simulator: 321 OK, 0 FAIL, 0 WARN, and 0 SKIP in every run. The names, statuses,
and order of all 321 checks match the baseline. The stages covered text/profile/
admin, infrastructure/media, and finally state/synchronization.

The unchanged main simulator already copies the modules recursively into its
sandbox. Its loader and behavioral assertions therefore required no changes.
The independent loading fixture adds six process variants:

```text
php fp-plugins/mastodon/regression-test/check-module-loading.ph_ frontend
php fp-plugins/mastodon/regression-test/check-module-loading.ph_ admin
php fp-plugins/mastodon/regression-test/check-module-loading.ph_ scoped
php fp-plugins/mastodon/regression-test/check-module-loading.ph_ cwd
php fp-plugins/mastodon/regression-test/check-module-loading.ph_ overrides
php fp-plugins/mastodon/regression-test/check-module-loading.ph_ repeat
```

The fixture loads no instance configuration. Its `.ph_` extension keeps the
core/admin stubs outside normal PHPStan symbol discovery, as with the existing
simulator. The documentation checker reads the explicit include list, detects
missing or unlisted modules, and validates physical file/line references. It
does not execute plugin code. Input line endings are normalized so the checks
accept both LF and CRLF source files.

Local runtime checks used Windows and PHP 8.5.5. The syntax target remains PHP
7.2–8.5; templates and Smarty calls were not changed. These local checks do not
constitute a complete PHP/Smarty/web-server matrix or a live Mastodon instance
test. The complete regression and PHPStan results were obtained before the
subsequent CRLF conversion; syntax and documentation consistency were checked
again after that conversion.

## PHPStan on an installed instance

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

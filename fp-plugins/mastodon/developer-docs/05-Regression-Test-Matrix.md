# 05 - Regression Matrix

## Harness model

The distributed `fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_`
loads the real Mastodon plugin from an isolated copy of the current FlatPress
tree. The plugin code itself is not replaced. HTTP responses, fixture content,
media files and selected FlatPress boundaries are controlled by the harness.
The nested script resolves the blog root three directories above its location.
A content-identical root `simulate_mastodon_plugin.php` is optional, not a file
shipped in this snapshot. Neither CLI regression nor documentation checks require it.

Normal deterministic run verified for the current implementation, including mapped-entry deletion protection:

```text
php fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_ --summary
Exit-code: 0
[OK]: 286
[FAIL]: 0
[WARN]: 0
[SKIP]: 0
```

Static `test_result()` calls found in the script: `287`.
One test is optional and only runs with `--live-auth`:

```text
Configured credentials verify (read-only smoke test)
```

## Category summary

The catalog below is generated from all static `test_result()` calls and therefore remains the canonical line-reference list for the consistency checker.

| Category                           | Static tests |
| ---------------------------------- | ------------ |
| Regular simulation tests           | 286          |
| Optional live/auth smoke           | 1            |
| Total static `test_result()` calls | 287          |

## What the harness proves well

The harness now also checks the real `comment_validate` filter chain with CommentCenter moderation, the direct save path with the CommentCenter filter explicitly removed, preservation of the checked Mastodon opt-in after unrelated validation errors, explicit hook priorities/argument counts, and cleanup of rejected or externally orphaned pending grants. The complete simulator remains green when CommentCenter is absent from the active plugin configuration; CommentCenter-specific assertions become not applicable while the direct FlatPress save path remains fully exercised. It also checks that imported Mastodon status footer links render with `target="_blank"` and `rel="nofollow noopener noreferrer"` while those BBCode attributes are stripped from outbound Mastodon text. It also checks URL-aware status budgeting at punctuation boundaries, so commas and full stops directly after links stay outside the fixed Mastodon URL placeholder. It also checks that advanced comment-shard maintenance controls are not embedded in the main settings template, are available from a dedicated maintenance template, that notification-based reply hints import old-thread replies before the slower rotation fallback, and that the central `disable_comment_reply_sync` gate disables FlatPress comment export, Mastodon reply import, reply-notification/context imports and reply deletion follow-ups while keeping entry synchronization active, that the optional `{comment_mastodon}` visitor opt-in is shown only while local comments may be exported, that FlatPress/admin comments with a stored `LOGGEDIN` marker export without a public visitor marker, that visitor opt-ins are persisted even before credentials are complete, that local visitor comments without a stored opt-in marker stay local even when saved during an admin session, and that opted-in visitor replies are not blocked by non-opted-in local parents, and that the optional explicit one-way mode blocks Mastodon-to-FlatPress imports while preserving FlatPress-to-Mastodon exports, keeps hidden import settings intact in the admin save path, hides import-only admin UI output, hides import-only companion-plugin diagnostics while keeping export helpers, re-exports local objects after remote deletion, and keeps locally deleted imported external Mastodon replies tombstoned even when the remote author edits the reply before the next content sync. It also verifies that Mastodon instance capability detection prefers machine-readable `api_versions[mastodon]`, handles nightly version strings, preserves compact `configuration.accounts` snapshots, loads the widget stylesheet through `plugin_mastodon_head()` as a versioned `res/mastodon.css` asset, refreshes the profile-widget cache during automatic and manual one-way sync paths, sends `exclude_direct=true` only when account-status support is known, retries without it for compatible-server rejections, ignores notification fallback payloads when the normal mention status is present, preserves long remote media descriptions, avoids repeated failed `/api/v2/instance` requests within one PHP request, keeps safe defaults when instance information is temporarily unavailable, version-gates unattached media cleanup deletes, and renders the compact Mastodon profile widget solely from a local public profile cache with a locally stored avatar.

| Area                                     | What is real                                                                                                                 | What is simulated                                                               |
| ---------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------- |
| State read/write and scheduler summaries | Plugin state logic and actual files in the sandbox.                                                                          | Test-specific content directory and fixture state.                              |
| Mastodon API behavior                    | Plugin API selection, payload building, fallback handling and response parsing.                                              | HTTP responses and remote status/media payloads.                                |
| Media handling                           | Local extraction, MIME/type decisions, media-family selection, upload planning, thumbnail payloads, reuse and cleanup logic. | Actual remote processing, CDN behavior and instance-specific media transcoding. |
| Text conversion                          | Real conversion helpers.                                                                                                     | Fixture strings and expected results.                                           |
| Deletion sync                            | Real deletion orchestration and status delete fallback.                                                                      | Remote deletion responses and missing-status scenarios.                         |
| Imported remote reply ignores            | Real tombstone checks, comment mapping cleanup and no-DELETE cleanup for `source=remote` comments.                           | External reply edits and legacy pending-deletion cleanup states.                |
| Admin assignment                         | Real admin assignment function and Smarty variable preparation.                                                              | Test harness Smarty object.                                                     |
| Entry scanner pruning                    | Real direct `YY/MM/entry*.txt` collector, scheduled-window month pruning and dirty-parent augmentation.                      | Synthetic trees with entry-like files below `comments/` plus targeted fixtures. |
| Profile widget                           | Real cache reader, widget callback, local avatar path validation and language files.                                         | Mocked `verify_credentials` account payload and avatar download.                |

The harness additionally verifies that external Mastodon profile URLs receive `target="_blank"` while relative and configured blog-base comment author URLs stay in the current tab. For FlatPress installations directly in the domain root, the same test also covers recognized PrettyURLs/PATH_INFO/GET routes, FlatPress-owned asset paths, existing static pages and unknown same-host root paths.

## Sandbox-content isolation policy

The harness no longer copies live `fp-content/content` into the simulation sandbox by default. This keeps regression runs independent of production-like blog size and avoids shared-host request timeouts before the first assertion. The sandbox still copies configuration and plugin files, creates an empty `fp-content/content` directory, and then seeds deterministic entry/comment/media fixtures.

Explicit live-content smoke coverage remains available with `--include-live-content` or `SIMULATE_MASTODON_INCLUDE_LIVE_CONTENT=1`, but it is intentionally outside the normal deterministic regression run.

## Large-state memory policy

The simulation verifies that `state.json` is written as compact JSON, then runs a small always-on scheduler-state test (`300x10`) and a heavy `3000x10` test. The heavy test is guarded before the synthetic state is built, because creating the large PHP array and full JSON string can exceed 128 MiB shared-hosting limits. Non-CI low-memory runs emit `[WARN]`; CI runs first try to raise `memory_limit` to 384M and emit `[SKIP]` if that is not possible. A skipped or warned heavy test does not make the regression run fail, but the coverage should be repeated in a better-resourced environment. The skip branch can be exercised by setting `CI=1` and `SIMULATE_MASTODON_DISABLE_MEMORY_RAISE=1` together with a low `memory_limit`.

## Scheduled-guard isolation policy

The dirty-comment scheduled-run regression clears the `content` sync guard immediately before calling `plugin_mastodon_run_sync(false)`. This keeps the fixture independent from earlier cooldown tests on both file-backed and APCu-backed shared hosts; otherwise an APCu guard can survive sandbox directory cleanup and produce `sync_cooldown` before `dirty_comments` is exercised.

## Date-window fixture isolation policy

Date-window-sensitive comment-export regressions use the plugin's existing `$GLOBALS['plugin_mastodon_test_now']` clock hook with a fixed UTC timestamp. The harness freezes the clock for the complete related fixture group and unsets the hook afterwards. Fixed entry/comment dates, FlatPress IDs, filenames, request payloads and diagnostics therefore remain reproducible without widening the configured synchronization window or replacing production logic with forced synchronization.

## Mutable option fixture isolation policy

The admin-assignment regression snapshots the normalized options returned by `plugin_mastodon_get_options()`, saves explicit values for `sync_scheduled_window_days`, `old_thread_reply_check` and `delete_sync_enabled`, asserts the assigned values against that local fixture, and restores the previous options afterwards. This prevents a preceding regression scenario from changing the test result while still exercising the real option persistence and admin-assignment paths.

## Locally deleted imported remote replies

When a FlatPress admin deletes a comment that originally came from a Mastodon reply (`source=remote`), the harness now treats that deletion as a local ignore decision. The comment-deleted hook must set `comment_tombstones`, remove the reverse `comments_remote` mapping and avoid queuing a Mastodon `DELETE` for a status that may belong to another account. Regression coverage verifies both the unchanged reply and the edited-remote-reply case so a later `/api/v1/statuses/:id/context` pass cannot re-create the local comment, and it preserves `source=local` ownership for already exported FlatPress comments seen again in the context response.

The deletion-sync cleanup regression also builds a legacy pending state with a missing local `source=remote` comment. It must mark the remote status as locally ignored, clear the mapping, leave `deleted_remote_comments` unchanged and record no outbound `DELETE` request.

## Locally deleted mapped entries

Eight static regression groups use the real export/delete/import/deletion functions. They
cover the exact normal manual reproduction, scheduled and full manual content runs, enabled
remote updates with comment/reply synchronization enabled, direct file removal without the hook,
matching hashes, remote-sourced entry
mappings, disabled deletion synchronization, HTTP 500 with protected replay and successful
retry, and an already missing remote status. The disabled-deletion case asserts no outbound
DELETE and retained mappings. The successful cases assert one deletion counter increment,
removed forward/reverse mappings, cleared pending work and no recreated entry.

The replay includes remote media in changed-content cases: there must be no media download,
remote write or immediate thread-context request. Skipped entries still advance the remote
status cursor. A direct import-boundary assertion requires false with unchanged state and zero
requests, including matching hashes. A version matrix covers 4.0.0, 4.3.0, 4.4.0, 4.5.0, 4.6.0 and
5.0.0-nightly.2026-10-22, asserting the exact DELETE URL. An unknown-capability fixture asserts
the legacy retry without the media parameter. Existing comment/reply, one-way, deletion cursor
and descendant reconciliation tests remain part of the full run.

## Locally deleted exported comments and remote-ID ownership

Four focused regressions protect the exported-comment invariant without loading every shard. The normal manual partial-sync and automatic scheduled partial-sync cases place the mapped comment outside the configured content window, delete it through the FlatPress hook, and then make the old thread visible through context rotation. Both runs must already contain `local_deleted_pending_remote_delete`, import zero replacement comments, preserve the original `source=local` mapping for deletion sync and keep `comments_remote` pointed at that owner.

A bypass-hook case removes the local file directly to model legacy state, manual filesystem changes or an earlier hook failure. The remote-import boundary must load only the reverse-indexed shard, create the same tombstone and refuse the stale reply. A mapping-ownership case proves that a duplicate remote ID cannot overwrite another local comment's owner while a legitimate remap of the same local comment removes its previous reverse-index entry.

## What still needs real-world testing
- Real Mastodon instances with different configuration values, moderation modes and server-side media validation messages.
- Real audio/video/image processing latency, storage/CDN failures, thumbnail generation and server-specific MIME policies.
- Webhost-specific file locking, permissions and long request timeouts.
- OAuth app registration on instances with custom policies.
- Browser/Admin UI rendering with the actual FlatPress theme and Smarty version.
- Sidebar/widget rendering in the active theme with real narrow mobile and desktop widget columns.

## Media-family selection regression focus
The media-family policy is protected by dedicated tests. These tests call the real plugin media collector, planner and upload helper. They verify that raw FlatPress content may contain multiple media families, but the export plan only forwards a Mastodon-compatible selected set to uploads and status requests.

| Scenario                | Expected selected media                                                                             |
| ----------------------- | --------------------------------------------------------------------------------------------------- |
| Image + audio + video   | Images only, up to the instance media limit.                                                        |
| Audio + video           | Exactly one audio attachment.                                                                       |
| Video only with poster  | Exactly one video attachment; poster is a `/api/v2/media` thumbnail field, not a second `media_id`. |
| Multiple images/gallery | Images up to `configuration.statuses.max_media_attachments`.                                        |

## More tag in entry exports

The More regressions cover the first marker, case-insensitive matching, markers
at the beginning/end, unchanged exports without a marker, Unicode and escaped
text. Further assertions protect the title, entry link, tags after the marker
and unchanged input data. Long teasers with URLs and optional tags must still
fit within 80 or 500 characters. Cached instance data for Mastodon 4.0.0 through
5.0.0-nightly.2026-10-22 also exercises a URL reservation of 30 characters and
a limit of 180 without additional HTTP requests. The chronology test additionally
checks the boundary through actual export paths with mocked POST and PUT calls.

In the verified Windows run with PHP 8.5.5, the combined PHPStan Level 5 analysis
of the plugin and simulator exceeded the previous 3200 MB recommendation.
The limit was raised using `-d memory_limit=4800M` and `--memory-limit=4800M`
for this check; the configuration and rules were unchanged. The initial implementation check created an optional content-identical root
`simulate_mastodon_plugin.php` copy. Current README commands and the updated
checker use the distributed `.ph_` file directly, without requiring that copy. The regression test itself ran directly using the documented `.ph_`
variant.

## Documentation-checker regression coverage

The October 26, 2026 audit additionally exercised the checker in isolated copies
of the documentation and source files. It accepted the distributed harness
without a root copy and accepted an identical optional copy with CRLF line
endings. It rejected stale normal/static/category totals, an unknown helper in
the README, a missing local Markdown target, the wrong Smarty scan version, an
incorrect physical function line, an incorrect declaration count and a drifting
optional root harness. An inventory check confirmed descriptions for all 456
distinct callable names across 459 named declarations. These 12 audit cases
are separate from the 278 regular plugin simulation assertions above.

All 69 Mermaid blocks in this documentation set were also parsed and rendered
with Mermaid 12.2.0. Rendering success checks syntax and SVG generation;
comparison with the PHP control flow remains a separate review step.

## Full static test catalog

| Line  | Static test name                                                                                                                                                |
| ----- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 3207  | Simulation summary mode shortens verbose JSON details                                                                                                           |
| 3219  | Simulation final summary reports exit code and status counters                                                                                                  |
| 3240  | Simulation sandbox excludes live fp-content/content by default                                                                                                  |
| 3251  | Simulation sandbox can include live fp-content/content only when explicitly requested                                                                           |
| 3275  | Entry file scanner skips comment directories while keeping real FlatPress entries                                                                               |
| 3334  | Scheduled direct YY/MM scanner keeps active-window entries and dirty-comment parents                                                                            |
| 3383  | Scheduled direct scanner honors the 7-day admin window at parse stage                                                                                           |
| 3418  | Scheduled direct scanner honors the 14-day admin window across a month boundary                                                                                 |
| 3453  | Scheduled direct scanner honors the 30-day admin window across a year boundary                                                                                  |
| 3487  | Scheduled direct scanner treats more than three dirty entries as mandatory candidates                                                                           |
| 3527  | Scheduled direct scanner treats more than three dirty-comment parents as mandatory candidates                                                                   |
| 3554  | Manual force sync keeps the direct all-YY/MM scanner repair path                                                                                                |
| 3578  | Mastodon companion-plugin detection uses FlatPress central enabled-plugin state                                                                                 |
| 3587  | Mastodon companion-plugin detection respects FlatPress plugin_exists() for missing configured plugins                                                           |
| 3600  | Mastodon admin companion-plugin diagnostics cover BBCode, PhotoSwipe, AudioVideo, Tag and Emoticons                                                             |
| 3630  | One-way companion-plugin diagnostics hide import-only helpers and describe export helpers                                                                       |
| 3649  | FlatPress locale is normalized to a Mastodon language code                                                                                                      |
| 3667  | Mastodon HTML -> FlatPress BBCode                                                                                                                               |
| 3688  | Mastodon mention noise is cleaned for FlatPress                                                                                                                 |
| 3693  | Imported Mastodon entry keeps the Mastodon status link on a new line with target blank                                                                          |
| 3700  | Imported Mastodon status footer BBCode opens the single toot in a new tab                                                                                       |
| 3706  | Imported Mastodon status footer attributes are stripped for outbound Mastodon text                                                                              |
| 3714  | Imported Mastodon status footer renders target blank HTML for the single toot                                                                                   |
| 3724  | Mastodon emojis become FlatPress emoticons                                                                                                                      |
| 3732  | FlatPress BBCode -> Mastodon text                                                                                                                               |
| 3740  | FlatPress URLs lose HTML-like attributes and omit localhost links                                                                                               |
| 3818  | Comment author target blank is limited to external URLs                                                                                                         |
| 3833  | FlatPress entry tags become a Mastodon hashtag footer                                                                                                           |
| 3858  | Mastodon tags become FlatPress tag BBCode without a visible hashtag footer                                                                                      |
| 3881  | Removing Mastodon tags removes FlatPress tag BBCode on update                                                                                                   |
| 3894  | FlatPress entry without tag BBCode has no Mastodon hashtag footer                                                                                               |
| 3907  | Mastodon head metadata uses the configured instance URL and username                                                                                            |
| 3920  | Mastodon head metadata tolerates usernames stored with a leading at-sign                                                                                        |
| 3933  | Mastodon head metadata omits profile metadata when no username is configured                                                                                    |
| 3948  | Actual FlatPress comment exports as readable Mastodon reply                                                                                                     |
| 3958  | FlatPress comment export appends the public comment link on a new line                                                                                          |
| 3969  | FlatPress entry export converts Emoticons plugin shortcodes in titles and bodies to Unicode emoji for Mastodon                                                  |
| 3988  | FlatPress comment export converts Emoticons plugin shortcodes in author lines and bodies to Unicode emoji for Mastodon                                          |
| 4006  | Long local entry export is limited even without a public permalink                                                                                              |
| 4030  | Entry More boundary handles first marker, empty teaser, Unicode and literal text                                                                                |
| 4050  | Entry More boundary preserves title, permalink, tag footer and original entry data                                                                              |
| 4079  | Entry More teaser still respects server character and URL budgets with and without tags                                                                         |
| 4089  | FlatPress single-image entry exposes one uploadable media item                                                                                                  |
| 4099  | FlatPress gallery entry exposes uploadable media items and keeps raw gallery BBCode out of Mastodon text                                                        |
| 4152  | FlatPress AudioVideo entry exposes described audio/video media items and strips raw player BBCode from Mastodon text                                            |
| 4172  | AudioVideo BBCode parser renders optional description endtags as media attributes and keeps legacy single tags working                                          |
| 4208  | Mastodon media upload sends AudioVideo files with descriptions and video thumbnail                                                                              |
| 4233  | Mastodon media planner exports only images when an entry mixes image, audio and video media                                                                     |
| 4244  | Mastodon media planner exports exactly one audio attachment when an entry mixes audio and video media                                                           |
| 4258  | Mastodon media planner exports one video and keeps its poster as an upload thumbnail only                                                                       |
| 4271  | Mastodon media planner keeps multiple images up to the Mastodon media limit                                                                                     |
| 4300  | Mastodon export uploads only the selected audio item from an audio/video FlatPress entry                                                                        |
| 4330  | Mastodon export sends a video poster as upload thumbnail instead of a second media ID                                                                           |
| 4362  | Mastodon single image imports into a FlatPress image tag and stored file                                                                                        |
| 4386  | Mastodon import preserves long remote media descriptions without corrupting FlatPress image markup                                                              |
| 4430  | Mastodon multiple images import into a FlatPress gallery with captions                                                                                          |
| 4479  | Mastodon audio and video attachments import into FlatPress AudioVideo BBCode and stored files                                                                   |
| 4518  | Mastodon audio/video import retries alternate direct media URLs when the first download candidate fails                                                         |
| 4532  | First sync is immediately due                                                                                                                                   |
| 4535  | Next day after 23:00 is due                                                                                                                                     |
| 4536  | Before configured time is not due                                                                                                                               |
| 4557  | Scheduled synchronization due checks use stored UTC time regardless of PHP default timezone                                                                     |
| 4579  | Pending deletion synchronization due checks use UTC cooldowns regardless of PHP default timezone                                                                |
| 4610  | Admin state timestamps are formatted with FlatPress timeoffset regardless of PHP default timezone                                                               |
| 4625  | Scheduled content synchronization uses a short file/APCu cooldown guard                                                                                         |
| 4642  | Full Mastodon state fallback is not stored in APCu                                                                                                              |
| 4674  | Central Mastodon rate-limit guard stops requests after the per-run request budget                                                                               |
| 4708  | Central Mastodon rate-limit guard stops media uploads after the per-run media budget                                                                            |
| 4732  | Central Mastodon rate-limit guard stops status deletions after the per-run delete budget                                                                        |
| 4758  | Cached Mastodon versions before 4.4 delete statuses without the delete_media query parameter                                                                    |
| 4782  | Status deletion retries without delete_media when an older Mastodon server rejects the query parameter                                                          |
| 4812  | Persistent Mastodon media-upload window budget is shared across forced runs                                                                                     |
| 4842  | Persistent media-upload window budget is written to the synchronization last error                                                                              |
| 4869  | Persistent Mastodon delete window budget is shared across forced runs                                                                                           |
| 4893  | Persistent delete window budget is written to the synchronization last error                                                                                    |
| 4920  | Persistent Mastodon account-status paging window budget is shared across forced runs                                                                            |
| 4943  | Persistent account-status paging window budget is written to the synchronization last error                                                                     |
| 4966  | Admin synchronization time converts stored UTC to FlatPress local time and back                                                                                 |
| 4989  | Admin synchronization time conversion supports fractional FlatPress offsets                                                                                     |
| 5005  | Sync start date normalization accepts valid ISO dates                                                                                                           |
| 5011  | Local and remote date helpers derive stable date keys                                                                                                           |
| 5020  | Remote-to-local update toggle normalizes safely                                                                                                                 |
| 5031  | Comment-as-entry import toggle normalizes safely                                                                                                                |
| 5053  | Comment/reply synchronization disable toggle normalizes and saves safely                                                                                        |
| 5084  | Mastodon comment opt-in is required only when local comments may be exported                                                                                    |
| 5110  | Mastodon comment opt-in markup is shown only while comment/reply sync is active                                                                                 |
| 5127  | State entry mapping created                                                                                                                                     |
| 5128  | State comment mapping created                                                                                                                                   |
| 5130  | State comment-to-reply opt-in marker created                                                                                                                    |
| 5131  | Local visitor comment without Mastodon opt-in is not eligible for Mastodon export                                                                               |
| 5182  | Mastodon runtime files in fp-content/plugin_mastodon use FlatPress FILE_PERMISSIONS                                                                             |
| 5221  | Initial local thread comment/reply sync is enabled                                                                                                              |
| 5380  | OAuth code exchange                                                                                                                                             |
| 5388  | State cache stays fresh after writing state.json                                                                                                                |
| 5409  | Pretty-printed legacy state.json remains readable after compact-state optimization                                                                              |
| 5457  | State write persists compact split state.json while preserving round-trip mappings                                                                              |
| 5522  | Split comment shards support bounded per-entry state reads                                                                                                      |
| 5546  | Partial split-state writes preserve unloaded comment shards                                                                                                     |
| 5590  | Legacy inline comment state migrates to per-entry comment shards on write                                                                                       |
| 5665  | Legacy inline migration preserves non-contiguous comments and creates a migration backup                                                                        |
| 5724  | Comment-shard diagnostics detect stale metadata and repair the reverse index from shards                                                                        |
| 5745  | CLI comment-shard diagnostics entry point returns a machine-checkable status                                                                                    |
| 5780  | Shard-write failures leave the main state unchanged and return failure                                                                                          |
| 5819  | Main-state write failures after shard writes remain repairable from shard files                                                                                 |
| 5856  | Legacy combined stats are migrated into separate content and deletion counters                                                                                  |
| 5880  | Instance configuration cache avoids repeated /api/v2/instance requests                                                                                          |
| 5907  | Nightly Mastodon versions use cached api_versions for status edit and delete capabilities                                                                       |
| 5930  | Machine-readable api_versions are preferred over complex human-readable Mastodon version strings                                                                |
| 5959  | api_versions below delete_media support suppress the delete_media query even with a nightly version string                                                      |
| 5985  | api_versions below unattached media delete support skip uploaded media cleanup DELETE requests                                                                  |
| 6008  | Cached Mastodon versions before 4.4 skip unattached media DELETE cleanup requests                                                                               |
| 6042  | Unknown unattached media delete capability stays best-effort without an instance lookup                                                                         |
| 6081  | Mastodon API version 4 deletes unattached uploaded media during cleanup                                                                                         |
| 6112  | Mastodon 4.6 configuration.accounts limits survive compact instance snapshots                                                                                   |
| 6144  | Mastodon 4.6.0-nightly account-status import sends exclude_direct for privacy                                                                                   |
| 6176  | Mastodon API version 10 account-status import sends exclude_direct even on forked version strings                                                               |
| 6212  | Account-status import retries without exclude_direct when a compatible server rejects the parameter                                                             |
| 6247  | Cached Mastodon versions before 4.6 keep account-status import compatible without exclude_direct                                                                |
| 6276  | Failed instance-information lookups are negatively cached per request and fall back to defaults                                                                 |
| 6313  | Mastodon URL budgeting respects characters_reserved_per_url when limiting plain-text exports                                                                    |
| 6332  | Mastodon URL budgeting counts trailing punctuation outside the URL placeholder                                                                                  |
| 6362  | Configured credentials verify (read-only smoke test)                                                                                                            |
| 6374  | Entry export uses clean Mastodon text and suppresses localhost permalinks                                                                                       |
| 6382  | Comment export is reply-like, localized and emoji-aware                                                                                                         |
| 6419  | Remote entry imported through sync with Mastodon media visible in FlatPress                                                                                     |
| 6425  | Deletion sync is queued for a follow-up request instead of running inside the content sync request                                                              |
| 6431  | Content sync updates the synchronization timestamp and counters while leaving the deletion timestamp for the follow-up request                                  |
| 6450  | Private mention status is skipped during Mastodon import                                                                                                        |
| 6456  | Remote imported entry keeps a stable FlatPress date                                                                                                             |
| 6462  | Remote comment imported through sync                                                                                                                            |
| 6468  | Nested remote comment keeps parent mapping metadata                                                                                                             |
| 6474  | Private mention reply is skipped during Mastodon import                                                                                                         |
| 6512  | Entry export creates a top-level Mastodon status                                                                                                                |
| 6518  | Comment to entry exports with in_reply_to_id on the entry status                                                                                                |
| 6524  | Comment to comment exports with in_reply_to_id on the parent reply                                                                                              |
| 6530  | Entry and comment exports include the configured Mastodon language code                                                                                         |
| 6549  | Deletion sync waits at least five minutes after a completed content sync                                                                                        |
| 6601  | Create and update status requests include the configured Mastodon language code                                                                                 |
| 6644  | Initial Mastodon media uploads include the attachment description in POST /api/v2/media                                                                         |
| 6676  | Unchanged entry media is reused without a new upload when only the post text changes                                                                            |
| 6749  | Mastodon 4.1+ updates changed image descriptions through status media_attributes without re-uploading media                                                     |
| 6786  | Older Mastodon versions fall back to a fresh upload when only the media description changes                                                                     |
| 6873  | Full local-to-remote sync reuses stored media IDs when the attachments did not change                                                                           |
| 6973  | Full sync updates changed media descriptions through status media_attributes on Mastodon 4.1+                                                                   |
| 7098  | Batch entry export keeps older FlatPress entries below newer ones on Mastodon                                                                                   |
| 7135  | Entry More boundary applies to status creation and mapped status updates                                                                                        |
| 7233  | New local comment on an already synchronized older entry is exported to Mastodon                                                                                |
| 7312  | Disabled comment/reply sync keeps local comments off Mastodon                                                                                                   |
| 7391  | Disabled comment/reply sync skips remote reply deletion follow-up                                                                                               |
| 7487  | Known synchronized entry mappings older than the sync start date do not trigger context refreshes                                                               |
| 7561  | Scheduled content synchronization respects the automatic recent-content window                                                                                  |
| 7587  | Productive content-sync workset loads only active scheduled comment shards                                                                                      |
| 7646  | Normal manual synchronization bypasses the daily due check but still respects the automatic window                                                              |
| 7708  | Explicit full manual synchronization bypasses the automatic window while keeping normal limits                                                                  |
| 7787  | Older changed mapped FlatPress entries are synchronized through the dirty queue                                                                                 |
| 7839  | Post-success comment hook queues older changed mapped comments                                                                                                  |
| 7869  | Scheduled sync updates older dirty comments through direct YY/MM dirty-parent candidates                                                                        |
| 7916  | Remote-write guard suppresses dirty tracking for Mastodon-owned entry_save calls                                                                                |
| 8005  | Large scheduled dirty-tracking sync parses only active-window and dirty entries                                                                                 |
| 8088  | Optional old-thread reply checks rotate through known synchronized threads                                                                                      |
| 8129  | Disabled old-thread reply checks do not refresh known synchronized entry contexts                                                                               |
| 8210  | Disabled comment/reply sync imports Mastodon entries without fetching reply contexts or notifications                                                           |
| 8289  | Automatic scheduled synchronization rotates known synchronized threads through the full sync path                                                               |
| 8375  | Notification hints import a new reply on an old mapped Mastodon entry without context rotation                                                                  |
| 8391  | Notification fallback payloads are ignored when a normal mention status is available                                                                            |
| 8460  | Notification hints import a new reply on an old mapped Mastodon reply with reply metadata                                                                       |
| 8565  | Notification context hints use the old-thread budget before normal rotation                                                                                     |
| 8616  | Reply-notification polling is skipped until read:notifications is authorized                                                                                    |
| 8739  | Remote updates do not overwrite existing local content when the toggle is disabled                                                                              |
| 8759  | Remote updates overwrite existing local content when the toggle is enabled                                                                                      |
| 8784  | FlatPress single-image entry exports Mastodon media_ids and strips raw image BBCode                                                                             |
| 8795  | FlatPress gallery entry exports Mastodon media_ids up to the instance limit and strips raw gallery BBCode                                                       |
| 8933  | Sync start date filters local exports by entry and comment date                                                                                                 |
| 9019  | Sync start date filters remote imports by status and reply date                                                                                                 |
| 9111  | Remote Mastodon imports honor the FlatPress timeoffset for stored entry/comment dates and ordering keys                                                         |
| 9182  | Remote sync start filtering respects the FlatPress timeoffset near midnight                                                                                     |
| 9247  | Sync reports local export failures instead of silently succeeding                                                                                               |
| 9265  | Shared-request synchronization refreshes the PHP execution budget for long Mastodon work                                                                        |
| 9348  | Synchronized local comments are not imported as duplicate entries from Mastodon by default                                                                      |
| 9440  | Synchronized local comments may be imported as entries when the toggle is enabled                                                                               |
| 9575  | Local FlatPress comments on imported Mastodon entries sync back to Mastodon, including nested replies                                                           |
| 9651  | Asynchronous Mastodon media uploads are polled until the attachment is ready                                                                                    |
| 9675  | Long-running Mastodon media uploads refresh the PHP execution budget for upload and polling                                                                     |
| 9747  | Slow asynchronous Mastodon video uploads keep polling beyond the old short retry window                                                                         |
| 9813  | Mastodon audio uploads keep polling even when pending responses have no preview_url                                                                             |
| 9878  | Follow-up deletion sync removes remote Mastodon content after local FlatPress deletion                                                                          |
| 9903  | Follow-up deletion sync updates last_deletion_run without overwriting the last synchronization timestamp or content counters                                    |
| 9959  | Deletion sync falls back to DELETE without delete_media for older Mastodon servers                                                                              |
| 10022 | Follow-up deletion sync removes mirrored FlatPress content after remote Mastodon deletion                                                                       |
| 10077 | Deletion sync skips old locally deleted mappings outside the sync start date window                                                                             |
| 10127 | Deletion sync skips old remotely mirrored mappings outside the sync start date window                                                                           |
| 10184 | Scheduled deletion sync skips remote lookups outside the automatic scheduled window                                                                             |
| 10257 | Scheduled deletion sync still propagates old local deletions outside the automatic window                                                                       |
| 10318 | Large scheduled deletion syncs resume from the saved entry cursor                                                                                               |
| 10402 | Large scheduled deletion syncs resume from the saved comment cursor inside one entry shard                                                                      |
| 10447 | Disabling deletion synchronization clears pending delete work without issuing deletion requests                                                                 |
| 10467 | Content sync before deletion sync protects a locally deleted exported FlatPress comment from stale re-import                                                    |
| 10486 | Deletion sync after the protected content sync reattaches the imported descendant reply to the synchronized entry status and keeps a later verification pending |
| 10529 | Normal manual partial content sync keeps an old locally deleted exported comment tombstoned without stale re-import                                             |
| 10568 | Automatic scheduled partial content sync keeps an old locally deleted exported comment tombstoned without stale re-import                                       |
| 10605 | Remote import defensively tombstones a missing source=local comment when the deletion hook was bypassed                                                         |
| 10632 | Comment mapping rejects duplicate remote ids and removes the previous reverse id on a legitimate remap                                                          |
| 10659 | Deletion sync keeps imported descendants for a later follow-up verification when Mastodon still returns the child reply once                                    |
| 10676 | Tombstones prevent re-importing the deleted exported parent comment while descendant rechecks are still pending                                                 |
| 10689 | A later follow-up deletion sync removes the imported descendant once Mastodon reports the child reply missing                                                   |
| 10720 | Content sync before deletion sync also protects a locally deleted exported FlatPress comment when the imported descendant reply already has its own child       |
| 10740 | Deletion sync after the protected content sync keeps the descendant child below the reattached reply and verifies both remote replies in the same pass          |
| 10780 | Deletion sync removes imported descendants immediately when Mastodon already reports the child reply missing in the first follow-up pass                        |
| 10811 | Hybrid descendant rechecks keep only the direct child queued after the first stale follow-up pass of a deeper reply chain                                       |
| 10830 | Hybrid descendant rechecks remove the direct child and its direct child within the same targeted follow-up request                                              |
| 10855 | Locally deleted imported external Mastodon reply is tombstoned immediately and not re-imported unchanged                                                        |
| 10880 | Locally deleted imported external Mastodon reply is not re-imported after the remote author edits it                                                            |
| 10903 | Deletion sync treats locally deleted imported remote replies as local ignore decisions without remote DELETE                                                    |
| 10985 | Remote Mastodon comment updates increment the local-comment update counter instead of the entry counter                                                         |
| 11026 | Admin assignment exposes split sync counters, local timestamps, old-thread and deletion-sync options                                                            |
| 11119 | Admin maintenance actions are separated into a dedicated template reached from the main plugin page                                                             |
| 11210 | Media upload cleanup deletes already uploaded attachments when a later media upload fails                                                                       |
| 11277 | Media upload cleanup deletes uploaded attachments when final status creation fails                                                                              |
| 11335 | OAuth scope discovery prefers the profile scope on current Mastodon instances                                                                                   |
| 11381 | OAuth scope discovery falls back to read:accounts plus notifications on older Mastodon instances                                                                |
| 11433 | Existing registered apps keep the legacy read:accounts scope until they are re-registered                                                                       |
| 11524 | Instance information refresh persists a compact snapshot including the exact Mastodon version                                                                   |
| 11570 | Admin assignment exposes cached instance-information rows without triggering another live instance request                                                      |
| 11587 | Changing the configured instance URL invalidates the saved instance-information snapshot                                                                        |
| 11625 | Sync-related instance limit helpers reuse the stored instance snapshot without another /api/v2/instance request                                                 |
| 11663 | Entry More export uses cached limits across Mastodon 4.0 through 5.0 nightly without extra requests                                                             |
| 11675 | Manual sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                            |
| 11693 | Replies to FlatPress comments on remote-sourced entries are exported as replies to the parent Mastodon reply                                                    |
| 11723 | Unsynchronized local parent comment is exported before local child reply                                                                                        |
| 11743 | Non-forced sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                        |
| 11766 | Manual sync exports Emoticons plugin shortcodes from FlatPress entries to Unicode emoji in Mastodon status requests                                             |
| 11782 | Manual sync exports Emoticons plugin shortcodes from FlatPress comments to Unicode emoji in Mastodon reply requests                                             |
| 11803 | Manual sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled         |
| 11829 | Non-forced sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled     |
| 11852 | Another Mastodon member reply to an exported FlatPress comment is still imported as a FlatPress comment when comment-as-entry import is enabled                 |
| 11869 | Disabling the quote option imports a Mastodon reply to an exported FlatPress comment without a leading quote block                                              |
| 11883 | Imported Mastodon replies to another Mastodon reply quote the replied-to Mastodon user and text by default                                                      |
| 11902 | Disabling the quote option imports Mastodon reply-to-reply comments without a leading quote block                                                               |
| 11916 | Manual sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                          |
| 11940 | Non-forced sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                      |
| 11993 | Scheduler state is written as a compact summary without full mapping arrays                                                                                     |
| 12012 | Fresh scheduler-state read uses the APCu-capable FlatPress I/O path and does not load full state.json                                                           |
| 12041 | Stale scheduler-state falls back to the full state and rebuilds the summary                                                                                     |
| 12062 | Manual admin synchronization still loads the full state before reporting configuration errors                                                                   |
| 12081 | sync.log uses append-only writes with size-based rotation                                                                                                       |
| 12107 | Large skip volumes are logged as aggregate summaries                                                                                                            |
| 12120 | Small 300x10 state keeps scheduler-state compact and disables full APCu fallback                                                                                |
| 12142 | Fresh small scheduler-state read avoids full state.json and uses APCu-capable file I/O                                                                          |
| 12165 | Large 3000x10 state keeps scheduler-state compact and disables full APCu fallback                                                                               |
| 12188 | Fresh large scheduler-state read avoids full state.json and uses APCu-capable file I/O                                                                          |
| 12304 | Automatic scheduled sync exports a new current comment on an old mapped entry through dirty_comments                                                            |
| 12407 | Authenticated FlatPress comments export after a stored LOGGEDIN marker without visitor opt-in                                                                   |
| 12518 | Admin session does not grant Mastodon export to visitor comments without opt-in                                                                                 |
| 12584 | Visitor Mastodon opt-in checkbox preserves POST state and hook priorities/signatures are explicit                                                               |
| 12686 | Visitor opt-in persists through the direct FlatPress save path when CommentCenter is disabled                                                                   |
| 12865 | CommentCenter preserves visitor Mastodon opt-in until admin approval and export                                                                                 |
| 13003 | CommentCenter rejection and bounded pruning remove orphaned pending opt-in grants                                                                               |
| 13109 | Visitor opt-in is stored without complete credentials and later permits comment export                                                                          |
| 13214 | Opted-in visitor replies export even when their local parent has no Mastodon opt-in                                                                             |
| 13275 | One-way mode option is disabled by default, normalized, assigned to admin UI, templated and translated                                                          |
| 13315 | One-way admin save preserves hidden import options while keeping one-way mode enabled                                                                           |
| 13332 | One-way admin save preserves hidden import options when one-way mode is disabled in the same submit                                                             |
| 13352 | Bidirectional admin save still stores visible unchecked import options as disabled                                                                              |
| 13384 | One-way admin template hides import controls, notification hints and import-only counters                                                                       |
| 13410 | One-way admin template keeps export, OAuth, instance, token, state and deletion outputs visible                                                                 |
| 13426 | One-way admin assignment hides import-only companion plugins and uses one-way companion intro                                                                   |
| 13532 | One-way mode blocks Mastodon-to-FlatPress imports while FlatPress-to-Mastodon export still runs                                                                 |
| 13619 | Automatic one-way content sync refreshes the Mastodon widget profile cache without remote import reads                                                          |
| 13694 | One-way deletion sync keeps local content, removes stale remote mappings and queues re-export                                                                   |
| 13769 | One-way content sync re-exports local objects whose remote mappings were unlinked after remote deletion                                                         |
| 13838 | One-way pending descendant rechecks keep local comments and queue them for re-export instead of deleting them                                                   |
| 13859 | Mastodon widget remains hidden and performs no HTTP requests while its local profile cache is missing                                                           |
| 13902 | Mastodon widget profile cache is refreshed from verify_credentials and stores only public profile data plus a local avatar                                      |
| 13927 | Mastodon widget renders compact local-cache markup without remote API calls or inline CSS                                                                       |
| 13951 | Mastodon widget stylesheet is loaded by plugin_mastodon_head as a versioned CSS asset                                                                           |
| 13966 | Mastodon widget profile refresh reuses the cached avatar when the Mastodon avatar URL is unchanged                                                              |
| 13993 | Mastodon widget uses a localized fallback avatar alt text for Mastodon 4.0-4.5 account payloads without avatar_description                                      |
| 14012 | Mastodon widget hides incomplete local profile caches instead of falling back to remote avatar URLs                                                             |
| 14037 | Mastodon widget is registered and translated in all FlatPress plugin language files                                                                             |
| 14220 | Normal content sync preserves a deleted exported entry and its mapping until deletion sync removes the Mastodon status                                          |
| 14228 | Scheduled and full content sync cannot resurrect deleted mapped entries even when remote updates are enabled                                                    |
| 14235 | Entry import deletion protection precedes matching hashes and covers bypassed hooks and remote-sourced mappings                                                 |
| 14240 | Disabling deletion sync preserves missing entry mappings without recreating local content or deleting remote statuses                                           |
| 14245 | Failed remote entry deletion retains import protection and mapping ownership until a successful retry                                                           |
| 14250 | Entry deletion after a protected content replay accepts an already missing Mastodon status                                                                      |
| 14261 | Deleted entry replay and deletion remain compatible with Mastodon 4.0 through 5.0 nightly API capability gates                                                  |
| 14266 | Protected entry deletion retains the legacy retry without delete_media when instance capability is unknown                                                      |

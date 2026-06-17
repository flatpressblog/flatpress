# 05 - Regression Matrix

## Harness model
`simulate_mastodon_plugin.php` loads the real Mastodon plugin and simulates the FlatPress and Mastodon boundaries. The plugin code itself is not replaced. The HTTP layer, fixture content, media files and some FlatPress services are controlled by the harness so behavior is deterministic.
Normal run checked for this documentation set after the exported-comment deletion invariant update:
```text
php simulate_mastodon_plugin.php --summary
Exit-code: 0
[OK]: 269
[FAIL]: 0
[WARN]: 0
[SKIP]: 0
```
Static `test_result()` calls found in the script: `270`.
One test is optional and only runs with `--live-auth`:

```text
Configured credentials verify (read-only smoke test)
```

## Category summary

The catalog below is generated from all static `test_result()` calls and therefore remains the canonical line-reference list for the consistency checker.

| Category                           | Static tests |
| ---------------------------------- | ------------ |
| Regular simulation tests           | 269          |
| Optional live/auth smoke           | 1            |
| Total static `test_result()` calls | 270          |

## What the harness proves well

The harness now also checks that imported Mastodon status footer links render with `target="_blank"` and `rel="nofollow noopener noreferrer"` while those BBCode attributes are stripped from outbound Mastodon text. It also checks that advanced comment-shard maintenance controls are not embedded in the main settings template, are available from a dedicated maintenance template, that notification-based reply hints import old-thread replies before the slower rotation fallback, and that the central `disable_comment_reply_sync` gate disables FlatPress comment export, Mastodon reply import, reply-notification/context imports and reply deletion follow-ups while keeping entry synchronization active, that the optional `{comment_mastodon}` visitor opt-in is shown only while local comments may be exported, that FlatPress/admin comments with a stored `LOGGEDIN` marker export without a public visitor marker, that visitor opt-ins are persisted even before credentials are complete, that local visitor comments without a stored opt-in marker stay local even when saved during an admin session, and that opted-in visitor replies are not blocked by non-opted-in local parents, and that the optional explicit one-way mode blocks Mastodon-to-FlatPress imports while preserving FlatPress-to-Mastodon exports, keeps hidden import settings intact in the admin save path, hides import-only admin UI output, hides import-only companion-plugin diagnostics while keeping export helpers, re-exports local objects after remote deletion, and keeps locally deleted imported external Mastodon replies tombstoned even when the remote author edits the reply before the next content sync. It also verifies that Mastodon instance capability detection prefers machine-readable `api_versions[mastodon]`, handles nightly version strings, preserves compact `configuration.accounts` snapshots, loads the widget stylesheet through `plugin_mastodon_head()` as a versioned `res/mastodon.css` asset, refreshes the profile-widget cache during automatic and manual one-way sync paths, sends `exclude_direct=true` only when account-status support is known, retries without it for compatible-server rejections, ignores notification fallback payloads when the normal mention status is present, preserves long remote media descriptions, avoids repeated failed `/api/v2/instance` requests within one PHP request, keeps safe defaults when instance information is temporarily unavailable, version-gates unattached media cleanup deletes, and renders the compact Mastodon profile widget solely from a local public profile cache with a locally stored avatar.

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

## Locally deleted imported remote replies

When a FlatPress admin deletes a comment that originally came from a Mastodon reply (`source=remote`), the harness now treats that deletion as a local ignore decision. The comment-deleted hook must set `comment_tombstones`, remove the reverse `comments_remote` mapping and avoid queuing a Mastodon `DELETE` for a status that may belong to another account. Regression coverage verifies both the unchanged reply and the edited-remote-reply case so a later `/api/v1/statuses/:id/context` pass cannot re-create the local comment, and it preserves `source=local` ownership for already exported FlatPress comments seen again in the context response.

The deletion-sync cleanup regression also builds a legacy pending state with a missing local `source=remote` comment. It must mark the remote status as locally ignored, clear the mapping, leave `deleted_remote_comments` unchanged and record no outbound `DELETE` request.

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

## Full static test catalog

| Line  | Static test name                                                                                                                                                |
| ----- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 3201  | Simulation summary mode shortens verbose JSON details                                                                                                           |
| 3213  | Simulation final summary reports exit code and status counters                                                                                                  |
| 3234  | Simulation sandbox excludes live fp-content/content by default                                                                                                  |
| 3245  | Simulation sandbox can include live fp-content/content only when explicitly requested                                                                           |
| 3269  | Entry file scanner skips comment directories while keeping real FlatPress entries                                                                               |
| 3328  | Scheduled direct YY/MM scanner keeps active-window entries and dirty-comment parents                                                                            |
| 3377  | Scheduled direct scanner honors the 7-day admin window at parse stage                                                                                           |
| 3412  | Scheduled direct scanner honors the 14-day admin window across a month boundary                                                                                 |
| 3447  | Scheduled direct scanner honors the 30-day admin window across a year boundary                                                                                  |
| 3481  | Scheduled direct scanner treats more than three dirty entries as mandatory candidates                                                                           |
| 3521  | Scheduled direct scanner treats more than three dirty-comment parents as mandatory candidates                                                                   |
| 3548  | Manual force sync keeps the direct all-YY/MM scanner repair path                                                                                                |
| 3572  | Mastodon companion-plugin detection uses FlatPress central enabled-plugin state                                                                                 |
| 3581  | Mastodon companion-plugin detection respects FlatPress plugin_exists() for missing configured plugins                                                           |
| 3594  | Mastodon admin companion-plugin diagnostics cover BBCode, PhotoSwipe, AudioVideo, Tag and Emoticons                                                             |
| 3624  | One-way companion-plugin diagnostics hide import-only helpers and describe export helpers                                                                       |
| 3643  | FlatPress locale is normalized to a Mastodon language code                                                                                                      |
| 3661  | Mastodon HTML -> FlatPress BBCode                                                                                                                               |
| 3682  | Mastodon mention noise is cleaned for FlatPress                                                                                                                 |
| 3687  | Imported Mastodon entry keeps the Mastodon status link on a new line with target blank                                                                          |
| 3694  | Imported Mastodon status footer BBCode opens the single toot in a new tab                                                                                       |
| 3700  | Imported Mastodon status footer attributes are stripped for outbound Mastodon text                                                                              |
| 3708  | Imported Mastodon status footer renders target blank HTML for the single toot                                                                                   |
| 3718  | Mastodon emojis become FlatPress emoticons                                                                                                                      |
| 3726  | FlatPress BBCode -> Mastodon text                                                                                                                               |
| 3734  | FlatPress URLs lose HTML-like attributes and omit localhost links                                                                                               |
| 3812  | Comment author target blank is limited to external URLs                                                                                                         |
| 3827  | FlatPress entry tags become a Mastodon hashtag footer                                                                                                           |
| 3852  | Mastodon tags become FlatPress tag BBCode without a visible hashtag footer                                                                                      |
| 3875  | Removing Mastodon tags removes FlatPress tag BBCode on update                                                                                                   |
| 3888  | FlatPress entry without tag BBCode has no Mastodon hashtag footer                                                                                               |
| 3901  | Mastodon head metadata uses the configured instance URL and username                                                                                            |
| 3914  | Mastodon head metadata tolerates usernames stored with a leading at-sign                                                                                        |
| 3927  | Mastodon head metadata stays silent when no username is configured                                                                                              |
| 3941  | Actual FlatPress comment exports as readable Mastodon reply                                                                                                     |
| 3951  | FlatPress comment export appends the public comment link on a new line                                                                                          |
| 3962  | FlatPress entry export converts Emoticons plugin shortcodes in titles and bodies to Unicode emoji for Mastodon                                                  |
| 3981  | FlatPress comment export converts Emoticons plugin shortcodes in author lines and bodies to Unicode emoji for Mastodon                                          |
| 3999  | Long local entry export is limited even without a public permalink                                                                                              |
| 4008  | FlatPress single-image entry exposes one uploadable media item                                                                                                  |
| 4018  | FlatPress gallery entry exposes uploadable media items and keeps raw gallery BBCode out of Mastodon text                                                        |
| 4071  | FlatPress AudioVideo entry exposes described audio/video media items and strips raw player BBCode from Mastodon text                                            |
| 4091  | AudioVideo BBCode parser renders optional description endtags as media attributes and keeps legacy single tags working                                          |
| 4127  | Mastodon media upload sends AudioVideo files with descriptions and video thumbnail                                                                              |
| 4152  | Mastodon media planner exports only images when an entry mixes image, audio and video media                                                                     |
| 4163  | Mastodon media planner exports exactly one audio attachment when an entry mixes audio and video media                                                           |
| 4177  | Mastodon media planner exports one video and keeps its poster as an upload thumbnail only                                                                       |
| 4190  | Mastodon media planner keeps multiple images up to the Mastodon media limit                                                                                     |
| 4219  | Mastodon export uploads only the selected audio item from an audio/video FlatPress entry                                                                        |
| 4249  | Mastodon export sends a video poster as upload thumbnail instead of a second media ID                                                                           |
| 4281  | Mastodon single image imports into a FlatPress image tag and stored file                                                                                        |
| 4305  | Mastodon import preserves long remote media descriptions without corrupting FlatPress image markup                                                              |
| 4349  | Mastodon multiple images import into a FlatPress gallery with captions                                                                                          |
| 4398  | Mastodon audio and video attachments import into FlatPress AudioVideo BBCode and stored files                                                                   |
| 4437  | Mastodon audio/video import retries alternate direct media URLs when the first download candidate fails                                                         |
| 4451  | First sync is immediately due                                                                                                                                   |
| 4454  | Next day after 23:00 is due                                                                                                                                     |
| 4455  | Before configured time is not due                                                                                                                               |
| 4476  | Scheduled synchronization due checks use stored UTC time regardless of PHP default timezone                                                                     |
| 4498  | Pending deletion synchronization due checks use UTC cooldowns regardless of PHP default timezone                                                                |
| 4529  | Admin state timestamps are formatted with FlatPress timeoffset regardless of PHP default timezone                                                               |
| 4544  | Scheduled content synchronization uses a short file/APCu cooldown guard                                                                                         |
| 4561  | Full Mastodon state fallback is not stored in APCu                                                                                                              |
| 4593  | Central Mastodon rate-limit guard stops requests after the per-run request budget                                                                               |
| 4627  | Central Mastodon rate-limit guard stops media uploads after the per-run media budget                                                                            |
| 4651  | Central Mastodon rate-limit guard stops status deletions after the per-run delete budget                                                                        |
| 4677  | Cached Mastodon versions before 4.4 delete statuses without the delete_media query parameter                                                                    |
| 4701  | Status deletion retries without delete_media when an older Mastodon server rejects the query parameter                                                          |
| 4731  | Persistent Mastodon media-upload window budget is shared across forced runs                                                                                     |
| 4761  | Persistent media-upload window budget is written to the synchronization last error                                                                              |
| 4788  | Persistent Mastodon delete window budget is shared across forced runs                                                                                           |
| 4812  | Persistent delete window budget is written to the synchronization last error                                                                                    |
| 4839  | Persistent Mastodon account-status paging window budget is shared across forced runs                                                                            |
| 4862  | Persistent account-status paging window budget is written to the synchronization last error                                                                     |
| 4885  | Admin synchronization time converts stored UTC to FlatPress local time and back                                                                                 |
| 4908  | Admin synchronization time conversion supports fractional FlatPress offsets                                                                                     |
| 4924  | Sync start date normalization accepts valid ISO dates                                                                                                           |
| 4930  | Local and remote date helpers derive stable date keys                                                                                                           |
| 4939  | Remote-to-local update toggle normalizes safely                                                                                                                 |
| 4950  | Comment-as-entry import toggle normalizes safely                                                                                                                |
| 4972  | Comment/reply synchronization disable toggle normalizes and saves safely                                                                                        |
| 5003  | Mastodon comment opt-in is required only when local comments may be exported                                                                                    |
| 5029  | Mastodon comment opt-in markup is shown only while comment/reply sync is active                                                                                 |
| 5046  | State entry mapping created                                                                                                                                     |
| 5047  | State comment mapping created                                                                                                                                   |
| 5049  | State comment-to-reply opt-in marker created                                                                                                                    |
| 5050  | Local visitor comment without Mastodon opt-in is not eligible for Mastodon export                                                                               |
| 5101  | Mastodon runtime files in fp-content/plugin_mastodon use FlatPress FILE_PERMISSIONS                                                                             |
| 5140  | Initial local thread comment/reply sync is enabled                                                                                                              |
| 5299  | OAuth code exchange                                                                                                                                             |
| 5307  | State cache stays fresh after writing state.json                                                                                                                |
| 5328  | Pretty-printed legacy state.json remains readable after compact-state optimization                                                                              |
| 5376  | State write persists compact split state.json while preserving round-trip mappings                                                                              |
| 5441  | Split comment shards support bounded per-entry state reads                                                                                                      |
| 5465  | Partial split-state writes preserve unloaded comment shards                                                                                                     |
| 5509  | Legacy inline comment state migrates to per-entry comment shards on write                                                                                       |
| 5584  | Legacy inline migration preserves non-contiguous comments and creates a migration backup                                                                        |
| 5643  | Comment-shard diagnostics detect stale metadata and repair the reverse index from shards                                                                        |
| 5664  | CLI comment-shard diagnostics entry point returns a machine-checkable status                                                                                    |
| 5699  | Shard-write failures leave the main state unchanged and return failure                                                                                          |
| 5738  | Main-state write failures after shard writes remain repairable from shard files                                                                                 |
| 5775  | Legacy combined stats are migrated into separate content and deletion counters                                                                                  |
| 5799  | Instance configuration cache avoids repeated /api/v2/instance requests                                                                                          |
| 5826  | Nightly Mastodon versions use cached api_versions for status edit and delete capabilities                                                                       |
| 5849  | Machine-readable api_versions are preferred over complex human-readable Mastodon version strings                                                                |
| 5878  | api_versions below delete_media support suppress the delete_media query even with a nightly version string                                                      |
| 5904  | api_versions below unattached media delete support skip uploaded media cleanup DELETE requests                                                                  |
| 5927  | Cached Mastodon versions before 4.4 skip unattached media DELETE cleanup requests                                                                               |
| 5961  | Unknown unattached media delete capability stays best-effort without an instance lookup                                                                         |
| 6000  | Mastodon API version 4 deletes unattached uploaded media during cleanup                                                                                         |
| 6031  | Mastodon 4.6 configuration.accounts limits survive compact instance snapshots                                                                                   |
| 6063  | Mastodon 4.6.0-nightly account-status import sends exclude_direct for privacy                                                                                   |
| 6095  | Mastodon API version 10 account-status import sends exclude_direct even on forked version strings                                                               |
| 6131  | Account-status import retries without exclude_direct when a compatible server rejects the parameter                                                             |
| 6166  | Cached Mastodon versions before 4.6 keep account-status import compatible without exclude_direct                                                                |
| 6195  | Failed instance-information lookups are negatively cached per request and fall back to defaults                                                                 |
| 6232  | Mastodon URL budgeting respects characters_reserved_per_url when limiting plain-text exports                                                                    |
| 6257  | Configured credentials verify (read-only smoke test)                                                                                                            |
| 6269  | Entry export uses clean Mastodon text and suppresses localhost permalinks                                                                                       |
| 6277  | Comment export is reply-like, localized and emoji-aware                                                                                                         |
| 6314  | Remote entry imported through sync with Mastodon media visible in FlatPress                                                                                     |
| 6320  | Deletion sync is queued for a follow-up request instead of running inside the content sync request                                                              |
| 6326  | Content sync updates the synchronization timestamp and counters while leaving the deletion timestamp for the follow-up request                                  |
| 6345  | Private mention status is skipped during Mastodon import                                                                                                        |
| 6351  | Remote imported entry keeps a stable FlatPress date                                                                                                             |
| 6357  | Remote comment imported through sync                                                                                                                            |
| 6363  | Nested remote comment keeps parent mapping metadata                                                                                                             |
| 6369  | Private mention reply is skipped during Mastodon import                                                                                                         |
| 6407  | Entry export creates a top-level Mastodon status                                                                                                                |
| 6413  | Comment to entry exports with in_reply_to_id on the entry status                                                                                                |
| 6419  | Comment to comment exports with in_reply_to_id on the parent reply                                                                                              |
| 6425  | Entry and comment exports include the configured Mastodon language code                                                                                         |
| 6444  | Deletion sync waits at least five minutes after a completed content sync                                                                                        |
| 6496  | Create and update status requests include the configured Mastodon language code                                                                                 |
| 6539  | Initial Mastodon media uploads include the attachment description in POST /api/v2/media                                                                         |
| 6571  | Unchanged entry media is reused without a new upload when only the post text changes                                                                            |
| 6644  | Mastodon 4.1+ updates changed image descriptions through status media_attributes without re-uploading media                                                     |
| 6681  | Older Mastodon versions fall back to a fresh upload when only the media description changes                                                                     |
| 6768  | Full local-to-remote sync reuses stored media IDs when the attachments did not change                                                                           |
| 6868  | Full sync updates changed media descriptions through status media_attributes on Mastodon 4.1+                                                                   |
| 6991  | Batch entry export keeps older FlatPress entries below newer ones on Mastodon                                                                                   |
| 7094  | New local comment on an already synchronized older entry is exported to Mastodon                                                                                |
| 7173  | Disabled comment/reply sync keeps local comments off Mastodon                                                                                                   |
| 7252  | Disabled comment/reply sync skips remote reply deletion follow-up                                                                                               |
| 7348  | Known synchronized entry mappings older than the sync start date do not trigger context refreshes                                                               |
| 7422  | Scheduled content synchronization respects the automatic recent-content window                                                                                  |
| 7448  | Productive content-sync workset loads only active scheduled comment shards                                                                                      |
| 7507  | Normal manual synchronization bypasses the daily due check but still respects the automatic window                                                              |
| 7569  | Explicit full manual synchronization bypasses the automatic window while keeping normal limits                                                                  |
| 7648  | Older changed mapped FlatPress entries are synchronized through the dirty queue                                                                                 |
| 7700  | Post-success comment hook queues older changed mapped comments                                                                                                  |
| 7730  | Scheduled sync updates older dirty comments through direct YY/MM dirty-parent candidates                                                                        |
| 7777  | Remote-write guard suppresses dirty tracking for Mastodon-owned entry_save calls                                                                                |
| 7866  | Large scheduled dirty-tracking sync parses only active-window and dirty entries                                                                                 |
| 7949  | Optional old-thread reply checks rotate through known synchronized threads                                                                                      |
| 7990  | Disabled old-thread reply checks do not refresh known synchronized entry contexts                                                                               |
| 8071  | Disabled comment/reply sync imports Mastodon entries without fetching reply contexts or notifications                                                           |
| 8150  | Automatic scheduled synchronization rotates known synchronized threads through the full sync path                                                               |
| 8236  | Notification hints import a new reply on an old mapped Mastodon entry without context rotation                                                                  |
| 8252  | Notification fallback payloads are ignored when a normal mention status is available                                                                            |
| 8321  | Notification hints import a new reply on an old mapped Mastodon reply with reply metadata                                                                       |
| 8426  | Notification context hints use the old-thread budget before normal rotation                                                                                     |
| 8477  | Reply-notification polling is skipped until read:notifications is authorized                                                                                    |
| 8600  | Remote updates do not overwrite existing local content when the toggle is disabled                                                                              |
| 8620  | Remote updates overwrite existing local content when the toggle is enabled                                                                                      |
| 8645  | FlatPress single-image entry exports Mastodon media_ids and strips raw image BBCode                                                                             |
| 8656  | FlatPress gallery entry exports Mastodon media_ids up to the instance limit and strips raw gallery BBCode                                                       |
| 8794  | Sync start date filters local exports by entry and comment date                                                                                                 |
| 8880  | Sync start date filters remote imports by status and reply date                                                                                                 |
| 8972  | Remote Mastodon imports honor the FlatPress timeoffset for stored entry/comment dates and ordering keys                                                         |
| 9043  | Remote sync start filtering respects the FlatPress timeoffset near midnight                                                                                     |
| 9108  | Sync reports local export failures instead of silently succeeding                                                                                               |
| 9126  | Shared-request synchronization refreshes the PHP execution budget for long Mastodon work                                                                        |
| 9209  | Synchronized local comments are not imported as duplicate entries from Mastodon by default                                                                      |
| 9301  | Synchronized local comments may be imported as entries when the toggle is enabled                                                                               |
| 9436  | Local FlatPress comments on imported Mastodon entries sync back to Mastodon, including nested replies                                                           |
| 9512  | Asynchronous Mastodon media uploads are polled until the attachment is ready                                                                                    |
| 9536  | Long-running Mastodon media uploads refresh the PHP execution budget for upload and polling                                                                     |
| 9608  | Slow asynchronous Mastodon video uploads keep polling beyond the old short retry window                                                                         |
| 9674  | Mastodon audio uploads keep polling even when pending responses have no preview_url                                                                             |
| 9739  | Follow-up deletion sync removes remote Mastodon content after local FlatPress deletion                                                                          |
| 9764  | Follow-up deletion sync updates last_deletion_run without overwriting the last synchronization timestamp or content counters                                    |
| 9820  | Deletion sync falls back to DELETE without delete_media for older Mastodon servers                                                                              |
| 9883  | Follow-up deletion sync removes mirrored FlatPress content after remote Mastodon deletion                                                                       |
| 9938  | Deletion sync skips old locally deleted mappings outside the sync start date window                                                                             |
| 9988  | Deletion sync skips old remotely mirrored mappings outside the sync start date window                                                                           |
| 10045 | Scheduled deletion sync skips remote lookups outside the automatic scheduled window                                                                             |
| 10118 | Scheduled deletion sync still propagates old local deletions outside the automatic window                                                                       |
| 10179 | Large scheduled deletion syncs resume from the saved entry cursor                                                                                               |
| 10263 | Large scheduled deletion syncs resume from the saved comment cursor inside one entry shard                                                                      |
| 10308 | Disabling deletion synchronization clears pending delete work without issuing deletion requests                                                                 |
| 10328 | Content sync before deletion sync protects a locally deleted exported FlatPress comment from stale re-import                                                    |
| 10347 | Deletion sync after the protected content sync reattaches the imported descendant reply to the synchronized entry status and keeps a later verification pending |
| 10390 | Normal manual partial content sync keeps an old locally deleted exported comment tombstoned without stale re-import                                             |
| 10429 | Automatic scheduled partial content sync keeps an old locally deleted exported comment tombstoned without stale re-import                                       |
| 10466 | Remote import defensively tombstones a missing source=local comment when the deletion hook was bypassed                                                         |
| 10493 | Comment mapping rejects duplicate remote ids and removes the previous reverse id on a legitimate remap                                                          |
| 10520 | Deletion sync keeps imported descendants for a later follow-up verification when Mastodon still returns the child reply once                                    |
| 10537 | Tombstones prevent re-importing the deleted exported parent comment while descendant rechecks are still pending                                                 |
| 10550 | A later follow-up deletion sync removes the imported descendant once Mastodon reports the child reply missing                                                   |
| 10581 | Content sync before deletion sync also protects a locally deleted exported FlatPress comment when the imported descendant reply already has its own child       |
| 10601 | Deletion sync after the protected content sync keeps the descendant child below the reattached reply and verifies both remote replies in the same pass          |
| 10641 | Deletion sync removes imported descendants immediately when Mastodon already reports the child reply missing in the first follow-up pass                        |
| 10672 | Hybrid descendant rechecks keep only the direct child queued after the first stale follow-up pass of a deeper reply chain                                       |
| 10691 | Hybrid descendant rechecks remove the direct child and its direct child within the same targeted follow-up request                                              |
| 10716 | Locally deleted imported external Mastodon reply is tombstoned immediately and not re-imported unchanged                                                        |
| 10741 | Locally deleted imported external Mastodon reply is not re-imported after the remote author edits it                                                            |
| 10764 | Deletion sync treats locally deleted imported remote replies as local ignore decisions without remote DELETE                                                    |
| 10846 | Remote Mastodon comment updates increment the local-comment update counter instead of the entry counter                                                         |
| 10878 | Admin assignment exposes split sync counters, local admin timestamps, and the deletion-sync option                                                              |
| 10968 | Admin maintenance actions are separated into a dedicated template reached from the main plugin page                                                             |
| 11059 | Media upload cleanup deletes already uploaded attachments when a later media upload fails                                                                       |
| 11126 | Media upload cleanup deletes uploaded attachments when final status creation fails                                                                              |
| 11184 | OAuth scope discovery prefers the profile scope on current Mastodon instances                                                                                   |
| 11230 | OAuth scope discovery falls back to read:accounts plus notifications on older Mastodon instances                                                                |
| 11282 | Existing registered apps keep the legacy read:accounts scope until they are re-registered                                                                       |
| 11373 | Instance information refresh persists a compact snapshot including the exact Mastodon version                                                                   |
| 11419 | Admin assignment exposes cached instance-information rows without triggering another live instance request                                                      |
| 11436 | Changing the configured instance URL invalidates the saved instance-information snapshot                                                                        |
| 11474 | Sync-related instance limit helpers reuse the stored instance snapshot without another /api/v2/instance request                                                 |
| 11496 | Manual sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                            |
| 11514 | Replies to FlatPress comments on remote-sourced entries are exported as replies to the parent Mastodon reply                                                    |
| 11544 | Unsynchronized local parent comment is exported before local child reply                                                                                        |
| 11564 | Non-forced sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                        |
| 11587 | Manual sync exports Emoticons plugin shortcodes from FlatPress entries to Unicode emoji in Mastodon status requests                                             |
| 11603 | Manual sync exports Emoticons plugin shortcodes from FlatPress comments to Unicode emoji in Mastodon reply requests                                             |
| 11624 | Manual sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled         |
| 11650 | Non-forced sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled     |
| 11673 | Another Mastodon member reply to an exported FlatPress comment is still imported as a FlatPress comment when comment-as-entry import is enabled                 |
| 11690 | Disabling the quote option imports a Mastodon reply to an exported FlatPress comment without a leading quote block                                              |
| 11704 | Imported Mastodon replies to another Mastodon reply quote the replied-to Mastodon user and text by default                                                      |
| 11723 | Disabling the quote option imports Mastodon reply-to-reply comments without a leading quote block                                                               |
| 11737 | Manual sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                          |
| 11761 | Non-forced sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                      |
| 11814 | Scheduler state is written as a compact summary without full mapping arrays                                                                                     |
| 11833 | Fresh scheduler-state read uses the APCu-capable FlatPress I/O path and does not load full state.json                                                           |
| 11862 | Stale scheduler-state falls back to the full state and rebuilds the summary                                                                                     |
| 11883 | Manual admin synchronization still loads the full state before reporting configuration errors                                                                   |
| 11902 | sync.log uses append-only writes with size-based rotation                                                                                                       |
| 11928 | Large skip volumes are logged as aggregate summaries                                                                                                            |
| 11941 | Small 300x10 state keeps scheduler-state compact and disables full APCu fallback                                                                                |
| 11963 | Fresh small scheduler-state read avoids full state.json and uses APCu-capable file I/O                                                                          |
| 11986 | Large 3000x10 state keeps scheduler-state compact and disables full APCu fallback                                                                               |
| 12009 | Fresh large scheduler-state read avoids full state.json and uses APCu-capable file I/O                                                                          |
| 12125 | Automatic scheduled sync exports a new current comment on an old mapped entry through dirty_comments                                                            |
| 12223 | Authenticated FlatPress comments export after a stored LOGGEDIN marker without visitor opt-in                                                                   |
| 12334 | Admin session does not grant Mastodon export to visitor comments without opt-in                                                                                 |
| 12449 | CommentCenter preserves visitor Mastodon opt-in until admin approval and export                                                                                 |
| 12557 | Visitor opt-in is stored without complete credentials and later permits comment export                                                                          |
| 12662 | Opted-in visitor replies export even when their local parent has no Mastodon opt-in                                                                             |
| 12721 | One-way mode option is disabled by default, normalized, assigned to admin UI, templated and translated                                                          |
| 12761 | One-way admin save preserves hidden import options while keeping one-way mode enabled                                                                           |
| 12778 | One-way admin save preserves hidden import options when one-way mode is disabled in the same submit                                                             |
| 12798 | Bidirectional admin save still stores visible unchecked import options as disabled                                                                              |
| 12830 | One-way admin template hides import controls, notification hints and import-only counters                                                                       |
| 12856 | One-way admin template keeps export, OAuth, instance, token, state and deletion outputs visible                                                                 |
| 12872 | One-way admin assignment hides import-only companion plugins and uses one-way companion intro                                                                   |
| 12978 | One-way mode blocks Mastodon-to-FlatPress imports while FlatPress-to-Mastodon export still runs                                                                 |
| 13065 | Automatic one-way content sync refreshes the Mastodon widget profile cache without remote import reads                                                          |
| 13140 | One-way deletion sync keeps local content, removes stale remote mappings and queues re-export                                                                   |
| 13215 | One-way content sync re-exports local objects whose remote mappings were unlinked after remote deletion                                                         |
| 13284 | One-way pending descendant rechecks keep local comments and queue them for re-export instead of deleting them                                                   |
| 13305 | Mastodon widget remains hidden and performs no HTTP requests while its local profile cache is missing                                                           |
| 13348 | Mastodon widget profile cache is refreshed from verify_credentials and stores only public profile data plus a local avatar                                      |
| 13373 | Mastodon widget renders compact local-cache markup without remote API calls or inline CSS                                                                       |
| 13397 | Mastodon widget stylesheet is loaded by plugin_mastodon_head as a versioned CSS asset                                                                           |
| 13412 | Mastodon widget profile refresh reuses the cached avatar when the Mastodon avatar URL is unchanged                                                              |
| 13439 | Mastodon widget uses a localized fallback avatar alt text for Mastodon 4.0-4.5 account payloads without avatar_description                                      |
| 13458 | Mastodon widget hides incomplete local profile caches instead of falling back to remote avatar URLs                                                             |
| 13483 | Mastodon widget is registered and translated in all FlatPress plugin language files                                                                             |

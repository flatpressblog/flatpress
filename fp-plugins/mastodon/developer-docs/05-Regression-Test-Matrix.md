# 05 - Regression Matrix

## Harness model
`simulate_mastodon_plugin.php` loads the real Mastodon plugin and simulates the FlatPress and Mastodon boundaries. The plugin code itself is not replaced. The HTTP layer, fixture content, media files and some FlatPress services are controlled by the harness so behavior is deterministic.
Normal run checked for this documentation set after the one-way admin UI update:
```text
php simulate_mastodon_plugin.php
Exit-code: 0
[OK]: 220
[FAIL]: 0
[WARN]: 0
[SKIP]: 0
```
Static `test_result()` calls found in the script: `221`.
One test is optional and only runs with `--live-auth`:

```text
Configured credentials verify (read-only smoke test)
```
## Category summary

The catalog below is generated from all static `test_result()` calls and therefore remains the canonical line-reference list for the consistency checker.

| Category                           | Static tests |
| ---------------------------------- | ------------ |
| Regular simulation tests           | 220          |
| Optional live/auth smoke           | 1            |
| Total static `test_result()` calls | 221          |

## What the harness proves well

The harness now also checks that advanced comment-shard maintenance controls are not embedded in the main settings template, are available from a dedicated maintenance template, that notification-based reply hints import old-thread replies before the slower rotation fallback, and that the optional explicit one-way mode blocks Mastodon-to-FlatPress imports while preserving FlatPress-to-Mastodon exports, keeps hidden import settings intact in the admin save path, hides import-only admin UI output, hides import-only companion-plugin diagnostics while keeping export helpers, and re-exports local objects after remote deletion.

| Area                                     | What is real                                                                                                                 | What is simulated                                                               |
| ---------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------- |
| State read/write and scheduler summaries | Plugin state logic and actual files in the sandbox.                                                                          | Test-specific content directory and fixture state.                              |
| Mastodon API behavior                    | Plugin API selection, payload building, fallback handling and response parsing.                                              | HTTP responses and remote status/media payloads.                                |
| Media handling                           | Local extraction, MIME/type decisions, media-family selection, upload planning, thumbnail payloads, reuse and cleanup logic. | Actual remote processing, CDN behavior and instance-specific media transcoding. |
| Text conversion                          | Real conversion helpers.                                                                                                     | Fixture strings and expected results.                                           |
| Deletion sync                            | Real deletion orchestration and status delete fallback.                                                                      | Remote deletion responses and missing-status scenarios.                         |
| Admin assignment                         | Real admin assignment function and Smarty variable preparation.                                                              | Test harness Smarty object.                                                     |
| Entry scanner pruning                    | Real direct `YY/MM/entry*.txt` collector, scheduled-window month pruning and dirty-parent augmentation.                      | Synthetic trees with entry-like files below `comments/` plus targeted fixtures. |

## Sandbox-content isolation policy
The harness no longer copies live `fp-content/content` into the simulation sandbox by default. This keeps regression runs independent of production-like blog size and avoids shared-host request timeouts before the first assertion. The sandbox still copies configuration and plugin files, creates an empty `fp-content/content` directory, and then seeds deterministic entry/comment/media fixtures.

Explicit live-content smoke coverage remains available with `--include-live-content` or `SIMULATE_MASTODON_INCLUDE_LIVE_CONTENT=1`, but it is intentionally outside the normal deterministic regression run.

## Large-state memory policy
The simulation verifies that `state.json` is written as compact JSON, then runs a small always-on scheduler-state test (`300x10`) and a heavy `3000x10` test. The heavy test is guarded before the synthetic state is built, because creating the large PHP array and full JSON string can exceed 128 MiB shared-hosting limits. Non-CI low-memory runs emit `[WARN]`; CI runs first try to raise `memory_limit` to 384M and emit `[SKIP]` if that is not possible. A skipped or warned heavy test does not make the regression run fail, but the coverage should be repeated in a better-resourced environment. The skip branch can be exercised by setting `CI=1` and `SIMULATE_MASTODON_DISABLE_MEMORY_RAISE=1` together with a low `memory_limit`.

## What still needs real-world testing
- Real Mastodon instances with different configuration values, moderation modes and server-side media validation messages.
- Real audio/video/image processing latency, storage/CDN failures, thumbnail generation and server-specific MIME policies.
- Webhost-specific file locking, permissions and long request timeouts.
- OAuth app registration on instances with custom policies.
- Browser/Admin UI rendering with the actual FlatPress theme and Smarty version.

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
| 2801  | Simulation summary mode shortens verbose JSON details                                                                                                           |
| 2813  | Simulation final summary reports exit code and status counters                                                                                                  |
| 2834  | Simulation sandbox excludes live fp-content/content by default                                                                                                  |
| 2845  | Simulation sandbox can include live fp-content/content only when explicitly requested                                                                           |
| 2869  | Entry file scanner skips comment directories while keeping real FlatPress entries                                                                               |
| 2928  | Scheduled direct YY/MM scanner keeps active-window entries and dirty-comment parents                                                                            |
| 2977  | Scheduled direct scanner honors the 7-day admin window at parse stage                                                                                           |
| 3012  | Scheduled direct scanner honors the 14-day admin window across a month boundary                                                                                 |
| 3047  | Scheduled direct scanner honors the 30-day admin window across a year boundary                                                                                  |
| 3081  | Scheduled direct scanner treats more than three dirty entries as mandatory candidates                                                                           |
| 3121  | Scheduled direct scanner treats more than three dirty-comment parents as mandatory candidates                                                                   |
| 3148  | Manual force sync keeps the direct all-YY/MM scanner repair path                                                                                                |
| 3172  | Mastodon companion-plugin detection uses FlatPress central enabled-plugin state                                                                                 |
| 3181  | Mastodon companion-plugin detection respects FlatPress plugin_exists() for missing configured plugins                                                           |
| 3194  | Mastodon admin companion-plugin diagnostics cover BBCode, PhotoSwipe, AudioVideo, Tag and Emoticons                                                             |
| 3224  | One-way companion-plugin diagnostics hide import-only helpers and describe export helpers                                                                       |
| 3243  | FlatPress locale is normalized to a Mastodon language code                                                                                                      |
| 3261  | Mastodon HTML -> FlatPress BBCode                                                                                                                               |
| 3282  | Mastodon mention noise is cleaned for FlatPress                                                                                                                 |
| 3287  | Imported Mastodon entry keeps the Mastodon link on a new line                                                                                                   |
| 3295  | Mastodon emojis become FlatPress emoticons                                                                                                                      |
| 3303  | FlatPress BBCode -> Mastodon text                                                                                                                               |
| 3311  | FlatPress URLs lose HTML-like attributes and omit localhost links                                                                                               |
| 3323  | FlatPress entry tags become a Mastodon hashtag footer                                                                                                           |
| 3348  | Mastodon tags become FlatPress tag BBCode without a visible hashtag footer                                                                                      |
| 3371  | Removing Mastodon tags removes FlatPress tag BBCode on update                                                                                                   |
| 3384  | FlatPress entry without tag BBCode has no Mastodon hashtag footer                                                                                               |
| 3397  | Mastodon head metadata uses the configured instance URL and username                                                                                            |
| 3410  | Mastodon head metadata tolerates usernames stored with a leading at-sign                                                                                        |
| 3423  | Mastodon head metadata stays silent when no username is configured                                                                                              |
| 3437  | Actual FlatPress comment exports as readable Mastodon reply                                                                                                     |
| 3447  | FlatPress comment export appends the public comment link on a new line                                                                                          |
| 3458  | FlatPress entry export converts Emoticons plugin shortcodes in titles and bodies to Unicode emoji for Mastodon                                                  |
| 3477  | FlatPress comment export converts Emoticons plugin shortcodes in author lines and bodies to Unicode emoji for Mastodon                                          |
| 3495  | Long local entry export is limited even without a public permalink                                                                                              |
| 3504  | FlatPress single-image entry exposes one uploadable media item                                                                                                  |
| 3514  | FlatPress gallery entry exposes uploadable media items and keeps raw gallery BBCode out of Mastodon text                                                        |
| 3567  | FlatPress AudioVideo entry exposes described audio/video media items and strips raw player BBCode from Mastodon text                                            |
| 3587  | AudioVideo BBCode parser renders optional description endtags as media attributes and keeps legacy single tags working                                          |
| 3623  | Mastodon media upload sends AudioVideo files with descriptions and video thumbnail                                                                              |
| 3648  | Mastodon media planner exports only images when an entry mixes image, audio and video media                                                                     |
| 3659  | Mastodon media planner exports exactly one audio attachment when an entry mixes audio and video media                                                           |
| 3673  | Mastodon media planner exports one video and keeps its poster as an upload thumbnail only                                                                       |
| 3686  | Mastodon media planner keeps multiple images up to the Mastodon media limit                                                                                     |
| 3715  | Mastodon export uploads only the selected audio item from an audio/video FlatPress entry                                                                        |
| 3745  | Mastodon export sends a video poster as upload thumbnail instead of a second media ID                                                                           |
| 3777  | Mastodon single image imports into a FlatPress image tag and stored file                                                                                        |
| 3814  | Mastodon multiple images import into a FlatPress gallery with captions                                                                                          |
| 3863  | Mastodon audio and video attachments import into FlatPress AudioVideo BBCode and stored files                                                                   |
| 3902  | Mastodon audio/video import retries alternate direct media URLs when the first download candidate fails                                                         |
| 3916  | First sync is immediately due                                                                                                                                   |
| 3919  | Next day after 23:00 is due                                                                                                                                     |
| 3920  | Before configured time is not due                                                                                                                               |
| 3941  | Scheduled synchronization due checks use stored UTC time regardless of PHP default timezone                                                                     |
| 3963  | Pending deletion synchronization due checks use UTC cooldowns regardless of PHP default timezone                                                                |
| 3994  | Admin state timestamps are formatted with FlatPress timeoffset regardless of PHP default timezone                                                               |
| 4009  | Scheduled content synchronization uses a short file/APCu cooldown guard                                                                                         |
| 4026  | Full Mastodon state fallback is not stored in APCu                                                                                                              |
| 4058  | Central Mastodon rate-limit guard stops requests after the per-run request budget                                                                               |
| 4092  | Central Mastodon rate-limit guard stops media uploads after the per-run media budget                                                                            |
| 4116  | Central Mastodon rate-limit guard stops status deletions after the per-run delete budget                                                                        |
| 4142  | Cached Mastodon versions before 4.4 delete statuses without the delete_media query parameter                                                                    |
| 4166  | Status deletion retries without delete_media when an older Mastodon server rejects the query parameter                                                          |
| 4196  | Persistent Mastodon media-upload window budget is shared across forced runs                                                                                     |
| 4226  | Persistent media-upload window budget is written to the synchronization last error                                                                              |
| 4253  | Persistent Mastodon delete window budget is shared across forced runs                                                                                           |
| 4277  | Persistent delete window budget is written to the synchronization last error                                                                                    |
| 4304  | Persistent Mastodon account-status paging window budget is shared across forced runs                                                                            |
| 4327  | Persistent account-status paging window budget is written to the synchronization last error                                                                     |
| 4342  | Admin synchronization time converts stored UTC to FlatPress local time and back                                                                                 |
| 4364  | Admin synchronization time conversion supports fractional FlatPress offsets                                                                                     |
| 4380  | Sync start date normalization accepts valid ISO dates                                                                                                           |
| 4386  | Local and remote date helpers derive stable date keys                                                                                                           |
| 4395  | Remote-to-local update toggle normalizes safely                                                                                                                 |
| 4406  | Comment-as-entry import toggle normalizes safely                                                                                                                |
| 4421  | State entry mapping created                                                                                                                                     |
| 4422  | State comment mapping created                                                                                                                                   |
| 4473  | Mastodon runtime files in fp-content/plugin_mastodon use FlatPress FILE_PERMISSIONS                                                                             |
| 4665  | OAuth code exchange                                                                                                                                             |
| 4673  | State cache stays fresh after writing state.json                                                                                                                |
| 4694  | Pretty-printed legacy state.json remains readable after compact-state optimization                                                                              |
| 4742  | State write persists compact split state.json while preserving round-trip mappings                                                                              |
| 4807  | Split comment shards support bounded per-entry state reads                                                                                                      |
| 4831  | Partial split-state writes preserve unloaded comment shards                                                                                                     |
| 4875  | Legacy inline comment state migrates to per-entry comment shards on write                                                                                       |
| 4950  | Legacy inline migration preserves non-contiguous comments and creates a migration backup                                                                        |
| 5009  | Comment-shard diagnostics detect stale metadata and repair the reverse index from shards                                                                        |
| 5030  | CLI comment-shard diagnostics entry point returns a machine-checkable status                                                                                    |
| 5065  | Shard-write failures leave the main state unchanged and return failure                                                                                          |
| 5104  | Main-state write failures after shard writes remain repairable from shard files                                                                                 |
| 5141  | Legacy combined stats are migrated into separate content and deletion counters                                                                                  |
| 5165  | Instance configuration cache avoids repeated /api/v2/instance requests                                                                                          |
| 5186  | Mastodon URL budgeting respects characters_reserved_per_url when limiting plain-text exports                                                                    |
| 5223  | Entry export uses clean Mastodon text and suppresses localhost permalinks                                                                                       |
| 5231  | Comment export is reply-like, localized and emoji-aware                                                                                                         |
| 5256  | Remote entry imported through sync with Mastodon media visible in FlatPress                                                                                     |
| 5262  | Deletion sync is queued for a follow-up request instead of running inside the content sync request                                                              |
| 5268  | Content sync updates the synchronization timestamp and counters while leaving the deletion timestamp for the follow-up request                                  |
| 5287  | Private mention status is skipped during Mastodon import                                                                                                        |
| 5293  | Remote imported entry keeps a stable FlatPress date                                                                                                             |
| 5299  | Remote comment imported through sync                                                                                                                            |
| 5305  | Nested remote comment keeps parent mapping metadata                                                                                                             |
| 5311  | Private mention reply is skipped during Mastodon import                                                                                                         |
| 5349  | Entry export creates a top-level Mastodon status                                                                                                                |
| 5355  | Comment to entry exports with in_reply_to_id on the entry status                                                                                                |
| 5361  | Comment to comment exports with in_reply_to_id on the parent reply                                                                                              |
| 5367  | Entry and comment exports include the configured Mastodon language code                                                                                         |
| 5386  | Deletion sync waits at least five minutes after a completed content sync                                                                                        |
| 5438  | Create and update status requests include the configured Mastodon language code                                                                                 |
| 5481  | Initial Mastodon media uploads include the attachment description in POST /api/v2/media                                                                         |
| 5513  | Unchanged entry media is reused without a new upload when only the post text changes                                                                            |
| 5586  | Mastodon 4.1+ updates changed image descriptions through status media_attributes without re-uploading media                                                     |
| 5623  | Older Mastodon versions fall back to a fresh upload when only the media description changes                                                                     |
| 5710  | Full local-to-remote sync reuses stored media IDs when the attachments did not change                                                                           |
| 5810  | Full sync updates changed media descriptions through status media_attributes on Mastodon 4.1+                                                                   |
| 5933  | Batch entry export keeps older FlatPress entries below newer ones on Mastodon                                                                                   |
| 6035  | New local comment on an already synchronized older entry is exported to Mastodon                                                                                |
| 6130  | Known synchronized entry mappings older than the sync start date do not trigger context refreshes                                                               |
| 6204  | Scheduled content synchronization respects the automatic recent-content window                                                                                  |
| 6230  | Productive content-sync workset loads only active scheduled comment shards                                                                                      |
| 6289  | Normal manual synchronization bypasses the daily due check but still respects the automatic window                                                              |
| 6351  | Explicit full manual synchronization bypasses the automatic window while keeping normal limits                                                                  |
| 6430  | Older changed mapped FlatPress entries are synchronized through the dirty queue                                                                                 |
| 6482  | Post-success comment hook queues older changed mapped comments                                                                                                  |
| 6512  | Scheduled sync updates older dirty comments through direct YY/MM dirty-parent candidates                                                                        |
| 6559  | Remote-write guard suppresses dirty tracking for Mastodon-owned entry_save calls                                                                                |
| 6648  | Large scheduled dirty-tracking sync parses only active-window and dirty entries                                                                                 |
| 6731  | Optional old-thread reply checks rotate through known synchronized threads                                                                                      |
| 6772  | Disabled old-thread reply checks do not refresh known synchronized entry contexts                                                                               |
| 6838  | Automatic scheduled synchronization rotates known synchronized threads through the full sync path                                                               |
| 6920  | Notification hints import a new reply on an old mapped Mastodon entry without context rotation                                                                  |
| 6993  | Notification hints import a new reply on an old mapped Mastodon reply with reply metadata                                                                       |
| 7098  | Notification context hints use the old-thread budget before normal rotation                                                                                     |
| 7149  | Reply-notification polling is skipped until read:notifications is authorized                                                                                    |
| 7272  | Remote updates do not overwrite existing local content when the toggle is disabled                                                                              |
| 7292  | Remote updates overwrite existing local content when the toggle is enabled                                                                                      |
| 7317  | FlatPress single-image entry exports Mastodon media_ids and strips raw image BBCode                                                                             |
| 7328  | FlatPress gallery entry exports Mastodon media_ids up to the instance limit and strips raw gallery BBCode                                                       |
| 7464  | Sync start date filters local exports by entry and comment date                                                                                                 |
| 7550  | Sync start date filters remote imports by status and reply date                                                                                                 |
| 7642  | Remote Mastodon imports honor the FlatPress timeoffset for stored entry/comment dates and ordering keys                                                         |
| 7713  | Remote sync start filtering respects the FlatPress timeoffset near midnight                                                                                     |
| 7778  | Sync reports local export failures instead of silently succeeding                                                                                               |
| 7796  | Shared-request synchronization refreshes the PHP execution budget for long Mastodon work                                                                        |
| 7879  | Synchronized local comments are not imported as duplicate entries from Mastodon by default                                                                      |
| 7971  | Synchronized local comments may be imported as entries when the toggle is enabled                                                                               |
| 8103  | Local FlatPress comments on imported Mastodon entries sync back to Mastodon, including nested replies                                                           |
| 8179  | Asynchronous Mastodon media uploads are polled until the attachment is ready                                                                                    |
| 8203  | Long-running Mastodon media uploads refresh the PHP execution budget for upload and polling                                                                     |
| 8275  | Slow asynchronous Mastodon video uploads keep polling beyond the old short retry window                                                                         |
| 8341  | Mastodon audio uploads keep polling even when pending responses have no preview_url                                                                             |
| 8406  | Follow-up deletion sync removes remote Mastodon content after local FlatPress deletion                                                                          |
| 8431  | Follow-up deletion sync updates last_deletion_run without overwriting the last synchronization timestamp or content counters                                    |
| 8487  | Deletion sync falls back to DELETE without delete_media for older Mastodon servers                                                                              |
| 8550  | Follow-up deletion sync removes mirrored FlatPress content after remote Mastodon deletion                                                                       |
| 8605  | Deletion sync skips old locally deleted mappings outside the sync start date window                                                                             |
| 8655  | Deletion sync skips old remotely mirrored mappings outside the sync start date window                                                                           |
| 8712  | Scheduled deletion sync skips remote lookups outside the automatic scheduled window                                                                             |
| 8785  | Scheduled deletion sync still propagates old local deletions outside the automatic window                                                                       |
| 8846  | Large scheduled deletion syncs resume from the saved entry cursor                                                                                               |
| 8930  | Large scheduled deletion syncs resume from the saved comment cursor inside one entry shard                                                                      |
| 8975  | Disabling deletion synchronization clears pending delete work without issuing deletion requests                                                                 |
| 8995  | Content sync before deletion sync protects a locally deleted exported FlatPress comment from stale re-import                                                    |
| 9014  | Deletion sync after the protected content sync reattaches the imported descendant reply to the synchronized entry status and keeps a later verification pending |
| 9043  | Deletion sync keeps imported descendants for a later follow-up verification when Mastodon still returns the child reply once                                    |
| 9060  | Tombstones prevent re-importing the deleted exported parent comment while descendant rechecks are still pending                                                 |
| 9073  | A later follow-up deletion sync removes the imported descendant once Mastodon reports the child reply missing                                                   |
| 9104  | Content sync before deletion sync also protects a locally deleted exported FlatPress comment when the imported descendant reply already has its own child       |
| 9124  | Deletion sync after the protected content sync keeps the descendant child below the reattached reply and verifies both remote replies in the same pass          |
| 9164  | Deletion sync removes imported descendants immediately when Mastodon already reports the child reply missing in the first follow-up pass                        |
| 9195  | Hybrid descendant rechecks keep only the direct child queued after the first stale follow-up pass of a deeper reply chain                                       |
| 9214  | Hybrid descendant rechecks remove the direct child and its direct child within the same targeted follow-up request                                              |
| 9300  | Remote Mastodon comment updates increment the local-comment update counter instead of the entry counter                                                         |
| 9332  | Admin assignment exposes split sync counters, local admin timestamps, and the deletion-sync option                                                              |
| 9423  | Admin maintenance actions are separated into a dedicated template reached from the main plugin page                                                             |
| 9514  | Media upload cleanup deletes already uploaded attachments when a later media upload fails                                                                       |
| 9581  | Media upload cleanup deletes uploaded attachments when final status creation fails                                                                              |
| 9639  | OAuth scope discovery prefers the profile scope on current Mastodon instances                                                                                   |
| 9685  | OAuth scope discovery falls back to read:accounts plus notifications on older Mastodon instances                                                                |
| 9737  | Existing registered apps keep the legacy read:accounts scope until they are re-registered                                                                       |
| 9828  | Instance information refresh persists a compact snapshot including the exact Mastodon version                                                                   |
| 9874  | Admin assignment exposes cached instance-information rows without triggering another live instance request                                                      |
| 9891  | Changing the configured instance URL invalidates the saved instance-information snapshot                                                                        |
| 9929  | Sync-related instance limit helpers reuse the stored instance snapshot without another /api/v2/instance request                                                 |
| 9951  | Manual sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                            |
| 9969  | Replies to FlatPress comments on remote-sourced entries are exported as replies to the parent Mastodon reply                                                    |
| 9999  | Unsynchronized local parent comment is exported before local child reply                                                                                        |
| 10019 | Non-forced sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                        |
| 10042 | Manual sync exports Emoticons plugin shortcodes from FlatPress entries to Unicode emoji in Mastodon status requests                                             |
| 10058 | Manual sync exports Emoticons plugin shortcodes from FlatPress comments to Unicode emoji in Mastodon reply requests                                             |
| 10079 | Manual sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled         |
| 10105 | Non-forced sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled     |
| 10128 | Another Mastodon member reply to an exported FlatPress comment is still imported as a FlatPress comment when comment-as-entry import is enabled                 |
| 10145 | Disabling the quote option imports a Mastodon reply to an exported FlatPress comment without a leading quote block                                              |
| 10159 | Imported Mastodon replies to another Mastodon reply quote the replied-to Mastodon user and text by default                                                      |
| 10178 | Disabling the quote option imports Mastodon reply-to-reply comments without a leading quote block                                                               |
| 10192 | Manual sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                          |
| 10216 | Non-forced sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                      |
| 10269 | Scheduler state is written as a compact summary without full mapping arrays                                                                                     |
| 10288 | Fresh scheduler-state read uses the APCu-capable FlatPress I/O path and does not load full state.json                                                           |
| 10317 | Stale scheduler-state falls back to the full state and rebuilds the summary                                                                                     |
| 10338 | Manual admin synchronization still loads the full state before reporting configuration errors                                                                   |
| 10357 | sync.log uses append-only writes with size-based rotation                                                                                                       |
| 10383 | Large skip volumes are logged as aggregate summaries                                                                                                            |
| 10396 | Small 300x10 state keeps scheduler-state compact and disables full APCu fallback                                                                                |
| 10418 | Fresh small scheduler-state read avoids full state.json and uses APCu-capable file I/O                                                                          |
| 10441 | Large 3000x10 state keeps scheduler-state compact and disables full APCu fallback                                                                               |
| 10464 | Fresh large scheduler-state read avoids full state.json and uses APCu-capable file I/O                                                                          |
| 10577 | Automatic scheduled sync exports a new current comment on an old mapped entry through dirty_comments                                                            |
| 10634 | One-way mode option is disabled by default, normalized, assigned to admin UI, templated and translated                                                          |
| 10674 | One-way admin save preserves hidden import options while keeping one-way mode enabled                                                                           |
| 10691 | One-way admin save preserves hidden import options when one-way mode is disabled in the same submit                                                             |
| 10711 | Bidirectional admin save still stores visible unchecked import options as disabled                                                                              |
| 10743 | One-way admin template hides import controls, notification hints and import-only counters                                                                       |
| 10769 | One-way admin template keeps export, OAuth, instance, token, state and deletion outputs visible                                                                 |
| 10785 | One-way admin assignment hides import-only companion plugins and uses one-way companion intro                                                                   |
| 10865 | One-way mode blocks Mastodon-to-FlatPress imports while FlatPress-to-Mastodon export still runs                                                                 |
| 10943 | One-way deletion sync keeps local content, removes stale remote mappings and queues re-export                                                                   |
| 10992 | One-way content sync re-exports local objects whose remote mappings were unlinked after remote deletion                                                         |
| 11055 | One-way pending descendant rechecks keep local comments and queue them for re-export instead of deleting them                                                   |

### Optional live/auth smoke

| Line | Static test name                                     |
| ---- | ---------------------------------------------------- |
| 5211 | Configured credentials verify (read-only smoke test) |

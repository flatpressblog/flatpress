# 05 - Regression Matrix

## Harness model
`simulate_mastodon_plugin.php` loads the real Mastodon plugin and simulates the FlatPress and Mastodon boundaries. The plugin code itself is not replaced. The HTTP layer, fixture content, media files and some FlatPress services are controlled by the harness so behavior is deterministic.
Normal run checked for this documentation set after the timezone hardening update:
```text
php simulate_mastodon_plugin.php
Exit-code: 0
[OK]: 208
[FAIL]: 0
[WARN]: 0
[SKIP]: 0
```
Static `test_result()` calls found in the script: `209`.
One test is optional and only runs with `--live-auth`:

```text
Configured credentials verify (read-only smoke test)
```
## Category summary

The catalog below is generated from all static `test_result()` calls and therefore remains the canonical line-reference list for the consistency checker.

| Category                           | Static tests |
| ---------------------------------- | ------------ |
| Regular simulation tests           | 208          |
| Optional live/auth smoke           | 1            |
| Total static `test_result()` calls | 209          |

## What the harness proves well

The harness now also checks that advanced comment-shard maintenance controls are not embedded in the main settings template, are available from a dedicated maintenance template, and that notification-based reply hints import old-thread replies before the slower rotation fallback.

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

|  Line | Static test name                                                                                                                                                |
| ----: | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
|  2801 | Simulation summary mode shortens verbose JSON details                                                                                                           |
|  2813 | Simulation final summary reports exit code and status counters                                                                                                  |
|  2834 | Simulation sandbox excludes live fp-content/content by default                                                                                                  |
|  2845 | Simulation sandbox can include live fp-content/content only when explicitly requested                                                                           |
|  2869 | Entry file scanner skips comment directories while keeping real FlatPress entries                                                                               |
|  2928 | Scheduled direct YY/MM scanner keeps active-window entries and dirty-comment parents                                                                            |
|  2977 | Scheduled direct scanner honors the 7-day admin window at parse stage                                                                                           |
|  3012 | Scheduled direct scanner honors the 14-day admin window across a month boundary                                                                                 |
|  3047 | Scheduled direct scanner honors the 30-day admin window across a year boundary                                                                                  |
|  3081 | Scheduled direct scanner treats more than three dirty entries as mandatory candidates                                                                           |
|  3121 | Scheduled direct scanner treats more than three dirty-comment parents as mandatory candidates                                                                   |
|  3148 | Manual force sync keeps the direct all-YY/MM scanner repair path                                                                                                |
|  3172 | Mastodon companion-plugin detection uses FlatPress central enabled-plugin state                                                                                 |
|  3181 | Mastodon companion-plugin detection respects FlatPress plugin_exists() for missing configured plugins                                                           |
|  3194 | Mastodon admin companion-plugin diagnostics cover BBCode, PhotoSwipe, AudioVideo, Tag and Emoticons                                                             |
|  3225 | FlatPress locale is normalized to a Mastodon language code                                                                                                      |
|  3243 | Mastodon HTML -> FlatPress BBCode                                                                                                                               |
|  3264 | Mastodon mention noise is cleaned for FlatPress                                                                                                                 |
|  3269 | Imported Mastodon entry keeps the Mastodon link on a new line                                                                                                   |
|  3277 | Mastodon emojis become FlatPress emoticons                                                                                                                      |
|  3285 | FlatPress BBCode -> Mastodon text                                                                                                                               |
|  3293 | FlatPress URLs lose HTML-like attributes and omit localhost links                                                                                               |
|  3305 | FlatPress entry tags become a Mastodon hashtag footer                                                                                                           |
|  3330 | Mastodon tags become FlatPress tag BBCode without a visible hashtag footer                                                                                      |
|  3353 | Removing Mastodon tags removes FlatPress tag BBCode on update                                                                                                   |
|  3366 | FlatPress entry without tag BBCode has no Mastodon hashtag footer                                                                                               |
|  3379 | Mastodon head metadata uses the configured instance URL and username                                                                                            |
|  3392 | Mastodon head metadata tolerates usernames stored with a leading at-sign                                                                                        |
|  3405 | Mastodon head metadata stays silent when no username is configured                                                                                              |
|  3419 | Actual FlatPress comment exports as readable Mastodon reply                                                                                                     |
|  3429 | FlatPress comment export appends the public comment link on a new line                                                                                          |
|  3440 | FlatPress entry export converts Emoticons plugin shortcodes in titles and bodies to Unicode emoji for Mastodon                                                  |
|  3459 | FlatPress comment export converts Emoticons plugin shortcodes in author lines and bodies to Unicode emoji for Mastodon                                          |
|  3477 | Long local entry export is limited even without a public permalink                                                                                              |
|  3486 | FlatPress single-image entry exposes one uploadable media item                                                                                                  |
|  3496 | FlatPress gallery entry exposes uploadable media items and keeps raw gallery BBCode out of Mastodon text                                                        |
|  3549 | FlatPress AudioVideo entry exposes described audio/video media items and strips raw player BBCode from Mastodon text                                            |
|  3569 | AudioVideo BBCode parser renders optional description endtags as media attributes and keeps legacy single tags working                                          |
|  3605 | Mastodon media upload sends AudioVideo files with descriptions and video thumbnail                                                                              |
|  3630 | Mastodon media planner exports only images when an entry mixes image, audio and video media                                                                     |
|  3641 | Mastodon media planner exports exactly one audio attachment when an entry mixes audio and video media                                                           |
|  3655 | Mastodon media planner exports one video and keeps its poster as an upload thumbnail only                                                                       |
|  3668 | Mastodon media planner keeps multiple images up to the Mastodon media limit                                                                                     |
|  3697 | Mastodon export uploads only the selected audio item from an audio/video FlatPress entry                                                                        |
|  3727 | Mastodon export sends a video poster as upload thumbnail instead of a second media ID                                                                           |
|  3759 | Mastodon single image imports into a FlatPress image tag and stored file                                                                                        |
|  3796 | Mastodon multiple images import into a FlatPress gallery with captions                                                                                          |
|  3845 | Mastodon audio and video attachments import into FlatPress AudioVideo BBCode and stored files                                                                   |
|  3884 | Mastodon audio/video import retries alternate direct media URLs when the first download candidate fails                                                         |
|  3898 | First sync is immediately due                                                                                                                                   |
|  3901 | Next day after 23:00 is due                                                                                                                                     |
|  3902 | Before configured time is not due                                                                                                                               |
|  3923 | Scheduled synchronization due checks use stored UTC time regardless of PHP default timezone                                                                     |
|  3945 | Pending deletion synchronization due checks use UTC cooldowns regardless of PHP default timezone                                                                |
|  3976 | Admin state timestamps are formatted with FlatPress timeoffset regardless of PHP default timezone                                                               |
|  3991 | Scheduled content synchronization uses a short file/APCu cooldown guard                                                                                         |
|  4008 | Full Mastodon state fallback is not stored in APCu                                                                                                              |
|  4040 | Central Mastodon rate-limit guard stops requests after the per-run request budget                                                                               |
|  4074 | Central Mastodon rate-limit guard stops media uploads after the per-run media budget                                                                            |
|  4098 | Central Mastodon rate-limit guard stops status deletions after the per-run delete budget                                                                        |
|  4124 | Cached Mastodon versions before 4.4 delete statuses without the delete_media query parameter                                                                    |
|  4148 | Status deletion retries without delete_media when an older Mastodon server rejects the query parameter                                                          |
|  4178 | Persistent Mastodon media-upload window budget is shared across forced runs                                                                                     |
|  4208 | Persistent media-upload window budget is written to the synchronization last error                                                                              |
|  4235 | Persistent Mastodon delete window budget is shared across forced runs                                                                                           |
|  4259 | Persistent delete window budget is written to the synchronization last error                                                                                    |
|  4286 | Persistent Mastodon account-status paging window budget is shared across forced runs                                                                            |
|  4309 | Persistent account-status paging window budget is written to the synchronization last error                                                                     |
|  4324 | Admin synchronization time converts stored UTC to FlatPress local time and back                                                                                 |
|  4346 | Admin synchronization time conversion supports fractional FlatPress offsets                                                                                     |
|  4362 | Sync start date normalization accepts valid ISO dates                                                                                                           |
|  4368 | Local and remote date helpers derive stable date keys                                                                                                           |
|  4377 | Remote-to-local update toggle normalizes safely                                                                                                                 |
|  4388 | Comment-as-entry import toggle normalizes safely                                                                                                                |
|  4403 | State entry mapping created                                                                                                                                     |
|  4404 | State comment mapping created                                                                                                                                   |
|  4455 | Mastodon runtime files in fp-content/plugin_mastodon use FlatPress FILE_PERMISSIONS                                                                             |
|  4647 | OAuth code exchange                                                                                                                                             |
|  4655 | State cache stays fresh after writing state.json                                                                                                                |
|  4676 | Pretty-printed legacy state.json remains readable after compact-state optimization                                                                              |
|  4724 | State write persists compact split state.json while preserving round-trip mappings                                                                              |
|  4789 | Split comment shards support bounded per-entry state reads                                                                                                      |
|  4813 | Partial split-state writes preserve unloaded comment shards                                                                                                     |
|  4857 | Legacy inline comment state migrates to per-entry comment shards on write                                                                                       |
|  4932 | Legacy inline migration preserves non-contiguous comments and creates a migration backup                                                                        |
|  4991 | Comment-shard diagnostics detect stale metadata and repair the reverse index from shards                                                                        |
|  5012 | CLI comment-shard diagnostics entry point returns a machine-checkable status                                                                                    |
|  5047 | Shard-write failures leave the main state unchanged and return failure                                                                                          |
|  5086 | Main-state write failures after shard writes remain repairable from shard files                                                                                 |
|  5123 | Legacy combined stats are migrated into separate content and deletion counters                                                                                  |
|  5147 | Instance configuration cache avoids repeated /api/v2/instance requests                                                                                          |
|  5168 | Mastodon URL budgeting respects characters_reserved_per_url when limiting plain-text exports                                                                    |
|  5205 | Entry export uses clean Mastodon text and suppresses localhost permalinks                                                                                       |
|  5213 | Comment export is reply-like, localized and emoji-aware                                                                                                         |
|  5238 | Remote entry imported through sync with Mastodon media visible in FlatPress                                                                                     |
|  5244 | Deletion sync is queued for a follow-up request instead of running inside the content sync request                                                              |
|  5250 | Content sync updates the synchronization timestamp and counters while leaving the deletion timestamp for the follow-up request                                  |
|  5269 | Private mention status is skipped during Mastodon import                                                                                                        |
|  5275 | Remote imported entry keeps a stable FlatPress date                                                                                                             |
|  5281 | Remote comment imported through sync                                                                                                                            |
|  5287 | Nested remote comment keeps parent mapping metadata                                                                                                             |
|  5293 | Private mention reply is skipped during Mastodon import                                                                                                         |
|  5331 | Entry export creates a top-level Mastodon status                                                                                                                |
|  5337 | Comment to entry exports with in_reply_to_id on the entry status                                                                                                |
|  5343 | Comment to comment exports with in_reply_to_id on the parent reply                                                                                              |
|  5349 | Entry and comment exports include the configured Mastodon language code                                                                                         |
|  5368 | Deletion sync waits at least five minutes after a completed content sync                                                                                        |
|  5420 | Create and update status requests include the configured Mastodon language code                                                                                 |
|  5463 | Initial Mastodon media uploads include the attachment description in POST /api/v2/media                                                                         |
|  5495 | Unchanged entry media is reused without a new upload when only the post text changes                                                                            |
|  5568 | Mastodon 4.1+ updates changed image descriptions through status media_attributes without re-uploading media                                                     |
|  5605 | Older Mastodon versions fall back to a fresh upload when only the media description changes                                                                     |
|  5692 | Full local-to-remote sync reuses stored media IDs when the attachments did not change                                                                           |
|  5792 | Full sync updates changed media descriptions through status media_attributes on Mastodon 4.1+                                                                   |
|  5915 | Batch entry export keeps older FlatPress entries below newer ones on Mastodon                                                                                   |
|  6017 | New local comment on an already synchronized older entry is exported to Mastodon                                                                                |
|  6112 | Known synchronized entry mappings older than the sync start date do not trigger context refreshes                                                               |
|  6186 | Scheduled content synchronization respects the automatic recent-content window                                                                                  |
|  6212 | Productive content-sync workset loads only active scheduled comment shards                                                                                      |
|  6271 | Normal manual synchronization bypasses the daily due check but still respects the automatic window                                                              |
|  6333 | Explicit full manual synchronization bypasses the automatic window while keeping normal limits                                                                  |
|  6412 | Older changed mapped FlatPress entries are synchronized through the dirty queue                                                                                 |
|  6464 | Post-success comment hook queues older changed mapped comments                                                                                                  |
|  6494 | Scheduled sync updates older dirty comments through direct YY/MM dirty-parent candidates                                                                        |
|  6541 | Remote-write guard suppresses dirty tracking for Mastodon-owned entry_save calls                                                                                |
|  6630 | Large scheduled dirty-tracking sync parses only active-window and dirty entries                                                                                 |
|  6713 | Optional old-thread reply checks rotate through known synchronized threads                                                                                      |
|  6754 | Disabled old-thread reply checks do not refresh known synchronized entry contexts                                                                               |
|  6820 | Automatic scheduled synchronization rotates known synchronized threads through the full sync path                                                               |
|  6902 | Notification hints import a new reply on an old mapped Mastodon entry without context rotation                                                                  |
|  6975 | Notification hints import a new reply on an old mapped Mastodon reply with reply metadata                                                                       |
|  7080 | Notification context hints use the old-thread budget before normal rotation                                                                                     |
|  7131 | Reply-notification polling is skipped until read:notifications is authorized                                                                                    |
|  7254 | Remote updates do not overwrite existing local content when the toggle is disabled                                                                              |
|  7274 | Remote updates overwrite existing local content when the toggle is enabled                                                                                      |
|  7299 | FlatPress single-image entry exports Mastodon media_ids and strips raw image BBCode                                                                             |
|  7310 | FlatPress gallery entry exports Mastodon media_ids up to the instance limit and strips raw gallery BBCode                                                       |
|  7446 | Sync start date filters local exports by entry and comment date                                                                                                 |
|  7532 | Sync start date filters remote imports by status and reply date                                                                                                 |
|  7624 | Remote Mastodon imports honor the FlatPress timeoffset for stored entry/comment dates and ordering keys                                                         |
|  7695 | Remote sync start filtering respects the FlatPress timeoffset near midnight                                                                                     |
|  7760 | Sync reports local export failures instead of silently succeeding                                                                                               |
|  7778 | Shared-request synchronization refreshes the PHP execution budget for long Mastodon work                                                                        |
|  7861 | Synchronized local comments are not imported as duplicate entries from Mastodon by default                                                                      |
|  7953 | Synchronized local comments may be imported as entries when the toggle is enabled                                                                               |
|  8085 | Local FlatPress comments on imported Mastodon entries sync back to Mastodon, including nested replies                                                           |
|  8161 | Asynchronous Mastodon media uploads are polled until the attachment is ready                                                                                    |
|  8185 | Long-running Mastodon media uploads refresh the PHP execution budget for upload and polling                                                                     |
|  8257 | Slow asynchronous Mastodon video uploads keep polling beyond the old short retry window                                                                         |
|  8323 | Mastodon audio uploads keep polling even when pending responses have no preview_url                                                                             |
|  8388 | Follow-up deletion sync removes remote Mastodon content after local FlatPress deletion                                                                          |
|  8413 | Follow-up deletion sync updates last_deletion_run without overwriting the last synchronization timestamp or content counters                                    |
|  8469 | Deletion sync falls back to DELETE without delete_media for older Mastodon servers                                                                              |
|  8532 | Follow-up deletion sync removes mirrored FlatPress content after remote Mastodon deletion                                                                       |
|  8587 | Deletion sync skips old locally deleted mappings outside the sync start date window                                                                             |
|  8637 | Deletion sync skips old remotely mirrored mappings outside the sync start date window                                                                           |
|  8694 | Scheduled deletion sync skips remote lookups outside the automatic scheduled window                                                                             |
|  8767 | Scheduled deletion sync still propagates old local deletions outside the automatic window                                                                       |
|  8828 | Large scheduled deletion syncs resume from the saved entry cursor                                                                                               |
|  8912 | Large scheduled deletion syncs resume from the saved comment cursor inside one entry shard                                                                      |
|  8957 | Disabling deletion synchronization clears pending delete work without issuing deletion requests                                                                 |
|  8977 | Content sync before deletion sync protects a locally deleted exported FlatPress comment from stale re-import                                                    |
|  8996 | Deletion sync after the protected content sync reattaches the imported descendant reply to the synchronized entry status and keeps a later verification pending |
|  9025 | Deletion sync keeps imported descendants for a later follow-up verification when Mastodon still returns the child reply once                                    |
|  9042 | Tombstones prevent re-importing the deleted exported parent comment while descendant rechecks are still pending                                                 |
|  9055 | A later follow-up deletion sync removes the imported descendant once Mastodon reports the child reply missing                                                   |
|  9086 | Content sync before deletion sync also protects a locally deleted exported FlatPress comment when the imported descendant reply already has its own child       |
|  9106 | Deletion sync after the protected content sync keeps the descendant child below the reattached reply and verifies both remote replies in the same pass          |
|  9146 | Deletion sync removes imported descendants immediately when Mastodon already reports the child reply missing in the first follow-up pass                        |
|  9177 | Hybrid descendant rechecks keep only the direct child queued after the first stale follow-up pass of a deeper reply chain                                       |
|  9196 | Hybrid descendant rechecks remove the direct child and its direct child within the same targeted follow-up request                                              |
|  9282 | Remote Mastodon comment updates increment the local-comment update counter instead of the entry counter                                                         |
|  9314 | Admin assignment exposes split sync counters, local admin timestamps, and the deletion-sync option                                                              |
|  9405 | Admin maintenance actions are separated into a dedicated template reached from the main plugin page                                                             |
|  9496 | Media upload cleanup deletes already uploaded attachments when a later media upload fails                                                                       |
|  9563 | Media upload cleanup deletes uploaded attachments when final status creation fails                                                                              |
|  9621 | OAuth scope discovery prefers the profile scope on current Mastodon instances                                                                                   |
|  9667 | OAuth scope discovery falls back to read:accounts plus notifications on older Mastodon instances                                                                |
|  9719 | Existing registered apps keep the legacy read:accounts scope until they are re-registered                                                                       |
|  9810 | Instance information refresh persists a compact snapshot including the exact Mastodon version                                                                   |
|  9856 | Admin assignment exposes cached instance-information rows without triggering another live instance request                                                      |
|  9873 | Changing the configured instance URL invalidates the saved instance-information snapshot                                                                        |
|  9911 | Sync-related instance limit helpers reuse the stored instance snapshot without another /api/v2/instance request                                                 |
|  9933 | Manual sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                            |
|  9951 | Replies to FlatPress comments on remote-sourced entries are exported as replies to the parent Mastodon reply                                                    |
|  9981 | Unsynchronized local parent comment is exported before local child reply                                                                                        |
| 10001 | Non-forced sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                        |
| 10024 | Manual sync exports Emoticons plugin shortcodes from FlatPress entries to Unicode emoji in Mastodon status requests                                             |
| 10040 | Manual sync exports Emoticons plugin shortcodes from FlatPress comments to Unicode emoji in Mastodon reply requests                                             |
| 10061 | Manual sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled         |
| 10087 | Non-forced sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled     |
| 10110 | Another Mastodon member reply to an exported FlatPress comment is still imported as a FlatPress comment when comment-as-entry import is enabled                 |
| 10127 | Disabling the quote option imports a Mastodon reply to an exported FlatPress comment without a leading quote block                                              |
| 10141 | Imported Mastodon replies to another Mastodon reply quote the replied-to Mastodon user and text by default                                                      |
| 10160 | Disabling the quote option imports Mastodon reply-to-reply comments without a leading quote block                                                               |
| 10174 | Manual sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                          |
| 10198 | Non-forced sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                      |
| 10251 | Scheduler state is written as a compact summary without full mapping arrays                                                                                     |
| 10270 | Fresh scheduler-state read uses the APCu-capable FlatPress I/O path and does not load full state.json                                                           |
| 10299 | Stale scheduler-state falls back to the full state and rebuilds the summary                                                                                     |
| 10320 | Manual admin synchronization still loads the full state before reporting configuration errors                                                                   |
| 10339 | sync.log uses append-only writes with size-based rotation                                                                                                       |
| 10365 | Large skip volumes are logged as aggregate summaries                                                                                                            |
| 10378 | Small 300x10 state keeps scheduler-state compact and disables full APCu fallback                                                                                |
| 10400 | Fresh small scheduler-state read avoids full state.json and uses APCu-capable file I/O                                                                          |
| 10423 | Large 3000x10 state keeps scheduler-state compact and disables full APCu fallback                                                                               |
| 10446 | Fresh large scheduler-state read avoids full state.json and uses APCu-capable file I/O                                                                          |
| 10559 | Automatic scheduled sync exports a new current comment on an old mapped entry through dirty_comments                                                            |

### Optional live/auth smoke

| Line | Static test name                                     |
| ---: | ---------------------------------------------------- |
| 5193 | Configured credentials verify (read-only smoke test) |

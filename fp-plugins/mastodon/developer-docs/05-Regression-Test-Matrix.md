# 05 - Regression Matrix

## Harness model
`simulate_mastodon_plugin.php` loads the real Mastodon plugin and simulates the FlatPress and Mastodon boundaries. The plugin code itself is not replaced. The HTTP layer, fixture content, media files and some FlatPress services are controlled by the harness so behavior is deterministic.
Normal run checked for this documentation set after the media-family selection change:
```text
php simulate_mastodon_plugin.php
Exit-code: 0
[OK]: 170
[FAIL]: 0
```
Static `test_result()` calls found in the script: `171`.
One test is optional and only runs with `--live-auth`:

```text
Configured credentials verify (read-only smoke test)
```
## Category summary
| Category                             | Static tests |
| ------------------------------------ | -----------: |
| Comments and reply threads           |           35 |
| Content sync local/remote            |           17 |
| Deletion sync and tombstones         |           27 |
| Media export/import/reuse            |           37 |
| OAuth and account setup              |            4 |
| Other integration behavior           |            6 |
| Rate limits and budgets              |            5 |
| Scheduler, state, logging, and scale |           20 |
| Text, tags, URLs, and conversion     |           19 |
| Optional live/auth smoke             |            1 |

## What the harness proves well
| Area                                     | What is real                                                                                                                 | What is simulated                                                               |
| ---------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------- |
| State read/write and scheduler summaries | Plugin state logic and actual files in the sandbox.                                                                          | Test-specific content directory and fixture state.                              |
| Mastodon API behavior                    | Plugin API selection, payload building, fallback handling and response parsing.                                              | HTTP responses and remote status/media payloads.                                |
| Media handling                           | Local extraction, MIME/type decisions, media-family selection, upload planning, thumbnail payloads, reuse and cleanup logic. | Actual remote processing, CDN behavior and instance-specific media transcoding. |
| Text conversion                          | Real conversion helpers.                                                                                                     | Fixture strings and expected results.                                           |
| Deletion sync                            | Real deletion orchestration and status delete fallback.                                                                      | Remote deletion responses and missing-status scenarios.                         |
| Admin assignment                         | Real admin assignment function and Smarty variable preparation.                                                              | Test harness Smarty object.                                                     |

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

### Comments and reply threads

| Line | Static test name                                                                                                                                            |
| ---: | ----------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2616 | Actual FlatPress comment exports as readable Mastodon reply                                                                                                 |
| 2626 | FlatPress comment export appends the public comment link on a new line                                                                                      |
| 2656 | FlatPress comment export converts Emoticons plugin shortcodes in author lines and bodies to Unicode emoji for Mastodon                                      |
| 3499 | Comment-as-entry import toggle normalizes safely                                                                                                            |
| 3515 | State comment mapping created                                                                                                                               |
| 3886 | Comment export is reply-like, localized and emoji-aware                                                                                                     |
| 3954 | Remote comment imported through sync                                                                                                                        |
| 3960 | Nested remote comment keeps parent mapping metadata                                                                                                         |
| 3966 | Private mention reply is skipped during Mastodon import                                                                                                     |
| 4010 | Comment to entry exports with in_reply_to_id on the entry status                                                                                            |
| 4016 | Comment to comment exports with in_reply_to_id on the parent reply                                                                                          |
| 4022 | Entry and comment exports include the configured Mastodon language code                                                                                     |
| 4691 | New local comment on an already synchronized older entry is exported to Mastodon                                                                            |
| 5110 | Post-success comment hook queues older changed mapped comments                                                                                              |
| 5325 | Optional old-thread reply checks rotate through known synchronized threads                                                                                  |
| 5366 | Disabled old-thread reply checks do not refresh known synchronized entry contexts                                                                           |
| 5681 | Sync start date filters local exports by entry and comment date                                                                                             |
| 5767 | Sync start date filters remote imports by status and reply date                                                                                             |
| 5859 | Remote Mastodon imports honor the FlatPress timeoffset for stored entry/comment dates and ordering keys                                                     |
| 6096 | Synchronized local comments are not imported as duplicate entries from Mastodon by default                                                                  |
| 6188 | Synchronized local comments may be imported as entries when the toggle is enabled                                                                           |
| 6320 | Local FlatPress comments on imported Mastodon entries sync back to Mastodon, including nested replies                                                       |
| 7445 | Remote Mastodon comment updates increment the local-comment update counter instead of the entry counter                                                     |
| 7989 | Manual sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                        |
| 8007 | Replies to FlatPress comments on remote-sourced entries are exported as replies to the parent Mastodon reply                                                |
| 8023 | Non-forced sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                    |
| 8062 | Manual sync exports Emoticons plugin shortcodes from FlatPress comments to Unicode emoji in Mastodon reply requests                                         |
| 8083 | Manual sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled     |
| 8109 | Non-forced sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled |
| 8132 | Another Mastodon member reply to an exported FlatPress comment is still imported as a FlatPress comment when comment-as-entry import is enabled             |
| 8149 | Disabling the quote option imports a Mastodon reply to an exported FlatPress comment without a leading quote block                                          |
| 8163 | Imported Mastodon replies to another Mastodon reply quote the replied-to Mastodon user and text by default                                                  |
| 8182 | Disabling the quote option imports Mastodon reply-to-reply comments without a leading quote block                                                           |
| 8196 | Manual sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                      |
| 8220 | Non-forced sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                  |

### Content sync local/remote

| Line | Static test name                                                                                   |
| ---: | -------------------------------------------------------------------------------------------------- |
| 2466 | Imported Mastodon entry keeps the Mastodon link on a new line                                      |
| 3097 | First sync is immediately due                                                                      |
| 3435 | Admin synchronization time converts stored UTC to FlatPress local time and back                    |
| 3457 | Admin synchronization time conversion supports fractional FlatPress offsets                        |
| 3473 | Sync start date normalization accepts valid ISO dates                                              |
| 3479 | Local and remote date helpers derive stable date keys                                              |
| 3488 | Remote-to-local update toggle normalizes safely                                                    |
| 3942 | Private mention status is skipped during Mastodon import                                           |
| 3948 | Remote imported entry keeps a stable FlatPress date                                                |
| 4589 | Batch entry export keeps older FlatPress entries below newer ones on Mastodon                      |
| 4860 | Scheduled content synchronization respects the automatic recent-content window                     |
| 4917 | Normal manual synchronization bypasses the daily due check but still respects the automatic window |
| 5058 | Older changed mapped FlatPress entries are synchronized through the dirty queue                    |
| 5489 | Remote updates do not overwrite existing local content when the toggle is disabled                 |
| 5509 | Remote updates overwrite existing local content when the toggle is enabled                         |
| 5930 | Remote sync start filtering respects the FlatPress timeoffset near midnight                        |
| 5995 | Sync reports local export failures instead of silently succeeding                                  |

### Deletion sync and tombstones

| Line | Static test name                                                                                                                                                |
| ---: | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 3209 | Central Mastodon rate-limit guard stops status deletions after the per-run delete budget                                                                        |
| 3346 | Persistent Mastodon delete window budget is shared across forced runs                                                                                           |
| 3370 | Persistent delete window budget is written to the synchronization last error                                                                                    |
| 3796 | Legacy combined stats are migrated into separate content and deletion counters                                                                                  |
| 3917 | Deletion sync is queued for a follow-up request instead of running inside the content sync request                                                              |
| 3923 | Content sync updates the synchronization timestamp and counters while leaving the deletion timestamp for the follow-up request                                  |
| 4041 | Deletion sync waits at least five minutes after a completed content sync                                                                                        |
| 6623 | Follow-up deletion sync removes remote Mastodon content after local FlatPress deletion                                                                          |
| 6648 | Follow-up deletion sync updates last_deletion_run without overwriting the last synchronization timestamp or content counters                                    |
| 6767 | Follow-up deletion sync removes mirrored FlatPress content after remote Mastodon deletion                                                                       |
| 6823 | Deletion sync skips old locally deleted mappings outside the sync start date window                                                                             |
| 6873 | Deletion sync skips old remotely mirrored mappings outside the sync start date window                                                                           |
| 6930 | Scheduled deletion sync skips remote lookups outside the automatic scheduled window                                                                             |
| 7003 | Scheduled deletion sync still propagates old local deletions outside the automatic window                                                                       |
| 7064 | Large scheduled deletion syncs resume from the saved entry cursor                                                                                               |
| 7119 | Disabling deletion synchronization clears pending delete work without issuing deletion requests                                                                 |
| 7139 | Content sync before deletion sync protects a locally deleted exported FlatPress comment from stale re-import                                                    |
| 7158 | Deletion sync after the protected content sync reattaches the imported descendant reply to the synchronized entry status and keeps a later verification pending |
| 7187 | Deletion sync keeps imported descendants for a later follow-up verification when Mastodon still returns the child reply once                                    |
| 7204 | Tombstones prevent re-importing the deleted exported parent comment while descendant rechecks are still pending                                                 |
| 7217 | A later follow-up deletion sync removes the imported descendant once Mastodon reports the child reply missing                                                   |
| 7248 | Content sync before deletion sync also protects a locally deleted exported FlatPress comment when the imported descendant reply already has its own child       |
| 7268 | Deletion sync after the protected content sync keeps the descendant child below the reattached reply and verifies both remote replies in the same pass          |
| 7309 | Deletion sync removes imported descendants immediately when Mastodon already reports the child reply missing in the first follow-up pass                        |
| 7340 | Hybrid descendant rechecks keep only the direct child queued after the first stale follow-up pass of a deeper reply chain                                       |
| 7359 | Hybrid descendant rechecks remove the direct child and its direct child within the same targeted follow-up request                                              |
| 7477 | Admin assignment exposes split sync counters, local admin timestamps, and the deletion-sync option                                                              |

### Media export/import/reuse

| Line | Static test name                                                                                                       |
| ---: | ---------------------------------------------------------------------------------------------------------------------- |
| 2402 | Mastodon admin companion-plugin diagnostics cover BBCode, PhotoSwipe, AudioVideo, Tag and Emoticons                    |
| 2685 | FlatPress single-image entry exposes one uploadable media item                                                         |
| 2695 | FlatPress gallery entry exposes uploadable media items and keeps raw gallery BBCode out of Mastodon text               |
| 2748 | FlatPress AudioVideo entry exposes described audio/video media items and strips raw player BBCode from Mastodon text   |
| 2768 | AudioVideo BBCode parser renders optional description endtags as media attributes and keeps legacy single tags working |
| 2804 | Mastodon media upload sends AudioVideo files with descriptions and video thumbnail                                     |
| 2829 | Mastodon media planner exports only images when an entry mixes image, audio and video media                            |
| 2840 | Mastodon media planner exports exactly one audio attachment when an entry mixes audio and video media                  |
| 2854 | Mastodon media planner exports one video and keeps its poster as an upload thumbnail only                              |
| 2867 | Mastodon media planner keeps multiple images up to the Mastodon media limit                                            |
| 2896 | Mastodon export uploads only the selected audio item from an audio/video FlatPress entry                               |
| 2926 | Mastodon export sends a video poster as upload thumbnail instead of a second media ID                                  |
| 2958 | Mastodon single image imports into a FlatPress image tag and stored file                                               |
| 2995 | Mastodon multiple images import into a FlatPress gallery with captions                                                 |
| 3044 | Mastodon audio and video attachments import into FlatPress AudioVideo BBCode and stored files                          |
| 3083 | Mastodon audio/video import retries alternate direct media URLs when the first download candidate fails                |
| 3185 | Central Mastodon rate-limit guard stops media uploads after the per-run media budget                                   |
| 3235 | Cached Mastodon versions before 4.4 delete statuses without the delete_media query parameter                           |
| 3259 | Status deletion retries without delete_media when an older Mastodon server rejects the query parameter                 |
| 3289 | Persistent Mastodon media-upload window budget is shared across forced runs                                            |
| 3319 | Persistent media-upload window budget is written to the synchronization last error                                     |
| 3911 | Remote entry imported through sync with Mastodon media visible in FlatPress                                            |
| 4136 | Initial Mastodon media uploads include the attachment description in POST /api/v2/media                                |
| 4168 | Unchanged entry media is reused without a new upload when only the post text changes                                   |
| 4241 | Mastodon 4.1+ updates changed image descriptions through status media_attributes without re-uploading media            |
| 4278 | Older Mastodon versions fall back to a fresh upload when only the media description changes                            |
| 4365 | Full local-to-remote sync reuses stored media IDs when the attachments did not change                                  |
| 4465 | Full sync updates changed media descriptions through status media_attributes on Mastodon 4.1+                          |
| 5534 | FlatPress single-image entry exports Mastodon media_ids and strips raw image BBCode                                    |
| 5545 | FlatPress gallery entry exports Mastodon media_ids up to the instance limit and strips raw gallery BBCode              |
| 6396 | Asynchronous Mastodon media uploads are polled until the attachment is ready                                           |
| 6420 | Long-running Mastodon media uploads refresh the PHP execution budget for upload and polling                            |
| 6492 | Slow asynchronous Mastodon video uploads keep polling beyond the old short retry window                                |
| 6558 | Mastodon audio uploads keep polling even when pending responses have no preview_url                                    |
| 6704 | Deletion sync falls back to DELETE without delete_media for older Mastodon servers                                     |
| 7553 | Media upload cleanup deletes already uploaded attachments when a later media upload fails                              |
| 7620 | Media upload cleanup deletes uploaded attachments when final status creation fails                                     |

### OAuth and account setup

| Line | Static test name                                                                          |
| ---: | ----------------------------------------------------------------------------------------- |
| 3758 | OAuth code exchange                                                                       |
| 7677 | OAuth scope discovery prefers the profile scope on current Mastodon instances             |
| 7723 | OAuth scope discovery falls back to read:accounts on older Mastodon instances             |
| 7775 | Existing registered apps keep the legacy read:accounts scope until they are re-registered |

### Other integration behavior

| Line | Static test name                                                                                      |
| ---: | ----------------------------------------------------------------------------------------------------- |
| 2387 | Mastodon companion-plugin detection respects FlatPress plugin_exists() for missing configured plugins |
| 2461 | Mastodon mention noise is cleaned for FlatPress                                                       |
| 2589 | Mastodon head metadata tolerates usernames stored with a leading at-sign                              |
| 2602 | Mastodon head metadata stays silent when no username is configured                                    |
| 3100 | Next day after 23:00 is due                                                                           |
| 3101 | Before configured time is not due                                                                     |

### Rate limits and budgets

| Line | Static test name                                                                             |
| ---: | -------------------------------------------------------------------------------------------- |
| 3151 | Central Mastodon rate-limit guard stops requests after the per-run request budget            |
| 3397 | Persistent Mastodon account-status paging window budget is shared across forced runs         |
| 3420 | Persistent account-status paging window budget is written to the synchronization last error  |
| 3841 | Mastodon URL budgeting respects characters_reserved_per_url when limiting plain-text exports |
| 6013 | Shared-request synchronization refreshes the PHP execution budget for long Mastodon work     |

### Scheduler, state, logging, and scale

| Line | Static test name                                                                                           |
| ---: | ---------------------------------------------------------------------------------------------------------- |
| 2374 | Mastodon companion-plugin detection uses FlatPress central enabled-plugin state                            |
| 3109 | Scheduled content synchronization uses a short file/APCu cooldown guard                                    |
| 3119 | Full Mastodon state fallback is not stored in APCu                                                         |
| 3514 | State entry mapping created                                                                                |
| 3566 | Mastodon runtime files in fp-content/plugin_mastodon use FlatPress FILE_PERMISSIONS                        |
| 3766 | State cache stays fresh after writing state.json                                                           |
| 3820 | Instance configuration cache avoids repeated /api/v2/instance requests                                     |
| 4786 | Known synchronized entry mappings older than the sync start date do not trigger context refreshes          |
| 5153 | Remote-write guard suppresses dirty tracking for Mastodon-owned entry_save calls                           |
| 5242 | Large scheduled dirty-tracking sync parses only active-window and dirty entries                            |
| 7866 | Instance information refresh persists a compact snapshot including the exact Mastodon version              |
| 7912 | Admin assignment exposes cached instance-information rows without triggering another live instance request |
| 8273 | Scheduler state is written as a compact summary without full mapping arrays                                |
| 8292 | Fresh scheduler-state read uses the APCu-capable FlatPress I/O path and does not load full state.json      |
| 8321 | Stale scheduler-state falls back to the full state and rebuilds the summary                                |
| 8342 | Manual admin synchronization still loads the full state before reporting configuration errors              |
| 8361 | sync.log uses append-only writes with size-based rotation                                                  |
| 8387 | Large skip volumes are logged as aggregate summaries                                                       |
| 8400 | Large 3000x10 state keeps scheduler-state compact and disables full APCu fallback                          |
| 8420 | Fresh large scheduler-state read avoids full state.json and uses APCu-capable file I/O                     |

### Text, tags, URLs, and conversion

| Line | Static test name                                                                                                    |
| ---: | ------------------------------------------------------------------------------------------------------------------- |
| 2422 | FlatPress locale is normalized to a Mastodon language code                                                          |
| 2440 | Mastodon HTML -> FlatPress BBCode                                                                                   |
| 2474 | Mastodon emojis become FlatPress emoticons                                                                          |
| 2482 | FlatPress BBCode -> Mastodon text                                                                                   |
| 2490 | FlatPress URLs lose HTML-like attributes and omit localhost links                                                   |
| 2502 | FlatPress entry tags become a Mastodon hashtag footer                                                               |
| 2527 | Mastodon tags become FlatPress tag BBCode without a visible hashtag footer                                          |
| 2550 | Removing Mastodon tags removes FlatPress tag BBCode on update                                                       |
| 2563 | FlatPress entry without tag BBCode has no Mastodon hashtag footer                                                   |
| 2576 | Mastodon head metadata uses the configured instance URL and username                                                |
| 2637 | FlatPress entry export converts Emoticons plugin shortcodes in titles and bodies to Unicode emoji for Mastodon      |
| 2676 | Long local entry export is limited even without a public permalink                                                  |
| 3878 | Entry export uses clean Mastodon text and suppresses localhost permalinks                                           |
| 4004 | Entry export creates a top-level Mastodon status                                                                    |
| 4093 | Create and update status requests include the configured Mastodon language code                                     |
| 4979 | Explicit full manual synchronization bypasses the automatic window while keeping normal limits                      |
| 7929 | Changing the configured instance URL invalidates the saved instance-information snapshot                            |
| 7967 | Sync-related instance limit helpers reuse the stored instance snapshot without another /api/v2/instance request     |
| 8046 | Manual sync exports Emoticons plugin shortcodes from FlatPress entries to Unicode emoji in Mastodon status requests |

### Optional live/auth smoke

| Line | Static test name                                     |
| ---: | ---------------------------------------------------- |
| 3866 | Configured credentials verify (read-only smoke test) |

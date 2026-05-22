# 05 - Regression Matrix

## Harness model
`simulate_mastodon_plugin.php` loads the real Mastodon plugin and simulates the FlatPress and Mastodon boundaries. The plugin code itself is not replaced. The HTTP layer, fixture content, media files and some FlatPress services are controlled by the harness so behavior is deterministic.
Normal run checked for this documentation set after the direct `YY/MM` scanner and documentation-consistency updates:
```text
php simulate_mastodon_plugin.php
Exit-code: 0
[OK]: 174
[FAIL]: 0
```
Static `test_result()` calls found in the script: `175`.
One test is optional and only runs with `--live-auth`:

```text
Configured credentials verify (read-only smoke test)
```
## Category summary
| Category                             | Static tests |
| ------------------------------------ | -----------: |
| Comments and reply threads           |           36 |
| Content sync local/remote            |           19 |
| Deletion sync and tombstones         |           27 |
| Media export/import/reuse            |           37 |
| OAuth and account setup              |            4 |
| Other integration behavior           |            6 |
| Rate limits and budgets              |            5 |
| Scheduler, state, logging, and scale |           21 |
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
| Entry scanner pruning                    | Real direct `YY/MM/entry*.txt` collector, scheduled-window month pruning and dirty-parent augmentation.                      | Synthetic trees with entry-like files below `comments/` plus targeted fixtures. |

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

| Line | Static test name                                                                                                                                              |
| ---: | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2712 | Actual FlatPress comment exports as readable Mastodon reply                                                                                                   |
| 2722 | FlatPress comment export appends the public comment link on a new line                                                                                        |
| 2752 | FlatPress comment export converts Emoticons plugin shortcodes in author lines and bodies to Unicode emoji for Mastodon                                        |
| 3595 | Comment-as-entry import toggle normalizes safely                                                                                                              |
| 3611 | State comment mapping created                                                                                                                                 |
| 3982 | Comment export is reply-like, localized and emoji-aware                                                                                                       |
| 4050 | Remote comment imported through sync                                                                                                                          |
| 4056 | Nested remote comment keeps parent mapping metadata                                                                                                           |
| 4062 | Private mention reply is skipped during Mastodon import                                                                                                       |
| 4106 | Comment to entry exports with in_reply_to_id on the entry status                                                                                              |
| 4112 | Comment to comment exports with in_reply_to_id on the parent reply                                                                                            |
| 4118 | Entry and comment exports include the configured Mastodon language code                                                                                       |
| 4787 | New local comment on an already synchronized older entry is exported to Mastodon                                                                              |
| 5206 | Post-success comment hook queues older changed mapped comments                                                                                                |
| 5455 | Optional old-thread reply checks rotate through known synchronized threads                                                                                    |
| 5496 | Disabled old-thread reply checks do not refresh known synchronized entry contexts                                                                             |
| 5811 | Sync start date filters local exports by entry and comment date                                                                                               |
| 5897 | Sync start date filters remote imports by status and reply date                                                                                               |
| 5989 | Remote Mastodon imports honor the FlatPress timeoffset for stored entry/comment dates and ordering keys                                                       |
| 6226 | Synchronized local comments are not imported as duplicate entries from Mastodon by default                                                                    |
| 6318 | Synchronized local comments may be imported as entries when the toggle is enabled                                                                             |
| 6450 | Local FlatPress comments on imported Mastodon entries sync back to Mastodon, including nested replies                                                         |
| 7575 | Remote Mastodon comment updates increment the local-comment update counter instead of the entry counter                                                       |
| 8119 | Manual sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                          |
| 8137 | Replies to FlatPress comments on remote-sourced entries are exported as replies to the parent Mastodon reply                                                  |
| 8167 | Unsynchronized local parent comment is exported before local child reply                                                                                      |
| 8187 | Non-forced sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                      |
| 8226 | Manual sync exports Emoticons plugin shortcodes from FlatPress comments to Unicode emoji in Mastodon reply requests                                           |
| 8247 | Manual sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled       |
| 8273 | Non-forced sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled   |
| 8296 | Another Mastodon member reply to an exported FlatPress comment is still imported as a FlatPress comment when comment-as-entry import is enabled               |
| 8313 | Disabling the quote option imports a Mastodon reply to an exported FlatPress comment without a leading quote block                                            |
| 8327 | Imported Mastodon replies to another Mastodon reply quote the replied-to Mastodon user and text by default                                                    |
| 8346 | Disabling the quote option imports Mastodon reply-to-reply comments without a leading quote block                                                             |
| 8360 | Manual sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                        |
| 8384 | Non-forced sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                    |

### Content sync local/remote

| Line | Static test name                                                                                     |
| ---: | ---------------------------------------------------------------------------------------------------- |
| 2382 | Entry file scanner skips comment directories while keeping real FlatPress entries                    |
| 2441 | Scheduled direct YY/MM scanner keeps active-window entries and dirty-comment parents                 |
| 2562 | Imported Mastodon entry keeps the Mastodon link on a new line                                        |
| 3193 | First sync is immediately due                                                                        |
| 3531 | Admin synchronization time converts stored UTC to FlatPress local time and back                      |
| 3553 | Admin synchronization time conversion supports fractional FlatPress offsets                          |
| 3569 | Sync start date normalization accepts valid ISO dates                                                |
| 3575 | Local and remote date helpers derive stable date keys                                                |
| 3584 | Remote-to-local update toggle normalizes safely                                                      |
| 4038 | Private mention status is skipped during Mastodon import                                             |
| 4044 | Remote imported entry keeps a stable FlatPress date                                                  |
| 4685 | Batch entry export keeps older FlatPress entries below newer ones on Mastodon                        |
| 4956 | Scheduled content synchronization respects the automatic recent-content window                       |
| 5013 | Normal manual synchronization bypasses the daily due check but still respects the automatic window   |
| 5154 | Older changed mapped FlatPress entries are synchronized through the dirty queue                      |
| 5619 | Remote updates do not overwrite existing local content when the toggle is disabled                   |
| 5639 | Remote updates overwrite existing local content when the toggle is enabled                           |
| 6060 | Remote sync start filtering respects the FlatPress timeoffset near midnight                          |
| 6125 | Sync reports local export failures instead of silently succeeding                                    |

### Deletion sync and tombstones

| Line | Static test name                                                                                                                                                |
| ---: | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 3305 | Central Mastodon rate-limit guard stops status deletions after the per-run delete budget                                                                        |
| 3442 | Persistent Mastodon delete window budget is shared across forced runs                                                                                           |
| 3466 | Persistent delete window budget is written to the synchronization last error                                                                                    |
| 3892 | Legacy combined stats are migrated into separate content and deletion counters                                                                                  |
| 4013 | Deletion sync is queued for a follow-up request instead of running inside the content sync request                                                              |
| 4019 | Content sync updates the synchronization timestamp and counters while leaving the deletion timestamp for the follow-up request                                  |
| 4137 | Deletion sync waits at least five minutes after a completed content sync                                                                                        |
| 6753 | Follow-up deletion sync removes remote Mastodon content after local FlatPress deletion                                                                          |
| 6778 | Follow-up deletion sync updates last_deletion_run without overwriting the last synchronization timestamp or content counters                                    |
| 6897 | Follow-up deletion sync removes mirrored FlatPress content after remote Mastodon deletion                                                                       |
| 6953 | Deletion sync skips old locally deleted mappings outside the sync start date window                                                                             |
| 7003 | Deletion sync skips old remotely mirrored mappings outside the sync start date window                                                                           |
| 7060 | Scheduled deletion sync skips remote lookups outside the automatic scheduled window                                                                             |
| 7133 | Scheduled deletion sync still propagates old local deletions outside the automatic window                                                                       |
| 7194 | Large scheduled deletion syncs resume from the saved entry cursor                                                                                               |
| 7249 | Disabling deletion synchronization clears pending delete work without issuing deletion requests                                                                 |
| 7269 | Content sync before deletion sync protects a locally deleted exported FlatPress comment from stale re-import                                                    |
| 7288 | Deletion sync after the protected content sync reattaches the imported descendant reply to the synchronized entry status and keeps a later verification pending |
| 7317 | Deletion sync keeps imported descendants for a later follow-up verification when Mastodon still returns the child reply once                                    |
| 7334 | Tombstones prevent re-importing the deleted exported parent comment while descendant rechecks are still pending                                                 |
| 7347 | A later follow-up deletion sync removes the imported descendant once Mastodon reports the child reply missing                                                   |
| 7378 | Content sync before deletion sync also protects a locally deleted exported FlatPress comment when the imported descendant reply already has its own child       |
| 7398 | Deletion sync after the protected content sync keeps the descendant child below the reattached reply and verifies both remote replies in the same pass          |
| 7439 | Deletion sync removes imported descendants immediately when Mastodon already reports the child reply missing in the first follow-up pass                        |
| 7470 | Hybrid descendant rechecks keep only the direct child queued after the first stale follow-up pass of a deeper reply chain                                       |
| 7489 | Hybrid descendant rechecks remove the direct child and its direct child within the same targeted follow-up request                                              |
| 7607 | Admin assignment exposes split sync counters, local admin timestamps, and the deletion-sync option                                                              |

### Media export/import/reuse

| Line | Static test name                                                                                                       |
| ---: | ---------------------------------------------------------------------------------------------------------------------- |
| 2498 | Mastodon admin companion-plugin diagnostics cover BBCode, PhotoSwipe, AudioVideo, Tag and Emoticons                    |
| 2781 | FlatPress single-image entry exposes one uploadable media item                                                         |
| 2791 | FlatPress gallery entry exposes uploadable media items and keeps raw gallery BBCode out of Mastodon text               |
| 2844 | FlatPress AudioVideo entry exposes described audio/video media items and strips raw player BBCode from Mastodon text   |
| 2864 | AudioVideo BBCode parser renders optional description endtags as media attributes and keeps legacy single tags working |
| 2900 | Mastodon media upload sends AudioVideo files with descriptions and video thumbnail                                     |
| 2925 | Mastodon media planner exports only images when an entry mixes image, audio and video media                            |
| 2936 | Mastodon media planner exports exactly one audio attachment when an entry mixes audio and video media                  |
| 2950 | Mastodon media planner exports one video and keeps its poster as an upload thumbnail only                              |
| 2963 | Mastodon media planner keeps multiple images up to the Mastodon media limit                                            |
| 2992 | Mastodon export uploads only the selected audio item from an audio/video FlatPress entry                               |
| 3022 | Mastodon export sends a video poster as upload thumbnail instead of a second media ID                                  |
| 3054 | Mastodon single image imports into a FlatPress image tag and stored file                                               |
| 3091 | Mastodon multiple images import into a FlatPress gallery with captions                                                 |
| 3140 | Mastodon audio and video attachments import into FlatPress AudioVideo BBCode and stored files                          |
| 3179 | Mastodon audio/video import retries alternate direct media URLs when the first download candidate fails                |
| 3281 | Central Mastodon rate-limit guard stops media uploads after the per-run media budget                                   |
| 3331 | Cached Mastodon versions before 4.4 delete statuses without the delete_media query parameter                           |
| 3355 | Status deletion retries without delete_media when an older Mastodon server rejects the query parameter                 |
| 3385 | Persistent Mastodon media-upload window budget is shared across forced runs                                            |
| 3415 | Persistent media-upload window budget is written to the synchronization last error                                     |
| 4007 | Remote entry imported through sync with Mastodon media visible in FlatPress                                            |
| 4232 | Initial Mastodon media uploads include the attachment description in POST /api/v2/media                                |
| 4264 | Unchanged entry media is reused without a new upload when only the post text changes                                   |
| 4337 | Mastodon 4.1+ updates changed image descriptions through status media_attributes without re-uploading media            |
| 4374 | Older Mastodon versions fall back to a fresh upload when only the media description changes                            |
| 4461 | Full local-to-remote sync reuses stored media IDs when the attachments did not change                                  |
| 4561 | Full sync updates changed media descriptions through status media_attributes on Mastodon 4.1+                          |
| 5664 | FlatPress single-image entry exports Mastodon media_ids and strips raw image BBCode                                    |
| 5675 | FlatPress gallery entry exports Mastodon media_ids up to the instance limit and strips raw gallery BBCode              |
| 6526 | Asynchronous Mastodon media uploads are polled until the attachment is ready                                           |
| 6550 | Long-running Mastodon media uploads refresh the PHP execution budget for upload and polling                            |
| 6622 | Slow asynchronous Mastodon video uploads keep polling beyond the old short retry window                                |
| 6688 | Mastodon audio uploads keep polling even when pending responses have no preview_url                                    |
| 6834 | Deletion sync falls back to DELETE without delete_media for older Mastodon servers                                     |
| 7683 | Media upload cleanup deletes already uploaded attachments when a later media upload fails                              |
| 7750 | Media upload cleanup deletes uploaded attachments when final status creation fails                                     |

### OAuth and account setup

| Line | Static test name                                                                           |
| ---: | ------------------------------------------------------------------------------------------ |
| 3854 | OAuth code exchange                                                                        |
| 7807 | OAuth scope discovery prefers the profile scope on current Mastodon instances              |
| 7853 | OAuth scope discovery falls back to read:accounts on older Mastodon instances              |
| 7905 | Existing registered apps keep the legacy read:accounts scope until they are re-registered  |

### Other integration behavior

| Line | Static test name                                                                                      |
| ---: | ----------------------------------------------------------------------------------------------------- |
| 2483 | Mastodon companion-plugin detection respects FlatPress plugin_exists() for missing configured plugins |
| 2557 | Mastodon mention noise is cleaned for FlatPress                                                       |
| 2685 | Mastodon head metadata tolerates usernames stored with a leading at-sign                              |
| 2698 | Mastodon head metadata stays silent when no username is configured                                    |
| 3196 | Next day after 23:00 is due                                                                           |
| 3197 | Before configured time is not due                                                                     |

### Rate limits and budgets

| Line | Static test name                                                                             |
| ---: | -------------------------------------------------------------------------------------------- |
| 3247 | Central Mastodon rate-limit guard stops requests after the per-run request budget            |
| 3493 | Persistent Mastodon account-status paging window budget is shared across forced runs         |
| 3516 | Persistent account-status paging window budget is written to the synchronization last error  |
| 3937 | Mastodon URL budgeting respects characters_reserved_per_url when limiting plain-text exports |
| 6143 | Shared-request synchronization refreshes the PHP execution budget for long Mastodon work     |

### Scheduler, state, logging, and scale

| Line | Static test name                                                                                           |
| ---: | ---------------------------------------------------------------------------------------------------------- |
| 2471 | Mastodon companion-plugin detection uses FlatPress central enabled-plugin state                            |
| 3205 | Scheduled content synchronization uses a short file/APCu cooldown guard                                    |
| 3215 | Full Mastodon state fallback is not stored in APCu                                                         |
| 3610 | State entry mapping created                                                                                |
| 3662 | Mastodon runtime files in fp-content/plugin_mastodon use FlatPress FILE_PERMISSIONS                        |
| 3862 | State cache stays fresh after writing state.json                                                           |
| 3916 | Instance configuration cache avoids repeated /api/v2/instance requests                                     |
| 4882 | Known synchronized entry mappings older than the sync start date do not trigger context refreshes          |
| 5236 | Scheduled sync updates older dirty comments through direct YY/MM dirty-parent candidates                   |
| 5283 | Remote-write guard suppresses dirty tracking for Mastodon-owned entry_save calls                           |
| 5372 | Large scheduled dirty-tracking sync parses only active-window and dirty entries                            |
| 7996 | Instance information refresh persists a compact snapshot including the exact Mastodon version              |
| 8042 | Admin assignment exposes cached instance-information rows without triggering another live instance request |
| 8437 | Scheduler state is written as a compact summary without full mapping arrays                                |
| 8456 | Fresh scheduler-state read uses the APCu-capable FlatPress I/O path and does not load full state.json      |
| 8485 | Stale scheduler-state falls back to the full state and rebuilds the summary                                |
| 8506 | Manual admin synchronization still loads the full state before reporting configuration errors              |
| 8525 | sync.log uses append-only writes with size-based rotation                                                  |
| 8551 | Large skip volumes are logged as aggregate summaries                                                       |
| 8564 | Large 3000x10 state keeps scheduler-state compact and disables full APCu fallback                          |
| 8584 | Fresh large scheduler-state read avoids full state.json and uses APCu-capable file I/O                     |

### Text, tags, URLs, and conversion

| Line | Static test name                                                                                                    |
| ---: | ------------------------------------------------------------------------------------------------------------------- |
| 2518 | FlatPress locale is normalized to a Mastodon language code                                                          |
| 2536 | Mastodon HTML -> FlatPress BBCode                                                                                   |
| 2570 | Mastodon emojis become FlatPress emoticons                                                                          |
| 2578 | FlatPress BBCode -> Mastodon text                                                                                   |
| 2586 | FlatPress URLs lose HTML-like attributes and omit localhost links                                                   |
| 2598 | FlatPress entry tags become a Mastodon hashtag footer                                                               |
| 2623 | Mastodon tags become FlatPress tag BBCode without a visible hashtag footer                                          |
| 2646 | Removing Mastodon tags removes FlatPress tag BBCode on update                                                       |
| 2659 | FlatPress entry without tag BBCode has no Mastodon hashtag footer                                                   |
| 2672 | Mastodon head metadata uses the configured instance URL and username                                                |
| 2733 | FlatPress entry export converts Emoticons plugin shortcodes in titles and bodies to Unicode emoji for Mastodon      |
| 2772 | Long local entry export is limited even without a public permalink                                                  |
| 3974 | Entry export uses clean Mastodon text and suppresses localhost permalinks                                           |
| 4100 | Entry export creates a top-level Mastodon status                                                                    |
| 4189 | Create and update status requests include the configured Mastodon language code                                     |
| 5075 | Explicit full manual synchronization bypasses the automatic window while keeping normal limits                      |
| 8059 | Changing the configured instance URL invalidates the saved instance-information snapshot                            |
| 8097 | Sync-related instance limit helpers reuse the stored instance snapshot without another /api/v2/instance request     |
| 8210 | Manual sync exports Emoticons plugin shortcodes from FlatPress entries to Unicode emoji in Mastodon status requests |

### Optional live/auth smoke

| Line | Static test name                                     |
| ---: | ---------------------------------------------------- |
| 3962 | Configured credentials verify (read-only smoke test) |

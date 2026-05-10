# 05 - Test and Regression Matrix

## Harness model

`simulate_mastodon_plugin.php` loads the real Mastodon plugin and simulates the FlatPress and Mastodon boundaries. The plugin code itself is not replaced. The HTTP layer, fixture content, media files and some FlatPress services are controlled by the harness so behavior is deterministic.

Normal run checked for this documentation set:

```text
php simulate_mastodon_plugin.php
Exit-code: 0
[OK]: 164
[FAIL]: 0
```

Static `test_result()` calls found in the script: `165`.

One test is optional and only runs with `--live-auth`:

```text
Configured credentials verify (read-only smoke test)
```

## Category summary

| Category                             | Static tests |
| ------------------------------------ | -----------: |
| Comments and reply threads           |           34 |
| Content sync local/remote            |            4 |
| Deletion sync and tombstones         |           31 |
| Media export/import/reuse            |           27 |
| OAuth and account setup              |            5 |
| Other integration behavior           |           22 |
| Rate limits and budgets              |            7 |
| Scheduler, state, logging, and scale |           16 |
| Text, tags, URLs, and conversion     |           19 |

## What the harness proves well

| Area                                     | What is real                                                                    | What is simulated                                         |
|------------------------------------------|---------------------------------------------------------------------------------|-----------------------------------------------------------|
| State read/write and scheduler summaries | Plugin state logic and actual files in the sandbox.                             | Test-specific content directory and fixture state.        |
| Mastodon API behavior                    | Plugin API selection, payload building, fallback handling and response parsing. | HTTP responses and remote status/media payloads.          |
| Media handling                           | Local extraction, MIME/type decisions, upload planning, cleanup logic.          | Actual remote processing and CDN behavior.                |
| Text conversion                          | Real conversion helpers.                                                        | Fixture strings and expected results.                     |
| Deletion sync                            | Real deletion orchestration and status delete fallback.                         | Remote deletion responses and missing-status scenarios.   |
| Admin assignment                         | Real admin assignment function and Smarty variable preparation.                 | Test harness Smarty object.                               |

## What still needs real-world testing

- Real Mastodon instances with different configuration values and moderation modes.
- Real media processing latency, storage/CDN failures and server-specific MIME policies.
- Webhost-specific file locking, permissions and long request timeouts.
- OAuth app registration on instances with custom policies.
- Browser/Admin UI rendering with the actual FlatPress theme and Smarty version.

## Full static test catalog

### Comments and reply threads

| Line | Static test name                                                                                                                                            |
| ---- | ----------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2616 | Actual FlatPress comment exports as readable Mastodon reply                                                                                                 |
| 2626 | FlatPress comment export appends the public comment link on a new line                                                                                      |
| 2656 | FlatPress comment export converts Emoticons plugin shortcodes in author lines and bodies to Unicode emoji for Mastodon                                      |
| 3383 | Comment-as-entry import toggle normalizes safely                                                                                                            |
| 3770 | Comment export is reply-like, localized and emoji-aware                                                                                                     |
| 3838 | Remote comment imported through sync                                                                                                                        |
| 3844 | Nested remote comment keeps parent mapping metadata                                                                                                         |
| 3850 | Private mention reply is skipped during Mastodon import                                                                                                     |
| 3894 | Comment to entry exports with in_reply_to_id on the entry status                                                                                            |
| 3900 | Comment to comment exports with in_reply_to_id on the parent reply                                                                                          |
| 3906 | Entry and comment exports include the configured Mastodon language code                                                                                     |
| 4575 | New local comment on an already synchronized older entry is exported to Mastodon                                                                            |
| 4994 | Post-success comment hook queues older changed mapped comments                                                                                              |
| 5209 | Optional old-thread reply checks rotate through known synchronized threads                                                                                  |
| 5250 | Disabled old-thread reply checks do not refresh known synchronized entry contexts                                                                           |
| 5565 | Sync start date filters local exports by entry and comment date                                                                                             |
| 5651 | Sync start date filters remote imports by status and reply date                                                                                             |
| 5743 | Remote Mastodon imports honor the FlatPress timeoffset for stored entry/comment dates and ordering keys                                                     |
| 5980 | Synchronized local comments are not imported as duplicate entries from Mastodon by default                                                                  |
| 6072 | Synchronized local comments may be imported as entries when the toggle is enabled                                                                           |
| 6204 | Local FlatPress comments on imported Mastodon entries sync back to Mastodon, including nested replies                                                       |
| 7329 | Remote Mastodon comment updates increment the local-comment update counter instead of the entry counter                                                     |
| 7873 | Manual sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                        |
| 7891 | Replies to FlatPress comments on remote-sourced entries are exported as replies to the parent Mastodon reply                                                |
| 7907 | Non-forced sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                    |
| 7946 | Manual sync exports Emoticons plugin shortcodes from FlatPress comments to Unicode emoji in Mastodon reply requests                                         |
| 7967 | Manual sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled     |
| 7993 | Non-forced sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled |
| 8016 | Another Mastodon member reply to an exported FlatPress comment is still imported as a FlatPress comment when comment-as-entry import is enabled             |
| 8033 | Disabling the quote option imports a Mastodon reply to an exported FlatPress comment without a leading quote block                                          |
| 8047 | Imported Mastodon replies to another Mastodon reply quote the replied-to Mastodon user and text by default                                                  |
| 8066 | Disabling the quote option imports Mastodon reply-to-reply comments without a leading quote block                                                           |
| 8080 | Manual sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                      |
| 8104 | Non-forced sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                  |

### Content sync local/remote

| Line | Static test name                                                                                   |
| ---- | -------------------------------------------------------------------------------------------------- |
| 2466 | Imported Mastodon entry keeps the Mastodon link on a new line                                      |
| 2676 | Long local entry export is limited even without a public permalink                                 |
| 4801 | Normal manual synchronization bypasses the daily due check but still respects the automatic window |
| 4863 | Explicit full manual synchronization bypasses the automatic window while keeping normal limits     |

### Deletion sync and tombstones

| Line | Static test name                                                                                                                                                |
| ---- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 3093 | Central Mastodon rate-limit guard stops status deletions after the per-run delete budget                                                                        |
| 3119 | Cached Mastodon versions before 4.4 delete statuses without the delete_media query parameter                                                                    |
| 3143 | Status deletion retries without delete_media when an older Mastodon server rejects the query parameter                                                          |
| 3230 | Persistent Mastodon delete window budget is shared across forced runs                                                                                           |
| 3254 | Persistent delete window budget is written to the synchronization last error                                                                                    |
| 3680 | Legacy combined stats are migrated into separate content and deletion counters                                                                                  |
| 3801 | Deletion sync is queued for a follow-up request instead of running inside the content sync request                                                              |
| 3807 | Content sync updates the synchronization timestamp and counters while leaving the deletion timestamp for the follow-up request                                  |
| 3925 | Deletion sync waits at least five minutes after a completed content sync                                                                                        |
| 6507 | Follow-up deletion sync removes remote Mastodon content after local FlatPress deletion                                                                          |
| 6532 | Follow-up deletion sync updates last_deletion_run without overwriting the last synchronization timestamp or content counters                                    |
| 6588 | Deletion sync falls back to DELETE without delete_media for older Mastodon servers                                                                              |
| 6651 | Follow-up deletion sync removes mirrored FlatPress content after remote Mastodon deletion                                                                       |
| 6707 | Deletion sync skips old locally deleted mappings outside the sync start date window                                                                             |
| 6757 | Deletion sync skips old remotely mirrored mappings outside the sync start date window                                                                           |
| 6814 | Scheduled deletion sync skips remote lookups outside the automatic scheduled window                                                                             |
| 6887 | Scheduled deletion sync still propagates old local deletions outside the automatic window                                                                       |
| 7003 | Disabling deletion synchronization clears pending delete work without issuing deletion requests                                                                 |
| 7023 | Content sync before deletion sync protects a locally deleted exported FlatPress comment from stale re-import                                                    |
| 7042 | Deletion sync after the protected content sync reattaches the imported descendant reply to the synchronized entry status and keeps a later verification pending |
| 7071 | Deletion sync keeps imported descendants for a later follow-up verification when Mastodon still returns the child reply once                                    |
| 7088 | Tombstones prevent re-importing the deleted exported parent comment while descendant rechecks are still pending                                                 |
| 7101 | A later follow-up deletion sync removes the imported descendant once Mastodon reports the child reply missing                                                   |
| 7132 | Content sync before deletion sync also protects a locally deleted exported FlatPress comment when the imported descendant reply already has its own child       |
| 7152 | Deletion sync after the protected content sync keeps the descendant child below the reattached reply and verifies both remote replies in the same pass          |
| 7193 | Deletion sync removes imported descendants immediately when Mastodon already reports the child reply missing in the first follow-up pass                        |
| 7224 | Hybrid descendant rechecks keep only the direct child queued after the first stale follow-up pass of a deeper reply chain                                       |
| 7243 | Hybrid descendant rechecks remove the direct child and its direct child within the same targeted follow-up request                                              |
| 7361 | Admin assignment exposes split sync counters, local admin timestamps, and the deletion-sync option                                                              |
| 7437 | Media upload cleanup deletes already uploaded attachments when a later media upload fails                                                                       |
| 7504 | Media upload cleanup deletes uploaded attachments when final status creation fails                                                                              |

### Media export/import/reuse

| Line | Static test name                                                                                                       |
| ---- | ---------------------------------------------------------------------------------------------------------------------- |
| 2402 | Mastodon admin companion-plugin diagnostics cover BBCode, PhotoSwipe, AudioVideo, Tag and Emoticons                    |
| 2685 | FlatPress single-image entry exposes one uploadable media item                                                         |
| 2695 | FlatPress gallery entry exposes uploadable media items and keeps raw gallery BBCode out of Mastodon text               |
| 2748 | FlatPress AudioVideo entry exposes described audio/video media items and strips raw player BBCode from Mastodon text   |
| 2768 | AudioVideo BBCode parser renders optional description endtags as media attributes and keeps legacy single tags working |
| 2804 | Mastodon media upload sends AudioVideo files with descriptions and video thumbnail                                     |
| 2842 | Mastodon single image imports into a FlatPress image tag and stored file                                               |
| 2879 | Mastodon multiple images import into a FlatPress gallery with captions                                                 |
| 2928 | Mastodon audio and video attachments import into FlatPress AudioVideo BBCode and stored files                          |
| 2967 | Mastodon audio/video import retries alternate direct media URLs when the first download candidate fails                |
| 2981 | First sync is immediately due                                                                                          |
| 3069 | Central Mastodon rate-limit guard stops media uploads after the per-run media budget                                   |
| 3173 | Persistent Mastodon media-upload window budget is shared across forced runs                                            |
| 3203 | Persistent media-upload window budget is written to the synchronization last error                                     |
| 3795 | Remote entry imported through sync with Mastodon media visible in FlatPress                                            |
| 4020 | Initial Mastodon media uploads include the attachment description in POST /api/v2/media                                |
| 4052 | Unchanged entry media is reused without a new upload when only the post text changes                                   |
| 4125 | Mastodon 4.1+ updates changed image descriptions through status media_attributes without re-uploading media            |
| 4162 | Older Mastodon versions fall back to a fresh upload when only the media description changes                            |
| 4249 | Full local-to-remote sync reuses stored media IDs when the attachments did not change                                  |
| 4349 | Full sync updates changed media descriptions through status media_attributes on Mastodon 4.1+                          |
| 5418 | FlatPress single-image entry exports Mastodon media_ids and strips raw image BBCode                                    |
| 5429 | FlatPress gallery entry exports Mastodon media_ids up to the instance limit and strips raw gallery BBCode              |
| 6280 | Asynchronous Mastodon media uploads are polled until the attachment is ready                                           |
| 6304 | Long-running Mastodon media uploads refresh the PHP execution budget for upload and polling                            |
| 6376 | Slow asynchronous Mastodon video uploads keep polling beyond the old short retry window                                |
| 6442 | Mastodon audio uploads keep polling even when pending responses have no preview_url                                    |

### OAuth and account setup

| Line | Static test name                                                                          |
| ---- | ----------------------------------------------------------------------------------------- |
| 3642 | OAuth code exchange                                                                       |
| 3750 | Configured credentials verify (read-only smoke test)                                      |
| 7561 | OAuth scope discovery prefers the profile scope on current Mastodon instances             |
| 7607 | OAuth scope discovery falls back to read:accounts on older Mastodon instances             |
| 7659 | Existing registered apps keep the legacy read:accounts scope until they are re-registered |

### Other integration behavior

| Line | Static test name                                                                                      |
| ---- | ----------------------------------------------------------------------------------------------------- |
| 2387 | Mastodon companion-plugin detection respects FlatPress plugin_exists() for missing configured plugins |
| 2589 | Mastodon head metadata tolerates usernames stored with a leading at-sign                              |
| 2602 | Mastodon head metadata stays silent when no username is configured                                    |
| 2984 | Next day after 23:00 is due                                                                           |
| 2985 | Before configured time is not due                                                                     |
| 3319 | Admin synchronization time converts stored UTC to FlatPress local time and back                       |
| 3341 | Admin synchronization time conversion supports fractional FlatPress offsets                           |
| 3357 | Sync start date normalization accepts valid ISO dates                                                 |
| 3363 | Local and remote date helpers derive stable date keys                                                 |
| 3372 | Remote-to-local update toggle normalizes safely                                                       |
| 3450 | Mastodon runtime files in fp-content/plugin_mastodon use FlatPress FILE_PERMISSIONS                   |
| 3832 | Remote imported entry keeps a stable FlatPress date                                                   |
| 3888 | Entry export creates a top-level Mastodon status                                                      |
| 4473 | Batch entry export keeps older FlatPress entries below newer ones on Mastodon                         |
| 4744 | Scheduled content synchronization respects the automatic recent-content window                        |
| 4942 | Older changed mapped FlatPress entries are synchronized through the dirty queue                       |
| 5037 | Remote-write guard suppresses dirty tracking for Mastodon-owned entry_save calls                      |
| 5373 | Remote updates do not overwrite existing local content when the toggle is disabled                    |
| 5393 | Remote updates overwrite existing local content when the toggle is enabled                            |
| 5814 | Remote sync start filtering respects the FlatPress timeoffset near midnight                           |
| 5879 | Sync reports local export failures instead of silently succeeding                                     |
| 7750 | Instance information refresh persists a compact snapshot including the exact Mastodon version         |

### Rate limits and budgets

| Line | Static test name                                                                                                |
| ---- | --------------------------------------------------------------------------------------------------------------- |
| 3035 | Central Mastodon rate-limit guard stops requests after the per-run request budget                               |
| 3281 | Persistent Mastodon account-status paging window budget is shared across forced runs                            |
| 3304 | Persistent account-status paging window budget is written to the synchronization last error                     |
| 3704 | Instance configuration cache avoids repeated /api/v2/instance requests                                          |
| 5897 | Shared-request synchronization refreshes the PHP execution budget for long Mastodon work                        |
| 7796 | Admin assignment exposes cached instance-information rows without triggering another live instance request      |
| 7851 | Sync-related instance limit helpers reuse the stored instance snapshot without another /api/v2/instance request |

### Scheduler, state, logging, and scale

| Line | Static test name                                                                                       |
| ---- | ------------------------------------------------------------------------------------------------------ |
| 2374 | Mastodon companion-plugin detection uses FlatPress central enabled-plugin state                        |
| 2993 | Scheduled content synchronization uses a short file/APCu cooldown guard                                |
| 3003 | Full Mastodon state fallback is not stored in APCu                                                     |
| 3398 | State entry mapping created                                                                            |
| 3399 | State comment mapping created                                                                          |
| 3650 | State cache stays fresh after writing state.json                                                       |
| 5126 | Large scheduled dirty-tracking sync parses only active-window and dirty entries                        |
| 6948 | Large scheduled deletion syncs resume from the saved entry cursor                                      |
| 8157 | Scheduler state is written as a compact summary without full mapping arrays                            |
| 8176 | Fresh scheduler-state read uses the APCu-capable FlatPress I/O path and does not load full state.json  |
| 8205 | Stale scheduler-state falls back to the full state and rebuilds the summary                            |
| 8226 | Manual admin synchronization still loads the full state before reporting configuration errors          |
| 8245 | sync.log uses append-only writes with size-based rotation                                              |
| 8271 | Large skip volumes are logged as aggregate summaries                                                   |
| 8284 | Large 3000x10 state keeps scheduler-state compact and disables full APCu fallback                      |
| 8304 | Fresh large scheduler-state read avoids full state.json and uses APCu-capable file I/O                 |

### Text, tags, URLs, and conversion

| Line | Static test name                                                                                                    |
| ---- | ------------------------------------------------------------------------------------------------------------------- |
| 2422 | FlatPress locale is normalized to a Mastodon language code                                                          |
| 2440 | Mastodon HTML -> FlatPress BBCode                                                                                   |
| 2461 | Mastodon mention noise is cleaned for FlatPress                                                                     |
| 2474 | Mastodon emojis become FlatPress emoticons                                                                          |
| 2482 | FlatPress BBCode -> Mastodon text                                                                                   |
| 2490 | FlatPress URLs lose HTML-like attributes and omit localhost links                                                   |
| 2502 | FlatPress entry tags become a Mastodon hashtag footer                                                               |
| 2527 | Mastodon tags become FlatPress tag BBCode without a visible hashtag footer                                          |
| 2550 | Removing Mastodon tags removes FlatPress tag BBCode on update                                                       |
| 2563 | FlatPress entry without tag BBCode has no Mastodon hashtag footer                                                   |
| 2576 | Mastodon head metadata uses the configured instance URL and username                                                |
| 2637 | FlatPress entry export converts Emoticons plugin shortcodes in titles and bodies to Unicode emoji for Mastodon      |
| 3725 | Mastodon URL budgeting respects characters_reserved_per_url when limiting plain-text exports                        |
| 3762 | Entry export uses clean Mastodon text and suppresses localhost permalinks                                           |
| 3826 | Private mention status is skipped during Mastodon import                                                            |
| 3977 | Create and update status requests include the configured Mastodon language code                                     |
| 4670 | Known synchronized entry mappings older than the sync start date do not trigger context refreshes                   |
| 7813 | Changing the configured instance URL invalidates the saved instance-information snapshot                            |
| 7930 | Manual sync exports Emoticons plugin shortcodes from FlatPress entries to Unicode emoji in Mastodon status requests |

## Process-to-test coverage map

| Process                    | Risk covered                                                     | Representative static test names                                                                                                                                                        |
| -------------------------- | ---------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| P1 Scheduled content sync  | Scheduler state, cooldown, large-state fast path, APCu path      | Scheduler state is written as a compact summary; Fresh scheduler-state read uses the APCu-capable FlatPress I/O path; Large 3000x10 state keeps scheduler-state compact                 |
| P2 Manual content sync     | Forced normal/full sync behavior and configuration failures      | Normal manual synchronization bypasses the daily due check; Explicit full manual synchronization bypasses the automatic window; Manual admin synchronization still loads the full state |
| P3 Remote top-level import | Remote import filters, content window, media/tags                | Remote-sourced entries and import/update tests; Mastodon tags become FlatPress tag BBCode                                                                                               |
| P4 Remote reply import     | Context descendants, nested replies, self-replies, quote option  | Imported Mastodon replies to another Mastodon reply quote the replied-to Mastodon user; self-reply/comment-as-entry tests                                                               |
| P5 Local entry export      | Text limits, URLs, tags, create/update requests                  | FlatPress BBCode -> Mastodon text; Long local entry export is limited; tag footer tests                                                                                                 |
| P6 Local comment export    | Reply target resolution and nested comments                      | Actual FlatPress comment exports as readable Mastodon reply; replies to FlatPress comments on remote-sourced entries                                                                    |
| P7 Media export            | Extraction, upload, polling, reuse, alt text, cleanup            | Single image/gallery/AudioVideo upload tests; media_attributes/reupload tests; cleanup tests                                                                                            |
| P8 Media import            | Remote URL fallback, BBCode/AudioVideo generation                | Mastodon media attachments import into image/gallery/AudioVideo BBCode                                                                                                                  |
| P9 Deletion sync           | Missing local/remote mappings, delete_media fallback, tombstones | Cached Mastodon versions before 4.4 delete statuses without delete_media; fallback retry; deletion sync tests                                                                           |
| P10 Dirty hooks            | Entry/comment save/delete and remote-write guard                 | Dirty tracking and deletion-pending hook tests                                                                                                                                          |
| P11 OAuth/capabilities     | Scope discovery, legacy scope retention, instance cache          | OAuth scope discovery tests; instance configuration cache tests                                                                                                                         |
| P12 Admin diagnostics      | Admin assignments and companion plugin status                    | Admin companion-plugin diagnostics; admin assignment exposes counters and timestamps                                                                                                    |

## When to add a new regression test

Add or update a test when a change modifies any of these:

1. The shape of a Mastodon API request.
2. A fallback decision based on instance version or response code.
3. A state field that can survive across requests.
4. Dirty/deletion behavior from FlatPress hooks.
5. Media upload/reuse/cleanup behavior.
6. Remote reply parent resolution, tombstones or recheck queues.
7. Scheduler fast path, large-state behavior or rate-limit budgets.

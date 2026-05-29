# 05 - Regression Matrix

## Harness model
`simulate_mastodon_plugin.php` loads the real Mastodon plugin and simulates the FlatPress and Mastodon boundaries. The plugin code itself is not replaced. The HTTP layer, fixture content, media files and some FlatPress services are controlled by the harness so behavior is deterministic.
Normal run checked for this documentation set after the summary-output update:
```text
php simulate_mastodon_plugin.php
Exit-code: 0
[OK]: 188
[FAIL]: 0
[WARN]: 0
[SKIP]: 0
```
Static `test_result()` calls found in the script: `189`.
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
| Other integration behavior           |           10 |
| Rate limits and budgets              |            5 |
| Scheduler, state, logging, and scale |           31 |
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

### Comments and reply threads

| Line | Static test name                                                                                                                                            |
| ---: | ----------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 3362 | Actual FlatPress comment exports as readable Mastodon reply                                                                                                 |
| 3372 | FlatPress comment export appends the public comment link on a new line                                                                                      |
| 3402 | FlatPress comment export converts Emoticons plugin shortcodes in author lines and bodies to Unicode emoji for Mastodon                                      |
| 4243 | Comment-as-entry import toggle normalizes safely                                                                                                            |
| 4259 | State comment mapping created                                                                                                                               |
| 4715 | Comment export is reply-like, localized and emoji-aware                                                                                                     |
| 4783 | Remote comment imported through sync                                                                                                                        |
| 4789 | Nested remote comment keeps parent mapping metadata                                                                                                         |
| 4795 | Private mention reply is skipped during Mastodon import                                                                                                     |
| 4839 | Comment to entry exports with in_reply_to_id on the entry status                                                                                            |
| 4845 | Comment to comment exports with in_reply_to_id on the parent reply                                                                                          |
| 4851 | Entry and comment exports include the configured Mastodon language code                                                                                     |
| 5519 | New local comment on an already synchronized older entry is exported to Mastodon                                                                            |
| 5938 | Post-success comment hook queues older changed mapped comments                                                                                              |
| 6187 | Optional old-thread reply checks rotate through known synchronized threads                                                                                  |
| 6228 | Disabled old-thread reply checks do not refresh known synchronized entry contexts                                                                           |
| 6543 | Sync start date filters local exports by entry and comment date                                                                                             |
| 6629 | Sync start date filters remote imports by status and reply date                                                                                             |
| 6721 | Remote Mastodon imports honor the FlatPress timeoffset for stored entry/comment dates and ordering keys                                                     |
| 6958 | Synchronized local comments are not imported as duplicate entries from Mastodon by default                                                                  |
| 7050 | Synchronized local comments may be imported as entries when the toggle is enabled                                                                           |
| 7182 | Local FlatPress comments on imported Mastodon entries sync back to Mastodon, including nested replies                                                       |
| 8305 | Remote Mastodon comment updates increment the local-comment update counter instead of the entry counter                                                     |
| 8849 | Manual sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                        |
| 8867 | Replies to FlatPress comments on remote-sourced entries are exported as replies to the parent Mastodon reply                                                |
| 8897 | Unsynchronized local parent comment is exported before local child reply                                                                                    |
| 8917 | Non-forced sync exports FlatPress comments on remote-sourced entries to Mastodon replies                                                                    |
| 8956 | Manual sync exports Emoticons plugin shortcodes from FlatPress comments to Unicode emoji in Mastodon reply requests                                         |
| 8977 | Manual sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled     |
| 9003 | Non-forced sync imports another Mastodon member reply to an exported FlatPress comment only as a FlatPress comment when comment-as-entry import is disabled |
| 9026 | Another Mastodon member reply to an exported FlatPress comment is still imported as a FlatPress comment when comment-as-entry import is enabled             |
| 9043 | Disabling the quote option imports a Mastodon reply to an exported FlatPress comment without a leading quote block                                          |
| 9057 | Imported Mastodon replies to another Mastodon reply quote the replied-to Mastodon user and text by default                                                  |
| 9076 | Disabling the quote option imports Mastodon reply-to-reply comments without a leading quote block                                                           |
| 9090 | Manual sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                      |
| 9114 | Non-forced sync imports a Mastodon self-reply to an exported FlatPress comment only as a FlatPress comment                                                  |

### Content sync local/remote

| Line | Static test name                                                                                   |
| ---: | -------------------------------------------------------------------------------------------------- |
| 2812 | Entry file scanner skips comment directories while keeping real FlatPress entries                  |
| 2871 | Scheduled direct YY/MM scanner keeps active-window entries and dirty-comment parents               |
| 3212 | Imported Mastodon entry keeps the Mastodon link on a new line                                      |
| 3841 | First sync is immediately due                                                                      |
| 4179 | Admin synchronization time converts stored UTC to FlatPress local time and back                    |
| 4201 | Admin synchronization time conversion supports fractional FlatPress offsets                        |
| 4217 | Sync start date normalization accepts valid ISO dates                                              |
| 4223 | Local and remote date helpers derive stable date keys                                              |
| 4232 | Remote-to-local update toggle normalizes safely                                                    |
| 4771 | Private mention status is skipped during Mastodon import                                           |
| 4777 | Remote imported entry keeps a stable FlatPress date                                                |
| 5417 | Batch entry export keeps older FlatPress entries below newer ones on Mastodon                      |
| 5688 | Scheduled content synchronization respects the automatic recent-content window                     |
| 5745 | Normal manual synchronization bypasses the daily due check but still respects the automatic window |
| 5886 | Older changed mapped FlatPress entries are synchronized through the dirty queue                    |
| 6351 | Remote updates do not overwrite existing local content when the toggle is disabled                 |
| 6371 | Remote updates overwrite existing local content when the toggle is enabled                         |
| 6792 | Remote sync start filtering respects the FlatPress timeoffset near midnight                        |
| 6857 | Sync reports local export failures instead of silently succeeding                                  |

### Deletion sync and tombstones

| Line | Static test name                                                                                                                                                |
| ---: | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 3953 | Central Mastodon rate-limit guard stops status deletions after the per-run delete budget                                                                        |
| 4090 | Persistent Mastodon delete window budget is shared across forced runs                                                                                           |
| 4114 | Persistent delete window budget is written to the synchronization last error                                                                                    |
| 4625 | Legacy combined stats are migrated into separate content and deletion counters                                                                                  |
| 4746 | Deletion sync is queued for a follow-up request instead of running inside the content sync request                                                              |
| 4752 | Content sync updates the synchronization timestamp and counters while leaving the deletion timestamp for the follow-up request                                  |
| 4870 | Deletion sync waits at least five minutes after a completed content sync                                                                                        |
| 7485 | Follow-up deletion sync removes remote Mastodon content after local FlatPress deletion                                                                          |
| 7510 | Follow-up deletion sync updates last_deletion_run without overwriting the last synchronization timestamp or content counters                                    |
| 7629 | Follow-up deletion sync removes mirrored FlatPress content after remote Mastodon deletion                                                                       |
| 7684 | Deletion sync skips old locally deleted mappings outside the sync start date window                                                                             |
| 7734 | Deletion sync skips old remotely mirrored mappings outside the sync start date window                                                                           |
| 7791 | Scheduled deletion sync skips remote lookups outside the automatic scheduled window                                                                             |
| 7864 | Scheduled deletion sync still propagates old local deletions outside the automatic window                                                                       |
| 7925 | Large scheduled deletion syncs resume from the saved entry cursor                                                                                               |
| 7980 | Disabling deletion synchronization clears pending delete work without issuing deletion requests                                                                 |
| 8000 | Content sync before deletion sync protects a locally deleted exported FlatPress comment from stale re-import                                                    |
| 8019 | Deletion sync after the protected content sync reattaches the imported descendant reply to the synchronized entry status and keeps a later verification pending |
| 8048 | Deletion sync keeps imported descendants for a later follow-up verification when Mastodon still returns the child reply once                                    |
| 8065 | Tombstones prevent re-importing the deleted exported parent comment while descendant rechecks are still pending                                                 |
| 8078 | A later follow-up deletion sync removes the imported descendant once Mastodon reports the child reply missing                                                   |
| 8109 | Content sync before deletion sync also protects a locally deleted exported FlatPress comment when the imported descendant reply already has its own child       |
| 8129 | Deletion sync after the protected content sync keeps the descendant child below the reattached reply and verifies both remote replies in the same pass          |
| 8169 | Deletion sync removes imported descendants immediately when Mastodon already reports the child reply missing in the first follow-up pass                        |
| 8200 | Hybrid descendant rechecks keep only the direct child queued after the first stale follow-up pass of a deeper reply chain                                       |
| 8219 | Hybrid descendant rechecks remove the direct child and its direct child within the same targeted follow-up request                                              |
| 8337 | Admin assignment exposes split sync counters, local admin timestamps, and the deletion-sync option                                                              |

### Media export/import/reuse

| Line | Static test name                                                                                                       |
| ---: | ---------------------------------------------------------------------------------------------------------------------- |
| 3137 | Mastodon admin companion-plugin diagnostics cover BBCode, PhotoSwipe, AudioVideo, Tag and Emoticons                    |
| 3429 | FlatPress single-image entry exposes one uploadable media item                                                         |
| 3439 | FlatPress gallery entry exposes uploadable media items and keeps raw gallery BBCode out of Mastodon text               |
| 3492 | FlatPress AudioVideo entry exposes described audio/video media items and strips raw player BBCode from Mastodon text   |
| 3512 | AudioVideo BBCode parser renders optional description endtags as media attributes and keeps legacy single tags working |
| 3548 | Mastodon media upload sends AudioVideo files with descriptions and video thumbnail                                     |
| 3573 | Mastodon media planner exports only images when an entry mixes image, audio and video media                            |
| 3584 | Mastodon media planner exports exactly one audio attachment when an entry mixes audio and video media                  |
| 3598 | Mastodon media planner exports one video and keeps its poster as an upload thumbnail only                              |
| 3611 | Mastodon media planner keeps multiple images up to the Mastodon media limit                                            |
| 3640 | Mastodon export uploads only the selected audio item from an audio/video FlatPress entry                               |
| 3670 | Mastodon export sends a video poster as upload thumbnail instead of a second media ID                                  |
| 3702 | Mastodon single image imports into a FlatPress image tag and stored file                                               |
| 3739 | Mastodon multiple images import into a FlatPress gallery with captions                                                 |
| 3788 | Mastodon audio and video attachments import into FlatPress AudioVideo BBCode and stored files                          |
| 3827 | Mastodon audio/video import retries alternate direct media URLs when the first download candidate fails                |
| 3929 | Central Mastodon rate-limit guard stops media uploads after the per-run media budget                                   |
| 3979 | Cached Mastodon versions before 4.4 delete statuses without the delete_media query parameter                           |
| 4003 | Status deletion retries without delete_media when an older Mastodon server rejects the query parameter                 |
| 4033 | Persistent Mastodon media-upload window budget is shared across forced runs                                            |
| 4063 | Persistent media-upload window budget is written to the synchronization last error                                     |
| 4740 | Remote entry imported through sync with Mastodon media visible in FlatPress                                            |
| 4965 | Initial Mastodon media uploads include the attachment description in POST /api/v2/media                                |
| 4997 | Unchanged entry media is reused without a new upload when only the post text changes                                   |
| 5070 | Mastodon 4.1+ updates changed image descriptions through status media_attributes without re-uploading media            |
| 5107 | Older Mastodon versions fall back to a fresh upload when only the media description changes                            |
| 5194 | Full local-to-remote sync reuses stored media IDs when the attachments did not change                                  |
| 5294 | Full sync updates changed media descriptions through status media_attributes on Mastodon 4.1+                          |
| 6396 | FlatPress single-image entry exports Mastodon media_ids and strips raw image BBCode                                    |
| 6407 | FlatPress gallery entry exports Mastodon media_ids up to the instance limit and strips raw gallery BBCode              |
| 7258 | Asynchronous Mastodon media uploads are polled until the attachment is ready                                           |
| 7282 | Long-running Mastodon media uploads refresh the PHP execution budget for upload and polling                            |
| 7354 | Slow asynchronous Mastodon video uploads keep polling beyond the old short retry window                                |
| 7420 | Mastodon audio uploads keep polling even when pending responses have no preview_url                                    |
| 7566 | Deletion sync falls back to DELETE without delete_media for older Mastodon servers                                     |
| 8413 | Media upload cleanup deletes already uploaded attachments when a later media upload fails                              |
| 8480 | Media upload cleanup deletes uploaded attachments when final status creation fails                                     |

### OAuth and account setup

| Line | Static test name                                                                          |
| ---: | ----------------------------------------------------------------------------------------- |
| 4502 | OAuth code exchange                                                                       |
| 8537 | OAuth scope discovery prefers the profile scope on current Mastodon instances             |
| 8583 | OAuth scope discovery falls back to read:accounts on older Mastodon instances             |
| 8635 | Existing registered apps keep the legacy read:accounts scope until they are re-registered |

### Other integration behavior

| Line | Static test name                                                                                      |
| ---: | ----------------------------------------------------------------------------------------------------- |
| 2744 | Simulation summary mode shortens verbose JSON details                                                 |
| 2756 | Simulation final summary reports exit code and status counters                                        |
| 2777 | Simulation sandbox excludes live fp-content/content by default                                        |
| 2788 | Simulation sandbox can include live fp-content/content only when explicitly requested                 |
| 3124 | Mastodon companion-plugin detection respects FlatPress plugin_exists() for missing configured plugins |
| 3207 | Mastodon mention noise is cleaned for FlatPress                                                       |
| 3335 | Mastodon head metadata tolerates usernames stored with a leading at-sign                              |
| 3348 | Mastodon head metadata stays silent when no username is configured                                    |
| 3844 | Next day after 23:00 is due                                                                           |
| 3845 | Before configured time is not due                                                                     |

### Rate limits and budgets

| Line | Static test name                                                                             |
| ---: | -------------------------------------------------------------------------------------------- |
| 3895 | Central Mastodon rate-limit guard stops requests after the per-run request budget            |
| 4141 | Persistent Mastodon account-status paging window budget is shared across forced runs         |
| 4164 | Persistent account-status paging window budget is written to the synchronization last error  |
| 4670 | Mastodon URL budgeting respects characters_reserved_per_url when limiting plain-text exports |
| 6875 | Shared-request synchronization refreshes the PHP execution budget for long Mastodon work     |

### Scheduler, state, logging, and scale

| Line | Static test name                                                                                           |
| ---: | ---------------------------------------------------------------------------------------------------------- |
| 2920 | Scheduled direct scanner honors the 7-day admin window at parse stage                                      |
| 2955 | Scheduled direct scanner honors the 14-day admin window across a month boundary                            |
| 2990 | Scheduled direct scanner honors the 30-day admin window across a year boundary                             |
| 3024 | Scheduled direct scanner treats more than three dirty entries as mandatory candidates                      |
| 3064 | Scheduled direct scanner treats more than three dirty-comment parents as mandatory candidates              |
| 3091 | Manual force sync keeps the direct all-YY/MM scanner repair path                                           |
| 3115 | Mastodon companion-plugin detection uses FlatPress central enabled-plugin state                            |
| 3853 | Scheduled content synchronization uses a short file/APCu cooldown guard                                    |
| 3863 | Full Mastodon state fallback is not stored in APCu                                                         |
| 4258 | State entry mapping created                                                                                |
| 4310 | Mastodon runtime files in fp-content/plugin_mastodon use FlatPress FILE_PERMISSIONS                        |
| 4510 | State cache stays fresh after writing state.json                                                           |
| 4531 | Pretty-printed legacy state.json remains readable after compact-state optimization                         |
| 4577 | State write persists compact state.json while preserving round-trip mappings                               |
| 4649 | Instance configuration cache avoids repeated /api/v2/instance requests                                     |
| 5614 | Known synchronized entry mappings older than the sync start date do not trigger context refreshes          |
| 5968 | Scheduled sync updates older dirty comments through direct YY/MM dirty-parent candidates                   |
| 6015 | Remote-write guard suppresses dirty tracking for Mastodon-owned entry_save calls                           |
| 6104 | Large scheduled dirty-tracking sync parses only active-window and dirty entries                            |
| 8726 | Instance information refresh persists a compact snapshot including the exact Mastodon version              |
| 8772 | Admin assignment exposes cached instance-information rows without triggering another live instance request |
| 9167 | Scheduler state is written as a compact summary without full mapping arrays                                |
| 9186 | Fresh scheduler-state read uses the APCu-capable FlatPress I/O path and does not load full state.json      |
| 9215 | Stale scheduler-state falls back to the full state and rebuilds the summary                                |
| 9236 | Manual admin synchronization still loads the full state before reporting configuration errors              |
| 9255 | sync.log uses append-only writes with size-based rotation                                                  |
| 9281 | Large skip volumes are logged as aggregate summaries                                                       |
| 9294 | Small 300x10 state keeps scheduler-state compact and disables full APCu fallback                           |
| 9316 | Fresh small scheduler-state read avoids full state.json and uses APCu-capable file I/O                     |
| 9339 | Large 3000x10 state keeps scheduler-state compact and disables full APCu fallback                          |
| 9362 | Fresh large scheduler-state read avoids full state.json and uses APCu-capable file I/O                     |

### Text, tags, URLs, and conversion

| Line | Static test name                                                                                                    |
| ---: | ------------------------------------------------------------------------------------------------------------------- |
| 3168 | FlatPress locale is normalized to a Mastodon language code                                                          |
| 3186 | Mastodon HTML -> FlatPress BBCode                                                                                   |
| 3220 | Mastodon emojis become FlatPress emoticons                                                                          |
| 3228 | FlatPress BBCode -> Mastodon text                                                                                   |
| 3236 | FlatPress URLs lose HTML-like attributes and omit localhost links                                                   |
| 3248 | FlatPress entry tags become a Mastodon hashtag footer                                                               |
| 3273 | Mastodon tags become FlatPress tag BBCode without a visible hashtag footer                                          |
| 3296 | Removing Mastodon tags removes FlatPress tag BBCode on update                                                       |
| 3309 | FlatPress entry without tag BBCode has no Mastodon hashtag footer                                                   |
| 3322 | Mastodon head metadata uses the configured instance URL and username                                                |
| 3383 | FlatPress entry export converts Emoticons plugin shortcodes in titles and bodies to Unicode emoji for Mastodon      |
| 3420 | Long local entry export is limited even without a public permalink                                                  |
| 4707 | Entry export uses clean Mastodon text and suppresses localhost permalinks                                           |
| 4833 | Entry export creates a top-level Mastodon status                                                                    |
| 4922 | Create and update status requests include the configured Mastodon language code                                     |
| 5807 | Explicit full manual synchronization bypasses the automatic window while keeping normal limits                      |
| 8789 | Changing the configured instance URL invalidates the saved instance-information snapshot                            |
| 8827 | Sync-related instance limit helpers reuse the stored instance snapshot without another /api/v2/instance request     |
| 8940 | Manual sync exports Emoticons plugin shortcodes from FlatPress entries to Unicode emoji in Mastodon status requests |

### Optional live/auth smoke

| Line | Static test name                                     |
| ---: | ---------------------------------------------------- |
| 4695 | Configured credentials verify (read-only smoke test) |

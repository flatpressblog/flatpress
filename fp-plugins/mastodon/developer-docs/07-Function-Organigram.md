# 07 – PHP Function Organigram

## Purpose of this document

This document gives developers a structured overview of the Mastodon plugin PHP functions.

The layout is intentionally hierarchical so responsibilities, call paths, and important helper functions can be identified quickly.

## Scope

- The focus is the Mastodon plugin PHP code.
- The document is organized by responsibility and call path.
- The lightweight `scheduler-state.json` request-time summary is covered separately from the full `state.json` synchronization mapping state.
- Recently added FlatPress configuration reuse, centralized plugin-state detection, FlatPress I/O helpers, explicit `FILE_PERMISSIONS` handling for Mastodon runtime files, the compact APCu-capable scheduler-state summary, append-only rotated sync logging, and file-backed synchronization cooldown guards are reflected explicitly.
- The companion-plugin diagnostics for BBCode, PhotoSwipe, AudioVideo, Tag, and Emoticons are covered.
- The admin-side Mastodon instance-information snapshot, manual refresh button, exact-version display, and reuse of cached instance capabilities for later sync runs are covered.
- Mastodon instance-dependent URL budgeting, cached instance-version capability checks for status media-attribute edits and `delete_media` status deletion cleanup, bounded PHP execution-budget refreshes, central per-run Mastodon API rate-limit guards with request/media-upload/delete budgets, persistent cross-run media-upload/delete/account-status-page windows, OAuth scope discovery with a strict `profile`-scope preference and an older-instance fallback to `read:accounts`, media-type-aware asynchronous media-upload readiness polling for longer AudioVideo processing, AudioVideo optional endtag descriptions for local export and remote import, scheduled-run recent-content windows, Core post-success-hook-driven dirty queues for changed older mapped content, a Mastodon local-write guard for remote mirror operations, targeted non-full local candidate lists, optional rotating known-thread reply checks, best-effort cleanup of unattached uploaded Mastodon media before a failed final posting finishes, follow-up deletion synchronization with scheduled-window-limited remote existence lookups and progress cursors for large mapping sets, the admin toggle that can disable deletion synchronization, comment tombstones that block stale re-imports of deleted remote replies, early protection of locally deleted exported FlatPress comments before the next content sync, local reattachment of surviving descendant replies to the synchronized entry status during deletion follow-up, targeted descendant-recheck queues with a dedicated `comment_rechecks` follow-up scope, 5-minute scheduled-sync cooldown guards, state-write failure reporting, and the separate persistence of content-sync and deletion-sync counters are reflected explicitly.
- FlatPress timeoffset-aware remote import timestamps for Mastodon statuses and replies are reflected explicitly.
- FlatPress-local admin time display for the daily synchronization time, last content sync, and last deletion sync is reflected explicitly while keeping the stored synchronization time in UTC.
- Friendly deletion-sync guards for normalized comment mapping metadata are reflected explicitly.
- The default-enabled option to quote the replied-to Mastodon comment during comment import, including the replied-to user label, is reflected explicitly.
- The admin text for the known-thread reply-check option now explains that enabling it imports replies on already synchronized threads at the cost of additional Mastodon API requests.
- The admin panel now separates normal manual synchronization from explicit full admin runs: the normal button bypasses the daily due check but still respects the automatic 7/14/30-day window, while full synchronization and full deletion checks intentionally ignore that window but still obey budgets, persistent rate-limit windows, locks, and progress cursors.
- Export-side conversion of active Emoticons plugin shortcodes to standard Unicode emoji for FlatPress entry titles, entry bodies, comment author labels, and comment bodies is reflected explicitly.
- All plugin functions and admin panel methods currently present in the file are covered.
- Function summaries are derived from the current source file PHPDoc and verified against the function names and their location in the file.
- The terms **Entry** and **Comment** follow the wording used by the source code.

These protection mechanisms should always apply to both manual and scheduled runs:
| **Protection**                          | **Manual runs too?** | **Reason**                               |
| --------------------------------------- | -------------------: | ---------------------------------------- |
| 240 API requests per run                |                  Yes | Mastodon limit protection                |
| 24 media uploads per 30 minutes         |                  Yes | protects media limit                     |
| 24 deletions per 30 minutes             |                  Yes | protects delete limit                    |
| Paging window                           |                  Yes | protects paging bucket                   |
| Remote remaining floor                  |                  Yes | protects against scarce Mastodon headers |
| Progress cursor in delete sync          |                  Yes | prevents repetition of the same mappings |
| Safely process pending child rechecks   |                  Yes | data consistency                         |

Fixed runtime and request budgets:
| **Category**                           |                                 **Limit** |
| -------------------------------------- | ----------------------------------------: |
| Request budget per sync run            |                                     `240` |
| Media upload budget per sync run       |                                      `24` |
| Delete budget per sync run             |                                      `24` |
| Media upload window                    |             `24` uploads / `1800` seconds |
| Delete window                          |             `24` deletes / `1800` seconds |
| Status page window                     |        `300` status pages / `900` seconds |
| Remote Rate Limit Floor                |    stops at `X-RateLimit-Remaining <= 10` |
| Max. status pages during remote import |                                       `5` |
| Status page limit per API page         |                                      `40` |
| Default sync time                      |                                   `03:00` |
| Sync cooldown                          |                             `300` seconds |
| State fallback TTL                     |                             `300` seconds |

The plugin thus combines its own internal budgets with Mastodon’s Remote-RateLimit headers.
Throttling occurs upon an HTTP 429 response or if X-RateLimit-Remaining is too low.

Content, Characters, and Media:
| **Category**                | **Primary Source**                                                      |                                           **Fallback** |
| --------------------------- | ----------------------------------------------------------------------- | -----------------------------------------------------: |
| Status character limit      | `/api/v2/instance → configuration.statuses.max_characters`              |                                                  `500` |
| Reserved characters per URL | `/api/v2/instance → configuration.statuses.characters_reserved_per_url` |                                                   `23` |
| Max. media attachments      | `/api/v2/instance → configuration.statuses.max_media_attachments`       |                                                    `4` |
| Media description limit     | `/api/v2/instance → configuration.media_attachments.description_limit`  |                                                 `1500` |
| Image size limit            | `/api/v2/instance → configuration.media_attachments.image_size_limit`   |                              no local limit if unknown |
| Video/audio size limit      | `/api/v2/instance → video_size_limit` / `audio_size_limit`              |    Audio falls back to video, otherwise no local limit |

Media Processing:
| **Area**                                     | **Limit / Behavior**                                               |
| -------------------------------------------- | ------------------------------------------------------------------ |
| Imported media width in FlatPress BBCode     | `320`                                                              |
| Image/other media processing attempts        | `12`                                                               |
| Video/GIFV processing attempts               | `60`, from 10 MiB `75`, from 50 MiB `90`                           |
| Audio processing attempts                    | `60`, from 50 MiB `75`                                             |
| Image/other upload timeouts                  | approx. `90` seconds                                               |
| Video/GIFV/audio upload timeouts             | approx. `180` seconds, from 50 MiB `300` seconds                   |
| Media polling                                | `GET /api/v1/media/:id`, retry wait time roughly `0.1–5.0` seconds |
| cURL redirect limit                          | `5`                                                                |
| cURL connect timeout                         | `15` seconds                                                       |

Persistence, Logs, and Regression Protection:
| **Category**                | **Limit / File**                                  |
| --------------------------- | ------------------------------------------------- |
| State file                  | `fp-content/plugin_mastodon/state.json`           |
| Scheduler state             | `fp-content/plugin_mastodon/scheduler-state.json` |
| Sync lock                   | `sync.lock`                                       |
| Guard file                  | `sync.guard.json`                                 |
| Rate Limit Window           | `rate-limit-windows.json`                         |
| Sync Log                    | `sync.log`                                        |
| Max. Log Size               | `1 MiB`                                           |
| Rotated Log Files           | `3`                                               |
| Pending Comment Rechecks    | `3`                                               |
| Old Thread Context Rotation | `3`                                               |

## Mastodon API Endpoints

- `GET /.well-known/oauth-authorization-server`
- `POST /api/v1/apps`
- `GET /oauth/authorize`
- `POST /oauth/token`
- `GET /api/v1/accounts/verify_credentials`
- `GET /api/v1/accounts/:id/statuses`
- `GET /api/v1/statuses/:id/context`
- `GET /api/v1/statuses/:id`
- `POST /api/v1/statuses`
- `PUT /api/v1/statuses/:id`
- `DELETE /api/v1/statuses/:id`
- `GET /api/v2/instance`
- `POST /api/v2/media`
- `GET /api/v1/media/:id`
- `DELETE /api/v1/media/:id`

Current export behavior uses these endpoints in a version-aware way:
- `POST /api/v2/media` carries the initial media `description` whenever the FlatPress entry already knows the alt text.
- `PUT /api/v1/statuses/:id` reuses stored `media_ids` for unchanged attachments and, on Mastodon 4.1+, can update changed alt text in place through `media_attributes` without re-uploading the file payload.
- `POST /api/v2/media` is preferred over deprecated `POST /api/v1/media`; audio, video, and GIFV uploads may return `202` and require polling `GET /api/v1/media/:id` until `url` is available. The plugin now uses longer, media-type-aware polling windows for audio/video/GIFV and treats pending audio/video responses without `preview_url` as still processing when the attachment ID/type is recognizable.
- Remote media import retries alternate direct media URLs (`url`, `remote_url`, and for images `preview_url`) and uses longer transfer timeouts for downloaded media, so temporary object-storage or CDN failures do not immediately lose AudioVideo imports.
- Current Mastodon media entities can be `image`, `gifv`, `video`, or `audio`; `audio` was added after early Mastodon media APIs, so import/export code must tolerate older payloads and unknown attachment types.
- Instance-provided limits from `/api/v2/instance` are authoritative when available: `statuses.max_media_attachments`, `media_attachments.supported_mime_types`, `image_size_limit`, `video_size_limit`, `audio_size_limit`, and description limits. Public defaults still commonly include 4 media per status, one video/audio per status, and server-specific size/transcoding limits.
- Official Mastodon API rate limits are protected locally by a per-run guard and persistent cross-run windows: the plugin reserves a conservative general request budget, a media-upload budget, and a status-delete budget for each run, counts media uploads and status deletes across a 30-minute file-backed window, counts account-status paging across a 15-minute file-backed window, and also observes `X-RateLimit-*` / `429` responses so synchronization stops cleanly with a visible state/log error before it repeatedly hammers the instance.

## Function count

The plugin file currently contains **337** callable functions/methods documented in this organigram:
- **334** top-level plugin functions
- **3** admin panel class methods (`setup()`, `main()`, `onsubmit()`)

## High-level call flow

### Frontend and scheduler entry points

- `plugin_mastodon_head()`  
  Reads the saved Mastodon configuration, normalizes the instance URL and username, and emits:
  - `<link rel="me" ...>`
  - `<meta name="fediverse:creator" ...>`

- `plugin_mastodon_maybe_sync()`  
  Reads only the compact scheduler-state summary for normal non-POST frontend requests and then either:
  - runs `plugin_mastodon_run_sync()` when the scheduled content sync is due, or
  - runs `plugin_mastodon_run_deletion_sync()` in a later follow-up request when deletion work is pending and its persisted not-before timestamp has passed.
  The full `state.json` mapping state is loaded only after a real synchronization path starts or when the scheduler summary is missing/stale and must be rebuilt conservatively.

- `plugin_mastodon_run_sync()`  
  Refreshes the shared-request PHP execution budget through `plugin_mastodon_extend_time_limit()`, protects locally deleted exported FlatPress comment mappings through `plugin_mastodon_protect_locally_deleted_exported_comments()` so stale Mastodon thread context cannot resurrect them during the same request, and then executes the two main directions in order:
  1. `plugin_mastodon_sync_remote_to_local()`
  2. `plugin_mastodon_sync_local_to_remote()`
  The first boolean argument means "explicitly triggered / bypass the daily due check"; the optional second boolean controls whether the automatic scheduled window is ignored. This keeps normal admin-triggered synchronization aligned with scheduled behavior (`true, false`) while preserving explicit full checks (`true, true`). Non-forced scheduled runs are gated by `plugin_mastodon_sync_guard_active()` and mark a 5-minute `content` cooldown through `plugin_mastodon_sync_guard_mark()` before network work starts. After acquiring the sync lock, the function starts a per-run Mastodon API guard through `plugin_mastodon_rate_limit_guard_start()`, so general requests, media uploads, status deletes, persistent cross-run media/delete windows, persistent account-status paging windows, and Mastodon `X-RateLimit-*` headers can stop the run cleanly. If both directions finish successfully, the function marks a follow-up deletion pass as pending for a later web request and stores a `deletions_not_before` timestamp at least 5 minutes after the completed content sync. A completed sync is reported as failed if `plugin_mastodon_state_write()` cannot persist the updated state; the plugin no longer places the full mapping state into APCu.

- `plugin_mastodon_run_deletion_sync()`  
  Runs the real deletion synchronization in a separate follow-up request. It first checks `plugin_mastodon_should_run_deletion_sync()` so the user-controlled admin toggle can disable the delete pass, then checks `plugin_mastodon_deletion_sync_due()` so the delete pass cannot start before the persisted 5-minute separation window has passed. It applies the same 5-minute cooldown guard with the `deletion` kind for non-forced scheduled runs, starts the per-run Mastodon API guard after acquiring the sync lock, and every remote status delete must pass both the per-run delete budget and the persistent cross-run delete window; for Mastodon servers before 4.4.0 it can omit `delete_media=1` from the first request when cached instance information proves the older version, and otherwise retries once without that query parameter when the server rejects it. It resets only `deletion_stats` while preserving `content_stats` from the last content sync, gates all mapped items through `plugin_mastodon_mapping_matches_sync_start()` so the delete pass stays inside the manual sync-start window, and uses `plugin_mastodon_mapping_matches_deletion_lookup_window()` to limit only remote existence lookups for still-existing local items to the automatic scheduled 7/14/30-day window. Local deletions are still propagated to Mastodon when they are inside the manual sync-start range, even if they are outside the automatic scheduled window. Large full delete passes advance through `deletion_cursor_entries` and `deletion_cursor_comments` via `plugin_mastodon_mapping_keys_after_cursor()`, so later runs continue after the last successfully checked mapping instead of repeating the first batch. The function then either runs the full reconciliation pass or a targeted `comment_rechecks` follow-up scope via `plugin_mastodon_state_has_comment_recheck_scope()`. Direct descendants of newly deleted replies are queued with `plugin_mastodon_queue_comment_descendant_remote_rechecks()`, surviving direct child replies can be reattached to the synchronized entry status through `plugin_mastodon_reattach_local_comment_to_entry_status()`, and pending deep-thread cleanup is processed breadth-first through `plugin_mastodon_process_pending_comment_remote_rechecks()`. A successful delete pass is reported as failed if the resulting state cannot be persisted.

### Local → remote export path

- `plugin_mastodon_sync_local_to_remote()`
  - loads options and persisted state
  - retrieves export candidates with `plugin_mastodon_list_local_entries_for_sync()`: scheduled/non-full runs parse only the active recent-content window plus dirty-entry targets, while explicit full repair runs keep the complete local scan
  - applies the permanent `sync_start_date` lower bound and, for scheduled non-forced runs, the selected 7/14/30-day recent-content window
  - relies on Core post-success hooks to queue changed older mapped entries/comments and local mapped deletions, so scheduled/non-full runs do not parse all old files merely to discover dirty work
  - orders entries with `plugin_mastodon_compare_local_entries_for_export()`
  - builds entry text with `plugin_mastodon_build_entry_status_text()`
  - budgets status length via `plugin_mastodon_instance_url_reserved_length()`, `plugin_mastodon_status_text_length()`, and `plugin_mastodon_limit_status_text()`
  - refreshes the request time budget for long-running export loops and Mastodon communication through `plugin_mastodon_extend_time_limit()`
  - derives the Mastodon `language` value through `plugin_mastodon_configured_status_language()` and `plugin_mastodon_normalize_status_language()`
  - collects local images, galleries, AudioVideo `[audioplayer]` tags, and AudioVideo `[videoplayer]` tags with `plugin_mastodon_collect_local_entry_media()`, including optional text between AudioVideo opening and closing tags
  - normalizes media items, applies Mastodon's media-family policy through `plugin_mastodon_select_status_media_items()`, and computes attachment/description signatures from the selected media set with `plugin_mastodon_prepare_entry_media_items()`, `plugin_mastodon_entry_media_attachment_signature_from_items()`, and `plugin_mastodon_entry_media_description_signature_from_items()`
  - prepares a per-entry sync strategy with `plugin_mastodon_prepare_entry_media_sync_plan()`
  - uploads media through `plugin_mastodon_upload_media_items()` only when attachments really changed or when an older/unknown Mastodon version cannot edit descriptions in place, and each upload must pass the per-run media-upload budget enforced by `plugin_mastodon_rate_limit_acquire()`
  - sends initial descriptions in `POST /api/v2/media`, including image alt text, AudioVideo optional endtag text, and legacy AudioVideo description/title/alt attributes; video posters become Mastodon thumbnails when the local poster file is usable
  - reuses stored remote `media_ids` for text-only edits, and on Mastodon 4.1+ forwards changed descriptions through `plugin_mastodon_status_media_attributes()` and `plugin_mastodon_update_status()`
  - waits for asynchronously processed media through `plugin_mastodon_fetch_media_attachment()` and `plugin_mastodon_wait_for_media_attachment()` when required; `plugin_mastodon_media_processing_attempts()` gives audio, video, and GIFV uploads a longer bounded polling window than images
  - performs best-effort cleanup of unattached uploaded media through `plugin_mastodon_delete_media_attachment()` and `plugin_mastodon_cleanup_uploaded_media()` when a media upload or the final status create/update request fails
  - persists mappings and cached remote media metadata with `plugin_mastodon_state_set_entry_mapping()`, `plugin_mastodon_state_set_entry_media_meta()`, and `plugin_mastodon_state_set_comment_mapping()`
  - scans comment IDs directly with `plugin_mastodon_list_local_comment_ids()` so new local comments are not missed when FlatPress comment-list caches lag behind the filesystem
  - continues exporting local FlatPress comments even when the top-level entry itself is remote-sourced, while still skipping re-export of the remote entry status body
  - builds comment replies with `plugin_mastodon_build_comment_status_text()`
  - resolves reply targets with `plugin_mastodon_resolve_comment_reply_target()`
  - defers child-comment exports with `plugin_mastodon_local_comment_parent_export_pending()` until the parent reply already has a remote Mastodon ID, so backdated or out-of-order FlatPress replies still become proper Mastodon reply chains

### Remote → local import path

- `plugin_mastodon_sync_remote_to_local()`
  - refreshes the request time budget for long-running import loops and Mastodon communication through `plugin_mastodon_extend_time_limit()`
  - consumes pre-seeded tombstones from `plugin_mastodon_protect_locally_deleted_exported_comments()` so deleted exported FlatPress comments are not resurrected from later Mastodon thread-context refreshes
  - validates credentials with `plugin_mastodon_verify_credentials()`
  - fetches account statuses with `plugin_mastodon_fetch_account_statuses()`
  - applies the permanent `sync_start_date` lower bound and, for scheduled non-forced runs, the selected 7/14/30-day recent-content window to newly fetched statuses
  - imports top-level statuses with `plugin_mastodon_import_remote_entry()` and deliberately skips reply statuses as entry imports when `import_synced_comments_as_entries` is disabled, because Mastodon account timelines may still include self-replies even with `exclude_replies=true`
  - fetches thread context with `plugin_mastodon_fetch_status_context()`
  - imports replies with `plugin_mastodon_import_remote_context_descendants()`
  - imports individual replies through `plugin_mastodon_import_remote_comment()`
  - imports remote audio/video descriptions as optional `[audioplayer]...[/audioplayer]` and `[videoplayer]...[/videoplayer]` text so FlatPress keeps Mastodon alt text locally
  - blocks stale re-imports of deleted remote replies via `plugin_mastodon_state_has_comment_tombstone()`
  - optionally prepends a BBCode quote of the replied-to Mastodon comment (or the previously exported FlatPress comment) so FlatPress readers can see who was answered and what was answered to
  - refreshes newly fetched thread contexts immediately and, when enabled in the admin panel, refreshes older known synchronized threads through `plugin_mastodon_collect_known_entry_context_targets()` in bounded rotating batches while still skipping mappings outside the configured synchronization start-date window
  - aborts the import direction with the rate-limit reason when the central per-run guard has blocked further API work

### FlatPress Core post-success dirty-tracking hooks

The FlatPress Core emits the new `entry_saved`, `entry_deleted`, `comment_saved`, and `comment_deleted` hooks only after the corresponding write/delete operation has succeeded. The Mastodon plugin handlers use these hooks to persist dirty queues for changed older mapped entries/comments and deletion follow-up work. Remote import/delete code wraps its own FlatPress writes in a depth-based local-write guard so Mastodon mirror operations do not look like local manual edits.

### Admin panel flow

- `plugin_mastodon_admin_assign()` assigns the plugin data for the Smarty view, including companion-plugin diagnostics, the scheduled-window radio choices, the known-thread reply-check toggle, compact scheduler-state status/statistics, cached Mastodon instance-information rows, and cache-state/error metadata.
- `setup()` registers the admin template and delegates to `plugin_mastodon_admin_assign()`.
- `main()` keeps the FlatPress admin lifecycle stable.
- `onsubmit()` handles:
  - configuration normalization and storage
  - OAuth app registration
  - authorization code exchange
  - normal manual synchronization with scheduled-window behavior
  - explicit full synchronization from the manual start date
  - explicit full deletion reconciliation from the manual start date
  - explicit instance-information refreshes for the admin table
  - deferred deletion synchronization on later non-POST requests

## A. Entry points and admin integration

- `plugin_mastodon_head()` — line 2217 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_maybe_sync()` — line 10062 — Run the scheduled synchronization when the current request is due, using the compact scheduler summary before any full state load.
- `plugin_mastodon_run_sync()` — line 9979 — Run a full synchronization cycle.
- `plugin_mastodon_run_deletion_sync()` — line 9648 — Run the deferred deletion synchronization in a follow-up request after content sync completed, with scheduled-window-limited remote existence lookups and progress cursors.
- `plugin_mastodon_sync_due()` — line 9949 — Determine whether the scheduled synchronization is currently due.
- `plugin_mastodon_admin_boolean_label()` — line 10094 — Return a localized yes/no/unknown label for admin diagnostics.
- `plugin_mastodon_admin_add_info_row()` — line 10109 — Add one populated admin diagnostics row to the instance-information table.
- `plugin_mastodon_admin_assign()` — line 10228 — Assign plugin data to Smarty for the admin panel, including compact scheduler status/statistics and cached instance-information rows.
- `setup()` — line 10276 — Register the Mastodon admin panel template and assign plugin data to Smarty.
- `main()` — line 10281 — Keep the admin panel lifecycle compatible with FlatPress without extra processing.
- `onsubmit()` — line 10285 — Process configuration saves, OAuth actions, authorization-code exchange, manual instance-information refreshes, and the manual synchronization trigger.

## B. Defaults, configuration, secrets, and centralized FlatPress feature toggles

- `plugin_mastodon_default_options()` — line 120 — Return the default plugin option values.
- `plugin_mastodon_clear_saved_instance_info()` — line 152 — Remove persisted instance-information snapshots and related admin refresh errors from an option array.
- `plugin_mastodon_compact_instance_document()` — line 171 — Reduce `/api/v2/instance` responses to the stable subset reused by the plugin and admin table.
- `plugin_mastodon_saved_instance_document()` — line 376 — Decode the persisted compact instance snapshot from the saved plugin configuration.
- `plugin_mastodon_store_instance_document()` — line 421 — Persist a compact instance-information snapshot in the FlatPress plugin configuration and warm request/APCu caches.
- `plugin_mastodon_store_instance_error()` — line 467 — Persist the latest instance-information refresh failure for the admin diagnostics view.
- `plugin_mastodon_refresh_instance_information()` — line 483 — Force a live `/api/v2/instance` refresh, compact the response, and store it for later requests.
- `plugin_mastodon_default_content_stats()` — line 503 — Return the default counters for the last content synchronization.
- `plugin_mastodon_default_deletion_stats()` — line 520 — Return the default counters for the last deletion synchronization.
- `plugin_mastodon_default_state()` — line 533 — Return the default runtime state structure, including the targeted deletion-follow-up scope marker and deletion progress cursors.
- `plugin_mastodon_oauth_legacy_scopes()` — line 563 — Return the legacy OAuth scope string used before scope discovery was added.
- `plugin_mastodon_oauth_profile_scopes()` — line 571 — Return the stricter OAuth scope string that uses `profile` for `verify_credentials`.
- `plugin_mastodon_oauth_server_metadata()` — line 580 — Discover and cache OAuth authorization-server metadata from `/.well-known/oauth-authorization-server`.
- `plugin_mastodon_oauth_supported_scopes()` — line 599 — Parse the discoverable OAuth scopes supported by the configured Mastodon instance.
- `plugin_mastodon_oauth_scope_supported()` — line 636 — Check whether the configured Mastodon instance advertises support for a specific OAuth scope.
- `plugin_mastodon_oauth_preferred_scopes()` — line 657 — Prefer the narrow `profile` scope on current instances and fall back to `read:accounts` on older ones.
- `plugin_mastodon_oauth_scopes()` — line 674 — Return the OAuth scopes that the currently registered app may safely request.
- `plugin_mastodon_fp_config()` — line 751 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 787 — Read a nested FlatPress configuration value.
- `plugin_mastodon_fp_timeoffset_seconds()` — line 803 — Return the configured FlatPress time offset in seconds for exact local admin-time conversion.
- `plugin_mastodon_fp_timeoffset_label()` — line 823 — Format the configured FlatPress time offset as a UTC label for admin users.
- `plugin_mastodon_sync_time_to_minutes()` — line 853 — Convert a normalized `HH:MM` sync-time value into minutes after midnight.
- `plugin_mastodon_minutes_to_sync_time()` — line 864 — Convert minutes after midnight back into a normalized `HH:MM` sync-time value.
- `plugin_mastodon_sync_time_utc_to_local()` — line 880 — Convert the stored UTC synchronization time to the FlatPress-local admin time.
- `plugin_mastodon_sync_time_local_to_utc()` — line 891 — Convert the FlatPress-local admin synchronization time back to stored UTC.
- `plugin_mastodon_format_admin_datetime()` — line 902 — Format stored UTC timestamps for the admin panel using FlatPress `timeoffset`, date format, and time format.
- `plugin_mastodon_fp_timeoffset_hours()` — line 815 — Return the configured FlatPress time offset in whole hours.
- `plugin_mastodon_get_options()` — line 1859 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_save_options()` — line 1918 — Persist plugin options, invalidate mismatching instance snapshots on URL changes, and keep matching refreshed snapshots.
- `plugin_mastodon_secret_key()` — line 2010 — Build the encryption key used for stored secrets.
- `plugin_mastodon_secret_encode()` — line 2027 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_decode()` — line 2050 — Decode a previously stored secret value.
- `plugin_mastodon_normalize_instance_url()` — line 2081 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_head_username()` — line 2114 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_instance_authority()` — line 2153 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_profile_url()` — line 2183 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_fediverse_creator_value()` — line 2199 — Build the fediverse creator meta value.
- `plugin_mastodon_normalize_status_language()` — line 2257 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_configured_status_language()` — line 2279 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_normalize_sync_time()` — line 2299 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_sync_start_date()` — line 2316 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_scheduled_window_days()` — line 2342 — Normalize the configured 7/14/30-day automatic window for scheduled runs.
- `plugin_mastodon_scheduled_window_choices()` — line 2354 — Return the localized admin radio choices for the scheduled synchronization window.
- `plugin_mastodon_normalize_boolean_option()` — line 2367 — Normalize a boolean-like option value to the stored string representation.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 2383 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_should_update_local_from_remote()` — line 2392 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 2401 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 2410 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_normalize_quote_imported_reply_parent()` — line 2419 — Normalize the toggle that controls whether imported Mastodon replies should quote the replied-to comment.
- `plugin_mastodon_should_quote_imported_reply_parent()` — line 2428 — Check whether imported Mastodon replies should include a quote of the replied-to comment.
- `plugin_mastodon_normalize_old_thread_reply_check()` — line 2437 — Normalize the toggle that enables rotating context checks for known synchronized Mastodon threads.
- `plugin_mastodon_should_check_old_thread_replies()` — line 2446 — Check whether known synchronized Mastodon threads should be checked for replies in rotating batches.
- `plugin_mastodon_normalize_delete_sync_enabled()` — line 2455 — Normalize the toggle that enables or disables the follow-up deletion synchronization.
- `plugin_mastodon_should_run_deletion_sync()` — line 2464 — Check whether the follow-up deletion synchronization is enabled.
- `plugin_mastodon_enabled_plugin_state()` — line 4540 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_tag_plugin_active()` — line 4605 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_bbcode_plugin_active()` — line 4622 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_photoswipe_plugin_active()` — line 4639 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_audiovideo_plugin_active()` — line 4656 — Determine whether the AudioVideo plugin is active for the current FlatPress request.
- `plugin_mastodon_emoticons_plugin_active()` — line 4673 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_companion_plugins_status()` — line 4688 — Return the status of companion FlatPress plugins used for the full Mastodon feature set.

## C. Caching, filesystem helpers, logging, and persisted state

- `plugin_mastodon_runtime_cache_get()` — line 694 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 718 — Store a value in the request-local plugin cache.
- `plugin_mastodon_runtime_cache_clear()` — line 736 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_io_read_file()` — line 936 — Read a file through the FlatPress I/O layer when available, allowing the core APCu file hotcache for small files.
- `plugin_mastodon_io_read_file_uncached()` — line 952 — Read a file without FlatPress request-local caches.
- `plugin_mastodon_file_permissions_mode()` — line 970 — Return the FlatPress `FILE_PERMISSIONS` mode used for plugin runtime files.
- `plugin_mastodon_apply_file_permissions()` — line 979 — Apply FlatPress `FILE_PERMISSIONS` to a plugin runtime file.
- `plugin_mastodon_io_write_file()` — line 992 — Write a file through the FlatPress I/O layer when available and enforce `FILE_PERMISSIONS` after successful writes.
- `plugin_mastodon_io_append_file()` — line 1013 — Append to a file with `FILE_APPEND | LOCK_EX`, without re-reading/re-writing the complete log payload.
- `plugin_mastodon_log_max_bytes()` — line 1053 — Return the sync.log rotation size limit, with a simulation override for load tests.
- `plugin_mastodon_log_rotate_files()` — line 1064 — Return the number of retained rotated sync logs.
- `plugin_mastodon_io_rotate_file_if_needed()` — line 1077 — Rotate `sync.log` before append when the next line would exceed the size cap.
- `plugin_mastodon_apcu_enabled()` — line 1116 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_cache_key()` — line 1125 — Build the namespaced APCu key used by this plugin.
- `plugin_mastodon_apcu_fetch()` — line 1139 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 1154 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_delete()` — line 1167 — Delete a value from APCu using FlatPress `apcu_delete_key()` when available.
- `plugin_mastodon_state_fallback_key()` — line 1186 — Return the legacy APCu key that used to hold a full last-known-good state fallback.
- `plugin_mastodon_state_fallback_store()` — line 1195 — Remove the legacy full-state APCu fallback instead of storing large mapping arrays.
- `plugin_mastodon_state_fallback_read()` — line 1204 — Return an empty full-state fallback and delete the legacy APCu entry when APCu is available.
- `plugin_mastodon_sync_guard_kind()` — line 1214 — Normalize the cooldown guard kind.
- `plugin_mastodon_sync_guard_apcu_key()` — line 1224 — Return the APCu key used for one sync cooldown guard.
- `plugin_mastodon_sync_guard_entry_active()` — line 1234 — Check whether one cooldown guard entry is still active.
- `plugin_mastodon_sync_guard_apcu_store()` — line 1245 — Store one cooldown guard in APCu.
- `plugin_mastodon_sync_guard_file_read()` — line 1258 — Read and prune the file-backed cooldown guard.
- `plugin_mastodon_sync_guard_file_write()` — line 1285 — Write the file-backed cooldown guard.
- `plugin_mastodon_sync_guard_active()` — line 1306 — Check whether a recent scheduled content/deletion pass is still cooling down.
- `plugin_mastodon_sync_guard_mark()` — line 1331 — Mark a 5-minute cooldown guard for a scheduled sync/deletion pass.
- `plugin_mastodon_sync_guard_clear()` — line 1352 — Clear one sync cooldown guard.
- `plugin_mastodon_rate_limit_default_budgets()` — line 1367 — Return conservative per-run Mastodon API budgets.
- `plugin_mastodon_rate_limit_window_budgets()` — line 1391 — Return persistent cross-run Mastodon API window budgets.
- `plugin_mastodon_rate_limit_window_config()` — line 1418 — Return the persistent window key, budget, TTL, and block reason for one request kind.
- `plugin_mastodon_rate_limit_window_entry()` — line 1438 — Normalize one persistent rate-limit window entry and drop expired values.
- `plugin_mastodon_rate_limit_window_read()` — line 1458 — Read and prune the persistent cross-run rate-limit windows.
- `plugin_mastodon_rate_limit_window_write()` — line 1490 — Persist cross-run rate-limit windows with FlatPress file permissions.
- `plugin_mastodon_rate_limit_window_acquire()` — line 1515 — Reserve one media-upload/delete/account-status-page slot from the persistent cross-run window.
- `plugin_mastodon_rate_limit_window_clear()` — line 1564 — Clear persistent rate-limit windows for tests or recovery tooling.
- `plugin_mastodon_rate_limit_guard_start()` — line 1576 — Start the per-run Mastodon API rate-limit guard.
- `plugin_mastodon_rate_limit_guard_stop()` — line 1602 — Stop the per-run Mastodon API rate-limit guard while keeping its summary inspectable.
- `plugin_mastodon_rate_limit_guard_active()` — line 1613 — Check whether the per-run Mastodon API guard is active.
- `plugin_mastodon_rate_limit_guard_summary()` — line 1621 — Return the current per-run rate-limit guard summary.
- `plugin_mastodon_rate_limit_headers()` — line 1630 — Normalize response headers for rate-limit checks.
- `plugin_mastodon_rate_limit_request_kind()` — line 1651 — Classify Mastodon API requests as general, media upload, status deletion, or account-status paging.
- `plugin_mastodon_rate_limit_block()` — line 1676 — Mark the current run as blocked by a rate-limit budget, persistent window, or Mastodon response and log the stop reason with budget counters once per run.
- `plugin_mastodon_rate_limit_acquire()` — line 1720 — Reserve per-run and persistent window budget before a Mastodon API request proceeds.
- `plugin_mastodon_rate_limit_observe_response()` — line 1765 — Update the guard from `X-RateLimit-*` headers and `429` responses.
- `plugin_mastodon_rate_limit_blocked_reason()` — line 1789 — Return the current rate-limit block reason.
- `plugin_mastodon_rate_limit_blocked_response()` — line 1801 — Build the synthetic `429` response used for locally blocked requests.
- `plugin_mastodon_rate_limit_state_error()` — line 1818 — Return the rate-limit reason that should be written to synchronization state.
- `plugin_mastodon_file_prestat()` — line 1828 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 1848 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_ensure_state_dir()` — line 2789 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_log()` — line 2798 — Append a line to the rotated append-only plugin sync log.
- `plugin_mastodon_log_skip()` — line 2813 — Aggregate high-volume skip messages by reason until the current sync phase ends.
- `plugin_mastodon_log_flush_skip_summaries()` — line 2843 — Flush aggregated skip counters as concise summary lines.
- `plugin_mastodon_state_read()` — line 2873 — Load the persisted runtime state from disk without storing or reading a full-state APCu fallback.
- `plugin_mastodon_scheduler_state_default()` — line 2911 — Return an empty compact scheduler summary derived from the full default state.
- `plugin_mastodon_scheduler_source_signature()` — line 2932 — Return the current stat-based signature of `state.json` used to validate scheduler summaries.
- `plugin_mastodon_scheduler_state_normalize()` — line 2941 — Normalize scheduler summary fields without touching full mapping arrays.
- `plugin_mastodon_scheduler_state_from_state()` — line 2979 — Build the lightweight scheduler summary from a full runtime state.
- `plugin_mastodon_scheduler_state_write()` — line 2999 — Persist the compact scheduler summary; write failures only disable the optimization.
- `plugin_mastodon_scheduler_state_decode_fresh()` — line 3025 — Decode a scheduler-state JSON payload only when its embedded full-state signature is current, allowing a stale FlatPress cached read to be corrected before loading full `state.json`.
- `plugin_mastodon_scheduler_state_read()` — line 3044 — Load the compact scheduler summary through the APCu-capable FlatPress file I/O path, retry with an uncached scheduler-state read if a host returns stale cached content, and rebuild from full state only when missing, invalid, or truly stale.
- `plugin_mastodon_state_write()` — line 3090 — Persist the runtime state to disk and refresh the compact scheduler summary after successful writes, without caching the full state in APCu.
- `plugin_mastodon_normalize_deletions_pending_scope()` — line 3115 — Normalize the targeted deletion-follow-up scope marker.
- `plugin_mastodon_state_normalize()` — line 3128 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_comment_key()` — line 3179 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_set_entry_mapping()` — line 3194 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_comment_mapping()` — line 3233 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_remove_entry_mapping()` — line 3268 — Remove the mapping between a local entry and a remote status.
- `plugin_mastodon_state_remove_comment_mapping()` — line 3290 — Remove the mapping between a local comment and a remote status.
- `plugin_mastodon_state_set_dirty_entry()` — line 3315 — Add an older changed entry to the persistent dirty queue.
- `plugin_mastodon_state_remove_dirty_entry()` — line 3335 — Remove an entry from the dirty queue after successful synchronization or cleanup.
- `plugin_mastodon_state_has_dirty_entry()` — line 3348 — Check whether an entry is queued for synchronization outside the scheduled window.
- `plugin_mastodon_state_set_dirty_comment()` — line 3361 — Add an older changed comment to the persistent dirty queue.
- `plugin_mastodon_state_remove_dirty_comment()` — line 3384 — Remove a comment from the dirty queue after successful synchronization or cleanup.
- `plugin_mastodon_state_has_dirty_comment()` — line 3398 — Check whether a comment is queued for synchronization outside the scheduled window.
- `plugin_mastodon_local_write_guard_enter()` — line 3407 — Increase the depth guard while the plugin mirrors remote Mastodon data into FlatPress.
- `plugin_mastodon_local_write_guard_leave()` — line 3418 — Decrease the local-write guard depth after a guarded FlatPress write/delete.
- `plugin_mastodon_local_write_guard_active()` — line 3430 — Check whether Mastodon-owned local writes should suppress dirty hook handling.
- `plugin_mastodon_dirty_tracking_options()` — line 3438 — Return normalized options for post-success dirty hooks, or an empty array when guard/configuration prevents state writes.
- `plugin_mastodon_on_entry_saved()` — line 3457 — Handle Core `entry_saved` and queue changed mapped local entries when they require a later targeted export.
- `plugin_mastodon_on_entry_deleted()` — line 3506 — Handle Core `entry_deleted`, clear dirty-entry state, and mark mapped local deletions for the deletion follow-up pass.
- `plugin_mastodon_on_comment_saved()` — line 3533 — Handle Core `comment_saved` and queue changed mapped local comments without exporting unrelated old comments.
- `plugin_mastodon_on_comment_deleted()` — line 3591 — Handle Core `comment_deleted`, clear dirty-comment state, and mark mapped local comment deletions for the deletion follow-up pass.
- `plugin_mastodon_state_get_entry_meta()` — line 3616 — Return mapping metadata for a local entry.
- `plugin_mastodon_state_entry_remote_media()` — line 3659 — Return normalized stored remote media descriptors for one entry mapping.
- `plugin_mastodon_state_entry_media_attachment_signature()` — line 3689 — Return the stored local attachment signature for one entry mapping.
- `plugin_mastodon_state_entry_media_description_signature()` — line 3699 — Return the stored local media-description signature for one entry mapping.
- `plugin_mastodon_state_set_entry_media_meta()` — line 3630 — Persist cached remote media IDs plus local attachment/description signatures for a synchronized entry.
- `plugin_mastodon_state_get_comment_meta()` — line 3711 — Return mapping metadata for a local comment.
- `plugin_mastodon_state_set_comment_tombstone()` — line 3725 — Store a tombstone that blocks stale re-imports of one deleted remote comment.
- `plugin_mastodon_state_has_comment_tombstone()` — line 3745 — Check whether one remote Mastodon comment status was tombstoned locally.
- `plugin_mastodon_protect_locally_deleted_exported_comments()` — line 3756 — Tombstone locally deleted exported FlatPress comment mappings before the next content sync can stale-reimport them from Mastodon thread context.
- `plugin_mastodon_reattach_local_comment_to_entry_status()` — line 3799 — Remove a local imported reply parent link and reattach the surviving reply to the synchronized entry status after its remote parent reply disappeared.
- `plugin_mastodon_state_remove_pending_comment_remote_recheck()` — line 3859 — Remove one pending descendant recheck marker.
- `plugin_mastodon_state_get_pending_comment_remote_recheck()` — line 3873 — Return one pending descendant recheck marker.
- `plugin_mastodon_state_set_pending_comment_remote_recheck()` — line 3888 — Mark one local comment for follow-up verification after an ancestor disappeared remotely.
- `plugin_mastodon_state_set_deletions_pending()` — line 3914 — Persist whether another deletion follow-up request is pending, which scope it should run, and when it may start.
- `plugin_mastodon_deletion_sync_due()` — line 3928 — Check whether the pending deletion synchronization may start after its persisted not-before timestamp.
- `plugin_mastodon_state_has_comment_recheck_scope()` — line 3956 — Check whether the next deletion follow-up request should run only the targeted descendant recheck scope.
- `plugin_mastodon_build_comment_remote_child_index()` — line 3965 — Build a direct-child index for mapped remote reply trees.
- `plugin_mastodon_queue_comment_descendant_remote_rechecks()` — line 3993 — Queue only the direct mapped local children of one deleted remote comment for additional verification passes.
- `plugin_mastodon_process_pending_comment_remote_rechecks()` — line 4032 — Process pending descendant rechecks breadth-first so deeper reply chains can converge within the same targeted follow-up request.

## D. Date, timestamp, visibility, and threading helpers

- `plugin_mastodon_timestamp_to_flatpress_time()` — line 837 — Convert a real Unix timestamp into FlatPress's offset-adjusted timestamp model.
- `plugin_mastodon_timestamp_date_key()` — line 2473 — Convert a FlatPress-adjusted timestamp into a stable date key.
- `plugin_mastodon_local_item_date_key()` — line 2487 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_remote_status_date_key()` — line 2510 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_datetime_date_key()` — line 2536 — Normalize a stored date/datetime string to the sync-start date-key format.
- `plugin_mastodon_date_matches_sync_start()` — line 2560 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_scheduled_window_start_date()` — line 2578 — Return the FlatPress-local date key that starts the automatic scheduled sync window.
- `plugin_mastodon_date_matches_content_window()` — line 2595 — Combine the durable sync-start lower bound with the scheduled-run recent-content window.
- `plugin_mastodon_local_item_matches_sync_start()` — line 2617 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_local_item_matches_content_window()` — line 2629 — Determine whether a local FlatPress item is inside the active content synchronization window.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 2639 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_mapping_effective_date_key()` — line 2709 — Resolve the best date key available for a synchronized mapping.
- `plugin_mastodon_mapping_matches_deletion_lookup_window()` — line 2750 — Decide whether an existing local mapping should receive a remote deletion lookup in the current scheduled or forced delete pass.
- `plugin_mastodon_mapping_keys_after_cursor()` — line 2771 — Return sorted mapping keys after a saved deletion cursor so large delete passes can continue across runs.
- `plugin_mastodon_remote_status_matches_content_window()` — line 2650 — Determine whether a remote Mastodon status is inside the active content synchronization window.
- `plugin_mastodon_mapping_matches_sync_start()` — line 2661 — Determine whether a stored synchronization mapping still belongs to the active sync-start window.
- `plugin_mastodon_parse_iso_datetime()` — line 4142 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 4160 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_remote_status_timestamp()` — line 4182 — Resolve the best FlatPress-adjusted timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 4201 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 4214 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_comment_parent_fields()` — line 4226 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_normalize_boolean_option()` — line 2367 — Normalize a boolean-like option value to the stored string representation.
- `plugin_mastodon_normalize_comment_parent_id()` — line 4235 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 4252 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_resolve_comment_reply_target()` — line 4274 — Resolve the remote reply target for a local comment export.
- `plugin_mastodon_list_local_comment_ids()` — line 4327 — Scan the FlatPress comment directory directly so local reply export is not blocked by stale comment-list caches.

## E. Text, URLs, language strings, tags, emojis, and BBCode/HTML conversion

- `plugin_mastodon_guess_subject()` — line 4364 — Guess a subject line from imported plain text.
- `plugin_mastodon_html_entity_decode()` — line 4406 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_blog_base_url()` — line 4415 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_extract_url_token()` — line 4451 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_absolute_url()` — line 4468 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_lang_string()` — line 4510 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_normalize_tag_list()` — line 4736 — Normalize a list of tag labels.
- `plugin_mastodon_extend_time_limit()` — line 7027 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_extract_flatpress_tags()` — line 4767 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 4792 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 4809 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_remote_status_tags()` — line 4830 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 4857 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 4920 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 4933 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 4946 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 5000 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_prepare_emoticons_for_mastodon()` — line 5015 — Convert active FlatPress emoticon shortcodes to standard Unicode emoji before Mastodon export.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 5028 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_is_public_host()` — line 5050 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_public_url_for_mastodon()` — line 5073 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_plain_text_from_bbcode()` — line 5092 — Convert FlatPress BBCode into plain text for Mastodon export, removing complete AudioVideo player tags including optional description endtag content.
- `plugin_mastodon_subject_line_is_noise()` — line 5136 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_domains_match()` — line 5164 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_cleanup_imported_text()` — line 5178 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_dom_children_to_flatpress()` — line 5232 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 5250 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_public_entry_url()` — line 5359 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_comments_url()` — line 5386 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_comment_url()` — line 5414 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 5429 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_flatpress_to_mastodon()` — line 5531 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_limit_text()` — line 5648 — Limit text to a maximum number of characters.

## F. Local content access, media processing, hashing, and export ordering

- `plugin_mastodon_entry_hash()` — line 5670 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_comment_hash()` — line 5682 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_remote_status_author_label()` — line 8920 — Build a readable author label for quoted Mastodon replies.
- `plugin_mastodon_strip_leading_quote_block()` — line 8953 — Remove one leading BBCode quote block so imported reply quotes do not compound indefinitely.
- `plugin_mastodon_imported_reply_quote_payload()` — line 8986 — Resolve the author and body that should be quoted for an imported Mastodon reply.
- `plugin_mastodon_build_imported_reply_quote()` — line 9029 — Build the optional BBCode quote block for an imported Mastodon reply.
- `plugin_mastodon_safe_path_component()` — line 5700 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_safe_filename()` — line 5715 — Sanitize a file name for local storage.
- `plugin_mastodon_normalize_media_relative_path()` — line 5728 — Normalize a FlatPress media path and reject absolute, URL, or traversal paths.
- `plugin_mastodon_media_relative_to_absolute()` — line 5751 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_media_prepare_directory()` — line 5764 — Ensure that a media directory exists.
- `plugin_mastodon_media_delete_tree()` — line 5780 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_copy_tree()` — line 5807 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_bbcode_attr_escape()` — line 5845 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_bbcode_text_escape()` — line 5857 — Escape plain text embedded between BBCode tags, used for imported AudioVideo media descriptions.
- `plugin_mastodon_media_guess_mime_type()` — line 5879 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_type_from_mime()` — line 5946 — Classify a MIME type or file extension as image, video, or audio for Mastodon media handling.
- `plugin_mastodon_extension_from_mime_type()` — line 5980 — Resolve a safe file extension from a MIME type with a fallback.
- `plugin_mastodon_media_parse_tag_attributes()` — line 6101 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_media_description_from_bbcode_content()` — line 6132 — Normalize optional AudioVideo BBCode content into a Mastodon media description.
- `plugin_mastodon_instance_supported_media_mime_types()` — line 6027 — Return the MIME types advertised by the configured Mastodon instance.
- `plugin_mastodon_instance_media_size_limit()` — line 6047 — Return the configured byte-size limit for an image, video/GIFV, or audio upload.
- `plugin_mastodon_validate_local_media_item()` — line 6074 — Validate one local media file against available instance MIME and byte-size limits before upload.
- `plugin_mastodon_media_extract_default_path()` — line 6158 — Extract the default path parameter from FlatPress media BBCode such as `[img=...]`, `[gallery=...]`, `[audioplayer="..."]`, and `[videoplayer="..."]`.
- `plugin_mastodon_add_local_media_item()` — line 6187 — Add one normalized local media item while deduplicating and enforcing an expected media family.
- `plugin_mastodon_collect_local_entry_media()` — line 6236 — Collect local images, galleries, audio, video, optional AudioVideo endtag descriptions, and video poster thumbnails referenced by an entry.
- `plugin_mastodon_select_status_media_items()` — line 6382 — Select one Mastodon-compatible media family for a status: images first, otherwise one audio, otherwise one video with poster kept as thumbnail only.
- `plugin_mastodon_prepare_entry_media_items()` — line 6443 — Validate collected local media, apply media-family selection, and return reusable path/description tuples.
- `plugin_mastodon_entry_media_attachment_signature_from_items()` — line 6471 — Hash only the attachment identity of the selected media items.
- `plugin_mastodon_entry_media_description_signature_from_items()` — line 6494 — Hash only the alt-text portion of the selected media items.
- `plugin_mastodon_entry_media_signature()` — line 6513 — Build a combined attachment+description signature for media references contained in entry content.
- `plugin_mastodon_remote_media_attachment_type()` — line 6530 — Normalize a Mastodon attachment type, including extension-based fallbacks for older or incomplete payloads.
- `plugin_mastodon_remote_status_media_attachments()` — line 6557 — Extract supported image, audio, video, and GIFV attachments from a remote Mastodon status.
- `plugin_mastodon_remote_status_image_attachments()` — line 6587 — Extract image attachments from a remote Mastodon status.
- `plugin_mastodon_remote_media_source_url()` — line 6596 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_media_source_urls()` — line 6614 — Resolve direct-download fallback candidates for a remote attachment; audio/video/GIFV avoid `preview_url` as a file-source fallback, while images may use it.
- `plugin_mastodon_remote_media_description()` — line 6636 — Resolve the best description for a remote attachment.
- `plugin_mastodon_remote_media_focus()` — line 6650 — Normalize a Mastodon media focus string when present.
- `plugin_mastodon_remote_media_descriptors_from_status()` — line 6667 — Extract reusable media descriptors (`id`, `description`, `focus`) from a Mastodon status payload.
- `plugin_mastodon_remote_media_descriptors_from_media_ids()` — line 6698 — Build fallback reusable media descriptors from already-known IDs and local media items.
- `plugin_mastodon_media_download()` — line 6765 — Download a remote media asset with an extended media-transfer timeout.
- `plugin_mastodon_remote_download_basename()` — line 6779 — Build a safe local basename for a downloaded remote image, audio, video, GIFV, or poster.
- `plugin_mastodon_store_remote_media_url()` — line 6809 — Download and persist one remote media URL.
- `plugin_mastodon_build_imported_media_bbcode()` — line 6831 — Build FlatPress BBCode for imported remote media attachments: images become `[img]`/`[gallery]`, audio becomes `[audioplayer]`, and video/GIFV becomes `[videoplayer]` with imported optional description endtag text and an imported poster when available; alternate direct media URLs are retried before an attachment is skipped.
- `plugin_mastodon_collect_entry_files()` — line 7819 — Collect entry files recursively from the FlatPress content tree.
- `plugin_mastodon_local_item_timestamp()` — line 7846 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_compare_local_entries_for_export()` — line 7875 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_test_note_local_entry_parse()` — line 7894 — Simulation-only no-op counter hook used to prove targeted scheduled scans avoid parsing all old local entries.
- `plugin_mastodon_dirty_entry_id_lookup()` — line 7906 — Collect local entry IDs that must be parsed because an entry or one of its comments is present in the dirty queues.
- `plugin_mastodon_should_parse_local_entry_for_sync()` — line 7937 — Decide whether one entry belongs to the active scheduled window, the dirty target set, or an explicit full scan.
- `plugin_mastodon_list_local_entries_for_sync()` — line 7962 — Build the local export candidate list for scheduled/non-full runs from active-window entries plus dirty targets while preserving full repair scans.
- `plugin_mastodon_list_local_entries()` — line 8003 — List local FlatPress entry identifiers.

## G. HTTP transport, PHP timeout budgeting, instance capability lookup, status-length budgeting, OAuth, Mastodon API calls, and media upload

- `plugin_mastodon_extend_time_limit()` — line 7027 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_instance_document()` — line 7058 — Load and cache the compact Mastodon instance document, preferring the saved FlatPress snapshot before APCu and live network fetches.
- `plugin_mastodon_instance_version()` — line 7102 — Extract the human-readable Mastodon server version from the cached instance document.
- `plugin_mastodon_instance_supports_status_media_attributes()` — line 7120 — Decide whether `PUT /api/v1/statuses/:id` may safely use `media_attributes` for in-place alt-text edits.
- `plugin_mastodon_instance_supports_status_delete_media()` — line 7144 — Use cached or stored instance-version information to decide whether `DELETE /api/v1/statuses/:id?delete_media=1` is documented as supported, without spending an extra network request during deletion synchronization.
- `plugin_mastodon_instance_configuration()` — line 7161 — Return the normalized `configuration` subtree from the cached Mastodon instance document.
- `plugin_mastodon_instance_media_limit()` — line 7171 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_media_description_limit()` — line 7184 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_url_reserved_length()` — line 7197 — Return the reserved Mastodon character budget used for each URL.
- `plugin_mastodon_instance_registration_summary()` — line 10130 — Summarize the cached registration policy advertised by the instance for the admin diagnostics table.
- `plugin_mastodon_admin_instance_info_rows()` — line 10156 — Build the localized admin-table rows from the cached instance-information snapshot without forcing another live request.
- `plugin_mastodon_status_text_length()` — line 7211 — Calculate the Mastodon-visible status length with instance URL budgeting.
- `plugin_mastodon_limit_status_text()` — line 7243 — Truncate status text using Mastodon URL-budget rules.
- `plugin_mastodon_http_request_multipart()` — line 7322 — Perform a multipart HTTP request.
- `plugin_mastodon_fetch_media_attachment()` — line 7455 — Fetch a single Mastodon media attachment by ID.
- `plugin_mastodon_media_processing_attempts()` — line 7520 — Calculate media-type- and size-aware polling attempts for asynchronous Mastodon media processing.
- `plugin_mastodon_media_transfer_timeout()` — line 7544 — Calculate longer upload transfer timeouts for audio/video/GIFV while keeping image uploads lighter.
- `plugin_mastodon_wait_for_media_attachment()` — line 7564 — Poll an asynchronously processed Mastodon media attachment until it is ready or times out, including pending audio/video responses without `preview_url`.
- `plugin_mastodon_upload_media_items()` — line 7613 — Upload local media items to Mastodon and collect the created media IDs; AudioVideo posters are sent as Mastodon `thumbnail` multipart fields for video uploads.
- `plugin_mastodon_parse_http_response_headers()` — line 8041 — Parse raw HTTP response headers.
- `plugin_mastodon_stream_context_request()` — line 8071 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_status_media_attributes()` — line 6732 — Build the `media_attributes` array used for in-place status edits of already attached media.
- `plugin_mastodon_prepare_entry_media_sync_plan()` — line 7730 — Decide whether an entry should upload fresh media, reuse stored IDs, or reuse IDs plus `media_attributes`.
- `plugin_mastodon_array_is_list()` — line 8111 — Detect whether a PHP array is a zero-based list that should use `[]` form-field notation.
- `plugin_mastodon_array_contains_only_form_scalars()` — line 8131 — Detect whether a list can be serialized as repeated scalar `[]` fields.
- `plugin_mastodon_http_build_query()` — line 8149 — Build an application/x-www-form-urlencoded query string, emitting Rack-compatible Mastodon array fields such as `media_ids[]` and nested `media_attributes[][description]`.
- `plugin_mastodon_http_request()` — line 8205 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_mastodon_api()` — line 8323 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_json()` — line 8372 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_response_error_message()` — line 8387 — Extract the most useful error message from an API response.
- `plugin_mastodon_oauth_legacy_scopes()` — line 563 — Return the legacy OAuth scope string used by older registrations.
- `plugin_mastodon_oauth_profile_scopes()` — line 571 — Return the stricter scope string that uses `profile` for `verify_credentials`.
- `plugin_mastodon_oauth_server_metadata()` — line 580 — Discover OAuth server metadata from `/.well-known/oauth-authorization-server`.
- `plugin_mastodon_oauth_supported_scopes()` — line 599 — Extract the discoverable scope list from OAuth server metadata.
- `plugin_mastodon_oauth_scope_supported()` — line 636 — Check whether the configured Mastodon instance supports a given OAuth scope.
- `plugin_mastodon_oauth_preferred_scopes()` — line 657 — Prefer `profile` on current instances and fall back to `read:accounts` on older ones.
- `plugin_mastodon_register_app()` — line 8416 — Register the FlatPress application on the configured Mastodon instance with the preferred discoverable scope set.
- `plugin_mastodon_build_authorize_url()` — line 8439 — Build the OAuth authorization URL using the scopes that the registered app may safely request.
- `plugin_mastodon_exchange_code_for_token()` — line 8459 — Exchange an OAuth authorization code for an access token using the same negotiated scope string.
- `plugin_mastodon_verify_credentials()` — line 8489 — Verify the currently configured access token.
- `plugin_mastodon_instance_character_limit()` — line 8506 — Return the status character limit of the configured instance.
- `plugin_mastodon_fetch_account_statuses()` — line 8521 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_status_context()` — line 8568 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_fetch_status()` — line 8579 — Fetch a single Mastodon status.
- `plugin_mastodon_delete_status()` — line 8590 — Delete a Mastodon status; cached Mastodon versions before 4.4.0 omit `delete_media=1`, while unknown servers first try the media-cleanup variant and retry once without the query parameter on legacy-style rejection responses.
- `plugin_mastodon_delete_status_should_retry_without_delete_media()` — line 8618 — Decide whether a failed status DELETE should be retried without `delete_media=1`, while avoiding retries for missing statuses or active rate-limit stops.
- `plugin_mastodon_status_missing_response()` — line 8638 — Check whether an API response means that the referenced Mastodon status no longer exists.
- `plugin_mastodon_create_status()` — line 8651 — Create a Mastodon status.
- `plugin_mastodon_update_status()` — line 8678 — Update an existing Mastodon status.

## H. Import/export builders and synchronization orchestration

- `plugin_mastodon_build_entry_status_text()` — line 8704 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_comment_status_text()` — line 8772 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_import_remote_entry()` — line 8810 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_import_remote_comment()` — line 9071 — Import a remote Mastodon reply into FlatPress as a comment while respecting comment tombstones, including early tombstones for locally deleted exported comments.
- `plugin_mastodon_import_remote_context_descendants()` — line 9160 — Import remote Mastodon replies from a fetched thread context while blocking tombstoned parent/child replies.
- `plugin_mastodon_old_thread_context_rotation_limit()` — line 9261 — Return the maximum number of known synchronized threads checked for replies per content sync run.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 9277 — Collect known synchronized entry threads for optional rotating reply-context refreshes while respecting the synchronization start-date window.
- `plugin_mastodon_sync_remote_to_local()` — line 9350 — Synchronize remote Mastodon content into FlatPress with the durable start-date lower bound, scheduled-run window, and optional known-thread reply rotation.
- `plugin_mastodon_sync_local_to_remote()` — line 9417 — Synchronize local FlatPress content to Mastodon, including remote-sourced entry comment export, scheduled-run window filtering, dirty-queue processing, media-plan reuse of stored `media_ids`, and version-aware in-place alt-text updates.
- `plugin_mastodon_run_deletion_sync()` — line 9648 — Reconcile mapped deletions between FlatPress and Mastodon in a separate follow-up request, limiting scheduled remote existence lookups to the active window while cursoring large mapping sets.

## Recommended reading order for new developers

A practical way to understand the plugin is:

1. `plugin_mastodon_run_sync()`
2. `plugin_mastodon_run_deletion_sync()`
3. `plugin_mastodon_sync_local_to_remote()`
4. `plugin_mastodon_sync_remote_to_local()`
5. `plugin_mastodon_build_entry_status_text()`
6. `plugin_mastodon_build_comment_status_text()`
7. `plugin_mastodon_import_remote_entry()`
8. `plugin_mastodon_import_remote_comment()`
9. `plugin_mastodon_state_read()` / `plugin_mastodon_state_write()`
10. `plugin_mastodon_scheduler_state_read()` / `plugin_mastodon_scheduler_state_write()`
11. `plugin_mastodon_get_options()`
12. `plugin_mastodon_enabled_plugin_state()`
13. `plugin_mastodon_extend_time_limit()`
14. `plugin_mastodon_instance_configuration()`
15. `plugin_mastodon_instance_url_reserved_length()` / `plugin_mastodon_status_text_length()` / `plugin_mastodon_limit_status_text()`
16. `plugin_mastodon_http_request()`
17. `plugin_mastodon_collect_local_entry_media()`
18. `plugin_mastodon_upload_media_items()` / `plugin_mastodon_wait_for_media_attachment()`

## Current feature areas reflected in the function set

The current plugin version includes dedicated function groups for:

- Mastodon identity metadata in the HTML head
- request-local runtime cache, optional APCu-backed small-value/cache guards, and core APCu file hotcache usage for compact scheduler summaries
- reuse of the early FlatPress configuration (`EARLY_FP_CONFIG`) with APCu/runtime fallbacks
- centralized FlatPress plugin-state detection for companion-plugin diagnostics
- FlatPress-native file I/O wrappers for plugin state, APCu-capable scheduler summary reads, rotated append-only logs, and downloaded media
- request-time scheduler due checks that avoid loading the full `state.json` mappings when no sync is due and can benefit from the core file/APCu hotcache for `scheduler-state.json`
- encrypted storage of sensitive configuration values
- sync start date filtering plus a configurable 7/14/30-day automatic window for scheduled runs
- Core-hook-fed persistent dirty queues that still synchronize changed older mapped entries and comments outside the scheduled window
- optional rotating reply checks for known synchronized Mastodon threads
- status language export derived from the FlatPress locale configuration
- optional remote overwrite of existing local content
- optional import of already synchronized comments as entries
- companion-plugin status reporting for BBCode, PhotoSwipe, AudioVideo, Tag, and Emoticons
- tag synchronization through the FlatPress Tag plugin BBCode
- emoji conversion between FlatPress-style shortcodes and Mastodon-style Unicode
- bidirectional media synchronization for entry images, galleries, audio, and video
- AudioVideo `[audioplayer]` and `[videoplayer]` export from FlatPress entries to Mastodon media uploads
- Mastodon `audio`, `video`, and `gifv` attachment import into `fp-content/attachs/mastodon/...` with FlatPress AudioVideo BBCode
- initial alt-text upload through `/api/v2/media` plus cached remote media descriptors for later edits
- reuse of stored Mastodon `media_ids` for text-only entry updates without redundant media re-upload
- Mastodon-version-aware in-place alt-text updates through status `media_attributes` on Mastodon 4.1+
- Mastodon instance-aware URL length budgeting for exported statuses
- bounded PHP execution-budget refresh for long-running shared FlatPress requests
- asynchronous media-upload readiness polling for `/api/v2/media`
- deferred deletion synchronization in a follow-up web request
- known-thread reply refresh during remote import
- explicit export ordering so older local entries are posted before newer ones

## Maintenance notes

When changing the plugin, these clusters usually need to stay in sync:

- **configuration + early FlatPress config reuse + centralized plugin-state lookup + admin UI + normalization helpers**
- **state mappings + import/export code + deferred deletion reconciliation**
- **text conversion + media conversion + hashtag handling + companion-plugin expectations**
- **date filters + scheduling + known-thread refresh**
- **FlatPress file I/O wrappers + state/log/media persistence**
- **HTTP transport + PHP timeout-budget refresh + OAuth + instance capability caching + URL-budget handling + async media readiness polling**

A change in one of these areas often requires corresponding updates in the simulation script.

## Alphabetical appendix
- `main()` — line 10281 — Keep the admin panel lifecycle compatible with FlatPress without extra processing.
- `onsubmit()` — line 10285 — Process configuration saves, OAuth actions, including app registration and authorization-code exchange, and the manual synchronization trigger.
- `plugin_mastodon_absolute_url()` — line 4468 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_admin_assign()` — line 10228 — Assign plugin data to Smarty for the admin panel.
- `plugin_mastodon_apcu_cache_key()` — line 1125 — Build the namespaced APCu key used by this plugin.
- `plugin_mastodon_apcu_delete()` — line 1167 — Delete a value from APCu using FlatPress `apcu_delete_key()` when available.
- `plugin_mastodon_apcu_enabled()` — line 1116 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_fetch()` — line 1139 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 1154 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_state_fallback_key()` — line 1186 — Return the legacy APCu key that used to hold a full last-known-good state fallback.
- `plugin_mastodon_state_fallback_store()` — line 1195 — Remove the legacy full-state APCu fallback instead of storing large mapping arrays.
- `plugin_mastodon_state_fallback_read()` — line 1204 — Return an empty full-state fallback and delete the legacy APCu entry when APCu is available.
- `plugin_mastodon_sync_guard_kind()` — line 1214 — Normalize the cooldown guard kind.
- `plugin_mastodon_sync_guard_apcu_key()` — line 1224 — Return the APCu key used for one sync cooldown guard.
- `plugin_mastodon_sync_guard_entry_active()` — line 1234 — Check whether one cooldown guard entry is still active.
- `plugin_mastodon_sync_guard_apcu_store()` — line 1245 — Store one cooldown guard in APCu.
- `plugin_mastodon_sync_guard_file_read()` — line 1258 — Read and prune the file-backed cooldown guard.
- `plugin_mastodon_sync_guard_file_write()` — line 1285 — Write the file-backed cooldown guard.
- `plugin_mastodon_sync_guard_active()` — line 1306 — Check whether a recent scheduled content/deletion pass is still cooling down.
- `plugin_mastodon_sync_guard_mark()` — line 1331 — Mark a 5-minute cooldown guard for a scheduled sync/deletion pass.
- `plugin_mastodon_sync_guard_clear()` — line 1352 — Clear one sync cooldown guard.
- `plugin_mastodon_rate_limit_default_budgets()` — line 1367 — Return conservative per-run Mastodon API budgets.
- `plugin_mastodon_rate_limit_window_budgets()` — line 1391 — Return persistent cross-run Mastodon API window budgets.
- `plugin_mastodon_rate_limit_window_config()` — line 1418 — Return the persistent window key, budget, TTL, and block reason for one request kind.
- `plugin_mastodon_rate_limit_window_entry()` — line 1438 — Normalize one persistent rate-limit window entry and drop expired values.
- `plugin_mastodon_rate_limit_window_read()` — line 1458 — Read and prune the persistent cross-run rate-limit windows.
- `plugin_mastodon_rate_limit_window_write()` — line 1490 — Persist cross-run rate-limit windows with FlatPress file permissions.
- `plugin_mastodon_rate_limit_window_acquire()` — line 1515 — Reserve one media-upload/delete/account-status-page slot from the persistent cross-run window.
- `plugin_mastodon_rate_limit_window_clear()` — line 1564 — Clear persistent rate-limit windows for tests or recovery tooling.
- `plugin_mastodon_rate_limit_guard_start()` — line 1576 — Start the per-run Mastodon API rate-limit guard.
- `plugin_mastodon_rate_limit_guard_stop()` — line 1602 — Stop the per-run Mastodon API rate-limit guard while keeping its summary inspectable.
- `plugin_mastodon_rate_limit_guard_active()` — line 1613 — Check whether the per-run Mastodon API guard is active.
- `plugin_mastodon_rate_limit_guard_summary()` — line 1621 — Return the current per-run rate-limit guard summary.
- `plugin_mastodon_rate_limit_headers()` — line 1630 — Normalize response headers for rate-limit checks.
- `plugin_mastodon_rate_limit_request_kind()` — line 1651 — Classify Mastodon API requests as general, media upload, status deletion, or account-status paging.
- `plugin_mastodon_rate_limit_block()` — line 1676 — Mark the current run as blocked by a rate-limit budget, persistent window, or Mastodon response and log the stop reason with budget counters once per run.
- `plugin_mastodon_rate_limit_acquire()` — line 1720 — Reserve per-run and persistent window budget before a Mastodon API request proceeds.
- `plugin_mastodon_rate_limit_observe_response()` — line 1765 — Update the guard from `X-RateLimit-*` headers and `429` responses.
- `plugin_mastodon_rate_limit_blocked_reason()` — line 1789 — Return the current rate-limit block reason.
- `plugin_mastodon_rate_limit_blocked_response()` — line 1801 — Build the synthetic `429` response used for locally blocked requests.
- `plugin_mastodon_rate_limit_state_error()` — line 1818 — Return the rate-limit reason that should be written to synchronization state.
- `plugin_mastodon_bbcode_attr_escape()` — line 5845 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_bbcode_text_escape()` — line 5857 — Escape plain text embedded between BBCode tags.
- `plugin_mastodon_bbcode_plugin_active()` — line 4622 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_blog_base_url()` — line 4415 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_build_authorize_url()` — line 8439 — Build the OAuth authorization URL using the scopes that the registered app may safely request.
- `plugin_mastodon_build_comment_status_text()` — line 8772 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_build_entry_status_text()` — line 8704 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 4920 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_build_imported_media_bbcode()` — line 6831 — Build FlatPress BBCode for imported remote media attachments, including AudioVideo optional description endtag text.
- `plugin_mastodon_cleanup_imported_text()` — line 5178 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_cleanup_uploaded_media()` — line 7484 — Best-effort cleanup for uploaded Mastodon media that never reached a final status request.
- `plugin_mastodon_collect_entry_files()` — line 7819 — Collect entry files recursively from the FlatPress content tree.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 9277 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed while respecting the synchronization start-date window.
- `plugin_mastodon_collect_local_entry_media()` — line 6236 — Collect local images, galleries, AudioVideo media, optional AudioVideo endtag descriptions, and video poster thumbnails referenced by an entry.
- `plugin_mastodon_select_status_media_items()` — line 6382 — Apply the Mastodon status media-family rule before media signatures, reuse checks, uploads, and final status requests.
- `plugin_mastodon_comment_hash()` — line 5682 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_comment_parent_fields()` — line 4226 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_companion_plugins_status()` — line 4688 — Return the status of companion FlatPress plugins used for the full Mastodon feature set.
- `plugin_mastodon_compare_local_entries_for_export()` — line 7875 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_configured_status_language()` — line 2279 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_create_status()` — line 8651 — Create a Mastodon status.
- `plugin_mastodon_date_matches_content_window()` — line 2595 — Combine the durable sync-start lower bound with the scheduled-run recent-content window.
- `plugin_mastodon_date_matches_sync_start()` — line 2560 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_datetime_date_key()` — line 2536 — Normalize a stored date/datetime string to the sync-start date-key format.
- `plugin_mastodon_default_content_stats()` — line 503 — Return the default counters for the last content synchronization.
- `plugin_mastodon_default_deletion_stats()` — line 520 — Return the default counters for the last deletion synchronization.
- `plugin_mastodon_default_options()` — line 120 — Return the default plugin option values.
- `plugin_mastodon_default_state()` — line 533 — Return the default runtime state structure, including the targeted deletion-follow-up scope marker and deletion progress cursors.
- `plugin_mastodon_delete_media_attachment()` — line 7465 — Delete an uploaded Mastodon media attachment before it is attached to a final status.
- `plugin_mastodon_delete_status()` — line 8590 — Delete a Mastodon status; cached Mastodon versions before 4.4.0 omit `delete_media=1`, while unknown servers first try the media-cleanup variant and retry once without the query parameter on legacy-style rejection responses.
- `plugin_mastodon_delete_status_should_retry_without_delete_media()` — line 8618 — Decide whether a failed status DELETE should be retried without `delete_media=1`, while avoiding retries for missing statuses or active rate-limit stops.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 4252 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_dom_children_to_flatpress()` — line 5232 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 5250 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_domains_match()` — line 5164 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 4933 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 4946 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_emoticons_plugin_active()` — line 4673 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_enabled_plugin_state()` — line 4540 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_ensure_state_dir()` — line 2789 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_entry_hash()` — line 5670 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_entry_media_signature()` — line 6513 — Build a signature for media references contained in entry content.
- `plugin_mastodon_exchange_code_for_token()` — line 8459 — Exchange an OAuth authorization code for an access token using the same negotiated scope string.
- `plugin_mastodon_extension_from_mime_type()` — line 5980 — Resolve a safe file extension from a MIME type.
- `plugin_mastodon_extend_time_limit()` — line 7027 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_extract_flatpress_tags()` — line 4767 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_extract_url_token()` — line 4451 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_fediverse_creator_value()` — line 2199 — Build the fediverse creator meta value.
- `plugin_mastodon_fetch_account_statuses()` — line 8521 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_media_attachment()` — line 7455 — Fetch a single Mastodon media attachment by ID.
- `plugin_mastodon_fetch_status()` — line 8579 — Fetch a single Mastodon status.
- `plugin_mastodon_fetch_status_context()` — line 8568 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_file_prestat()` — line 1828 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 1848 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_flatpress_to_mastodon()` — line 5531 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_fp_config()` — line 751 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 787 — Read a nested FlatPress configuration value.
- `plugin_mastodon_fp_timeoffset_seconds()` — line 803 — Return the configured FlatPress time offset in seconds for exact local admin-time conversion.
- `plugin_mastodon_fp_timeoffset_label()` — line 823 — Format the configured FlatPress time offset as a UTC label for admin users.
- `plugin_mastodon_sync_time_to_minutes()` — line 853 — Convert a normalized `HH:MM` sync-time value into minutes after midnight.
- `plugin_mastodon_minutes_to_sync_time()` — line 864 — Convert minutes after midnight back into a normalized `HH:MM` sync-time value.
- `plugin_mastodon_sync_time_utc_to_local()` — line 880 — Convert the stored UTC synchronization time to the FlatPress-local admin time.
- `plugin_mastodon_sync_time_local_to_utc()` — line 891 — Convert the FlatPress-local admin synchronization time back to stored UTC.
- `plugin_mastodon_format_admin_datetime()` — line 902 — Format stored UTC timestamps for the admin panel using FlatPress `timeoffset`, date format, and time format.
- `plugin_mastodon_get_options()` — line 1859 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_guess_subject()` — line 4364 — Guess a subject line from imported plain text.
- `plugin_mastodon_head()` — line 2217 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_html_entity_decode()` — line 4406 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_http_build_query()` — line 8149 — Build an application/x-www-form-urlencoded query string.
- `plugin_mastodon_http_request()` — line 8205 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_http_request_multipart()` — line 7322 — Perform a multipart HTTP request.
- `plugin_mastodon_import_remote_comment()` — line 9071 — Import a remote Mastodon reply into FlatPress as a comment while respecting comment tombstones, including early tombstones for locally deleted exported comments.
- `plugin_mastodon_import_remote_context_descendants()` — line 9160 — Import remote Mastodon replies from a fetched thread context while blocking tombstoned parent/child replies.
- `plugin_mastodon_import_remote_entry()` — line 8810 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_instance_authority()` — line 2153 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_instance_character_limit()` — line 8506 — Return the status character limit of the configured instance.
- `plugin_mastodon_instance_configuration()` — line 7161 — Load and cache the Mastodon instance configuration document.
- `plugin_mastodon_instance_media_description_limit()` — line 7184 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_media_limit()` — line 7171 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_url_reserved_length()` — line 7197 — Return the reserved Mastodon character budget used for each URL.
- `plugin_mastodon_io_append_file()` — line 1013 — Append to a file with `FILE_APPEND | LOCK_EX`, without re-reading/re-writing the complete log payload.
- `plugin_mastodon_io_read_file()` — line 936 — Read a file through the FlatPress I/O layer when available, allowing the core APCu file hotcache for small files.
- `plugin_mastodon_io_read_file_uncached()` — line 952 — Read a file without FlatPress request-local caches.
- `plugin_mastodon_file_permissions_mode()` — line 970 — Return the FlatPress `FILE_PERMISSIONS` mode used for plugin runtime files.
- `plugin_mastodon_admin_add_info_row()` — line 10109 — Add one admin diagnostics row when the value is available.
- `plugin_mastodon_admin_boolean_label()` — line 10094 — Return a localized yes/no/unknown label for admin diagnostics.
- `plugin_mastodon_apply_file_permissions()` — line 979 — Apply FlatPress `FILE_PERMISSIONS` to a plugin runtime file.
- `plugin_mastodon_io_write_file()` — line 992 — Write a file through the FlatPress I/O layer when available and enforce `FILE_PERMISSIONS` after successful writes.
- `plugin_mastodon_is_public_host()` — line 5050 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_lang_string()` — line 4510 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_limit_status_text()` — line 7243 — Truncate status text using Mastodon URL-budget rules.
- `plugin_mastodon_limit_text()` — line 5648 — Limit text to a maximum number of characters.
- `plugin_mastodon_list_local_entries()` — line 8003 — List local FlatPress entry identifiers.
- `plugin_mastodon_local_item_date_key()` — line 2487 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_local_item_matches_content_window()` — line 2629 — Determine whether a local FlatPress item is inside the active content synchronization window.
- `plugin_mastodon_local_item_matches_sync_start()` — line 2617 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_local_item_timestamp()` — line 7846 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_log()` — line 2798 — Append a line to the rotated append-only plugin sync log.
- `plugin_mastodon_log_flush_skip_summaries()` — line 2843 — Flush aggregated skip counters as concise summary lines.
- `plugin_mastodon_log_max_bytes()` — line 1053 — Return the sync.log rotation size limit.
- `plugin_mastodon_log_rotate_files()` — line 1064 — Return the number of retained rotated sync logs.
- `plugin_mastodon_log_skip()` — line 2813 — Aggregate high-volume skip messages by reason until the current sync phase ends.
- `plugin_mastodon_mapping_matches_sync_start()` — line 2661 — Determine whether a stored synchronization mapping still belongs to the active sync-start window.
- `plugin_mastodon_mastodon_api()` — line 8323 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 4809 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 5429 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_mastodon_json()` — line 8372 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_maybe_sync()` — line 10062 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_media_copy_tree()` — line 5807 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_media_delete_tree()` — line 5780 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_download()` — line 6765 — Download a remote media asset.
- `plugin_mastodon_media_guess_mime_type()` — line 5879 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_description_from_bbcode_content()` — line 6132 — Normalize optional AudioVideo BBCode content into a Mastodon media description.
- `plugin_mastodon_media_parse_tag_attributes()` — line 6101 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_media_prepare_directory()` — line 5764 — Ensure that a media directory exists.
- `plugin_mastodon_media_relative_to_absolute()` — line 5751 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_media_processing_attempts()` — line 7520 — Calculate media-type- and size-aware polling attempts for asynchronous Mastodon media processing.
- `plugin_mastodon_media_transfer_timeout()` — line 7544 — Calculate media-type- and size-aware HTTP transfer timeouts for uploads.
- `plugin_mastodon_media_type_from_mime()` — line 5946 — Classify a MIME type or extension as image, video, or audio.
- `plugin_mastodon_normalize_comment_parent_id()` — line 4235 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_normalize_delete_sync_enabled()` — line 2455 — Normalize the toggle that enables or disables the follow-up deletion synchronization.
- `plugin_mastodon_normalize_head_username()` — line 2114 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_normalize_media_relative_path()` — line 5728 — Normalize a FlatPress media path and reject unsafe paths.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 2401 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_normalize_instance_url()` — line 2081 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_old_thread_reply_check()` — line 2437 — Normalize the toggle that enables rotating context checks for known synchronized Mastodon threads.
- `plugin_mastodon_normalize_scheduled_window_days()` — line 2342 — Normalize the configured 7/14/30-day automatic window for scheduled runs.
- `plugin_mastodon_normalize_status_language()` — line 2257 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_normalize_sync_start_date()` — line 2316 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_sync_time()` — line 2299 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_tag_list()` — line 4736 — Normalize a list of tag labels.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 2383 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_oauth_legacy_scopes()` — line 563 — Return the legacy OAuth scope string used before scope discovery was added.
- `plugin_mastodon_oauth_preferred_scopes()` — line 657 — Prefer the narrow `profile` scope on current instances and fall back to `read:accounts` on older ones.
- `plugin_mastodon_oauth_profile_scopes()` — line 571 — Return the stricter OAuth scope string that uses `profile` for `verify_credentials`.
- `plugin_mastodon_oauth_scope_supported()` — line 636 — Check whether the configured Mastodon instance advertises support for a specific OAuth scope.
- `plugin_mastodon_oauth_scopes()` — line 674 — Return the OAuth scopes that the currently registered app may safely request.
- `plugin_mastodon_oauth_server_metadata()` — line 580 — Discover and cache OAuth authorization-server metadata from `/.well-known/oauth-authorization-server`.
- `plugin_mastodon_oauth_supported_scopes()` — line 599 — Parse the discoverable OAuth scopes supported by the configured Mastodon instance.
- `plugin_mastodon_old_thread_context_rotation_limit()` — line 9261 — Return the maximum number of known synchronized threads checked for replies per content sync run.
- `plugin_mastodon_parse_http_response_headers()` — line 8041 — Parse raw HTTP response headers.
- `plugin_mastodon_parse_iso_datetime()` — line 4142 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 4160 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_photoswipe_plugin_active()` — line 4639 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_plain_text_from_bbcode()` — line 5092 — Convert FlatPress BBCode into plain text for Mastodon export, removing complete AudioVideo player tags including optional description content.
- `plugin_mastodon_profile_url()` — line 2183 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_public_comment_url()` — line 5414 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_public_comments_url()` — line 5386 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_entry_url()` — line 5359 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_url_for_mastodon()` — line 5073 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_register_app()` — line 8416 — Register the FlatPress application on the configured Mastodon instance with the preferred discoverable scope set.
- `plugin_mastodon_remote_media_description()` — line 6636 — Resolve the best description for a remote attachment.
- `plugin_mastodon_remote_media_source_url()` — line 6596 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_media_source_urls()` — line 6614 — Resolve direct-download fallback candidates for a remote attachment.
- `plugin_mastodon_remote_status_date_key()` — line 2510 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_remote_status_image_attachments()` — line 6587 — Extract image attachments from a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 4214 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_remote_status_matches_content_window()` — line 2650 — Determine whether a remote Mastodon status is inside the active content synchronization window.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 2639 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_remote_status_tags()` — line 4830 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_remote_status_timestamp()` — line 4182 — Resolve the best FlatPress-adjusted timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 4201 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 5000 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 5028 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_resolve_comment_reply_target()` — line 4274 — Resolve the remote reply target for a local comment export.
- `plugin_mastodon_list_local_comment_ids()` — line 4327 — Scan the FlatPress comment directory directly so local reply export is not blocked by stale comment-list caches.
- `plugin_mastodon_response_error_message()` — line 8387 — Extract the most useful error message from an API response.
- `plugin_mastodon_run_deletion_sync()` — line 9648 — Run the deferred deletion synchronization in a follow-up request after content sync completed, with scheduled-window-limited remote existence lookups and progress cursors.
- `plugin_mastodon_run_sync()` — line 9979 — Run a full synchronization cycle.
- `plugin_mastodon_runtime_cache_clear()` — line 736 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_runtime_cache_get()` — line 694 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 718 — Store a value in the request-local plugin cache.
- `plugin_mastodon_safe_filename()` — line 5715 — Sanitize a file name for local storage.
- `plugin_mastodon_safe_path_component()` — line 5700 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_save_options()` — line 1918 — Persist plugin options.
- `plugin_mastodon_scheduled_window_choices()` — line 2354 — Return the localized admin radio choices for the scheduled synchronization window.
- `plugin_mastodon_scheduled_window_start_date()` — line 2578 — Return the FlatPress-local date key that starts the automatic scheduled sync window.
- `plugin_mastodon_secret_decode()` — line 2050 — Decode a previously stored secret value.
- `plugin_mastodon_secret_encode()` — line 2027 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_key()` — line 2010 — Build the encryption key used for stored secrets.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 2410 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_should_check_old_thread_replies()` — line 2446 — Check whether known synchronized Mastodon threads should be checked for replies in rotating batches.
- `plugin_mastodon_should_run_deletion_sync()` — line 2464 — Check whether the follow-up deletion synchronization is enabled.
- `plugin_mastodon_should_update_local_from_remote()` — line 2392 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_state_comment_key()` — line 3179 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_get_comment_meta()` — line 3711 — Return mapping metadata for a local comment.
- `plugin_mastodon_state_has_dirty_comment()` — line 3398 — Check whether a comment is queued for synchronization outside the scheduled window.
- `plugin_mastodon_state_has_dirty_entry()` — line 3348 — Check whether an entry is queued for synchronization outside the scheduled window.
- `plugin_mastodon_state_set_comment_tombstone()` — line 3725 — Store a tombstone that blocks stale re-imports of one deleted remote comment.
- `plugin_mastodon_state_has_comment_tombstone()` — line 3745 — Check whether one remote Mastodon comment status was tombstoned locally.
- `plugin_mastodon_protect_locally_deleted_exported_comments()` — line 3756 — Tombstone locally deleted exported FlatPress comment mappings before the next content sync can stale-reimport them from Mastodon thread context.
- `plugin_mastodon_reattach_local_comment_to_entry_status()` — line 3799 — Remove a local imported reply parent link and reattach the surviving reply to the synchronized entry status after its remote parent reply disappeared.
- `plugin_mastodon_state_remove_pending_comment_remote_recheck()` — line 3859 — Remove one pending descendant recheck marker.
- `plugin_mastodon_state_get_pending_comment_remote_recheck()` — line 3873 — Return one pending descendant recheck marker.
- `plugin_mastodon_state_set_pending_comment_remote_recheck()` — line 3888 — Mark one local comment for follow-up verification after an ancestor disappeared remotely.
- `plugin_mastodon_state_set_deletions_pending()` — line 3914 — Persist whether another deletion follow-up request is pending, which scope it should run, and when it may start.
- `plugin_mastodon_deletion_sync_due()` — line 3928 — Check whether the pending deletion synchronization may start after its persisted not-before timestamp.
- `plugin_mastodon_state_has_comment_recheck_scope()` — line 3956 — Check whether the next deletion follow-up request should run only the targeted descendant recheck scope.
- `plugin_mastodon_build_comment_remote_child_index()` — line 3965 — Build a direct-child index for mapped remote reply trees.
- `plugin_mastodon_queue_comment_descendant_remote_rechecks()` — line 3993 — Queue only the direct mapped local children of one deleted remote comment for additional verification passes.
- `plugin_mastodon_process_pending_comment_remote_rechecks()` — line 4032 — Process pending descendant rechecks breadth-first so deeper reply chains can converge within the same targeted follow-up request.
- `plugin_mastodon_state_entry_media_attachment_signature()` — line 3689 — Return the stored attachment-signature for one entry mapping.
- `plugin_mastodon_state_entry_media_description_signature()` — line 3699 — Return the stored description-signature for one entry mapping.
- `plugin_mastodon_state_entry_remote_media()` — line 3659 — Return stored remote media descriptors for one entry mapping.
- `plugin_mastodon_state_get_entry_meta()` — line 3616 — Return mapping metadata for a local entry.
- `plugin_mastodon_normalize_deletions_pending_scope()` — line 3115 — Normalize the targeted deletion-follow-up scope marker.
- `plugin_mastodon_state_normalize()` — line 3128 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_scheduler_source_signature()` — line 2932 — Return the current stat-based signature of the full state file.
- `plugin_mastodon_scheduler_state_default()` — line 2911 — Return an empty scheduler state derived from the full default state.
- `plugin_mastodon_scheduler_state_from_state()` — line 2979 — Build the lightweight scheduler summary from a full runtime state.
- `plugin_mastodon_scheduler_state_normalize()` — line 2941 — Normalize a scheduler summary without touching full mapping arrays.
- `plugin_mastodon_scheduler_state_read()` — line 3044 — Load the compact scheduler summary through the APCu-capable FlatPress file I/O path, retry with an uncached scheduler-state read if a host returns stale cached content, and rebuild from full state only when missing, invalid, or truly stale.
- `plugin_mastodon_scheduler_state_write()` — line 2999 — Persist the lightweight scheduler state.
- `plugin_mastodon_state_read()` — line 2873 — Load the persisted runtime state from disk without storing or reading a full-state APCu fallback.
- `plugin_mastodon_state_remove_dirty_comment()` — line 3384 — Remove a comment from the dirty queue.
- `plugin_mastodon_state_remove_dirty_entry()` — line 3335 — Remove an entry from the dirty queue.
- `plugin_mastodon_state_remove_comment_mapping()` — line 3290 — Remove the mapping between a local comment and a remote status.
- `plugin_mastodon_state_remove_entry_mapping()` — line 3268 — Remove the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_comment_mapping()` — line 3233 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_set_dirty_comment()` — line 3361 — Add an older changed comment to the persistent dirty queue.
- `plugin_mastodon_state_set_dirty_entry()` — line 3315 — Add an older changed entry to the persistent dirty queue.
- `plugin_mastodon_state_set_entry_mapping()` — line 3194 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_write()` — line 3090 — Persist the runtime state to disk and refresh the compact scheduler summary after successful writes, without caching the full state in APCu.
- `plugin_mastodon_status_missing_response()` — line 8638 — Check whether an API response means that the referenced Mastodon status no longer exists.
- `plugin_mastodon_status_text_length()` — line 7211 — Calculate the Mastodon-visible status length with instance URL budgeting.
- `plugin_mastodon_stream_context_request()` — line 8071 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 4792 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 4857 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_subject_line_is_noise()` — line 5136 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_sync_due()` — line 9949 — Determine whether the scheduled synchronization is currently due.
- `plugin_mastodon_sync_local_to_remote()` — line 9417 — Synchronize local FlatPress content to Mastodon.
- `plugin_mastodon_sync_remote_to_local()` — line 9350 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_tag_plugin_active()` — line 4605 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_timestamp_date_key()` — line 2473 — Convert a FlatPress-adjusted timestamp into a stable date key.
- `plugin_mastodon_update_status()` — line 8678 — Update an existing Mastodon status.
- `plugin_mastodon_upload_media_items()` — line 7613 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_verify_credentials()` — line 8489 — Verify the currently configured access token.
- `plugin_mastodon_wait_for_media_attachment()` — line 7564 — Poll an asynchronously processed Mastodon media attachment until it is ready or times out.
- `setup()` — line 10276 — Register the Mastodon admin panel template and assign plugin data to Smarty.

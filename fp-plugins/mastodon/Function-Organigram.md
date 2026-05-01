# Mastodon Plugin – PHP Function Organigram

## Purpose of this document

This document gives developers a structured overview of the Mastodon plugin PHP functions.

The layout is intentionally hierarchical so responsibilities, call paths, and important helper functions can be identified quickly.

## Scope

- The focus is the Mastodon plugin PHP code.
- The document is organized by responsibility and call path.
- Recently added FlatPress configuration reuse, centralized plugin-state detection, and FlatPress I/O helpers are reflected explicitly.
- The companion-plugin diagnostics for BBCode, PhotoSwipe, Tag, and Emoticons are covered.
- The admin-side Mastodon instance-information snapshot, manual refresh button, exact-version display, and reuse of cached instance capabilities for later sync runs are covered.
- Mastodon instance-dependent URL budgeting, bounded PHP execution-budget refreshes, OAuth scope discovery with a strict `profile`-scope preference and an older-instance fallback to `read:accounts`, asynchronous media-upload readiness polling, best-effort cleanup of unattached uploaded Mastodon media before a failed final posting finishes, follow-up deletion synchronization, the admin toggle that can disable deletion synchronization, comment tombstones that block stale re-imports of deleted remote replies, early protection of locally deleted exported FlatPress comments before the next content sync, local reattachment of surviving descendant replies to the synchronized entry status during deletion follow-up, targeted descendant-recheck queues with a dedicated `comment_rechecks` follow-up scope, and the separate persistence of content-sync and deletion-sync counters are reflected explicitly.
- FlatPress timeoffset-aware remote import timestamps for Mastodon statuses and replies are reflected explicitly.
- FlatPress-local admin time display for the daily synchronization time, last content sync, and last deletion sync is reflected explicitly while keeping the stored synchronization time in UTC.
- The default-enabled option to quote the replied-to Mastodon comment during comment import, including the replied-to user label, is reflected explicitly.
- Export-side conversion of active Emoticons plugin shortcodes to standard Unicode emoji for FlatPress entry titles, entry bodies, comment author labels, and comment bodies is reflected explicitly.
- All plugin functions and admin panel methods currently present in the file are covered.
- Function summaries are derived from the current source file PHPDoc and verified against the function names and their location in the file.
- The terms **Entry** and **Comment** follow the wording used by the source code.

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
- `GET /api/v2/instance`
- `POST /api/v2/media`
- `GET /api/v1/media/:id`
- `DELETE /api/v1/media/:id`

Current export behavior uses these endpoints in a version-aware way:
- `POST /api/v2/media` carries the initial media `description` whenever the FlatPress entry already knows the alt text.
- `PUT /api/v1/statuses/:id` reuses stored `media_ids` for unchanged attachments and, on Mastodon 4.1+, can update changed alt text in place through `media_attributes` without re-uploading the file payload.

## Function count

The plugin file currently contains **243** callable functions/methods documented in this organigram:
- **240** top-level plugin functions
- **3** admin panel class methods (`setup()`, `main()`, `onsubmit()`)

## High-level call flow

### Frontend and scheduler entry points

- `plugin_mastodon_head()`  
  Reads the saved Mastodon configuration, normalizes the instance URL and username, and emits:
  - `<link rel="me" ...>`
  - `<meta name="fediverse:creator" ...>`

- `plugin_mastodon_maybe_sync()`  
  Reads the saved state for the current request and then either:
  - runs `plugin_mastodon_run_sync()` when the scheduled content sync is due, or
  - runs `plugin_mastodon_run_deletion_sync()` in a later follow-up request when deletion work is pending.

- `plugin_mastodon_run_sync()`  
  Refreshes the shared-request PHP execution budget through `plugin_mastodon_extend_time_limit()`, protects locally deleted exported FlatPress comment mappings through `plugin_mastodon_protect_locally_deleted_exported_comments()` so stale Mastodon thread context cannot resurrect them during the same request, and then executes the two main directions in order:
  1. `plugin_mastodon_sync_remote_to_local()`
  2. `plugin_mastodon_sync_local_to_remote()`
  If both directions finish successfully, the function marks a follow-up deletion pass as pending for a later web request.

- `plugin_mastodon_run_deletion_sync()`  
  Runs the real deletion synchronization in a separate follow-up request. It first checks `plugin_mastodon_should_run_deletion_sync()` so the user-controlled admin toggle can disable the delete pass, resets only `deletion_stats` while preserving `content_stats` from the last content sync, gates mapped items through `plugin_mastodon_mapping_matches_sync_start()` so the delete pass stays inside the configured sync-start window, then either runs the full reconciliation pass or a targeted `comment_rechecks` follow-up scope via `plugin_mastodon_state_has_comment_recheck_scope()`. Direct descendants of newly deleted replies are queued with `plugin_mastodon_queue_comment_descendant_remote_rechecks()`, surviving direct child replies can be reattached to the synchronized entry status through `plugin_mastodon_reattach_local_comment_to_entry_status()`, and pending deep-thread cleanup is processed breadth-first through `plugin_mastodon_process_pending_comment_remote_rechecks()`.

### Local → remote export path

- `plugin_mastodon_sync_local_to_remote()`
  - loads options and persisted state
  - retrieves export candidates with `plugin_mastodon_list_local_entries()`
  - orders entries with `plugin_mastodon_compare_local_entries_for_export()`
  - builds entry text with `plugin_mastodon_build_entry_status_text()`
  - budgets status length via `plugin_mastodon_instance_url_reserved_length()`, `plugin_mastodon_status_text_length()`, and `plugin_mastodon_limit_status_text()`
  - refreshes the request time budget for long-running export loops and Mastodon communication through `plugin_mastodon_extend_time_limit()`
  - derives the Mastodon `language` value through `plugin_mastodon_configured_status_language()` and `plugin_mastodon_normalize_status_language()`
  - collects local images and galleries with `plugin_mastodon_collect_local_entry_media()`
  - normalizes media items and computes attachment/description signatures with `plugin_mastodon_prepare_entry_media_items()`, `plugin_mastodon_entry_media_attachment_signature_from_items()`, and `plugin_mastodon_entry_media_description_signature_from_items()`
  - prepares a per-entry sync strategy with `plugin_mastodon_prepare_entry_media_sync_plan()`
  - uploads media through `plugin_mastodon_upload_media_items()` only when attachments really changed or when an older/unknown Mastodon version cannot edit descriptions in place
  - sends initial alt text in `POST /api/v2/media`, reuses stored remote `media_ids` for text-only edits, and on Mastodon 4.1+ forwards changed alt text through `plugin_mastodon_status_media_attributes()` and `plugin_mastodon_update_status()`
  - waits for asynchronously processed media through `plugin_mastodon_fetch_media_attachment()` and `plugin_mastodon_wait_for_media_attachment()` when required
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
  - imports top-level statuses with `plugin_mastodon_import_remote_entry()` and deliberately skips reply statuses as entry imports when `import_synced_comments_as_entries` is disabled, because Mastodon account timelines may still include self-replies even with `exclude_replies=true`
  - fetches thread context with `plugin_mastodon_fetch_status_context()`
  - imports replies with `plugin_mastodon_import_remote_context_descendants()`
  - imports individual replies through `plugin_mastodon_import_remote_comment()`
  - blocks stale re-imports of deleted remote replies via `plugin_mastodon_state_has_comment_tombstone()`
  - optionally prepends a BBCode quote of the replied-to Mastodon comment (or the previously exported FlatPress comment) so FlatPress readers can see who was answered and what was answered to
  - refreshes known older threads with `plugin_mastodon_collect_known_entry_context_targets()`

### Admin panel flow

- `plugin_mastodon_admin_assign()` assigns the plugin data for the Smarty view, including companion-plugin diagnostics, cached Mastodon instance-information rows, and cache-state/error metadata.
- `setup()` registers the admin template and delegates to `plugin_mastodon_admin_assign()`.
- `main()` keeps the FlatPress admin lifecycle stable.
- `onsubmit()` handles:
  - configuration normalization and storage
  - OAuth app registration
  - authorization code exchange
  - manual synchronization
  - explicit instance-information refreshes for the admin table
  - deferred deletion synchronization on later non-POST requests

## A. Entry points and admin integration

- `plugin_mastodon_head()` — line 1262 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_maybe_sync()` — line 7161 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_run_sync()` — line 7096 — Run a full synchronization cycle.
- `plugin_mastodon_run_deletion_sync()` — line 6875 — Run the deferred deletion synchronization in a follow-up request after content sync completed.
- `plugin_mastodon_sync_due()` — line 7074 — Determine whether the scheduled synchronization is currently due.
- `plugin_mastodon_admin_assign()` — line 7323 — Assign plugin data to Smarty for the admin panel, including cached instance-information rows.
- `setup()` — line 7362 — Register the Mastodon admin panel template and assign plugin data to Smarty.
- `main()` — line 7367 — Keep the admin panel lifecycle compatible with FlatPress without extra processing.
- `onsubmit()` — line 7371 — Process configuration saves, OAuth actions, authorization-code exchange, manual instance-information refreshes, and the manual synchronization trigger.

## B. Defaults, configuration, secrets, and centralized FlatPress feature toggles

- `plugin_mastodon_default_options()` — line 56 — Return the default plugin option values.
- `plugin_mastodon_clear_saved_instance_info()` — line 86 — Remove persisted instance-information snapshots and related admin refresh errors from an option array.
- `plugin_mastodon_compact_instance_document()` — line 105 — Reduce `/api/v2/instance` responses to the stable subset reused by the plugin and admin table.
- `plugin_mastodon_saved_instance_document()` — line 310 — Decode the persisted compact instance snapshot from the saved plugin configuration.
- `plugin_mastodon_store_instance_document()` — line 355 — Persist a compact instance-information snapshot in the FlatPress plugin configuration and warm request/APCu caches.
- `plugin_mastodon_store_instance_error()` — line 401 — Persist the latest instance-information refresh failure for the admin diagnostics view.
- `plugin_mastodon_refresh_instance_information()` — line 417 — Force a live `/api/v2/instance` refresh, compact the response, and store it for later requests.
- `plugin_mastodon_default_content_stats()` — line 437 — Return the default counters for the last content synchronization.
- `plugin_mastodon_default_deletion_stats()` — line 454 — Return the default counters for the last deletion synchronization.
- `plugin_mastodon_default_state()` — line 467 — Return the default runtime state structure, including the targeted deletion-follow-up scope marker.
- `plugin_mastodon_oauth_legacy_scopes()` — line 491 — Return the legacy OAuth scope string used before scope discovery was added.
- `plugin_mastodon_oauth_profile_scopes()` — line 499 — Return the stricter OAuth scope string that uses `profile` for `verify_credentials`.
- `plugin_mastodon_oauth_server_metadata()` — line 508 — Discover and cache OAuth authorization-server metadata from `/.well-known/oauth-authorization-server`.
- `plugin_mastodon_oauth_supported_scopes()` — line 527 — Parse the discoverable OAuth scopes supported by the configured Mastodon instance.
- `plugin_mastodon_oauth_scope_supported()` — line 564 — Check whether the configured Mastodon instance advertises support for a specific OAuth scope.
- `plugin_mastodon_oauth_preferred_scopes()` — line 585 — Prefer the narrow `profile` scope on current instances and fall back to `read:accounts` on older ones.
- `plugin_mastodon_oauth_scopes()` — line 602 — Return the OAuth scopes that the currently registered app may safely request.
- `plugin_mastodon_fp_config()` — line 679 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 715 — Read a nested FlatPress configuration value.
- `plugin_mastodon_fp_timeoffset_seconds()` — line 731 — Return the configured FlatPress time offset in seconds for exact local admin-time conversion.
- `plugin_mastodon_fp_timeoffset_label()` — line 751 — Format the configured FlatPress time offset as a UTC label for admin users.
- `plugin_mastodon_sync_time_to_minutes()` — line 781 — Convert a normalized `HH:MM` sync-time value into minutes after midnight.
- `plugin_mastodon_minutes_to_sync_time()` — line 792 — Convert minutes after midnight back into a normalized `HH:MM` sync-time value.
- `plugin_mastodon_sync_time_utc_to_local()` — line 808 — Convert the stored UTC synchronization time to the FlatPress-local admin time.
- `plugin_mastodon_sync_time_local_to_utc()` — line 819 — Convert the FlatPress-local admin synchronization time back to stored UTC.
- `plugin_mastodon_format_admin_datetime()` — line 830 — Format stored UTC timestamps for the admin panel using FlatPress `timeoffset`, date format, and time format.
- `plugin_mastodon_fp_timeoffset_hours()` — line 731 — Return the configured FlatPress time offset in whole hours.
- `plugin_mastodon_get_options()` — line 915 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_save_options()` — line 970 — Persist plugin options, invalidate mismatching instance snapshots on URL changes, and keep matching refreshed snapshots.
- `plugin_mastodon_secret_key()` — line 1055 — Build the encryption key used for stored secrets.
- `plugin_mastodon_secret_encode()` — line 1072 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_decode()` — line 1095 — Decode a previously stored secret value.
- `plugin_mastodon_normalize_instance_url()` — line 1126 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_head_username()` — line 1159 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_instance_authority()` — line 1198 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_profile_url()` — line 1228 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_fediverse_creator_value()` — line 1244 — Build the fediverse creator meta value.
- `plugin_mastodon_normalize_status_language()` — line 1302 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_configured_status_language()` — line 1324 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_normalize_sync_time()` — line 1344 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_sync_start_date()` — line 1361 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_boolean_option()` — line 1387 — Normalize a boolean-like option value to the stored string representation.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 1403 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_should_update_local_from_remote()` — line 1412 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 1421 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 1430 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_normalize_quote_imported_reply_parent()` — line 1439 — Normalize the toggle that controls whether imported Mastodon replies should quote the replied-to comment.
- `plugin_mastodon_should_quote_imported_reply_parent()` — line 1448 — Check whether imported Mastodon replies should include a quote of the replied-to comment.
- `plugin_mastodon_normalize_delete_sync_enabled()` — line 1457 — Normalize the toggle that enables or disables the follow-up deletion synchronization.
- `plugin_mastodon_should_run_deletion_sync()` — line 1466 — Check whether the follow-up deletion synchronization is enabled.
- `plugin_mastodon_enabled_plugin_state()` — line 2802 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_tag_plugin_active()` — line 2867 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_bbcode_plugin_active()` — line 2884 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_photoswipe_plugin_active()` — line 2901 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_emoticons_plugin_active()` — line 2918 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_companion_plugins_status()` — line 2933 — Return the status of companion FlatPress plugins used for the full Mastodon feature set.

## C. Caching, filesystem helpers, logging, and persisted state

- `plugin_mastodon_runtime_cache_get()` — line 622 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 646 — Store a value in the request-local plugin cache.
- `plugin_mastodon_runtime_cache_clear()` — line 664 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_io_read_file()` — line 761 — Read a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_read_file_uncached()` — line 774 — Read a file without FlatPress request-local caches.
- `plugin_mastodon_io_write_file()` — line 791 — Write a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_append_file()` — line 804 — Append to a file via the FlatPress I/O layer.
- `plugin_mastodon_apcu_enabled()` — line 820 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_cache_key()` — line 829 — Build the namespaced APCu key used by this plugin.
- `plugin_mastodon_apcu_fetch()` — line 843 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 858 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_delete()` — line 871 — Delete a value from APCu using the FlatPress namespace key builder.
- `plugin_mastodon_file_prestat()` — line 884 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 904 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_ensure_state_dir()` — line 1639 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_log()` — line 1648 — Append a line to the plugin sync log.
- `plugin_mastodon_state_read()` — line 1658 — Load the persisted runtime state from disk.
- `plugin_mastodon_state_write()` — line 1697 — Persist the runtime state to disk.
- `plugin_mastodon_normalize_deletions_pending_scope()` — line 1721 — Normalize the targeted deletion-follow-up scope marker.
- `plugin_mastodon_state_normalize()` — line 1734 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_comment_key()` — line 1781 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_set_entry_mapping()` — line 1796 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_comment_mapping()` — line 1834 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_remove_entry_mapping()` — line 1869 — Remove the mapping between a local entry and a remote status.
- `plugin_mastodon_state_remove_comment_mapping()` — line 1888 — Remove the mapping between a local comment and a remote status.
- `plugin_mastodon_state_get_entry_meta()` — line 1909 — Return mapping metadata for a local entry.
- `plugin_mastodon_state_set_entry_media_meta()` — line 1923 — Persist cached remote media IDs plus local attachment/description signatures for a synchronized entry.
- `plugin_mastodon_state_get_comment_meta()` — line 2004 — Return mapping metadata for a local comment.
- `plugin_mastodon_state_set_comment_tombstone()` — line 2018 — Store a tombstone that blocks stale re-imports of one deleted remote comment.
- `plugin_mastodon_state_has_comment_tombstone()` — line 2038 — Check whether one remote Mastodon comment status was tombstoned locally.
- `plugin_mastodon_protect_locally_deleted_exported_comments()` — line 2050 — Tombstone locally deleted exported FlatPress comment mappings before the next content sync can stale-reimport them from Mastodon thread context.
- `plugin_mastodon_reattach_local_comment_to_entry_status()` — line 2096 — Remove a local imported reply parent link and reattach the surviving reply to the synchronized entry status after its remote parent reply disappeared.
- `plugin_mastodon_state_remove_pending_comment_remote_recheck()` — line 2156 — Remove one pending descendant recheck marker.
- `plugin_mastodon_state_get_pending_comment_remote_recheck()` — line 2170 — Return one pending descendant recheck marker.
- `plugin_mastodon_state_set_pending_comment_remote_recheck()` — line 2185 — Mark one local comment for follow-up verification after an ancestor disappeared remotely.
- `plugin_mastodon_state_set_deletions_pending()` — line 2210 — Persist whether another deletion follow-up request is pending and which scope it should run.
- `plugin_mastodon_state_has_comment_recheck_scope()` — line 2222 — Check whether the next deletion follow-up request should run only the targeted descendant recheck scope.
- `plugin_mastodon_build_comment_remote_child_index()` — line 2231 — Build a direct-child index for mapped remote reply trees.
- `plugin_mastodon_queue_comment_descendant_remote_rechecks()` — line 2259 — Queue only the direct mapped local children of one deleted remote comment for additional verification passes.
- `plugin_mastodon_process_pending_comment_remote_rechecks()` — line 2298 — Process pending descendant rechecks breadth-first so deeper reply chains can converge within the same targeted follow-up request.

## D. Date, timestamp, visibility, and threading helpers

- `plugin_mastodon_timestamp_to_flatpress_time()` — line 744 — Convert a real Unix timestamp into FlatPress's offset-adjusted timestamp model.
- `plugin_mastodon_timestamp_date_key()` — line 1475 — Convert a FlatPress-adjusted timestamp into a stable date key.
- `plugin_mastodon_local_item_date_key()` — line 1489 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_remote_status_date_key()` — line 1509 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_datetime_date_key()` — line 1535 — Normalize a stored date/datetime string to the sync-start date-key format.
- `plugin_mastodon_date_matches_sync_start()` — line 1559 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_local_item_matches_sync_start()` — line 1578 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 1588 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_mapping_matches_sync_start()` — line 1599 — Determine whether a stored synchronization mapping still belongs to the active sync-start window.
- `plugin_mastodon_parse_iso_datetime()` — line 2403 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 2421 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_remote_status_timestamp()` — line 2443 — Resolve the best FlatPress-adjusted timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 2462 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 2475 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_comment_parent_fields()` — line 2487 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_normalize_boolean_option()` — line 1387 — Normalize a boolean-like option value to the stored string representation.
- `plugin_mastodon_normalize_comment_parent_id()` — line 2496 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 2513 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_resolve_comment_reply_target()` — line 2535 — Resolve the remote reply target for a local comment export.
- `plugin_mastodon_list_local_comment_ids()` — line 2589 — Scan the FlatPress comment directory directly so local reply export is not blocked by stale comment-list caches.

## E. Text, URLs, language strings, tags, emojis, and BBCode/HTML conversion

- `plugin_mastodon_guess_subject()` — line 2626 — Guess a subject line from imported plain text.
- `plugin_mastodon_html_entity_decode()` — line 2668 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_blog_base_url()` — line 2677 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_extract_url_token()` — line 2713 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_absolute_url()` — line 2730 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_lang_string()` — line 2772 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_normalize_tag_list()` — line 2974 — Normalize a list of tag labels.
- `plugin_mastodon_extend_time_limit()` — line 4614 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_extract_flatpress_tags()` — line 3005 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 3030 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 3047 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_remote_status_tags()` — line 3068 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 3095 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 3158 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 3171 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 3184 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 3238 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_prepare_emoticons_for_mastodon()` — line 3253 — Convert active FlatPress emoticon shortcodes to standard Unicode emoji before Mastodon export.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 3266 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_is_public_host()` — line 3288 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_public_url_for_mastodon()` — line 3311 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_plain_text_from_bbcode()` — line 3330 — Convert FlatPress BBCode into plain text for Mastodon export.
- `plugin_mastodon_subject_line_is_noise()` — line 3372 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_domains_match()` — line 3400 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_cleanup_imported_text()` — line 3414 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_dom_children_to_flatpress()` — line 3468 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 3486 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_public_entry_url()` — line 3595 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_comments_url()` — line 3622 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_comment_url()` — line 3650 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 3665 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_flatpress_to_mastodon()` — line 3767 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_limit_text()` — line 3882 — Limit text to a maximum number of characters.

## F. Local content access, media processing, hashing, and export ordering

- `plugin_mastodon_entry_hash()` — line 3904 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_comment_hash()` — line 3916 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_remote_status_author_label()` — line 6245 — Build a readable author label for quoted Mastodon replies.
- `plugin_mastodon_strip_leading_quote_block()` — line 6278 — Remove one leading BBCode quote block so imported reply quotes do not compound indefinitely.
- `plugin_mastodon_imported_reply_quote_payload()` — line 6311 — Resolve the author and body that should be quoted for an imported Mastodon reply.
- `plugin_mastodon_build_imported_reply_quote()` — line 6354 — Build the optional BBCode quote block for an imported Mastodon reply.
- `plugin_mastodon_safe_path_component()` — line 3934 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_safe_filename()` — line 3949 — Sanitize a file name for local storage.
- `plugin_mastodon_media_relative_to_absolute()` — line 3962 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_media_prepare_directory()` — line 3975 — Ensure that a media directory exists.
- `plugin_mastodon_media_delete_tree()` — line 3991 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_copy_tree()` — line 4018 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_bbcode_attr_escape()` — line 4056 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_media_guess_mime_type()` — line 4068 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_parse_tag_attributes()` — line 4104 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_collect_local_entry_media()` — line 4135 — Collect local images referenced by an entry or gallery tag.
- `plugin_mastodon_prepare_entry_media_items()` — line 4259 — Normalize collected local media items into reusable path/description tuples.
- `plugin_mastodon_entry_media_attachment_signature_from_items()` — line 4282 — Hash only the attachment identity of normalized media items.
- `plugin_mastodon_entry_media_description_signature_from_items()` — line 4305 — Hash only the alt-text portion of normalized media items.
- `plugin_mastodon_entry_media_signature()` — line 4324 — Build a combined attachment+description signature for media references contained in entry content.
- `plugin_mastodon_remote_status_image_attachments()` — line 4341 — Extract image attachments from a remote Mastodon status.
- `plugin_mastodon_remote_media_source_url()` — line 4364 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_media_description()` — line 4378 — Resolve the best description for a remote attachment.
- `plugin_mastodon_remote_media_focus()` — line 4392 — Normalize a Mastodon media focus string when present.
- `plugin_mastodon_remote_media_descriptors_from_status()` — line 4409 — Extract reusable media descriptors (`id`, `description`, `focus`) from a Mastodon status payload.
- `plugin_mastodon_remote_media_descriptors_from_media_ids()` — line 4436 — Build fallback reusable media descriptors from already-known IDs and local media items.
- `plugin_mastodon_media_download()` — line 4499 — Download a remote media asset.
- `plugin_mastodon_build_imported_media_bbcode()` — line 4511 — Build FlatPress BBCode for imported remote media attachments.
- `plugin_mastodon_collect_entry_files()` — line 5314 — Collect entry files recursively from the FlatPress content tree.
- `plugin_mastodon_local_item_timestamp()` — line 5341 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_compare_local_entries_for_export()` — line 5367 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_list_local_entries()` — line 5385 — List local FlatPress entry identifiers.

## G. HTTP transport, PHP timeout budgeting, instance capability lookup, status-length budgeting, OAuth, Mastodon API calls, and media upload

- `plugin_mastodon_extend_time_limit()` — line 4614 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_instance_document()` — line 4645 — Load and cache the compact Mastodon instance document, preferring the saved FlatPress snapshot before APCu and live network fetches.
- `plugin_mastodon_instance_version()` — line 4689 — Extract the human-readable Mastodon server version from the cached instance document.
- `plugin_mastodon_instance_supports_status_media_attributes()` — line 4707 — Decide whether `PUT /api/v1/statuses/:id` may safely use `media_attributes` for in-place alt-text edits.
- `plugin_mastodon_instance_configuration()` — line 4724 — Return the normalized `configuration` subtree from the cached Mastodon instance document.
- `plugin_mastodon_instance_media_limit()` — line 4734 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_media_description_limit()` — line 4747 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_url_reserved_length()` — line 4760 — Return the reserved Mastodon character budget used for each URL.
- `plugin_mastodon_instance_registration_summary()` — line 7225 — Summarize the cached registration policy advertised by the instance for the admin diagnostics table.
- `plugin_mastodon_admin_instance_info_rows()` — line 7251 — Build the localized admin-table rows from the cached instance-information snapshot without forcing another live request.
- `plugin_mastodon_status_text_length()` — line 4774 — Calculate the Mastodon-visible status length with instance URL budgeting.
- `plugin_mastodon_limit_status_text()` — line 4806 — Truncate status text using Mastodon URL-budget rules.
- `plugin_mastodon_http_request_multipart()` — line 4884 — Perform a multipart HTTP request.
- `plugin_mastodon_fetch_media_attachment()` — line 5016 — Fetch a single Mastodon media attachment by ID.
- `plugin_mastodon_wait_for_media_attachment()` — line 5082 — Poll an asynchronously processed Mastodon media attachment until it is ready or times out.
- `plugin_mastodon_upload_media_items()` — line 5128 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_parse_http_response_headers()` — line 5422 — Parse raw HTTP response headers.
- `plugin_mastodon_stream_context_request()` — line 5452 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_status_media_attributes()` — line 4466 — Build the `media_attributes` array used for in-place status edits of already attached media.
- `plugin_mastodon_prepare_entry_media_sync_plan()` — line 5225 — Decide whether an entry should upload fresh media, reuse stored IDs, or reuse IDs plus `media_attributes`.
- `plugin_mastodon_array_is_list()` — line 5492 — Detect whether a PHP array is a zero-based list that should use `[]` form-field notation.
- `plugin_mastodon_array_contains_only_form_scalars()` — line 5512 — Detect whether a list can be serialized as repeated scalar `[]` fields.
- `plugin_mastodon_http_build_query()` — line 5530 — Build an application/x-www-form-urlencoded query string, emitting Rack-compatible Mastodon array fields such as `media_ids[]` and nested `media_attributes[][description]`.
- `plugin_mastodon_http_request()` — line 5585 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_mastodon_api()` — line 5702 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_json()` — line 5746 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_response_error_message()` — line 5761 — Extract the most useful error message from an API response.
- `plugin_mastodon_oauth_legacy_scopes()` — line 491 — Return the legacy OAuth scope string used by older registrations.
- `plugin_mastodon_oauth_profile_scopes()` — line 499 — Return the stricter scope string that uses `profile` for `verify_credentials`.
- `plugin_mastodon_oauth_server_metadata()` — line 508 — Discover OAuth server metadata from `/.well-known/oauth-authorization-server`.
- `plugin_mastodon_oauth_supported_scopes()` — line 527 — Extract the discoverable scope list from OAuth server metadata.
- `plugin_mastodon_oauth_scope_supported()` — line 564 — Check whether the configured Mastodon instance supports a given OAuth scope.
- `plugin_mastodon_oauth_preferred_scopes()` — line 585 — Prefer `profile` on current instances and fall back to `read:accounts` on older ones.
- `plugin_mastodon_register_app()` — line 5790 — Register the FlatPress application on the configured Mastodon instance with the preferred discoverable scope set.
- `plugin_mastodon_build_authorize_url()` — line 5813 — Build the OAuth authorization URL using the scopes that the registered app may safely request.
- `plugin_mastodon_exchange_code_for_token()` — line 5833 — Exchange an OAuth authorization code for an access token using the same negotiated scope string.
- `plugin_mastodon_verify_credentials()` — line 5863 — Verify the currently configured access token.
- `plugin_mastodon_instance_character_limit()` — line 5880 — Return the status character limit of the configured instance.
- `plugin_mastodon_fetch_account_statuses()` — line 5895 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_status_context()` — line 5942 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_fetch_status()` — line 5953 — Fetch a single Mastodon status.
- `plugin_mastodon_delete_status()` — line 5964 — Delete a Mastodon status, including media when requested.
- `plugin_mastodon_status_missing_response()` — line 5977 — Check whether an API response means that the referenced Mastodon status no longer exists.
- `plugin_mastodon_create_status()` — line 5990 — Create a Mastodon status.
- `plugin_mastodon_update_status()` — line 6015 — Update an existing Mastodon status.

## H. Import/export builders and synchronization orchestration

- `plugin_mastodon_build_entry_status_text()` — line 6039 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_comment_status_text()` — line 6107 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_import_remote_entry()` — line 6145 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_import_remote_comment()` — line 6396 — Import a remote Mastodon reply into FlatPress as a comment while respecting comment tombstones, including early tombstones for locally deleted exported comments.
- `plugin_mastodon_import_remote_context_descendants()` — line 6480 — Import remote Mastodon replies from a fetched thread context while blocking tombstoned parent/child replies.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 6583 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
- `plugin_mastodon_sync_remote_to_local()` — line 6617 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_sync_local_to_remote()` — line 6676 — Synchronize local FlatPress content to Mastodon, including remote-sourced entry comment export, media-plan reuse of stored `media_ids`, and version-aware in-place alt-text updates.
- `plugin_mastodon_run_deletion_sync()` — line 6875 — Reconcile mapped deletions between FlatPress and Mastodon in a separate follow-up request.

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
10. `plugin_mastodon_get_options()`
11. `plugin_mastodon_enabled_plugin_state()`
12. `plugin_mastodon_extend_time_limit()`
13. `plugin_mastodon_instance_configuration()`
14. `plugin_mastodon_instance_url_reserved_length()` / `plugin_mastodon_status_text_length()` / `plugin_mastodon_limit_status_text()`
15. `plugin_mastodon_http_request()`
16. `plugin_mastodon_collect_local_entry_media()`
17. `plugin_mastodon_upload_media_items()` / `plugin_mastodon_wait_for_media_attachment()`

## Current feature areas reflected in the function set

The current plugin version includes dedicated function groups for:

- Mastodon identity metadata in the HTML head
- runtime cache and optional APCu-backed shared cache
- reuse of the early FlatPress configuration (`EARLY_FP_CONFIG`) with APCu/runtime fallbacks
- centralized FlatPress plugin-state detection for companion-plugin diagnostics
- FlatPress-native file I/O wrappers for plugin state, logs, and downloaded media
- encrypted storage of sensitive configuration values
- sync start date filtering
- status language export derived from the FlatPress locale configuration
- optional remote overwrite of existing local content
- optional import of already synchronized comments as entries
- companion-plugin status reporting for BBCode, PhotoSwipe, Tag, and Emoticons
- tag synchronization through the FlatPress Tag plugin BBCode
- emoji conversion between FlatPress-style shortcodes and Mastodon-style Unicode
- bidirectional media synchronization for entry images and galleries
- initial alt-text upload through `/api/v2/media` plus cached remote media descriptors for later edits
- reuse of stored Mastodon `media_ids` for text-only entry updates without redundant media re-upload
- Mastodon-version-aware in-place alt-text updates through status `media_attributes` on Mastodon 4.1+
- Mastodon instance-aware URL length budgeting for exported statuses
- bounded PHP execution-budget refresh for long-running shared FlatPress requests
- asynchronous media-upload readiness polling for `/api/v2/media`
- deferred deletion synchronization in a follow-up web request
- old-thread reply refresh during remote import
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
- `main()` — line 7367 — Keep the admin panel lifecycle compatible with FlatPress without extra processing.
- `onsubmit()` — line 7371 — Process configuration saves, OAuth actions, including app registration and authorization-code exchange, and the manual synchronization trigger.
- `plugin_mastodon_absolute_url()` — line 2730 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_admin_assign()` — line 7323 — Assign plugin data to Smarty for the admin panel.
- `plugin_mastodon_apcu_cache_key()` — line 829 — Build the namespaced APCu key used by this plugin.
- `plugin_mastodon_apcu_delete()` — line 871 — Delete a value from APCu using the FlatPress namespace key builder.
- `plugin_mastodon_apcu_enabled()` — line 820 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_fetch()` — line 843 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 858 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_bbcode_attr_escape()` — line 4056 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_bbcode_plugin_active()` — line 2884 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_blog_base_url()` — line 2677 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_build_authorize_url()` — line 5813 — Build the OAuth authorization URL using the scopes that the registered app may safely request.
- `plugin_mastodon_build_comment_status_text()` — line 6107 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_build_entry_status_text()` — line 6039 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 3158 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_build_imported_media_bbcode()` — line 4511 — Build FlatPress BBCode for imported remote media attachments.
- `plugin_mastodon_cleanup_imported_text()` — line 3414 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_cleanup_uploaded_media()` — line 5045 — Best-effort cleanup for uploaded Mastodon media that never reached a final status request.
- `plugin_mastodon_collect_entry_files()` — line 5314 — Collect entry files recursively from the FlatPress content tree.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 6583 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
- `plugin_mastodon_collect_local_entry_media()` — line 4135 — Collect local images referenced by an entry or gallery tag.
- `plugin_mastodon_comment_hash()` — line 3916 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_comment_parent_fields()` — line 2487 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_companion_plugins_status()` — line 2933 — Return the status of companion FlatPress plugins used for the full Mastodon feature set.
- `plugin_mastodon_compare_local_entries_for_export()` — line 5367 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_configured_status_language()` — line 1324 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_create_status()` — line 5990 — Create a Mastodon status.
- `plugin_mastodon_date_matches_sync_start()` — line 1559 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_datetime_date_key()` — line 1535 — Normalize a stored date/datetime string to the sync-start date-key format.
- `plugin_mastodon_default_content_stats()` — line 437 — Return the default counters for the last content synchronization.
- `plugin_mastodon_default_deletion_stats()` — line 454 — Return the default counters for the last deletion synchronization.
- `plugin_mastodon_default_options()` — line 56 — Return the default plugin option values.
- `plugin_mastodon_default_state()` — line 467 — Return the default runtime state structure, including the targeted deletion-follow-up scope marker.
- `plugin_mastodon_delete_media_attachment()` — line 5026 — Delete an uploaded Mastodon media attachment before it is attached to a final status.
- `plugin_mastodon_delete_status()` — line 5964 — Delete a Mastodon status, including media when requested.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 2513 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_dom_children_to_flatpress()` — line 3468 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 3486 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_domains_match()` — line 3400 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 3171 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 3184 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_emoticons_plugin_active()` — line 2918 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_enabled_plugin_state()` — line 2802 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_ensure_state_dir()` — line 1639 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_entry_hash()` — line 3904 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_entry_media_signature()` — line 4324 — Build a signature for media references contained in entry content.
- `plugin_mastodon_exchange_code_for_token()` — line 5833 — Exchange an OAuth authorization code for an access token using the same negotiated scope string.
- `plugin_mastodon_extend_time_limit()` — line 4614 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_extract_flatpress_tags()` — line 3005 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_extract_url_token()` — line 2713 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_fediverse_creator_value()` — line 1244 — Build the fediverse creator meta value.
- `plugin_mastodon_fetch_account_statuses()` — line 5895 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_media_attachment()` — line 5016 — Fetch a single Mastodon media attachment by ID.
- `plugin_mastodon_fetch_status()` — line 5953 — Fetch a single Mastodon status.
- `plugin_mastodon_fetch_status_context()` — line 5942 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_file_prestat()` — line 884 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 904 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_flatpress_to_mastodon()` — line 3767 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_fp_config()` — line 679 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 715 — Read a nested FlatPress configuration value.
- `plugin_mastodon_fp_timeoffset_seconds()` — line 731 — Return the configured FlatPress time offset in seconds for exact local admin-time conversion.
- `plugin_mastodon_fp_timeoffset_label()` — line 751 — Format the configured FlatPress time offset as a UTC label for admin users.
- `plugin_mastodon_sync_time_to_minutes()` — line 781 — Convert a normalized `HH:MM` sync-time value into minutes after midnight.
- `plugin_mastodon_minutes_to_sync_time()` — line 792 — Convert minutes after midnight back into a normalized `HH:MM` sync-time value.
- `plugin_mastodon_sync_time_utc_to_local()` — line 808 — Convert the stored UTC synchronization time to the FlatPress-local admin time.
- `plugin_mastodon_sync_time_local_to_utc()` — line 819 — Convert the FlatPress-local admin synchronization time back to stored UTC.
- `plugin_mastodon_format_admin_datetime()` — line 830 — Format stored UTC timestamps for the admin panel using FlatPress `timeoffset`, date format, and time format.
- `plugin_mastodon_get_options()` — line 915 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_guess_subject()` — line 2626 — Guess a subject line from imported plain text.
- `plugin_mastodon_head()` — line 1262 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_html_entity_decode()` — line 2668 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_http_build_query()` — line 5530 — Build an application/x-www-form-urlencoded query string.
- `plugin_mastodon_http_request()` — line 5585 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_http_request_multipart()` — line 4884 — Perform a multipart HTTP request.
- `plugin_mastodon_import_remote_comment()` — line 6396 — Import a remote Mastodon reply into FlatPress as a comment while respecting comment tombstones, including early tombstones for locally deleted exported comments.
- `plugin_mastodon_import_remote_context_descendants()` — line 6480 — Import remote Mastodon replies from a fetched thread context while blocking tombstoned parent/child replies.
- `plugin_mastodon_import_remote_entry()` — line 6145 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_instance_authority()` — line 1198 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_instance_character_limit()` — line 5880 — Return the status character limit of the configured instance.
- `plugin_mastodon_instance_configuration()` — line 4724 — Load and cache the Mastodon instance configuration document.
- `plugin_mastodon_instance_media_description_limit()` — line 4747 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_media_limit()` — line 4734 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_url_reserved_length()` — line 4760 — Return the reserved Mastodon character budget used for each URL.
- `plugin_mastodon_io_append_file()` — line 804 — Append to a file via the FlatPress I/O layer.
- `plugin_mastodon_io_read_file()` — line 761 — Read a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_read_file_uncached()` — line 774 — Read a file without FlatPress request-local caches.
- `plugin_mastodon_io_write_file()` — line 791 — Write a file through the FlatPress I/O layer when available.
- `plugin_mastodon_is_public_host()` — line 3288 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_lang_string()` — line 2772 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_limit_status_text()` — line 4806 — Truncate status text using Mastodon URL-budget rules.
- `plugin_mastodon_limit_text()` — line 3882 — Limit text to a maximum number of characters.
- `plugin_mastodon_list_local_entries()` — line 5385 — List local FlatPress entry identifiers.
- `plugin_mastodon_local_item_date_key()` — line 1489 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_local_item_matches_sync_start()` — line 1578 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_local_item_timestamp()` — line 5341 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_log()` — line 1648 — Append a line to the plugin sync log.
- `plugin_mastodon_mapping_matches_sync_start()` — line 1599 — Determine whether a stored synchronization mapping still belongs to the active sync-start window.
- `plugin_mastodon_mastodon_api()` — line 5702 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 3047 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 3665 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_mastodon_json()` — line 5746 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_maybe_sync()` — line 7161 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_media_copy_tree()` — line 4018 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_media_delete_tree()` — line 3991 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_download()` — line 4499 — Download a remote media asset.
- `plugin_mastodon_media_guess_mime_type()` — line 4068 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_parse_tag_attributes()` — line 4104 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_media_prepare_directory()` — line 3975 — Ensure that a media directory exists.
- `plugin_mastodon_media_relative_to_absolute()` — line 3962 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_normalize_comment_parent_id()` — line 2496 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_normalize_delete_sync_enabled()` — line 1457 — Normalize the toggle that enables or disables the follow-up deletion synchronization.
- `plugin_mastodon_normalize_head_username()` — line 1159 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 1421 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_normalize_instance_url()` — line 1126 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_status_language()` — line 1302 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_normalize_sync_start_date()` — line 1361 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_sync_time()` — line 1344 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_tag_list()` — line 2974 — Normalize a list of tag labels.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 1403 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_oauth_legacy_scopes()` — line 491 — Return the legacy OAuth scope string used before scope discovery was added.
- `plugin_mastodon_oauth_preferred_scopes()` — line 585 — Prefer the narrow `profile` scope on current instances and fall back to `read:accounts` on older ones.
- `plugin_mastodon_oauth_profile_scopes()` — line 499 — Return the stricter OAuth scope string that uses `profile` for `verify_credentials`.
- `plugin_mastodon_oauth_scope_supported()` — line 564 — Check whether the configured Mastodon instance advertises support for a specific OAuth scope.
- `plugin_mastodon_oauth_scopes()` — line 602 — Return the OAuth scopes that the currently registered app may safely request.
- `plugin_mastodon_oauth_server_metadata()` — line 508 — Discover and cache OAuth authorization-server metadata from `/.well-known/oauth-authorization-server`.
- `plugin_mastodon_oauth_supported_scopes()` — line 527 — Parse the discoverable OAuth scopes supported by the configured Mastodon instance.
- `plugin_mastodon_parse_http_response_headers()` — line 5422 — Parse raw HTTP response headers.
- `plugin_mastodon_parse_iso_datetime()` — line 2403 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 2421 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_photoswipe_plugin_active()` — line 2901 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_plain_text_from_bbcode()` — line 3330 — Convert FlatPress BBCode into plain text for Mastodon export.
- `plugin_mastodon_profile_url()` — line 1228 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_public_comment_url()` — line 3650 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_public_comments_url()` — line 3622 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_entry_url()` — line 3595 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_url_for_mastodon()` — line 3311 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_register_app()` — line 5790 — Register the FlatPress application on the configured Mastodon instance with the preferred discoverable scope set.
- `plugin_mastodon_remote_media_description()` — line 4378 — Resolve the best description for a remote attachment.
- `plugin_mastodon_remote_media_source_url()` — line 4364 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_status_date_key()` — line 1509 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_remote_status_image_attachments()` — line 4341 — Extract image attachments from a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 2475 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 1588 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_remote_status_tags()` — line 3068 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_remote_status_timestamp()` — line 2443 — Resolve the best FlatPress-adjusted timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 2462 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 3238 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 3266 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_resolve_comment_reply_target()` — line 2535 — Resolve the remote reply target for a local comment export.
- `plugin_mastodon_list_local_comment_ids()` — line 2589 — Scan the FlatPress comment directory directly so local reply export is not blocked by stale comment-list caches.
- `plugin_mastodon_response_error_message()` — line 5761 — Extract the most useful error message from an API response.
- `plugin_mastodon_run_deletion_sync()` — line 6875 — Run the deferred deletion synchronization in a follow-up request after content sync completed.
- `plugin_mastodon_run_sync()` — line 7096 — Run a full synchronization cycle.
- `plugin_mastodon_runtime_cache_clear()` — line 664 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_runtime_cache_get()` — line 622 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 646 — Store a value in the request-local plugin cache.
- `plugin_mastodon_safe_filename()` — line 3949 — Sanitize a file name for local storage.
- `plugin_mastodon_safe_path_component()` — line 3934 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_save_options()` — line 970 — Persist plugin options.
- `plugin_mastodon_secret_decode()` — line 1095 — Decode a previously stored secret value.
- `plugin_mastodon_secret_encode()` — line 1072 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_key()` — line 1055 — Build the encryption key used for stored secrets.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 1430 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_should_run_deletion_sync()` — line 1466 — Check whether the follow-up deletion synchronization is enabled.
- `plugin_mastodon_should_update_local_from_remote()` — line 1412 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_state_comment_key()` — line 1781 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_get_comment_meta()` — line 2004 — Return mapping metadata for a local comment.
- `plugin_mastodon_state_set_comment_tombstone()` — line 2018 — Store a tombstone that blocks stale re-imports of one deleted remote comment.
- `plugin_mastodon_state_has_comment_tombstone()` — line 2038 — Check whether one remote Mastodon comment status was tombstoned locally.
- `plugin_mastodon_protect_locally_deleted_exported_comments()` — line 2050 — Tombstone locally deleted exported FlatPress comment mappings before the next content sync can stale-reimport them from Mastodon thread context.
- `plugin_mastodon_reattach_local_comment_to_entry_status()` — line 2096 — Remove a local imported reply parent link and reattach the surviving reply to the synchronized entry status after its remote parent reply disappeared.
- `plugin_mastodon_state_remove_pending_comment_remote_recheck()` — line 2156 — Remove one pending descendant recheck marker.
- `plugin_mastodon_state_get_pending_comment_remote_recheck()` — line 2170 — Return one pending descendant recheck marker.
- `plugin_mastodon_state_set_pending_comment_remote_recheck()` — line 2185 — Mark one local comment for follow-up verification after an ancestor disappeared remotely.
- `plugin_mastodon_state_set_deletions_pending()` — line 2210 — Persist whether another deletion follow-up request is pending and which scope it should run.
- `plugin_mastodon_state_has_comment_recheck_scope()` — line 2222 — Check whether the next deletion follow-up request should run only the targeted descendant recheck scope.
- `plugin_mastodon_build_comment_remote_child_index()` — line 2231 — Build a direct-child index for mapped remote reply trees.
- `plugin_mastodon_queue_comment_descendant_remote_rechecks()` — line 2259 — Queue only the direct mapped local children of one deleted remote comment for additional verification passes.
- `plugin_mastodon_process_pending_comment_remote_rechecks()` — line 2298 — Process pending descendant rechecks breadth-first so deeper reply chains can converge within the same targeted follow-up request.
- `plugin_mastodon_state_get_entry_meta()` — line 1909 — Return mapping metadata for a local entry.
- `plugin_mastodon_normalize_deletions_pending_scope()` — line 1721 — Normalize the targeted deletion-follow-up scope marker.
- `plugin_mastodon_state_normalize()` — line 1734 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_read()` — line 1658 — Load the persisted runtime state from disk.
- `plugin_mastodon_state_remove_comment_mapping()` — line 1888 — Remove the mapping between a local comment and a remote status.
- `plugin_mastodon_state_remove_entry_mapping()` — line 1869 — Remove the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_comment_mapping()` — line 1834 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_set_entry_mapping()` — line 1796 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_write()` — line 1697 — Persist the runtime state to disk.
- `plugin_mastodon_status_missing_response()` — line 5977 — Check whether an API response means that the referenced Mastodon status no longer exists.
- `plugin_mastodon_status_text_length()` — line 4774 — Calculate the Mastodon-visible status length with instance URL budgeting.
- `plugin_mastodon_stream_context_request()` — line 5452 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 3030 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 3095 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_subject_line_is_noise()` — line 3372 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_sync_due()` — line 7074 — Determine whether the scheduled synchronization is currently due.
- `plugin_mastodon_sync_local_to_remote()` — line 6676 — Synchronize local FlatPress content to Mastodon.
- `plugin_mastodon_sync_remote_to_local()` — line 6617 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_tag_plugin_active()` — line 2867 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_timestamp_date_key()` — line 1475 — Convert a FlatPress-adjusted timestamp into a stable date key.
- `plugin_mastodon_update_status()` — line 6015 — Update an existing Mastodon status.
- `plugin_mastodon_upload_media_items()` — line 5128 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_verify_credentials()` — line 5863 — Verify the currently configured access token.
- `plugin_mastodon_wait_for_media_attachment()` — line 5082 — Poll an asynchronously processed Mastodon media attachment until it is ready or times out.
- `setup()` — line 7362 — Register the Mastodon admin panel template and assign plugin data to Smarty.

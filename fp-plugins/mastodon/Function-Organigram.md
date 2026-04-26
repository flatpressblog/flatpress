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
- Mastodon instance-dependent URL budgeting, bounded PHP execution-budget refreshes, OAuth scope discovery with a strict `profile`-scope preference and an older-instance fallback to `read:accounts`, asynchronous media-upload readiness polling, best-effort cleanup of unattached uploaded Mastodon media before a failed final posting finishes, follow-up deletion synchronization, the admin toggle that can disable deletion synchronization, and the separate persistence of content-sync and deletion-sync counters are reflected explicitly.
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

The plugin file currently contains **210** callable functions/methods documented in this organigram:
- **207** top-level plugin functions
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
  Refreshes the shared-request PHP execution budget through `plugin_mastodon_extend_time_limit()` and then executes the two main directions in order:
  1. `plugin_mastodon_sync_remote_to_local()`
  2. `plugin_mastodon_sync_local_to_remote()`
  If both directions finish successfully, the function marks a follow-up deletion pass as pending for a later web request.

- `plugin_mastodon_run_deletion_sync()`  
  Runs the real deletion synchronization in a separate follow-up request. It first checks `plugin_mastodon_should_run_deletion_sync()` so the user-controlled admin toggle can disable the delete pass, resets only `deletion_stats` while preserving `content_stats` from the last content sync, including the dedicated `updated_local_comments` counter for Mastodon edits of existing FlatPress comments, then gates mapped items through `plugin_mastodon_mapping_matches_sync_start()` so the delete pass stays inside the configured sync-start window, and finally reconciles missing local items against Mastodon with `plugin_mastodon_delete_status()` and missing remote statuses against FlatPress with `plugin_mastodon_fetch_status()` and local delete helpers.

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
  - builds comment replies with `plugin_mastodon_build_comment_status_text()`
  - resolves reply targets with `plugin_mastodon_resolve_comment_reply_target()`

### Remote → local import path

- `plugin_mastodon_sync_remote_to_local()`
  - refreshes the request time budget for long-running import loops and Mastodon communication through `plugin_mastodon_extend_time_limit()`
  - validates credentials with `plugin_mastodon_verify_credentials()`
  - fetches account statuses with `plugin_mastodon_fetch_account_statuses()`
  - imports top-level statuses with `plugin_mastodon_import_remote_entry()`
  - fetches thread context with `plugin_mastodon_fetch_status_context()`
  - imports replies with `plugin_mastodon_import_remote_context_descendants()`
  - imports individual replies through `plugin_mastodon_import_remote_comment()`
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

- `plugin_mastodon_head()` — line 1195 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_maybe_sync()` — line 6321 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_run_sync()` — line 6261 — Run a full synchronization cycle.
- `plugin_mastodon_run_deletion_sync()` — line 6083 — Run the deferred deletion synchronization in a follow-up request after content sync completed.
- `plugin_mastodon_sync_due()` — line 6239 — Determine whether the scheduled synchronization is currently due.
- `plugin_mastodon_admin_assign()` — line 6484 — Assign plugin data to Smarty for the admin panel, including cached instance-information rows.
- `setup()` — line 6521 — Register the Mastodon admin panel template and assign plugin data to Smarty.
- `main()` — line 6526 — Keep the admin panel lifecycle compatible with FlatPress without extra processing.
- `onsubmit()` — line 6530 — Process configuration saves, OAuth actions, authorization-code exchange, manual instance-information refreshes, and the manual synchronization trigger.

## B. Defaults, configuration, secrets, and centralized FlatPress feature toggles

- `plugin_mastodon_default_options()` — line 53 — Return the default plugin option values.
- `plugin_mastodon_clear_saved_instance_info()` — line 82 — Remove persisted instance-information snapshots and related admin refresh errors from an option array.
- `plugin_mastodon_compact_instance_document()` — line 101 — Reduce `/api/v2/instance` responses to the stable subset reused by the plugin and admin table.
- `plugin_mastodon_saved_instance_document()` — line 306 — Decode the persisted compact instance snapshot from the saved plugin configuration.
- `plugin_mastodon_store_instance_document()` — line 351 — Persist a compact instance-information snapshot in the FlatPress plugin configuration and warm request/APCu caches.
- `plugin_mastodon_store_instance_error()` — line 397 — Persist the latest instance-information refresh failure for the admin diagnostics view.
- `plugin_mastodon_refresh_instance_information()` — line 413 — Force a live `/api/v2/instance` refresh, compact the response, and store it for later requests.
- `plugin_mastodon_default_content_stats()` — line 433 — Return the default counters for the last content synchronization.
- `plugin_mastodon_default_deletion_stats()` — line 450 — Return the default counters for the last deletion synchronization.
- `plugin_mastodon_default_state()` — line 463 — Return the default runtime state structure.
- `plugin_mastodon_oauth_legacy_scopes()` — line 484 — Return the legacy OAuth scope string used before scope discovery was added.
- `plugin_mastodon_oauth_profile_scopes()` — line 492 — Return the stricter OAuth scope string that uses `profile` for `verify_credentials`.
- `plugin_mastodon_oauth_server_metadata()` — line 501 — Discover and cache OAuth authorization-server metadata from `/.well-known/oauth-authorization-server`.
- `plugin_mastodon_oauth_supported_scopes()` — line 520 — Parse the discoverable OAuth scopes supported by the configured Mastodon instance.
- `plugin_mastodon_oauth_scope_supported()` — line 557 — Check whether the configured Mastodon instance advertises support for a specific OAuth scope.
- `plugin_mastodon_oauth_preferred_scopes()` — line 578 — Prefer the narrow `profile` scope on current instances and fall back to `read:accounts` on older ones.
- `plugin_mastodon_oauth_scopes()` — line 595 — Return the OAuth scopes that the currently registered app may safely request.
- `plugin_mastodon_fp_config()` — line 672 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 708 — Read a nested FlatPress configuration value.
- `plugin_mastodon_get_options()` — line 863 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_save_options()` — line 916 — Persist plugin options, invalidate mismatching instance snapshots on URL changes, and keep matching refreshed snapshots.
- `plugin_mastodon_secret_key()` — line 988 — Build the encryption key used for stored secrets.
- `plugin_mastodon_secret_encode()` — line 1005 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_decode()` — line 1028 — Decode a previously stored secret value.
- `plugin_mastodon_normalize_instance_url()` — line 1059 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_head_username()` — line 1092 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_instance_authority()` — line 1131 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_profile_url()` — line 1161 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_fediverse_creator_value()` — line 1177 — Build the fediverse creator meta value.
- `plugin_mastodon_normalize_status_language()` — line 1235 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_configured_status_language()` — line 1257 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_normalize_sync_time()` — line 1277 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_sync_start_date()` — line 1294 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 1320 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_should_update_local_from_remote()` — line 1336 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 1345 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 1361 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_normalize_delete_sync_enabled()` — line 1370 — Normalize the toggle that enables or disables the follow-up deletion synchronization.
- `plugin_mastodon_should_run_deletion_sync()` — line 1386 — Check whether the follow-up deletion synchronization is enabled.
- `plugin_mastodon_enabled_plugin_state()` — line 2247 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_tag_plugin_active()` — line 2312 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_bbcode_plugin_active()` — line 2329 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_photoswipe_plugin_active()` — line 2346 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_emoticons_plugin_active()` — line 2363 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_companion_plugins_status()` — line 2378 — Return the status of companion FlatPress plugins used for the full Mastodon feature set.

## C. Caching, filesystem helpers, logging, and persisted state

- `plugin_mastodon_runtime_cache_get()` — line 615 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 639 — Store a value in the request-local plugin cache.
- `plugin_mastodon_runtime_cache_clear()` — line 657 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_io_read_file()` — line 726 — Read a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_write_file()` — line 739 — Write a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_append_file()` — line 752 — Append to a file via the FlatPress I/O layer.
- `plugin_mastodon_apcu_enabled()` — line 768 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_cache_key()` — line 777 — Build the namespaced APCu key used by this plugin.
- `plugin_mastodon_apcu_fetch()` — line 791 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 806 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_delete()` — line 819 — Delete a value from APCu using the FlatPress namespace key builder.
- `plugin_mastodon_file_prestat()` — line 832 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 852 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_ensure_state_dir()` — line 1556 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_log()` — line 1565 — Append a line to the plugin sync log.
- `plugin_mastodon_state_read()` — line 1575 — Load the persisted runtime state from disk.
- `plugin_mastodon_state_write()` — line 1614 — Persist the runtime state to disk.
- `plugin_mastodon_state_normalize()` — line 1638 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_comment_key()` — line 1684 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_set_entry_mapping()` — line 1699 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_comment_mapping()` — line 1737 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_remove_entry_mapping()` — line 1772 — Remove the mapping between a local entry and a remote status.
- `plugin_mastodon_state_remove_comment_mapping()` — line 1791 — Remove the mapping between a local comment and a remote status.
- `plugin_mastodon_state_get_entry_meta()` — line 1812 — Return mapping metadata for a local entry.
- `plugin_mastodon_state_set_entry_media_meta()` — line 1826 — Persist cached remote media IDs plus local attachment/description signatures for a synchronized entry.
- `plugin_mastodon_state_get_comment_meta()` — line 1907 — Return mapping metadata for a local comment.

## D. Date, timestamp, visibility, and threading helpers

- `plugin_mastodon_timestamp_date_key()` — line 1395 — Convert a Unix timestamp into a stable UTC date key.
- `plugin_mastodon_local_item_date_key()` — line 1409 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_remote_status_date_key()` — line 1429 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_datetime_date_key()` — line 1455 — Normalize a stored date/datetime string to the sync-start date-key format.
- `plugin_mastodon_date_matches_sync_start()` — line 1476 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_local_item_matches_sync_start()` — line 1495 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 1505 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_mapping_matches_sync_start()` — line 1516 — Determine whether a stored synchronization mapping still belongs to the active sync-start window.
- `plugin_mastodon_parse_iso_datetime()` — line 1917 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 1935 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_remote_status_timestamp()` — line 1957 — Resolve the best timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 1976 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 1989 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_comment_parent_fields()` — line 2001 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_normalize_comment_parent_id()` — line 2010 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 2027 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_resolve_comment_reply_target()` — line 2049 — Resolve the remote reply target for a local comment export.

## E. Text, URLs, language strings, tags, emojis, and BBCode/HTML conversion

- `plugin_mastodon_guess_subject()` — line 2071 — Guess a subject line from imported plain text.
- `plugin_mastodon_html_entity_decode()` — line 2113 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_blog_base_url()` — line 2122 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_extract_url_token()` — line 2158 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_absolute_url()` — line 2175 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_lang_string()` — line 2217 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_normalize_tag_list()` — line 2419 — Normalize a list of tag labels.
- `plugin_mastodon_extend_time_limit()` — line 4042 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_extract_flatpress_tags()` — line 2450 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 2475 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 2492 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_remote_status_tags()` — line 2513 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 2540 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 2603 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 2616 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 2629 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 2683 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 2694 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_is_public_host()` — line 2716 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_public_url_for_mastodon()` — line 2739 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_plain_text_from_bbcode()` — line 2758 — Convert FlatPress BBCode into plain text for Mastodon export.
- `plugin_mastodon_subject_line_is_noise()` — line 2800 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_domains_match()` — line 2828 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_cleanup_imported_text()` — line 2842 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_dom_children_to_flatpress()` — line 2896 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 2914 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_public_entry_url()` — line 3023 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_comments_url()` — line 3050 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_comment_url()` — line 3078 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 3093 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_flatpress_to_mastodon()` — line 3195 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_limit_text()` — line 3310 — Limit text to a maximum number of characters.

## F. Local content access, media processing, hashing, and export ordering

- `plugin_mastodon_entry_hash()` — line 3332 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_comment_hash()` — line 3344 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_safe_path_component()` — line 3362 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_safe_filename()` — line 3377 — Sanitize a file name for local storage.
- `plugin_mastodon_media_relative_to_absolute()` — line 3390 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_media_prepare_directory()` — line 3403 — Ensure that a media directory exists.
- `plugin_mastodon_media_delete_tree()` — line 3419 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_copy_tree()` — line 3446 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_bbcode_attr_escape()` — line 3484 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_media_guess_mime_type()` — line 3496 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_parse_tag_attributes()` — line 3532 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_collect_local_entry_media()` — line 3563 — Collect local images referenced by an entry or gallery tag.
- `plugin_mastodon_prepare_entry_media_items()` — line 3687 — Normalize collected local media items into reusable path/description tuples.
- `plugin_mastodon_entry_media_attachment_signature_from_items()` — line 3710 — Hash only the attachment identity of normalized media items.
- `plugin_mastodon_entry_media_description_signature_from_items()` — line 3733 — Hash only the alt-text portion of normalized media items.
- `plugin_mastodon_entry_media_signature()` — line 3752 — Build a combined attachment+description signature for media references contained in entry content.
- `plugin_mastodon_remote_status_image_attachments()` — line 3769 — Extract image attachments from a remote Mastodon status.
- `plugin_mastodon_remote_media_source_url()` — line 3792 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_media_description()` — line 3806 — Resolve the best description for a remote attachment.
- `plugin_mastodon_remote_media_focus()` — line 3820 — Normalize a Mastodon media focus string when present.
- `plugin_mastodon_remote_media_descriptors_from_status()` — line 3837 — Extract reusable media descriptors (`id`, `description`, `focus`) from a Mastodon status payload.
- `plugin_mastodon_remote_media_descriptors_from_media_ids()` — line 3864 — Build fallback reusable media descriptors from already-known IDs and local media items.
- `plugin_mastodon_media_download()` — line 3927 — Download a remote media asset.
- `plugin_mastodon_build_imported_media_bbcode()` — line 3939 — Build FlatPress BBCode for imported remote media attachments.
- `plugin_mastodon_collect_entry_files()` — line 4742 — Collect entry files recursively from the FlatPress content tree.
- `plugin_mastodon_local_item_timestamp()` — line 4769 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_compare_local_entries_for_export()` — line 4795 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_list_local_entries()` — line 4813 — List local FlatPress entry identifiers.

## G. HTTP transport, PHP timeout budgeting, instance capability lookup, status-length budgeting, OAuth, Mastodon API calls, and media upload

- `plugin_mastodon_extend_time_limit()` — line 4042 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_instance_document()` — line 4073 — Load and cache the compact Mastodon instance document, preferring the saved FlatPress snapshot before APCu and live network fetches.
- `plugin_mastodon_instance_version()` — line 4117 — Extract the human-readable Mastodon server version from the cached instance document.
- `plugin_mastodon_instance_supports_status_media_attributes()` — line 4135 — Decide whether `PUT /api/v1/statuses/:id` may safely use `media_attributes` for in-place alt-text edits.
- `plugin_mastodon_instance_configuration()` — line 4152 — Return the normalized `configuration` subtree from the cached Mastodon instance document.
- `plugin_mastodon_instance_media_limit()` — line 4162 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_media_description_limit()` — line 4175 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_url_reserved_length()` — line 4188 — Return the reserved Mastodon character budget used for each URL.
- `plugin_mastodon_instance_registration_summary()` — line 6386 — Summarize the cached registration policy advertised by the instance for the admin diagnostics table.
- `plugin_mastodon_admin_instance_info_rows()` — line 6412 — Build the localized admin-table rows from the cached instance-information snapshot without forcing another live request.
- `plugin_mastodon_status_text_length()` — line 4202 — Calculate the Mastodon-visible status length with instance URL budgeting.
- `plugin_mastodon_limit_status_text()` — line 4234 — Truncate status text using Mastodon URL-budget rules.
- `plugin_mastodon_http_request_multipart()` — line 4312 — Perform a multipart HTTP request.
- `plugin_mastodon_fetch_media_attachment()` — line 4444 — Fetch a single Mastodon media attachment by ID.
- `plugin_mastodon_wait_for_media_attachment()` — line 4510 — Poll an asynchronously processed Mastodon media attachment until it is ready or times out.
- `plugin_mastodon_upload_media_items()` — line 4556 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_parse_http_response_headers()` — line 4850 — Parse raw HTTP response headers.
- `plugin_mastodon_stream_context_request()` — line 4880 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_status_media_attributes()` — line 3894 — Build the `media_attributes` array used for in-place status edits of already attached media.
- `plugin_mastodon_prepare_entry_media_sync_plan()` — line 4653 — Decide whether an entry should upload fresh media, reuse stored IDs, or reuse IDs plus `media_attributes`.
- `plugin_mastodon_array_is_list()` — line 4920 — Detect whether a PHP array is a zero-based list that should use `[]` form-field notation.
- `plugin_mastodon_array_contains_only_form_scalars()` — line 4940 — Detect whether a list can be serialized as repeated scalar `[]` fields.
- `plugin_mastodon_http_build_query()` — line 4958 — Build an application/x-www-form-urlencoded query string, emitting Rack-compatible Mastodon array fields such as `media_ids[]` and nested `media_attributes[][description]`.
- `plugin_mastodon_http_request()` — line 5013 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_mastodon_api()` — line 5130 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_json()` — line 5174 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_response_error_message()` — line 5189 — Extract the most useful error message from an API response.
- `plugin_mastodon_oauth_legacy_scopes()` — line 484 — Return the legacy OAuth scope string used by older registrations.
- `plugin_mastodon_oauth_profile_scopes()` — line 492 — Return the stricter scope string that uses `profile` for `verify_credentials`.
- `plugin_mastodon_oauth_server_metadata()` — line 501 — Discover OAuth server metadata from `/.well-known/oauth-authorization-server`.
- `plugin_mastodon_oauth_supported_scopes()` — line 520 — Extract the discoverable scope list from OAuth server metadata.
- `plugin_mastodon_oauth_scope_supported()` — line 557 — Check whether the configured Mastodon instance supports a given OAuth scope.
- `plugin_mastodon_oauth_preferred_scopes()` — line 578 — Prefer `profile` on current instances and fall back to `read:accounts` on older ones.
- `plugin_mastodon_register_app()` — line 5218 — Register the FlatPress application on the configured Mastodon instance with the preferred discoverable scope set.
- `plugin_mastodon_build_authorize_url()` — line 5241 — Build the OAuth authorization URL using the scopes that the registered app may safely request.
- `plugin_mastodon_exchange_code_for_token()` — line 5261 — Exchange an OAuth authorization code for an access token using the same negotiated scope string.
- `plugin_mastodon_verify_credentials()` — line 5291 — Verify the currently configured access token.
- `plugin_mastodon_instance_character_limit()` — line 5308 — Return the status character limit of the configured instance.
- `plugin_mastodon_fetch_account_statuses()` — line 5323 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_status_context()` — line 5370 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_fetch_status()` — line 5381 — Fetch a single Mastodon status.
- `plugin_mastodon_delete_status()` — line 5392 — Delete a Mastodon status, including media when requested.
- `plugin_mastodon_status_missing_response()` — line 5405 — Check whether an API response means that the referenced Mastodon status no longer exists.
- `plugin_mastodon_create_status()` — line 5418 — Create a Mastodon status.
- `plugin_mastodon_update_status()` — line 5443 — Update an existing Mastodon status.

## H. Import/export builders and synchronization orchestration

- `plugin_mastodon_build_entry_status_text()` — line 5467 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_comment_status_text()` — line 5535 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_import_remote_entry()` — line 5573 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_import_remote_comment()` — line 5673 — Import a remote Mastodon reply into FlatPress as a comment.
- `plugin_mastodon_import_remote_context_descendants()` — line 5745 — Import remote Mastodon replies from a fetched thread context.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 5830 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
- `plugin_mastodon_sync_remote_to_local()` — line 5864 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_sync_local_to_remote()` — line 5923 — Synchronize local FlatPress content to Mastodon, including media-plan reuse of stored `media_ids` and version-aware in-place alt-text updates.
- `plugin_mastodon_run_deletion_sync()` — line 6083 — Reconcile mapped deletions between FlatPress and Mastodon in a separate follow-up request.

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
- `main()` — line 6526 — Keep the admin panel lifecycle compatible with FlatPress without extra processing.
- `onsubmit()` — line 6530 — Process configuration saves, OAuth actions, including app registration and authorization-code exchange, and the manual synchronization trigger.
- `plugin_mastodon_absolute_url()` — line 2175 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_admin_assign()` — line 6484 — Assign plugin data to Smarty for the admin panel.
- `plugin_mastodon_apcu_cache_key()` — line 777 — Build the namespaced APCu key used by this plugin.
- `plugin_mastodon_apcu_delete()` — line 819 — Delete a value from APCu using the FlatPress namespace key builder.
- `plugin_mastodon_apcu_enabled()` — line 768 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_fetch()` — line 791 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 806 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_bbcode_attr_escape()` — line 3484 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_bbcode_plugin_active()` — line 2329 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_blog_base_url()` — line 2122 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_build_authorize_url()` — line 5241 — Build the OAuth authorization URL using the scopes that the registered app may safely request.
- `plugin_mastodon_build_comment_status_text()` — line 5535 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_build_entry_status_text()` — line 5467 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 2603 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_build_imported_media_bbcode()` — line 3939 — Build FlatPress BBCode for imported remote media attachments.
- `plugin_mastodon_cleanup_imported_text()` — line 2842 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_cleanup_uploaded_media()` — line 4473 — Best-effort cleanup for uploaded Mastodon media that never reached a final status request.
- `plugin_mastodon_collect_entry_files()` — line 4742 — Collect entry files recursively from the FlatPress content tree.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 5830 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
- `plugin_mastodon_collect_local_entry_media()` — line 3563 — Collect local images referenced by an entry or gallery tag.
- `plugin_mastodon_comment_hash()` — line 3344 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_comment_parent_fields()` — line 2001 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_companion_plugins_status()` — line 2378 — Return the status of companion FlatPress plugins used for the full Mastodon feature set.
- `plugin_mastodon_compare_local_entries_for_export()` — line 4795 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_configured_status_language()` — line 1257 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_create_status()` — line 5418 — Create a Mastodon status.
- `plugin_mastodon_date_matches_sync_start()` — line 1476 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_datetime_date_key()` — line 1455 — Normalize a stored date/datetime string to the sync-start date-key format.
- `plugin_mastodon_default_content_stats()` — line 433 — Return the default counters for the last content synchronization.
- `plugin_mastodon_default_deletion_stats()` — line 450 — Return the default counters for the last deletion synchronization.
- `plugin_mastodon_default_options()` — line 53 — Return the default plugin option values.
- `plugin_mastodon_default_state()` — line 463 — Return the default runtime state structure.
- `plugin_mastodon_delete_media_attachment()` — line 4454 — Delete an uploaded Mastodon media attachment before it is attached to a final status.
- `plugin_mastodon_delete_status()` — line 5392 — Delete a Mastodon status, including media when requested.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 2027 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_dom_children_to_flatpress()` — line 2896 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 2914 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_domains_match()` — line 2828 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 2616 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 2629 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_emoticons_plugin_active()` — line 2363 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_enabled_plugin_state()` — line 2247 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_ensure_state_dir()` — line 1556 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_entry_hash()` — line 3332 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_entry_media_signature()` — line 3752 — Build a signature for media references contained in entry content.
- `plugin_mastodon_exchange_code_for_token()` — line 5261 — Exchange an OAuth authorization code for an access token using the same negotiated scope string.
- `plugin_mastodon_extend_time_limit()` — line 4042 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_extract_flatpress_tags()` — line 2450 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_extract_url_token()` — line 2158 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_fediverse_creator_value()` — line 1177 — Build the fediverse creator meta value.
- `plugin_mastodon_fetch_account_statuses()` — line 5323 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_media_attachment()` — line 4444 — Fetch a single Mastodon media attachment by ID.
- `plugin_mastodon_fetch_status()` — line 5381 — Fetch a single Mastodon status.
- `plugin_mastodon_fetch_status_context()` — line 5370 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_file_prestat()` — line 832 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 852 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_flatpress_to_mastodon()` — line 3195 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_fp_config()` — line 672 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 708 — Read a nested FlatPress configuration value.
- `plugin_mastodon_get_options()` — line 863 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_guess_subject()` — line 2071 — Guess a subject line from imported plain text.
- `plugin_mastodon_head()` — line 1195 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_html_entity_decode()` — line 2113 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_http_build_query()` — line 4958 — Build an application/x-www-form-urlencoded query string.
- `plugin_mastodon_http_request()` — line 5013 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_http_request_multipart()` — line 4312 — Perform a multipart HTTP request.
- `plugin_mastodon_import_remote_comment()` — line 5673 — Import a remote Mastodon reply into FlatPress as a comment.
- `plugin_mastodon_import_remote_context_descendants()` — line 5745 — Import remote Mastodon replies from a fetched thread context.
- `plugin_mastodon_import_remote_entry()` — line 5573 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_instance_authority()` — line 1131 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_instance_character_limit()` — line 5308 — Return the status character limit of the configured instance.
- `plugin_mastodon_instance_configuration()` — line 4152 — Load and cache the Mastodon instance configuration document.
- `plugin_mastodon_instance_media_description_limit()` — line 4175 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_media_limit()` — line 4162 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_url_reserved_length()` — line 4188 — Return the reserved Mastodon character budget used for each URL.
- `plugin_mastodon_io_append_file()` — line 752 — Append to a file via the FlatPress I/O layer.
- `plugin_mastodon_io_read_file()` — line 726 — Read a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_write_file()` — line 739 — Write a file through the FlatPress I/O layer when available.
- `plugin_mastodon_is_public_host()` — line 2716 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_lang_string()` — line 2217 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_limit_status_text()` — line 4234 — Truncate status text using Mastodon URL-budget rules.
- `plugin_mastodon_limit_text()` — line 3310 — Limit text to a maximum number of characters.
- `plugin_mastodon_list_local_entries()` — line 4813 — List local FlatPress entry identifiers.
- `plugin_mastodon_local_item_date_key()` — line 1409 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_local_item_matches_sync_start()` — line 1495 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_local_item_timestamp()` — line 4769 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_log()` — line 1565 — Append a line to the plugin sync log.
- `plugin_mastodon_mapping_matches_sync_start()` — line 1516 — Determine whether a stored synchronization mapping still belongs to the active sync-start window.
- `plugin_mastodon_mastodon_api()` — line 5130 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 2492 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 3093 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_mastodon_json()` — line 5174 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_maybe_sync()` — line 6321 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_media_copy_tree()` — line 3446 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_media_delete_tree()` — line 3419 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_download()` — line 3927 — Download a remote media asset.
- `plugin_mastodon_media_guess_mime_type()` — line 3496 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_parse_tag_attributes()` — line 3532 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_media_prepare_directory()` — line 3403 — Ensure that a media directory exists.
- `plugin_mastodon_media_relative_to_absolute()` — line 3390 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_normalize_comment_parent_id()` — line 2010 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_normalize_delete_sync_enabled()` — line 1370 — Normalize the toggle that enables or disables the follow-up deletion synchronization.
- `plugin_mastodon_normalize_head_username()` — line 1092 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 1345 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_normalize_instance_url()` — line 1059 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_status_language()` — line 1235 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_normalize_sync_start_date()` — line 1294 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_sync_time()` — line 1277 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_tag_list()` — line 2419 — Normalize a list of tag labels.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 1320 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_oauth_legacy_scopes()` — line 484 — Return the legacy OAuth scope string used before scope discovery was added.
- `plugin_mastodon_oauth_preferred_scopes()` — line 578 — Prefer the narrow `profile` scope on current instances and fall back to `read:accounts` on older ones.
- `plugin_mastodon_oauth_profile_scopes()` — line 492 — Return the stricter OAuth scope string that uses `profile` for `verify_credentials`.
- `plugin_mastodon_oauth_scope_supported()` — line 557 — Check whether the configured Mastodon instance advertises support for a specific OAuth scope.
- `plugin_mastodon_oauth_scopes()` — line 595 — Return the OAuth scopes that the currently registered app may safely request.
- `plugin_mastodon_oauth_server_metadata()` — line 501 — Discover and cache OAuth authorization-server metadata from `/.well-known/oauth-authorization-server`.
- `plugin_mastodon_oauth_supported_scopes()` — line 520 — Parse the discoverable OAuth scopes supported by the configured Mastodon instance.
- `plugin_mastodon_parse_http_response_headers()` — line 4850 — Parse raw HTTP response headers.
- `plugin_mastodon_parse_iso_datetime()` — line 1917 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 1935 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_photoswipe_plugin_active()` — line 2346 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_plain_text_from_bbcode()` — line 2758 — Convert FlatPress BBCode into plain text for Mastodon export.
- `plugin_mastodon_profile_url()` — line 1161 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_public_comment_url()` — line 3078 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_public_comments_url()` — line 3050 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_entry_url()` — line 3023 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_url_for_mastodon()` — line 2739 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_register_app()` — line 5218 — Register the FlatPress application on the configured Mastodon instance with the preferred discoverable scope set.
- `plugin_mastodon_remote_media_description()` — line 3806 — Resolve the best description for a remote attachment.
- `plugin_mastodon_remote_media_source_url()` — line 3792 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_status_date_key()` — line 1429 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_remote_status_image_attachments()` — line 3769 — Extract image attachments from a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 1989 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 1505 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_remote_status_tags()` — line 2513 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_remote_status_timestamp()` — line 1957 — Resolve the best timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 1976 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 2683 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 2694 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_resolve_comment_reply_target()` — line 2049 — Resolve the remote reply target for a local comment export.
- `plugin_mastodon_response_error_message()` — line 5189 — Extract the most useful error message from an API response.
- `plugin_mastodon_run_deletion_sync()` — line 6083 — Run the deferred deletion synchronization in a follow-up request after content sync completed.
- `plugin_mastodon_run_sync()` — line 6261 — Run a full synchronization cycle.
- `plugin_mastodon_runtime_cache_clear()` — line 657 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_runtime_cache_get()` — line 615 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 639 — Store a value in the request-local plugin cache.
- `plugin_mastodon_safe_filename()` — line 3377 — Sanitize a file name for local storage.
- `plugin_mastodon_safe_path_component()` — line 3362 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_save_options()` — line 916 — Persist plugin options.
- `plugin_mastodon_secret_decode()` — line 1028 — Decode a previously stored secret value.
- `plugin_mastodon_secret_encode()` — line 1005 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_key()` — line 988 — Build the encryption key used for stored secrets.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 1361 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_should_run_deletion_sync()` — line 1386 — Check whether the follow-up deletion synchronization is enabled.
- `plugin_mastodon_should_update_local_from_remote()` — line 1336 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_state_comment_key()` — line 1684 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_get_comment_meta()` — line 1907 — Return mapping metadata for a local comment.
- `plugin_mastodon_state_get_entry_meta()` — line 1812 — Return mapping metadata for a local entry.
- `plugin_mastodon_state_normalize()` — line 1638 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_read()` — line 1575 — Load the persisted runtime state from disk.
- `plugin_mastodon_state_remove_comment_mapping()` — line 1791 — Remove the mapping between a local comment and a remote status.
- `plugin_mastodon_state_remove_entry_mapping()` — line 1772 — Remove the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_comment_mapping()` — line 1737 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_set_entry_mapping()` — line 1699 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_write()` — line 1614 — Persist the runtime state to disk.
- `plugin_mastodon_status_missing_response()` — line 5405 — Check whether an API response means that the referenced Mastodon status no longer exists.
- `plugin_mastodon_status_text_length()` — line 4202 — Calculate the Mastodon-visible status length with instance URL budgeting.
- `plugin_mastodon_stream_context_request()` — line 4880 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 2475 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 2540 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_subject_line_is_noise()` — line 2800 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_sync_due()` — line 6239 — Determine whether the scheduled synchronization is currently due.
- `plugin_mastodon_sync_local_to_remote()` — line 5923 — Synchronize local FlatPress content to Mastodon.
- `plugin_mastodon_sync_remote_to_local()` — line 5864 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_tag_plugin_active()` — line 2312 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_timestamp_date_key()` — line 1395 — Convert a Unix timestamp into a stable UTC date key.
- `plugin_mastodon_update_status()` — line 5443 — Update an existing Mastodon status.
- `plugin_mastodon_upload_media_items()` — line 4556 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_verify_credentials()` — line 5291 — Verify the currently configured access token.
- `plugin_mastodon_wait_for_media_attachment()` — line 4510 — Poll an asynchronously processed Mastodon media attachment until it is ready or times out.
- `setup()` — line 6521 — Register the Mastodon admin panel template and assign plugin data to Smarty.

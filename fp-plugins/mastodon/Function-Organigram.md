# Mastodon Plugin – PHP Function Organigram

## Purpose of this document

This document gives developers a structured overview of the Mastodon plugin PHP functions.

The layout is intentionally hierarchical so responsibilities, call paths, and important helper functions can be identified quickly.

## Scope

- The focus is the Mastodon plugin PHP code.
- The document is organized by responsibility and call path.
- Recently added FlatPress configuration reuse, centralized plugin-state detection, and FlatPress I/O helpers are reflected explicitly.
- The companion-plugin diagnostics for BBCode, PhotoSwipe, Tag, and Emoticons are covered.
- Mastodon instance-dependent URL budgeting, bounded PHP execution-budget refreshes, asynchronous media-upload readiness polling, follow-up deletion synchronization, the admin toggle that can disable deletion synchronization, and the separate persistence of content-sync and deletion-sync counters are reflected explicitly.
- All plugin functions and admin panel methods currently present in the file are covered.
- Function summaries are derived from the current source file PHPDoc and verified against the function names and their location in the file.
- The terms **Entry** and **Comment** follow the wording used by the source code.

## Function count

The plugin file currently contains **175** callable functions/methods documented in this organigram:
- **172** top-level plugin functions
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
  - uploads media through `plugin_mastodon_upload_media_items()`
  - waits for asynchronously processed media through `plugin_mastodon_fetch_media_attachment()` and `plugin_mastodon_wait_for_media_attachment()` when required
  - creates or updates Mastodon statuses with `plugin_mastodon_create_status()` / `plugin_mastodon_update_status()`
  - builds comment replies with `plugin_mastodon_build_comment_status_text()`
  - resolves reply targets with `plugin_mastodon_resolve_comment_reply_target()`
  - persists mappings with `plugin_mastodon_state_set_entry_mapping()` and `plugin_mastodon_state_set_comment_mapping()`

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

- `plugin_mastodon_admin_assign()` assigns the plugin data for the Smarty view, including companion-plugin diagnostics.
- `setup()` registers the admin template and delegates to `plugin_mastodon_admin_assign()`.
- `main()` keeps the FlatPress admin lifecycle stable.
- `onsubmit()` handles:
  - configuration normalization and storage
  - OAuth app registration
  - authorization code exchange
  - manual synchronization
  - deferred deletion synchronization on later non-POST requests

## A. Entry points and admin integration

- `plugin_mastodon_head()` — line 696 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_maybe_sync()` — line 5250 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_run_sync()` — line 5190 — Run a full synchronization cycle.
- `plugin_mastodon_run_deletion_sync()` — line 5012 — Run the deferred deletion synchronization in a follow-up request after content sync completed.
- `plugin_mastodon_sync_due()` — line 5168 — Determine whether the scheduled synchronization is currently due.
- `plugin_mastodon_admin_assign()` — line 5278 — Assign plugin data to Smarty for the admin panel.
- `setup()` — line 5313 — Register the Mastodon admin panel template and assign plugin data to Smarty.
- `main()` — line 5318 — Keep the admin panel lifecycle compatible with FlatPress without extra processing.
- `onsubmit()` — line 5322 — Process configuration saves, OAuth actions, and the manual synchronization trigger.

## B. Defaults, configuration, secrets, and centralized FlatPress feature toggles

- `plugin_mastodon_default_options()` — line 53 — Return the default plugin option values.
- `plugin_mastodon_default_content_stats()` — line 75 — Return the default counters for the last content synchronization.
- `plugin_mastodon_default_deletion_stats()` — line 91 — Return the default counters for the last deletion synchronization.
- `plugin_mastodon_default_state()` — line 104 — Return the default runtime state structure.
- `plugin_mastodon_oauth_scopes()` — line 125 — Return the OAuth scopes requested by the plugin.
- `plugin_mastodon_fp_config()` — line 197 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 233 — Read a nested FlatPress configuration value.
- `plugin_mastodon_get_options()` — line 390 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_save_options()` — line 441 — Persist plugin options.
- `plugin_mastodon_secret_key()` — line 489 — Build the encryption key used for stored secrets.
- `plugin_mastodon_secret_encode()` — line 506 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_decode()` — line 529 — Decode a previously stored secret value.
- `plugin_mastodon_normalize_instance_url()` — line 560 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_head_username()` — line 593 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_instance_authority()` — line 632 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_profile_url()` — line 662 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_fediverse_creator_value()` — line 678 — Build the fediverse creator meta value.
- `plugin_mastodon_normalize_status_language()` — line 736 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_configured_status_language()` — line 758 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_normalize_sync_time()` — line 778 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_sync_start_date()` — line 795 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 821 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_should_update_local_from_remote()` — line 837 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 846 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 862 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_normalize_delete_sync_enabled()` — line 871 — Normalize the toggle that enables or disables the follow-up deletion synchronization.
- `plugin_mastodon_should_run_deletion_sync()` — line 887 — Check whether the follow-up deletion synchronization is enabled.
- `plugin_mastodon_enabled_plugin_state()` — line 1665 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_tag_plugin_active()` — line 1730 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_bbcode_plugin_active()` — line 1747 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_photoswipe_plugin_active()` — line 1764 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_emoticons_plugin_active()` — line 1781 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_companion_plugins_status()` — line 1796 — Return the status of companion FlatPress plugins used for the full Mastodon feature set.

## C. Caching, filesystem helpers, logging, and persisted state

- `plugin_mastodon_runtime_cache_get()` — line 136 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 164 — Store a value in the request-local plugin cache.
- `plugin_mastodon_runtime_cache_clear()` — line 182 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_io_read_file()` — line 251 — Read a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_write_file()` — line 264 — Write a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_append_file()` — line 277 — Append to a file via the FlatPress I/O layer.
- `plugin_mastodon_apcu_enabled()` — line 293 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_cache_key()` — line 302 — Build the namespaced APCu key used by this plugin.
- `plugin_mastodon_apcu_fetch()` — line 316 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 333 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_delete()` — line 346 — Delete a value from APCu using the FlatPress namespace key builder.
- `plugin_mastodon_file_prestat()` — line 359 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 379 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_ensure_state_dir()` — line 1057 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_log()` — line 1066 — Append a line to the plugin sync log.
- `plugin_mastodon_state_read()` — line 1076 — Load the persisted runtime state from disk.
- `plugin_mastodon_state_write()` — line 1115 — Persist the runtime state to disk.
- `plugin_mastodon_state_normalize()` — line 1139 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_comment_key()` — line 1185 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_set_entry_mapping()` — line 1200 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_comment_mapping()` — line 1238 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_remove_entry_mapping()` — line 1273 — Remove the mapping between a local entry and a remote status.
- `plugin_mastodon_state_remove_comment_mapping()` — line 1292 — Remove the mapping between a local comment and a remote status.
- `plugin_mastodon_state_get_entry_meta()` — line 1313 — Return mapping metadata for a local entry.
- `plugin_mastodon_state_get_comment_meta()` — line 1325 — Return mapping metadata for a local comment.

## D. Date, timestamp, visibility, and threading helpers

- `plugin_mastodon_timestamp_date_key()` — line 896 — Convert a Unix timestamp into a stable UTC date key.
- `plugin_mastodon_local_item_date_key()` — line 910 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_remote_status_date_key()` — line 930 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_datetime_date_key()` — line 956 — Normalize a stored date/datetime string to the sync-start date-key format.
- `plugin_mastodon_date_matches_sync_start()` — line 977 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_local_item_matches_sync_start()` — line 996 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 1006 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_mapping_matches_sync_start()` — line 1017 — Determine whether a stored synchronization mapping still belongs to the active sync-start window.
- `plugin_mastodon_parse_iso_datetime()` — line 1335 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 1353 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_remote_status_timestamp()` — line 1375 — Resolve the best timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 1394 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 1407 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_comment_parent_fields()` — line 1419 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_normalize_comment_parent_id()` — line 1428 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 1445 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_resolve_comment_reply_target()` — line 1467 — Resolve the remote reply target for a local comment export.

## E. Text, URLs, language strings, tags, emojis, and BBCode/HTML conversion

- `plugin_mastodon_guess_subject()` — line 1489 — Guess a subject line from imported plain text.
- `plugin_mastodon_html_entity_decode()` — line 1531 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_blog_base_url()` — line 1540 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_extract_url_token()` — line 1576 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_absolute_url()` — line 1593 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_lang_string()` — line 1635 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_normalize_tag_list()` — line 1837 — Normalize a list of tag labels.
- `plugin_mastodon_extend_time_limit()` — line 3297 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_extract_flatpress_tags()` — line 1868 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 1893 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 1910 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_remote_status_tags()` — line 1931 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 1958 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 2021 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 2034 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 2047 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 2101 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 2112 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_is_public_host()` — line 2134 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_public_url_for_mastodon()` — line 2157 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_plain_text_from_bbcode()` — line 2176 — Convert FlatPress BBCode into plain text for Mastodon export.
- `plugin_mastodon_subject_line_is_noise()` — line 2218 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_domains_match()` — line 2246 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_cleanup_imported_text()` — line 2260 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_dom_children_to_flatpress()` — line 2314 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 2332 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_public_entry_url()` — line 2441 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_comments_url()` — line 2468 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_comment_url()` — line 2496 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 2511 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_flatpress_to_mastodon()` — line 2613 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_limit_text()` — line 2728 — Limit text to a maximum number of characters.

## F. Local content access, media processing, hashing, and export ordering

- `plugin_mastodon_entry_hash()` — line 2750 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_comment_hash()` — line 2762 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_safe_path_component()` — line 2780 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_safe_filename()` — line 2795 — Sanitize a file name for local storage.
- `plugin_mastodon_media_relative_to_absolute()` — line 2808 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_media_prepare_directory()` — line 2821 — Ensure that a media directory exists.
- `plugin_mastodon_media_delete_tree()` — line 2837 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_copy_tree()` — line 2864 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_bbcode_attr_escape()` — line 2902 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_media_guess_mime_type()` — line 2914 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_parse_tag_attributes()` — line 2950 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_collect_local_entry_media()` — line 2981 — Collect local images referenced by an entry or gallery tag.
- `plugin_mastodon_entry_media_signature()` — line 3104 — Build a signature for media references contained in entry content.
- `plugin_mastodon_remote_status_image_attachments()` — line 3130 — Extract image attachments from a remote Mastodon status.
- `plugin_mastodon_remote_media_source_url()` — line 3153 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_media_description()` — line 3167 — Resolve the best description for a remote attachment.
- `plugin_mastodon_media_download()` — line 3182 — Download a remote media asset.
- `plugin_mastodon_build_imported_media_bbcode()` — line 3194 — Build FlatPress BBCode for imported remote media attachments.
- `plugin_mastodon_collect_entry_files()` — line 3772 — Collect entry files recursively from the FlatPress content tree.
- `plugin_mastodon_local_item_timestamp()` — line 3799 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_compare_local_entries_for_export()` — line 3825 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_list_local_entries()` — line 3843 — List local FlatPress entry identifiers.

## G. HTTP transport, PHP timeout budgeting, instance capability lookup, status-length budgeting, OAuth, Mastodon API calls, and media upload

- `plugin_mastodon_extend_time_limit()` — line 3297 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_instance_configuration()` — line 3328 — Load and cache the Mastodon instance configuration document.
- `plugin_mastodon_instance_media_limit()` — line 3358 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_media_description_limit()` — line 3371 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_url_reserved_length()` — line 3384 — Return the reserved Mastodon character budget used for each URL.
- `plugin_mastodon_status_text_length()` — line 3398 — Calculate the Mastodon-visible status length with instance URL budgeting.
- `plugin_mastodon_limit_status_text()` — line 3430 — Truncate status text using Mastodon URL-budget rules.
- `plugin_mastodon_http_request_multipart()` — line 3508 — Perform a multipart HTTP request.
- `plugin_mastodon_fetch_media_attachment()` — line 3637 — Fetch a single Mastodon media attachment by ID.
- `plugin_mastodon_wait_for_media_attachment()` — line 3648 — Poll an asynchronously processed Mastodon media attachment until it is ready or times out.
- `plugin_mastodon_upload_media_items()` — line 3694 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_parse_http_response_headers()` — line 3880 — Parse raw HTTP response headers.
- `plugin_mastodon_stream_context_request()` — line 3910 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_http_build_query()` — line 3949 — Build an application/x-www-form-urlencoded query string.
- `plugin_mastodon_http_request()` — line 3986 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_mastodon_api()` — line 4100 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_json()` — line 4144 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_response_error_message()` — line 4159 — Extract the most useful error message from an API response.
- `plugin_mastodon_register_app()` — line 4188 — Register the FlatPress application on the configured Mastodon instance.
- `plugin_mastodon_build_authorize_url()` — line 4210 — Build the OAuth authorization URL.
- `plugin_mastodon_exchange_code_for_token()` — line 4230 — Exchange an OAuth authorization code for an access token.
- `plugin_mastodon_verify_credentials()` — line 4257 — Verify the currently configured access token.
- `plugin_mastodon_instance_character_limit()` — line 4274 — Return the status character limit of the configured instance.
- `plugin_mastodon_fetch_account_statuses()` — line 4289 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_status_context()` — line 4336 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_fetch_status()` — line 4347 — Fetch a single Mastodon status.
- `plugin_mastodon_delete_status()` — line 4358 — Delete a Mastodon status, including media when requested.
- `plugin_mastodon_status_missing_response()` — line 4371 — Check whether an API response means that the referenced Mastodon status no longer exists.
- `plugin_mastodon_create_status()` — line 4384 — Create a Mastodon status.
- `plugin_mastodon_update_status()` — line 4408 — Update an existing Mastodon status.

## H. Import/export builders and synchronization orchestration

- `plugin_mastodon_build_entry_status_text()` — line 4428 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_comment_status_text()` — line 4496 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_import_remote_entry()` — line 4534 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_import_remote_comment()` — line 4634 — Import a remote Mastodon reply into FlatPress as a comment.
- `plugin_mastodon_import_remote_context_descendants()` — line 4706 — Import remote Mastodon replies from a fetched thread context.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 4791 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
- `plugin_mastodon_sync_remote_to_local()` — line 4825 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_sync_local_to_remote()` — line 4884 — Synchronize local FlatPress content to Mastodon.
- `plugin_mastodon_run_deletion_sync()` — line 5012 — Reconcile mapped deletions between FlatPress and Mastodon in a separate follow-up request.

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
- `main()` — line 5320 — Keep the admin panel lifecycle compatible with FlatPress without extra processing.
- `onsubmit()` — line 5324 — Process configuration saves, OAuth actions, and the manual synchronization trigger.
- `plugin_mastodon_absolute_url()` — line 1594 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_admin_assign()` — line 5279 — Assign plugin data to Smarty for the admin panel.
- `plugin_mastodon_apcu_cache_key()` — line 303 — Build the namespaced APCu key used by this plugin.
- `plugin_mastodon_apcu_delete()` — line 347 — Delete a value from APCu using the FlatPress namespace key builder.
- `plugin_mastodon_apcu_enabled()` — line 294 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_fetch()` — line 317 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 334 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_bbcode_attr_escape()` — line 2903 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_bbcode_plugin_active()` — line 1748 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_blog_base_url()` — line 1541 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_build_authorize_url()` — line 4211 — Build the OAuth authorization URL.
- `plugin_mastodon_build_comment_status_text()` — line 4497 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_build_entry_status_text()` — line 4429 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 2022 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_build_imported_media_bbcode()` — line 3195 — Build FlatPress BBCode for imported remote media attachments.
- `plugin_mastodon_cleanup_imported_text()` — line 2261 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_collect_entry_files()` — line 3773 — Collect entry files recursively from the FlatPress content tree.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 4792 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
- `plugin_mastodon_collect_local_entry_media()` — line 2982 — Collect local images referenced by an entry or gallery tag.
- `plugin_mastodon_comment_hash()` — line 2763 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_comment_parent_fields()` — line 1420 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_companion_plugins_status()` — line 1797 — Return the status of companion FlatPress plugins used for the full Mastodon feature set.
- `plugin_mastodon_compare_local_entries_for_export()` — line 3826 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_configured_status_language()` — line 759 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_create_status()` — line 4385 — Create a Mastodon status.
- `plugin_mastodon_date_matches_sync_start()` — line 978 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_datetime_date_key()` — line 957 — Normalize a stored date/datetime string to the sync-start date-key format.
- `plugin_mastodon_default_options()` — line 53 — Return the default plugin option values.
- `plugin_mastodon_default_content_stats()` — line 75 — Return the default counters for the last content synchronization.
- `plugin_mastodon_default_deletion_stats()` — line 92 — Return the default counters for the last deletion synchronization.
- `plugin_mastodon_default_state()` — line 105 — Return the default runtime state structure.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 1446 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_dom_children_to_flatpress()` — line 2315 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 2333 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_domains_match()` — line 2247 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 2035 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 2048 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_emoticons_plugin_active()` — line 1782 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_enabled_plugin_state()` — line 1666 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_ensure_state_dir()` — line 1058 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_entry_hash()` — line 2751 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_entry_media_signature()` — line 3105 — Build a signature for media references contained in entry content.
- `plugin_mastodon_exchange_code_for_token()` — line 4231 — Exchange an OAuth authorization code for an access token.
- `plugin_mastodon_extend_time_limit()` — line 3298 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_extract_flatpress_tags()` — line 1869 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_extract_url_token()` — line 1577 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_fediverse_creator_value()` — line 679 — Build the fediverse creator meta value.
- `plugin_mastodon_fetch_account_statuses()` — line 4290 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_media_attachment()` — line 3638 — Fetch a single Mastodon media attachment by ID.
- `plugin_mastodon_fetch_status_context()` — line 4337 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_file_prestat()` — line 360 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 380 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_flatpress_to_mastodon()` — line 2614 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_fp_config()` — line 198 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 234 — Read a nested FlatPress configuration value.
- `plugin_mastodon_get_options()` — line 391 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_guess_subject()` — line 1490 — Guess a subject line from imported plain text.
- `plugin_mastodon_head()` — line 697 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_html_entity_decode()` — line 1532 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_http_build_query()` — line 3950 — Build an application/x-www-form-urlencoded query string.
- `plugin_mastodon_http_request()` — line 3987 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_http_request_multipart()` — line 3509 — Perform a multipart HTTP request.
- `plugin_mastodon_import_remote_comment()` — line 4635 — Import a remote Mastodon reply into FlatPress as a comment.
- `plugin_mastodon_import_remote_context_descendants()` — line 4707 — Import remote Mastodon replies from a fetched thread context.
- `plugin_mastodon_import_remote_entry()` — line 4535 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_instance_authority()` — line 633 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_instance_character_limit()` — line 4275 — Return the status character limit of the configured instance.
- `plugin_mastodon_instance_configuration()` — line 3329 — Load and cache the Mastodon instance configuration document.
- `plugin_mastodon_instance_media_description_limit()` — line 3372 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_media_limit()` — line 3359 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_url_reserved_length()` — line 3385 — Return the reserved Mastodon character budget used for each URL.
- `plugin_mastodon_io_append_file()` — line 278 — Append to a file via the FlatPress I/O layer.
- `plugin_mastodon_io_read_file()` — line 252 — Read a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_write_file()` — line 265 — Write a file through the FlatPress I/O layer when available.
- `plugin_mastodon_is_public_host()` — line 2135 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_lang_string()` — line 1636 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_limit_status_text()` — line 3431 — Truncate status text using Mastodon URL-budget rules.
- `plugin_mastodon_limit_text()` — line 2729 — Limit text to a maximum number of characters.
- `plugin_mastodon_list_local_entries()` — line 3844 — List local FlatPress entry identifiers.
- `plugin_mastodon_local_item_date_key()` — line 911 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_local_item_matches_sync_start()` — line 997 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_local_item_timestamp()` — line 3800 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_log()` — line 1067 — Append a line to the plugin sync log.
- `plugin_mastodon_mastodon_api()` — line 4101 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 1911 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 2512 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_mastodon_json()` — line 4145 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_mapping_matches_sync_start()` — line 1018 — Determine whether a stored synchronization mapping still belongs to the active sync-start window.
- `plugin_mastodon_maybe_sync()` — line 5251 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_media_copy_tree()` — line 2865 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_media_delete_tree()` — line 2838 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_download()` — line 3183 — Download a remote media asset.
- `plugin_mastodon_media_guess_mime_type()` — line 2915 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_parse_tag_attributes()` — line 2951 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_media_prepare_directory()` — line 2822 — Ensure that a media directory exists.
- `plugin_mastodon_media_relative_to_absolute()` — line 2809 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_normalize_comment_parent_id()` — line 1429 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_normalize_head_username()` — line 594 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 847 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_normalize_instance_url()` — line 561 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_status_language()` — line 737 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_normalize_sync_start_date()` — line 796 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_sync_time()` — line 779 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_tag_list()` — line 1838 — Normalize a list of tag labels.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 822 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_oauth_scopes()` — line 126 — Return the OAuth scopes requested by the plugin.
- `plugin_mastodon_parse_http_response_headers()` — line 3881 — Parse raw HTTP response headers.
- `plugin_mastodon_parse_iso_datetime()` — line 1336 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 1354 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_photoswipe_plugin_active()` — line 1765 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_plain_text_from_bbcode()` — line 2177 — Convert FlatPress BBCode into plain text for Mastodon export.
- `plugin_mastodon_profile_url()` — line 663 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_public_comment_url()` — line 2497 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_public_comments_url()` — line 2469 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_entry_url()` — line 2442 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_url_for_mastodon()` — line 2158 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_register_app()` — line 4189 — Register the FlatPress application on the configured Mastodon instance.
- `plugin_mastodon_remote_media_description()` — line 3168 — Resolve the best description for a remote attachment.
- `plugin_mastodon_remote_media_source_url()` — line 3154 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_status_date_key()` — line 931 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_remote_status_image_attachments()` — line 3131 — Extract image attachments from a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 1408 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 1007 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_remote_status_tags()` — line 1932 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_remote_status_timestamp()` — line 1376 — Resolve the best timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 1395 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 2102 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 2113 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_resolve_comment_reply_target()` — line 1468 — Resolve the remote reply target for a local comment export.
- `plugin_mastodon_response_error_message()` — line 4160 — Extract the most useful error message from an API response.
- `plugin_mastodon_run_deletion_sync()` — line 5013 — Run the deferred deletion synchronization in a follow-up request after content sync completed.
- `plugin_mastodon_run_sync()` — line 5191 — Run a full synchronization cycle.
- `plugin_mastodon_runtime_cache_clear()` — line 183 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_runtime_cache_get()` — line 137 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 165 — Store a value in the request-local plugin cache.
- `plugin_mastodon_safe_filename()` — line 2796 — Sanitize a file name for local storage.
- `plugin_mastodon_safe_path_component()` — line 2781 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_save_options()` — line 442 — Persist plugin options.
- `plugin_mastodon_secret_decode()` — line 530 — Decode a previously stored secret value.
- `plugin_mastodon_secret_encode()` — line 507 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_key()` — line 490 — Build the encryption key used for stored secrets.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 863 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_normalize_delete_sync_enabled()` — line 872 — Normalize the toggle that enables or disables the follow-up deletion synchronization.
- `plugin_mastodon_should_run_deletion_sync()` — line 888 — Check whether the follow-up deletion synchronization is enabled.
- `plugin_mastodon_should_update_local_from_remote()` — line 838 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_state_comment_key()` — line 1186 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_get_comment_meta()` — line 1326 — Return mapping metadata for a local comment.
- `plugin_mastodon_state_get_entry_meta()` — line 1314 — Return mapping metadata for a local entry.
- `plugin_mastodon_state_normalize()` — line 1140 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_read()` — line 1077 — Load the persisted runtime state from disk.
- `plugin_mastodon_state_remove_comment_mapping()` — line 1293 — Remove the mapping between a local comment and a remote status.
- `plugin_mastodon_state_remove_entry_mapping()` — line 1274 — Remove the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_comment_mapping()` — line 1239 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_set_entry_mapping()` — line 1201 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_write()` — line 1116 — Persist the runtime state to disk.
- `plugin_mastodon_status_text_length()` — line 3399 — Calculate the Mastodon-visible status length with instance URL budgeting.
- `plugin_mastodon_stream_context_request()` — line 3911 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 1894 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 1959 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_subject_line_is_noise()` — line 2219 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_sync_due()` — line 5169 — Determine whether the scheduled synchronization is currently due.
- `plugin_mastodon_sync_local_to_remote()` — line 4885 — Synchronize local FlatPress content to Mastodon.
- `plugin_mastodon_sync_remote_to_local()` — line 4826 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_tag_plugin_active()` — line 1731 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_timestamp_date_key()` — line 897 — Convert a Unix timestamp into a stable UTC date key.
- `plugin_mastodon_update_status()` — line 4409 — Update an existing Mastodon status.
- `plugin_mastodon_upload_media_items()` — line 3695 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_verify_credentials()` — line 4258 — Verify the currently configured access token.
- `plugin_mastodon_wait_for_media_attachment()` — line 3649 — Poll an asynchronously processed Mastodon media attachment until it is ready or times out.
- `setup()` — line 5315 — Register the Mastodon admin panel template and assign plugin data to Smarty.

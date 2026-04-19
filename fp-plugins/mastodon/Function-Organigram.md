# Mastodon Plugin – PHP Function Organigram

## Purpose of this document

This document gives developers a structured overview of the Mastodon plugin PHP functions.

The layout is intentionally hierarchical so responsibilities, call paths, and important helper functions can be identified quickly.

## Scope

- The focus is the Mastodon plugin PHP code.
- The document is organized by responsibility and call path.
- Recently added FlatPress configuration reuse, centralized plugin-state detection, and FlatPress I/O helpers are reflected explicitly.
- The companion-plugin diagnostics for BBCode, PhotoSwipe, Tag, and Emoticons are covered.
- Mastodon instance-dependent URL budgeting, bounded PHP execution-budget refreshes, asynchronous media-upload readiness polling, and follow-up deletion synchronization are reflected explicitly.
- All plugin functions and admin panel methods currently present in the file are covered.
- Function summaries are derived from the current source file PHPDoc and verified against the function names and their location in the file.
- The terms **Entry** and **Comment** follow the wording used by the source code.

## Function count

The plugin file currently contains **171** callable functions/methods documented in this organigram:
- **168** top-level plugin functions
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
  Runs the real deletion synchronization in a separate follow-up request. It first gates mapped items through `plugin_mastodon_mapping_matches_sync_start()` so the delete pass stays inside the configured sync-start window, then reconciles missing local items against Mastodon with `plugin_mastodon_delete_status()` and missing remote statuses against FlatPress with `plugin_mastodon_fetch_status()` and local delete helpers.

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

- `plugin_mastodon_head()` — line 667 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_maybe_sync()` — line 5169 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_run_sync()` — line 5109 — Run a full synchronization cycle.
- `plugin_mastodon_run_deletion_sync()` — line 4937 — Run the deferred deletion synchronization in a follow-up request after content sync completed.
- `plugin_mastodon_sync_due()` — line 5087 — Determine whether the scheduled synchronization is currently due.
- `plugin_mastodon_admin_assign()` — line 5197 — Assign plugin data to Smarty for the admin panel.
- `setup()` — line 5232 — Register the Mastodon admin panel template and assign plugin data to Smarty.
- `main()` — line 5237 — Keep the admin panel lifecycle compatible with FlatPress without extra processing.
- `onsubmit()` — line 5241 — Process configuration saves, OAuth actions, and the manual synchronization trigger.

## B. Defaults, configuration, secrets, and centralized FlatPress feature toggles

- `plugin_mastodon_default_options()` — line 53 — Return the default plugin option values.
- `plugin_mastodon_default_state()` — line 74 — Return the default runtime state structure.
- `plugin_mastodon_oauth_scopes()` — line 106 — Return the OAuth scopes requested by the plugin.
- `plugin_mastodon_fp_config()` — line 178 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 214 — Read a nested FlatPress configuration value.
- `plugin_mastodon_get_options()` — line 371 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_save_options()` — line 420 — Persist plugin options.
- `plugin_mastodon_secret_key()` — line 460 — Build the encryption key used for stored secrets.
- `plugin_mastodon_secret_encode()` — line 477 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_decode()` — line 500 — Decode a previously stored secret value.
- `plugin_mastodon_normalize_instance_url()` — line 531 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_head_username()` — line 564 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_instance_authority()` — line 603 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_profile_url()` — line 633 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_fediverse_creator_value()` — line 649 — Build the fediverse creator meta value.
- `plugin_mastodon_normalize_status_language()` — line 707 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_configured_status_language()` — line 729 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_normalize_sync_time()` — line 749 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_sync_start_date()` — line 766 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 792 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_should_update_local_from_remote()` — line 808 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 817 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 833 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_enabled_plugin_state()` — line 1589 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_tag_plugin_active()` — line 1654 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_bbcode_plugin_active()` — line 1671 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_photoswipe_plugin_active()` — line 1688 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_emoticons_plugin_active()` — line 1705 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_companion_plugins_status()` — line 1720 — Return the status of companion FlatPress plugins used for the full Mastodon feature set.

## C. Caching, filesystem helpers, logging, and persisted state

- `plugin_mastodon_runtime_cache_get()` — line 117 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 145 — Store a value in the request-local plugin cache.
- `plugin_mastodon_runtime_cache_clear()` — line 163 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_io_read_file()` — line 232 — Read a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_write_file()` — line 245 — Write a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_append_file()` — line 258 — Append to a file via the FlatPress I/O layer.
- `plugin_mastodon_apcu_enabled()` — line 274 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_cache_key()` — line 283 — Build the namespaced APCu key used by this plugin.
- `plugin_mastodon_apcu_fetch()` — line 297 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 314 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_delete()` — line 327 — Delete a value from APCu using the FlatPress namespace key builder.
- `plugin_mastodon_file_prestat()` — line 340 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 360 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_ensure_state_dir()` — line 1003 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_log()` — line 1012 — Append a line to the plugin sync log.
- `plugin_mastodon_state_read()` — line 1022 — Load the persisted runtime state from disk.
- `plugin_mastodon_state_write()` — line 1061 — Persist the runtime state to disk.
- `plugin_mastodon_state_normalize()` — line 1085 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_comment_key()` — line 1109 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_set_entry_mapping()` — line 1124 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_comment_mapping()` — line 1162 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_remove_entry_mapping()` — line 1197 — Remove the mapping between a local entry and a remote status.
- `plugin_mastodon_state_remove_comment_mapping()` — line 1216 — Remove the mapping between a local comment and a remote status.
- `plugin_mastodon_state_get_entry_meta()` — line 1237 — Return mapping metadata for a local entry.
- `plugin_mastodon_state_get_comment_meta()` — line 1249 — Return mapping metadata for a local comment.

## D. Date, timestamp, visibility, and threading helpers

- `plugin_mastodon_timestamp_date_key()` — line 842 — Convert a Unix timestamp into a stable UTC date key.
- `plugin_mastodon_local_item_date_key()` — line 856 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_remote_status_date_key()` — line 876 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_datetime_date_key()` — line 902 — Normalize a stored date/datetime string to the sync-start date-key format.
- `plugin_mastodon_date_matches_sync_start()` — line 923 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_local_item_matches_sync_start()` — line 942 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 952 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_mapping_matches_sync_start()` — line 963 — Determine whether a stored synchronization mapping still belongs to the active sync-start window.
- `plugin_mastodon_parse_iso_datetime()` — line 1259 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 1277 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_remote_status_timestamp()` — line 1299 — Resolve the best timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 1318 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 1331 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_comment_parent_fields()` — line 1343 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_normalize_comment_parent_id()` — line 1352 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 1369 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_resolve_comment_reply_target()` — line 1391 — Resolve the remote reply target for a local comment export.

## E. Text, URLs, language strings, tags, emojis, and BBCode/HTML conversion

- `plugin_mastodon_guess_subject()` — line 1413 — Guess a subject line from imported plain text.
- `plugin_mastodon_html_entity_decode()` — line 1455 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_blog_base_url()` — line 1464 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_extract_url_token()` — line 1500 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_absolute_url()` — line 1517 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_lang_string()` — line 1559 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_normalize_tag_list()` — line 1761 — Normalize a list of tag labels.
- `plugin_mastodon_extend_time_limit()` — line 3222 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_extract_flatpress_tags()` — line 1792 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 1817 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 1834 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_remote_status_tags()` — line 1855 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 1882 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 1946 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 1959 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 1972 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 2026 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 2037 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_is_public_host()` — line 2059 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_public_url_for_mastodon()` — line 2082 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_plain_text_from_bbcode()` — line 2101 — Convert FlatPress BBCode into plain text for Mastodon export.
- `plugin_mastodon_subject_line_is_noise()` — line 2143 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_domains_match()` — line 2171 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_cleanup_imported_text()` — line 2185 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_dom_children_to_flatpress()` — line 2239 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 2257 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_public_entry_url()` — line 2366 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_comments_url()` — line 2393 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_comment_url()` — line 2421 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 2436 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_flatpress_to_mastodon()` — line 2538 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_limit_text()` — line 2653 — Limit text to a maximum number of characters.

## F. Local content access, media processing, hashing, and export ordering

- `plugin_mastodon_entry_hash()` — line 2675 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_comment_hash()` — line 2687 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_safe_path_component()` — line 2705 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_safe_filename()` — line 2720 — Sanitize a file name for local storage.
- `plugin_mastodon_media_relative_to_absolute()` — line 2733 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_media_prepare_directory()` — line 2746 — Ensure that a media directory exists.
- `plugin_mastodon_media_delete_tree()` — line 2762 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_copy_tree()` — line 2789 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_bbcode_attr_escape()` — line 2827 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_media_guess_mime_type()` — line 2839 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_parse_tag_attributes()` — line 2875 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_collect_local_entry_media()` — line 2906 — Collect local images referenced by an entry or gallery tag.
- `plugin_mastodon_entry_media_signature()` — line 3029 — Build a signature for media references contained in entry content.
- `plugin_mastodon_remote_status_image_attachments()` — line 3055 — Extract image attachments from a remote Mastodon status.
- `plugin_mastodon_remote_media_source_url()` — line 3078 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_media_description()` — line 3092 — Resolve the best description for a remote attachment.
- `plugin_mastodon_media_download()` — line 3107 — Download a remote media asset.
- `plugin_mastodon_build_imported_media_bbcode()` — line 3119 — Build FlatPress BBCode for imported remote media attachments.
- `plugin_mastodon_collect_entry_files()` — line 3697 — Collect entry files recursively from the FlatPress content tree.
- `plugin_mastodon_local_item_timestamp()` — line 3724 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_compare_local_entries_for_export()` — line 3750 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_list_local_entries()` — line 3768 — List local FlatPress entry identifiers.

## G. HTTP transport, PHP timeout budgeting, instance capability lookup, status-length budgeting, OAuth, Mastodon API calls, and media upload

- `plugin_mastodon_extend_time_limit()` — line 3222 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_instance_configuration()` — line 3253 — Load and cache the Mastodon instance configuration document.
- `plugin_mastodon_instance_media_limit()` — line 3283 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_media_description_limit()` — line 3296 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_url_reserved_length()` — line 3309 — Return the reserved Mastodon character budget used for each URL.
- `plugin_mastodon_status_text_length()` — line 3323 — Calculate the Mastodon-visible status length with instance URL budgeting.
- `plugin_mastodon_limit_status_text()` — line 3355 — Truncate status text using Mastodon URL-budget rules.
- `plugin_mastodon_http_request_multipart()` — line 3433 — Perform a multipart HTTP request.
- `plugin_mastodon_fetch_media_attachment()` — line 3562 — Fetch a single Mastodon media attachment by ID.
- `plugin_mastodon_wait_for_media_attachment()` — line 3573 — Poll an asynchronously processed Mastodon media attachment until it is ready or times out.
- `plugin_mastodon_upload_media_items()` — line 3619 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_parse_http_response_headers()` — line 3805 — Parse raw HTTP response headers.
- `plugin_mastodon_stream_context_request()` — line 3835 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_http_build_query()` — line 3874 — Build an application/x-www-form-urlencoded query string.
- `plugin_mastodon_http_request()` — line 3911 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_mastodon_api()` — line 4025 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_json()` — line 4069 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_response_error_message()` — line 4084 — Extract the most useful error message from an API response.
- `plugin_mastodon_register_app()` — line 4113 — Register the FlatPress application on the configured Mastodon instance.
- `plugin_mastodon_build_authorize_url()` — line 4135 — Build the OAuth authorization URL.
- `plugin_mastodon_exchange_code_for_token()` — line 4155 — Exchange an OAuth authorization code for an access token.
- `plugin_mastodon_verify_credentials()` — line 4182 — Verify the currently configured access token.
- `plugin_mastodon_instance_character_limit()` — line 4199 — Return the status character limit of the configured instance.
- `plugin_mastodon_fetch_account_statuses()` — line 4214 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_status_context()` — line 4261 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_fetch_status()` — line 4272 — Fetch a single Mastodon status.
- `plugin_mastodon_delete_status()` — line 4283 — Delete a Mastodon status, including media when requested.
- `plugin_mastodon_status_missing_response()` — line 4296 — Check whether an API response means that the referenced Mastodon status no longer exists.
- `plugin_mastodon_create_status()` — line 4309 — Create a Mastodon status.
- `plugin_mastodon_update_status()` — line 4333 — Update an existing Mastodon status.

## H. Import/export builders and synchronization orchestration

- `plugin_mastodon_build_entry_status_text()` — line 4353 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_comment_status_text()` — line 4421 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_import_remote_entry()` — line 4459 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_import_remote_comment()` — line 4559 — Import a remote Mastodon reply into FlatPress as a comment.
- `plugin_mastodon_import_remote_context_descendants()` — line 4631 — Import remote Mastodon replies from a fetched thread context.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 4716 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
- `plugin_mastodon_sync_remote_to_local()` — line 4750 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_sync_local_to_remote()` — line 4809 — Synchronize local FlatPress content to Mastodon.
- `plugin_mastodon_run_deletion_sync()` — line 4937 — Reconcile mapped deletions between FlatPress and Mastodon in a separate follow-up request.

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
- `main()` — line 5237 — Keep the admin panel lifecycle compatible with FlatPress without extra processing.
- `onsubmit()` — line 5241 — Process configuration saves, OAuth actions, and the manual synchronization trigger.
- `plugin_mastodon_absolute_url()` — line 1517 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_admin_assign()` — line 5197 — Assign plugin data to Smarty for the admin panel.
- `plugin_mastodon_apcu_cache_key()` — line 283 — Build the namespaced APCu key used by this plugin.
- `plugin_mastodon_apcu_delete()` — line 327 — Delete a value from APCu using the FlatPress namespace key builder.
- `plugin_mastodon_apcu_enabled()` — line 274 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_fetch()` — line 297 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 314 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_bbcode_attr_escape()` — line 2827 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_bbcode_plugin_active()` — line 1671 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_blog_base_url()` — line 1464 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_build_authorize_url()` — line 4135 — Build the OAuth authorization URL.
- `plugin_mastodon_build_comment_status_text()` — line 4421 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_build_entry_status_text()` — line 4353 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 1946 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_build_imported_media_bbcode()` — line 3119 — Build FlatPress BBCode for imported remote media attachments.
- `plugin_mastodon_cleanup_imported_text()` — line 2185 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_collect_entry_files()` — line 3697 — Collect entry files recursively from the FlatPress content tree.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 4716 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
- `plugin_mastodon_collect_local_entry_media()` — line 2906 — Collect local images referenced by an entry or gallery tag.
- `plugin_mastodon_comment_hash()` — line 2687 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_comment_parent_fields()` — line 1343 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_companion_plugins_status()` — line 1720 — Return the status of companion FlatPress plugins used for the full Mastodon feature set.
- `plugin_mastodon_compare_local_entries_for_export()` — line 3750 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_configured_status_language()` — line 729 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_create_status()` — line 4309 — Create a Mastodon status.
- `plugin_mastodon_date_matches_sync_start()` — line 923 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_datetime_date_key()` — line 902 — Normalize a stored date/datetime string to the sync-start date-key format.
- `plugin_mastodon_default_options()` — line 53 — Return the default plugin option values.
- `plugin_mastodon_default_state()` — line 74 — Return the default runtime state structure.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 1369 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_dom_children_to_flatpress()` — line 2239 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 2257 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_domains_match()` — line 2171 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 1959 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 1972 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_emoticons_plugin_active()` — line 1705 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_enabled_plugin_state()` — line 1589 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_ensure_state_dir()` — line 1003 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_entry_hash()` — line 2675 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_entry_media_signature()` — line 3029 — Build a signature for media references contained in entry content.
- `plugin_mastodon_exchange_code_for_token()` — line 4155 — Exchange an OAuth authorization code for an access token.
- `plugin_mastodon_extend_time_limit()` — line 3222 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_extract_flatpress_tags()` — line 1792 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_extract_url_token()` — line 1500 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_fediverse_creator_value()` — line 649 — Build the fediverse creator meta value.
- `plugin_mastodon_fetch_account_statuses()` — line 4214 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_media_attachment()` — line 3562 — Fetch a single Mastodon media attachment by ID.
- `plugin_mastodon_fetch_status_context()` — line 4261 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_file_prestat()` — line 340 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 360 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_flatpress_to_mastodon()` — line 2538 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_fp_config()` — line 178 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 214 — Read a nested FlatPress configuration value.
- `plugin_mastodon_get_options()` — line 371 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_guess_subject()` — line 1413 — Guess a subject line from imported plain text.
- `plugin_mastodon_head()` — line 667 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_html_entity_decode()` — line 1455 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_http_build_query()` — line 3874 — Build an application/x-www-form-urlencoded query string.
- `plugin_mastodon_http_request()` — line 3911 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_http_request_multipart()` — line 3433 — Perform a multipart HTTP request.
- `plugin_mastodon_import_remote_comment()` — line 4559 — Import a remote Mastodon reply into FlatPress as a comment.
- `plugin_mastodon_import_remote_context_descendants()` — line 4631 — Import remote Mastodon replies from a fetched thread context.
- `plugin_mastodon_import_remote_entry()` — line 4459 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_instance_authority()` — line 603 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_instance_character_limit()` — line 4199 — Return the status character limit of the configured instance.
- `plugin_mastodon_instance_configuration()` — line 3253 — Load and cache the Mastodon instance configuration document.
- `plugin_mastodon_instance_media_description_limit()` — line 3296 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_media_limit()` — line 3283 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_url_reserved_length()` — line 3309 — Return the reserved Mastodon character budget used for each URL.
- `plugin_mastodon_io_append_file()` — line 258 — Append to a file via the FlatPress I/O layer.
- `plugin_mastodon_io_read_file()` — line 232 — Read a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_write_file()` — line 245 — Write a file through the FlatPress I/O layer when available.
- `plugin_mastodon_is_public_host()` — line 2059 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_lang_string()` — line 1559 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_limit_status_text()` — line 3355 — Truncate status text using Mastodon URL-budget rules.
- `plugin_mastodon_limit_text()` — line 2653 — Limit text to a maximum number of characters.
- `plugin_mastodon_list_local_entries()` — line 3768 — List local FlatPress entry identifiers.
- `plugin_mastodon_local_item_date_key()` — line 856 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_local_item_matches_sync_start()` — line 942 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_local_item_timestamp()` — line 3724 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_log()` — line 1012 — Append a line to the plugin sync log.
- `plugin_mastodon_mastodon_api()` — line 4025 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 1834 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 2436 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_mastodon_json()` — line 4069 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_mapping_matches_sync_start()` — line 963 — Determine whether a stored synchronization mapping still belongs to the active sync-start window.
- `plugin_mastodon_maybe_sync()` — line 5169 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_media_copy_tree()` — line 2789 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_media_delete_tree()` — line 2762 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_download()` — line 3107 — Download a remote media asset.
- `plugin_mastodon_media_guess_mime_type()` — line 2839 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_parse_tag_attributes()` — line 2875 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_media_prepare_directory()` — line 2746 — Ensure that a media directory exists.
- `plugin_mastodon_media_relative_to_absolute()` — line 2733 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_normalize_comment_parent_id()` — line 1352 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_normalize_head_username()` — line 564 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 817 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_normalize_instance_url()` — line 531 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_status_language()` — line 707 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_normalize_sync_start_date()` — line 766 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_sync_time()` — line 749 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_tag_list()` — line 1761 — Normalize a list of tag labels.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 792 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_oauth_scopes()` — line 106 — Return the OAuth scopes requested by the plugin.
- `plugin_mastodon_parse_http_response_headers()` — line 3805 — Parse raw HTTP response headers.
- `plugin_mastodon_parse_iso_datetime()` — line 1259 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 1277 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_photoswipe_plugin_active()` — line 1688 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_plain_text_from_bbcode()` — line 2101 — Convert FlatPress BBCode into plain text for Mastodon export.
- `plugin_mastodon_profile_url()` — line 633 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_public_comment_url()` — line 2421 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_public_comments_url()` — line 2393 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_entry_url()` — line 2366 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_url_for_mastodon()` — line 2082 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_register_app()` — line 4113 — Register the FlatPress application on the configured Mastodon instance.
- `plugin_mastodon_remote_media_description()` — line 3092 — Resolve the best description for a remote attachment.
- `plugin_mastodon_remote_media_source_url()` — line 3078 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_status_date_key()` — line 876 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_remote_status_image_attachments()` — line 3055 — Extract image attachments from a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 1331 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 952 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_remote_status_tags()` — line 1855 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_remote_status_timestamp()` — line 1299 — Resolve the best timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 1318 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 2026 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 2037 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_resolve_comment_reply_target()` — line 1391 — Resolve the remote reply target for a local comment export.
- `plugin_mastodon_response_error_message()` — line 4084 — Extract the most useful error message from an API response.
- `plugin_mastodon_run_deletion_sync()` — line 4937 — Run the deferred deletion synchronization in a follow-up request after content sync completed.
- `plugin_mastodon_run_sync()` — line 5109 — Run a full synchronization cycle.
- `plugin_mastodon_runtime_cache_clear()` — line 163 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_runtime_cache_get()` — line 117 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 145 — Store a value in the request-local plugin cache.
- `plugin_mastodon_safe_filename()` — line 2720 — Sanitize a file name for local storage.
- `plugin_mastodon_safe_path_component()` — line 2705 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_save_options()` — line 420 — Persist plugin options.
- `plugin_mastodon_secret_decode()` — line 500 — Decode a previously stored secret value.
- `plugin_mastodon_secret_encode()` — line 477 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_key()` — line 460 — Build the encryption key used for stored secrets.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 833 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_should_update_local_from_remote()` — line 808 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_state_comment_key()` — line 1109 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_get_comment_meta()` — line 1249 — Return mapping metadata for a local comment.
- `plugin_mastodon_state_get_entry_meta()` — line 1237 — Return mapping metadata for a local entry.
- `plugin_mastodon_state_normalize()` — line 1085 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_read()` — line 1022 — Load the persisted runtime state from disk.
- `plugin_mastodon_state_remove_comment_mapping()` — line 1216 — Remove the mapping between a local comment and a remote status.
- `plugin_mastodon_state_remove_entry_mapping()` — line 1197 — Remove the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_comment_mapping()` — line 1162 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_set_entry_mapping()` — line 1124 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_write()` — line 1061 — Persist the runtime state to disk.
- `plugin_mastodon_status_text_length()` — line 3323 — Calculate the Mastodon-visible status length with instance URL budgeting.
- `plugin_mastodon_stream_context_request()` — line 3835 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 1817 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 1882 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_subject_line_is_noise()` — line 2143 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_sync_due()` — line 5087 — Determine whether the scheduled synchronization is currently due.
- `plugin_mastodon_sync_local_to_remote()` — line 4809 — Synchronize local FlatPress content to Mastodon.
- `plugin_mastodon_sync_remote_to_local()` — line 4750 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_tag_plugin_active()` — line 1654 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_timestamp_date_key()` — line 842 — Convert a Unix timestamp into a stable UTC date key.
- `plugin_mastodon_update_status()` — line 4333 — Update an existing Mastodon status.
- `plugin_mastodon_upload_media_items()` — line 3619 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_verify_credentials()` — line 4182 — Verify the currently configured access token.
- `plugin_mastodon_wait_for_media_attachment()` — line 3573 — Poll an asynchronously processed Mastodon media attachment until it is ready or times out.
- `setup()` — line 5232 — Register the Mastodon admin panel template and assign plugin data to Smarty.

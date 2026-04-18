# Mastodon Plugin – PHP Function Organigram

## Purpose of this document

This document gives developers a structured overview of the Mastodon plugin PHP functions.

The layout is intentionally hierarchical so responsibilities, call paths, and important helper functions can be identified quickly.

## Scope

- The focus is the Mastodon plugin PHP code.
- The document is organized by responsibility and call path.
- Recently added FlatPress configuration reuse, centralized plugin-state detection, and FlatPress I/O helpers are reflected explicitly.
- The companion-plugin diagnostics for BBCode, PhotoSwipe, Tag, and Emoticons are covered.
- Mastodon instance-dependent URL budgeting, bounded PHP execution-budget refreshes, and asynchronous media-upload readiness polling are reflected explicitly.
- All plugin functions and admin panel methods currently present in the file are covered.
- Function summaries are derived from the current source file PHPDoc and verified against the function names and their location in the file.
- The terms **Entry** and **Comment** follow the wording used by the source code.

## Function count

The plugin file currently contains **163** callable functions/methods documented in this organigram:
- **160** top-level plugin functions
- **3** admin panel class methods (`setup()`, `main()`, `onsubmit()`)

## High-level call flow

### Frontend and scheduler entry points

- `plugin_mastodon_head()`  
  Reads the saved Mastodon configuration, normalizes the instance URL and username, and emits:
  - `<link rel="me" ...>`
  - `<meta name="fediverse:creator" ...>`

- `plugin_mastodon_maybe_sync()`  
  Calls `plugin_mastodon_sync_due()` and, if due, runs `plugin_mastodon_run_sync()` within the current FlatPress request.

- `plugin_mastodon_run_sync()`  
  Refreshes the shared-request PHP execution budget through `plugin_mastodon_extend_time_limit()` and then executes the two main directions in order:
  1. `plugin_mastodon_sync_local_to_remote()`
  2. `plugin_mastodon_sync_remote_to_local()`

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

## A. Entry points and admin integration

- `plugin_mastodon_head()` — line 677 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_maybe_sync()` — line 4871 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_run_sync()` — line 4812 — Run a full synchronization cycle.
- `plugin_mastodon_sync_due()` — line 4790 — Determine whether the scheduled synchronization is currently due.
- `plugin_mastodon_admin_assign()` — line 4887 — Assign plugin data to Smarty for the admin panel.
- `setup()` — line 4922 — Register the Mastodon admin panel template and assign plugin data to Smarty.
- `main()` — line 4927 — Keep the admin panel lifecycle compatible with FlatPress without extra processing.
- `onsubmit()` — line 4931 — Process configuration saves, OAuth actions, and the manual synchronization trigger.

## B. Defaults, configuration, secrets, and centralized FlatPress feature toggles

- `plugin_mastodon_default_options()` — line 86 — Return the default plugin option values.
- `plugin_mastodon_default_state()` — line 107 — Return the default runtime state structure.
- `plugin_mastodon_oauth_scopes()` — line 133 — Return the OAuth scopes requested by the plugin.
- `plugin_mastodon_fp_config()` — line 205 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 241 — Read a nested FlatPress configuration value.
- `plugin_mastodon_get_options()` — line 398 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_save_options()` — line 430 — Persist plugin options.
- `plugin_mastodon_secret_key()` — line 470 — Build the encryption key used for stored secrets.
- `plugin_mastodon_secret_encode()` — line 487 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_decode()` — line 510 — Decode a previously stored secret value.
- `plugin_mastodon_normalize_instance_url()` — line 541 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_head_username()` — line 574 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_instance_authority()` — line 613 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_profile_url()` — line 643 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_fediverse_creator_value()` — line 659 — Build the fediverse creator meta value.
- `plugin_mastodon_normalize_status_language()` — line 717 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_configured_status_language()` — line 739 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_normalize_sync_time()` — line 759 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_sync_start_date()` — line 776 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 802 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_should_update_local_from_remote()` — line 818 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 827 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 843 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_enabled_plugin_state()` — line 1469 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_tag_plugin_active()` — line 1536 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_bbcode_plugin_active()` — line 1553 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_photoswipe_plugin_active()` — line 1570 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_emoticons_plugin_active()` — line 1587 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_companion_plugins_status()` — line 1602 — Return the status of companion FlatPress plugins used for the full Mastodon feature set.

## C. Caching, filesystem helpers, logging, and persisted state

- `plugin_mastodon_runtime_cache_get()` — line 144 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 172 — Store a value in the request-local plugin cache.
- `plugin_mastodon_runtime_cache_clear()` — line 190 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_io_read_file()` — line 259 — Read a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_write_file()` — line 272 — Write a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_append_file()` — line 285 — Append to a file via the FlatPress I/O layer.
- `plugin_mastodon_apcu_enabled()` — line 301 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_cache_key()` — line 310 — Build the namespaced APCu key used by this plugin.
- `plugin_mastodon_apcu_fetch()` — line 324 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 341 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_delete()` — line 354 — Delete a value from APCu using the FlatPress namespace key builder.
- `plugin_mastodon_file_prestat()` — line 367 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 387 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_ensure_state_dir()` — line 950 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_log()` — line 959 — Append a line to the plugin sync log.
- `plugin_mastodon_state_read()` — line 969 — Load the persisted runtime state from disk.
- `plugin_mastodon_state_write()` — line 1007 — Persist the runtime state to disk.
- `plugin_mastodon_state_normalize()` — line 1031 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_comment_key()` — line 1049 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_set_entry_mapping()` — line 1064 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_comment_mapping()` — line 1092 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_get_entry_meta()` — line 1117 — Return mapping metadata for a local entry.
- `plugin_mastodon_state_get_comment_meta()` — line 1129 — Return mapping metadata for a local comment.

## D. Date, timestamp, visibility, and threading helpers

- `plugin_mastodon_timestamp_date_key()` — line 852 — Convert a Unix timestamp into a stable UTC date key.
- `plugin_mastodon_local_item_date_key()` — line 866 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_remote_status_date_key()` — line 886 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_date_matches_sync_start()` — line 913 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_local_item_matches_sync_start()` — line 932 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 942 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_parse_iso_datetime()` — line 1139 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 1157 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_remote_status_timestamp()` — line 1179 — Resolve the best timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 1198 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 1211 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_comment_parent_fields()` — line 1223 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_normalize_comment_parent_id()` — line 1232 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 1249 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_resolve_comment_reply_target()` — line 1271 — Resolve the remote reply target for a local comment export.

## E. Text, URLs, language strings, tags, emojis, and BBCode/HTML conversion

- `plugin_mastodon_guess_subject()` — line 1293 — Guess a subject line from imported plain text.
- `plugin_mastodon_html_entity_decode()` — line 1335 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_blog_base_url()` — line 1344 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_extract_url_token()` — line 1380 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_absolute_url()` — line 1397 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_lang_string()` — line 1439 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_normalize_tag_list()` — line 1643 — Normalize a list of tag labels.
- `plugin_mastodon_extend_time_limit()` — line 3101 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_extract_flatpress_tags()` — line 1674 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 1699 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 1716 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_remote_status_tags()` — line 1737 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 1764 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 1831 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 1844 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 1857 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 1911 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 1922 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_is_public_host()` — line 1944 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_public_url_for_mastodon()` — line 1967 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_plain_text_from_bbcode()` — line 1986 — Convert FlatPress BBCode into plain text for Mastodon export.
- `plugin_mastodon_subject_line_is_noise()` — line 2028 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_domains_match()` — line 2056 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_cleanup_imported_text()` — line 2070 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_dom_children_to_flatpress()` — line 2124 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 2142 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_public_entry_url()` — line 2245 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_comments_url()` — line 2272 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_comment_url()` — line 2300 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 2315 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_flatpress_to_mastodon()` — line 2417 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_limit_text()` — line 2532 — Limit text to a maximum number of characters.

## F. Local content access, media processing, hashing, and export ordering

- `plugin_mastodon_entry_hash()` — line 2554 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_comment_hash()` — line 2566 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_safe_path_component()` — line 2584 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_safe_filename()` — line 2599 — Sanitize a file name for local storage.
- `plugin_mastodon_media_relative_to_absolute()` — line 2612 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_media_prepare_directory()` — line 2625 — Ensure that a media directory exists.
- `plugin_mastodon_media_delete_tree()` — line 2641 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_copy_tree()` — line 2668 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_bbcode_attr_escape()` — line 2706 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_media_guess_mime_type()` — line 2718 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_parse_tag_attributes()` — line 2754 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_collect_local_entry_media()` — line 2785 — Collect local images referenced by an entry or gallery tag.
- `plugin_mastodon_entry_media_signature()` — line 2908 — Build a signature for media references contained in entry content.
- `plugin_mastodon_remote_status_image_attachments()` — line 2934 — Extract image attachments from a remote Mastodon status.
- `plugin_mastodon_remote_media_source_url()` — line 2957 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_media_description()` — line 2971 — Resolve the best description for a remote attachment.
- `plugin_mastodon_media_download()` — line 2986 — Download a remote media asset.
- `plugin_mastodon_build_imported_media_bbcode()` — line 2998 — Build FlatPress BBCode for imported remote media attachments.
- `plugin_mastodon_collect_entry_files()` — line 3576 — Collect entry files recursively from the FlatPress content tree.
- `plugin_mastodon_local_item_timestamp()` — line 3607 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_compare_local_entries_for_export()` — line 3633 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_list_local_entries()` — line 3651 — List local FlatPress entry identifiers.

## G. HTTP transport, PHP timeout budgeting, instance capability lookup, status-length budgeting, OAuth, Mastodon API calls, and media upload

- `plugin_mastodon_extend_time_limit()` — line 3101 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_instance_configuration()` — line 3132 — Load and cache the Mastodon instance configuration document.
- `plugin_mastodon_instance_media_limit()` — line 3162 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_media_description_limit()` — line 3175 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_url_reserved_length()` — line 3188 — Return the reserved Mastodon character budget used for each URL.
- `plugin_mastodon_status_text_length()` — line 3202 — Calculate the Mastodon-visible status length with instance URL budgeting.
- `plugin_mastodon_limit_status_text()` — line 3234 — Truncate status text using Mastodon URL-budget rules.
- `plugin_mastodon_http_request_multipart()` — line 3312 — Perform a multipart HTTP request.
- `plugin_mastodon_fetch_media_attachment()` — line 3441 — Fetch a single Mastodon media attachment by ID.
- `plugin_mastodon_wait_for_media_attachment()` — line 3452 — Poll an asynchronously processed Mastodon media attachment until it is ready or times out.
- `plugin_mastodon_upload_media_items()` — line 3498 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_parse_http_response_headers()` — line 3688 — Parse raw HTTP response headers.
- `plugin_mastodon_stream_context_request()` — line 3721 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_http_build_query()` — line 3760 — Build an application/x-www-form-urlencoded query string.
- `plugin_mastodon_http_request()` — line 3797 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_mastodon_api()` — line 3911 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_json()` — line 3955 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_response_error_message()` — line 3970 — Extract the most useful error message from an API response.
- `plugin_mastodon_register_app()` — line 3999 — Register the FlatPress application on the configured Mastodon instance.
- `plugin_mastodon_build_authorize_url()` — line 4021 — Build the OAuth authorization URL.
- `plugin_mastodon_exchange_code_for_token()` — line 4041 — Exchange an OAuth authorization code for an access token.
- `plugin_mastodon_verify_credentials()` — line 4068 — Verify the currently configured access token.
- `plugin_mastodon_instance_character_limit()` — line 4085 — Return the status character limit of the configured instance.
- `plugin_mastodon_fetch_account_statuses()` — line 4100 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_status_context()` — line 4147 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_create_status()` — line 4160 — Create a Mastodon status.
- `plugin_mastodon_update_status()` — line 4184 — Update an existing Mastodon status.

## H. Import/export builders and synchronization orchestration

- `plugin_mastodon_build_entry_status_text()` — line 4204 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_comment_status_text()` — line 4272 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_import_remote_entry()` — line 4310 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_import_remote_comment()` — line 4410 — Import a remote Mastodon reply into FlatPress as a comment.
- `plugin_mastodon_import_remote_context_descendants()` — line 4482 — Import remote Mastodon replies from a fetched thread context.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 4567 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
- `plugin_mastodon_sync_remote_to_local()` — line 4601 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_sync_local_to_remote()` — line 4660 — Synchronize local FlatPress content to Mastodon.

## Recommended reading order for new developers

A practical way to understand the plugin is:

1. `plugin_mastodon_run_sync()`
2. `plugin_mastodon_sync_local_to_remote()`
3. `plugin_mastodon_sync_remote_to_local()`
4. `plugin_mastodon_build_entry_status_text()`
5. `plugin_mastodon_build_comment_status_text()`
6. `plugin_mastodon_import_remote_entry()`
7. `plugin_mastodon_import_remote_comment()`
8. `plugin_mastodon_state_read()` / `plugin_mastodon_state_write()`
9. `plugin_mastodon_get_options()`
10. `plugin_mastodon_enabled_plugin_state()`
11. `plugin_mastodon_extend_time_limit()`
12. `plugin_mastodon_instance_configuration()`
13. `plugin_mastodon_instance_url_reserved_length()` / `plugin_mastodon_status_text_length()` / `plugin_mastodon_limit_status_text()`
14. `plugin_mastodon_http_request()`
15. `plugin_mastodon_collect_local_entry_media()`
16. `plugin_mastodon_upload_media_items()` / `plugin_mastodon_wait_for_media_attachment()`

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
- old-thread reply refresh during remote import
- explicit export ordering so older local entries are posted before newer ones

## Maintenance notes

When changing the plugin, these clusters usually need to stay in sync:

- **configuration + early FlatPress config reuse + centralized plugin-state lookup + admin UI + normalization helpers**
- **state mappings + import/export code**
- **text conversion + media conversion + hashtag handling + companion-plugin expectations**
- **date filters + scheduling + known-thread refresh**
- **FlatPress file I/O wrappers + state/log/media persistence**
- **HTTP transport + PHP timeout-budget refresh + OAuth + instance capability caching + URL-budget handling + async media readiness polling**

A change in one of these areas often requires corresponding updates in the simulation script.

## Alphabetical appendix

- `main()` — line 4927 — Keep the admin panel lifecycle compatible with FlatPress without extra processing.
- `onsubmit()` — line 4931 — Process configuration saves, OAuth actions, and the manual synchronization trigger.
- `plugin_mastodon_absolute_url()` — line 1397 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_admin_assign()` — line 4887 — Assign plugin data to Smarty for the admin panel.
- `plugin_mastodon_apcu_cache_key()` — line 310 — Build the namespaced APCu key used by this plugin.
- `plugin_mastodon_apcu_delete()` — line 354 — Delete a value from APCu using the FlatPress namespace key builder.
- `plugin_mastodon_apcu_enabled()` — line 301 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_fetch()` — line 324 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 341 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_bbcode_attr_escape()` — line 2706 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_bbcode_plugin_active()` — line 1553 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_blog_base_url()` — line 1344 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_build_authorize_url()` — line 4021 — Build the OAuth authorization URL.
- `plugin_mastodon_build_comment_status_text()` — line 4272 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_build_entry_status_text()` — line 4204 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 1831 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_build_imported_media_bbcode()` — line 2998 — Build FlatPress BBCode for imported remote media attachments.
- `plugin_mastodon_cleanup_imported_text()` — line 2070 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_collect_entry_files()` — line 3576 — Collect entry files recursively from the FlatPress content tree.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 4567 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
- `plugin_mastodon_collect_local_entry_media()` — line 2785 — Collect local images referenced by an entry or gallery tag.
- `plugin_mastodon_comment_hash()` — line 2566 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_comment_parent_fields()` — line 1223 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_companion_plugins_status()` — line 1602 — Return the status of companion FlatPress plugins used for the full Mastodon feature set.
- `plugin_mastodon_compare_local_entries_for_export()` — line 3633 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_configured_status_language()` — line 739 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_create_status()` — line 4160 — Create a Mastodon status.
- `plugin_mastodon_date_matches_sync_start()` — line 913 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_default_options()` — line 86 — Return the default plugin option values.
- `plugin_mastodon_default_state()` — line 107 — Return the default runtime state structure.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 1249 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_dom_children_to_flatpress()` — line 2124 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 2142 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_domains_match()` — line 2056 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 1844 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 1857 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_emoticons_plugin_active()` — line 1587 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_enabled_plugin_state()` — line 1469 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_ensure_state_dir()` — line 950 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_entry_hash()` — line 2554 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_entry_media_signature()` — line 2908 — Build a signature for media references contained in entry content.
- `plugin_mastodon_exchange_code_for_token()` — line 4041 — Exchange an OAuth authorization code for an access token.
- `plugin_mastodon_extend_time_limit()` — line 3101 — Refresh or raise the PHP execution time budget for long-running Mastodon work without lowering an existing higher or unlimited limit.
- `plugin_mastodon_extract_flatpress_tags()` — line 1674 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_extract_url_token()` — line 1380 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_fediverse_creator_value()` — line 659 — Build the fediverse creator meta value.
- `plugin_mastodon_fetch_account_statuses()` — line 4100 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_media_attachment()` — line 3441 — Fetch a single Mastodon media attachment by ID.
- `plugin_mastodon_fetch_status_context()` — line 4147 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_file_prestat()` — line 367 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 387 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_flatpress_to_mastodon()` — line 2417 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_fp_config()` — line 205 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 241 — Read a nested FlatPress configuration value.
- `plugin_mastodon_get_options()` — line 398 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_guess_subject()` — line 1293 — Guess a subject line from imported plain text.
- `plugin_mastodon_head()` — line 677 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_html_entity_decode()` — line 1335 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_http_build_query()` — line 3760 — Build an application/x-www-form-urlencoded query string.
- `plugin_mastodon_http_request()` — line 3797 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_http_request_multipart()` — line 3312 — Perform a multipart HTTP request.
- `plugin_mastodon_import_remote_comment()` — line 4410 — Import a remote Mastodon reply into FlatPress as a comment.
- `plugin_mastodon_import_remote_context_descendants()` — line 4482 — Import remote Mastodon replies from a fetched thread context.
- `plugin_mastodon_import_remote_entry()` — line 4310 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_instance_authority()` — line 613 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_instance_character_limit()` — line 4085 — Return the status character limit of the configured instance.
- `plugin_mastodon_instance_configuration()` — line 3132 — Load and cache the Mastodon instance configuration document.
- `plugin_mastodon_instance_media_description_limit()` — line 3175 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_media_limit()` — line 3162 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_url_reserved_length()` — line 3188 — Return the reserved Mastodon character budget used for each URL.
- `plugin_mastodon_io_append_file()` — line 285 — Append to a file via the FlatPress I/O layer.
- `plugin_mastodon_io_read_file()` — line 259 — Read a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_write_file()` — line 272 — Write a file through the FlatPress I/O layer when available.
- `plugin_mastodon_is_public_host()` — line 1944 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_lang_string()` — line 1439 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_limit_status_text()` — line 3234 — Truncate status text using Mastodon URL-budget rules.
- `plugin_mastodon_limit_text()` — line 2532 — Limit text to a maximum number of characters.
- `plugin_mastodon_list_local_entries()` — line 3651 — List local FlatPress entry identifiers.
- `plugin_mastodon_local_item_date_key()` — line 866 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_local_item_matches_sync_start()` — line 932 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_local_item_timestamp()` — line 3607 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_log()` — line 959 — Append a line to the plugin sync log.
- `plugin_mastodon_mastodon_api()` — line 3911 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 1716 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 2315 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_mastodon_json()` — line 3955 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_maybe_sync()` — line 4871 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_media_copy_tree()` — line 2668 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_media_delete_tree()` — line 2641 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_download()` — line 2986 — Download a remote media asset.
- `plugin_mastodon_media_guess_mime_type()` — line 2718 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_parse_tag_attributes()` — line 2754 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_media_prepare_directory()` — line 2625 — Ensure that a media directory exists.
- `plugin_mastodon_media_relative_to_absolute()` — line 2612 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_normalize_comment_parent_id()` — line 1232 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_normalize_head_username()` — line 574 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 827 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_normalize_instance_url()` — line 541 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_status_language()` — line 717 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_normalize_sync_start_date()` — line 776 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_sync_time()` — line 759 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_tag_list()` — line 1643 — Normalize a list of tag labels.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 802 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_oauth_scopes()` — line 133 — Return the OAuth scopes requested by the plugin.
- `plugin_mastodon_parse_http_response_headers()` — line 3688 — Parse raw HTTP response headers.
- `plugin_mastodon_parse_iso_datetime()` — line 1139 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 1157 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_photoswipe_plugin_active()` — line 1570 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_plain_text_from_bbcode()` — line 1986 — Convert FlatPress BBCode into plain text for Mastodon export.
- `plugin_mastodon_profile_url()` — line 643 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_public_comment_url()` — line 2300 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_public_comments_url()` — line 2272 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_entry_url()` — line 2245 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_url_for_mastodon()` — line 1967 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_register_app()` — line 3999 — Register the FlatPress application on the configured Mastodon instance.
- `plugin_mastodon_remote_media_description()` — line 2971 — Resolve the best description for a remote attachment.
- `plugin_mastodon_remote_media_source_url()` — line 2957 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_status_date_key()` — line 886 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_remote_status_image_attachments()` — line 2934 — Extract image attachments from a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 1211 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 942 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_remote_status_tags()` — line 1737 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_remote_status_timestamp()` — line 1179 — Resolve the best timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 1198 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 1911 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 1922 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_resolve_comment_reply_target()` — line 1271 — Resolve the remote reply target for a local comment export.
- `plugin_mastodon_response_error_message()` — line 3970 — Extract the most useful error message from an API response.
- `plugin_mastodon_run_sync()` — line 4812 — Run a full synchronization cycle.
- `plugin_mastodon_runtime_cache_clear()` — line 190 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_runtime_cache_get()` — line 144 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 172 — Store a value in the request-local plugin cache.
- `plugin_mastodon_safe_filename()` — line 2599 — Sanitize a file name for local storage.
- `plugin_mastodon_safe_path_component()` — line 2584 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_save_options()` — line 430 — Persist plugin options.
- `plugin_mastodon_secret_decode()` — line 510 — Decode a previously stored secret value.
- `plugin_mastodon_secret_encode()` — line 487 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_key()` — line 470 — Build the encryption key used for stored secrets.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 843 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_should_update_local_from_remote()` — line 818 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_state_comment_key()` — line 1049 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_get_comment_meta()` — line 1129 — Return mapping metadata for a local comment.
- `plugin_mastodon_state_get_entry_meta()` — line 1117 — Return mapping metadata for a local entry.
- `plugin_mastodon_state_normalize()` — line 1031 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_read()` — line 969 — Load the persisted runtime state from disk.
- `plugin_mastodon_state_set_comment_mapping()` — line 1092 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_set_entry_mapping()` — line 1064 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_write()` — line 1007 — Persist the runtime state to disk.
- `plugin_mastodon_status_text_length()` — line 3202 — Calculate the Mastodon-visible status length with instance URL budgeting.
- `plugin_mastodon_stream_context_request()` — line 3721 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 1699 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 1764 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_subject_line_is_noise()` — line 2028 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_sync_due()` — line 4790 — Determine whether the scheduled synchronization is currently due.
- `plugin_mastodon_sync_local_to_remote()` — line 4660 — Synchronize local FlatPress content to Mastodon.
- `plugin_mastodon_sync_remote_to_local()` — line 4601 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_tag_plugin_active()` — line 1536 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_timestamp_date_key()` — line 852 — Convert a Unix timestamp into a stable UTC date key.
- `plugin_mastodon_update_status()` — line 4184 — Update an existing Mastodon status.
- `plugin_mastodon_upload_media_items()` — line 3498 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_verify_credentials()` — line 4068 — Verify the currently configured access token.
- `plugin_mastodon_wait_for_media_attachment()` — line 3452 — Poll an asynchronously processed Mastodon media attachment until it is ready or times out.
- `setup()` — line 4922 — Register the Mastodon admin panel template and assign plugin data to Smarty.
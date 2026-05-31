# 07 – PHP Function Organigram

## Purpose of this document

This document gives developers a structured overview of the Mastodon plugin PHP functions.

The file intentionally combines two views:

- a human-oriented organigram that explains responsibilities, call paths and maintenance rules;
- a generated function catalog with current line references that is checked against `fp-plugins/mastodon/plugin.mastodon.php`.

The plugin currently contains **401** callable functions/methods.

- **398** top-level plugin functions
- **6** admin action methods implemented with the shared method names `setup()`, `main()` or `onsubmit()`

## Function count

The count above is derived from the current PHP source and verified by `developer-docs/check-consistency.php`.
Duplicate admin method names such as `setup()`, `main()` and `onsubmit()` are implemented by separate admin action classes, while the generated catalog records the current effective source line for each shared method name.

## Scope

| Topic          | Current behavior in the current split-state stand                                                                                                        |
| -------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| State files    | `state.json` keeps global mappings, cursors, queues and `comments_remote`; per-entry comment mappings live below `state-comments/`. |
| Comment shards | Each entry has one shard file so scheduled/manual sync paths can load only the required comment workset.                            |
| Reverse index  | `comments_remote` remains global so remote reply imports can detect duplicates without scanning every shard.                        |
| Migration      | Legacy inline comments are migrated to shards with a `state.json.migration-backup-*` safety copy.                                   |
| Repair         | Admin and CLI maintenance can diagnose shard metadata and rebuild repairable indexes from shard files.                              |

The focus is the Mastodon plugin PHP code. Template and language files are mentioned when they are part of an admin flow, but the callable catalog below is generated from `plugin.mastodon.php`.

## Mastodon API Endpoints

| Endpoint                                      | Main helper/function area                   | Use                                                                           |
| --------------------------------------------- | ------------------------------------------- | ----------------------------------------------------------------------------- |
| `GET /.well-known/oauth-authorization-server` | `plugin_mastodon_oauth_server_metadata()`   | Discover OAuth scopes and prefer the narrow `profile` scope when available.   |
| `POST /api/v1/apps`                           | `plugin_mastodon_register_app()`            | Register the FlatPress application with compatible scopes.                    |
| `GET /oauth/authorize`                        | `plugin_mastodon_build_authorize_url()`     | Send the administrator to Mastodon OAuth authorization.                       |
| `POST /oauth/token`                           | `plugin_mastodon_exchange_code_for_token()` | Exchange the authorization code for an access token.                          |
| `GET /api/v1/accounts/verify_credentials`     | `plugin_mastodon_verify_credentials()`      | Validate the configured account and cache account metadata.                   |
| `GET /api/v1/accounts/:id/statuses`           | `plugin_mastodon_fetch_account_statuses()`  | Discover statuses already posted by the authenticated account.                |
| `GET /api/v1/statuses/:id/context`            | `plugin_mastodon_fetch_status_context()`    | Import replies and refresh known synchronized threads.                        |
| `GET /api/v1/statuses/:id`                    | `plugin_mastodon_fetch_status()`            | Verify one remote status or deletion target.                                  |
| `POST /api/v1/statuses`                       | `plugin_mastodon_create_status()`           | Create entry/comment statuses.                                                |
| `PUT /api/v1/statuses/:id`                    | `plugin_mastodon_update_status()`           | Update changed entry/comment statuses when possible.                          |
| `DELETE /api/v1/statuses/:id`                 | `plugin_mastodon_delete_status()`           | Delete remote statuses; version-aware `delete_media` handling is centralized. |
| `GET /api/v2/instance`                        | `plugin_mastodon_upload_media_items()`      | Cache instance limits and feature support.                                    |
| `POST /api/v2/media`                          | `plugin_mastodon_upload_media_items()`      | Upload media before final status creation/update.                             |
| `GET /api/v1/media/:id`                       | `plugin_mastodon_fetch_media_attachment()`  | Poll asynchronous media processing.                                           |
| `DELETE /api/v1/media/:id`                    | `plugin_mastodon_delete_media_attachment()` | Clean up uploaded media that is not attached to a final status.               |

The local state split does not change these API boundaries. It changes how the plugin stores and loads local mappings before and after calling the same Mastodon API helpers.

## High-level call flow

### Frontend and scheduler entry points

```mermaid
flowchart TD
	Front[FlatPress frontend/admin request] --> Head[plugin_mastodon_head]
	Head --> Maybe[plugin_mastodon_maybe_sync]
	Maybe --> Due{scheduled content sync due?}
	Due -- no --> Fast[read scheduler-state only]
	Due -- yes --> Run[plugin_mastodon_run_sync false]
	Run --> EmptyState[read main state with empty comment workset]
	EmptyState --> Workset[load scheduled/dirty/known-thread shards]
	Workset --> LocalRemote[local to remote export]
	Workset --> RemoteLocal[remote to local import and thread refresh]
	Run --> Scheduler[write compact scheduler-state]
```

Scheduled requests must keep normal page rendering cheap. They therefore prefer `scheduler-state.json` and only enter full synchronization when the configured due checks pass. In the current split-state stand the scheduled sync opens the main state without all comment shards, then loads only the entry shards selected by the automatic window, dirty queues and known-thread rotation.

### Local → remote export path

```mermaid
flowchart LR
	Entries[FlatPress entries/comments] --> Collect[collect entry files and dirty queues]
	Collect --> Shards[load affected comment shards]
	Shards --> Build[build entry/comment status text and media]
	Build --> Api[create/update/delete Mastodon statuses]
	Api --> State[update entries, comments_remote and changed shards]
	State --> Write[atomic shard/main-state write with last_error support]
```

The export path is expected to behave the same for users as before the split-state change: changed FlatPress entries and comments are posted or updated on Mastodon. The implementation difference is that comment mappings are loaded entry-by-entry instead of as one monolithic `comments` array.

### Remote → local import path

```mermaid
flowchart LR
	Known[Known remote statuses] --> Context[fetch status context]
	Context --> Desc[process descendants]
	Desc --> Reverse[check global comments_remote]
	Reverse --> Shard[load target entry shard]
	Shard --> Import[import or update local FlatPress comment/entry]
	Import --> Persist[update reverse index and shard]
```

`comments_remote` remains global by design. It gives the remote-import path a fast duplicate check before the code loads the affected entry shard. This avoids scanning all shard files when refreshing a known thread.

### Deletion reconciliation path

```mermaid
flowchart TD
	Start[plugin_mastodon_run_deletion_sync] --> EntryCursor[entry deletion cursor]
	EntryCursor --> EntryDeletes[entry status deletion checks]
	EntryDeletes --> CommentCursor[comment shard cursor]
	CommentCursor --> LoadShard[load one entry shard]
	LoadShard --> CommentDeletes[comment deletion checks]
	CommentDeletes --> Resume[store entry/comment cursor for continuation]
	Resume --> Finish[write state and scheduler summary]
```

Deletion synchronization changed more than the other sync paths because it now iterates over entry shards. The important invariant is that the cursor can resume both between entries and within one comment shard.

### FlatPress Core post-success dirty-tracking hooks

```mermaid
flowchart LR
	EntrySave[entry_saved] --> DirtyEntry[mark dirty entry without full comment load]
	EntryDelete[entry_deleted] --> EntryDeleteState[mark deletion and affected shard state]
	CommentSave[comment_saved] --> ParentShard[load parent entry shard]
	CommentDelete[comment_deleted] --> CommentDeletion[mark comment deletion in parent shard]
	DirtyEntry --> StateWrite[write state/shards]
	EntryDeleteState --> StateWrite
	ParentShard --> StateWrite
	CommentDeletion --> StateWrite
```

The dirty hooks must stay fast because they run during normal FlatPress editing. Stand 36 keeps these hooks shard-aware so saving one comment does not force all historical comment mappings into memory.

### Admin panel and maintenance flow

```mermaid
flowchart TD
	Main[Visible Mastodon plugin settings] --> Button[State maintenance button]
	Button --> Maint[Hidden mastodon_maintenance action]
	Maint --> Template[admin.plugin.mastodon.maintenance.tpl]
	Template --> Diagnose[plugin_mastodon_state_diagnose_comment_shards]
	Template --> Repair[plugin_mastodon_state_repair_comment_shards]
	CLI[mastodon-state-cli.php] --> Diagnose
	CLI --> Repair
	Diagnose --> Rows[diagnostic rows and shared errorlist messages]
	Repair --> Rows
```

The regular settings page keeps configuration, authorization and manual sync controls visible. The advanced state maintenance actions live on the separate `mastodon_maintenance` action and are also reachable by CLI.

## A. Entry points and admin integration

This group contains the functions and shared admin action methods that FlatPress calls directly or indirectly. It covers frontend metadata, scheduled entry checks, admin assignment, the separate maintenance panel, state-maintenance result formatting and the CLI maintenance command.

The hidden `mastodon_maintenance` panel must have its language/panelstrings alias in every language file before FlatPress builds `$panelstrings`; the runtime fallback remains only the second safety layer.

- `plugin_mastodon_head()` — line 2241 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_admin_language_strings()` — line 5973 — Return Mastodon admin-language strings with guaranteed maintenance fallbacks.
- `plugin_mastodon_sync_due()` — line 11777 — Determine whether the scheduled synchronization is currently due.
- `plugin_mastodon_maybe_sync()` — line 11893 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_admin_boolean_label()` — line 11925 — Return a localized yes/no/unknown label for admin diagnostics.
- `plugin_mastodon_admin_add_info_row()` — line 11940 — Add one admin diagnostics row when the value is available.
- `plugin_mastodon_instance_registration_summary()` — line 11961 — Summarize the registration state advertised by the Mastodon instance.
- `plugin_mastodon_admin_instance_info_rows()` — line 11987 — Build the rows for the admin instance-information diagnostics table.
- `plugin_mastodon_admin_panel_link()` — line 12060 — Build a local admin-panel link for Mastodon plugin pages.
- `plugin_mastodon_admin_is_maintenance_request()` — line 12080 — Decide whether the current request targets the dedicated Mastodon maintenance page.
- `plugin_mastodon_admin_empty_state_maintenance_result()` — line 12088 — Return an empty admin maintenance result container.
- `plugin_mastodon_admin_state_maintenance_result()` — line 12104 — Convert comment-shard diagnostics into template-friendly admin rows.
- `plugin_mastodon_cli_comment_shard_maintenance()` — line 12141 — Print command-line comment-shard diagnostics or perform a repair.
- `plugin_mastodon_admin_assign()` — line 12175 — Assign plugin data to Smarty for the admin panel.
- `setup()` — line 12315 — Register the active Mastodon admin template and assign plugin data to Smarty.
- `main()` — line 12320 — Keep the FlatPress admin panel lifecycle stable without additional processing.
- `onsubmit()` — line 12324 — Process submitted Mastodon admin actions for the active admin panel.

## B. Defaults, configuration, secrets, and centralized FlatPress feature toggles

This group owns normalized option values, stored secrets, instance snapshots, OAuth scope negotiation, FlatPress time conversion and feature toggles. These helpers should not load comment shards or turn a settings page into a full state scan.

- `plugin_mastodon_default_options()` — line 138 — Return the default plugin option values.
- `plugin_mastodon_clear_saved_instance_info()` — line 170 — Remove any stored Mastodon instance information from the plugin options.
- `plugin_mastodon_compact_instance_document()` — line 189 — Reduce a live Mastodon instance document to the stable subset that is useful for the plugin.
- `plugin_mastodon_saved_instance_document()` — line 394 — Read a previously stored Mastodon instance snapshot from the plugin options.
- `plugin_mastodon_store_instance_document()` — line 439 — Persist a compact Mastodon instance snapshot inside the plugin configuration.
- `plugin_mastodon_store_instance_error()` — line 485 — Persist the latest instance-information refresh error for the admin diagnostics view.
- `plugin_mastodon_refresh_instance_information()` — line 501 — Force a live refresh of the Mastodon instance information and persist the compact snapshot.
- `plugin_mastodon_default_content_stats()` — line 521 — Return the default counters for the last content synchronization.
- `plugin_mastodon_default_deletion_stats()` — line 538 — Return the default counters for the last deletion synchronization.
- `plugin_mastodon_default_state()` — line 551 — Return the default runtime state structure.
- `plugin_mastodon_oauth_legacy_scopes()` — line 587 — Return the legacy OAuth scopes used before scope discovery was added.
- `plugin_mastodon_oauth_profile_scopes()` — line 595 — Return the stricter OAuth scopes preferred on current Mastodon instances.
- `plugin_mastodon_oauth_server_metadata()` — line 604 — Fetch OAuth authorization server metadata for the configured Mastodon instance.
- `plugin_mastodon_oauth_supported_scopes()` — line 623 — Return the OAuth scopes supported by the configured Mastodon instance, if discoverable.
- `plugin_mastodon_oauth_scope_supported()` — line 660 — Determine whether the configured Mastodon instance advertises support for a scope.
- `plugin_mastodon_oauth_preferred_scopes()` — line 681 — Return the preferred OAuth scopes for the configured Mastodon instance.
- `plugin_mastodon_oauth_scopes()` — line 698 — Return the OAuth scopes that the currently registered app may safely request.
- `plugin_mastodon_fp_config()` — line 775 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 811 — Read a nested FlatPress configuration value.
- `plugin_mastodon_fp_timeoffset_seconds()` — line 827 — Return the configured FlatPress time offset in seconds.
- `plugin_mastodon_fp_timeoffset_hours()` — line 839 — Return the configured FlatPress time offset in whole hours.
- `plugin_mastodon_fp_timeoffset_label()` — line 847 — Format the configured FlatPress time offset as a UTC label for admin users.
- `plugin_mastodon_sync_time_to_minutes()` — line 877 — Convert a HH:MM time value into minutes after midnight.
- `plugin_mastodon_minutes_to_sync_time()` — line 888 — Convert minutes after midnight into a normalized HH:MM time value.
- `plugin_mastodon_sync_time_utc_to_local()` — line 904 — Convert the stored UTC synchronization time to the FlatPress-local admin time.
- `plugin_mastodon_sync_time_local_to_utc()` — line 915 — Convert a FlatPress-local admin synchronization time back to stored UTC time.
- `plugin_mastodon_format_admin_datetime()` — line 926 — Format a stored UTC timestamp for the admin panel using FlatPress local time and configured formats.
- `plugin_mastodon_get_options()` — line 1883 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_save_options()` — line 1942 — Persist plugin options.
- `plugin_mastodon_secret_key()` — line 2034 — Build the encryption key used for stored secrets.
- `plugin_mastodon_secret_encode()` — line 2051 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_decode()` — line 2074 — Decode a previously stored secret value.
- `plugin_mastodon_normalize_instance_url()` — line 2105 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_head_username()` — line 2138 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_instance_authority()` — line 2177 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_profile_url()` — line 2207 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_fediverse_creator_value()` — line 2223 — Build the fediverse creator meta value.
- `plugin_mastodon_normalize_status_language()` — line 2281 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_configured_status_language()` — line 2303 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_normalize_sync_time()` — line 2323 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_sync_start_date()` — line 2340 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_scheduled_window_days()` — line 2366 — Normalize the automatic scheduled synchronization window.
- `plugin_mastodon_scheduled_window_choices()` — line 2378 — Return the admin radio choices for the scheduled synchronization window.
- `plugin_mastodon_normalize_boolean_option()` — line 2391 — Normalize a boolean-like option value to the stored string representation.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 2407 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_should_update_local_from_remote()` — line 2416 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 2425 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 2434 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_normalize_quote_imported_reply_parent()` — line 2443 — Normalize the toggle that controls whether imported Mastodon replies should quote the replied-to comment in FlatPress.
- `plugin_mastodon_should_quote_imported_reply_parent()` — line 2452 — Check whether imported Mastodon replies should include a quote of the replied-to comment.
- `plugin_mastodon_normalize_old_thread_reply_check()` — line 2461 — Normalize the toggle that enables rotating context checks for known synchronized Mastodon threads.
- `plugin_mastodon_should_check_old_thread_replies()` — line 2470 — Check whether known synchronized Mastodon threads should be checked for replies in rotating batches.
- `plugin_mastodon_normalize_delete_sync_enabled()` — line 2479 — Normalize the toggle that enables the follow-up deletion synchronization.
- `plugin_mastodon_should_run_deletion_sync()` — line 2488 — Check whether the follow-up deletion synchronization is enabled.
- `plugin_mastodon_enabled_plugin_state()` — line 6059 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_tag_plugin_active()` — line 6124 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_bbcode_plugin_active()` — line 6141 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_photoswipe_plugin_active()` — line 6158 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_audiovideo_plugin_active()` — line 6175 — Determine whether the Audio/Video plugin is active for the current FlatPress request.
- `plugin_mastodon_emoticons_plugin_active()` — line 6192 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_companion_plugins_status()` — line 6207 — Return the status of companion FlatPress plugins used for the full Mastodon feature set.

## C. Caching, filesystem helpers, logging, and persisted state

This group owns request-local caches, FlatPress file I/O wrappers, log rotation, scheduler summaries, sync cooldown guards, state migration, per-entry comment shards, repair/diagnosis, dirty queues, tombstones, pending rechecks and durable state-write error reporting.

- `plugin_mastodon_runtime_cache_get()` — line 718 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 742 — Store a value in the request-local plugin cache.
- `plugin_mastodon_runtime_cache_clear()` — line 760 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_io_read_file()` — line 960 — Read a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_read_file_uncached()` — line 976 — Read a file without FlatPress request-local caches.
- `plugin_mastodon_file_permissions_mode()` — line 994 — Return the FlatPress file permission mode for runtime files.
- `plugin_mastodon_apply_file_permissions()` — line 1003 — Apply FlatPress FILE_PERMISSIONS to a runtime file.
- `plugin_mastodon_io_write_file()` — line 1016 — Write a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_append_file()` — line 1037 — Append to a file without re-reading and rewriting the complete payload.
- `plugin_mastodon_log_max_bytes()` — line 1077 — Return the maximum sync.log size before rotation.
- `plugin_mastodon_log_rotate_files()` — line 1088 — Return the number of retained sync.log rotation files.
- `plugin_mastodon_io_rotate_file_if_needed()` — line 1101 — Rotate an append-only log file when the next append would exceed the size cap.
- `plugin_mastodon_apcu_enabled()` — line 1140 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_cache_key()` — line 1149 — Build the namespaced APCu key used by this plugin.
- `plugin_mastodon_apcu_fetch()` — line 1163 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 1178 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_delete()` — line 1191 — Delete a value from APCu using the FlatPress namespace key builder.
- `plugin_mastodon_state_fallback_key()` — line 1210 — Return the legacy APCu key that used to hold a full last-known-good state fallback.
- `plugin_mastodon_state_fallback_store()` — line 1219 — Remove the legacy full-state APCu fallback instead of storing large mapping arrays.
- `plugin_mastodon_state_fallback_read()` — line 1228 — Full-state APCu fallback is intentionally disabled to avoid multi-MiB APCu entries.
- `plugin_mastodon_sync_guard_kind()` — line 1238 — Normalize the cooldown guard kind.
- `plugin_mastodon_sync_guard_apcu_key()` — line 1248 — Return the APCu key used for a sync cooldown guard.
- `plugin_mastodon_sync_guard_entry_active()` — line 1258 — Return true when one guard entry is still active.
- `plugin_mastodon_sync_guard_apcu_store()` — line 1269 — Store one cooldown guard in APCu.
- `plugin_mastodon_sync_guard_file_read()` — line 1282 — Read and prune the file-backed cooldown guard.
- `plugin_mastodon_sync_guard_file_write()` — line 1309 — Write the file-backed cooldown guard.
- `plugin_mastodon_sync_guard_active()` — line 1330 — Return true when a recent scheduled sync/deletion pass should cool down.
- `plugin_mastodon_sync_guard_mark()` — line 1355 — Mark a short cooldown guard for a scheduled sync/deletion pass.
- `plugin_mastodon_sync_guard_clear()` — line 1376 — Clear one sync cooldown guard.
- `plugin_mastodon_file_prestat()` — line 1852 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 1872 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_ensure_state_dir()` — line 2813 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_ensure_comment_shard_dir()` — line 2821 — Ensure that the comment-shard directory exists.
- `plugin_mastodon_state_is_entry_id()` — line 2830 — Return whether a string looks like a FlatPress entry id.
- `plugin_mastodon_state_entry_id_from_comment_key()` — line 2839 — Return the entry id portion from a compound comment-state key.
- `plugin_mastodon_state_comment_shard_file()` — line 2850 — Return the shard path for an entry's comment mappings.
- `plugin_mastodon_state_comment_shard_relative_path()` — line 2865 — Return the relative shard path used in state metadata.
- `plugin_mastodon_state_write_json_file()` — line 2879 — Write a JSON payload atomically when possible.
- `plugin_mastodon_state_write_lock_acquire()` — line 2918 — Acquire the short state-write lock used for multi-file state mutations.
- `plugin_mastodon_state_write_lock_release()` — line 2941 — Release a state-write lock handle.
- `plugin_mastodon_json_decode_string_at()` — line 2954 — Decode a JSON string literal starting at the current offset.
- `plugin_mastodon_json_skip_ws()` — line 2991 — Skip JSON whitespace in a string scanner.
- `plugin_mastodon_json_top_level_property_bounds()` — line 3008 — Find a top-level JSON property value in an object payload without decoding the whole object.
- `plugin_mastodon_state_create_migration_backup()` — line 3109 — Create a timestamped backup of the legacy main state before an inline-to-shard migration mutates it.
- `plugin_mastodon_state_migrate_inline_comments_to_shards()` — line 3131 — Iterate a legacy inline comments object and persist per-entry shards without building one giant comments array.
- `plugin_mastodon_state_try_streaming_legacy_migration()` — line 3270 — Migrate legacy inline comment mappings to per-entry shards without decoding the high-volume comments array.
- `plugin_mastodon_state_pending_recheck_entry_ids()` — line 3321 — Return the entry ids referenced by pending comment rechecks.
- `plugin_mastodon_state_unload_comment_shard_from_memory()` — line 3342 — Drop one loaded comment shard from a partial state's in-memory working set.
- `plugin_mastodon_state_group_comments_by_entry()` — line 3367 — Group comment mappings by their parent entry id.
- `plugin_mastodon_state_read_comment_shard()` — line 3395 — Load one per-entry comment shard.
- `plugin_mastodon_state_write_comment_shards()` — line 3430 — Write all per-entry comment shards.
- `plugin_mastodon_state_main_payload()` — line 3488 — Build the main state payload without high-volume inline comment mappings.
- `plugin_mastodon_state_load_comment_shards()` — line 3507 — Load comment shards into a state array.
- `plugin_mastodon_state_comment_shards_partial()` — line 3547 — Return whether a state array only contains a subset of comment shards.
- `plugin_mastodon_state_loaded_comment_entry_ids()` — line 3556 — Return the entry ids whose comment shards are loaded in a partial state.
- `plugin_mastodon_state_comment_entry_loaded()` — line 3575 — Check whether one entry's comment shard is loaded into the state array.
- `plugin_mastodon_state_mark_comment_entry_loaded()` — line 3589 — Mark one entry's comment shard as loaded in a partial state.
- `plugin_mastodon_state_load_comment_shard_into()` — line 3609 — Load one entry comment shard into a partial state when needed.
- `plugin_mastodon_state_comment_shard_entry_ids()` — line 3630 — Return entry ids with known comment shards.
- `plugin_mastodon_state_load_all_comment_shards_into()` — line 3648 — Load every comment shard into a state array for full-map maintenance paths.
- `plugin_mastodon_state_comment_shard_files()` — line 3660 — Return all comment shard files currently present on disk keyed by entry id.
- `plugin_mastodon_state_diagnose_comment_shards()` — line 3712 — Scan shard files and compare them with the main-state metadata and reverse comment index.
- `plugin_mastodon_state_repair_comment_shards()` — line 3827 — Rebuild the main shard metadata and global comments_remote reverse index from shard files.
- `plugin_mastodon_state_cleanup_stale_comment_shards()` — line 3867 — Remove shard files that are no longer referenced by the main state.
- `plugin_mastodon_state_recover_from_comment_shards()` — line 3924 — Return a recovered state if comment shards were written but the main state is missing.
- `plugin_mastodon_log()` — line 3989 — Append a line to the plugin sync log.
- `plugin_mastodon_log_skip()` — line 4004 — Aggregate high-volume skip messages until the current sync phase ends.
- `plugin_mastodon_log_flush_skip_summaries()` — line 4034 — Flush aggregated skip log messages.
- `plugin_mastodon_state_read()` — line 4064 — Load the persisted runtime state from disk.
- `plugin_mastodon_scheduler_state_default()` — line 4109 — Return an empty scheduler state derived from the full default state.
- `plugin_mastodon_scheduler_source_signature()` — line 4130 — Return the current stat-based signature of the full state file.
- `plugin_mastodon_scheduler_state_normalize()` — line 4139 — Normalize a scheduler summary without touching full mapping arrays.
- `plugin_mastodon_scheduler_state_from_state()` — line 4177 — Build the lightweight scheduler summary from a full runtime state.
- `plugin_mastodon_scheduler_state_write()` — line 4197 — Persist the lightweight scheduler state. Failure only disables the request-time optimization.
- `plugin_mastodon_scheduler_state_decode_fresh()` — line 4223 — Decode a scheduler-state JSON payload only when it matches the current full-state signature.
- `plugin_mastodon_scheduler_state_read()` — line 4242 — Load the lightweight scheduler summary and rebuild it conservatively when stale.
- `plugin_mastodon_state_write()` — line 4288 — Persist the runtime state to disk.
- `plugin_mastodon_normalize_deletions_pending_scope()` — line 4345 — Normalize the pending deletion scope marker.
- `plugin_mastodon_state_normalize()` — line 4358 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_comment_key()` — line 4409 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_comment_mappings()` — line 4418 — Return the loaded comment mapping array from a state object.
- `plugin_mastodon_state_set_comment_mappings()` — line 4436 — Replace the loaded comment mapping array in a state object.
- `plugin_mastodon_state_comment_remote_mappings()` — line 4454 — Return the global remote-comment reverse index.
- `plugin_mastodon_state_set_comment_remote_mappings()` — line 4480 — Replace the global remote-comment reverse index in a state object.
- `plugin_mastodon_state_comment_shard_entries()` — line 4506 — Return the main-state metadata for per-entry comment shards.
- `plugin_mastodon_state_set_comment_shard_entries()` — line 4525 — Replace the main-state metadata for per-entry comment shards.
- `plugin_mastodon_state_write_error_set()` — line 4546 — Store a state write error for callers that need a last_error message even when persistence fails.
- `plugin_mastodon_state_write_error_clear()` — line 4554 — Clear the last state write error marker.
- `plugin_mastodon_state_write_last_error()` — line 4562 — Return the last state write error marker from the current request.
- `plugin_mastodon_state_write_with_last_error()` — line 4572 — Persist state and copy a failed write reason into the caller-visible last_error field.
- `plugin_mastodon_state_set_entry_mapping()` — line 4602 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_comment_mapping()` — line 4641 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_remove_entry_mapping()` — line 4683 — Remove the mapping between a local entry and a remote status.
- `plugin_mastodon_state_remove_comment_mapping()` — line 4705 — Remove the mapping between a local comment and a remote status.
- `plugin_mastodon_state_set_dirty_entry()` — line 4739 — Add an older changed entry to the persistent dirty queue.
- `plugin_mastodon_state_remove_dirty_entry()` — line 4759 — Remove an entry from the dirty queue.
- `plugin_mastodon_state_has_dirty_entry()` — line 4772 — Check whether an entry is queued for synchronization although it is outside the scheduled window.
- `plugin_mastodon_state_set_dirty_comment()` — line 4785 — Add an older changed comment to the persistent dirty queue.
- `plugin_mastodon_state_remove_dirty_comment()` — line 4808 — Remove a comment from the dirty queue.
- `plugin_mastodon_state_has_dirty_comment()` — line 4822 — Check whether a comment is queued for synchronization although it is outside the scheduled window.
- `plugin_mastodon_local_write_guard_enter()` — line 4831 — Increase the local-write guard depth while the plugin mirrors remote Mastodon data into FlatPress.
- `plugin_mastodon_local_write_guard_leave()` — line 4842 — Decrease the local-write guard depth after a plugin-owned FlatPress write.
- `plugin_mastodon_local_write_guard_active()` — line 4854 — Return whether FlatPress write hooks are currently triggered by Mastodon remote mirroring.
- `plugin_mastodon_dirty_tracking_options()` — line 4862 — Check whether dirty tracking should persist state for a local FlatPress write hook.
- `plugin_mastodon_on_entry_saved()` — line 4881 — Mark a local FlatPress entry write as dirty for a later Mastodon sync.
- `plugin_mastodon_on_entry_deleted()` — line 4930 — Mark a local FlatPress entry deletion for the Mastodon deletion sync.
- `plugin_mastodon_on_comment_saved()` — line 4957 — Mark a local FlatPress comment write as dirty for a later Mastodon sync.
- `plugin_mastodon_on_comment_deleted()` — line 5015 — Mark a local FlatPress comment deletion for the Mastodon deletion sync.
- `plugin_mastodon_state_get_entry_meta()` — line 5040 — Return mapping metadata for a local entry.
- `plugin_mastodon_state_set_entry_media_meta()` — line 5054 — Persist media metadata for a synchronized local entry.
- `plugin_mastodon_state_entry_remote_media()` — line 5083 — Return sanitized remote-media descriptors stored inside one entry mapping.
- `plugin_mastodon_state_entry_media_attachment_signature()` — line 5113 — Return the stored attachment-signature for one entry mapping.
- `plugin_mastodon_state_entry_media_description_signature()` — line 5123 — Return the stored description-signature for one entry mapping.
- `plugin_mastodon_state_get_comment_meta()` — line 5135 — Return mapping metadata for a local comment.
- `plugin_mastodon_state_set_comment_tombstone()` — line 5150 — Store a tombstone that blocks re-importing a deleted remote comment status.
- `plugin_mastodon_state_has_comment_tombstone()` — line 5170 — Check whether one remote Mastodon comment status was tombstoned locally.
- `plugin_mastodon_protect_locally_deleted_exported_comments()` — line 5181 — Protect locally deleted exported FlatPress comments from stale Mastodon re-imports before deletion sync runs.
- `plugin_mastodon_reattach_local_comment_to_entry_status()` — line 5231 — Reattach one imported local comment to the synchronized entry status after its remote parent reply disappeared.
- `plugin_mastodon_state_remove_pending_comment_remote_recheck()` — line 5291 — Remove one pending descendant recheck marker.
- `plugin_mastodon_state_get_pending_comment_remote_recheck()` — line 5305 — Return one pending descendant recheck marker.
- `plugin_mastodon_state_set_pending_comment_remote_recheck()` — line 5320 — Mark one local comment for follow-up verification after an ancestor status disappeared remotely.
- `plugin_mastodon_state_set_deletions_pending()` — line 5346 — Update the runtime marker that tells the scheduler whether another deletion follow-up request is required.
- `plugin_mastodon_deletion_sync_due()` — line 5360 — Determine whether a pending deletion synchronization may start now.
- `plugin_mastodon_state_has_comment_recheck_scope()` — line 5388 — Check whether the current deletion follow-up request should focus on pending descendant reply rechecks only.
- `plugin_mastodon_build_comment_remote_child_index()` — line 5397 — Build an index of mapped local comments grouped by their direct remote parent status.
- `plugin_mastodon_queue_comment_descendant_remote_rechecks()` — line 5428 — Queue the direct mapped local children of one deleted remote comment for additional verification passes.
- `plugin_mastodon_process_pending_comment_remote_rechecks()` — line 5467 — Process queued descendant reply rechecks using a small FIFO queue.

## D. Date, timestamp, visibility, and threading helpers

This group decides whether local and remote items belong to the configured synchronization start date and automatic content window. It also resolves comment parentage and reply targets without loading unrelated comment shards.

- `plugin_mastodon_timestamp_to_flatpress_time()` — line 861 — Convert a real Unix timestamp into FlatPress's offset-adjusted timestamp model.
- `plugin_mastodon_timestamp_date_key()` — line 2497 — Convert a FlatPress-adjusted timestamp into a stable date key.
- `plugin_mastodon_local_item_date_key()` — line 2511 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_remote_status_date_key()` — line 2534 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_datetime_date_key()` — line 2560 — Normalize a date/datetime string to a sync-start date key.
- `plugin_mastodon_date_matches_sync_start()` — line 2584 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_scheduled_window_start_date()` — line 2602 — Return the FlatPress-local date key that starts the automatic scheduled sync window.
- `plugin_mastodon_date_matches_content_window()` — line 2619 — Determine whether a date is inside the manual lower bound and, for scheduled runs, the automatic window.
- `plugin_mastodon_local_item_matches_sync_start()` — line 2641 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_local_item_matches_content_window()` — line 2653 — Determine whether a local FlatPress item is inside the active content synchronization window.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 2663 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_remote_status_matches_content_window()` — line 2674 — Determine whether a remote Mastodon status is inside the active content synchronization window.
- `plugin_mastodon_mapping_matches_sync_start()` — line 2685 — Determine whether a synchronized mapping should participate in the deletion follow-up for the current sync start date.
- `plugin_mastodon_mapping_effective_date_key()` — line 2733 — Return the most stable date key available for a synchronized mapping.
- `plugin_mastodon_mapping_matches_deletion_lookup_window()` — line 2774 — Determine whether an existing local mapping should be remotely checked for deletion in this run.
- `plugin_mastodon_mapping_keys_after_cursor()` — line 2795 — Return mapping keys ordered after a saved deletion cursor.
- `plugin_mastodon_parse_iso_datetime()` — line 5577 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 5595 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_remote_status_timestamp()` — line 5617 — Resolve the best timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 5636 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 5649 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_comment_parent_fields()` — line 5661 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_normalize_comment_parent_id()` — line 5670 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 5687 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_resolve_comment_reply_target()` — line 5709 — Resolve the remote reply target for a local comment export.
- `plugin_mastodon_local_comment_parent_export_pending()` — line 5734 — Determine whether a local parent comment should be exported before its child reply.
- `plugin_mastodon_list_local_comment_ids()` — line 5762 — List local FlatPress comment identifiers by scanning the entry comment directory directly.

## E. Text, URLs, language strings, tags, emojis, and BBCode/HTML conversion

This group converts between FlatPress text/BBCode/HTML and Mastodon-ready plain text or FlatPress BBCode. It also handles localized strings, public URLs, FlatPress tags, Mastodon hashtags and Emoticons shortcode conversion.

- `plugin_mastodon_guess_subject()` — line 5799 — Guess a subject line from imported plain text.
- `plugin_mastodon_html_entity_decode()` — line 5841 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_blog_base_url()` — line 5850 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_extract_url_token()` — line 5886 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_absolute_url()` — line 5903 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_lang_string()` — line 5945 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_normalize_tag_list()` — line 6255 — Normalize a list of tag labels.
- `plugin_mastodon_extract_flatpress_tags()` — line 6286 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 6311 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 6328 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_remote_status_tags()` — line 6349 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 6376 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 6439 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 6452 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 6465 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 6519 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_prepare_emoticons_for_mastodon()` — line 6534 — Convert FlatPress emoticon shortcodes to Mastodon-safe Unicode glyphs when the plugin is active.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 6547 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_is_public_host()` — line 6569 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_public_url_for_mastodon()` — line 6592 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_plain_text_from_bbcode()` — line 6611 — Convert FlatPress BBCode into plain text for Mastodon export.
- `plugin_mastodon_subject_line_is_noise()` — line 6655 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_domains_match()` — line 6683 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_cleanup_imported_text()` — line 6697 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_dom_children_to_flatpress()` — line 6751 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 6769 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_public_entry_url()` — line 6878 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_comments_url()` — line 6905 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_comment_url()` — line 6933 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 6948 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_flatpress_to_mastodon()` — line 7050 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_limit_text()` — line 7167 — Limit text to a maximum number of characters.

## F. Local content access, media processing, hashing, and export ordering

This group reads local entries/comments, handles local and remote media descriptors, computes change signatures and builds efficient direct scanner candidate lists for scheduled and full synchronization paths.

- `plugin_mastodon_entry_hash()` — line 7189 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_comment_hash()` — line 7201 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_safe_path_component()` — line 7219 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_safe_filename()` — line 7234 — Sanitize a file name for local storage.
- `plugin_mastodon_normalize_media_relative_path()` — line 7247 — Normalize a FlatPress media path relative to fp-content.
- `plugin_mastodon_media_relative_to_absolute()` — line 7270 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_media_prepare_directory()` — line 7283 — Ensure that a media directory exists.
- `plugin_mastodon_media_delete_tree()` — line 7299 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_copy_tree()` — line 7326 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_bbcode_attr_escape()` — line 7364 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_bbcode_text_escape()` — line 7376 — Escape plain text embedded between BBCode tags.
- `plugin_mastodon_media_guess_mime_type()` — line 7398 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_type_from_mime()` — line 7465 — Return a stable FlatPress/Mastodon media family for a MIME type.
- `plugin_mastodon_extension_from_mime_type()` — line 7499 — Return an appropriate file extension for a MIME type.
- `plugin_mastodon_instance_supported_media_mime_types()` — line 7546 — Return instance-advertised supported media MIME types.
- `plugin_mastodon_instance_media_size_limit()` — line 7566 — Return the configured byte-size limit for a media family, or 0 if unknown.
- `plugin_mastodon_validate_local_media_item()` — line 7593 — Validate a local media item against known instance upload limits.
- `plugin_mastodon_media_parse_tag_attributes()` — line 7620 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_media_description_from_bbcode_content()` — line 7651 — Normalize optional AudioVideo BBCode content into a Mastodon media description.
- `plugin_mastodon_media_extract_default_path()` — line 7677 — Extract the default path parameter from a FlatPress media tag attribute string.
- `plugin_mastodon_add_local_media_item()` — line 7706 — Append a local media item to the collection while avoiding duplicates.
- `plugin_mastodon_collect_local_entry_media()` — line 7755 — Collect local images, galleries, audio and video referenced by an entry.
- `plugin_mastodon_select_status_media_items()` — line 7901 — Select a Mastodon-compatible media set for one status.
- `plugin_mastodon_prepare_entry_media_items()` — line 7962 — Normalize local entry media items for signature and export planning.
- `plugin_mastodon_entry_media_attachment_signature_from_items()` — line 7990 — Build a signature for the actual media payload of local entry attachments.
- `plugin_mastodon_entry_media_description_signature_from_items()` — line 8013 — Build a signature for local entry media descriptions.
- `plugin_mastodon_entry_media_signature()` — line 8032 — Build a signature for media references contained in entry content.
- `plugin_mastodon_remote_media_attachment_type()` — line 8049 — Return the normalized Mastodon attachment type.
- `plugin_mastodon_remote_status_media_attachments()` — line 8076 — Extract supported media attachments from a remote Mastodon status.
- `plugin_mastodon_remote_status_image_attachments()` — line 8106 — Extract image attachments from a remote Mastodon status.
- `plugin_mastodon_remote_media_source_url()` — line 8115 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_media_source_urls()` — line 8133 — Return direct-download candidate URLs for a remote media attachment.
- `plugin_mastodon_remote_media_description()` — line 8155 — Resolve the best description for a remote attachment.
- `plugin_mastodon_remote_media_focus()` — line 8169 — Resolve the stored focus string for a remote attachment, if any.
- `plugin_mastodon_remote_media_descriptors_from_status()` — line 8186 — Build sanitized remote-media descriptors from a Mastodon status payload.
- `plugin_mastodon_remote_media_descriptors_from_media_ids()` — line 8217 — Build remote-media descriptors from freshly uploaded media IDs and the current local descriptions.
- `plugin_mastodon_status_media_attributes()` — line 8251 — Build media_attributes descriptors for PUT /api/v1/statuses/:id.
- `plugin_mastodon_media_download()` — line 8284 — Download a remote media asset.
- `plugin_mastodon_remote_download_basename()` — line 8298 — Build a safe basename for a downloaded remote attachment.
- `plugin_mastodon_store_remote_media_url()` — line 8328 — Download and store one remote media URL.
- `plugin_mastodon_build_imported_media_bbcode()` — line 8350 — Build FlatPress BBCode for imported remote media attachments.
- `plugin_mastodon_is_two_digit_path_segment()` — line 9337 — Determine whether a FlatPress content-tree path segment is a two-digit date segment.
- `plugin_mastodon_two_digit_year_to_full_year()` — line 9346 — Convert a FlatPress two-digit year segment into the full year used by PHP's legacy date parser.
- `plugin_mastodon_entry_month_end_date_key()` — line 9357 — Return the last date key that can occur inside a FlatPress YY/MM content directory.
- `plugin_mastodon_entry_month_may_match_scheduled_window()` — line 9379 — Determine whether a FlatPress YY/MM directory can contain scheduled-sync entry candidates.
- `plugin_mastodon_collect_entry_files_from_month()` — line 9399 — Collect direct entry files from one canonical FlatPress YY/MM content directory.
- `plugin_mastodon_collect_entry_files_legacy()` — line 9432 — Legacy fallback for non-canonical content trees.
- `plugin_mastodon_collect_entry_files()` — line 9460 — Collect canonical entry files directly from FlatPress YY/MM content directories.
- `plugin_mastodon_collect_scheduled_entry_files()` — line 9503 — Collect scheduled-sync entry candidates directly from relevant FlatPress YY/MM content directories.
- `plugin_mastodon_add_dirty_entry_files()` — line 9548 — Append existing dirty entry files by canonical FlatPress ID.
- `plugin_mastodon_collect_entry_files_for_sync()` — line 9568 — Collect local entry files for a local-to-remote synchronization pass; scheduled runs add all dirty parents without a hard cap.
- `plugin_mastodon_local_item_timestamp()` — line 9587 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_compare_local_entries_for_export()` — line 9616 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_test_note_local_entry_parse()` — line 9635 — Increment a simulation-only local-entry parse counter when enabled.
- `plugin_mastodon_dirty_entry_id_lookup()` — line 9647 — Return entry identifiers that are explicitly queued by dirty entry/comment hooks.
- `plugin_mastodon_content_sync_comment_entry_ids()` — line 9677 — Return entry ids whose comment shards may be needed by the current content-sync workset.
- `plugin_mastodon_state_load_content_sync_comment_workset()` — line 9701 — Load comment shards needed for the current content synchronization workset.
- `plugin_mastodon_should_parse_local_entry_for_sync()` — line 9717 — Determine whether a local entry file should be parsed during a scheduled local-to-remote pass.
- `plugin_mastodon_list_local_entries_for_sync()` — line 9742 — List local FlatPress entries for a local-to-remote synchronization pass.
- `plugin_mastodon_list_local_entries()` — line 9781 — List local FlatPress entries ordered for export.

## G. HTTP transport, PHP timeout budgeting, instance capability lookup, status-length budgeting, OAuth, Mastodon API calls, and media upload

This group performs network-facing work: request budgeting, persistent rate-limit windows, HTTP transport, OAuth API calls, instance capability lookup, status length budgeting, media upload/polling/cleanup and Mastodon status create/update/delete helpers.

- `plugin_mastodon_rate_limit_default_budgets()` — line 1391 — Return the default Mastodon API budgets for one synchronization run.
- `plugin_mastodon_rate_limit_window_budgets()` — line 1415 — Return the persistent Mastodon API window budgets across synchronization runs.
- `plugin_mastodon_rate_limit_window_config()` — line 1442 — Return persistent window configuration for one request kind.
- `plugin_mastodon_rate_limit_window_entry()` — line 1462 — Normalize one persistent rate-limit window entry.
- `plugin_mastodon_rate_limit_window_read()` — line 1482 — Read the persistent rate-limit windows.
- `plugin_mastodon_rate_limit_window_write()` — line 1514 — Persist the current rate-limit windows.
- `plugin_mastodon_rate_limit_window_acquire()` — line 1539 — Reserve one item from a persistent cross-run Mastodon rate-limit window.
- `plugin_mastodon_rate_limit_window_clear()` — line 1588 — Clear persistent rate-limit windows, mainly for tests and recovery tooling.
- `plugin_mastodon_rate_limit_guard_start()` — line 1600 — Start a per-run Mastodon API rate-limit guard.
- `plugin_mastodon_rate_limit_guard_stop()` — line 1626 — Stop the current per-run Mastodon API rate-limit guard while keeping its summary inspectable.
- `plugin_mastodon_rate_limit_guard_active()` — line 1637 — Return whether a per-run Mastodon API rate-limit guard is active.
- `plugin_mastodon_rate_limit_guard_summary()` — line 1645 — Return the current rate-limit guard summary.
- `plugin_mastodon_rate_limit_headers()` — line 1654 — Normalize a response header map for rate-limit checks.
- `plugin_mastodon_rate_limit_request_kind()` — line 1675 — Determine whether a Mastodon API request consumes one of the stricter per-run budgets.
- `plugin_mastodon_rate_limit_block()` — line 1700 — Mark the current rate-limit guard as blocked.
- `plugin_mastodon_rate_limit_acquire()` — line 1744 — Reserve budget for one Mastodon API request.
- `plugin_mastodon_rate_limit_observe_response()` — line 1789 — Update the current per-run guard from Mastodon rate-limit response headers.
- `plugin_mastodon_rate_limit_blocked_reason()` — line 1813 — Return the current rate-limit block reason, if any.
- `plugin_mastodon_rate_limit_blocked_response()` — line 1825 — Build a synthetic API response for locally blocked Mastodon requests.
- `plugin_mastodon_rate_limit_state_error()` — line 1842 — Return the rate-limit reason that should be written to sync state, if any.
- `plugin_mastodon_extend_time_limit()` — line 8546 — Best-effort refresh/increase of the PHP execution time budget for long-running Mastodon work.
- `plugin_mastodon_instance_document()` — line 8577 — Load and cache the full Mastodon instance document returned by /api/v2/instance.
- `plugin_mastodon_instance_version()` — line 8621 — Return the Mastodon version string advertised by /api/v2/instance, if any.
- `plugin_mastodon_instance_supports_status_media_attributes()` — line 8639 — Determine whether the configured Mastodon instance should support media description updates on already-posted statuses.
- `plugin_mastodon_instance_supports_status_delete_media()` — line 8663 — Determine whether cached instance information confirms support for the delete_media query parameter on DELETE /api/v1/statuses/:id.
- `plugin_mastodon_instance_configuration()` — line 8680 — Load and cache the Mastodon instance configuration document.
- `plugin_mastodon_instance_media_limit()` — line 8690 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_media_description_limit()` — line 8703 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_url_reserved_length()` — line 8716 — Return the per-URL character budget used by the configured instance.
- `plugin_mastodon_status_text_length()` — line 8730 — Calculate the Mastodon-visible length of a plain-text status.
- `plugin_mastodon_limit_status_text()` — line 8762 — Limit Mastodon plain text while respecting the instance URL budget.
- `plugin_mastodon_http_request_multipart()` — line 8841 — Perform a multipart HTTP request.
- `plugin_mastodon_fetch_media_attachment()` — line 8974 — Fetch the current processing status of a Mastodon media attachment.
- `plugin_mastodon_delete_media_attachment()` — line 8984 — Delete an uploaded Mastodon media attachment before it is attached to a final status.
- `plugin_mastodon_cleanup_uploaded_media()` — line 9003 — Best-effort cleanup for uploaded Mastodon media that never reached a final status request.
- `plugin_mastodon_media_processing_attempts()` — line 9039 — Determine how patiently the plugin should wait for Mastodon media processing.
- `plugin_mastodon_media_transfer_timeout()` — line 9063 — Determine a practical transfer timeout for one media upload.
- `plugin_mastodon_wait_for_media_attachment()` — line 9083 — Wait briefly until an asynchronously uploaded Mastodon media attachment is ready.
- `plugin_mastodon_upload_media_items()` — line 9132 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_prepare_entry_media_sync_plan()` — line 9249 — Decide whether a local entry update can reuse already-uploaded Mastodon media or needs a fresh upload.
- `plugin_mastodon_parse_http_response_headers()` — line 9819 — Parse raw HTTP response headers.
- `plugin_mastodon_stream_context_request()` — line 9849 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_array_is_list()` — line 9889 — Detect whether a value is a numerically indexed list.
- `plugin_mastodon_array_contains_only_form_scalars()` — line 9909 — Detect whether a list contains only scalar-compatible form values.
- `plugin_mastodon_http_build_query()` — line 9927 — Build an application/x-www-form-urlencoded query string for Mastodon requests.
- `plugin_mastodon_http_request()` — line 9983 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_mastodon_api()` — line 10101 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_json()` — line 10150 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_response_error_message()` — line 10165 — Extract the most useful error message from an API response.
- `plugin_mastodon_register_app()` — line 10194 — Register the FlatPress application on the configured Mastodon instance.
- `plugin_mastodon_build_authorize_url()` — line 10217 — Build the OAuth authorization URL.
- `plugin_mastodon_exchange_code_for_token()` — line 10237 — Exchange an OAuth authorization code for an access token.
- `plugin_mastodon_verify_credentials()` — line 10267 — Verify the currently configured access token.
- `plugin_mastodon_instance_character_limit()` — line 10284 — Return the status character limit of the configured instance.
- `plugin_mastodon_fetch_account_statuses()` — line 10299 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_status_context()` — line 10346 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_fetch_status()` — line 10357 — Fetch a single Mastodon status.
- `plugin_mastodon_delete_status()` — line 10368 — Delete a Mastodon status.
- `plugin_mastodon_delete_status_should_retry_without_delete_media()` — line 10396 — Check whether a failed status deletion may be caused by Mastodon versions before 4.4.0 not understanding the optional delete_media query parameter.
- `plugin_mastodon_status_missing_response()` — line 10416 — Check whether an API response means that the referenced Mastodon status is missing.
- `plugin_mastodon_create_status()` — line 10429 — Create a Mastodon status.
- `plugin_mastodon_update_status()` — line 10456 — Update an existing Mastodon status.

## H. Import/export builders and synchronization orchestration

This group composes lower-level helpers into visible synchronization behavior. It is the first group to inspect when automatic sync, normal manual sync, full manual sync, full deletion continuation or rotating known-thread refresh behaves differently after a state-format change.

- `plugin_mastodon_build_entry_status_text()` — line 10482 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_comment_status_text()` — line 10550 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_import_remote_entry()` — line 10588 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_remote_status_author_label()` — line 10698 — Build a readable account label for a Mastodon status author.
- `plugin_mastodon_strip_leading_quote_block()` — line 10731 — Remove one leading BBCode quote block from imported comment text.
- `plugin_mastodon_imported_reply_quote_payload()` — line 10764 — Resolve the author and body that should be quoted for an imported Mastodon reply.
- `plugin_mastodon_build_imported_reply_quote()` — line 10807 — Build an optional BBCode quote block for an imported Mastodon reply.
- `plugin_mastodon_import_remote_comment()` — line 10849 — Import a remote Mastodon reply into FlatPress as a comment.
- `plugin_mastodon_import_remote_context_descendants()` — line 10942 — Import remote Mastodon replies from a fetched thread context.
- `plugin_mastodon_old_thread_context_rotation_limit()` — line 11044 — Return the maximum number of known synchronized threads checked for replies per content sync run.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 11060 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
- `plugin_mastodon_sync_remote_to_local()` — line 11133 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_sync_local_to_remote()` — line 11200 — Synchronize local FlatPress content to Mastodon.
- `plugin_mastodon_run_deletion_sync()` — line 11432 — Run the deletion synchronization in a follow-up request after content sync completed.
- `plugin_mastodon_run_sync()` — line 11807 — Run a full synchronization cycle.

## Recommended reading order for new developers

| Step | Read                                                                                                    |
| ---- | ------------------------------------------------------------------------------------------------------- |
| 1    | Purpose, scope and API endpoint table to understand the external boundaries.                            |
| 2    | High-level call flow diagrams before reading individual functions.                                      |
| 3    | Responsibility groups A–H for the subsystem you need to change.                                         |
| 4    | `02-State-Model.md` for state format details before touching shard/write logic.                         |
| 5    | `05-Regression-Test-Matrix.md` before changing sync, deletion, migration or admin maintenance behavior. |
| 6    | Generated function catalog for current line numbers when editing code.                                  |

## Current feature areas reflected in the function set

| Feature area            | Functions to inspect first                                                                                                 | Why it matters                                                                          |
| ----------------------- | -------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------- |
| Configuration and OAuth | `plugin_mastodon_default_options()`, `plugin_mastodon_normalize_*()`, OAuth helpers                                        | Controls admin options, scopes, authorization and account verification.                 |
| State and scheduler     | `plugin_mastodon_state_read()`, `plugin_mastodon_state_write_with_last_error()`, scheduler-state helpers                   | Keeps frontend requests cheap and sync state recoverable.                               |
| Comment shards          | `plugin_mastodon_state_load_comment_shard_into()`, `plugin_mastodon_state_write_comment_shards()`, diagnose/repair helpers | Prevents the old monolithic comment state from dominating memory.                       |
| Local export            | `plugin_mastodon_sync_local_to_remote()`, builders, hash/media helpers                                                     | Creates or updates Mastodon statuses from FlatPress entries/comments.                   |
| Remote import           | `plugin_mastodon_sync_remote_to_local()`, remote context/import helpers                                                    | Imports remote replies and updates synchronized threads.                                |
| Deletion reconciliation | `plugin_mastodon_run_deletion_sync()`, cursor helpers, delete API helper                                                   | Keeps local and remote deletions aligned without scanning everything at once.           |
| Admin/CLI maintenance   | `plugin_mastodon_admin_assign()`, `plugin_mastodon_cli_comment_shard_maintenance()`                                        | Provides human-facing diagnostics and repair without cluttering the main settings page. |

## Maintenance notes

- Do not reintroduce a path that decodes every comment shard for normal frontend, admin or scheduled-window requests.
- Keep `comments_remote` and per-entry comment shards consistent; when one side cannot be trusted, run the diagnostic/repair flow rather than guessing.
- Keep migration backups. A downgrade to a pre-shard plugin cannot understand a compact post-migration `state.json` without restoring the legacy backup.
- Any change to `plugin_mastodon_run_sync()`, `plugin_mastodon_sync_remote_to_local()` or `plugin_mastodon_run_deletion_sync()` should be checked against automatic sync, manual normal sync, full manual sync and deletion-resume tests.
- The admin maintenance page uses `mastodon_lang` in templates and the language-file alias `mastodon_maintenance` for FlatPress `shared:errorlist.tpl` messages.
- When adding a new callable function, update this generated catalog by rerunning the documentation generation step or by updating the line reference and running `check-consistency.php`.

## Alphabetical appendix / Generated function catalog

The appendix is alphabetical and repeats every callable function/method with the current source line and PHPDoc-derived summary. The consistency checker verifies the function names, line numbers and that every generated entry has a non-empty description.

- `main()` — line 12320 — Keep the FlatPress admin panel lifecycle stable without additional processing.
- `onsubmit()` — line 12324 — Process submitted Mastodon admin actions for the active admin panel.
- `plugin_mastodon_absolute_url()` — line 5903 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_add_dirty_entry_files()` — line 9548 — Append existing dirty entry files by canonical FlatPress ID.
- `plugin_mastodon_add_local_media_item()` — line 7706 — Append a local media item to the collection while avoiding duplicates.
- `plugin_mastodon_admin_add_info_row()` — line 11940 — Add one admin diagnostics row when the value is available.
- `plugin_mastodon_admin_assign()` — line 12175 — Assign plugin data to Smarty for the admin panel.
- `plugin_mastodon_admin_boolean_label()` — line 11925 — Return a localized yes/no/unknown label for admin diagnostics.
- `plugin_mastodon_admin_empty_state_maintenance_result()` — line 12088 — Return an empty admin maintenance result container.
- `plugin_mastodon_admin_instance_info_rows()` — line 11987 — Build the rows for the admin instance-information diagnostics table.
- `plugin_mastodon_admin_is_maintenance_request()` — line 12080 — Decide whether the current request targets the dedicated Mastodon maintenance page.
- `plugin_mastodon_admin_language_strings()` — line 5973 — Return Mastodon admin-language strings with guaranteed maintenance fallbacks.
- `plugin_mastodon_admin_panel_link()` — line 12060 — Build a local admin-panel link for Mastodon plugin pages.
- `plugin_mastodon_admin_state_maintenance_result()` — line 12104 — Convert comment-shard diagnostics into template-friendly admin rows.
- `plugin_mastodon_apcu_cache_key()` — line 1149 — Build the namespaced APCu key used by this plugin.
- `plugin_mastodon_apcu_delete()` — line 1191 — Delete a value from APCu using the FlatPress namespace key builder.
- `plugin_mastodon_apcu_enabled()` — line 1140 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_fetch()` — line 1163 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 1178 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_apply_file_permissions()` — line 1003 — Apply FlatPress FILE_PERMISSIONS to a runtime file.
- `plugin_mastodon_array_contains_only_form_scalars()` — line 9909 — Detect whether a list contains only scalar-compatible form values.
- `plugin_mastodon_array_is_list()` — line 9889 — Detect whether a value is a numerically indexed list.
- `plugin_mastodon_audiovideo_plugin_active()` — line 6175 — Determine whether the Audio/Video plugin is active for the current FlatPress request.
- `plugin_mastodon_bbcode_attr_escape()` — line 7364 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_bbcode_plugin_active()` — line 6141 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_bbcode_text_escape()` — line 7376 — Escape plain text embedded between BBCode tags.
- `plugin_mastodon_blog_base_url()` — line 5850 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_build_authorize_url()` — line 10217 — Build the OAuth authorization URL.
- `plugin_mastodon_build_comment_remote_child_index()` — line 5397 — Build an index of mapped local comments grouped by their direct remote parent status.
- `plugin_mastodon_build_comment_status_text()` — line 10550 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_build_entry_status_text()` — line 10482 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 6439 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_build_imported_media_bbcode()` — line 8350 — Build FlatPress BBCode for imported remote media attachments.
- `plugin_mastodon_build_imported_reply_quote()` — line 10807 — Build an optional BBCode quote block for an imported Mastodon reply.
- `plugin_mastodon_cleanup_imported_text()` — line 6697 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_cleanup_uploaded_media()` — line 9003 — Best-effort cleanup for uploaded Mastodon media that never reached a final status request.
- `plugin_mastodon_clear_saved_instance_info()` — line 170 — Remove any stored Mastodon instance information from the plugin options.
- `plugin_mastodon_cli_comment_shard_maintenance()` — line 12141 — Print command-line comment-shard diagnostics or perform a repair.
- `plugin_mastodon_collect_entry_files()` — line 9460 — Collect canonical entry files directly from FlatPress YY/MM content directories.
- `plugin_mastodon_collect_entry_files_for_sync()` — line 9568 — Collect local entry files for a local-to-remote synchronization pass; scheduled runs add all dirty parents without a hard cap.
- `plugin_mastodon_collect_entry_files_from_month()` — line 9399 — Collect direct entry files from one canonical FlatPress YY/MM content directory.
- `plugin_mastodon_collect_entry_files_legacy()` — line 9432 — Legacy fallback for non-canonical content trees.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 11060 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
- `plugin_mastodon_collect_local_entry_media()` — line 7755 — Collect local images, galleries, audio and video referenced by an entry.
- `plugin_mastodon_collect_scheduled_entry_files()` — line 9503 — Collect scheduled-sync entry candidates directly from relevant FlatPress YY/MM content directories.
- `plugin_mastodon_comment_hash()` — line 7201 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_comment_parent_fields()` — line 5661 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_compact_instance_document()` — line 189 — Reduce a live Mastodon instance document to the stable subset that is useful for the plugin.
- `plugin_mastodon_companion_plugins_status()` — line 6207 — Return the status of companion FlatPress plugins used for the full Mastodon feature set.
- `plugin_mastodon_compare_local_entries_for_export()` — line 9616 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_configured_status_language()` — line 2303 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_content_sync_comment_entry_ids()` — line 9677 — Return entry ids whose comment shards may be needed by the current content-sync workset.
- `plugin_mastodon_create_status()` — line 10429 — Create a Mastodon status.
- `plugin_mastodon_date_matches_content_window()` — line 2619 — Determine whether a date is inside the manual lower bound and, for scheduled runs, the automatic window.
- `plugin_mastodon_date_matches_sync_start()` — line 2584 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_datetime_date_key()` — line 2560 — Normalize a date/datetime string to a sync-start date key.
- `plugin_mastodon_default_content_stats()` — line 521 — Return the default counters for the last content synchronization.
- `plugin_mastodon_default_deletion_stats()` — line 538 — Return the default counters for the last deletion synchronization.
- `plugin_mastodon_default_options()` — line 138 — Return the default plugin option values.
- `plugin_mastodon_default_state()` — line 551 — Return the default runtime state structure.
- `plugin_mastodon_delete_media_attachment()` — line 8984 — Delete an uploaded Mastodon media attachment before it is attached to a final status.
- `plugin_mastodon_delete_status()` — line 10368 — Delete a Mastodon status.
- `plugin_mastodon_delete_status_should_retry_without_delete_media()` — line 10396 — Check whether a failed status deletion may be caused by Mastodon versions before 4.4.0 not understanding the optional delete_media query parameter.
- `plugin_mastodon_deletion_sync_due()` — line 5360 — Determine whether a pending deletion synchronization may start now.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 5687 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_dirty_entry_id_lookup()` — line 9647 — Return entry identifiers that are explicitly queued by dirty entry/comment hooks.
- `plugin_mastodon_dirty_tracking_options()` — line 4862 — Check whether dirty tracking should persist state for a local FlatPress write hook.
- `plugin_mastodon_dom_children_to_flatpress()` — line 6751 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 6769 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_domains_match()` — line 6683 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 6452 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 6465 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_emoticons_plugin_active()` — line 6192 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_enabled_plugin_state()` — line 6059 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_ensure_comment_shard_dir()` — line 2821 — Ensure that the comment-shard directory exists.
- `plugin_mastodon_ensure_state_dir()` — line 2813 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_entry_hash()` — line 7189 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_entry_media_attachment_signature_from_items()` — line 7990 — Build a signature for the actual media payload of local entry attachments.
- `plugin_mastodon_entry_media_description_signature_from_items()` — line 8013 — Build a signature for local entry media descriptions.
- `plugin_mastodon_entry_media_signature()` — line 8032 — Build a signature for media references contained in entry content.
- `plugin_mastodon_entry_month_end_date_key()` — line 9357 — Return the last date key that can occur inside a FlatPress YY/MM content directory.
- `plugin_mastodon_entry_month_may_match_scheduled_window()` — line 9379 — Determine whether a FlatPress YY/MM directory can contain scheduled-sync entry candidates.
- `plugin_mastodon_exchange_code_for_token()` — line 10237 — Exchange an OAuth authorization code for an access token.
- `plugin_mastodon_extend_time_limit()` — line 8546 — Best-effort refresh/increase of the PHP execution time budget for long-running Mastodon work.
- `plugin_mastodon_extension_from_mime_type()` — line 7499 — Return an appropriate file extension for a MIME type.
- `plugin_mastodon_extract_flatpress_tags()` — line 6286 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_extract_url_token()` — line 5886 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_fediverse_creator_value()` — line 2223 — Build the fediverse creator meta value.
- `plugin_mastodon_fetch_account_statuses()` — line 10299 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_media_attachment()` — line 8974 — Fetch the current processing status of a Mastodon media attachment.
- `plugin_mastodon_fetch_status()` — line 10357 — Fetch a single Mastodon status.
- `plugin_mastodon_fetch_status_context()` — line 10346 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_file_permissions_mode()` — line 994 — Return the FlatPress file permission mode for runtime files.
- `plugin_mastodon_file_prestat()` — line 1852 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 1872 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_flatpress_to_mastodon()` — line 7050 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_format_admin_datetime()` — line 926 — Format a stored UTC timestamp for the admin panel using FlatPress local time and configured formats.
- `plugin_mastodon_fp_config()` — line 775 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 811 — Read a nested FlatPress configuration value.
- `plugin_mastodon_fp_timeoffset_hours()` — line 839 — Return the configured FlatPress time offset in whole hours.
- `plugin_mastodon_fp_timeoffset_label()` — line 847 — Format the configured FlatPress time offset as a UTC label for admin users.
- `plugin_mastodon_fp_timeoffset_seconds()` — line 827 — Return the configured FlatPress time offset in seconds.
- `plugin_mastodon_get_options()` — line 1883 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_guess_subject()` — line 5799 — Guess a subject line from imported plain text.
- `plugin_mastodon_head()` — line 2241 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_html_entity_decode()` — line 5841 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_http_build_query()` — line 9927 — Build an application/x-www-form-urlencoded query string for Mastodon requests.
- `plugin_mastodon_http_request()` — line 9983 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_http_request_multipart()` — line 8841 — Perform a multipart HTTP request.
- `plugin_mastodon_import_remote_comment()` — line 10849 — Import a remote Mastodon reply into FlatPress as a comment.
- `plugin_mastodon_import_remote_context_descendants()` — line 10942 — Import remote Mastodon replies from a fetched thread context.
- `plugin_mastodon_import_remote_entry()` — line 10588 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_imported_reply_quote_payload()` — line 10764 — Resolve the author and body that should be quoted for an imported Mastodon reply.
- `plugin_mastodon_instance_authority()` — line 2177 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_instance_character_limit()` — line 10284 — Return the status character limit of the configured instance.
- `plugin_mastodon_instance_configuration()` — line 8680 — Load and cache the Mastodon instance configuration document.
- `plugin_mastodon_instance_document()` — line 8577 — Load and cache the full Mastodon instance document returned by /api/v2/instance.
- `plugin_mastodon_instance_media_description_limit()` — line 8703 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_media_limit()` — line 8690 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_media_size_limit()` — line 7566 — Return the configured byte-size limit for a media family, or 0 if unknown.
- `plugin_mastodon_instance_registration_summary()` — line 11961 — Summarize the registration state advertised by the Mastodon instance.
- `plugin_mastodon_instance_supported_media_mime_types()` — line 7546 — Return instance-advertised supported media MIME types.
- `plugin_mastodon_instance_supports_status_delete_media()` — line 8663 — Determine whether cached instance information confirms support for the delete_media query parameter on DELETE /api/v1/statuses/:id.
- `plugin_mastodon_instance_supports_status_media_attributes()` — line 8639 — Determine whether the configured Mastodon instance should support media description updates on already-posted statuses.
- `plugin_mastodon_instance_url_reserved_length()` — line 8716 — Return the per-URL character budget used by the configured instance.
- `plugin_mastodon_instance_version()` — line 8621 — Return the Mastodon version string advertised by /api/v2/instance, if any.
- `plugin_mastodon_io_append_file()` — line 1037 — Append to a file without re-reading and rewriting the complete payload.
- `plugin_mastodon_io_read_file()` — line 960 — Read a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_read_file_uncached()` — line 976 — Read a file without FlatPress request-local caches.
- `plugin_mastodon_io_rotate_file_if_needed()` — line 1101 — Rotate an append-only log file when the next append would exceed the size cap.
- `plugin_mastodon_io_write_file()` — line 1016 — Write a file through the FlatPress I/O layer when available.
- `plugin_mastodon_is_public_host()` — line 6569 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_is_two_digit_path_segment()` — line 9337 — Determine whether a FlatPress content-tree path segment is a two-digit date segment.
- `plugin_mastodon_json_decode_string_at()` — line 2954 — Decode a JSON string literal starting at the current offset.
- `plugin_mastodon_json_skip_ws()` — line 2991 — Skip JSON whitespace in a string scanner.
- `plugin_mastodon_json_top_level_property_bounds()` — line 3008 — Find a top-level JSON property value in an object payload without decoding the whole object.
- `plugin_mastodon_lang_string()` — line 5945 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_limit_status_text()` — line 8762 — Limit Mastodon plain text while respecting the instance URL budget.
- `plugin_mastodon_limit_text()` — line 7167 — Limit text to a maximum number of characters.
- `plugin_mastodon_list_local_comment_ids()` — line 5762 — List local FlatPress comment identifiers by scanning the entry comment directory directly.
- `plugin_mastodon_list_local_entries()` — line 9781 — List local FlatPress entries ordered for export.
- `plugin_mastodon_list_local_entries_for_sync()` — line 9742 — List local FlatPress entries for a local-to-remote synchronization pass.
- `plugin_mastodon_local_comment_parent_export_pending()` — line 5734 — Determine whether a local parent comment should be exported before its child reply.
- `plugin_mastodon_local_item_date_key()` — line 2511 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_local_item_matches_content_window()` — line 2653 — Determine whether a local FlatPress item is inside the active content synchronization window.
- `plugin_mastodon_local_item_matches_sync_start()` — line 2641 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_local_item_timestamp()` — line 9587 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_local_write_guard_active()` — line 4854 — Return whether FlatPress write hooks are currently triggered by Mastodon remote mirroring.
- `plugin_mastodon_local_write_guard_enter()` — line 4831 — Increase the local-write guard depth while the plugin mirrors remote Mastodon data into FlatPress.
- `plugin_mastodon_local_write_guard_leave()` — line 4842 — Decrease the local-write guard depth after a plugin-owned FlatPress write.
- `plugin_mastodon_log()` — line 3989 — Append a line to the plugin sync log.
- `plugin_mastodon_log_flush_skip_summaries()` — line 4034 — Flush aggregated skip log messages.
- `plugin_mastodon_log_max_bytes()` — line 1077 — Return the maximum sync.log size before rotation.
- `plugin_mastodon_log_rotate_files()` — line 1088 — Return the number of retained sync.log rotation files.
- `plugin_mastodon_log_skip()` — line 4004 — Aggregate high-volume skip messages until the current sync phase ends.
- `plugin_mastodon_mapping_effective_date_key()` — line 2733 — Return the most stable date key available for a synchronized mapping.
- `plugin_mastodon_mapping_keys_after_cursor()` — line 2795 — Return mapping keys ordered after a saved deletion cursor.
- `plugin_mastodon_mapping_matches_deletion_lookup_window()` — line 2774 — Determine whether an existing local mapping should be remotely checked for deletion in this run.
- `plugin_mastodon_mapping_matches_sync_start()` — line 2685 — Determine whether a synchronized mapping should participate in the deletion follow-up for the current sync start date.
- `plugin_mastodon_mastodon_api()` — line 10101 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 6328 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 6948 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_mastodon_json()` — line 10150 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_maybe_sync()` — line 11893 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_media_copy_tree()` — line 7326 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_media_delete_tree()` — line 7299 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_description_from_bbcode_content()` — line 7651 — Normalize optional AudioVideo BBCode content into a Mastodon media description.
- `plugin_mastodon_media_download()` — line 8284 — Download a remote media asset.
- `plugin_mastodon_media_extract_default_path()` — line 7677 — Extract the default path parameter from a FlatPress media tag attribute string.
- `plugin_mastodon_media_guess_mime_type()` — line 7398 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_parse_tag_attributes()` — line 7620 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_media_prepare_directory()` — line 7283 — Ensure that a media directory exists.
- `plugin_mastodon_media_processing_attempts()` — line 9039 — Determine how patiently the plugin should wait for Mastodon media processing.
- `plugin_mastodon_media_relative_to_absolute()` — line 7270 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_media_transfer_timeout()` — line 9063 — Determine a practical transfer timeout for one media upload.
- `plugin_mastodon_media_type_from_mime()` — line 7465 — Return a stable FlatPress/Mastodon media family for a MIME type.
- `plugin_mastodon_minutes_to_sync_time()` — line 888 — Convert minutes after midnight into a normalized HH:MM time value.
- `plugin_mastodon_normalize_boolean_option()` — line 2391 — Normalize a boolean-like option value to the stored string representation.
- `plugin_mastodon_normalize_comment_parent_id()` — line 5670 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_normalize_delete_sync_enabled()` — line 2479 — Normalize the toggle that enables the follow-up deletion synchronization.
- `plugin_mastodon_normalize_deletions_pending_scope()` — line 4345 — Normalize the pending deletion scope marker.
- `plugin_mastodon_normalize_head_username()` — line 2138 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 2425 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_normalize_instance_url()` — line 2105 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_media_relative_path()` — line 7247 — Normalize a FlatPress media path relative to fp-content.
- `plugin_mastodon_normalize_old_thread_reply_check()` — line 2461 — Normalize the toggle that enables rotating context checks for known synchronized Mastodon threads.
- `plugin_mastodon_normalize_quote_imported_reply_parent()` — line 2443 — Normalize the toggle that controls whether imported Mastodon replies should quote the replied-to comment in FlatPress.
- `plugin_mastodon_normalize_scheduled_window_days()` — line 2366 — Normalize the automatic scheduled synchronization window.
- `plugin_mastodon_normalize_status_language()` — line 2281 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_normalize_sync_start_date()` — line 2340 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_sync_time()` — line 2323 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_tag_list()` — line 6255 — Normalize a list of tag labels.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 2407 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_oauth_legacy_scopes()` — line 587 — Return the legacy OAuth scopes used before scope discovery was added.
- `plugin_mastodon_oauth_preferred_scopes()` — line 681 — Return the preferred OAuth scopes for the configured Mastodon instance.
- `plugin_mastodon_oauth_profile_scopes()` — line 595 — Return the stricter OAuth scopes preferred on current Mastodon instances.
- `plugin_mastodon_oauth_scope_supported()` — line 660 — Determine whether the configured Mastodon instance advertises support for a scope.
- `plugin_mastodon_oauth_scopes()` — line 698 — Return the OAuth scopes that the currently registered app may safely request.
- `plugin_mastodon_oauth_server_metadata()` — line 604 — Fetch OAuth authorization server metadata for the configured Mastodon instance.
- `plugin_mastodon_oauth_supported_scopes()` — line 623 — Return the OAuth scopes supported by the configured Mastodon instance, if discoverable.
- `plugin_mastodon_old_thread_context_rotation_limit()` — line 11044 — Return the maximum number of known synchronized threads checked for replies per content sync run.
- `plugin_mastodon_on_comment_deleted()` — line 5015 — Mark a local FlatPress comment deletion for the Mastodon deletion sync.
- `plugin_mastodon_on_comment_saved()` — line 4957 — Mark a local FlatPress comment write as dirty for a later Mastodon sync.
- `plugin_mastodon_on_entry_deleted()` — line 4930 — Mark a local FlatPress entry deletion for the Mastodon deletion sync.
- `plugin_mastodon_on_entry_saved()` — line 4881 — Mark a local FlatPress entry write as dirty for a later Mastodon sync.
- `plugin_mastodon_parse_http_response_headers()` — line 9819 — Parse raw HTTP response headers.
- `plugin_mastodon_parse_iso_datetime()` — line 5577 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 5595 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_photoswipe_plugin_active()` — line 6158 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_plain_text_from_bbcode()` — line 6611 — Convert FlatPress BBCode into plain text for Mastodon export.
- `plugin_mastodon_prepare_emoticons_for_mastodon()` — line 6534 — Convert FlatPress emoticon shortcodes to Mastodon-safe Unicode glyphs when the plugin is active.
- `plugin_mastodon_prepare_entry_media_items()` — line 7962 — Normalize local entry media items for signature and export planning.
- `plugin_mastodon_prepare_entry_media_sync_plan()` — line 9249 — Decide whether a local entry update can reuse already-uploaded Mastodon media or needs a fresh upload.
- `plugin_mastodon_process_pending_comment_remote_rechecks()` — line 5467 — Process queued descendant reply rechecks using a small FIFO queue.
- `plugin_mastodon_profile_url()` — line 2207 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_protect_locally_deleted_exported_comments()` — line 5181 — Protect locally deleted exported FlatPress comments from stale Mastodon re-imports before deletion sync runs.
- `plugin_mastodon_public_comment_url()` — line 6933 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_public_comments_url()` — line 6905 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_entry_url()` — line 6878 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_url_for_mastodon()` — line 6592 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_queue_comment_descendant_remote_rechecks()` — line 5428 — Queue the direct mapped local children of one deleted remote comment for additional verification passes.
- `plugin_mastodon_rate_limit_acquire()` — line 1744 — Reserve budget for one Mastodon API request.
- `plugin_mastodon_rate_limit_block()` — line 1700 — Mark the current rate-limit guard as blocked.
- `plugin_mastodon_rate_limit_blocked_reason()` — line 1813 — Return the current rate-limit block reason, if any.
- `plugin_mastodon_rate_limit_blocked_response()` — line 1825 — Build a synthetic API response for locally blocked Mastodon requests.
- `plugin_mastodon_rate_limit_default_budgets()` — line 1391 — Return the default Mastodon API budgets for one synchronization run.
- `plugin_mastodon_rate_limit_guard_active()` — line 1637 — Return whether a per-run Mastodon API rate-limit guard is active.
- `plugin_mastodon_rate_limit_guard_start()` — line 1600 — Start a per-run Mastodon API rate-limit guard.
- `plugin_mastodon_rate_limit_guard_stop()` — line 1626 — Stop the current per-run Mastodon API rate-limit guard while keeping its summary inspectable.
- `plugin_mastodon_rate_limit_guard_summary()` — line 1645 — Return the current rate-limit guard summary.
- `plugin_mastodon_rate_limit_headers()` — line 1654 — Normalize a response header map for rate-limit checks.
- `plugin_mastodon_rate_limit_observe_response()` — line 1789 — Update the current per-run guard from Mastodon rate-limit response headers.
- `plugin_mastodon_rate_limit_request_kind()` — line 1675 — Determine whether a Mastodon API request consumes one of the stricter per-run budgets.
- `plugin_mastodon_rate_limit_state_error()` — line 1842 — Return the rate-limit reason that should be written to sync state, if any.
- `plugin_mastodon_rate_limit_window_acquire()` — line 1539 — Reserve one item from a persistent cross-run Mastodon rate-limit window.
- `plugin_mastodon_rate_limit_window_budgets()` — line 1415 — Return the persistent Mastodon API window budgets across synchronization runs.
- `plugin_mastodon_rate_limit_window_clear()` — line 1588 — Clear persistent rate-limit windows, mainly for tests and recovery tooling.
- `plugin_mastodon_rate_limit_window_config()` — line 1442 — Return persistent window configuration for one request kind.
- `plugin_mastodon_rate_limit_window_entry()` — line 1462 — Normalize one persistent rate-limit window entry.
- `plugin_mastodon_rate_limit_window_read()` — line 1482 — Read the persistent rate-limit windows.
- `plugin_mastodon_rate_limit_window_write()` — line 1514 — Persist the current rate-limit windows.
- `plugin_mastodon_reattach_local_comment_to_entry_status()` — line 5231 — Reattach one imported local comment to the synchronized entry status after its remote parent reply disappeared.
- `plugin_mastodon_refresh_instance_information()` — line 501 — Force a live refresh of the Mastodon instance information and persist the compact snapshot.
- `plugin_mastodon_register_app()` — line 10194 — Register the FlatPress application on the configured Mastodon instance.
- `plugin_mastodon_remote_download_basename()` — line 8298 — Build a safe basename for a downloaded remote attachment.
- `plugin_mastodon_remote_media_attachment_type()` — line 8049 — Return the normalized Mastodon attachment type.
- `plugin_mastodon_remote_media_description()` — line 8155 — Resolve the best description for a remote attachment.
- `plugin_mastodon_remote_media_descriptors_from_media_ids()` — line 8217 — Build remote-media descriptors from freshly uploaded media IDs and the current local descriptions.
- `plugin_mastodon_remote_media_descriptors_from_status()` — line 8186 — Build sanitized remote-media descriptors from a Mastodon status payload.
- `plugin_mastodon_remote_media_focus()` — line 8169 — Resolve the stored focus string for a remote attachment, if any.
- `plugin_mastodon_remote_media_source_url()` — line 8115 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_media_source_urls()` — line 8133 — Return direct-download candidate URLs for a remote media attachment.
- `plugin_mastodon_remote_status_author_label()` — line 10698 — Build a readable account label for a Mastodon status author.
- `plugin_mastodon_remote_status_date_key()` — line 2534 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_remote_status_image_attachments()` — line 8106 — Extract image attachments from a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 5649 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_remote_status_matches_content_window()` — line 2674 — Determine whether a remote Mastodon status is inside the active content synchronization window.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 2663 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_remote_status_media_attachments()` — line 8076 — Extract supported media attachments from a remote Mastodon status.
- `plugin_mastodon_remote_status_tags()` — line 6349 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_remote_status_timestamp()` — line 5617 — Resolve the best timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 5636 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 6519 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 6547 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_resolve_comment_reply_target()` — line 5709 — Resolve the remote reply target for a local comment export.
- `plugin_mastodon_response_error_message()` — line 10165 — Extract the most useful error message from an API response.
- `plugin_mastodon_run_deletion_sync()` — line 11432 — Run the deletion synchronization in a follow-up request after content sync completed.
- `plugin_mastodon_run_sync()` — line 11807 — Run a full synchronization cycle.
- `plugin_mastodon_runtime_cache_clear()` — line 760 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_runtime_cache_get()` — line 718 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 742 — Store a value in the request-local plugin cache.
- `plugin_mastodon_safe_filename()` — line 7234 — Sanitize a file name for local storage.
- `plugin_mastodon_safe_path_component()` — line 7219 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_save_options()` — line 1942 — Persist plugin options.
- `plugin_mastodon_saved_instance_document()` — line 394 — Read a previously stored Mastodon instance snapshot from the plugin options.
- `plugin_mastodon_scheduled_window_choices()` — line 2378 — Return the admin radio choices for the scheduled synchronization window.
- `plugin_mastodon_scheduled_window_start_date()` — line 2602 — Return the FlatPress-local date key that starts the automatic scheduled sync window.
- `plugin_mastodon_scheduler_source_signature()` — line 4130 — Return the current stat-based signature of the full state file.
- `plugin_mastodon_scheduler_state_decode_fresh()` — line 4223 — Decode a scheduler-state JSON payload only when it matches the current full-state signature.
- `plugin_mastodon_scheduler_state_default()` — line 4109 — Return an empty scheduler state derived from the full default state.
- `plugin_mastodon_scheduler_state_from_state()` — line 4177 — Build the lightweight scheduler summary from a full runtime state.
- `plugin_mastodon_scheduler_state_normalize()` — line 4139 — Normalize a scheduler summary without touching full mapping arrays.
- `plugin_mastodon_scheduler_state_read()` — line 4242 — Load the lightweight scheduler summary and rebuild it conservatively when stale.
- `plugin_mastodon_scheduler_state_write()` — line 4197 — Persist the lightweight scheduler state. Failure only disables the request-time optimization.
- `plugin_mastodon_secret_decode()` — line 2074 — Decode a previously stored secret value.
- `plugin_mastodon_secret_encode()` — line 2051 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_key()` — line 2034 — Build the encryption key used for stored secrets.
- `plugin_mastodon_select_status_media_items()` — line 7901 — Select a Mastodon-compatible media set for one status.
- `plugin_mastodon_should_check_old_thread_replies()` — line 2470 — Check whether known synchronized Mastodon threads should be checked for replies in rotating batches.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 2434 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_should_parse_local_entry_for_sync()` — line 9717 — Determine whether a local entry file should be parsed during a scheduled local-to-remote pass.
- `plugin_mastodon_should_quote_imported_reply_parent()` — line 2452 — Check whether imported Mastodon replies should include a quote of the replied-to comment.
- `plugin_mastodon_should_run_deletion_sync()` — line 2488 — Check whether the follow-up deletion synchronization is enabled.
- `plugin_mastodon_should_update_local_from_remote()` — line 2416 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_state_cleanup_stale_comment_shards()` — line 3867 — Remove shard files that are no longer referenced by the main state.
- `plugin_mastodon_state_comment_entry_loaded()` — line 3575 — Check whether one entry's comment shard is loaded into the state array.
- `plugin_mastodon_state_comment_key()` — line 4409 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_comment_mappings()` — line 4418 — Return the loaded comment mapping array from a state object.
- `plugin_mastodon_state_comment_remote_mappings()` — line 4454 — Return the global remote-comment reverse index.
- `plugin_mastodon_state_comment_shard_entries()` — line 4506 — Return the main-state metadata for per-entry comment shards.
- `plugin_mastodon_state_comment_shard_entry_ids()` — line 3630 — Return entry ids with known comment shards.
- `plugin_mastodon_state_comment_shard_file()` — line 2850 — Return the shard path for an entry's comment mappings.
- `plugin_mastodon_state_comment_shard_files()` — line 3660 — Return all comment shard files currently present on disk keyed by entry id.
- `plugin_mastodon_state_comment_shard_relative_path()` — line 2865 — Return the relative shard path used in state metadata.
- `plugin_mastodon_state_comment_shards_partial()` — line 3547 — Return whether a state array only contains a subset of comment shards.
- `plugin_mastodon_state_create_migration_backup()` — line 3109 — Create a timestamped backup of the legacy main state before an inline-to-shard migration mutates it.
- `plugin_mastodon_state_diagnose_comment_shards()` — line 3712 — Scan shard files and compare them with the main-state metadata and reverse comment index.
- `plugin_mastodon_state_entry_id_from_comment_key()` — line 2839 — Return the entry id portion from a compound comment-state key.
- `plugin_mastodon_state_entry_media_attachment_signature()` — line 5113 — Return the stored attachment-signature for one entry mapping.
- `plugin_mastodon_state_entry_media_description_signature()` — line 5123 — Return the stored description-signature for one entry mapping.
- `plugin_mastodon_state_entry_remote_media()` — line 5083 — Return sanitized remote-media descriptors stored inside one entry mapping.
- `plugin_mastodon_state_fallback_key()` — line 1210 — Return the legacy APCu key that used to hold a full last-known-good state fallback.
- `plugin_mastodon_state_fallback_read()` — line 1228 — Full-state APCu fallback is intentionally disabled to avoid multi-MiB APCu entries.
- `plugin_mastodon_state_fallback_store()` — line 1219 — Remove the legacy full-state APCu fallback instead of storing large mapping arrays.
- `plugin_mastodon_state_get_comment_meta()` — line 5135 — Return mapping metadata for a local comment.
- `plugin_mastodon_state_get_entry_meta()` — line 5040 — Return mapping metadata for a local entry.
- `plugin_mastodon_state_get_pending_comment_remote_recheck()` — line 5305 — Return one pending descendant recheck marker.
- `plugin_mastodon_state_group_comments_by_entry()` — line 3367 — Group comment mappings by their parent entry id.
- `plugin_mastodon_state_has_comment_recheck_scope()` — line 5388 — Check whether the current deletion follow-up request should focus on pending descendant reply rechecks only.
- `plugin_mastodon_state_has_comment_tombstone()` — line 5170 — Check whether one remote Mastodon comment status was tombstoned locally.
- `plugin_mastodon_state_has_dirty_comment()` — line 4822 — Check whether a comment is queued for synchronization although it is outside the scheduled window.
- `plugin_mastodon_state_has_dirty_entry()` — line 4772 — Check whether an entry is queued for synchronization although it is outside the scheduled window.
- `plugin_mastodon_state_is_entry_id()` — line 2830 — Return whether a string looks like a FlatPress entry id.
- `plugin_mastodon_state_load_all_comment_shards_into()` — line 3648 — Load every comment shard into a state array for full-map maintenance paths.
- `plugin_mastodon_state_load_comment_shard_into()` — line 3609 — Load one entry comment shard into a partial state when needed.
- `plugin_mastodon_state_load_comment_shards()` — line 3507 — Load comment shards into a state array.
- `plugin_mastodon_state_load_content_sync_comment_workset()` — line 9701 — Load comment shards needed for the current content synchronization workset.
- `plugin_mastodon_state_loaded_comment_entry_ids()` — line 3556 — Return the entry ids whose comment shards are loaded in a partial state.
- `plugin_mastodon_state_main_payload()` — line 3488 — Build the main state payload without high-volume inline comment mappings.
- `plugin_mastodon_state_mark_comment_entry_loaded()` — line 3589 — Mark one entry's comment shard as loaded in a partial state.
- `plugin_mastodon_state_migrate_inline_comments_to_shards()` — line 3131 — Iterate a legacy inline comments object and persist per-entry shards without building one giant comments array.
- `plugin_mastodon_state_normalize()` — line 4358 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_pending_recheck_entry_ids()` — line 3321 — Return the entry ids referenced by pending comment rechecks.
- `plugin_mastodon_state_read()` — line 4064 — Load the persisted runtime state from disk.
- `plugin_mastodon_state_read_comment_shard()` — line 3395 — Load one per-entry comment shard.
- `plugin_mastodon_state_recover_from_comment_shards()` — line 3924 — Return a recovered state if comment shards were written but the main state is missing.
- `plugin_mastodon_state_remove_comment_mapping()` — line 4705 — Remove the mapping between a local comment and a remote status.
- `plugin_mastodon_state_remove_dirty_comment()` — line 4808 — Remove a comment from the dirty queue.
- `plugin_mastodon_state_remove_dirty_entry()` — line 4759 — Remove an entry from the dirty queue.
- `plugin_mastodon_state_remove_entry_mapping()` — line 4683 — Remove the mapping between a local entry and a remote status.
- `plugin_mastodon_state_remove_pending_comment_remote_recheck()` — line 5291 — Remove one pending descendant recheck marker.
- `plugin_mastodon_state_repair_comment_shards()` — line 3827 — Rebuild the main shard metadata and global comments_remote reverse index from shard files.
- `plugin_mastodon_state_set_comment_mapping()` — line 4641 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_set_comment_mappings()` — line 4436 — Replace the loaded comment mapping array in a state object.
- `plugin_mastodon_state_set_comment_remote_mappings()` — line 4480 — Replace the global remote-comment reverse index in a state object.
- `plugin_mastodon_state_set_comment_shard_entries()` — line 4525 — Replace the main-state metadata for per-entry comment shards.
- `plugin_mastodon_state_set_comment_tombstone()` — line 5150 — Store a tombstone that blocks re-importing a deleted remote comment status.
- `plugin_mastodon_state_set_deletions_pending()` — line 5346 — Update the runtime marker that tells the scheduler whether another deletion follow-up request is required.
- `plugin_mastodon_state_set_dirty_comment()` — line 4785 — Add an older changed comment to the persistent dirty queue.
- `plugin_mastodon_state_set_dirty_entry()` — line 4739 — Add an older changed entry to the persistent dirty queue.
- `plugin_mastodon_state_set_entry_mapping()` — line 4602 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_entry_media_meta()` — line 5054 — Persist media metadata for a synchronized local entry.
- `plugin_mastodon_state_set_pending_comment_remote_recheck()` — line 5320 — Mark one local comment for follow-up verification after an ancestor status disappeared remotely.
- `plugin_mastodon_state_try_streaming_legacy_migration()` — line 3270 — Migrate legacy inline comment mappings to per-entry shards without decoding the high-volume comments array.
- `plugin_mastodon_state_unload_comment_shard_from_memory()` — line 3342 — Drop one loaded comment shard from a partial state's in-memory working set.
- `plugin_mastodon_state_write()` — line 4288 — Persist the runtime state to disk.
- `plugin_mastodon_state_write_comment_shards()` — line 3430 — Write all per-entry comment shards.
- `plugin_mastodon_state_write_error_clear()` — line 4554 — Clear the last state write error marker.
- `plugin_mastodon_state_write_error_set()` — line 4546 — Store a state write error for callers that need a last_error message even when persistence fails.
- `plugin_mastodon_state_write_json_file()` — line 2879 — Write a JSON payload atomically when possible.
- `plugin_mastodon_state_write_last_error()` — line 4562 — Return the last state write error marker from the current request.
- `plugin_mastodon_state_write_lock_acquire()` — line 2918 — Acquire the short state-write lock used for multi-file state mutations.
- `plugin_mastodon_state_write_lock_release()` — line 2941 — Release a state-write lock handle.
- `plugin_mastodon_state_write_with_last_error()` — line 4572 — Persist state and copy a failed write reason into the caller-visible last_error field.
- `plugin_mastodon_status_media_attributes()` — line 8251 — Build media_attributes descriptors for PUT /api/v1/statuses/:id.
- `plugin_mastodon_status_missing_response()` — line 10416 — Check whether an API response means that the referenced Mastodon status is missing.
- `plugin_mastodon_status_text_length()` — line 8730 — Calculate the Mastodon-visible length of a plain-text status.
- `plugin_mastodon_store_instance_document()` — line 439 — Persist a compact Mastodon instance snapshot inside the plugin configuration.
- `plugin_mastodon_store_instance_error()` — line 485 — Persist the latest instance-information refresh error for the admin diagnostics view.
- `plugin_mastodon_store_remote_media_url()` — line 8328 — Download and store one remote media URL.
- `plugin_mastodon_stream_context_request()` — line 9849 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 6311 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_strip_leading_quote_block()` — line 10731 — Remove one leading BBCode quote block from imported comment text.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 6376 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_subject_line_is_noise()` — line 6655 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_sync_due()` — line 11777 — Determine whether the scheduled synchronization is currently due.
- `plugin_mastodon_sync_guard_active()` — line 1330 — Return true when a recent scheduled sync/deletion pass should cool down.
- `plugin_mastodon_sync_guard_apcu_key()` — line 1248 — Return the APCu key used for a sync cooldown guard.
- `plugin_mastodon_sync_guard_apcu_store()` — line 1269 — Store one cooldown guard in APCu.
- `plugin_mastodon_sync_guard_clear()` — line 1376 — Clear one sync cooldown guard.
- `plugin_mastodon_sync_guard_entry_active()` — line 1258 — Return true when one guard entry is still active.
- `plugin_mastodon_sync_guard_file_read()` — line 1282 — Read and prune the file-backed cooldown guard.
- `plugin_mastodon_sync_guard_file_write()` — line 1309 — Write the file-backed cooldown guard.
- `plugin_mastodon_sync_guard_kind()` — line 1238 — Normalize the cooldown guard kind.
- `plugin_mastodon_sync_guard_mark()` — line 1355 — Mark a short cooldown guard for a scheduled sync/deletion pass.
- `plugin_mastodon_sync_local_to_remote()` — line 11200 — Synchronize local FlatPress content to Mastodon.
- `plugin_mastodon_sync_remote_to_local()` — line 11133 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_sync_time_local_to_utc()` — line 915 — Convert a FlatPress-local admin synchronization time back to stored UTC time.
- `plugin_mastodon_sync_time_to_minutes()` — line 877 — Convert a HH:MM time value into minutes after midnight.
- `plugin_mastodon_sync_time_utc_to_local()` — line 904 — Convert the stored UTC synchronization time to the FlatPress-local admin time.
- `plugin_mastodon_tag_plugin_active()` — line 6124 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_test_note_local_entry_parse()` — line 9635 — Increment a simulation-only local-entry parse counter when enabled.
- `plugin_mastodon_timestamp_date_key()` — line 2497 — Convert a FlatPress-adjusted timestamp into a stable date key.
- `plugin_mastodon_timestamp_to_flatpress_time()` — line 861 — Convert a real Unix timestamp into FlatPress's offset-adjusted timestamp model.
- `plugin_mastodon_two_digit_year_to_full_year()` — line 9346 — Convert a FlatPress two-digit year segment into the full year used by PHP's legacy date parser.
- `plugin_mastodon_update_status()` — line 10456 — Update an existing Mastodon status.
- `plugin_mastodon_upload_media_items()` — line 9132 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_validate_local_media_item()` — line 7593 — Validate a local media item against known instance upload limits.
- `plugin_mastodon_verify_credentials()` — line 10267 — Verify the currently configured access token.
- `plugin_mastodon_wait_for_media_attachment()` — line 9083 — Wait briefly until an asynchronously uploaded Mastodon media attachment is ready.
- `setup()` — line 12315 — Register the active Mastodon admin template and assign plugin data to Smarty.

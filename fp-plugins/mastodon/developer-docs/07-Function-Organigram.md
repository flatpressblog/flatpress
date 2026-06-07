# 07 – PHP Function Organigram

## Purpose of this document

This document gives developers a structured overview of the Mastodon plugin PHP functions.

The file intentionally combines two views:

- a human-oriented organigram that explains responsibilities, call paths and maintenance rules;
- a generated function catalog with current line references that is checked against `fp-plugins/mastodon/plugin.mastodon.php`.

The plugin currently contains **433** callable functions/methods.

- **430** top-level plugin functions
- **6** admin action methods implemented with the shared method names `setup()`, `main()` or `onsubmit()`

## Function count

The count above is derived from the current PHP source and verified by `developer-docs/check-consistency.php`.
Duplicate admin method names such as `setup()`, `main()` and `onsubmit()` are implemented by separate admin action classes, while the generated catalog records the current effective source line for each shared method name.

## Scope

| Topic          | Current behavior in the current split-state stand                                                                                             |
| -------------- | --------------------------------------------------------------------------------------------------------------------------------------------- |
| State files    | `state.json` keeps global mappings, cursors, queues and `comments_remote`; per-entry comment mappings live below `state-comments/`.           |
| Comment shards | Each entry has one shard file so scheduled/manual sync paths can load only the required comment workset.                                      |
| Reverse index  | `comments_remote` remains global so remote reply imports can detect duplicates without scanning every shard.                                  |
| Migration      | Legacy inline comments are migrated to shards with a `state.json.migration-backup-*` safety copy.                                             |
| Repair         | Admin and CLI maintenance can diagnose shard metadata and rebuild repairable indexes from shard files.                                        |
| Profile widget | `plugin_mastodon_run_sync()` refreshes public profile/avatar cache; the frontend widget reads only local files and never calls Mastodon.      |

The focus is the Mastodon plugin PHP code. Template and language files are mentioned when they are part of an admin flow, but the callable catalog below is generated from `plugin.mastodon.php`.

## Mastodon API Endpoints

| Endpoint                                      | Main helper/function area                     | Use                                                                                                                                                                                     |
| --------------------------------------------- | --------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `GET /.well-known/oauth-authorization-server` | `plugin_mastodon_oauth_server_metadata()`     | Discover OAuth scopes and prefer the narrow `profile` scope when available.                                                                                                             |
| `POST /api/v1/apps`                           | `plugin_mastodon_register_app()`              | Register the FlatPress application with compatible scopes.                                                                                                                              |
| `GET /oauth/authorize`                        | `plugin_mastodon_build_authorize_url()`       | Send the administrator to Mastodon OAuth authorization.                                                                                                                                 |
| `POST /oauth/token`                           | `plugin_mastodon_exchange_code_for_token()`   | Exchange the authorization code for an access token.                                                                                                                                    |
| `GET /api/v1/accounts/verify_credentials`     | `plugin_mastodon_verify_credentials()`        | Validate the configured account, cache account metadata and refresh the local profile-widget cache when avatar data is available.                                                       |
| `GET /api/v1/accounts/:id/statuses`           | `plugin_mastodon_fetch_account_statuses()`    | Discover statuses already posted by the authenticated account; add `exclude_direct=true` when Mastodon 4.6/API v10 support is known and retry once without it on compatible rejections. |
| `GET /api/v1/notifications`                   | `plugin_mastodon_fetch_reply_notifications()` | Prioritize mention notifications as old-thread reply hints.                                                                                                                             |
| `GET /api/v1/statuses/:id/context`            | `plugin_mastodon_fetch_status_context()`      | Import replies and refresh known synchronized threads.                                                                                                                                  |
| `GET /api/v1/statuses/:id`                    | `plugin_mastodon_fetch_status()`              | Verify one remote status or deletion target.                                                                                                                                            |
| `POST /api/v1/statuses`                       | `plugin_mastodon_create_status()`             | Create entry/comment statuses.                                                                                                                                                          |
| `PUT /api/v1/statuses/:id`                    | `plugin_mastodon_update_status()`             | Update changed entry/comment statuses when possible.                                                                                                                                    |
| `DELETE /api/v1/statuses/:id`                 | `plugin_mastodon_delete_status()`             | Delete remote statuses; version-aware `delete_media` handling is centralized.                                                                                                           |
| `GET /api/v2/instance`                        | `plugin_mastodon_upload_media_items()`        | Cache instance limits and feature support.                                                                                                                                              |
| `POST /api/v2/media`                          | `plugin_mastodon_upload_media_items()`        | Upload media before final status creation/update.                                                                                                                                       |
| `GET /api/v1/media/:id`                       | `plugin_mastodon_fetch_media_attachment()`    | Poll asynchronous media processing.                                                                                                                                                     |
| `DELETE /api/v1/media/:id`                    | `plugin_mastodon_delete_media_attachment()`   | Clean up uploaded media when API v4 / Mastodon >= 4.4.0 is known or support is unknown.                                                                                                 |

The local state split does not change these API boundaries. It changes how the plugin stores and loads local mappings before and after calling the same Mastodon API helpers.

## High-level call flow

### Frontend and scheduler entry points

```mermaid
flowchart TD
	Front[FlatPress frontend/admin request] --> Head[plugin_mastodon_head]
	Front --> Widget[plugin_mastodon_widget]
	Widget --> ProfileCache[read local profile/profile.json and avatar only]
	ProfileCache --> WidgetOut{complete local cache?}
	WidgetOut -- yes --> Render[render compact Mastodon widget]
	WidgetOut -- no --> Hide[return no widget]
	Head --> Maybe[plugin_mastodon_maybe_sync]
	Maybe --> Due{scheduled content sync due?}
	Due -- no --> Fast[read scheduler-state only]
	Due -- yes --> Run[plugin_mastodon_run_sync false]
	Run --> ProfileRefresh[refresh profile/avatar cache via verify_credentials]
	ProfileRefresh --> EmptyState[read main state with empty comment workset]
	EmptyState --> Workset[load scheduled/dirty/known-thread shards]
	Workset --> LocalRemote[local to remote export]
	Workset --> OneWay{disable_remote_import enabled?}
	OneWay -- no --> RemoteLocal[remote to local import and thread refresh]
	OneWay -- yes --> SkipRemote[skip Mastodon-to-FlatPress import]
	Run --> Scheduler[write compact scheduler-state]
```

The public widget follows the same response-time rule: `plugin_mastodon_run_sync()` refreshes `fp-content/plugin_mastodon/profile/profile.json` and the local avatar during scheduled/manual syncs, while the widget itself only reads those local files and hides itself when the cache is incomplete. Scheduled requests must keep normal page rendering cheap. They therefore prefer `scheduler-state.json` and only enter full synchronization when the configured due checks pass. When `disable_remote_import` is enabled, the same run keeps the local-to-remote export path active but skips Mastodon-to-FlatPress import and thread refresh. The due checks treat stored `sync_time`, `last_run` and deletion cooldown values as UTC so PHP default timezone drift cannot shift the FlatPress-local admin schedule. In the current split-state stand the scheduled sync opens the main state without all comment shards, then loads only the entry shards selected by the automatic window, dirty queues and known-thread rotation.

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
	Start[remote-to-local phase] --> Enabled{disable_remote_import enabled?}
	Enabled -- yes --> Skip[return without local FlatPress writes]
	Enabled -- no --> Known[Known remote statuses]
	Known --> Context[fetch status context]
	Context --> Desc[process descendants]
	Desc --> Reverse[check global comments_remote]
	Reverse --> Shard[load target entry shard]
	Shard --> Import[import or update local FlatPress comment/entry]
	Import --> Persist[update reverse index and shard]
```

`comments_remote` remains global by design. It gives the remote-import path a fast duplicate check before the code loads the affected entry shard. This avoids scanning all shard files when refreshing a known thread. In explicit one-way mode the remote-import phase returns before these lookups can create, update or delete FlatPress content.

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

Deletion synchronization changed more than the other sync paths because it now iterates over entry shards. The important invariant is that the cursor can resume both between entries and within one comment shard. In explicit one-way mode, missing local mappings are still deleted on Mastodon, while missing Mastodon statuses only unlink stale remote mappings and queue the still-local FlatPress object for a fresh export.

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

The admin panel hides import-only companion diagnostics in explicit one-way mode while it keeps export helper diagnostics visible.

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

- `plugin_mastodon_head()` — line 2367 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_widget()` — line 11103 — Render the compact Mastodon profile widget from local cache only.
- `plugin_mastodon_admin_language_strings()` — line 6191 — Return Mastodon admin-language strings with guaranteed maintenance fallbacks.
- `plugin_mastodon_admin_apply_save_post()` — line 2025 — Apply submitted admin settings while preserving hidden remote-import options.
- `plugin_mastodon_sync_due()` — line 12896 — Determine whether the scheduled synchronization is currently due using stored UTC sync time and UTC `last_run` days.
- `plugin_mastodon_maybe_sync()` — line 13015 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_admin_boolean_label()` — line 13051 — Return a localized yes/no/unknown label for admin diagnostics.
- `plugin_mastodon_admin_add_info_row()` — line 13066 — Add one admin diagnostics row when the value is available.
- `plugin_mastodon_instance_registration_summary()` — line 13087 — Summarize the registration state advertised by the Mastodon instance.
- `plugin_mastodon_admin_instance_info_rows()` — line 13113 — Build the rows for the admin instance-information diagnostics table.
- `plugin_mastodon_admin_panel_link()` — line 13185 — Build a local admin-panel link for Mastodon plugin pages.
- `plugin_mastodon_admin_empty_state_maintenance_result()` — line 13205 — Return an empty admin maintenance result container.
- `plugin_mastodon_admin_state_maintenance_result()` — line 13221 — Convert comment-shard diagnostics into template-friendly admin rows.
- `plugin_mastodon_cli_comment_shard_maintenance()` — line 13258 — Print command-line comment-shard diagnostics or perform a repair.
- `plugin_mastodon_admin_assign()` — line 13292 — Assign plugin data to Smarty for the admin panel.
- `setup()` — line 13428 — Register the active Mastodon admin template and assign plugin data to Smarty.
- `main()` — line 13433 — Keep the FlatPress admin panel lifecycle stable without additional processing.
- `onsubmit()` — line 13437 — Process submitted Mastodon admin actions for the active admin panel.

## B. Defaults, configuration, secrets, and centralized FlatPress feature toggles

This group owns normalized option values, stored secrets, instance snapshots, OAuth scope negotiation, FlatPress time conversion and feature toggles. These helpers should not load comment shards or turn a settings page into a full state scan.

- `plugin_mastodon_default_options()` — line 163 — Return the default plugin option values.
- `plugin_mastodon_clear_saved_instance_info()` — line 197 — Remove any stored Mastodon instance information from the plugin options.
- `plugin_mastodon_compact_instance_document()` — line 216 — Reduce a live Mastodon instance document to the stable subset that is useful for the plugin.
- `plugin_mastodon_saved_instance_document()` — line 433 — Read a previously stored Mastodon instance snapshot from the plugin options.
- `plugin_mastodon_store_instance_document()` — line 478 — Persist a compact Mastodon instance snapshot inside the plugin configuration.
- `plugin_mastodon_store_instance_error()` — line 524 — Persist the latest instance-information refresh error for the admin diagnostics view.
- `plugin_mastodon_refresh_instance_information()` — line 540 — Force a live refresh of the Mastodon instance information and persist the compact snapshot.
- `plugin_mastodon_default_content_stats()` — line 560 — Return the default counters for the last content synchronization.
- `plugin_mastodon_default_deletion_stats()` — line 577 — Return the default counters for the last deletion synchronization.
- `plugin_mastodon_default_state()` — line 590 — Return the default runtime state structure.
- `plugin_mastodon_oauth_legacy_scopes()` — line 627 — Return the legacy OAuth scopes used before scope discovery was added.
- `plugin_mastodon_oauth_profile_scopes()` — line 635 — Return the stricter OAuth scopes preferred on current Mastodon instances.
- `plugin_mastodon_oauth_server_metadata()` — line 652 — Fetch OAuth authorization server metadata for the configured Mastodon instance.
- `plugin_mastodon_oauth_supported_scopes()` — line 671 — Return the OAuth scopes supported by the configured Mastodon instance, if discoverable.
- `plugin_mastodon_oauth_scope_supported()` — line 708 — Determine whether the configured Mastodon instance advertises support for a scope.
- `plugin_mastodon_oauth_preferred_scopes()` — line 729 — Return the preferred OAuth scopes for the configured Mastodon instance.
- `plugin_mastodon_oauth_scopes()` — line 746 — Return the OAuth scopes that the currently registered app may safely request.
- `plugin_mastodon_fp_config()` — line 864 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 900 — Read a nested FlatPress configuration value.
- `plugin_mastodon_fp_timeoffset_seconds()` — line 916 — Return the configured FlatPress time offset in seconds.
- `plugin_mastodon_fp_timeoffset_label()` — line 928 — Format the configured FlatPress time offset as a UTC label for admin users.
- `plugin_mastodon_sync_time_to_minutes()` — line 958 — Convert a HH:MM time value into minutes after midnight.
- `plugin_mastodon_minutes_to_sync_time()` — line 969 — Convert minutes after midnight into a normalized HH:MM time value.
- `plugin_mastodon_sync_time_utc_to_local()` — line 985 — Convert the stored UTC synchronization time to the FlatPress-local admin time.
- `plugin_mastodon_sync_time_local_to_utc()` — line 996 — Convert a FlatPress-local admin synchronization time back to stored UTC time.
- `plugin_mastodon_format_admin_datetime()` — line 1007 — Format a stored UTC timestamp for the admin panel using FlatPress local time and configured formats without depending on PHP's default timezone.
- `plugin_mastodon_get_options()` — line 1956 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_save_options()` — line 2064 — Persist plugin options.
- `plugin_mastodon_secret_key()` — line 2160 — Build the encryption key used for stored secrets.
- `plugin_mastodon_secret_encode()` — line 2177 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_decode()` — line 2200 — Decode a previously stored secret value.
- `plugin_mastodon_normalize_instance_url()` — line 2231 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_head_username()` — line 2264 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_instance_authority()` — line 2303 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_profile_url()` — line 2333 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_fediverse_creator_value()` — line 2349 — Build the fediverse creator meta value.
- `plugin_mastodon_normalize_status_language()` — line 2418 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_configured_status_language()` — line 2440 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_normalize_sync_time()` — line 2460 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_sync_start_date()` — line 2477 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_scheduled_window_days()` — line 2503 — Normalize the automatic scheduled synchronization window.
- `plugin_mastodon_scheduled_window_choices()` — line 2515 — Return the admin radio choices for the scheduled synchronization window.
- `plugin_mastodon_normalize_boolean_option()` — line 2528 — Normalize a boolean-like option value to the stored string representation.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 2544 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_should_update_local_from_remote()` — line 2553 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_normalize_disable_remote_import()` — line 2562 — Normalize the toggle that blocks Mastodon-to-FlatPress imports.
- `plugin_mastodon_should_import_remote_to_local()` — line 2571 — Check whether Mastodon responses may create, update or delete local FlatPress content.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 2580 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 2589 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_normalize_quote_imported_reply_parent()` — line 2598 — Normalize the toggle that controls whether imported Mastodon replies should quote the replied-to comment in FlatPress.
- `plugin_mastodon_should_quote_imported_reply_parent()` — line 2607 — Check whether imported Mastodon replies should include a quote of the replied-to comment.
- `plugin_mastodon_normalize_old_thread_reply_check()` — line 2616 — Normalize the toggle that enables rotating context checks for known synchronized Mastodon threads.
- `plugin_mastodon_should_check_old_thread_replies()` — line 2625 — Check whether known synchronized Mastodon threads should be checked for replies in rotating batches.
- `plugin_mastodon_normalize_delete_sync_enabled()` — line 2671 — Normalize the toggle that enables the follow-up deletion synchronization.
- `plugin_mastodon_should_run_deletion_sync()` — line 2680 — Check whether the follow-up deletion synchronization is enabled.
- `plugin_mastodon_enabled_plugin_state()` — line 6277 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_tag_plugin_active()` — line 6342 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_bbcode_plugin_active()` — line 6359 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_photoswipe_plugin_active()` — line 6376 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_audiovideo_plugin_active()` — line 6393 — Determine whether the Audio/Video plugin is active for the current FlatPress request.
- `plugin_mastodon_emoticons_plugin_active()` — line 6410 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_companion_plugins_status()` — line 6429 — Return mode-aware companion FlatPress plugin diagnostics; one-way mode hides import-only helpers and keeps export helpers.

## C. Caching, filesystem helpers, logging, and persisted state

This group owns request-local caches, FlatPress file I/O wrappers, log rotation, scheduler summaries, sync cooldown guards, state migration, per-entry comment shards, repair/diagnosis, dirty queues, tombstones, pending rechecks and durable state-write error reporting.

- `plugin_mastodon_ensure_profile_dir()` — line 10640 — Ensure the public profile-widget cache directory and directory-listing guard exist.
- `plugin_mastodon_widget_lang_string()` — line 10660 — Return localized public Mastodon widget strings with safe fallbacks.
- `plugin_mastodon_profile_text()` — line 10682 — Normalize public account text before writing it to the profile-widget cache.
- `plugin_mastodon_profile_public_url_allowed()` — line 10697 — Validate public HTTP(S) profile and avatar URLs before storing them.
- `plugin_mastodon_profile_relative_to_absolute()` — line 10715 — Resolve a profile-cache path below `fp-content/plugin_mastodon/profile/` to a local filesystem path.
- `plugin_mastodon_profile_relative_to_url()` — line 10732 — Resolve a profile-cache path below `fp-content/plugin_mastodon/profile/` to a public FlatPress URL.
- `plugin_mastodon_profile_cache_read()` — line 10749 — Read and validate the local public profile-widget cache.
- `plugin_mastodon_profile_cache_write()` — line 10790 — Write the public profile-widget cache without storing secrets.
- `plugin_mastodon_profile_url_from_account()` — line 10808 — Choose the Mastodon profile URL from account data or the configured instance/username.
- `plugin_mastodon_profile_avatar_url_from_account()` — line 10821 — Choose the preferred Mastodon avatar URL, preferring `avatar_static`.
- `plugin_mastodon_profile_avatar_type()` — line 10837 — Detect and allow only supported avatar image MIME types.
- `plugin_mastodon_profile_download_avatar()` — line 10888 — Download, validate and atomically store the avatar in the local profile cache directory.
- `plugin_mastodon_refresh_profile_cache_from_account()` — line 10949 — Build the public profile-widget cache from a verified Mastodon account payload.
- `plugin_mastodon_refresh_profile_cache()` — line 11037 — Refresh the public profile-widget cache through `verify_credentials`.
- `plugin_mastodon_refresh_profile_cache_for_sync()` — line 11056 — Refresh the public profile-widget cache during content synchronization without deciding the sync outcome by itself.
- `plugin_mastodon_widget_avatar_alt()` — line 11082 — Build the localized avatar alt text for the public Mastodon widget.

- `plugin_mastodon_runtime_cache_get()` — line 807 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 831 — Store a value in the request-local plugin cache.
- `plugin_mastodon_runtime_cache_clear()` — line 849 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_io_read_file()` — line 1044 — Read a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_read_file_uncached()` — line 1060 — Read a file without FlatPress request-local caches.
- `plugin_mastodon_file_permissions_mode()` — line 1078 — Return the FlatPress file permission mode for runtime files.
- `plugin_mastodon_apply_file_permissions()` — line 1087 — Apply FlatPress FILE_PERMISSIONS to a runtime file.
- `plugin_mastodon_io_write_file()` — line 1100 — Write a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_append_file()` — line 1121 — Append to a file without re-reading and rewriting the complete payload.
- `plugin_mastodon_log_max_bytes()` — line 1161 — Return the maximum sync.log size before rotation.
- `plugin_mastodon_log_rotate_files()` — line 1172 — Return the number of retained sync.log rotation files.
- `plugin_mastodon_io_rotate_file_if_needed()` — line 1185 — Rotate an append-only log file when the next append would exceed the size cap.
- `plugin_mastodon_apcu_enabled()` — line 1224 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_cache_key()` — line 1233 — Build the plugin APCu key suffix passed to the FlatPress APCu wrappers.
- `plugin_mastodon_apcu_fetch()` — line 1244 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 1259 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_delete()` — line 1272 — Delete a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_state_fallback_key()` — line 1283 — Return the legacy APCu key that used to hold a full last-known-good state fallback.
- `plugin_mastodon_state_fallback_store()` — line 1292 — Remove the legacy full-state APCu fallback instead of storing large mapping arrays.
- `plugin_mastodon_state_fallback_read()` — line 1301 — Full-state APCu fallback is intentionally disabled to avoid multi-MiB APCu entries.
- `plugin_mastodon_sync_guard_kind()` — line 1311 — Normalize the cooldown guard kind.
- `plugin_mastodon_sync_guard_apcu_key()` — line 1321 — Return the plugin-local APCu key suffix used for a sync cooldown guard.
- `plugin_mastodon_sync_guard_entry_active()` — line 1331 — Return true when one guard entry is still active.
- `plugin_mastodon_sync_guard_apcu_store()` — line 1342 — Store one cooldown guard through the FlatPress APCu wrapper.
- `plugin_mastodon_sync_guard_file_read()` — line 1355 — Read and prune the file-backed cooldown guard.
- `plugin_mastodon_sync_guard_file_write()` — line 1382 — Write the file-backed cooldown guard.
- `plugin_mastodon_sync_guard_active()` — line 1403 — Return true when a recent scheduled sync/deletion pass should cool down.
- `plugin_mastodon_sync_guard_mark()` — line 1428 — Mark a short cooldown guard for a scheduled sync/deletion pass.
- `plugin_mastodon_sync_guard_clear()` — line 1449 — Clear one sync cooldown guard from both FlatPress APCu wrappers and the file-backed guard.
- `plugin_mastodon_file_prestat()` — line 1925 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 1945 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_ensure_state_dir()` — line 3005 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_ensure_comment_shard_dir()` — line 3013 — Ensure that the comment-shard directory exists.
- `plugin_mastodon_state_is_entry_id()` — line 3022 — Return whether a string looks like a FlatPress entry id.
- `plugin_mastodon_state_entry_id_from_comment_key()` — line 3031 — Return the entry id portion from a compound comment-state key.
- `plugin_mastodon_state_comment_shard_file()` — line 3042 — Return the shard path for an entry's comment mappings.
- `plugin_mastodon_state_comment_shard_relative_path()` — line 3057 — Return the relative shard path used in state metadata.
- `plugin_mastodon_state_write_json_file()` — line 3071 — Write a JSON payload atomically when possible.
- `plugin_mastodon_state_write_lock_acquire()` — line 3110 — Acquire the short state-write lock used for multi-file state mutations.
- `plugin_mastodon_state_write_lock_release()` — line 3133 — Release a state-write lock handle.
- `plugin_mastodon_json_decode_string_at()` — line 3146 — Decode a JSON string literal starting at the current offset.
- `plugin_mastodon_json_skip_ws()` — line 3183 — Skip JSON whitespace in a string scanner.
- `plugin_mastodon_json_top_level_property_bounds()` — line 3200 — Find a top-level JSON property value in an object payload without decoding the whole object.
- `plugin_mastodon_state_create_migration_backup()` — line 3301 — Create a timestamped backup of the legacy main state before an inline-to-shard migration mutates it.
- `plugin_mastodon_state_migrate_inline_comments_to_shards()` — line 3323 — Iterate a legacy inline comments object and persist per-entry shards without building one giant comments array.
- `plugin_mastodon_state_try_streaming_legacy_migration()` — line 3462 — Migrate legacy inline comment mappings to per-entry shards without decoding the high-volume comments array.
- `plugin_mastodon_state_pending_recheck_entry_ids()` — line 3513 — Return the entry ids referenced by pending comment rechecks.
- `plugin_mastodon_state_unload_comment_shard_from_memory()` — line 3534 — Drop one loaded comment shard from a partial state's in-memory working set.
- `plugin_mastodon_state_group_comments_by_entry()` — line 3559 — Group comment mappings by their parent entry id.
- `plugin_mastodon_state_read_comment_shard()` — line 3587 — Load one per-entry comment shard.
- `plugin_mastodon_state_write_comment_shards()` — line 3622 — Write all per-entry comment shards.
- `plugin_mastodon_state_main_payload()` — line 3680 — Build the main state payload without high-volume inline comment mappings.
- `plugin_mastodon_state_load_comment_shards()` — line 3699 — Load comment shards into a state array.
- `plugin_mastodon_state_comment_shards_partial()` — line 3739 — Return whether a state array only contains a subset of comment shards.
- `plugin_mastodon_state_loaded_comment_entry_ids()` — line 3748 — Return the entry ids whose comment shards are loaded in a partial state.
- `plugin_mastodon_state_comment_entry_loaded()` — line 3767 — Check whether one entry's comment shard is loaded into the state array.
- `plugin_mastodon_state_mark_comment_entry_loaded()` — line 3781 — Mark one entry's comment shard as loaded in a partial state.
- `plugin_mastodon_state_load_comment_shard_into()` — line 3801 — Load one entry comment shard into a partial state when needed.
- `plugin_mastodon_state_comment_shard_entry_ids()` — line 3822 — Return entry ids with known comment shards.
- `plugin_mastodon_state_comment_shard_files()` — line 3839 — Return all comment shard files currently present on disk keyed by entry id.
- `plugin_mastodon_state_diagnose_comment_shards()` — line 3891 — Scan shard files and compare them with the main-state metadata and reverse comment index.
- `plugin_mastodon_state_repair_comment_shards()` — line 4006 — Rebuild the main shard metadata and global comments_remote reverse index from shard files.
- `plugin_mastodon_state_cleanup_stale_comment_shards()` — line 4046 — Remove shard files that are no longer referenced by the main state.
- `plugin_mastodon_state_recover_from_comment_shards()` — line 4103 — Return a recovered state if comment shards were written but the main state is missing.
- `plugin_mastodon_log()` — line 4168 — Append a line to the plugin sync log.
- `plugin_mastodon_log_skip()` — line 4183 — Aggregate high-volume skip messages until the current sync phase ends.
- `plugin_mastodon_log_flush_skip_summaries()` — line 4213 — Flush aggregated skip log messages.
- `plugin_mastodon_state_read()` — line 4243 — Load the persisted runtime state from disk.
- `plugin_mastodon_scheduler_state_default()` — line 4288 — Return an empty scheduler state derived from the full default state.
- `plugin_mastodon_scheduler_source_signature()` — line 4309 — Return the current stat-based signature of the full state file.
- `plugin_mastodon_scheduler_state_normalize()` — line 4318 — Normalize a scheduler summary without touching full mapping arrays.
- `plugin_mastodon_scheduler_state_from_state()` — line 4356 — Build the lightweight scheduler summary from a full runtime state.
- `plugin_mastodon_scheduler_state_write()` — line 4376 — Persist the lightweight scheduler state. Failure only disables the request-time optimization.
- `plugin_mastodon_scheduler_state_decode_fresh()` — line 4402 — Decode a scheduler-state JSON payload only when it matches the current full-state signature.
- `plugin_mastodon_scheduler_state_read()` — line 4421 — Load the lightweight scheduler summary and rebuild it conservatively when stale.
- `plugin_mastodon_state_write()` — line 4467 — Persist the runtime state to disk.
- `plugin_mastodon_normalize_deletions_pending_scope()` — line 4524 — Normalize the pending deletion scope marker.
- `plugin_mastodon_state_normalize()` — line 4537 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_comment_key()` — line 4589 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_comment_mappings()` — line 4598 — Return the loaded comment mapping array from a state object.
- `plugin_mastodon_state_set_comment_mappings()` — line 4616 — Replace the loaded comment mapping array in a state object.
- `plugin_mastodon_state_comment_remote_mappings()` — line 4634 — Return the global remote-comment reverse index.
- `plugin_mastodon_state_set_comment_remote_mappings()` — line 4660 — Replace the global remote-comment reverse index in a state object.
- `plugin_mastodon_state_comment_shard_entries()` — line 4686 — Return the main-state metadata for per-entry comment shards.
- `plugin_mastodon_state_write_error_set()` — line 4704 — Store a state write error for callers that need a last_error message even when persistence fails.
- `plugin_mastodon_state_write_error_clear()` — line 4712 — Clear the last state write error marker.
- `plugin_mastodon_state_write_last_error()` — line 4720 — Return the last state write error marker from the current request.
- `plugin_mastodon_state_write_with_last_error()` — line 4730 — Persist state and copy a failed write reason into the caller-visible last_error field.
- `plugin_mastodon_state_set_entry_mapping()` — line 4760 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_comment_mapping()` — line 4799 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_remove_entry_mapping()` — line 4841 — Remove the mapping between a local entry and a remote status.
- `plugin_mastodon_state_remove_comment_mapping()` — line 4863 — Remove the mapping between a local comment and a remote status.
- `plugin_mastodon_state_unlink_entry_remote_for_reexport()` — line 4896 — Drop a stale remote entry mapping while keeping the local entry queued for a new export.
- `plugin_mastodon_state_unlink_comment_remote_for_reexport()` — line 4916 — Drop a stale remote comment mapping while keeping the local comment queued for a new export.
- `plugin_mastodon_state_set_dirty_entry()` — line 4937 — Add an older changed entry to the persistent dirty queue.
- `plugin_mastodon_state_remove_dirty_entry()` — line 4957 — Remove an entry from the dirty queue.
- `plugin_mastodon_state_has_dirty_entry()` — line 4970 — Check whether an entry is queued for synchronization although it is outside the scheduled window.
- `plugin_mastodon_state_set_dirty_comment()` — line 4983 — Add an older changed comment to the persistent dirty queue.
- `plugin_mastodon_state_remove_dirty_comment()` — line 5006 — Remove a comment from the dirty queue.
- `plugin_mastodon_state_has_dirty_comment()` — line 5020 — Check whether a comment is queued for synchronization although it is outside the scheduled window.
- `plugin_mastodon_local_write_guard_enter()` — line 5029 — Increase the local-write guard depth while the plugin mirrors remote Mastodon data into FlatPress.
- `plugin_mastodon_local_write_guard_leave()` — line 5040 — Decrease the local-write guard depth after a plugin-owned FlatPress write.
- `plugin_mastodon_local_write_guard_active()` — line 5052 — Return whether FlatPress write hooks are currently triggered by Mastodon remote mirroring.
- `plugin_mastodon_dirty_tracking_options()` — line 5060 — Check whether dirty tracking should persist state for a local FlatPress write hook.
- `plugin_mastodon_on_entry_saved()` — line 5079 — Mark a local FlatPress entry write as dirty for a later Mastodon sync.
- `plugin_mastodon_on_entry_deleted()` — line 5128 — Mark a local FlatPress entry deletion for the Mastodon deletion sync.
- `plugin_mastodon_on_comment_saved()` — line 5155 — Mark a local FlatPress comment write as dirty for a later Mastodon sync.
- `plugin_mastodon_on_comment_deleted()` — line 5216 — Mark a local FlatPress comment deletion for the Mastodon deletion sync.
- `plugin_mastodon_state_get_entry_meta()` — line 5251 — Return mapping metadata for a local entry.
- `plugin_mastodon_state_set_entry_media_meta()` — line 5265 — Persist media metadata for a synchronized local entry.
- `plugin_mastodon_state_entry_remote_media()` — line 5294 — Return sanitized remote-media descriptors stored inside one entry mapping.
- `plugin_mastodon_state_entry_media_attachment_signature()` — line 5324 — Return the stored attachment-signature for one entry mapping.
- `plugin_mastodon_state_entry_media_description_signature()` — line 5334 — Return the stored description-signature for one entry mapping.
- `plugin_mastodon_state_get_comment_meta()` — line 5346 — Return mapping metadata for a local comment.
- `plugin_mastodon_state_set_comment_tombstone()` — line 5361 — Store a tombstone that blocks re-importing a deleted remote comment status.
- `plugin_mastodon_state_has_comment_tombstone()` — line 5381 — Check whether one remote Mastodon comment status was tombstoned locally.
- `plugin_mastodon_protect_locally_deleted_exported_comments()` — line 5392 — Protect locally deleted exported FlatPress comments from stale Mastodon re-imports before deletion sync runs.
- `plugin_mastodon_reattach_local_comment_to_entry_status()` — line 5442 — Reattach one imported local comment to the synchronized entry status after its remote parent reply disappeared.
- `plugin_mastodon_state_remove_pending_comment_remote_recheck()` — line 5502 — Remove one pending descendant recheck marker.
- `plugin_mastodon_state_get_pending_comment_remote_recheck()` — line 5516 — Return one pending descendant recheck marker.
- `plugin_mastodon_state_set_pending_comment_remote_recheck()` — line 5531 — Mark one local comment for follow-up verification after an ancestor status disappeared remotely.
- `plugin_mastodon_state_set_deletions_pending()` — line 5557 — Update the runtime marker that tells the scheduler whether another deletion follow-up request is required.
- `plugin_mastodon_deletion_sync_due()` — line 5571 — Determine whether a pending deletion synchronization may start now using UTC cooldown timestamps.
- `plugin_mastodon_state_has_comment_recheck_scope()` — line 5599 — Check whether the current deletion follow-up request should focus on pending descendant reply rechecks only.
- `plugin_mastodon_build_comment_remote_child_index()` — line 5608 — Build an index of mapped local comments grouped by their direct remote parent status.
- `plugin_mastodon_queue_comment_descendant_remote_rechecks()` — line 5639 — Queue the direct mapped local children of one deleted remote comment for additional verification passes.
- `plugin_mastodon_process_pending_comment_remote_rechecks()` — line 5678 — Process queued descendant reply rechecks using a small FIFO queue.

## D. Date, timestamp, visibility, and threading helpers

This group decides whether local and remote items belong to the configured synchronization start date and automatic content window. It also resolves comment parentage and reply targets without loading unrelated comment shards.

- `plugin_mastodon_timestamp_to_flatpress_time()` — line 942 — Convert a real Unix timestamp into FlatPress's offset-adjusted timestamp model.
- `plugin_mastodon_timestamp_date_key()` — line 2689 — Convert a FlatPress-adjusted timestamp into a stable date key.
- `plugin_mastodon_local_item_date_key()` — line 2703 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_remote_status_date_key()` — line 2726 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_datetime_date_key()` — line 2752 — Normalize a date/datetime string to a sync-start date key.
- `plugin_mastodon_date_matches_sync_start()` — line 2776 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_scheduled_window_start_date()` — line 2794 — Return the FlatPress-local date key that starts the automatic scheduled sync window.
- `plugin_mastodon_date_matches_content_window()` — line 2811 — Determine whether a date is inside the manual lower bound and, for scheduled runs, the automatic window.
- `plugin_mastodon_local_item_matches_sync_start()` — line 2833 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_local_item_matches_content_window()` — line 2845 — Determine whether a local FlatPress item is inside the active content synchronization window.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 2855 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_remote_status_matches_content_window()` — line 2866 — Determine whether a remote Mastodon status is inside the active content synchronization window.
- `plugin_mastodon_mapping_matches_sync_start()` — line 2877 — Determine whether a synchronized mapping should participate in the deletion follow-up for the current sync start date.
- `plugin_mastodon_mapping_effective_date_key()` — line 2925 — Return the most stable date key available for a synchronized mapping.
- `plugin_mastodon_mapping_matches_deletion_lookup_window()` — line 2966 — Determine whether an existing local mapping should be remotely checked for deletion in this run.
- `plugin_mastodon_mapping_keys_after_cursor()` — line 2987 — Return mapping keys ordered after a saved deletion cursor.
- `plugin_mastodon_parse_iso_datetime()` — line 5794 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 5813 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_remote_status_timestamp()` — line 5836 — Resolve the best timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 5855 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 5868 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_comment_parent_fields()` — line 5880 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_normalize_comment_parent_id()` — line 5889 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 5906 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_resolve_comment_reply_target()` — line 5928 — Resolve the remote reply target for a local comment export.
- `plugin_mastodon_local_comment_parent_export_pending()` — line 5953 — Determine whether a local parent comment should be exported before its child reply.
- `plugin_mastodon_list_local_comment_ids()` — line 5981 — List local FlatPress comment identifiers by scanning the entry comment directory directly.

## E. Text, URLs, language strings, tags, emojis, and BBCode/HTML conversion

This group converts between FlatPress text/BBCode/HTML and Mastodon-ready plain text or FlatPress BBCode. It also handles localized strings, public URLs, FlatPress tags, Mastodon hashtags and Emoticons shortcode conversion.

- `plugin_mastodon_guess_subject()` — line 6018 — Guess a subject line from imported plain text.
- `plugin_mastodon_html_entity_decode()` — line 6060 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_blog_base_url()` — line 6069 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_extract_url_token()` — line 6105 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_absolute_url()` — line 6122 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_lang_string()` — line 6164 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_normalize_tag_list()` — line 6496 — Normalize a list of tag labels.
- `plugin_mastodon_extract_flatpress_tags()` — line 6527 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 6552 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 6569 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_remote_status_tags()` — line 6590 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 6617 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 6680 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 6693 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 6706 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 6760 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_prepare_emoticons_for_mastodon()` — line 6775 — Convert FlatPress emoticon shortcodes to Mastodon-safe Unicode glyphs when the plugin is active.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 6788 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_is_public_host()` — line 6810 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_public_url_for_mastodon()` — line 6833 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_plain_text_from_bbcode()` — line 6852 — Convert FlatPress BBCode into plain text for Mastodon export.
- `plugin_mastodon_subject_line_is_noise()` — line 6896 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_domains_match()` — line 6924 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_cleanup_imported_text()` — line 6938 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_dom_children_to_flatpress()` — line 6992 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 7010 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_public_entry_url()` — line 7119 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_comments_url()` — line 7146 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_comment_url()` — line 7174 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 7189 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_flatpress_to_mastodon()` — line 7291 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_limit_text()` — line 7408 — Limit text to a maximum number of characters.

## F. Local content access, media processing, hashing, and export ordering

This group reads local entries/comments, handles local and remote media descriptors, computes change signatures and builds efficient direct scanner candidate lists for scheduled and full synchronization paths.

- `plugin_mastodon_entry_hash()` — line 7430 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_comment_hash()` — line 7442 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_safe_path_component()` — line 7460 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_safe_filename()` — line 7475 — Sanitize a file name for local storage.
- `plugin_mastodon_normalize_media_relative_path()` — line 7488 — Normalize a FlatPress media path relative to fp-content.
- `plugin_mastodon_media_relative_to_absolute()` — line 7511 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_media_prepare_directory()` — line 7524 — Ensure that a media directory exists.
- `plugin_mastodon_media_delete_tree()` — line 7540 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_copy_tree()` — line 7567 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_bbcode_attr_escape()` — line 7605 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_bbcode_text_escape()` — line 7617 — Escape plain text embedded between BBCode tags.
- `plugin_mastodon_media_guess_mime_type()` — line 7639 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_type_from_mime()` — line 7706 — Return a stable FlatPress/Mastodon media family for a MIME type.
- `plugin_mastodon_extension_from_mime_type()` — line 7740 — Return an appropriate file extension for a MIME type.
- `plugin_mastodon_instance_supported_media_mime_types()` — line 7787 — Return instance-advertised supported media MIME types.
- `plugin_mastodon_instance_media_size_limit()` — line 7807 — Return the configured byte-size limit for a media family, or 0 if unknown.
- `plugin_mastodon_validate_local_media_item()` — line 7834 — Validate a local media item against known instance upload limits.
- `plugin_mastodon_media_parse_tag_attributes()` — line 7861 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_media_description_from_bbcode_content()` — line 7892 — Normalize optional AudioVideo BBCode content into a Mastodon media description.
- `plugin_mastodon_media_extract_default_path()` — line 7918 — Extract the default path parameter from a FlatPress media tag attribute string.
- `plugin_mastodon_add_local_media_item()` — line 7947 — Append a local media item to the collection while avoiding duplicates.
- `plugin_mastodon_collect_local_entry_media()` — line 7996 — Collect local images, galleries, audio and video referenced by an entry.
- `plugin_mastodon_select_status_media_items()` — line 8142 — Select a Mastodon-compatible media set for one status.
- `plugin_mastodon_prepare_entry_media_items()` — line 8203 — Normalize local entry media items for signature and export planning.
- `plugin_mastodon_entry_media_attachment_signature_from_items()` — line 8231 — Build a signature for the actual media payload of local entry attachments.
- `plugin_mastodon_entry_media_description_signature_from_items()` — line 8254 — Build a signature for local entry media descriptions.
- `plugin_mastodon_entry_media_signature()` — line 8273 — Build a signature for media references contained in entry content.
- `plugin_mastodon_remote_media_attachment_type()` — line 8290 — Return the normalized Mastodon attachment type.
- `plugin_mastodon_remote_status_media_attachments()` — line 8317 — Extract supported media attachments from a remote Mastodon status.
- `plugin_mastodon_remote_media_source_url()` — line 8347 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_media_source_urls()` — line 8365 — Return direct-download candidate URLs for a remote media attachment.
- `plugin_mastodon_remote_media_description()` — line 8387 — Resolve the best description for a remote attachment.
- `plugin_mastodon_remote_media_focus()` — line 8401 — Resolve the stored focus string for a remote attachment, if any.
- `plugin_mastodon_remote_media_descriptors_from_status()` — line 8418 — Build sanitized remote-media descriptors from a Mastodon status payload.
- `plugin_mastodon_remote_media_descriptors_from_media_ids()` — line 8449 — Build remote-media descriptors from freshly uploaded media IDs and the current local descriptions.
- `plugin_mastodon_status_media_attributes()` — line 8483 — Build media_attributes descriptors for PUT /api/v1/statuses/:id.
- `plugin_mastodon_media_download()` — line 8516 — Download a remote media asset.
- `plugin_mastodon_remote_download_basename()` — line 8530 — Build a safe basename for a downloaded remote attachment.
- `plugin_mastodon_build_imported_media_bbcode()` — line 8563 — Build FlatPress BBCode for imported remote media attachments.
- `plugin_mastodon_is_two_digit_path_segment()` — line 9689 — Determine whether a FlatPress content-tree path segment is a two-digit date segment.
- `plugin_mastodon_two_digit_year_to_full_year()` — line 9698 — Convert a FlatPress two-digit year segment into the full year used by PHP's legacy date parser.
- `plugin_mastodon_entry_month_end_date_key()` — line 9709 — Return the last date key that can occur inside a FlatPress YY/MM content directory.
- `plugin_mastodon_entry_month_may_match_scheduled_window()` — line 9731 — Determine whether a FlatPress YY/MM directory can contain scheduled-sync entry candidates.
- `plugin_mastodon_collect_entry_files_from_month()` — line 9751 — Collect direct entry files from one canonical FlatPress YY/MM content directory.
- `plugin_mastodon_collect_entry_files_legacy()` — line 9784 — Legacy fallback for non-canonical content trees.
- `plugin_mastodon_collect_entry_files()` — line 9812 — Collect canonical entry files directly from FlatPress YY/MM content directories.
- `plugin_mastodon_collect_scheduled_entry_files()` — line 9855 — Collect scheduled-sync entry candidates directly from relevant FlatPress YY/MM content directories.
- `plugin_mastodon_add_dirty_entry_files()` — line 9900 — Append existing dirty entry files by canonical FlatPress ID.
- `plugin_mastodon_collect_entry_files_for_sync()` — line 9920 — Collect local entry files for a local-to-remote synchronization pass; scheduled runs add all dirty parents without a hard cap.
- `plugin_mastodon_local_item_timestamp()` — line 9939 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_compare_local_entries_for_export()` — line 9968 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_test_note_local_entry_parse()` — line 9987 — Increment a simulation-only local-entry parse counter when enabled.
- `plugin_mastodon_dirty_entry_id_lookup()` — line 9999 — Return entry identifiers that are explicitly queued by dirty entry/comment hooks.
- `plugin_mastodon_content_sync_comment_entry_ids()` — line 10029 — Return entry ids whose comment shards may be needed by the current content-sync workset.
- `plugin_mastodon_state_load_content_sync_comment_workset()` — line 10053 — Load comment shards needed for the current content synchronization workset.
- `plugin_mastodon_should_parse_local_entry_for_sync()` — line 10069 — Determine whether a local entry file should be parsed during a scheduled local-to-remote pass.
- `plugin_mastodon_list_local_entries_for_sync()` — line 10094 — List local FlatPress entries for a local-to-remote synchronization pass.
- `plugin_mastodon_list_local_entries()` — line 10133 — List local FlatPress entries ordered for export.

## G. HTTP transport, PHP timeout budgeting, instance capability lookup, status-length budgeting, OAuth, Mastodon API calls, and media upload

This group performs network-facing work: request budgeting, persistent rate-limit windows, HTTP transport, OAuth API calls, instance capability lookup, status length budgeting, media upload/polling/cleanup and Mastodon status create/update/delete helpers.

- `plugin_mastodon_rate_limit_default_budgets()` — line 1464 — Return the default Mastodon API budgets for one synchronization run.
- `plugin_mastodon_rate_limit_window_budgets()` — line 1488 — Return the persistent Mastodon API window budgets across synchronization runs.
- `plugin_mastodon_rate_limit_window_config()` — line 1515 — Return persistent window configuration for one request kind.
- `plugin_mastodon_rate_limit_window_entry()` — line 1535 — Normalize one persistent rate-limit window entry.
- `plugin_mastodon_rate_limit_window_read()` — line 1555 — Read the persistent rate-limit windows.
- `plugin_mastodon_rate_limit_window_write()` — line 1587 — Persist the current rate-limit windows.
- `plugin_mastodon_rate_limit_window_acquire()` — line 1612 — Reserve one item from a persistent cross-run Mastodon rate-limit window.
- `plugin_mastodon_rate_limit_window_clear()` — line 1661 — Clear persistent rate-limit windows, mainly for tests and recovery tooling.
- `plugin_mastodon_rate_limit_guard_start()` — line 1673 — Start a per-run Mastodon API rate-limit guard.
- `plugin_mastodon_rate_limit_guard_stop()` — line 1699 — Stop the current per-run Mastodon API rate-limit guard while keeping its summary inspectable.
- `plugin_mastodon_rate_limit_guard_active()` — line 1710 — Return whether a per-run Mastodon API rate-limit guard is active.
- `plugin_mastodon_rate_limit_guard_summary()` — line 1718 — Return the current rate-limit guard summary.
- `plugin_mastodon_rate_limit_headers()` — line 1727 — Normalize a response header map for rate-limit checks.
- `plugin_mastodon_rate_limit_request_kind()` — line 1748 — Determine whether a Mastodon API request consumes one of the stricter per-run budgets.
- `plugin_mastodon_rate_limit_block()` — line 1773 — Mark the current rate-limit guard as blocked.
- `plugin_mastodon_rate_limit_acquire()` — line 1817 — Reserve budget for one Mastodon API request.
- `plugin_mastodon_rate_limit_observe_response()` — line 1862 — Update the current per-run guard from Mastodon rate-limit response headers.
- `plugin_mastodon_rate_limit_blocked_reason()` — line 1886 — Return the current rate-limit block reason, if any.
- `plugin_mastodon_rate_limit_blocked_response()` — line 1898 — Build a synthetic API response for locally blocked Mastodon requests.
- `plugin_mastodon_rate_limit_state_error()` — line 1915 — Return the rate-limit reason that should be written to sync state, if any.
- `plugin_mastodon_extend_time_limit()` — line 8759 — Best-effort refresh/increase of the PHP execution time budget for long-running Mastodon work.
- `plugin_mastodon_instance_document()` — line 8790 — Load and cache the full Mastodon instance document returned by /api/v2/instance.
- `plugin_mastodon_normalized_instance_version()` — line 8842 — Normalize a human-readable Mastodon version string for version_compare().
- `plugin_mastodon_instance_document_api_version()` — line 8865 — Return the machine-readable Mastodon API compatibility version from an instance document.
- `plugin_mastodon_instance_supports_status_media_attributes()` — line 8891 — Determine whether the configured Mastodon instance should support media description updates on already-posted statuses.
- `plugin_mastodon_instance_supports_mastodon_api_v4()` — line 8917 — Centralizes cached `api_versions[mastodon] >= 4` / Mastodon `>= 4.4.0` capability decisions for status deletion and unattached-media cleanup without extra network requests.
- `plugin_mastodon_instance_supports_account_statuses_exclude_direct()` — line 8947 — Determine whether cached instance information confirms support for the Mastodon 4.6 `exclude_direct` account-status filter.
- `plugin_mastodon_account_statuses_exclude_direct_should_retry_without()` — line 8970 — Check whether a failed account-status request may be retried without the optional `exclude_direct` filter for compatible or legacy Mastodon servers.
- `plugin_mastodon_instance_supports_status_delete_media()` — line 8997 — Determine whether cached instance information confirms support for the delete_media query parameter on DELETE /api/v1/statuses/:id.
- `plugin_mastodon_instance_supports_media_delete()` — line 9013 — Determine whether cached instance information confirms support for deleting unattached uploaded media through DELETE /api/v1/media/:id.
- `plugin_mastodon_instance_configuration()` — line 9022 — Load and cache the Mastodon instance configuration document.
- `plugin_mastodon_instance_media_limit()` — line 9032 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_media_description_limit()` — line 9045 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_url_reserved_length()` — line 9058 — Return the per-URL character budget used by the configured instance.
- `plugin_mastodon_status_text_length()` — line 9072 — Calculate the Mastodon-visible length of a plain-text status.
- `plugin_mastodon_limit_status_text()` — line 9104 — Limit Mastodon plain text while respecting the instance URL budget.
- `plugin_mastodon_http_request_multipart()` — line 9183 — Perform a multipart HTTP request.
- `plugin_mastodon_fetch_media_attachment()` — line 9316 — Fetch the current processing status of a Mastodon media attachment.
- `plugin_mastodon_delete_media_attachment()` — line 9326 — Delete an uploaded Mastodon media attachment before it is attached to a final status.
- `plugin_mastodon_cleanup_uploaded_media()` — line 9349 — Best-effort cleanup for uploaded Mastodon media that never reached a final status request.
- `plugin_mastodon_media_processing_attempts()` — line 9391 — Determine how patiently the plugin should wait for Mastodon media processing.
- `plugin_mastodon_media_transfer_timeout()` — line 9415 — Determine a practical transfer timeout for one media upload.
- `plugin_mastodon_wait_for_media_attachment()` — line 9435 — Wait briefly until an asynchronously uploaded Mastodon media attachment is ready.
- `plugin_mastodon_upload_media_items()` — line 9484 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_prepare_entry_media_sync_plan()` — line 9601 — Decide whether a local entry update can reuse already-uploaded Mastodon media or needs a fresh upload.
- `plugin_mastodon_parse_http_response_headers()` — line 10171 — Parse raw HTTP response headers.
- `plugin_mastodon_stream_context_request()` — line 10201 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_array_is_list()` — line 10241 — Detect whether a value is a numerically indexed list.
- `plugin_mastodon_array_contains_only_form_scalars()` — line 10261 — Detect whether a list contains only scalar-compatible form values.
- `plugin_mastodon_http_build_query()` — line 10279 — Build an application/x-www-form-urlencoded query string for Mastodon requests.
- `plugin_mastodon_http_request()` — line 10335 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_mastodon_api()` — line 10454 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_json()` — line 10504 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_response_error_message()` — line 10519 — Extract the most useful error message from an API response.
- `plugin_mastodon_register_app()` — line 10548 — Register the FlatPress application on the configured Mastodon instance.
- `plugin_mastodon_build_authorize_url()` — line 10571 — Build the OAuth authorization URL.
- `plugin_mastodon_exchange_code_for_token()` — line 10591 — Exchange an OAuth authorization code for an access token.
- `plugin_mastodon_verify_credentials()` — line 10621 — Verify the currently configured access token.
- `plugin_mastodon_instance_character_limit()` — line 11148 — Return the status character limit of the configured instance.
- `plugin_mastodon_fetch_account_statuses()` — line 11163 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_status_context()` — line 11221 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_fetch_status()` — line 11255 — Fetch a single Mastodon status.
- `plugin_mastodon_delete_status()` — line 11266 — Delete a Mastodon status.
- `plugin_mastodon_delete_status_should_retry_without_delete_media()` — line 11294 — Check whether a failed status deletion may be caused by Mastodon versions before 4.4.0 not understanding the optional delete_media query parameter.
- `plugin_mastodon_status_missing_response()` — line 11314 — Check whether an API response means that the referenced Mastodon status is missing.
- `plugin_mastodon_create_status()` — line 11327 — Create a Mastodon status.
- `plugin_mastodon_update_status()` — line 11354 — Update an existing Mastodon status.

## H. Import/export builders and synchronization orchestration

This group composes lower-level helpers into visible synchronization behavior. It is the first group to inspect when automatic sync, normal manual sync, full manual sync, full deletion continuation or rotating known-thread refresh behaves differently after a state-format change.

- `plugin_mastodon_build_entry_status_text()` — line 11380 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_comment_status_text()` — line 11448 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_import_remote_entry()` — line 11486 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_remote_status_author_label()` — line 11600 — Build a readable account label for a Mastodon status author.
- `plugin_mastodon_strip_leading_quote_block()` — line 11633 — Remove one leading BBCode quote block from imported comment text.
- `plugin_mastodon_imported_reply_quote_payload()` — line 11666 — Resolve the author and body that should be quoted for an imported Mastodon reply.
- `plugin_mastodon_build_imported_reply_quote()` — line 11709 — Build an optional BBCode quote block for an imported Mastodon reply.
- `plugin_mastodon_import_remote_comment()` — line 11751 — Import a remote Mastodon reply into FlatPress as a comment.
- `plugin_mastodon_import_remote_context_descendants()` — line 12018 — Import remote Mastodon replies from a fetched thread context.
- `plugin_mastodon_old_thread_context_rotation_limit()` — line 12124 — Return the maximum number of known synchronized threads checked for replies per content sync run.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 12141 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
- `plugin_mastodon_sync_remote_to_local()` — line 12217 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_sync_local_to_remote()` — line 12299 — Synchronize local FlatPress content to Mastodon.
- `plugin_mastodon_run_deletion_sync()` — line 12531 — Run the deletion synchronization in a follow-up request after content sync completed.
- `plugin_mastodon_run_sync()` — line 12927 — Run a full synchronization cycle.

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

- `main()` — line 13433 — Keep the FlatPress admin panel lifecycle stable without additional processing.
- `onsubmit()` — line 13437 — Process submitted Mastodon admin actions for the active admin panel.
- `plugin_mastodon_absolute_url()` — line 6122 — Convert a URL or path into an absolute URL when possible.
- `plugin_mastodon_account_statuses_exclude_direct_should_retry_without()` — line 8970 — Check whether a failed account-status request may be retried without the optional `exclude_direct` filter for compatible or legacy Mastodon servers.
- `plugin_mastodon_add_dirty_entry_files()` — line 9900 — Append existing dirty entry files by canonical FlatPress ID.
- `plugin_mastodon_add_local_media_item()` — line 7947 — Append a local media item to the collection while avoiding duplicates.
- `plugin_mastodon_admin_add_info_row()` — line 13066 — Add one admin diagnostics row when the value is available.
- `plugin_mastodon_admin_apply_save_post()` — line 2025 — Apply submitted admin settings while preserving hidden remote-import options.
- `plugin_mastodon_admin_assign()` — line 13292 — Assign plugin data to Smarty for the admin panel.
- `plugin_mastodon_admin_boolean_label()` — line 13051 — Return a localized yes/no/unknown label for admin diagnostics.
- `plugin_mastodon_admin_empty_state_maintenance_result()` — line 13205 — Return an empty admin maintenance result container.
- `plugin_mastodon_admin_instance_info_rows()` — line 13113 — Build the rows for the admin instance-information diagnostics table.
- `plugin_mastodon_admin_language_strings()` — line 6191 — Return Mastodon admin-language strings with guaranteed maintenance fallbacks.
- `plugin_mastodon_admin_panel_link()` — line 13185 — Build a local admin-panel link for Mastodon plugin pages.
- `plugin_mastodon_admin_state_maintenance_result()` — line 13221 — Convert comment-shard diagnostics into template-friendly admin rows.
- `plugin_mastodon_apcu_cache_key()` — line 1233 — Build the plugin APCu key suffix passed to the FlatPress APCu wrappers.
- `plugin_mastodon_apcu_delete()` — line 1272 — Delete a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_enabled()` — line 1224 — Check whether shared APCu caching is available for the plugin.
- `plugin_mastodon_apcu_fetch()` — line 1244 — Fetch a value from APCu through the FlatPress namespace helper.
- `plugin_mastodon_apcu_store()` — line 1259 — Store a value in APCu through the FlatPress namespace helper.
- `plugin_mastodon_apply_file_permissions()` — line 1087 — Apply FlatPress FILE_PERMISSIONS to a runtime file.
- `plugin_mastodon_array_contains_only_form_scalars()` — line 10261 — Detect whether a list contains only scalar-compatible form values.
- `plugin_mastodon_array_is_list()` — line 10241 — Detect whether a value is a numerically indexed list.
- `plugin_mastodon_audiovideo_plugin_active()` — line 6393 — Determine whether the Audio/Video plugin is active for the current FlatPress request.
- `plugin_mastodon_bbcode_attr_escape()` — line 7605 — Escape a value for safe BBCode attribute usage.
- `plugin_mastodon_bbcode_plugin_active()` — line 6359 — Determine whether the BBCode plugin is active for the current FlatPress request.
- `plugin_mastodon_bbcode_text_escape()` — line 7617 — Escape plain text embedded between BBCode tags.
- `plugin_mastodon_blog_base_url()` — line 6069 — Return the absolute base URL of the current FlatPress installation.
- `plugin_mastodon_build_authorize_url()` — line 10571 — Build the OAuth authorization URL.
- `plugin_mastodon_build_comment_remote_child_index()` — line 5608 — Build an index of mapped local comments grouped by their direct remote parent status.
- `plugin_mastodon_build_comment_status_text()` — line 11448 — Build the status body used when exporting a FlatPress comment.
- `plugin_mastodon_build_entry_status_text()` — line 11380 — Build the status body used when exporting a FlatPress entry.
- `plugin_mastodon_build_flatpress_tag_bbcode()` — line 6680 — Build Tag plugin BBCode from a list of remote Mastodon tags.
- `plugin_mastodon_build_imported_media_bbcode()` — line 8563 — Build FlatPress BBCode for imported remote media attachments.
- `plugin_mastodon_build_imported_reply_quote()` — line 11709 — Build an optional BBCode quote block for an imported Mastodon reply.
- `plugin_mastodon_cleanup_imported_text()` — line 6938 — Clean imported text before saving it to FlatPress.
- `plugin_mastodon_cleanup_uploaded_media()` — line 9349 — Best-effort cleanup for uploaded Mastodon media that never reached a final status request.
- `plugin_mastodon_clear_saved_instance_info()` — line 197 — Remove any stored Mastodon instance information from the plugin options.
- `plugin_mastodon_cli_comment_shard_maintenance()` — line 13258 — Print command-line comment-shard diagnostics or perform a repair.
- `plugin_mastodon_collect_entry_files()` — line 9812 — Collect canonical entry files directly from FlatPress YY/MM content directories.
- `plugin_mastodon_collect_entry_files_for_sync()` — line 9920 — Collect local entry files for a local-to-remote synchronization pass; scheduled runs add all dirty parents without a hard cap.
- `plugin_mastodon_collect_entry_files_from_month()` — line 9751 — Collect direct entry files from one canonical FlatPress YY/MM content directory.
- `plugin_mastodon_collect_entry_files_legacy()` — line 9784 — Legacy fallback for non-canonical content trees.
- `plugin_mastodon_collect_known_entry_context_targets()` — line 12141 — Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
- `plugin_mastodon_collect_local_entry_media()` — line 7996 — Collect local images, galleries, audio and video referenced by an entry.
- `plugin_mastodon_collect_scheduled_entry_files()` — line 9855 — Collect scheduled-sync entry candidates directly from relevant FlatPress YY/MM content directories.
- `plugin_mastodon_comment_hash()` — line 7442 — Build a change-detection hash for a FlatPress comment.
- `plugin_mastodon_comment_parent_fields()` — line 5880 — Return the comment fields that may contain a parent reference.
- `plugin_mastodon_compact_instance_document()` — line 216 — Reduce a live Mastodon instance document to the stable subset that is useful for the plugin.
- `plugin_mastodon_companion_plugins_status()` — line 6429 — Return mode-aware companion FlatPress plugin diagnostics; one-way mode hides import-only helpers and keeps export helpers.
- `plugin_mastodon_compare_local_entries_for_export()` — line 9968 — Compare local FlatPress entries for Mastodon export order.
- `plugin_mastodon_configured_status_language()` — line 2440 — Read the configured FlatPress locale and return the Mastodon language code.
- `plugin_mastodon_content_sync_comment_entry_ids()` — line 10029 — Return entry ids whose comment shards may be needed by the current content-sync workset.
- `plugin_mastodon_create_status()` — line 11327 — Create a Mastodon status.
- `plugin_mastodon_date_matches_content_window()` — line 2811 — Determine whether a date is inside the manual lower bound and, for scheduled runs, the automatic window.
- `plugin_mastodon_date_matches_sync_start()` — line 2776 — Determine whether a content date passes the configured sync start date.
- `plugin_mastodon_datetime_date_key()` — line 2752 — Normalize a date/datetime string to a sync-start date key.
- `plugin_mastodon_default_content_stats()` — line 560 — Return the default counters for the last content synchronization.
- `plugin_mastodon_default_deletion_stats()` — line 577 — Return the default counters for the last deletion synchronization.
- `plugin_mastodon_default_options()` — line 163 — Return the default plugin option values.
- `plugin_mastodon_default_state()` — line 590 — Return the default runtime state structure.
- `plugin_mastodon_delete_media_attachment()` — line 9326 — Delete an uploaded Mastodon media attachment before it is attached to a final status.
- `plugin_mastodon_delete_status()` — line 11266 — Delete a Mastodon status.
- `plugin_mastodon_delete_status_should_retry_without_delete_media()` — line 11294 — Check whether a failed status deletion may be caused by Mastodon versions before 4.4.0 not understanding the optional delete_media query parameter.
- `plugin_mastodon_deletion_sync_due()` — line 5571 — Determine whether a pending deletion synchronization may start now using UTC cooldown timestamps.
- `plugin_mastodon_detect_local_comment_parent_id()` — line 5906 — Detect the local parent comment identifier from comment data.
- `plugin_mastodon_dirty_entry_id_lookup()` — line 9999 — Return entry identifiers that are explicitly queued by dirty entry/comment hooks.
- `plugin_mastodon_dirty_tracking_options()` — line 5060 — Check whether dirty tracking should persist state for a local FlatPress write hook.
- `plugin_mastodon_dom_children_to_flatpress()` — line 6992 — Convert DOM child nodes into FlatPress BBCode text.
- `plugin_mastodon_dom_node_to_flatpress()` — line 7010 — Convert a single DOM node into FlatPress BBCode text.
- `plugin_mastodon_domains_match()` — line 6924 — Determine whether two host names belong to the same domain family.
- `plugin_mastodon_emoticon_entity_to_unicode()` — line 6693 — Convert an emoticon HTML entity into a Unicode character.
- `plugin_mastodon_emoticon_map()` — line 6706 — Return the FlatPress emoticon-to-Unicode lookup map.
- `plugin_mastodon_emoticons_plugin_active()` — line 6410 — Determine whether the Emoticons plugin is active for the current FlatPress request.
- `plugin_mastodon_enabled_plugin_state()` — line 6277 — Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
- `plugin_mastodon_ensure_comment_shard_dir()` — line 3013 — Ensure that the comment-shard directory exists.
- `plugin_mastodon_ensure_profile_dir()` — line 10640 — Ensure the public profile-widget cache directory and directory-listing guard exist.
- `plugin_mastodon_ensure_state_dir()` — line 3005 — Ensure that the plugin runtime directory exists.
- `plugin_mastodon_entry_hash()` — line 7430 — Build a change-detection hash for a FlatPress entry.
- `plugin_mastodon_entry_media_attachment_signature_from_items()` — line 8231 — Build a signature for the actual media payload of local entry attachments.
- `plugin_mastodon_entry_media_description_signature_from_items()` — line 8254 — Build a signature for local entry media descriptions.
- `plugin_mastodon_entry_media_signature()` — line 8273 — Build a signature for media references contained in entry content.
- `plugin_mastodon_entry_month_end_date_key()` — line 9709 — Return the last date key that can occur inside a FlatPress YY/MM content directory.
- `plugin_mastodon_entry_month_may_match_scheduled_window()` — line 9731 — Determine whether a FlatPress YY/MM directory can contain scheduled-sync entry candidates.
- `plugin_mastodon_exchange_code_for_token()` — line 10591 — Exchange an OAuth authorization code for an access token.
- `plugin_mastodon_extend_time_limit()` — line 8759 — Best-effort refresh/increase of the PHP execution time budget for long-running Mastodon work.
- `plugin_mastodon_extension_from_mime_type()` — line 7740 — Return an appropriate file extension for a MIME type.
- `plugin_mastodon_extract_flatpress_tags()` — line 6527 — Extract FlatPress Tag plugin labels from an entry body.
- `plugin_mastodon_extract_url_token()` — line 6105 — Extract the URL token from a BBCode or attribute fragment.
- `plugin_mastodon_fediverse_creator_value()` — line 2349 — Build the fediverse creator meta value.
- `plugin_mastodon_fetch_account_statuses()` — line 11163 — Fetch statuses for the authenticated Mastodon account.
- `plugin_mastodon_fetch_media_attachment()` — line 9316 — Fetch the current processing status of a Mastodon media attachment.
- `plugin_mastodon_fetch_reply_notifications()` — line 11232 — Fetch recent mention notifications for prioritized old-thread reply hints.
- `plugin_mastodon_fetch_status()` — line 11255 — Fetch a single Mastodon status.
- `plugin_mastodon_fetch_status_context()` — line 11221 — Fetch the conversation context for a Mastodon status.
- `plugin_mastodon_file_permissions_mode()` — line 1078 — Return the FlatPress file permission mode for runtime files.
- `plugin_mastodon_file_prestat()` — line 1925 — Read a cheap file metadata snapshot for cache validation.
- `plugin_mastodon_file_prestat_signature()` — line 1945 — Convert a file metadata snapshot into a stable cache signature.
- `plugin_mastodon_flatpress_to_mastodon()` — line 7291 — Convert FlatPress content into Mastodon-ready plain text.
- `plugin_mastodon_format_admin_datetime()` — line 1007 — Format a stored UTC timestamp for the admin panel using FlatPress local time and configured formats without depending on PHP's default timezone.
- `plugin_mastodon_fp_config()` — line 864 — Return the FlatPress configuration, preferring the early-loaded core cache.
- `plugin_mastodon_fp_config_value()` — line 900 — Read a nested FlatPress configuration value.
- `plugin_mastodon_fp_timeoffset_label()` — line 928 — Format the configured FlatPress time offset as a UTC label for admin users.
- `plugin_mastodon_fp_timeoffset_seconds()` — line 916 — Return the configured FlatPress time offset in seconds.
- `plugin_mastodon_get_options()` — line 1956 — Load the saved plugin options and merge them with defaults.
- `plugin_mastodon_guess_subject()` — line 6018 — Guess a subject line from imported plain text.
- `plugin_mastodon_head()` — line 2367 — Print Mastodon profile metadata into the HTML head.
- `plugin_mastodon_html_entity_decode()` — line 6060 — Decode HTML entities using the plugin defaults.
- `plugin_mastodon_http_build_query()` — line 10279 — Build an application/x-www-form-urlencoded query string for Mastodon requests.
- `plugin_mastodon_http_request()` — line 10335 — Perform an HTTP request using cURL or the stream fallback.
- `plugin_mastodon_http_request_multipart()` — line 9183 — Perform a multipart HTTP request.
- `plugin_mastodon_import_remote_comment()` — line 11751 — Import a remote Mastodon reply into FlatPress as a comment.
- `plugin_mastodon_import_remote_context_descendants()` — line 12018 — Import remote Mastodon replies from a fetched thread context.
- `plugin_mastodon_import_remote_entry()` — line 11486 — Import a remote Mastodon status into FlatPress as an entry.
- `plugin_mastodon_import_remote_notification_context()` — line 11889 — Use a notification status context to import an unmapped parent chain below a known entry.
- `plugin_mastodon_import_remote_notification_reply()` — line 11848 — Directly import a notification reply when its mapped parent entry or comment is already known.
- `plugin_mastodon_imported_reply_quote_payload()` — line 11666 — Resolve the author and body that should be quoted for an imported Mastodon reply.
- `plugin_mastodon_instance_authority()` — line 2303 — Return the Mastodon instance authority used in fediverse creator metadata.
- `plugin_mastodon_instance_character_limit()` — line 11148 — Return the status character limit of the configured instance.
- `plugin_mastodon_instance_configuration()` — line 9022 — Load and cache the Mastodon instance configuration document.
- `plugin_mastodon_instance_document()` — line 8790 — Load and cache the full Mastodon instance document returned by /api/v2/instance.
- `plugin_mastodon_instance_document_api_version()` — line 8865 — Return the machine-readable Mastodon API compatibility version from an instance document.
- `plugin_mastodon_instance_media_description_limit()` — line 9045 — Return the media description length limit of the configured instance.
- `plugin_mastodon_instance_media_limit()` — line 9032 — Return the media attachment limit of the configured instance.
- `plugin_mastodon_instance_media_size_limit()` — line 7807 — Return the configured byte-size limit for a media family, or 0 if unknown.
- `plugin_mastodon_instance_registration_summary()` — line 13087 — Summarize the registration state advertised by the Mastodon instance.
- `plugin_mastodon_instance_supported_media_mime_types()` — line 7787 — Return instance-advertised supported media MIME types.
- `plugin_mastodon_instance_supports_account_statuses_exclude_direct()` — line 8947 — Determine whether cached instance information confirms support for the Mastodon 4.6 `exclude_direct` account-status filter.
- `plugin_mastodon_instance_supports_mastodon_api_v4()` — line 8917 — Centralizes cached `api_versions[mastodon] >= 4` / Mastodon `>= 4.4.0` capability decisions for status deletion and unattached-media cleanup without extra network requests.
- `plugin_mastodon_instance_supports_media_delete()` — line 9013 — Determine whether cached instance information confirms support for deleting unattached uploaded media through DELETE /api/v1/media/:id.
- `plugin_mastodon_instance_supports_status_delete_media()` — line 8997 — Determine whether cached instance information confirms support for the delete_media query parameter on DELETE /api/v1/statuses/:id.
- `plugin_mastodon_instance_supports_status_media_attributes()` — line 8891 — Determine whether the configured Mastodon instance should support media description updates on already-posted statuses.
- `plugin_mastodon_instance_url_reserved_length()` — line 9058 — Return the per-URL character budget used by the configured instance.
- `plugin_mastodon_io_append_file()` — line 1121 — Append to a file without re-reading and rewriting the complete payload.
- `plugin_mastodon_io_read_file()` — line 1044 — Read a file through the FlatPress I/O layer when available.
- `plugin_mastodon_io_read_file_uncached()` — line 1060 — Read a file without FlatPress request-local caches.
- `plugin_mastodon_io_rotate_file_if_needed()` — line 1185 — Rotate an append-only log file when the next append would exceed the size cap.
- `plugin_mastodon_io_write_file()` — line 1100 — Write a file through the FlatPress I/O layer when available.
- `plugin_mastodon_is_public_host()` — line 6810 — Determine whether a host name resolves to a public endpoint.
- `plugin_mastodon_is_two_digit_path_segment()` — line 9689 — Determine whether a FlatPress content-tree path segment is a two-digit date segment.
- `plugin_mastodon_json_decode_string_at()` — line 3146 — Decode a JSON string literal starting at the current offset.
- `plugin_mastodon_json_skip_ws()` — line 3183 — Skip JSON whitespace in a string scanner.
- `plugin_mastodon_json_top_level_property_bounds()` — line 3200 — Find a top-level JSON property value in an object payload without decoding the whole object.
- `plugin_mastodon_lang_string()` — line 6164 — Return a localized plugin string or a provided fallback.
- `plugin_mastodon_limit_status_text()` — line 9104 — Limit Mastodon plain text while respecting the instance URL budget.
- `plugin_mastodon_limit_text()` — line 7408 — Limit text to a maximum number of characters.
- `plugin_mastodon_list_local_comment_ids()` — line 5981 — List local FlatPress comment identifiers by scanning the entry comment directory directly.
- `plugin_mastodon_list_local_entries()` — line 10133 — List local FlatPress entries ordered for export.
- `plugin_mastodon_list_local_entries_for_sync()` — line 10094 — List local FlatPress entries for a local-to-remote synchronization pass.
- `plugin_mastodon_local_comment_parent_export_pending()` — line 5953 — Determine whether a local parent comment should be exported before its child reply.
- `plugin_mastodon_local_item_date_key()` — line 2703 — Determine the date key of a local FlatPress entry or comment.
- `plugin_mastodon_local_item_matches_content_window()` — line 2845 — Determine whether a local FlatPress item is inside the active content synchronization window.
- `plugin_mastodon_local_item_matches_sync_start()` — line 2833 — Determine whether a local FlatPress item should be synchronized.
- `plugin_mastodon_local_item_timestamp()` — line 9939 — Resolve the best timestamp for a local FlatPress item.
- `plugin_mastodon_local_write_guard_active()` — line 5052 — Return whether FlatPress write hooks are currently triggered by Mastodon remote mirroring.
- `plugin_mastodon_local_write_guard_enter()` — line 5029 — Increase the local-write guard depth while the plugin mirrors remote Mastodon data into FlatPress.
- `plugin_mastodon_local_write_guard_leave()` — line 5040 — Decrease the local-write guard depth after a plugin-owned FlatPress write.
- `plugin_mastodon_log()` — line 4168 — Append a line to the plugin sync log.
- `plugin_mastodon_log_flush_skip_summaries()` — line 4213 — Flush aggregated skip log messages.
- `plugin_mastodon_log_max_bytes()` — line 1161 — Return the maximum sync.log size before rotation.
- `plugin_mastodon_log_rotate_files()` — line 1172 — Return the number of retained sync.log rotation files.
- `plugin_mastodon_log_skip()` — line 4183 — Aggregate high-volume skip messages until the current sync phase ends.
- `plugin_mastodon_mapping_effective_date_key()` — line 2925 — Return the most stable date key available for a synchronized mapping.
- `plugin_mastodon_mapping_keys_after_cursor()` — line 2987 — Return mapping keys ordered after a saved deletion cursor.
- `plugin_mastodon_mapping_matches_deletion_lookup_window()` — line 2966 — Determine whether an existing local mapping should be remotely checked for deletion in this run.
- `plugin_mastodon_mapping_matches_sync_start()` — line 2877 — Determine whether a synchronized mapping should participate in the deletion follow-up for the current sync start date.
- `plugin_mastodon_mastodon_api()` — line 10454 — Call the Mastodon API and return the raw HTTP response.
- `plugin_mastodon_mastodon_hashtag_footer()` — line 6569 — Convert FlatPress tag labels into a Mastodon hashtag footer line.
- `plugin_mastodon_mastodon_html_to_flatpress()` — line 7189 — Convert Mastodon HTML content into FlatPress BBCode.
- `plugin_mastodon_mastodon_json()` — line 10504 — Call the Mastodon API and decode a JSON response.
- `plugin_mastodon_maybe_sync()` — line 13015 — Run the scheduled synchronization when the current request is due.
- `plugin_mastodon_media_copy_tree()` — line 7567 — Copy a directory tree used for media synchronization.
- `plugin_mastodon_media_delete_tree()` — line 7540 — Delete a directory tree used for imported media.
- `plugin_mastodon_media_description_from_bbcode_content()` — line 7892 — Normalize optional AudioVideo BBCode content into a Mastodon media description.
- `plugin_mastodon_media_download()` — line 8516 — Download a remote media asset.
- `plugin_mastodon_media_extract_default_path()` — line 7918 — Extract the default path parameter from a FlatPress media tag attribute string.
- `plugin_mastodon_media_guess_mime_type()` — line 7639 — Guess the MIME type of a local media file.
- `plugin_mastodon_media_parse_tag_attributes()` — line 7861 — Parse key/value attributes from a FlatPress media tag.
- `plugin_mastodon_media_prepare_directory()` — line 7524 — Ensure that a media directory exists.
- `plugin_mastodon_media_processing_attempts()` — line 9391 — Determine how patiently the plugin should wait for Mastodon media processing.
- `plugin_mastodon_media_relative_to_absolute()` — line 7511 — Resolve a FlatPress media path to an absolute file path.
- `plugin_mastodon_media_transfer_timeout()` — line 9415 — Determine a practical transfer timeout for one media upload.
- `plugin_mastodon_media_type_from_mime()` — line 7706 — Return a stable FlatPress/Mastodon media family for a MIME type.
- `plugin_mastodon_minutes_to_sync_time()` — line 969 — Convert minutes after midnight into a normalized HH:MM time value.
- `plugin_mastodon_normalize_boolean_option()` — line 2528 — Normalize a boolean-like option value to the stored string representation.
- `plugin_mastodon_normalize_comment_parent_id()` — line 5889 — Normalize a stored local comment parent identifier.
- `plugin_mastodon_normalize_delete_sync_enabled()` — line 2671 — Normalize the toggle that enables the follow-up deletion synchronization.
- `plugin_mastodon_normalize_deletions_pending_scope()` — line 4524 — Normalize the pending deletion scope marker.
- `plugin_mastodon_normalize_disable_remote_import()` — line 2562 — Normalize the toggle that blocks Mastodon-to-FlatPress imports.
- `plugin_mastodon_normalize_head_username()` — line 2264 — Normalize the configured Mastodon username for HTML head metadata.
- `plugin_mastodon_normalize_import_synced_comments_as_entries()` — line 2580 — Normalize the toggle that allows importing already synchronized local comments as entries.
- `plugin_mastodon_normalize_instance_url()` — line 2231 — Normalize the configured Mastodon instance URL.
- `plugin_mastodon_normalize_media_relative_path()` — line 7488 — Normalize a FlatPress media path relative to fp-content.
- `plugin_mastodon_normalize_old_thread_context_limit()` — line 2634 — Clamp the admin-configurable old-thread refresh limit to the supported 1-10 range.
- `plugin_mastodon_normalize_old_thread_reply_check()` — line 2616 — Normalize the toggle that enables rotating context checks for known synchronized Mastodon threads.
- `plugin_mastodon_normalize_quote_imported_reply_parent()` — line 2598 — Normalize the toggle that controls whether imported Mastodon replies should quote the replied-to comment in FlatPress.
- `plugin_mastodon_normalize_scheduled_window_days()` — line 2503 — Normalize the automatic scheduled synchronization window.
- `plugin_mastodon_normalize_status_language()` — line 2418 — Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
- `plugin_mastodon_normalize_sync_start_date()` — line 2477 — Normalize the configured sync start date.
- `plugin_mastodon_normalize_sync_time()` — line 2460 — Normalize the configured daily sync time.
- `plugin_mastodon_normalize_tag_list()` — line 6496 — Normalize a list of tag labels.
- `plugin_mastodon_normalize_update_local_from_remote()` — line 2544 — Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
- `plugin_mastodon_normalized_instance_version()` — line 8842 — Normalize a human-readable Mastodon version string for version_compare().
- `plugin_mastodon_oauth_compatible_scopes()` — line 643 — Return broad new-registration OAuth scopes for instances without scope discovery.
- `plugin_mastodon_oauth_legacy_scopes()` — line 627 — Return the legacy OAuth scopes used before scope discovery was added.
- `plugin_mastodon_oauth_notifications_scope_available()` — line 795 — Determine whether the configured scope set can call the notifications endpoint.
- `plugin_mastodon_oauth_preferred_scopes()` — line 729 — Return the preferred OAuth scopes for the configured Mastodon instance.
- `plugin_mastodon_oauth_profile_scopes()` — line 635 — Return the stricter OAuth scopes preferred on current Mastodon instances.
- `plugin_mastodon_oauth_scope_list()` — line 763 — Split and normalize an OAuth scope string.
- `plugin_mastodon_oauth_scope_supported()` — line 708 — Determine whether the configured Mastodon instance advertises support for a scope.
- `plugin_mastodon_oauth_scopes()` — line 746 — Return the OAuth scopes that the currently registered app may safely request.
- `plugin_mastodon_oauth_scopes_include()` — line 785 — Check whether an OAuth scope string contains one required scope token.
- `plugin_mastodon_oauth_server_metadata()` — line 652 — Fetch OAuth authorization server metadata for the configured Mastodon instance.
- `plugin_mastodon_oauth_supported_scopes()` — line 671 — Return the OAuth scopes supported by the configured Mastodon instance, if discoverable.
- `plugin_mastodon_old_thread_context_limit_choices()` — line 2649 — Build the 1-10 admin select choices for old-thread context refreshes.
- `plugin_mastodon_old_thread_context_rotation_limit()` — line 12124 — Return the maximum number of known synchronized threads checked for replies per content sync run.
- `plugin_mastodon_on_comment_deleted()` — line 5216 — Mark a local FlatPress comment deletion for the Mastodon deletion sync.
- `plugin_mastodon_on_comment_saved()` — line 5155 — Mark a local FlatPress comment write as dirty for a later Mastodon sync.
- `plugin_mastodon_on_entry_deleted()` — line 5128 — Mark a local FlatPress entry deletion for the Mastodon deletion sync.
- `plugin_mastodon_on_entry_saved()` — line 5079 — Mark a local FlatPress entry write as dirty for a later Mastodon sync.
- `plugin_mastodon_parse_http_response_headers()` — line 10171 — Parse raw HTTP response headers.
- `plugin_mastodon_parse_iso_datetime()` — line 5794 — Parse an ISO date/time string into FlatPress date format.
- `plugin_mastodon_parse_iso_timestamp()` — line 5813 — Parse an ISO date/time value into a Unix timestamp.
- `plugin_mastodon_photoswipe_plugin_active()` — line 6376 — Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
- `plugin_mastodon_plain_text_from_bbcode()` — line 6852 — Convert FlatPress BBCode into plain text for Mastodon export.
- `plugin_mastodon_prepare_emoticons_for_mastodon()` — line 6775 — Convert FlatPress emoticon shortcodes to Mastodon-safe Unicode glyphs when the plugin is active.
- `plugin_mastodon_prepare_entry_media_items()` — line 8203 — Normalize local entry media items for signature and export planning.
- `plugin_mastodon_prepare_entry_media_sync_plan()` — line 9601 — Decide whether a local entry update can reuse already-uploaded Mastodon media or needs a fresh upload.
- `plugin_mastodon_process_pending_comment_remote_rechecks()` — line 5678 — Process queued descendant reply rechecks using a small FIFO queue.
- `plugin_mastodon_process_remote_reply_notifications()` — line 11945 — Process mention notifications as budgeted reply hints before normal old-thread rotation.
- `plugin_mastodon_profile_avatar_type()` — line 10837 — Detect and allow only supported avatar image MIME types.
- `plugin_mastodon_profile_avatar_url_from_account()` — line 10821 — Choose the preferred Mastodon avatar URL, preferring `avatar_static`.
- `plugin_mastodon_profile_cache_read()` — line 10749 — Read and validate the local public profile-widget cache.
- `plugin_mastodon_profile_cache_write()` — line 10790 — Write the public profile-widget cache without storing secrets.
- `plugin_mastodon_profile_download_avatar()` — line 10888 — Download, validate and atomically store the avatar in the local profile cache directory.
- `plugin_mastodon_profile_public_url_allowed()` — line 10697 — Validate public HTTP(S) profile and avatar URLs before storing them.
- `plugin_mastodon_profile_relative_to_absolute()` — line 10715 — Resolve a profile-cache path below `fp-content/plugin_mastodon/profile/` to a local filesystem path.
- `plugin_mastodon_profile_relative_to_url()` — line 10732 — Resolve a profile-cache path below `fp-content/plugin_mastodon/profile/` to a public FlatPress URL.
- `plugin_mastodon_profile_text()` — line 10682 — Normalize public account text before writing it to the profile-widget cache.
- `plugin_mastodon_profile_url()` — line 2333 — Build the public Mastodon profile URL used for the rel-me link.
- `plugin_mastodon_profile_url_from_account()` — line 10808 — Choose the Mastodon profile URL from account data or the configured instance/username.
- `plugin_mastodon_protect_locally_deleted_exported_comments()` — line 5392 — Protect locally deleted exported FlatPress comments from stale Mastodon re-imports before deletion sync runs.
- `plugin_mastodon_public_comment_url()` — line 7174 — Return the public URL for a specific FlatPress comment.
- `plugin_mastodon_public_comments_url()` — line 7146 — Return the public comments URL for a FlatPress entry.
- `plugin_mastodon_public_entry_url()` — line 7119 — Return the public URL for a FlatPress entry.
- `plugin_mastodon_public_url_for_mastodon()` — line 6833 — Return a Mastodon-safe public URL or an empty string.
- `plugin_mastodon_queue_comment_descendant_remote_rechecks()` — line 5639 — Queue the direct mapped local children of one deleted remote comment for additional verification passes.
- `plugin_mastodon_rate_limit_acquire()` — line 1817 — Reserve budget for one Mastodon API request.
- `plugin_mastodon_rate_limit_block()` — line 1773 — Mark the current rate-limit guard as blocked.
- `plugin_mastodon_rate_limit_blocked_reason()` — line 1886 — Return the current rate-limit block reason, if any.
- `plugin_mastodon_rate_limit_blocked_response()` — line 1898 — Build a synthetic API response for locally blocked Mastodon requests.
- `plugin_mastodon_rate_limit_default_budgets()` — line 1464 — Return the default Mastodon API budgets for one synchronization run.
- `plugin_mastodon_rate_limit_guard_active()` — line 1710 — Return whether a per-run Mastodon API rate-limit guard is active.
- `plugin_mastodon_rate_limit_guard_start()` — line 1673 — Start a per-run Mastodon API rate-limit guard.
- `plugin_mastodon_rate_limit_guard_stop()` — line 1699 — Stop the current per-run Mastodon API rate-limit guard while keeping its summary inspectable.
- `plugin_mastodon_rate_limit_guard_summary()` — line 1718 — Return the current rate-limit guard summary.
- `plugin_mastodon_rate_limit_headers()` — line 1727 — Normalize a response header map for rate-limit checks.
- `plugin_mastodon_rate_limit_observe_response()` — line 1862 — Update the current per-run guard from Mastodon rate-limit response headers.
- `plugin_mastodon_rate_limit_request_kind()` — line 1748 — Determine whether a Mastodon API request consumes one of the stricter per-run budgets.
- `plugin_mastodon_rate_limit_state_error()` — line 1915 — Return the rate-limit reason that should be written to sync state, if any.
- `plugin_mastodon_rate_limit_window_acquire()` — line 1612 — Reserve one item from a persistent cross-run Mastodon rate-limit window.
- `plugin_mastodon_rate_limit_window_budgets()` — line 1488 — Return the persistent Mastodon API window budgets across synchronization runs.
- `plugin_mastodon_rate_limit_window_clear()` — line 1661 — Clear persistent rate-limit windows, mainly for tests and recovery tooling.
- `plugin_mastodon_rate_limit_window_config()` — line 1515 — Return persistent window configuration for one request kind.
- `plugin_mastodon_rate_limit_window_entry()` — line 1535 — Normalize one persistent rate-limit window entry.
- `plugin_mastodon_rate_limit_window_read()` — line 1555 — Read the persistent rate-limit windows.
- `plugin_mastodon_rate_limit_window_write()` — line 1587 — Persist the current rate-limit windows.
- `plugin_mastodon_reattach_local_comment_to_entry_status()` — line 5442 — Reattach one imported local comment to the synchronized entry status after its remote parent reply disappeared.
- `plugin_mastodon_refresh_instance_information()` — line 540 — Force a live refresh of the Mastodon instance information and persist the compact snapshot.
- `plugin_mastodon_refresh_profile_cache()` — line 11037 — Refresh the public profile-widget cache through `verify_credentials`.
- `plugin_mastodon_refresh_profile_cache_for_sync()` — line 11056 — Refresh the public profile-widget cache during content synchronization without deciding the sync outcome by itself.
- `plugin_mastodon_refresh_profile_cache_from_account()` — line 10949 — Build the public profile-widget cache from a verified Mastodon account payload.
- `plugin_mastodon_register_app()` — line 10548 — Register the FlatPress application on the configured Mastodon instance.
- `plugin_mastodon_remote_download_basename()` — line 8530 — Build a safe basename for a downloaded remote attachment.
- `plugin_mastodon_remote_media_attachment_type()` — line 8290 — Return the normalized Mastodon attachment type.
- `plugin_mastodon_remote_media_description()` — line 8387 — Resolve the best description for a remote attachment.
- `plugin_mastodon_remote_media_descriptors_from_media_ids()` — line 8449 — Build remote-media descriptors from freshly uploaded media IDs and the current local descriptions.
- `plugin_mastodon_remote_media_descriptors_from_status()` — line 8418 — Build sanitized remote-media descriptors from a Mastodon status payload.
- `plugin_mastodon_remote_media_focus()` — line 8401 — Resolve the stored focus string for a remote attachment, if any.
- `plugin_mastodon_remote_media_source_url()` — line 8347 — Resolve the best downloadable source URL for a remote attachment.
- `plugin_mastodon_remote_media_source_urls()` — line 8365 — Return direct-download candidate URLs for a remote media attachment.
- `plugin_mastodon_remote_status_author_label()` — line 11600 — Build a readable account label for a Mastodon status author.
- `plugin_mastodon_remote_status_date_key()` — line 2726 — Determine the date key of a remote Mastodon status.
- `plugin_mastodon_remote_status_is_importable()` — line 5868 — Determine whether a remote Mastodon status may be imported.
- `plugin_mastodon_remote_status_matches_content_window()` — line 2866 — Determine whether a remote Mastodon status is inside the active content synchronization window.
- `plugin_mastodon_remote_status_matches_sync_start()` — line 2855 — Determine whether a remote Mastodon status should be synchronized.
- `plugin_mastodon_remote_status_media_attachments()` — line 8317 — Extract supported media attachments from a remote Mastodon status.
- `plugin_mastodon_remote_status_tags()` — line 6590 — Collect remote Mastodon tags from a status entity.
- `plugin_mastodon_remote_status_timestamp()` — line 5836 — Resolve the best timestamp for a remote Mastodon status.
- `plugin_mastodon_remote_status_visibility()` — line 5855 — Return the normalized visibility of a remote Mastodon status.
- `plugin_mastodon_replace_emoticon_shortcodes_with_unicode()` — line 6760 — Replace FlatPress emoticon shortcodes with Unicode glyphs.
- `plugin_mastodon_replace_unicode_emoticons_with_shortcodes()` — line 6788 — Replace Unicode emoticons with FlatPress shortcodes.
- `plugin_mastodon_resolve_comment_reply_target()` — line 5928 — Resolve the remote reply target for a local comment export.
- `plugin_mastodon_response_error_message()` — line 10519 — Extract the most useful error message from an API response.
- `plugin_mastodon_run_deletion_sync()` — line 12531 — Run the deletion synchronization in a follow-up request after content sync completed.
- `plugin_mastodon_run_sync()` — line 12927 — Run a full synchronization cycle.
- `plugin_mastodon_runtime_cache_clear()` — line 849 — Clear one request-local plugin cache bucket or the complete cache.
- `plugin_mastodon_runtime_cache_get()` — line 807 — Return a value from the request-local plugin cache.
- `plugin_mastodon_runtime_cache_set()` — line 831 — Store a value in the request-local plugin cache.
- `plugin_mastodon_safe_filename()` — line 7475 — Sanitize a file name for local storage.
- `plugin_mastodon_safe_path_component()` — line 7460 — Sanitize a string so it can be used as a path component.
- `plugin_mastodon_save_options()` — line 2064 — Persist plugin options.
- `plugin_mastodon_saved_instance_document()` — line 433 — Read a previously stored Mastodon instance snapshot from the plugin options.
- `plugin_mastodon_scheduled_window_choices()` — line 2515 — Return the admin radio choices for the scheduled synchronization window.
- `plugin_mastodon_scheduled_window_start_date()` — line 2794 — Return the FlatPress-local date key that starts the automatic scheduled sync window.
- `plugin_mastodon_scheduler_source_signature()` — line 4309 — Return the current stat-based signature of the full state file.
- `plugin_mastodon_scheduler_state_decode_fresh()` — line 4402 — Decode a scheduler-state JSON payload only when it matches the current full-state signature.
- `plugin_mastodon_scheduler_state_default()` — line 4288 — Return an empty scheduler state derived from the full default state.
- `plugin_mastodon_scheduler_state_from_state()` — line 4356 — Build the lightweight scheduler summary from a full runtime state.
- `plugin_mastodon_scheduler_state_normalize()` — line 4318 — Normalize a scheduler summary without touching full mapping arrays.
- `plugin_mastodon_scheduler_state_read()` — line 4421 — Load the lightweight scheduler summary and rebuild it conservatively when stale.
- `plugin_mastodon_scheduler_state_write()` — line 4376 — Persist the lightweight scheduler state. Failure only disables the request-time optimization.
- `plugin_mastodon_secret_decode()` — line 2200 — Decode a previously stored secret value.
- `plugin_mastodon_secret_encode()` — line 2177 — Encode a secret value before storing it in the configuration.
- `plugin_mastodon_secret_key()` — line 2160 — Build the encryption key used for stored secrets.
- `plugin_mastodon_select_status_media_items()` — line 8142 — Select a Mastodon-compatible media set for one status.
- `plugin_mastodon_should_check_old_thread_replies()` — line 2625 — Check whether known synchronized Mastodon threads should be checked for replies in rotating batches.
- `plugin_mastodon_should_import_remote_to_local()` — line 2571 — Check whether Mastodon responses may create, update or delete local FlatPress content.
- `plugin_mastodon_should_import_synced_comments_as_entries()` — line 2589 — Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
- `plugin_mastodon_should_parse_local_entry_for_sync()` — line 10069 — Determine whether a local entry file should be parsed during a scheduled local-to-remote pass.
- `plugin_mastodon_should_poll_reply_notifications()` — line 2662 — Check whether reply-notification polling is enabled and authorized.
- `plugin_mastodon_should_quote_imported_reply_parent()` — line 2607 — Check whether imported Mastodon replies should include a quote of the replied-to comment.
- `plugin_mastodon_should_run_deletion_sync()` — line 2680 — Check whether the follow-up deletion synchronization is enabled.
- `plugin_mastodon_should_update_local_from_remote()` — line 2553 — Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
- `plugin_mastodon_state_cleanup_stale_comment_shards()` — line 4046 — Remove shard files that are no longer referenced by the main state.
- `plugin_mastodon_state_comment_entry_loaded()` — line 3767 — Check whether one entry's comment shard is loaded into the state array.
- `plugin_mastodon_state_comment_key()` — line 4589 — Build the compound state key used for comment mappings.
- `plugin_mastodon_state_comment_mappings()` — line 4598 — Return the loaded comment mapping array from a state object.
- `plugin_mastodon_state_comment_remote_mappings()` — line 4634 — Return the global remote-comment reverse index.
- `plugin_mastodon_state_comment_shard_entries()` — line 4686 — Return the main-state metadata for per-entry comment shards.
- `plugin_mastodon_state_comment_shard_entry_ids()` — line 3822 — Return entry ids with known comment shards.
- `plugin_mastodon_state_comment_shard_file()` — line 3042 — Return the shard path for an entry's comment mappings.
- `plugin_mastodon_state_comment_shard_files()` — line 3839 — Return all comment shard files currently present on disk keyed by entry id.
- `plugin_mastodon_state_comment_shard_relative_path()` — line 3057 — Return the relative shard path used in state metadata.
- `plugin_mastodon_state_comment_shards_partial()` — line 3739 — Return whether a state array only contains a subset of comment shards.
- `plugin_mastodon_state_create_migration_backup()` — line 3301 — Create a timestamped backup of the legacy main state before an inline-to-shard migration mutates it.
- `plugin_mastodon_state_diagnose_comment_shards()` — line 3891 — Scan shard files and compare them with the main-state metadata and reverse comment index.
- `plugin_mastodon_state_entry_id_from_comment_key()` — line 3031 — Return the entry id portion from a compound comment-state key.
- `plugin_mastodon_state_entry_media_attachment_signature()` — line 5324 — Return the stored attachment-signature for one entry mapping.
- `plugin_mastodon_state_entry_media_description_signature()` — line 5334 — Return the stored description-signature for one entry mapping.
- `plugin_mastodon_state_entry_remote_media()` — line 5294 — Return sanitized remote-media descriptors stored inside one entry mapping.
- `plugin_mastodon_state_fallback_key()` — line 1283 — Return the legacy APCu key that used to hold a full last-known-good state fallback.
- `plugin_mastodon_state_fallback_read()` — line 1301 — Full-state APCu fallback is intentionally disabled to avoid multi-MiB APCu entries.
- `plugin_mastodon_state_fallback_store()` — line 1292 — Remove the legacy full-state APCu fallback instead of storing large mapping arrays.
- `plugin_mastodon_state_get_comment_meta()` — line 5346 — Return mapping metadata for a local comment.
- `plugin_mastodon_state_get_entry_meta()` — line 5251 — Return mapping metadata for a local entry.
- `plugin_mastodon_state_get_pending_comment_remote_recheck()` — line 5516 — Return one pending descendant recheck marker.
- `plugin_mastodon_state_group_comments_by_entry()` — line 3559 — Group comment mappings by their parent entry id.
- `plugin_mastodon_state_has_comment_recheck_scope()` — line 5599 — Check whether the current deletion follow-up request should focus on pending descendant reply rechecks only.
- `plugin_mastodon_state_has_comment_tombstone()` — line 5381 — Check whether one remote Mastodon comment status was tombstoned locally.
- `plugin_mastodon_state_has_dirty_comment()` — line 5020 — Check whether a comment is queued for synchronization although it is outside the scheduled window.
- `plugin_mastodon_state_has_dirty_entry()` — line 4970 — Check whether an entry is queued for synchronization although it is outside the scheduled window.
- `plugin_mastodon_state_is_entry_id()` — line 3022 — Return whether a string looks like a FlatPress entry id.
- `plugin_mastodon_state_load_comment_shard_into()` — line 3801 — Load one entry comment shard into a partial state when needed.
- `plugin_mastodon_state_load_comment_shards()` — line 3699 — Load comment shards into a state array.
- `plugin_mastodon_state_load_content_sync_comment_workset()` — line 10053 — Load comment shards needed for the current content synchronization workset.
- `plugin_mastodon_state_loaded_comment_entry_ids()` — line 3748 — Return the entry ids whose comment shards are loaded in a partial state.
- `plugin_mastodon_state_main_payload()` — line 3680 — Build the main state payload without high-volume inline comment mappings.
- `plugin_mastodon_state_mark_comment_entry_loaded()` — line 3781 — Mark one entry's comment shard as loaded in a partial state.
- `plugin_mastodon_state_migrate_inline_comments_to_shards()` — line 3323 — Iterate a legacy inline comments object and persist per-entry shards without building one giant comments array.
- `plugin_mastodon_state_normalize()` — line 4537 — Normalize a runtime state array and fill in missing keys.
- `plugin_mastodon_state_pending_recheck_entry_ids()` — line 3513 — Return the entry ids referenced by pending comment rechecks.
- `plugin_mastodon_state_read()` — line 4243 — Load the persisted runtime state from disk.
- `plugin_mastodon_state_read_comment_shard()` — line 3587 — Load one per-entry comment shard.
- `plugin_mastodon_state_recover_from_comment_shards()` — line 4103 — Return a recovered state if comment shards were written but the main state is missing.
- `plugin_mastodon_state_remove_comment_mapping()` — line 4863 — Remove the mapping between a local comment and a remote status.
- `plugin_mastodon_state_remove_dirty_comment()` — line 5006 — Remove a comment from the dirty queue.
- `plugin_mastodon_state_remove_dirty_entry()` — line 4957 — Remove an entry from the dirty queue.
- `plugin_mastodon_state_remove_entry_mapping()` — line 4841 — Remove the mapping between a local entry and a remote status.
- `plugin_mastodon_state_remove_pending_comment_remote_recheck()` — line 5502 — Remove one pending descendant recheck marker.
- `plugin_mastodon_state_repair_comment_shards()` — line 4006 — Rebuild the main shard metadata and global comments_remote reverse index from shard files.
- `plugin_mastodon_state_set_comment_mapping()` — line 4799 — Store the mapping between a local comment and a remote status.
- `plugin_mastodon_state_set_comment_mappings()` — line 4616 — Replace the loaded comment mapping array in a state object.
- `plugin_mastodon_state_set_comment_remote_mappings()` — line 4660 — Replace the global remote-comment reverse index in a state object.
- `plugin_mastodon_state_set_comment_tombstone()` — line 5361 — Store a tombstone that blocks re-importing a deleted remote comment status.
- `plugin_mastodon_state_set_deletions_pending()` — line 5557 — Update the runtime marker that tells the scheduler whether another deletion follow-up request is required.
- `plugin_mastodon_state_set_dirty_comment()` — line 4983 — Add an older changed comment to the persistent dirty queue.
- `plugin_mastodon_state_set_dirty_entry()` — line 4937 — Add an older changed entry to the persistent dirty queue.
- `plugin_mastodon_state_set_entry_mapping()` — line 4760 — Store the mapping between a local entry and a remote status.
- `plugin_mastodon_state_set_entry_media_meta()` — line 5265 — Persist media metadata for a synchronized local entry.
- `plugin_mastodon_state_set_pending_comment_remote_recheck()` — line 5531 — Mark one local comment for follow-up verification after an ancestor status disappeared remotely.
- `plugin_mastodon_state_try_streaming_legacy_migration()` — line 3462 — Migrate legacy inline comment mappings to per-entry shards without decoding the high-volume comments array.
- `plugin_mastodon_state_unlink_comment_remote_for_reexport()` — line 4916 — Drop a stale remote comment mapping while keeping the local comment queued for a new export.
- `plugin_mastodon_state_unlink_entry_remote_for_reexport()` — line 4896 — Drop a stale remote entry mapping while keeping the local entry queued for a new export.
- `plugin_mastodon_state_unload_comment_shard_from_memory()` — line 3534 — Drop one loaded comment shard from a partial state's in-memory working set.
- `plugin_mastodon_state_write()` — line 4467 — Persist the runtime state to disk.
- `plugin_mastodon_state_write_comment_shards()` — line 3622 — Write all per-entry comment shards.
- `plugin_mastodon_state_write_error_clear()` — line 4712 — Clear the last state write error marker.
- `plugin_mastodon_state_write_error_set()` — line 4704 — Store a state write error for callers that need a last_error message even when persistence fails.
- `plugin_mastodon_state_write_json_file()` — line 3071 — Write a JSON payload atomically when possible.
- `plugin_mastodon_state_write_last_error()` — line 4720 — Return the last state write error marker from the current request.
- `plugin_mastodon_state_write_lock_acquire()` — line 3110 — Acquire the short state-write lock used for multi-file state mutations.
- `plugin_mastodon_state_write_lock_release()` — line 3133 — Release a state-write lock handle.
- `plugin_mastodon_state_write_with_last_error()` — line 4730 — Persist state and copy a failed write reason into the caller-visible last_error field.
- `plugin_mastodon_status_media_attributes()` — line 8483 — Build media_attributes descriptors for PUT /api/v1/statuses/:id.
- `plugin_mastodon_status_missing_response()` — line 11314 — Check whether an API response means that the referenced Mastodon status is missing.
- `plugin_mastodon_status_text_length()` — line 9072 — Calculate the Mastodon-visible length of a plain-text status.
- `plugin_mastodon_store_instance_document()` — line 478 — Persist a compact Mastodon instance snapshot inside the plugin configuration.
- `plugin_mastodon_store_instance_error()` — line 524 — Persist the latest instance-information refresh error for the admin diagnostics view.
- `plugin_mastodon_stream_context_request()` — line 10201 — Perform an HTTP request through a stream context fallback.
- `plugin_mastodon_strip_flatpress_tag_bbcode()` — line 6552 — Remove Tag plugin BBCode blocks from entry content.
- `plugin_mastodon_strip_leading_quote_block()` — line 11633 — Remove one leading BBCode quote block from imported comment text.
- `plugin_mastodon_strip_trailing_mastodon_hashtag_footer()` — line 6617 — Remove a trailing Mastodon hashtag footer from imported plain text.
- `plugin_mastodon_subject_line_is_noise()` — line 6896 — Determine whether an extracted line should be ignored as a subject.
- `plugin_mastodon_sync_due()` — line 12896 — Determine whether the scheduled synchronization is currently due using stored UTC sync time and UTC `last_run` days.
- `plugin_mastodon_sync_guard_active()` — line 1403 — Return true when a recent scheduled sync/deletion pass should cool down.
- `plugin_mastodon_sync_guard_apcu_key()` — line 1321 — Return the plugin-local APCu key suffix used for a sync cooldown guard.
- `plugin_mastodon_sync_guard_apcu_store()` — line 1342 — Store one cooldown guard through the FlatPress APCu wrapper.
- `plugin_mastodon_sync_guard_clear()` — line 1449 — Clear one sync cooldown guard from both FlatPress APCu wrappers and the file-backed guard.
- `plugin_mastodon_sync_guard_entry_active()` — line 1331 — Return true when one guard entry is still active.
- `plugin_mastodon_sync_guard_file_read()` — line 1355 — Read and prune the file-backed cooldown guard.
- `plugin_mastodon_sync_guard_file_write()` — line 1382 — Write the file-backed cooldown guard.
- `plugin_mastodon_sync_guard_kind()` — line 1311 — Normalize the cooldown guard kind.
- `plugin_mastodon_sync_guard_mark()` — line 1428 — Mark a short cooldown guard for a scheduled sync/deletion pass.
- `plugin_mastodon_sync_local_to_remote()` — line 12299 — Synchronize local FlatPress content to Mastodon.
- `plugin_mastodon_sync_remote_to_local()` — line 12217 — Synchronize remote Mastodon content into FlatPress.
- `plugin_mastodon_sync_time_local_to_utc()` — line 996 — Convert a FlatPress-local admin synchronization time back to stored UTC time.
- `plugin_mastodon_sync_time_to_minutes()` — line 958 — Convert a HH:MM time value into minutes after midnight.
- `plugin_mastodon_sync_time_utc_to_local()` — line 985 — Convert the stored UTC synchronization time to the FlatPress-local admin time.
- `plugin_mastodon_tag_plugin_active()` — line 6342 — Determine whether the Tag plugin is active for the current FlatPress request.
- `plugin_mastodon_test_note_local_entry_parse()` — line 9987 — Increment a simulation-only local-entry parse counter when enabled.
- `plugin_mastodon_timestamp_date_key()` — line 2689 — Convert a FlatPress-adjusted timestamp into a stable date key.
- `plugin_mastodon_timestamp_to_flatpress_time()` — line 942 — Convert a real Unix timestamp into FlatPress's offset-adjusted timestamp model.
- `plugin_mastodon_two_digit_year_to_full_year()` — line 9698 — Convert a FlatPress two-digit year segment into the full year used by PHP's legacy date parser.
- `plugin_mastodon_update_status()` — line 11354 — Update an existing Mastodon status.
- `plugin_mastodon_upload_media_items()` — line 9484 — Upload local media items to Mastodon and collect the created media IDs.
- `plugin_mastodon_validate_local_media_item()` — line 7834 — Validate a local media item against known instance upload limits.
- `plugin_mastodon_verify_credentials()` — line 10621 — Verify the currently configured access token.
- `plugin_mastodon_wait_for_media_attachment()` — line 9435 — Wait briefly until an asynchronously uploaded Mastodon media attachment is ready.
- `plugin_mastodon_widget()` — line 11103 — Render the compact Mastodon profile widget from local cache only.
- `plugin_mastodon_widget_avatar_alt()` — line 11082 — Build the localized avatar alt text for the public Mastodon widget.
- `plugin_mastodon_widget_lang_string()` — line 10660 — Return localized public Mastodon widget strings with safe fallbacks.
- `setup()` — line 13428 — Register the active Mastodon admin template and assign plugin data to Smarty.

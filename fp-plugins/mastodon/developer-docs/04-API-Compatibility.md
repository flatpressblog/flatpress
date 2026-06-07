# 04 — API Endpoint and Version Documentation

## Supported Mastodon range

The current plugin implementation is best described as:

| Range      | Status                                                                                                                                                          |
| ---------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `< 4.0.0`  | Not supported by the current documented plugin target because `/api/v2/instance` is the capability source and there is no `/api/v1/instance` API fallback.      |
| `>= 4.0.0` | Technical target. Core endpoints used by the plugin are available; newer capability fields may be missing and internal defaults apply.                          |
| `>= 4.4.0` | Recommended for full documented delete/media-cleanup behavior because `delete_media` on status delete and `DELETE /api/v1/media/:id` are documented from 4.4.0. |
| `>= 4.6.0` | Enables the privacy hardening `exclude_direct=true` for account-status imports when cached instance information or API version 10 confirms support.             |
| Future 4.x | No hard upper bound is encoded. Continue checking official Mastodon API changelogs before changing endpoint behavior.                                           |

## Endpoint matrix

| Purpose                 | Method | Endpoint                                | API history                                       | Auth/scope              | Fallback behavior in plugin                                                                                                                                                                                                                                | Main function(s)                                                     |
| ----------------------- | ------ | --------------------------------------- | ------------------------------------------------- | ----------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------- |
| OAuth discovery         | GET    | /.well-known/oauth-authorization-server | 4.3.0                                             | Public                  | If unavailable for a new app, use `read:accounts` plus `read:notifications` instead of `profile`; existing legacy clients keep their stored scopes.                                                                                                        | plugin_mastodon_oauth_server_metadata / plugin_mastodon_oauth_scopes |
| App registration        | POST   | /api/v1/apps                            | 0.0.0                                             | Public                  | No alternate endpoint. Existing registrations keep stored scopes.                                                                                                                                                                                          | plugin_mastodon_register_app                                         |
| Authorize URL           | GET    | /oauth/authorize                        | 0.1.0 era OAuth                                   | Browser                 | No endpoint fallback; generated URL uses stored app credentials.                                                                                                                                                                                           | plugin_mastodon_build_authorize_url()                                |
| Token exchange          | POST   | /oauth/token                            | 0.1.0 era OAuth                                   | Public/client           | No endpoint fallback.                                                                                                                                                                                                                                      | plugin_mastodon_exchange_code_for_token()                            |
| Verify credentials      | GET    | /api/v1/accounts/verify_credentials     | 0.0.0                                             | User token              | Scope fallback: `profile` on current servers, `read:accounts` on older servers; successful account payloads refresh the local public profile-widget cache when avatar data is available.                                                                   | plugin_mastodon_verify_credentials                                   |
| Instance info           | GET    | /api/v2/instance                        | 4.0.0; `api_versions` 4.3.0                       | Public                  | No `/api/v1/instance` API fallback; compact snapshots include status/media/translation/timeline/account limits, failures are negatively cached per PHP request, and safe defaults apply.                                                                   | plugin_mastodon_instance_document                                    |
| Account statuses        | GET    | /api/v1/accounts/:id/statuses           | 0.0.0; `exclude_direct` 4.6.0 / API v10           | Public or read:statuses | Budgeted paging; known 4.6/API v10 servers receive `exclude_direct=true`; compatible-server 400/405/422 failures retry once without it; older or unknown support keeps the legacy query and local visibility filters still reject direct/private statuses. | plugin_mastodon_fetch_account_statuses                               |
| Notifications           | GET    | /api/v1/notifications                   | 0.0.0; limit 80 from 4.1; `fallback` 4.6.0        | read:notifications      | Optional hint layer; skipped when the current app/token lacks the scope; 4.6 fallback payloads are ignored when a normal mention status is present.                                                                                                        | plugin_mastodon_fetch_reply_notifications                            |
| Status context          | GET    | /api/v1/statuses/:id/context            | 0.0.0                                             | Public or read:statuses | Failures produce no descendants for that target; later rechecks may be queued.                                                                                                                                                                             | plugin_mastodon_fetch_status_context                                 |
| Single status           | GET    | /api/v1/statuses/:id                    | 0.0.0                                             | Public or read:statuses | 404/410 are treated as missing/deleted.                                                                                                                                                                                                                    | plugin_mastodon_fetch_status                                         |
| Create status           | POST   | /api/v1/statuses                        | 0.0.0                                             | write:statuses          | No create fallback.                                                                                                                                                                                                                                        | plugin_mastodon_create_status                                        |
| Edit status             | PUT    | /api/v1/statuses/:id                    | 3.5.0; `media_attributes` documented              | write:statuses          | No delete-and-redraft fallback. `api_versions[mastodon]` is preferred; otherwise human version >= 4.1.0 enables media-description updates.                                                                                                                 | plugin_mastodon_update_status                                        |
| Delete status           | DELETE | /api/v1/statuses/:id?delete_media=1     | status delete 0.0.0; `delete_media` 4.4.0, API v4 | write:statuses          | `api_versions[mastodon] >= 4` enables `delete_media`; lower API versions omit it; unknown support retries without it on legacy-style errors.                                                                                                               | plugin_mastodon_delete_status                                        |
| Upload media            | POST   | /api/v2/media                           | 3.1.3; `thumbnail` parameter 3.2.0                | write:media             | No deprecated `/api/v1/media` upload fallback. Video posters are sent as upload thumbnails.                                                                                                                                                                | plugin_mastodon_upload_media_items                                   |
| Poll media              | GET    | /api/v1/media/:id                       | 3.1.3                                             | write:media             | Media-type-aware polling and timeout windows.                                                                                                                                                                                                              | plugin_mastodon_wait_for_media_attachment                            |
| Delete unattached media | DELETE | /api/v1/media/:id                       | 4.4.0, API v4                                     | write:media             | `api_versions[mastodon] >= 4` or version >= 4.4.0 enables cleanup; known older servers skip it; unknown support stays best-effort.                                                                                                                         | plugin_mastodon_delete_media_attachment                              |

## Imported status source links

Imported Mastodon statuses that become FlatPress entries use Mastodon `Status.url` for the automatic source footer. This URL is the single status/toot permalink, while the author profile remains available separately as `account.url`. The plugin therefore builds the footer with `plugin_mastodon_imported_status_footer_bbcode()` as `[url=... target=_blank rel="nofollow noopener noreferrer"]Mastodon[/url]`, so opening the source status leaves the FlatPress blog in place.

The footer is intentionally limited to the imported entry source link. Imported reply author links continue to use the comment-author URL path and `modifier.is_external_url.php`; ordinary links that were already present in the Mastodon HTML body keep their source `href` during HTML-to-BBCode conversion.

## Profile-widget account cache

The compact Mastodon widget does not add a frontend API endpoint dependency. `plugin_mastodon_run_sync()` refreshes the profile/avatar cache via `GET /api/v1/accounts/verify_credentials` before content import/export work, including automatic scheduled runs, manual runs and explicit one-way/export-only mode. The cache stores only public fields in `fp-content/plugin_mastodon/profile/profile.json` and stores the downloaded avatar below `fp-content/plugin_mastodon/profile/`. Normal widget rendering reads only those local files and returns no widget when the cache or local avatar is incomplete. Its stylesheet is a local FlatPress asset, `fp-plugins/mastodon/res/mastodon.css`, emitted by `plugin_mastodon_head()` through `utils_asset_ver()` so frontend markup stays free of inline CSS.

| Cached field    | Source account field                     | Runtime rule                                                                                                               |
| --------------- | ---------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| Display name    | `display_name`, fallback `acct`          | Public text is normalized and escaped again during widget rendering.                                                       |
| Account handle  | `acct`, fallback `username`              | Stored without a leading `@`; rendered with one leading `@`.                                                               |
| Profile link    | `url`, fallback configured instance/user | Only absolute HTTP(S) URLs are accepted.                                                                                   |
| Avatar file     | `avatar_static`, fallback `avatar`       | Remote image is downloaded during refresh, MIME-checked and then served locally by FlatPress.                              |
| Avatar alt text | `avatar_description`, localized fallback | Mastodon 4.6/API-v9 descriptions are used when present; older payloads get a fallback string.                              |
| Refresh trigger | `verify_credentials` during `run_sync`   | The sync result is not decided solely by widget-cache refresh success; remote import may reuse the cached account payload. |

## Mastodon 4.6 account-status privacy hardening

`plugin_mastodon_fetch_account_statuses()` now treats `exclude_direct=true` as an additive Mastodon 4.6/API-v10 capability. The parameter is sent only when cached `/api/v2/instance` data confirms Mastodon `>= 4.6.0` or `api_versions.mastodon >= 10`. If a compatible server or fork still rejects the parameter with a legacy-style client error, the same page request is retried once without `exclude_direct`. This keeps Mastodon `>= 4.0.0` instances compatible while reducing the chance that direct statuses enter the import path on current servers. The existing local `visibility` checks remain the final safety net.

The compact instance snapshot also preserves `configuration.accounts.*` limits from Mastodon 4.6. They are currently informational because the plugin does not edit Mastodon profiles, but retaining them keeps admin diagnostics and future profile-related extensions deterministic.

## Mastodon status media-family rule

The status endpoint accepts `media_ids[]`, but Mastodon status validation rejects mixed media families when audio or video is present. User-facing Mastodon limits are the safest developer rule: up to several images may be attached to one post, but only one video or one audio file may be attached to one post. The plugin therefore never sends image+video, image+audio, or audio+video media IDs in one status.

The local export policy is deterministic:

1. If one or more images are present, export images only, up to `configuration.statuses.max_media_attachments`.
2. Otherwise, if audio is present, export exactly one audio attachment.
3. Otherwise, if video is present, export exactly one video attachment.
4. A FlatPress video `poster` image is passed as the `/api/v2/media` `thumbnail` field for the selected video upload; it is never appended as a second `media_id`.

The low-level upload helper can upload the media items it receives, but `plugin_mastodon_prepare_entry_media_sync_plan()` calls `plugin_mastodon_select_status_media_items()` before signatures, reuse checks, uploads, and final `POST`/`PUT /api/v1/statuses`.

## Instance-derived limits and internal defaults

The plugin reads `/api/v2/instance` with a short instance-information timeout and uses `configuration` values when present. Missing values fall back to safe internal defaults. A failed live lookup is negatively cached in request-local memory so later helper calls in the same PHP request do not repeat the same slow or failing network request.

| Limit                       | Instance field                                         | Default/fallback                                                                          |
| --------------------------- | ------------------------------------------------------ | ----------------------------------------------------------------------------------------- |
| Status max characters       | `configuration.statuses.max_characters`                | `500`                                                                                     |
| Reserved characters per URL | `configuration.statuses.characters_reserved_per_url`   | `23`                                                                                      |
| Max media attachments       | `configuration.statuses.max_media_attachments`         | `4`                                                                                       |
| Media description length    | `configuration.media_attachments.description_limit`    | `1500`                                                                                    |
| Supported MIME types        | `configuration.media_attachments.supported_mime_types` | Plugin validates broadly when unknown.                                                    |
| Image size limit            | `configuration.media_attachments.image_size_limit`     | No local size rejection when unknown.                                                     |
| Video size limit            | `configuration.media_attachments.video_size_limit`     | No local size rejection when unknown.                                                     |
| Audio size limit            | `configuration.media_attachments.audio_size_limit`     | Falls back to video size if explicit audio limit is absent; otherwise no local rejection. |

## Internal budgets and operational limits

| Name                                              | Value   | Purpose                                                                                                                         |
| ------------------------------------------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------- |
| PLUGIN_MASTODON_DEFAULT_SYNC_TIME                 | 03:00   | Default scheduled sync time.                                                                                                    |
| PLUGIN_MASTODON_MAX_STATUS_PAGES                  | 5       | Maximum account-status pages imported in one remote pass.                                                                       |
| Mastodon account-status page limit                | 40      | The plugin requests the API maximum for /api/v1/accounts/:id/statuses.                                                          |
| PLUGIN_MASTODON_IMPORTED_MEDIA_WIDTH              | 320     | Width used in imported FlatPress image markup.                                                                                  |
| PLUGIN_MASTODON_PROFILE_AVATAR_MAX_BYTES          | 5 MiB   | Maximum accepted downloaded profile avatar size for the local widget cache.                                                     |
| PLUGIN_MASTODON_PROFILE_REQUEST_TIMEOUT           | 15s     | Short timeout for account/profile-cache refresh HTTP work.                                                                      |
| PLUGIN_MASTODON_PENDING_COMMENT_RECHECK_LIMIT     | 3       | Remote reply recheck attempts per pending comment scope.                                                                        |
| PLUGIN_MASTODON_OLD_THREAD_CONTEXT_ROTATION_LIMIT | 3       | Default old-thread context targets rotated per run; admin value clamps to 1-10.                                                 |
| PLUGIN_MASTODON_NOTIFICATION_PAGE_LIMIT           | 30      | Safe Mastodon >= 4.0 notification page size used for mention hints.                                                             |
| PLUGIN_MASTODON_STATE_FALLBACK_TTL                | 300s    | Fallback cache TTL for state-related helpers.                                                                                   |
| PLUGIN_MASTODON_COOLDOWN_TTL                      | 300s    | File/APCu cooldown guard for scheduled sync paths; APCu access uses FlatPress `apcu_get`/`apcu_set`/`apcu_delete_key` wrappers. |
| PLUGIN_MASTODON_RUN_REQUEST_BUDGET                | 240     | Per-run general Mastodon API request budget.                                                                                    |
| PLUGIN_MASTODON_RUN_MEDIA_UPLOAD_BUDGET           | 24      | Per-run media upload budget.                                                                                                    |
| PLUGIN_MASTODON_RUN_DELETE_BUDGET                 | 24      | Per-run remote status delete budget.                                                                                            |
| PLUGIN_MASTODON_WINDOW_MEDIA_UPLOAD_TTL           | 1800s   | Persistent upload budget window.                                                                                                |
| PLUGIN_MASTODON_WINDOW_DELETE_TTL                 | 1800s   | Persistent delete budget window.                                                                                                |
| PLUGIN_MASTODON_WINDOW_STATUS_PAGE_BUDGET         | 300     | Cross-run status page budget.                                                                                                   |
| PLUGIN_MASTODON_WINDOW_STATUS_PAGE_TTL            | 900s    | Persistent account-status paging budget window.                                                                                 |
| PLUGIN_MASTODON_RATE_LIMIT_REMAINING_FLOOR        | 10      | Stop before the remote server's remaining quota is exhausted.                                                                   |
| PLUGIN_MASTODON_LOG_MAX_BYTES                     | 1048576 | Rotate sync.log when it grows beyond 1 MiB.                                                                                     |
| PLUGIN_MASTODON_LOG_ROTATE_FILES                  | 3       | Keep up to three rotated sync logs.                                                                                             |

## API fallback diagrams

### OAuth scope negotiation

```mermaid
flowchart TD
    Start[Need OAuth scopes]
    Discovery[GET well-known OAuth server metadata]
    Profile{profile advertised?}
    Stored{Existing app has stored scopes?}
    UseProfile[profile read:statuses read:notifications write:statuses write:media]
    UseCompatible[read:accounts read:statuses read:notifications write:statuses write:media]
    UseLegacy[stored legacy client scopes]

    Start --> Stored
    Stored -- existing legacy registration --> UseLegacy
    Stored -- none/current --> Discovery
    Discovery --> Profile
    Profile -- yes --> UseProfile
    Profile -- no or unavailable --> UseCompatible
```

### Status deletion compatibility

```mermaid
flowchart TD
    Delete[Delete mapped Mastodon status]
    Cap{Cached version known?}
    Old[DELETE /api/v1/statuses/:id]
    New[DELETE /api/v1/statuses/:id?delete_media=1]
    Fail{400 405 422 or delete_media error?}
    Retry[Retry DELETE /api/v1/statuses/:id]
    Done[Done or report failure]

    Delete --> Cap
    Cap -- < 4.4.0 --> Old --> Done
    Cap -- >= 4.4.0 --> New --> Fail
    Cap -- unknown --> New --> Fail
    Fail -- yes --> Retry --> Done
    Fail -- no --> Done
```

### Media upload and cleanup

```mermaid
sequenceDiagram
    participant Plugin
    participant Mastodon
    Plugin->>Plugin: collect all local media
    Plugin->>Plugin: select one status media family
    Plugin->>Mastodon: POST /api/v2/media with file, description, optional thumbnail
    Mastodon-->>Plugin: 200 ready or 202 processing
    loop until ready or budget exhausted
        Plugin->>Mastodon: GET /api/v1/media/:id
        Mastodon-->>Plugin: MediaAttachment
    end
    Plugin->>Mastodon: POST or PUT /api/v1/statuses with compatible media_ids[]
    alt status create/update fails after upload
        Plugin->>Plugin: check api_versions[mastodon] or cached version
        alt known unsupported before API v4 / 4.4.0
            Plugin->>Plugin: skip unsupported cleanup DELETE
        else supported or unknown
            Plugin->>Mastodon: DELETE /api/v1/media/:id
        end
    end
```

## External documentation references

- Mastodon instance API: https://docs.joinmastodon.org/methods/instance/
- Mastodon statuses API: https://docs.joinmastodon.org/methods/statuses/
- Mastodon media API: https://docs.joinmastodon.org/methods/media/
- Mastodon apps API: https://docs.joinmastodon.org/methods/apps/
- Mastodon accounts API: https://docs.joinmastodon.org/methods/accounts/
- Mastodon notifications API: https://docs.joinmastodon.org/methods/notifications/
- Mastodon OAuth methods and discovery: https://docs.joinmastodon.org/methods/oauth/
- Mastodon OAuth scopes: https://docs.joinmastodon.org/api/oauth-scopes/

## Capability sources

| Capability                             | Source                                                                           | Unknown behavior                                                  |
| -------------------------------------- | -------------------------------------------------------------------------------- | ----------------------------------------------------------------- |
| Status character limit                 | `/api/v2/instance` configuration                                                 | Use `500`.                                                        |
| URL reserved length                    | `/api/v2/instance` configuration                                                 | Use `23`.                                                         |
| Max media attachments                  | `/api/v2/instance` configuration                                                 | Use `4`.                                                          |
| Media MIME/size support                | `/api/v2/instance` configuration                                                 | Validate conservatively; do not invent server limits.             |
| Status `media_attributes` update       | Prefer `api_versions[mastodon]`; otherwise parsed version `>= 4.1.0`             | Treat as unsupported and re-upload when descriptions changed.     |
| Status delete `delete_media` parameter | Prefer `api_versions[mastodon] >= 4`; otherwise cached/stored version `>= 4.4.0` | Try with parameter, retry without on known legacy-style failures. |
| Unattached media cleanup delete        | Prefer `api_versions[mastodon] >= 4`; otherwise cached/stored version `>= 4.4.0` | Skip when known unsupported; unknown remains best-effort.         |
| OAuth `profile` scope                  | OAuth discovery `scopes_supported`                                               | Fall back to compatible `read:accounts` scope set for new apps.   |
| OAuth `read:notifications` scope       | Stored/current OAuth scope set                                                   | Skip notification hints and keep context rotation fallback.       |
| Profile avatar description             | Account payload field `avatar_description`                                       | Use localized fallback alt text when absent.                      |

## API change playbooks

### Adding a new Mastodon request

1. Add one API wrapper function rather than scattering raw endpoint strings.
2. Decide whether it consumes the general request budget or a specialized budget.
3. Define response-code handling, especially `401`, `403`, `404`, `410`, `422`, `429` and transport-code `0`.
4. Add state behavior for partial success/failure.
5. Add a simulation test with a mocked success response and at least one failure/fallback response.
6. Update the endpoint matrix in this file.

### Changing a fallback

1. Identify whether the decision is version-based, field-based, response-code-based or transport-error-based.
2. Prefer cached instance information for scheduled/deletion paths to avoid extra requests.
3. Prefer machine-readable `api_versions[mastodon]` over complex human-readable version strings for capability decisions.
4. Cache failed live instance-information lookups per PHP request and use defaults rather than repeating slow network calls.
3. Avoid fallback retry loops that can exceed budgets.
4. Preserve the first response metadata if the fallback response is returned to callers.
5. Add tests for known old version, known current version and unknown version if all three branches exist.

### Changing media behavior

1. Check both export and import paths.
2. Check instance limits and local validation.
3. Check upload cleanup on status create/update failure, including the shared `plugin_mastodon_instance_supports_mastodon_api_v4()` helper and the `plugin_mastodon_instance_supports_media_delete()` capability gate.
4. Check description-only changes and `media_attributes` capability.
5. Check that remote media import still has URL fallback order and safe local filenames.

### Changing profile-widget behavior

1. Keep the widget callback local-cache-only: no Mastodon API calls and no remote avatar downloads while rendering a page.
2. Refresh the cache from an already authenticated account/profile path.
3. Never store access tokens, client secrets or authorization headers in `profile.json`.
4. Keep the local avatar MIME allow-list conservative and reject SVG or unknown bodies.
5. Add simulation coverage for missing cache, successful cache refresh, no-HTTP rendering, fallback alt text and all language files.

# 01 — Process Map

## High-level process map

```mermaid
flowchart TD
    Request[Frontend request or admin action]
    MaybeSync[plugin_mastodon_maybe_sync]
    Admin[plugin_mastodon_admin_assign]
    RunSync[plugin_mastodon_run_sync]
    RunDelete[plugin_mastodon_run_deletion_sync]
    RemoteLocal[Remote to local import]
    LocalRemote[Local to remote export]
    Media[Media upload import cleanup]
    State[state.json mappings dirty tombstones cursors]
    Scheduler[scheduler-state.json compact summary]
    API[Mastodon API]
    Tests[Simulation regression harness]

    Request --> MaybeSync
    Request --> Admin
    MaybeSync --> Scheduler
    MaybeSync --> RunSync
    MaybeSync --> RunDelete
    Admin --> RunSync
    Admin --> RunDelete
    RunSync --> RemoteLocal
    RunSync --> LocalRemote
    RemoteLocal --> Media
    LocalRemote --> Media
    RemoteLocal <--> State
    LocalRemote <--> State
    RunDelete <--> State
    Media <--> API
    RemoteLocal <--> API
    LocalRemote <--> API
    RunDelete <--> API
    Tests --> RunSync
    Tests --> RunDelete
    Tests --> State
```

## Process catalog

| ID  | Process                             | Trigger                                                       | Core behavior                                                                                                                                   | Primary state                                                                  | Regression focus                                                                           |
| --- | ----------------------------------- | ------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------ |
| P1  | Scheduled content sync              | Frontend `init` hook via `plugin_mastodon_maybe_sync()`       | Reads compact scheduler-state first; may call `plugin_mastodon_run_sync(false)`.                                                                | scheduler-state.json, sync.guard.json, sync.lock, state.json                   | Simulation scheduler, cooldown, compact-state and large-state tests.                       |
| P2  | Manual content sync                 | Admin actions in `plugin_mastodon_admin_assign()`             | Calls `plugin_mastodon_run_sync(true, ...)` and bypasses due window; still respects lock and budgets.                                           | state.json, sync.lock, rate-limit-windows.json                                 | Manual normal/full synchronization tests.                                                  |
| P3  | Remote top-level status import      | Content sync when `update_local_from_remote` is enabled       | Verifies account, pages statuses, filters, converts HTML/media/tags, saves FlatPress entries.                                                   | entries, entries_remote, last_remote_status_id, content_stats                  | Remote import and content-window tests.                                                    |
| P4  | Remote reply import                 | Remote context pass for known imported/exported threads       | Fetches context descendants, resolves parent comments, queues rechecks or tombstones.                                                           | comments, comments_remote, comment_tombstones, pending_comment_remote_rechecks | Reply tree, self-reply, quote, comment-as-entry tests.                                     |
| P5  | Local entry export/update           | Dirty entry or local candidate in manual/full sync            | Builds status text, validates media, creates or edits Mastodon status, writes mappings.                                                         | dirty_entries, entries, entries_remote, media signatures, content_stats        | Local export, URL-budget, media reuse and update tests.                                    |
| P6  | Local comment export/update         | Dirty comment or local candidate under a mapped entry/comment | Resolves reply target, builds reply text, creates/edits Mastodon reply, handles pending parents.                                                | dirty_comments, comments, comments_remote, pending flags                       | Comment export, nested reply and pending parent tests.                                     |
| P7  | Media export                        | Local entry export/update                                     | Collects image/gallery/audio/video BBCode media, validates files, selects one Mastodon-compatible media family, uploads/polls/reuses/cleans up. | entry media metadata, rate-limit-windows.json                                  | Media upload, AudioVideo, media-family selection, thumbnail, processing and cleanup tests. |
| P8  | Media import                        | Remote status/reply import                                    | Downloads media via URL fallback order and builds FlatPress BBCode or AudioVideo tags.                                                          | imported entry/comment content, media files, captions                          | Remote media import tests.                                                                 |
| P9  | Deletion sync                       | Scheduled follow-up or admin action                           | Processes local missing mapped items and remote missing statuses; uses status delete fallback for pre-4.4 servers.                              | deletions_pending, deletion cursors, tombstones, rechecks, deletion_stats      | Deletion sync, legacy delete_media fallback and tombstone tests.                           |
| P10 | Dirty tracking hooks                | FlatPress post-success hooks                                  | Marks local changes unless a plugin-owned remote import/write guard is active.                                                                  | dirty_entries, dirty_comments, deletions_pending                               | Dirty tracking, remote-write guard and deletion tests.                                     |
| P11 | OAuth and instance capability setup | Admin registration/authorize/verify or first capability query | Registers app, discovers scopes, exchanges token, caches compact instance document.                                                             | options, instance_info_json, oauth_registered_scopes                           | OAuth, scope discovery, instance cache tests.                                              |
| P12 | Admin diagnostics                   | Opening plugin admin panel                                    | Reads options, state summaries, companion-plugin status, stats and manual-action results.                                                       | options, scheduler-state.json, state.json, sync.log                            | Admin assignment and diagnostics tests.                                                    |

## P1/P2 — Scheduled and manual content synchronization

```mermaid
flowchart TD
    Start[Request or admin action]
    Config{Instance URL and token available?}
    Due{Due or forced?}
    Cooldown{Scheduled cooldown active?}
    Lock{sync.lock acquired?}
    Guard[rate-limit guard start]
    Remote[Remote to local import]
    Local[Local to remote export]
    Write[Write state and scheduler summary]
    Stop[Return result]

    Start --> Config
    Config -- no --> Write
    Config -- yes --> Due
    Due -- no --> Stop
    Due -- yes --> Cooldown
    Cooldown -- yes and not forced --> Stop
    Cooldown -- no or forced --> Lock
    Lock -- no --> Stop
    Lock -- yes --> Guard
    Guard --> Remote
    Remote --> Local
    Local --> Write
    Write --> Stop
```

Manual sync bypasses the daily due check and can request a full window, but it still uses `sync.lock`, request budgets, media/delete windows and state writes. This matters on shared hosting: manual repair must not become unbounded.

## P3/P4 — Remote import

```mermaid
flowchart TD
    Verify[verify_credentials]
    Pages[GET account statuses pages]
    Filter{Importable? public or unlisted within window}
    Known{Known remote id?}
    Entry[Import or update FlatPress entry]
    Context[Fetch context for known threads]
    Desc[Import descendants as comments]
    Recheck[Queue pending recheck if parent unavailable]
    State[Update mappings stats last_remote_status_id]

    Verify --> Pages
    Pages --> Filter
    Filter -- no --> State
    Filter -- yes --> Known
    Known -- top-level --> Entry
    Known -- thread target --> Context
    Entry --> Context
    Context --> Desc
    Desc --> Recheck
    Desc --> State
    Recheck --> State
```

Remote import must respect local deletion protection. A remote reply with a tombstone must not be recreated as a FlatPress comment merely because it appears again in a context response.

## P5/P6/P7 — Local export and media lifecycle

```mermaid
flowchart TD
    Dirty[Dirty entry or comment]
    Parent{Reply target known?}
    Text[Build Mastodon text with URL budget tags emoji]
    MediaPlan{Entry media plan}
    Upload[POST api v2 media]
    Poll[GET api v1 media id until ready]
    Reuse[Reuse existing media ids]
    Attr[Use media_attributes if supported]
    Create[POST api v1 statuses]
    Update[PUT api v1 statuses id]
    Cleanup[DELETE unattached media on failure]
    Mapping[Update state mappings signatures dirty flags]

    Dirty --> Parent
    Parent -- missing parent --> Mapping
    Parent -- known or entry --> Text
    Text --> MediaPlan
    MediaPlan -- upload --> Upload --> Poll
    MediaPlan -- reuse --> Reuse
    MediaPlan -- description-only on >= 4.1 --> Attr
    Poll --> Create
    Reuse --> Update
    Attr --> Update
    Create --> Mapping
    Update --> Mapping
    Create -- failure after upload --> Cleanup
    Update -- failure after upload --> Cleanup
```

The media plan is one of the most important extension points. It compares attachment signatures and description signatures. If attachments did not change, the plugin can reuse remote media IDs. If only descriptions changed and the instance supports status `media_attributes`, it updates alt text without re-uploading. Otherwise it re-uploads.

Before the media plan computes signatures or uploads anything, it applies the Mastodon status media-family policy:

```text
if images exist: export images only, up to the instance image/media limit
else if audio exists: export exactly one audio attachment
else if video exists: export exactly one video attachment, with poster as thumbnail only
```

This policy belongs in the export planner, not in the raw collector. The collector still finds images, galleries, audio and video so diagnostics and change detection remain transparent. The planner then reduces the collected set to the one media family Mastodon will accept for a single status.

## P9 — Deletion synchronization

```mermaid
flowchart TD
    Start[Scheduled follow-up or admin deletion sync]
    Enabled{delete_sync_enabled?}
    Pending{pending or forced?}
    Due{due?}
    Lock{sync.lock acquired?}
    Entries[Find missing local mapped entries]
    Comments[Find missing local mapped comments]
    RemoteMissing[Check remote mapped statuses]
    Version{Known Mastodon version}
    Old[DELETE api v1 statuses id]
    New[DELETE api v1 statuses id delete_media=1]
    Retry{Legacy error 400 405 422 or delete_media message?}
    RetryOld[Retry DELETE without delete_media]
    Tombstone[Write tombstone or queue descendants recheck]
    State[Update cursors pending stats last_deletion_run]

    Start --> Enabled
    Enabled -- no --> State
    Enabled -- yes --> Pending
    Pending -- no and not forced --> State
    Pending --> Due
    Due -- no --> State
    Due -- yes --> Lock
    Lock -- yes --> Entries --> Version
    Lock -- yes --> Comments --> Version
    Lock -- yes --> RemoteMissing --> Tombstone
    Version -- < 4.4 --> Old
    Version -- >= 4.4 --> New
    Version -- unknown --> New
    New --> Retry
    Retry -- yes --> RetryOld
    Retry -- no --> Tombstone
    Old --> Tombstone
    RetryOld --> Tombstone
    Tombstone --> State
```

For Mastodon before 4.4.0, the status delete endpoint exists, but the optional `delete_media` parameter is not documented. The plugin therefore omits it when the cached version is known to be older and retries without it for unknown-version legacy failures.

## P10 — Dirty tracking and remote-write guard

```mermaid
stateDiagram-v2
    [*] --> LocalManualSave
    LocalManualSave --> DirtyEntry: entry saved
    LocalManualSave --> DirtyComment: comment saved
    LocalManualDelete --> DeletionsPending: mapped entry/comment deleted

    [*] --> PluginOwnedImport
    PluginOwnedImport --> Clean: remote-write guard active
    Clean --> [*]

    DirtyEntry --> LocalToRemoteSync
    DirtyComment --> LocalToRemoteSync
    DeletionsPending --> DeletionSync
```

A remote import writes FlatPress files too. The remote-write guard prevents those plugin-owned writes from being treated as local user edits that would immediately export back to Mastodon.

## P11/P12 — OAuth, capability setup and admin diagnostics

```mermaid
sequenceDiagram
    participant Admin
    participant Plugin
    participant Mastodon
    participant State

    Admin->>Plugin: register / authorize / verify / run sync
    Plugin->>Mastodon: GET /.well-known/oauth-authorization-server
    Mastodon-->>Plugin: scopes_supported or 404
    Plugin->>Mastodon: POST /api/v1/apps
    Plugin->>Admin: authorization URL
    Admin->>Plugin: authorization code
    Plugin->>Mastodon: POST /oauth/token
    Plugin->>Mastodon: GET /api/v1/accounts/verify_credentials
    Plugin->>Mastodon: GET /api/v2/instance
    Plugin->>State: store compact instance info and status
    Plugin->>Admin: diagnostics rows and manual results
```

Admin diagnostics should remain cheap enough for normal admin page loads while still showing enough information to diagnose missing credentials, stale state, companion plugin availability and last sync/deletion results.

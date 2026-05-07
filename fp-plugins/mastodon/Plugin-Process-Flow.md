# Mastodon Plugin Process Flows

This document describes the current FlatPress Mastodon plugin flow as implemented in `fp-plugins/mastodon/plugin.mastodon.php`, the related post-success hooks in the FlatPress core, and the companion plugin dependencies used to render imported content.

## Scope and important state files

The plugin keeps two different state layers:

- `fp-content/plugin_mastodon/scheduler-state.json` is the compact scheduler summary used on ordinary requests to decide whether a scheduled content or deletion synchronization is due.
- `fp-content/plugin_mastodon/state.json` is the full synchronization state. It contains entry/comment mappings, remote identifiers, dirty queues, tombstones, media metadata, cursors, and statistics. It is loaded only when a real sync, deletion sync, admin status view, or manual admin run needs the full state.

```mermaid
flowchart TD
    Request["FlatPress request"]
    Init["init hook: plugin_mastodon_maybe_sync"]
    SchedulerRead["Read scheduler-state.json through APCu-capable FlatPress I/O"]
    Due{"Content sync due?"}
    DeleteDue{"Deletion sync due and deletions pending?"}
    NoWork["No Mastodon HTTP request and no full state load"]
    FullState["Load full state.json"]
    ContentSync["plugin_mastodon_run_sync"]
    DeletionSync["plugin_mastodon_run_deletion_sync"]
    StateWrite["Write state.json and scheduler-state.json"]

    Request --> Init
    Init --> SchedulerRead
    SchedulerRead --> Due
    Due -- "No" --> DeleteDue
    DeleteDue -- "No" --> NoWork
    Due -- "Yes" --> FullState --> ContentSync --> StateWrite
    DeleteDue -- "Yes" --> FullState --> DeletionSync --> StateWrite
```

## 1. Bidirectional content synchronization

### 1.1 FlatPress entry to Mastodon status

A local FlatPress entry is exported as a Mastodon top-level status when it is inside the active synchronization window, or when it has been queued by post-success dirty tracking. A manual full sync keeps the repair behavior and scans all local entries.

```mermaid
sequenceDiagram
    autonumber
    actor Author as FlatPress author
    participant Core as FlatPress Core
    participant Hook as entry_saved hook
    participant Plugin as Mastodon Plugin
    participant State as state.json
    participant Media as Local media planner
    participant API as Mastodon API

    Author->>Core: Save or update entry
    Core-->>Hook: entry_saved after successful write
    Hook->>Plugin: plugin_mastodon_on_entry_saved
    Plugin->>Plugin: Check remote-write guard
    alt Guard active
        Plugin-->>State: Do not mark dirty
    else Local manual change
        Plugin->>State: Read mappings for entry
        alt Existing local-to-remote mapping changed outside window
            Plugin->>State: Add dirty_entries[entry_id]
        else Inside active window
            Plugin->>State: Remove stale dirty entry marker
        else New entry outside active window
            Plugin->>State: Keep unqueued until full/manual sync or active window
        end
        Plugin->>State: Persist scheduler summary
    end

    Note over Plugin,API: During scheduled or manual sync
    Plugin->>Plugin: plugin_mastodon_list_local_entries_for_sync
    Plugin->>Plugin: Select active-window entries plus dirty entries
    Plugin->>Plugin: plugin_mastodon_build_entry_status_text
    Plugin->>Media: plugin_mastodon_collect_local_entry_media
    Media-->>Plugin: Media IDs to reuse or local files to upload
    alt Entry already has remote_id
        Plugin->>API: PUT /api/v1/statuses/{id}
        API-->>Plugin: Updated status JSON
        Plugin->>State: Update entries mapping and clear dirty marker
    else New export
        Plugin->>API: POST /api/v1/statuses
        API-->>Plugin: Created status JSON
        Plugin->>State: Store entries[entry_id] and entries_remote[status_id]
    end
```

### 1.2 FlatPress comment to Mastodon reply

FlatPress comments are exported only after the parent entry has a remote status mapping. The plugin resolves the correct Mastodon `in_reply_to_id` from the entry mapping or from an existing parent-comment mapping.

```mermaid
sequenceDiagram
    autonumber
    actor Visitor as FlatPress visitor or author
    participant Core as FlatPress Core
    participant Hook as comment_saved hook
    participant Plugin as Mastodon Plugin
    participant State as state.json
    participant API as Mastodon API

    Visitor->>Core: Save FlatPress comment
    Core-->>Hook: comment_saved after successful write
    Hook->>Plugin: plugin_mastodon_on_comment_saved
    Plugin->>Plugin: Check remote-write guard
    alt Guard active
        Plugin-->>State: Do not mark dirty
    else Local manual comment
        Plugin->>State: Read comment and entry mappings
        alt Existing mapped comment changed outside window
            Plugin->>State: Add dirty_comments[entry_id/comment_id]
        else New comment on unsynchronized local entry inside window
            Plugin->>State: Queue entry as dirty so the parent status is created first
        else New comment without exportable parent
            Plugin->>State: Do not export yet
        end
    end

    Note over Plugin,API: During local-to-remote sync
    Plugin->>State: Require entry remote_id
    Plugin->>Plugin: Build comment status text
    Plugin->>Plugin: Resolve reply target
    alt Parent comment has remote mapping
        Plugin->>API: POST /api/v1/statuses with in_reply_to_id=parent comment status
    else Top-level FlatPress comment
        Plugin->>API: POST /api/v1/statuses with in_reply_to_id=entry status
    end
    API-->>Plugin: Created reply status JSON
    Plugin->>State: Store comments and comments_remote mapping
    Plugin->>State: Clear dirty comment marker
```

### 1.3 FlatPress replies to comments become Mastodon replies to replies

FlatPress comments can reply to other FlatPress comments. The plugin delays a child comment export until its parent comment has a remote Mastodon mapping. This avoids creating replies under the entry status when they should actually be replies under another Mastodon reply.

```mermaid
flowchart TD
    Comment["FlatPress comment selected for export"]
    DetectParent["Detect local parent comment ID"]
    ParentPending{"Parent comment exists and export is still pending?"}
    Defer["Defer this comment and retry in the same run"]
    Resolve["Resolve reply target"]
    ParentRemote{"Parent comment has remote_id?"}
    ReplyToParent["Create or update Mastodon status in_reply_to_id = parent remote_id"]
    ReplyToEntry["Create or update Mastodon status in_reply_to_id = entry remote_id"]
    Mapping["Store comment mapping with parent_comment_id and in_reply_to_remote_id"]
    Exhausted{"No progress after retry guard?"}
    LogDeferred["Log deferred export"]

    Comment --> DetectParent --> ParentPending
    ParentPending -- "Yes" --> Defer --> Exhausted
    Exhausted -- "No" --> DetectParent
    Exhausted -- "Yes" --> LogDeferred
    ParentPending -- "No" --> Resolve --> ParentRemote
    ParentRemote -- "Yes" --> ReplyToParent --> Mapping
    ParentRemote -- "No" --> ReplyToEntry --> Mapping
```

### 1.4 Mastodon top-level status to FlatPress entry

The plugin fetches account statuses from Mastodon, filters unsupported or out-of-window statuses, converts Mastodon HTML to FlatPress BBCode, imports remote media, and writes an entry under the remote-write guard.

```mermaid
sequenceDiagram
    autonumber
    participant Sync as plugin_mastodon_sync_remote_to_local
    participant API as Mastodon API
    participant Converter as HTML and media import
    participant Guard as Remote-write guard
    participant Core as FlatPress Core
    participant State as state.json

    Sync->>API: GET /api/v1/accounts/verify_credentials
    API-->>Sync: Account ID
    Sync->>API: GET account statuses since last_remote_status_id
    API-->>Sync: Status list
    loop Each remote top-level status
        Sync->>Sync: Check visibility and content window
        alt Not importable or outside window
            Sync->>Sync: Aggregate skip log
        else Importable
            Sync->>Converter: Convert HTML to FlatPress BBCode
            Converter->>Converter: Map links, quotes, code, lists, emoji shortcodes
            Converter->>Converter: Download media attachments when present
            Guard->>Guard: Enter local-write guard
            Guard->>Core: entry_save entry
            Core-->>Guard: entry_saved hook fires
            Guard->>Guard: Hook ignores plugin-owned write
            Guard->>Guard: Leave local-write guard
            Sync->>State: Store remote-source entry mapping
        end
    end
```

### 1.5 Mastodon replies in a known imported thread to FlatPress comments

After importing or refreshing a known entry status, the plugin fetches the Mastodon context and walks descendants. It imports only public/importable replies that are not blocked by local tombstones and whose parent relationship can be resolved.

```mermaid
flowchart TD
    EntryMapped["Known entry mapping: local entry <-> remote status"]
    FetchContext["Fetch /api/v1/statuses/{id}/context"]
    Descendants["Read context descendants"]
    BuildIndex["Build remote status lookup"]
    NextReply["Next descendant reply"]
    Importable{"Public/importable, in sync start, no tombstone?"}
    ParentKnown{"Parent is entry status or known/imported reply?"}
    Wait["Keep reply pending for another pass"]
    Import["plugin_mastodon_import_remote_comment"]
    Quote{"Quote imported reply parent enabled?"}
    AddQuote["Prepend FlatPress quote block"]
    SaveComment["comment_save under remote-write guard"]
    StoreMapping["Store comments and comments_remote mapping"]
    Fallback{"No progress in pass?"}
    ForceImport["Import remaining with unresolved parent reference"]

    EntryMapped --> FetchContext --> Descendants --> BuildIndex --> NextReply
    NextReply --> Importable
    Importable -- "No" --> NextReply
    Importable -- "Yes" --> ParentKnown
    ParentKnown -- "No" --> Wait --> Fallback
    ParentKnown -- "Yes" --> Import --> Quote
    Quote -- "Yes" --> AddQuote --> SaveComment
    Quote -- "No" --> SaveComment
    SaveComment --> StoreMapping --> NextReply
    Fallback -- "Progress made" --> NextReply
    Fallback -- "No progress" --> ForceImport --> SaveComment
```

## 2. Media, attachments, tags, and hashtags

### 2.1 FlatPress entry media to Mastodon media attachments

The plugin scans entry content for image, gallery, audio, and video BBCode. It validates each local file, respects the Mastodon instance media limits, uploads changed attachments, or reuses existing remote media IDs when possible.

```mermaid
flowchart TD
    EntryContent["FlatPress entry content"]
    Collect["plugin_mastodon_collect_local_entry_media"]
    ImageTags["img tags and inline img BBCode"]
    GalleryTags["gallery tags and gallery captions"]
    AudioVideoTags["audioplayer and videoplayer tags"]
    Validate["Validate file exists, MIME type, size limits, media type"]
    Plan["plugin_mastodon_prepare_entry_media_sync_plan"]
    Reuse{"Existing remote media signature unchanged?"}
    Upload["Upload via /api/v2/media or media endpoint"]
    Wait["Poll media processing until URL is available"]
    ReuseIDs["Reuse previous media IDs"]
    Status["Create or update Mastodon status with media_ids"]
    Cleanup{"Status request failed after upload?"}
    DeleteUpload["Best-effort DELETE uploaded media attachments"]
    StoreMeta["Store remote_media and media signatures in state"]

    EntryContent --> Collect
    Collect --> ImageTags --> Validate
    Collect --> GalleryTags --> Validate
    Collect --> AudioVideoTags --> Validate
    Validate --> Plan --> Reuse
    Reuse -- "Yes" --> ReuseIDs --> Status
    Reuse -- "No" --> Upload --> Wait --> Status
    Status --> Cleanup
    Cleanup -- "Yes" --> DeleteUpload
    Cleanup -- "No" --> StoreMeta
```

### 2.2 Mastodon media attachments to FlatPress media markup

Remote media is downloaded into FlatPress-managed directories. Images become PhotoSwipe-compatible `[img]` or `[gallery]` markup. Audio and video become AudioVideo plugin player tags.

```mermaid
flowchart TD
    RemoteStatus["Remote Mastodon status"]
    Attachments["media_attachments"]
    Classify{"Attachment type"}
    Image["image"]
    Audio["audio"]
    Video["video or gifv"]
    Download["Download url, remote_url, or preview_url fallback"]
    Temp["Write to temporary plugin media directory"]
    ImageStore["Move images to fp-content/images/mastodon/status-ID"]
    AttachStore["Move audio/video to fp-content/attachs/mastodon/status-ID"]
    Captions["Write .captions.conf when multiple images have descriptions"]
    ImageMarkup{"Image count"}
    ImgTag["Single image BBCode with width and title"]
    GalleryTag["Multiple image gallery BBCode with width"]
    AudioTag["Audio player BBCode with controls and description"]
    VideoTag["Video player BBCode with controls, poster, and description"]
    EntryContent["Append imported media BBCode to FlatPress entry or comment"]

    RemoteStatus --> Attachments --> Classify
    Classify -- "image" --> Image --> Download --> Temp --> ImageStore --> Captions --> ImageMarkup
    ImageMarkup -- "one" --> ImgTag --> EntryContent
    ImageMarkup -- "many" --> GalleryTag --> EntryContent
    Classify -- "audio" --> Audio --> Download --> Temp --> AttachStore --> AudioTag --> EntryContent
    Classify -- "video/gifv" --> Video --> Download --> Temp --> AttachStore --> VideoTag --> EntryContent
```

### 2.3 Tags and hashtags when the FlatPress Tag plugin is active

When the Tag plugin is active, local FlatPress `[tag]` metadata is exported as a Mastodon hashtag footer. Remote Mastodon tags are imported back as FlatPress tag BBCode. The plugin also strips its own hashtag footer during round-trip imports.

```mermaid
flowchart LR
    subgraph FlatPress_to_Mastodon["FlatPress to Mastodon"]
        FPEntry["Entry content with Tag plugin BBCode"]
        ExtractTags["Extract and normalize FlatPress tags"]
        StripTagBBCode["Remove [tag] markup from plain-text body"]
        Footer["Build hashtag footer line"]
        StatusText["Append hashtags to Mastodon status text"]
    end

    subgraph Mastodon_to_FlatPress["Mastodon to FlatPress"]
        RemoteStatus["Remote status with tags array and content"]
        ConvertHTML["Convert Mastodon HTML to FlatPress BBCode"]
        StripFooter["Strip trailing hashtag footer generated by this plugin"]
        BuildTagBBCode["Build [tag] tag1, tag2[/tag]"]
        SaveEntry["Save FlatPress entry with tag metadata"]
    end

    FPEntry --> ExtractTags --> Footer --> StatusText
    FPEntry --> StripTagBBCode --> StatusText
    RemoteStatus --> ConvertHTML --> StripFooter --> SaveEntry
    RemoteStatus --> BuildTagBBCode --> SaveEntry
```

### 2.4 Companion plugin dependency overview

The Mastodon plugin can store imported content without all companion plugins, but these plugins determine whether imported markup renders correctly in the FlatPress frontend.

```mermaid
flowchart TD
    MastodonPlugin["Mastodon plugin"]
    ImportedContent["Imported and synchronized content"]
    BBCode["BBCode plugin"]
    PhotoSwipe["PhotoSwipe plugin"]
    AudioVideo["AudioVideo plugin"]
    Tag["Tag plugin"]
    Emoticons["Emoticons plugin"]

    MastodonPlugin --> ImportedContent

    BBCode -->|"Renders imported formatting, links, images, and galleries"| ImportedContent
    PhotoSwipe -->|"Enhances image and gallery presentation"| ImportedContent
    AudioVideo -->|"Renders audio/video attachments as HTML5 media players"| ImportedContent
    Tag -->|"Enables FlatPress tags and Mastodon hashtags in both directions"| ImportedContent
    Emoticons -->|"Renders imported emoji shortcodes more nicely"| ImportedContent

    MastodonPlugin -. "detects plugin_bbcode_startup, do_bbcode_url, do_bbcode_img" .-> BBCode
    MastodonPlugin -. "detects PhotoSwipeFunctions" .-> PhotoSwipe
    MastodonPlugin -. "detects AudioVideoPlugin" .-> AudioVideo
    MastodonPlugin -. "detects plugin_tag_entry and the plugin_tag object" .-> Tag
    MastodonPlugin -. "detects plugin_emoticons global map" .-> Emoticons
```

## 3. Scheduled and manual sync flows

### 3.1 Daily scheduled content synchronization

The scheduled content sync is started from the `init` hook. Ordinary requests read only the compact scheduler state first. If the scheduled time has already run on the same day, the request exits without loading the full state and without contacting Mastodon.

```mermaid
flowchart TD
    Init["init hook"]
    Method{"Request method POST?"}
    Options["Load plugin options"]
    Configured{"Instance URL and access token configured?"}
    Scheduler["Read scheduler-state.json"]
    Due{"plugin_mastodon_sync_due?"}
    Guard{"Content sync cooldown active?"}
    Lock["Open sync.lock and acquire non-blocking exclusive lock"]
    FullState["Load full state.json"]
    Stats["Reset content_stats and last_error"]
    RemoteToLocal["plugin_mastodon_sync_remote_to_local"]
    LocalToRemote["plugin_mastodon_sync_local_to_remote"]
    FlushSkips["Flush aggregated skip-log summaries"]
    Write["Write state.json and scheduler-state.json"]
    DeletePending{"Deletion sync enabled?"}
    Pending["Mark deletions_pending and not-before cooldown"]
    Release["Release lock and stop rate-limit guard"]
    End["Return to normal FlatPress request"]

    Init --> Method
    Method -- "Yes" --> End
    Method -- "No" --> Options --> Configured
    Configured -- "No" --> End
    Configured -- "Yes" --> Scheduler --> Due
    Due -- "No" --> End
    Due -- "Yes" --> Guard
    Guard -- "Active" --> End
    Guard -- "Clear" --> FullState --> Lock
    Lock --> Stats --> RemoteToLocal --> LocalToRemote --> FlushSkips --> DeletePending
    DeletePending -- "Yes" --> Pending --> Write
    DeletePending -- "No" --> Write
    Write --> Release --> End
```

### 3.2 Local-to-remote candidate selection during scheduled sync

Scheduled syncs are optimized for large blogs. They do not parse every old entry every day. Manual full syncs still parse every entry.

```mermaid
flowchart TD
    Start["plugin_mastodon_list_local_entries_for_sync"]
    DirtyLookup["Build lookup from dirty_entries and dirty_comments"]
    CollectFiles["Collect entry files from CONTENT_DIR"]
    NextFile["Next entry file"]
    Force{"force == true?"}
    Dirty{"Entry ID in dirty lookup?"}
    Window{"Entry ID date inside active content window?"}
    Skip["Skip file without entry_parse"]
    Parse["entry_parse"]
    Draft{"Draft category?"}
    Add["Add to ordered export list"]
    Sort["Sort by local item timestamp"]
    Result["Return selected entries"]

    Start --> DirtyLookup --> CollectFiles --> NextFile
    NextFile --> Force
    Force -- "Yes" --> Parse
    Force -- "No" --> Dirty
    Dirty -- "Yes" --> Parse
    Dirty -- "No" --> Window
    Window -- "Yes" --> Parse
    Window -- "No" --> Skip --> NextFile
    Parse --> Draft
    Draft -- "Yes" --> NextFile
    Draft -- "No" --> Add --> NextFile
    NextFile --> Sort --> Result
```

### 3.3 Follow-up deletion synchronization

The deletion sync is intentionally separate from the content sync. It compares stored mappings against local files and remote statuses. Local deletions are propagated to Mastodon; remote deletions are reflected back into FlatPress under the remote-write guard.

```mermaid
flowchart TD
    Start["plugin_mastodon_run_deletion_sync"]
    Options["Load options and full state"]
    Enabled{"Deletion sync enabled?"}
    Pending{"Force or deletions_pending?"}
    Due{"Deletion sync due?"}
    Lock["Acquire sync.lock"]
    RecheckOnly{"Pending comment descendant rechecks only?"}
    EntryLoop["Iterate entry mappings with cursor"]
    EntryLocal{"Local entry exists?"}
    DeleteRemoteEntry["DELETE remote entry status"]
    EntryLookupWindow{"Remote lookup window allows check?"}
    FetchRemoteEntry["Fetch remote entry status"]
    MissingRemoteEntry{"Remote entry missing?"}
    DeleteLocalEntry["entry_delete under remote-write guard"]
    CommentLoop["Iterate comment mappings with cursor"]
    CommentLocal{"Local comment exists?"}
    DeleteRemoteComment["DELETE remote comment status"]
    QueueDesc["Queue descendant remote rechecks"]
    CommentLookupWindow{"Remote lookup window allows check?"}
    FetchRemoteComment["Fetch remote comment status"]
    MissingRemoteComment{"Remote comment missing?"}
    DeleteLocalComment["comment_delete under remote-write guard"]
    ProcessRechecks["Process pending comment remote rechecks"]
    Complete{"Failures or rate limit?"}
    ClearPending["Clear pending flags and cursors"]
    Retry["Keep deletions_pending with cooldown"]
    Write["Write state and scheduler summary"]

    Start --> Options --> Enabled
    Enabled -- "No" --> ClearPending --> Write
    Enabled -- "Yes" --> Pending
    Pending -- "No" --> Write
    Pending -- "Yes" --> Due
    Due -- "No" --> Write
    Due -- "Yes" --> Lock --> RecheckOnly
    RecheckOnly -- "No" --> EntryLoop
    EntryLoop --> EntryLocal
    EntryLocal -- "No" --> DeleteRemoteEntry --> CommentLoop
    EntryLocal -- "Yes" --> EntryLookupWindow
    EntryLookupWindow -- "No" --> CommentLoop
    EntryLookupWindow -- "Yes" --> FetchRemoteEntry --> MissingRemoteEntry
    MissingRemoteEntry -- "Yes" --> DeleteLocalEntry --> CommentLoop
    MissingRemoteEntry -- "No" --> CommentLoop
    CommentLoop --> CommentLocal
    CommentLocal -- "No" --> DeleteRemoteComment --> QueueDesc --> ProcessRechecks
    CommentLocal -- "Yes" --> CommentLookupWindow
    CommentLookupWindow -- "No" --> ProcessRechecks
    CommentLookupWindow -- "Yes" --> FetchRemoteComment --> MissingRemoteComment
    MissingRemoteComment -- "Yes" --> DeleteLocalComment --> QueueDesc --> ProcessRechecks
    MissingRemoteComment -- "No" --> ProcessRechecks
    RecheckOnly -- "Yes" --> ProcessRechecks
    ProcessRechecks --> Complete
    Complete -- "No failures" --> ClearPending --> Write
    Complete -- "Failures or rate limit" --> Retry --> Write
```

### 3.4 Manual full synchronization in the admin area

Manual full synchronization is an explicit admin repair and initial-import/export path. It intentionally loads the full state and scans all entries. It is not optimized away by dirty tracking.

```mermaid
sequenceDiagram
    autonumber
    actor Admin as FlatPress admin
    participant AdminUI as Mastodon admin panel
    participant Plugin as Mastodon Plugin
    participant State as state.json
    participant Core as FlatPress Core
    participant API as Mastodon API

    Admin->>AdminUI: Press "Run now" or "Run full synchronization"
    AdminUI->>Plugin: plugin_mastodon_run_sync(true, fullWindow)
    Plugin->>State: Load full state.json
    Plugin->>Plugin: Bypass scheduled cooldown guard because force=true
    Plugin->>Plugin: Acquire sync.lock
    Plugin->>API: Verify credentials
    Plugin->>API: Fetch remote statuses and contexts
    Plugin->>Core: Import or update entries/comments under remote-write guard
    Plugin->>Plugin: Scan local entries
    alt Full window requested
        Plugin->>Plugin: Parse every local entry as repair path
    else Manual non-full run
        Plugin->>Plugin: Use configured window and dirty candidates
    end
    Plugin->>API: Create or update statuses and replies
    Plugin->>State: Update mappings, stats, scheduler summary
    Plugin-->>AdminUI: Return result and diagnostics

    Admin->>AdminUI: Press "Run full deletion synchronization"
    AdminUI->>Plugin: plugin_mastodon_run_deletion_sync(true)
    Plugin->>State: Load full state.json
    Plugin->>Plugin: Ignore not-before cooldown because force=true
    Plugin->>Core: Delete local entries/comments under remote-write guard when remote disappeared
    Plugin->>API: Delete remote statuses when local content disappeared
    Plugin->>State: Update tombstones, cursors, deletion stats
    Plugin-->>AdminUI: Return deletion result
```

## 4. Core post-success hooks, dirty tracking, and remote-write guard

The current design depends on post-success hooks in the FlatPress core. These hooks fire after a write or delete operation has succeeded. The Mastodon plugin uses them to queue local manual changes instead of rediscovering every old change by scanning the whole archive daily.

```mermaid
flowchart TD
    subgraph Core["FlatPress core"]
        EntrySave["entry_save succeeds"]
        EntrySaved["do_action entry_saved"]
        EntryDelete["entry_delete succeeds"]
        EntryDeleted["do_action entry_deleted"]
        CommentSave["comment_save succeeds"]
        CommentSaved["do_action comment_saved"]
        CommentDelete["comment_delete succeeds"]
        CommentDeleted["do_action comment_deleted"]
    end

    subgraph Plugin["Mastodon plugin hook handlers"]
        Guard{"plugin_mastodon_local_write_guard_active?"}
        EntryDirty["plugin_mastodon_on_entry_saved sets dirty_entries when needed"]
        EntryDeletion["plugin_mastodon_on_entry_deleted sets deletions_pending when mapped"]
        CommentDirty["plugin_mastodon_on_comment_saved sets dirty_comments or queues parent entry"]
        CommentDeletion["plugin_mastodon_on_comment_deleted sets deletions_pending when mapped"]
        StateWrite["Write state.json and scheduler-state.json"]
    end

    EntrySave --> EntrySaved --> Guard
    EntryDelete --> EntryDeleted --> Guard
    CommentSave --> CommentSaved --> Guard
    CommentDelete --> CommentDeleted --> Guard

    Guard -- "Yes, plugin-owned remote import/delete" --> Stop["Ignore hook to avoid false local dirty markers"]
    Guard -- "No, local manual change" --> EntryDirty --> StateWrite
    Guard -- "No, local manual deletion" --> EntryDeletion --> StateWrite
    Guard -- "No, local manual comment" --> CommentDirty --> StateWrite
    Guard -- "No, local manual comment deletion" --> CommentDeletion --> StateWrite
```

```mermaid
sequenceDiagram
    autonumber
    participant RemoteSync as Remote import or remote deletion sync
    participant Guard as Local write guard
    participant Core as FlatPress Core
    participant Hook as Core post-success hook
    participant Handler as Mastodon hook handler
    participant State as state.json

    RemoteSync->>Guard: plugin_mastodon_local_write_guard_enter
    RemoteSync->>Core: entry_save, comment_save, entry_delete, or comment_delete
    Core-->>Hook: entry_saved, comment_saved, entry_deleted, or comment_deleted
    Hook->>Handler: Mastodon handler is called
    Handler->>Guard: plugin_mastodon_local_write_guard_active
    Guard-->>Handler: true
    Handler-->>State: No dirty marker and no deletion-pending marker
    RemoteSync->>Guard: plugin_mastodon_local_write_guard_leave
    RemoteSync->>State: Store authoritative remote-source mapping changes
```

## 5. Operational guarantees and intentional behavior

```mermaid
flowchart TD
    Goal["Large FlatPress archive"]
    OrdinaryRequest["Ordinary frontend GET"]
    ScheduledRun["Scheduled content sync"]
    DeletionRun["Follow-up deletion sync"]
    ManualRun["Manual full admin sync"]
    SchedulerOnly["Uses compact scheduler-state.json first"]
    TargetedScan["Scans active window plus dirty entries/comments"]
    FullStateNeeded["Loads full state.json because mappings are required"]
    FullRepair["Full repair scan remains available"]
    NoFalseDirty["Remote-write guard prevents plugin-owned writes from becoming local dirty markers"]

    Goal --> OrdinaryRequest --> SchedulerOnly
    Goal --> ScheduledRun --> TargetedScan
    Goal --> DeletionRun --> FullStateNeeded
    Goal --> ManualRun --> FullRepair
    ScheduledRun --> NoFalseDirty
    DeletionRun --> NoFalseDirty
    ManualRun --> NoFalseDirty
```

Key implications for developers:

- Scheduled content syncs are optimized for large blogs by using post-success dirty queues and date-window selection.
- Manual full syncs deliberately remain exhaustive and should not be replaced by dirty queues.
- Deletion syncs need the full mapping state because they compare local existence with remote status existence and maintain tombstones and descendant rechecks.
- Companion plugins improve rendering and feature completeness, but the Mastodon plugin still stores importable FlatPress markup even when a companion renderer is currently inactive.

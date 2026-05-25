# 01 — Double Opt-in De-duplication

## Goal

The newsletter plugin must never allow one e-mail address to create several active pending confirmation links or several confirmed subscriber rows.

The implementation uses three layers:

1. `plugin_newsletter_store_pending_token_once()` replaces older non-expired pending tokens for the same address whenever a new subscription request is accepted.
2. `plugin_newsletter_confirm_pending_token()` confirms only a non-expired matching token and removes every pending token for that same address after a successful confirmation.
3. `plugin_newsletter_add_subscriber_once()` appends the subscriber only when no matching subscriber row exists and reduces legacy duplicate rows for that address to one preserved row.

## Address comparison

Encrypted storage cannot be compared directly because encryption is randomized. The plugin therefore decrypts stored addresses and compares normalized plain addresses through `plugin_newsletter_email_matches()`.

The comparison key keeps the local part unchanged and normalizes the domain part through `plugin_newsletter_normalize_domain()` to lower-case ASCII when possible. This avoids duplicate rows caused by domain casing or IDN spelling while not merging local parts that a strict mail system could treat as different mailboxes.

## Subscription flow

```mermaid
flowchart TD
    Submit["POST newsletter_submit"]
    Validate["CSRF, honeypot, consent, syntax, DNS, blocklist, rate limit"]
    AlreadySub{"Already in subscribers.txt?"}
    Generate["Generate new token and encrypted address"]
    ReplacePending["plugin_newsletter_store_pending_token_once"]
    PendingFile["pending.txt"]
    SendMail["Send confirmation mail with newest token"]
    ConfirmedPage["Redirect: subscription-confirmed"]
    CheckMailPage["Redirect: check-your-email"]
    Reject["Reject / redirect to error page"]

    Submit --> Validate
    Validate -- "invalid" --> Reject
    Validate -- "valid" --> AlreadySub
    AlreadySub -- "yes" --> ConfirmedPage
    AlreadySub -- "no" --> Generate --> ReplacePending
    ReplacePending --> PendingFile
    ReplacePending --> SendMail --> CheckMailPage
```

## Confirmation flow

```mermaid
flowchart TD
    Link["GET newsletter_action=confirm & email & token"]
    FormalCheck{"Valid e-mail and non-empty token?"}
    PendingExists{"pending.txt exists?"}
    Scan["Read pending rows under file lock"]
    Expire["Drop expired or malformed pending rows"]
    Match{"Same address and hash_equals token?"}
    AddOnce["plugin_newsletter_add_subscriber_once"]
    RemovePending["Remove all pending rows for confirmed address"]
    SubscriberFile["subscribers.txt"]
    Success["Redirect: subscription-confirmed"]
    Invalid["Redirect: invalid-token"]

    Link --> FormalCheck
    FormalCheck -- "no" --> Invalid
    FormalCheck -- "yes" --> PendingExists
    PendingExists -- "no" --> Invalid
    PendingExists -- "yes" --> Scan --> Expire --> Match
    Match -- "no" --> Invalid
    Match -- "yes" --> AddOnce --> SubscriberFile
    AddOnce --> RemovePending --> Success
```

## Sequence of duplicate protection

```mermaid
sequenceDiagram
    participant U as User/browser
    participant S as Subscribe handler
    participant P as pending.txt
    participant C as Confirm handler
    participant B as subscribers.txt

    U->>S: Submit address
    S->>P: Store token A
    U->>S: Submit same address again
    S->>P: Remove token A, store token B
    U->>C: Open old token A
    C->>P: No matching pending token
    C-->>U: invalid-token
    U->>C: Open token B
    C->>P: Matching non-expired token
    C->>B: Add subscriber once
    C->>P: Remove all pending rows for same address
    C-->>U: subscription-confirmed
    U->>C: Replay token B
    C->>P: No matching pending token
    C-->>U: invalid-token
```

## Invariants

| Invariant                                                                                                                 | Enforced by                                            |
| ------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| At most one active pending token per address after a normal subscription request                                          | `plugin_newsletter_store_pending_token_once()`         |
| A successful confirmation removes all pending tokens for that address                                                     | `plugin_newsletter_confirm_pending_token()`            |
| A subscriber row is appended only if the address is not already confirmed                                                 | `plugin_newsletter_add_subscriber_once()`              |
| Legacy duplicate subscriber rows for a confirmed address are reduced to one row on the next confirmation for that address | `plugin_newsletter_add_subscriber_once()`              |
| Other addresses are not affected by the cleanup                                                                           | Storage callbacks filter only matching comparison keys |

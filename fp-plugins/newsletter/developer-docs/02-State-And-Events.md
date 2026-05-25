# 02 — State and Events

## Main state files

| File                             | Purpose                                                                   | Writer                                                    |
| -------------------------------- | ------------------------------------------------------------------------- | --------------------------------------------------------- |
| `pending.txt`                    | Non-confirmed double opt-in entries: `encryptedEmail|token|UnixTimestamp` | subscription and confirmation logic                       |
| `subscribers.txt`                | Confirmed entries: `encryptedEmail|UnixTimestamp`                         | confirmation, unsubscribe, admin delete, dispatch cleanup |
| `batch-offset.txt`               | Current staggered dispatch offset                                         | automatic and manual dispatch                             |
| `next-send-date.txt`             | Next scheduled dispatch timestamp                                         | automatic and manual dispatch                             |
| `manual-flag.txt`                | Marks a manual batch run after admin CSRF validation                      | admin send-now and dispatch completion                    |
| `blocked-ips.txt`                | Honeypot/rate-limit lockouts                                              | subscription handler                                      |
| `disposable-email-blocklist.txt` | Local disposable-domain list                                              | blocklist updater                                         |
| `newsletter-dns-cache.txt`       | Cached DNS validation results                                             | validation routine                                        |

## Request-level event map

```mermaid
flowchart TD
    Request["FlatPress request"]
    Load["plugin.newsletter.php loaded"]
    PreBlocklist["plugin_newsletter_maybe_update_blocklist silent preflight"]
    Init["plugin_newsletter_init"]
    Cleanup["Cleanup blocked IPs and expired pending rows"]
    AdminDelete{"POST newsletter_delete?"}
    AdminSend{"POST newsletter_send_now?"}
    AdminCsrf{"Admin CSRF valid?"}
    ManualReady{"No running batch?"}
    Unsub{"GET unsubscribe?"}
    Confirm{"GET confirm?"}
    Subscribe{"POST newsletter_submit?"}
    Dispatch["plugin_newsletter_check_and_send"]
    ExitBadCsrf["400 Invalid CSRF token"]
    RedirectRunning["Redirect: running batch"]
    Widget["register_widget newsletter"]
    AdminPanel["admin_addpanelaction plugin/newsletter"]
    Blocklist["Blocklist file already available to handlers"]

    Request --> Load --> PreBlocklist --> Init --> Cleanup
    Cleanup --> AdminDelete
    AdminDelete -- "yes" --> AdminPanel
    AdminDelete -- "no" --> AdminSend
    AdminSend -- "yes" --> AdminCsrf
    AdminCsrf -- "no" --> ExitBadCsrf
    AdminCsrf -- "yes" --> ManualReady
    ManualReady -- "yes" --> Dispatch
    ManualReady -- "no" --> RedirectRunning
    AdminSend -- "no" --> Unsub
    Unsub -- "yes" --> Dispatch
    Unsub -- "no" --> Confirm
    Confirm -- "yes" --> Dispatch
    Confirm -- "no" --> Subscribe
    Subscribe -- "yes" --> Dispatch
    Subscribe -- "no" --> Dispatch
    Dispatch --> Widget --> AdminPanel --> Blocklist
```

## Request-order invariants

| Invariant                                                                                          | Reason                                                                                    |
| -------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------- |
| Blocklist maintenance runs before subscription handling                                            | A clean installation can apply a freshly downloaded disposable-domain list immediately    |
| Failed pre-request blocklist fetches are silent and throttled                                      | Redirects and headers must not be broken by warning output on strict shared hosting       |
| `manual-flag.txt` is created only after a valid admin CSRF token                                   | Forged or stale POST requests must not leave a manual-dispatch marker                     |
| A running batch prevents a new manual send-now dispatch before `manual-flag.txt` is created        | Existing automatic or manual batch state remains authoritative                            |

## Double opt-in state machine

```mermaid
stateDiagram-v2
    [*] --> NotSubscribed
    NotSubscribed --> Pending: valid subscription request
    Pending --> Pending: same address subscribes again / replace token
    Pending --> Confirmed: newest valid token confirmed
    Pending --> NotSubscribed: pending token expires
    Pending --> NotSubscribed: old replaced token used
    Confirmed --> Confirmed: repeated subscription request
    Confirmed --> NotSubscribed: unsubscribe or admin delete
    Confirmed --> Confirmed: replayed confirmation token rejected
```

## Storage rules for pending and subscribers

- `pending.txt` is treated as a short-lived token queue.
- New pending entries are written through `plugin_newsletter_store_pending_token_once()`.
- Confirmations are processed through `plugin_newsletter_confirm_pending_token()`.
- Confirmed rows are written through `plugin_newsletter_add_subscriber_once()`.
- Stored encrypted values are never compared directly; comparison always decrypts and normalizes the plain address.
- Malformed and expired pending rows are removed opportunistically during storage and confirmation operations.


## Validation and DNS-cache lifecycle

```mermaid
flowchart TD
    Input["Plain e-mail input"]
    Prepare["plugin_newsletter_prepare_email_for_validation"]
    Domain["plugin_newsletter_normalize_domain"]
    Syntax{"Syntax valid?"}
    Cache{"DNS cache hit?"}
    Cleanup{"Day >= 28 and cleanup not run this month?"}
    CleanupRun["plugin_newsletter_cleanup_dns_cache"]
    Dns["MX lookup, dns_get_record fallback, A/AAAA fallback"]
    Accept["Accept address"]
    Reject["Reject address"]

    Input --> Prepare --> Domain --> Syntax
    Syntax -- "no" --> Reject
    Syntax -- "yes" --> Cleanup
    Cleanup -- "yes" --> CleanupRun --> Cache
    Cleanup -- "no" --> Cache
    Cache -- "valid" --> Accept
    Cache -- "invalid" --> Reject
    Cache -- "miss" --> Dns
    Dns -- "mail host found" --> Accept
    Dns -- "temporary DNS/API unavailable" --> Accept
    Dns -- "repeated no-host failures" --> Reject
```

## DNS-cache cleanup invariant

The DNS cache stores normalized ASCII domains. Cleanup must therefore derive subscriber domains from the subscriber row format `encryptedEmail|UnixTimestamp` by decrypting only the first column.

```mermaid
sequenceDiagram
    participant C as DNS cache
    participant S as subscribers.txt
    participant H as cleanup helper

    H->>C: Read domain|status|expires
    H->>S: Read encryptedEmail|timestamp rows
    H->>H: Split row at first pipe
    H->>H: Decrypt encryptedEmail only
    H->>H: Normalize domain to lower-case ASCII
    H->>C: Keep cache entries with subscribed domains
    H->>C: Remove cache entries without subscribers
    H->>C: Write normalized cache file
```

# 04 — Email Validation and DNS Cache

## Goal

The newsletter plugin should accept valid ASCII addresses and improve support for EAI/SMTPUTF8-style Unicode local parts without breaking shared-hosting compatibility.

The implementation separates three concerns:

1. `plugin_newsletter_prepare_email_for_validation()` performs syntax preparation and returns a normalized representation.
2. `plugin_newsletter_normalize_domain()` converts domains to lower-case ASCII and uses IDN/Punycode conversion when PHP's `intl` extension is available.
3. `plugin_newsletter_cleanup_dns_cache()` removes DNS-cache entries whose normalized domains no longer belong to confirmed subscribers.

## Validation flow

```mermaid
flowchart TD
    Raw["Raw submitted address"]
    Utf8{"Valid UTF-8 and no whitespace/control chars?"}
    Split{"Exactly one @ and non-empty local/domain?"}
    NormalizeDomain["Normalize domain to lower-case ASCII"]
    Length{"Local <= 64 bytes and full address <= 254 bytes?"}
    DotAtom{"Dot-atom rules: no edge dot, no double dot"}
    LocalType{"ASCII local part?"}
    Filter["FILTER_VALIDATE_EMAIL"]
    EaiRegex["Conservative Unicode local-part regex"]
    Prepared["Prepared: local@ascii-domain"]
    Invalid["Invalid"]

    Raw --> Utf8
    Utf8 -- "no" --> Invalid
    Utf8 -- "yes" --> Split
    Split -- "no" --> Invalid
    Split -- "yes" --> NormalizeDomain
    NormalizeDomain -- "fails" --> Invalid
    NormalizeDomain --> Length
    Length -- "no" --> Invalid
    Length -- "yes" --> DotAtom
    DotAtom -- "no" --> Invalid
    DotAtom -- "yes" --> LocalType
    LocalType -- "yes" --> Filter
    Filter -- "fails" --> Invalid
    Filter -- "passes" --> Prepared
    LocalType -- "no" --> EaiRegex
    EaiRegex -- "fails" --> Invalid
    EaiRegex -- "passes" --> Prepared
```

## DNS flow

```mermaid
flowchart TD
    Prepared["Prepared address"]
    Cache{"Cache entry for normalized domain?"}
    ValidCache["Cached valid"]
    InvalidCache["Cached invalid"]
    DnsApi{"DNS APIs available?"}
    Mx["getmxrr MX lookup"]
    DnsGet["dns_get_record MX fallback"]
    ARecord["A/AAAA fallback"]
    Temporary["Accept temporarily"]
    FailureLog["Record no-host failure"]
    Accept["Valid for subscription/dispatch"]
    Reject["Invalid"]

    Prepared --> Cache
    Cache -- "valid and not expired" --> ValidCache --> Accept
    Cache -- "invalid and not expired" --> InvalidCache --> Reject
    Cache -- "miss/expired" --> DnsApi
    DnsApi -- "no" --> Temporary
    DnsApi -- "yes" --> Mx
    Mx -- "found" --> Accept
    Mx -- "not found" --> DnsGet
    DnsGet -- "DNS error/API unavailable" --> Temporary
    DnsGet -- "MX found" --> Accept
    DnsGet -- "no MX" --> ARecord
    ARecord -- "A or AAAA found" --> Accept
    ARecord -- "none" --> FailureLog
    FailureLog -- "below threshold" --> Temporary
    FailureLog -- "third failure in 96 days" --> Reject
```

## DNS-cache cleanup

The cleanup runs at most once per month from day 28 when validation is called. It can also be called directly by the simulation.

Rules:

- Cache rows are parsed as `domain|status|expires`.
- Cache domains are normalized before being used as keys.
- Subscriber rows are parsed as `encryptedEmail|UnixTimestamp`.
- Only the encrypted e-mail column is decrypted.
- The subscriber domain is normalized with the same helper as validation.
- Cache entries for domains without confirmed subscribers are removed.

## EAI boundaries

The fallback improves compatibility for Unicode local parts on PHP builds where `FILTER_VALIDATE_EMAIL` rejects them. It does not guarantee that every downstream mail transport or provider accepts SMTPUTF8 during delivery. The plugin can validate and store such addresses; final delivery still depends on the server MTA and the receiving provider.

IDN domains require PHP's `intl` extension for reliable conversion. When `intl` is unavailable and a submitted domain contains non-ASCII characters, the plugin rejects the address rather than performing an invalid DNS lookup.


## Confirmation links

The confirmation handler uses the same syntax preparation helper as subscription validation for the formal e-mail check. This avoids a split-brain situation where a Unicode local part is accepted during subscription but rejected by the later confirmation link before `pending.txt` is checked.

```mermaid
sequenceDiagram
    participant U as User
    participant S as Subscribe handler
    participant P as pending.txt
    participant C as Confirm handler
    participant B as subscribers.txt

    U->>S: Submit EAI address
    S->>S: Prepare syntax and validate DNS/cache
    S->>P: Store pending encrypted EAI address
    U->>C: Open confirmation link
    C->>C: Prepare syntax with same helper
    C->>P: Match e-mail comparison key and token
    C->>B: Add subscriber once
```

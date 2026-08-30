# Reverse proxy and public URL detection

FlatPress distinguishes the connection that reaches PHP from the public request seen by the visitor's browser. This matters on shared hosts, CDNs, reverse proxies and load balancers that terminate TLS and forward the request to PHP over HTTP.

This document is intended for both FlatPress administrators and developers. The first part explains the observable behavior and configuration. The developer reference below documents the trust model and the public-URL helpers that should be reused by plugins and core code.

## What administrators need to know

On a conventional HTTPS host, PHP normally sees the same transport as the browser, for example:

```text
HTTPS = on
REQUEST_SCHEME = https
SERVER_PORT = 443
```

No forwarded transport information is required in this case. `general['trust_forwarded_scheme']` therefore normally remains `false`. That is the expected state and does **not** mean that HTTPS is disabled or was detected incorrectly.

A TLS-terminating hosting platform can look different to PHP:

```text
Browser: HTTPS on public port 443
PHP:     HTTP on backend port 80
Header:  X-Forwarded-Proto: https
```

FlatPress keeps these two views separate. It can use the forwarded public scheme and port without leaking an internal backend port such as `:80` into generated HTTPS URLs.

### Common hosting topologies

| Hosting situation | Typical PHP view | Forwarded scheme needed? | Typical saved `trust_forwarded_scheme` |
| --- | --- | --- | --- |
| Direct HTTPS origin | `HTTPS=on`, `REQUEST_SCHEME=https`, `SERVER_PORT=443` | No | `false` |
| Direct HTTP origin | `REQUEST_SCHEME=http`, `SERVER_PORT=80` | No | `false` |
| Direct HTTPS on a non-default public port | HTTPS with e.g. `SERVER_PORT=8443` | No | `false` |
| TLS terminator forwarding HTTPS to PHP as HTTP/80 | Backend HTTP/80 plus an unambiguous forwarded HTTPS scheme | Yes | Usually `true` after the administrator saves the matching public URL |
| Ambiguous or contradictory forwarded metadata | e.g. `X-Forwarded-Proto: https,http` | Rejected | `false` |

## Setup, URL confirmation and stored trust

During first-time setup and Location Migration Mode, FlatPress may use one unambiguous forwarded scheme as an **administrator-visible URL proposal**. This proposal is not yet a runtime security decision.

For a first installation, `SERVER_NAME` remains the host source so that an untrusted `Host` header cannot define the installation URL. Explicit Location Migration Mode may use the validated request host.

There is no separate "trust proxy" checkbox. The decision is made automatically when the administrator saves the public blog URL:

- during setup, when the URL in setup step 2 is submitted;
- on an installed blog, when **Admin area -> Configuration -> Blog URL** is saved.

The result is stored with the normal FlatPress configuration in:

```text
fp-content/config/settings.conf.php
```

For example:

```php
'general' => array(
    'www' => 'https://example.org/blog/',
    'trust_forwarded_scheme' => true,
    // ...
)
```

The default in `fp-defaults/settings-defaults.php` is `false`.

`trust_forwarded_scheme` is recalculated whenever the general configuration is saved. It is not a permanent manually controlled switch. If an installation is moved from a TLS-terminating proxy to a direct HTTPS host and the configuration is saved there, the value can correctly return to `false`.

### When is `trust_forwarded_scheme` stored as `true`?

`fp_should_trust_forwarded_scheme_for_url()` evaluates the URL being saved against the current request. Trust is persisted only when all relevant checks succeed, including:

- the saved URL is a valid HTTP or HTTPS URL;
- the forwarded scheme is present, valid and unambiguous;
- the forwarded scheme matches the scheme of the URL being saved;
- the URL host matches the validated `SERVER_NAME` used for this trust decision;
- forwarded transport metadata actually contributed to the public request context that the administrator is confirming;
- the effective public port matches the port of the saved URL, using 443/80 when the URL omits the scheme's default port.

Typical cases that leave the value at `false` include direct HTTPS without forwarded headers, a missing forwarded scheme, ambiguous values such as `https,http`, a host/scheme/port mismatch, or a forwarded header that did not actually contribute to the confirmed public context.

Administrators should not manually force this value to `true` merely because a hosting provider uses a proxy. Direct HTTPS hosts do not need it, and accepting untrusted forwarded transport metadata can weaken request-origin decisions.

## Public port handling

`SERVER_PORT` describes the connection that reached the PHP/backend server. Behind TLS termination it is not necessarily the port visible to the browser.

When an accepted forwarded context is used:

- a valid and unambiguous `X-Forwarded-Port` is used when present;
- otherwise the public default for the selected scheme is assumed (`443` for HTTPS, `80` for HTTP);
- a default port is omitted from generated URLs.

This prevents a public request from becoming `https://example.org:80/` simply because the hosting frontend forwarded HTTPS to PHP over backend HTTP port 80.

The same normalization works when a hosting stack sets `HTTPS=on` while PHP still sees backend port 80. Conversely, coherent direct HTTPS on a real non-default port such as 8443 is preserved unless an accepted forwarded context explicitly supplies another public port.

## HTTPS redirects and first-time setup

A first-installation URL candidate is deliberately marked as untrusted. Even if it proposes an HTTPS URL based on a forwarded candidate, it cannot trigger canonical HTTPS enforcement before the configuration has been saved.

This prevents redirect loops on TLS-terminating hosts where the browser repeatedly requests HTTPS while PHP sees only backend HTTP/80.

After setup has persisted the canonical URL and, when necessary, forwarded-scheme trust, `is_https()` and HTTPS redirect handling use the same centralized public request context. FlatPress does not maintain a second independent forwarded-header parser for redirects.

## PrettyURLs and the green capability checks

PrettyURLs Automatic mode is intentionally conservative. It selects Pretty automatically only when FlatPress has positive rewrite evidence, such as:

- the FlatPress rewrite environment marker from the generated Apache configuration;
- an IIS rewrite signal;
- a real routed path without `index.php` that actually reached the FlatPress front controller.

A generic host-provided `REDIRECT_URL` is not sufficient, because shared hosting platforms may set it for unrelated platform rewrites. If Pretty support cannot be proven, Automatic falls back to HTTP Get. Explicit Pretty remains selectable for NGINX and other servers whose rewrite configuration is managed outside `.htaccess`.

The green `check-green.svg` icons in the PrettyURLs admin panel describe **measured host capabilities**, not the mode currently selected by Automatic. The admin page runs authenticated, same-origin browser probes for all three routable modes:

- **Path Info** gets a check only when the probe suffix reaches `index.php` as `PATH_INFO` or `ORIG_PATH_INFO`;
- **HTTP Get** gets a check only when the generated `?u=/...` URL reaches `index.php` with the expected route parameter intact;
- **Pretty** gets a check only when a clean URL without `index.php` actually reaches the FlatPress front controller.

The probe endpoint is read-only, requires an authenticated FlatPress session, disables response caching and returns an exact response token only for a matching request. `capability-probe.js` is loaded with the FlatPress CSP nonce (`RANDOM_HEX`) and its URL is versioned with `utils_asset_ver()`.

The generated Apache configuration excludes the dedicated Path Info probe from the Pretty catch-all rule so that Pretty rewriting cannot create a false-positive Path Info result.

## Update-check diagnostics

The update checker does not attempt to bypass hosting-provider restrictions on outbound server-to-server HTTPS connections. Instead it records which transport was available and why the request failed.

Possible transport values include:

- `curl`;
- `stream`;
- `none`.

Failure diagnostics distinguish cases such as:

- `no_transport`;
- `transport_error`;
- `http_error`;
- `empty_response`;
- `invalid_response`.

This makes a hosting restriction distinguishable from an HTTP error or from an invalid FlatPress version response without adding provider-specific workarounds.

## Troubleshooting for administrators

### `trust_forwarded_scheme` is `false` although the blog uses HTTPS

This is normally correct on a direct HTTPS host. If PHP already sees `HTTPS=on`, `REQUEST_SCHEME=https` and the correct public port, no forwarded scheme needs to be trusted.

### The proposed setup URL contains `https://...:80/`

The backend port is being treated as public transport information. With the current public-request-context implementation, an accepted TLS-termination candidate should normalize backend HTTP/80 to public HTTPS/443 unless a different public port is explicitly and consistently forwarded.

### `ERR_TOO_MANY_REDIRECTS` during setup

Check whether the browser uses HTTPS while PHP receives HTTP/80 through a TLS terminator. An unconfirmed first-installation HTTPS proposal must not trigger canonical HTTPS redirection. After the matching URL has been saved, forwarded-scheme trust may be persisted for subsequent requests.

### A PrettyURLs mode has no green check

The check is based on a real request, not merely on the existence of `.htaccess`. A missing check means that the corresponding capability probe did not prove that exact URL form on the current host. For Pretty on Apache, verify that the generated rewrite configuration is active. For NGINX, a manually configured Pretty mode may still be usable even if server-side rewrite support cannot be inferred from the admin request itself; the browser probe must succeed before the green capability check appears.

## Developer reference

### Central public request context

`fp-includes/core/core.connection.php` centralizes scheme, public port and trust-source detection in:

```php
fp_public_request_context(array $trustedProxies = array(), $allowForwardedCandidate = false)
```

It returns:

```php
array(
    'scheme' => 'https',
    'port' => 443,
    'source' => 'configured-proxy',
    'forwarded' => true,
    'trusted' => true
)
```

The `source` value describes why the result was selected:

- `origin` - direct server/origin transport won;
- `trusted-proxy` - the immediate proxy was explicitly trusted, or a private/loopback/reserved intermediary was accepted when no explicit list was supplied;
- `proxy-heuristic` - multiple independent proxy/HTTPS signals corroborated the forwarded transport;
- `configured-proxy` - persisted `trust_forwarded_scheme` plus matching configured host and scheme authorized the forwarded scheme;
- `forwarded-candidate` - an untrusted setup/migration proposal only.

`forwarded-candidate` must never be used for cookie, redirect or other runtime security decisions. It is enabled only through `$allowForwardedCandidate = true` for administrator-visible setup/migration proposals.

### Forwarded-header validation and trust model

Forwarded schemes are read from supported transport metadata such as `X-Forwarded-Proto`, RFC `Forwarded: proto=...` and `X-Forwarded-Scheme`. Comma-separated values are accepted only when every value agrees. Conflicting or invalid values are ambiguous and are rejected.

`X-Forwarded-Port` follows the same principle: every reported hop must provide the same valid port in the range 1-65535. A value such as `443,8443` is ambiguous and is not guessed.

Runtime forwarded transport is accepted only through a trust path implemented by `fp_public_request_context()`. The current implementation can use an explicitly trusted proxy CIDR/IP, a private/loopback/reserved intermediary when no explicit list is supplied, corroborating HTTPS/proxy signals, or persisted configured trust. A single arbitrary client-supplied `X-Forwarded-Proto: https` is therefore not automatically a runtime HTTPS decision.

### Public-URL helper responsibilities

Core and plugins should reuse the centralized helpers instead of independently parsing transport headers:

| Helper | Responsibility |
| --- | --- |
| `fp_public_request_context()` | Determine effective public scheme, port, source and trust state |
| `is_https()` | Security-oriented boolean HTTPS decision using the public request context |
| `canonical_server_name()` | Validate and normalize `SERVER_NAME` without attaching a backend port |
| `canonical_server_host()` | Combine the validated host with the correct public non-default port |
| `fp_current_public_url()` | Build the externally visible current URL from the canonical FlatPress origin and request URI |
| `fp_setup_baseurl_candidate()` | Build the administrator-visible setup/migration proposal |
| `fp_should_trust_forwarded_scheme_for_url()` | Decide whether saving a public URL should persist forwarded-scheme trust |
| `fp_https_redirect_required()` | Decide whether a trusted configured HTTPS base URL requires an upgrade redirect |

Plugins must not reconstruct public URLs by independently combining `SERVER_PORT`, `HTTP_HOST`, `X-Forwarded-Proto`, `X-Forwarded-Port` or similar values. SEO Meta Tag Info already uses `fp_current_public_url()` for its current-page URL.

### Host validation

Trust decisions compare the configured host against validated `SERVER_NAME`; they do not use an arbitrary `HTTP_HOST` value. `canonical_server_name()` rejects control characters and invalid host forms and safely handles IPv4 and IPv6 literals.

This separation is intentional: request-host aliases may be valid for explicit migration, but a first-time setup or stored proxy-trust decision must not be derived from a potentially attacker-controlled Host header.

### Cache behavior

`is_https()` has a per-request cache and an optional APCu cache. The current APCu namespace is:

```text
fp:https:v4:<sha1(env_state)>
```

The cache key incorporates relevant server variables, normalized trusted proxies, the configured canonical URL and persisted forwarded-scheme trust. Changes to the trust state therefore do not reuse a result calculated for a different public-request context.

PrettyURLs Automatic detection uses its own versioned cache namespace and invalidates it through the plugin's `apcu_gen` generation when relevant configuration changes.

## Regression tests

The regression scripts are stored with the PrettyURLs plugin. Run them from the repository root:

```text
php fp-plugins/prettyurls/regression-tests/connection_proxy_regression.ph_
php fp-plugins/prettyurls/regression-tests/prettyurls_auto_mode_regression.ph_
```

`connection_proxy_regression.ph_` covers, among other cases:

- TLS termination on backend HTTP/80 with `HTTPS` unset;
- hosting stacks that set `HTTPS=on` while still exposing backend port 80;
- suppression of first-install HTTPS redirect loops;
- untrusted versus persisted forwarded-scheme handling;
- ambiguous forwarded schemes and ports;
- proxy identity/private-hop trust paths;
- direct non-default ports;
- prevention of backend `:80` leakage into public HTTPS URLs;
- canonical host validation.

`prettyurls_auto_mode_regression.ph_` covers, among other cases:

- rejection of generic `REDIRECT_URL` as standalone rewrite proof;
- Path Info, HTTP Get and Pretty capability probes;
- authenticated probe response handling;
- the CSP nonce and `utils_asset_ver()` for `capability-probe.js`;
- the Apache Path Info probe exception;
- explicit NGINX-compatible Pretty mode;
- explicit HTTP Get mode.

The scripts are CLI regression tests. The live browser capability checks still need a real web-server environment to prove what a particular host supports.

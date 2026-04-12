# FlatPress Smarty Block Cache Overview

This document summarizes FlatPress' custom Smarty `{cache}{/cache}` block, explains how it works, and lists every place where it is currently used in the codebase.

It is intended for core maintainers, theme authors, and plugin developers who want to understand the current fragment-cache layout before adding or changing cache blocks.

---

## 1. Why FlatPress uses a custom Smarty block cache

FlatPress keeps **global Smarty output caching disabled** at CMS level. That is intentional, because whole-page caching would be too coarse for many FlatPress pages and would make it harder to keep admin-only or request-dependent output safe.

To still speed up expensive and frequently repeated fragments, FlatPress ships a custom Smarty block plugin:

- **Implementation:** `fp-includes/fp-smartyplugins/block.cache.php`
- **Syntax:** `{cache ...}...{/cache}`

This allows FlatPress to cache only selected fragments while the rest of the page stays fully dynamic.

In practice, this approach is useful for fragments that are:

- rendered very often,
- identical across many requests,
- expensive enough to justify a file read instead of repeated template/plugin work,
- safe to share across visitors, or safely separated by cache-key variants.

---

## 2. How the custom block works

## 2.1 Storage location

Cached fragments are written below:

```text
CACHE_DIR/smarty-block-cache/<group>/<hash-prefix>/<hash>.cache.php
```

The cache file stores a serialized payload with:

- creation timestamp,
- TTL,
- template timestamp,
- rendered fragment content.

## 2.2 Cache key composition

Each block cache key is based on:

- `group`
- `id` or `key`
- `vary`
- automatic runtime variance
- current template metadata

Automatic runtime variance currently includes:

- FlatPress locale
- login state by default
- request path/query string when `vary_request=true` (or `vary_route=true`)

Template metadata is included so that the cache is invalidated automatically when the underlying template source timestamp changes.

## 2.3 Cache lifecycle

On block open:

1. FlatPress builds the cache state.
2. It tries to read the fragment from the cache file.
3. On a hit, the cached HTML/XML is returned immediately and the inner template code is skipped.

On block close:

1. FlatPress receives the rendered fragment content.
2. It serializes the payload.
3. It writes the fragment to the cache file.

## 2.4 Invalidation

A cached fragment is discarded when:

- the TTL has expired,
- the template source timestamp no longer matches,
- the file cannot be decoded as a valid cache payload.

---

## 3. Supported block attributes

The current block plugin supports these parameters.

### `id` / `key`

Optional explicit block identifier.

Use this whenever the same template may render multiple cache blocks or render a block conditionally. A stable `id` keeps the cache key deterministic.

### `ttl` / `lifetime`

Cache lifetime in seconds.

- Default: `3600`
- `<= 0` means no time-based expiry  
  (template timestamp invalidation still applies)

### `group`

Logical namespace used in the cache file path.

It helps separate unrelated fragments and makes the cache directory easier to inspect.

### `vary`

Additional custom value that becomes part of the cache key.

Use it when a fragment changes based on a known variable such as a widget option.

### `enabled`

Boolean-like flag.

When false, the block renders live and no cache file is used.

### `vary_login` / `vary_logged_in`

Boolean-like flag. Default: `true`.

This prevents guest/admin output from being mixed by default. It can be set to `false` for truly public fragments that are identical for both.

### `vary_request` / `vary_route`

Boolean-like flag. Default: `false`.

Enable this for fragments whose output depends on the current request path or query string, such as filtered feeds.

---

## 4. Current deployment map

The current codebase uses the Smarty block cache in **9 template files**.

### 4.1 Theme: top widget area

- **File:** `fp-interface/themes/leggero/widgetstop.tpl`
- **Block parameters:**
  - `id='theme_leggero_widgets_top'`
  - `ttl=3600`
  - `group='theme-leggero'`
  - `vary_login=false`

#### Why this helps

This wraps the `widgets pos=top` render path of the active Leggero theme.

The top widget area is usually composed of stable menu-like content. A cache hit avoids re-running the inner widget loop and avoids repeated rendering of the same fragment on every page view.

#### Why `vary_login=false` is safe here

This area is intended for public navigation content. There is no need to create separate guest/admin variants, so disabling login variance reduces duplicate cache files and unnecessary disk I/O.

---

### 4.2 Theme: bottom widget area

- **File:** `fp-interface/themes/leggero/widgetsbottom.tpl`
- **Block parameters:**
  - `id='theme_leggero_widgets_bottom'`
  - `ttl=3600`
  - `group='theme-leggero'`
  - `vary_login=false`

#### Why this helps

This is the same optimization pattern as the top widget area, but for the bottom widget region.

Bottom widgets are typically static navigation or informational blocks. Keeping a warm fragment cache here reduces repeated widget rendering and keeps the number of cache files low.

---

### 4.3 Categories widget template

- **File:** `fp-plugins/categories/tpls/widget.tpl`
- **Block parameters:**
  - `id='plugin_categories_widget'`
  - `ttl=1800`
  - `group='widget-categories'`
  - `vary=$categories_showcount`
  - `vary_login=false`

#### Why this helps

The categories widget is often displayed on many pages with the same configuration. Caching it avoids rebuilding the category list for every request.

#### Why `vary=$categories_showcount` is needed

The widget can render either with or without entry counts. That changes the output, so the cache must separate those variants.

#### Why `vary_login=false` is safe here

The output is public and does not contain admin-only controls.

---

### 4.4 Main RSS feed template

- **File:** `fp-interface/sharedtpls/rss.tpl`
- **Block parameters:**
  - `id='shared_feed_rss'`
  - `ttl=120`
  - `group='feeds-main'`
  - `vary_request=true`
  - `vary_login=false`

#### Why this helps

Feed readers and bots often request the same feed repeatedly within short time windows. A short-lived fragment cache avoids rebuilding the whole feed XML on every poll.

#### Why `vary_request=true` is important

Main feeds can be requested in different filtered forms. Request-based variance keeps those feed variants separate so one route does not reuse another route's cached output.

---

### 4.5 Main Atom feed template

- **File:** `fp-interface/sharedtpls/atom.tpl`
- **Block parameters:**
  - `id='shared_feed_atom'`
  - `ttl=120`
  - `group='feeds-main'`
  - `vary_request=true`
  - `vary_login=false`

#### Why this helps

This is the Atom counterpart to the RSS optimization. The benefit is the same: repeated polling requests reuse a recently generated feed body instead of rebuilding it each time.

---

### 4.6 Comment RSS feed template

- **File:** `fp-interface/sharedtpls/comment-rss.tpl`
- **Block parameters:**
  - `id='shared_comment_feed_rss'`
  - `ttl=60`
  - `group='feeds-comments'`
  - `vary_request=true`
  - `vary_login=false`

#### Why this helps

Comment feeds can be requested repeatedly for the same entry. Short caching reduces repeated feed generation while still keeping comment updates reasonably fresh.

#### Why the TTL is shorter

Comment streams are more volatile than navigation widgets, so the cache should refresh sooner.

---

### 4.7 Comment Atom feed template

- **File:** `fp-interface/sharedtpls/comment-atom.tpl`
- **Block parameters:**
  - `id='shared_comment_feed_atom'`
  - `ttl=60`
  - `group='feeds-comments'`
  - `vary_request=true`
  - `vary_login=false`

#### Why this helps

This is the Atom counterpart of the comment RSS feed cache. The rationale and safety constraints are the same.

---

### 4.8 LastComments RSS feed template

- **File:** `fp-plugins/lastcomments/tpls/plugin.lastcomments-feed.tpl`
- **Block parameters:**
  - `id='plugin_lastcomments_feed_rss'`
  - `ttl=60`
  - `group='feeds-lastcomments'`
  - `vary_request=true`
  - `vary_login=false`

#### Why this helps

The LastComments plugin feed is an ideal short-lived feed cache candidate. Many requests ask for the same XML, and the fragment is public.

---

### 4.9 LastComments Atom feed template

- **File:** `fp-plugins/lastcomments/tpls/plugin.lastcomments-atom.tpl`
- **Block parameters:**
  - `id='plugin_lastcomments_feed_atom'`
  - `ttl=60`
  - `group='feeds-lastcomments'`
  - `vary_request=true`
  - `vary_login=false`

#### Why this helps

This is the Atom equivalent of the LastComments RSS feed cache and follows the same strategy.

---

## 5. Why some areas are intentionally not cached

The absence of a cache block is often deliberate.

### 5.1 Right sidebar widgets in `widgets.tpl`

The right sidebar is intentionally rendered live.

Reason:

- it can contain context-sensitive widgets,
- widgets such as `related` depend on the currently viewed entry,
- some widgets are only shown in specific contexts,
- caching the whole sidebar would either risk stale/wrong output or require overly fine-grained cache keys.

### 5.2 Whole-page templates

Templates such as entry pages, search pages, forms, or admin views are not good default candidates for this block cache because they mix many dynamic concerns in a single render path.

The current strategy deliberately prefers **small, stable fragments** over broad page-level fragment caching.

---

## 6. Practical guidance for developers

When deciding whether to add a new `{cache}` block, use this checklist.

### Good candidates

Use a block cache when the fragment is:

- public or safely separated by cache-key variance,
- stable across many requests,
- expensive enough to justify a disk read,
- small enough that the number of cache files stays manageable.

### Bad candidates

Avoid block caches when the fragment is:

- heavily request-dependent,
- session-dependent,
- mixed with admin-only controls,
- already efficiently cached elsewhere,
- likely to create many low-value cache variants.

### Attribute selection tips

- Prefer an explicit `id`.
- Use `group` to keep related fragments together.
- Add `vary` only for real output differences.
- Disable `vary_login` only when the fragment is truly identical for guests and admins.
- Enable `vary_request` when the fragment depends on route or query parameters.
- Choose the shortest TTL that still reduces repeated work.

---

## 7. Current summary table

| Area | File | id | ttl | group | Extra variance | Why it is cached |
|---|---|---|---:|---|---|---|
| Leggero top widgets | `fp-interface/themes/leggero/widgetstop.tpl` | `theme_leggero_widgets_top` | 3600 | `theme-leggero` | `vary_login=false` | Stable top navigation/widget fragment |
| Leggero bottom widgets | `fp-interface/themes/leggero/widgetsbottom.tpl` | `theme_leggero_widgets_bottom` | 3600 | `theme-leggero` | `vary_login=false` | Stable bottom navigation/widget fragment |
| Categories widget | `fp-plugins/categories/tpls/widget.tpl` | `plugin_categories_widget` | 1800 | `widget-categories` | `vary=$categories_showcount`, `vary_login=false` | Reused sidebar widget with limited variants |
| Main RSS feed | `fp-interface/sharedtpls/rss.tpl` | `shared_feed_rss` | 120 | `feeds-main` | `vary_request=true`, `vary_login=false` | Repeated polling by feed readers |
| Main Atom feed | `fp-interface/sharedtpls/atom.tpl` | `shared_feed_atom` | 120 | `feeds-main` | `vary_request=true`, `vary_login=false` | Repeated polling by feed readers |
| Comment RSS feed | `fp-interface/sharedtpls/comment-rss.tpl` | `shared_comment_feed_rss` | 60 | `feeds-comments` | `vary_request=true`, `vary_login=false` | Short-lived per-request comment feed cache |
| Comment Atom feed | `fp-interface/sharedtpls/comment-atom.tpl` | `shared_comment_feed_atom` | 60 | `feeds-comments` | `vary_request=true`, `vary_login=false` | Short-lived per-request comment feed cache |
| LastComments RSS feed | `fp-plugins/lastcomments/tpls/plugin.lastcomments-feed.tpl` | `plugin_lastcomments_feed_rss` | 60 | `feeds-lastcomments` | `vary_request=true`, `vary_login=false` | Short-lived public plugin feed cache |
| LastComments Atom feed | `fp-plugins/lastcomments/tpls/plugin.lastcomments-atom.tpl` | `plugin_lastcomments_feed_atom` | 60 | `feeds-lastcomments` | `vary_request=true`, `vary_login=false` | Short-lived public plugin feed cache |

---

## 8. File references

### Block implementation

- `fp-includes/fp-smartyplugins/block.cache.php`

### Current template usage

- `fp-interface/themes/leggero/widgetstop.tpl`
- `fp-interface/themes/leggero/widgetsbottom.tpl`
- `fp-plugins/categories/tpls/widget.tpl`
- `fp-interface/sharedtpls/rss.tpl`
- `fp-interface/sharedtpls/atom.tpl`
- `fp-interface/sharedtpls/comment-rss.tpl`
- `fp-interface/sharedtpls/comment-atom.tpl`
- `fp-plugins/lastcomments/tpls/plugin.lastcomments-feed.tpl`
- `fp-plugins/lastcomments/tpls/plugin.lastcomments-atom.tpl`

---

## 9. Takeaway

The current FlatPress Smarty block cache is intentionally conservative.

It is not used as a page cache. Instead, it targets a small set of stable, high-reuse fragments where a cache hit can skip repeated template/plugin work without risking wrong output across visitors or routes.

That makes it a useful performance tool for FlatPress developers, especially when they keep cache scope narrow, cache keys explicit, and fragment variance well understood.

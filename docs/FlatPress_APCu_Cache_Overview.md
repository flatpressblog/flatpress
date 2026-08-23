# APCu Cache Overview in FlatPress 1.6.dev

This document summarizes all APCu-backed caches used in FlatPress `1.6.dev`, their purpose, lifetime, invalidation strategy, and rough performance impact.

It is intended for maintainers, plugin authors, and performance testing.

To view and manage user cache entries, [Joe Watkins'](https://github.com/krakjoe) [APCu Control Panel library](https://github.com/krakjoe/apcu) ([PHP License](https://github.com/krakjoe/apcu/blob/master/LICENSE)) can be used during the development phase.

---

## 1. Core APCu Helpers and Namespacing

### 1.1 `is_apcu_on()`

**File:** `fp-includes/core/core.apcu.php`  
**Purpose:** Central runtime check if APCu should be used.

- Verifies:
  - `apcu_fetch()` exists.
  - APCu is enabled (`apcu_enabled()` or `apc.enabled`).
  - In CLI/phpdbg, `apc.enable_cli` must be true **or** the dedicated test override `FP_APCU_ENABLE_CLI=1` must be present.
- Result is memoized per request in a static variable.

**Note:**  
`FP_APCU_ENABLE_CLI=1` exists only so FlatPress can simulate APCu-backed code paths in CLI-based regression tests. It does **not** bypass a genuinely disabled APCu extension.

**Impact:**  
High. Every APCu-aware function uses this as a guard, so misconfiguration here would disable all caches.

---

### 1.2 `apcu_ns()` and `apcu_key()`

**File:** `fp-includes/core/core.apcu.php`  

- `apcu_ns()`:
  - Computes a FlatPress-instance namespace ID based on `ABS_PATH` (or the core include path).
  - Bootstrap key: `fp:ns:<sha1(base_path)>`.
  - Value: random hex string (16–64 chars).
  - Stores the namespace in:
    - APCu under `fp:ns:<sha1(base_path)>`.
    - `FP_APCU_NS` constant.
    - `$GLOBALS['FP_APCU_NS']`.

- `apcu_key($key)`:
  - Returns:
    - `"<key>"` if APCu is off (no namespacing).
    - `"fp:<NS>:<key>"` if APCu is on, where `<NS>` is the current namespace ID.
  - All calls via wrapper `apcu_get()/apcu_set()/apcu_incr()` therefore live under `fp:<NS>:`.

**Impact:**  
High. This is the isolation layer between multiple FlatPress instances sharing an APCu pool.

---

### 1.3 Wrapper Functions

**File:** `fp-includes/core/core.apcu.php`  

- `apcu_get($key, &$ok = null)`  
- `apcu_set($key, $val, $ttl = 120)`  
- `apcu_incr($key, $step = 1, &$success = null)`  
- `apcu_delete_key($key)`

These helpers:

- Short-circuit if `is_apcu_on()` is false.
- Normalize keys through `apcu_key()`, except where raw `apcu_*` functions are used deliberately.
- Respect TTL (`$ttl`) for `apcu_set`.
- Give other subsystems a central, instance-safe delete path (`apcu_delete_key`) for invalidation and cleanup.

**Impact:**  
High. Most APCu usage flows through these helpers.

---

## 2. Core Data Caches

### 2.1 File Content Cache – `fp:io:…`

**Prefix:** `fp:io:<filename>:<mtime>:<size>`  
**File:** `fp-includes/core/core.fileio.php`  

- Caches results of `io_load_file()` for any file loaded through this helper  
  (most notably frequently accessed core, config, and content files).
- Key includes:
  - Absolute filename.
  - File mtime.
  - File size.
- Invalidation:
  - Automatic when the file changes (mtime/size).
  - No global version bump needed.
- APCu entry size guard:
  - Controlled via env `FP_APCU_IO_MAX_BYTES` (bytes). Default 32768 (32 KiB).
  - Values larger than this are **not** stored in APCu  
    (but are still returned and kept in the per-request local cache).
- TTL:
  - Controlled via env `FP_APCU_IO_TTL` (seconds). Default 3600s (1h), set in `defaults.php`  
    (fallback in `core.fileio.php` is 7200s).
  - Note: the key already changes with `mtime/size`, so TTL mainly limits  
    how long older versions can remain in APCu until eviction.
  - For constrained APCu pools (< 32 MiB), consider `FP_APCU_IO_TTL=600–1800`.

**Impact:**  
High. Reduces filesystem I/O for frequently accessed content and config files.

---

### 2.2 Entry Parse Cache – `fp:entry:parsed:…`

**Prefix:**  
- `fp:entry:parsed:<basename>:<mtime>:<size>` (BlogDB-level)  
- `fp:entry:parsed:<id>:<mtime>:<size>` (Entry-level)  

**Files:**

- `fp-includes/core/core.blogdb.php`
- `fp-includes/core/core.entry.php`

**What is cached:**

- Parsed entry metadata and content arrays.

**Invalidation:**

- mtime + size of the entry file.
- Any update to the entry file automatically switches the key.

**TTL / retention:**

- BlogDB-level cache: controlled via env `FP_APCU_ENTRY_TTL` (seconds), default 600.
- Entry-level cache: stored with a fixed 600s TTL.
- As with other signature-based caches, the key changes with `mtime/size`; TTL mainly limits  
  how long older versions remain in APCu.

**Impact:**  
High. This is the primary hot cache for the entry stream and single entry view.

---

### 2.3 Comment Index Cache – `fp:comments:list:…`

**Prefix:** `fp:comments:list:<entryId>:<dirMtime>`  
**File:** `fp-includes/core/core.comment.php`  

**What is cached:**

- The **comment ID list** for a given entry (used for full comment listing).

**Invalidation:**

- Based on the **comment directory mtime** (`filemtime($comment_dir)`).
- Any change that touches the comment directory (add/delete comment file) updates the mtime and therefore rotates the key.

**TTL:**

- `@apcu_set($key, $list, 300);` (5 minutes).

**Impact:**  
Medium–High. Speeds up full comment listing on popular entries.

---

### 2.3a Comment Count Cache – `fp:comments:count:…`

**Prefix:** `fp:comments:count:<entryId>:<dirMtime>`  
**File:** `fp-includes/core/core.comment.php`  

**What is cached:**

- The **comment count only** (no list building), intended for entry streams where templates  
  typically only need `{comments}` as a number.

**Invalidation:**

- Same as the list cache: **comment directory mtime** (`filemtime($comment_dir)`).

**TTL:**

- `@apcu_set(..., 300);` (5 minutes).

**On-disk cache (second-level cache / APCu-off fallback):**

- Cache file (via `comment_count_cachefile()` and `CACHE_DIR`): `fp-content/cache/<entryId>.txt`
- Format: `<dirMtime>:<count>`
- Behavior:
  - If the disk cache hits and APCu is enabled, the value is written back into APCu (seeding).
  - If APCu is disabled, the disk cache is the primary mechanism to avoid repeated directory scans.
- Invalidation:
  - Automatically cold when `dirMtime` changes.
  - Additionally deleted on comment save/delete hooks (`unlink()`), forcing a rescan.

**Impact:**  
High on stream pages with many entries. Avoids directory scans and avoids building/sorting full comment lists when only the count is required.

---

### 2.4 Static Page List – `fp:statics:list:…`

**Prefix:** `fp:statics:list:<mtime>:<size>:<natFlag>`  
**File:** `fp-includes/core/core.static.php`  

**What is cached:**

- The list of static page IDs, with optional “natural” sorting.

**Invalidation:**

- Directory mtime + size combined into a signature.

**TTL:**

- `@apcu_set($key, $list, 600);` (10 minutes).

**Impact:**  
Medium. Reduces repeated scanning of `fp-content/static/`.

---

### 2.5 Category Caches – `fp:cats:*`

**Prefixes:**

- `fp:cats:list:<mtime>:<size>`  
- `fp:cats:encoded:<mtime>:<size>`

**File:** `fp-includes/core/core.entry.php`  

**What is cached:**

- `fp:cats:list:`  
  - Structured category tree from `categories.txt`.
- `fp:cats:encoded:`  
  - Encoded categories mapping.

**Invalidation:**

- mtime + size of the categories file.

**TTL:**

- Both use `@apcu_set(..., 600);` (10 minutes).

**Impact:**  
Medium. All category link generation benefits, but categories change rarely.

---

### 2.6 Language Cache – `fp:lang:…`

**Prefix:** `fp:lang:<md5(real_path|mtime|locale)>`  
**File:** `fp-includes/core/core.language.php`  

**What is cached:**

- The fully loaded `$lang` array for one language file and locale.

**Invalidation:**

- File mtime and locale; key changes when language files or locale change.

**TTL:**

- `@apcu_set($ckey, $lang, 0);` (no expiry).

**Locale setup performance note:**

- `set_locale()` now tries the configured locale/charset candidates directly with PHP `setlocale()`.
- Language selection, FlatPress language files, configured output charset, and the existing `setlocale()` fallback sequence remain independent of APCu.

**Impact:**  
High on multi-language setups; otherwise medium.

---

### 2.7 INI Parser Cache – `fp:ini:…`

**Prefix:** `fp:ini:<sha1(real_path|mtime|size)>`  
**File:** `fp-plugins/seometataginfo/inc/class.iniparser.php`  

**What is cached:**

- Parsed INI config arrays used by the SEO MetaTag Info plugin.

**Invalidation:**

- mtime + size of the INI file (tokens are only included in the key when APCu is active).

**TTL:**

- `@apcu_set(..., 600);` (10 minutes).

**Impact:**  
Low–Medium. Useful for avoiding repeated disk + parsing cost on high-traffic sites using this plugin.

---

### 2.8 Network/Environment Caches – `fp:https:v2:*`, `fp:net:in_cidrs:*`

**Prefixes:**

- `fp:https:v2:<sha1(env_state)>`
- `fp:net:in_cidrs:<ip>|<sha1(sorted_unique_cidrs)>`

**File:** `fp-includes/core/core.connection.php`  

**What is cached:**

- `fp:https:v2:` – Result of “are we effectively running under HTTPS?” considering proxies and server vars.
- `fp:net:in_cidrs:` – Boolean results of “IP is in these CIDRs” checks.

**Invalidation:**

- HTTPS detection:
  - TTL is controlled via env `FP_HTTPS_CACHE_TTL` (seconds), default 120.
  - Key is a SHA1 over a JSON-encoded “env state” including relevant `$_SERVER` values and the normalized trusted proxy list.
- CIDR membership checks:
  - TTL is fixed at 3600 seconds.
  - Key includes the IP plus a SHA1 over the normalized CIDR list.

**Impact:**  
Low–Medium. Reduces repeated environment probing, especially under reverse proxies.

---

### 2.9 Base URL Config Cache – `fp:config:settings:*`

**Prefix:** `fp:config:settings:<sha1(abs_settings_conf_path)>`  
**File:** `fp-includes/core/core.connection.php`

**What is cached:**

- Parsed contents of `fp-content/config/settings.conf.php` (the full config array).
- The normalized canonical base URL string from `general['www']`, used to define `BLOG_BASEURL`.
- The parsed config is also exposed as `$GLOBALS['EARLY_FP_CONFIG']` so later `config_load()` can reuse it without re-reading the file.

**Invalidation:**

- On each request, FlatPress computes a lightweight file signature (`mtime:size`) via `stat()`.
- Cache entries store the same signature; if it differs, the file is reloaded and the cache is refreshed.
- This means configuration changes become effective **immediately on the next request**, even when APCu is enabled.

**TTL:**

- Stored with TTL `3600` seconds (1 hour) as a memory-pressure hint only; signature validation ensures freshness.

**Impact:**

Medium. Saves an include+parse of `settings.conf.php` on every request when `general['www']` is used to define `BLOG_BASEURL`, especially noticeable on shared hosting and under PHP-FPM with APCu enabled.

---


### 2.10 Main Stream Offset Anchors – `fp:fpdb:offset-anchors:v1:*`

**Logical APCu prefix:** `fp:fpdb:offset-anchors:v1:<sha1(index_signature)>`  
**Files:**

- `fp-includes/core/core.fpdb.class.php`
- `fp-includes/core/core.entry.php`
- `fp-includes/core/core.fileio.php`
- `fp-includes/core/core.apcu.php`

**Purpose:**

- Accelerates deep pages of the normal chronological entry stream.
- Stores only structural B+ tree navigation data:
  - absolute entry-index offset,
  - key at that offset,
  - preceding key required to preserve `FPDB_Query::getPrevId()` semantics.
- It does **not** cache entry content, rendered HTML, comments, language output, or Smarty results.

**Why absolute offsets are used:**

`admin.php?p=config` can change `general.maxentries`, and callers can pass an explicit `count` or `start`.  
The cache therefore never binds an anchor to a page number.

Examples:

- `page=9, count=5` -> absolute start offset `40`
- `page=3, count=20` -> absolute start offset `40`
- `start=40` -> absolute start offset `40`

All three can safely reuse the same structural anchor.

**Eligibility:**

The optimization is deliberately limited to the unfiltered main entry stream:

- main index/category `0`,
- no entry-ID query,
- no random query,
- no year/month/day filter,
- no category filter,
- no exclude index,
- positive `count`,
- start offset at or beyond the current anchor step.

All other query forms continue through the original B+ tree walker unchanged.

**Anchor spacing:**

- Default fixed step: `128`.
- Optional host override: `FP_FPDB_ANCHOR_STEP`.
- Accepted range is clamped to `16..4096`.
- For very large indexes the effective step grows automatically to keep the fixed anchor map at roughly no more than `8192` anchors.
- Optional switch `FP_FPDB_OFFSET_ANCHORS=0|false|off|no` disables the optimization completely.

**Validity signature:**

The main-index signature contains:

- cache schema version,
- `%%fpdb-index-generation.tmp` generation token,
- main index file mtime,
- main index file size,
- current B+ tree length.

`entry_index_generation_bump()` rotates the token only when the ordered main-index key set changes:

- a new main-index key is inserted,
- a main-index key is deleted.

Title-only and category-only edits do not move stream offsets and therefore do not force an unnecessary generation change.

The token is written through `io_write_file()`. If it cannot be created or safely rotated, offset-anchor reuse is disabled and FlatPress stays on the original walker.

**Backend and fallback order:**

```text
request-local anchor map
          |
          v
APCu namespaced hot cache
          |
          v
fp-content/cache/%%fpdb-offset-anchors-v1.json
          |
          v
original B+ tree walker
```

The pipes are intentionally aligned to make the fallback direction explicit.

- APCu is optional and uses the normal `is_apcu_on()`, `apcu_get()`, and `apcu_set()` wrappers.
- APCu TTL: `3600` seconds.
- The JSON fallback is regenerable runtime data and is written atomically through `io_write_file()`.
- Cache-file reads use `io_load_file_uncached()` because the anchor layer already provides its own request/APCu hierarchy and generation validation.
- If APCu is missing, full, disabled, or rejects a store, the file fallback remains available.
- If the file cache is missing, corrupt, read-only, or cannot be written, the B+ tree walker remains authoritative.
- Cache failures can reduce performance only; they must not change query results or entry write success.

**Warm-up behavior:**

- The first deep request for a new index generation still walks the original B+ tree and learns fixed-step anchors on that path.
- Following requests can start at the nearest verified anchor and walk only the remaining distance.
- Shallow pages below the anchor step do not perform generation or anchor-file lookups.

**Write and concurrency safety:**

- The B+ tree format and its insert/delete/rebalance implementation are unchanged.
- An anchor is accepted only when its key is still the exact walker key and the index signature remains unchanged after positioning.
- Newly learned anchors are persisted only if the signature still matches immediately before the write.
- The generation/cache files are not sources of truth and are safe to delete during cache maintenance.

**Language / charset safety:**

Offset anchors contain only index keys and offsets. They are independent of:

- FlatPress language,
- output charset,
- date/month translations,
- theme,
- Smarty,
- rendered content.

Changing language or charset therefore does not require anchor invalidation.

**Impact:**  
Low on shallow pages by design; high on deep entry-stream pagination of large blogs after warm-up.

---


## 3. Plugin and Template Infrastructure Caches

### 3.1 Plugin Discovery and Status – `fp:plugin:*`, `fp:plugins:*`

**Prefixes:**

- `fp:plugin:dir:v2:<md5(PLUGINS_DIR)>:<id>`
- `fp:plugin:url:v2:<md5(BLOG_BASEURL)>:<md5(PLUGINS_DIR)>:<id>`
- `fp:plugin:exists:v2:<md5(meta)>`
- `fp:plugins:list:v1:<dirMtime>`
- `fp:plugins:enableds:list:v1:<confMtime>`
- `fp:plugins:checkfile:v2:<md5(directory|file|mtimes)>`

**File:** `fp-includes/core/core.plugins.php`  

**What is cached:**

- Plugin directory paths and URLs.
- Existence checks for individual plugin files.
- Full plugin list for a given `PLUGINS_DIR`.
- “Enabled plugins” list based on config file mtime.

**Invalidation:**

- Changes to `PLUGINS_DIR` or plugin files (mtimes).
- Changes to the enabled-plugins config file.

**TTL:**

- These are stored with TTL `0` (no expiry), but keys incorporate mtimes, so they rotate when files change.

**Impact:**  
Medium. Reduces disk access when admin UI or core repeatedly scan plugin structures.

---

### 3.2 Smarty Plugin Index – `fp:spi:*`

**Prefix:** `fp:spi:<sha1(dir|token)>`  
**File:** `fp-includes/core/core.smarty.php`  

**What is cached:**

- “Smarty Plugin Index” mapping of plugin types to plugin files (function/modifier/block/etc.) for a given plugin directory.

**How it works (APCu + disk layer):**

- Token: `filemtime($dir)` (directory mtime).
- APCu (hot cache):
  - `@apcu_set('fp:spi:' . sha1($dir . '|' . $token), $map, 300);`
- Disk index (fallback / warm-start):
  - File: `CACHE_DIR/smarty_plugins.index.php` (typically `fp-content/cache/smarty_plugins.index.php`)
  - Stores `['_token' => <token>, 'map' => <map>]` as a PHP return payload.
  - Used on APCu misses and when APCu is off; regenerated when token mismatches.

**Impact:**  
Medium. Helps keep Smarty’s plugin lookup fast under load.

---

## 4. Feature-Specific Caches

### 4.1 Search Cache – `fp:search:rev` and `fp:search:v…`

**Prefixes:**

- `fp:search:rev`
- `fp:search:v<rev>:<hash>`

**File:** `search.php`  

**What is cached:**

- `fp:search:rev` – A small integer “content revision” summarizing CONTENT_DIR and key subdirectories.
- `fp:search:v…` – Search result data structures (IDs, snippets, etc.) based on normalized parameters and the current rev.

**Invalidation:**

- `fp:search:rev` recalculated via filesystem scan; cached for 5 seconds.
- `fp:search:v…` is tied to the current rev; any rev change automatically makes existing cache keys cold.

**TTL:**

- `fp:search:rev` – `@apcu_set(..., 5);`  
- Search result entries – `@apcu_set($key, $val, 900);` (15 minutes), used only when APCu is on.

**PrettyURLs dependency:**  
No. The search cache works on IDs and meta; URLs are built at render time.

**Impact:**  
Medium. Helps under repeated, identical search requests.

---

### 4.2 BBCode Plugin Caches – `fp:bbcode:*`

**Prefixes:**

- `fp:bbcode:parser:v1:<md5(source_files|options)>`
- `fp:bbcode:commentparser:v1:<...>`
- `fp:bbcode:imginfo:v1:<md5(path|mtime|size)>`
- `fp:bbcode:iptc:v1:<md5(path|mtime|size)>`
- `fp:bbcode:obf:v1:<md5(mode|string)>`
- `fp:bbcode:toolbar:images:v1:<md5(IMAGES_DIR|mtime)>`
- `fp:bbcode:toolbar:galleries:v1:<md5(IMAGES_DIR|mtime)>`
- `fp:bbcode:toolbar:attachs:v1:<md5(UPLOADS_DIR|mtime)>`

**File:** `fp-plugins/bbcode/plugin.bbcode.php`  

**What is cached:**

- Parser instances for entries and comments.  
  (Base objects are stored in APCu and **cloned** on retrieval to avoid shared-state mutation.)
- Image metadata (`getimagesize()`, IPTC, etc.).
- Obfuscated email strings.
- Toolbar dropdown lists (images, galleries, attachments) for the editor.

**Invalidation:**

- File mtimes (parser sources, image dirs, upload dirs).
- TTLs:
  - Parsers and toolbars: typically 300 seconds.
  - Image info: 600 seconds.
  - Obfuscation: 7200 seconds (2 hours), but only cached for modes 1/2 and short inputs (≤ 256 chars).  
    Mode 3 (random) is intentionally not cached.

**Impact:**  
Medium. Particularly useful when image metadata is frequently queried.

---

### 4.3 Archives Plugin Caches – `fp:archives:*`

**Prefixes:**

- `fp:archives:v`
- `fp:archives:list:vN:loc-<sha1(lang|charset)>:<sig>`
- `fp:archives:html:vN:loc-<sha1(lang|charset)>:<sig>`

**File:** `fp-plugins/archives/plugin.archives.php`  

**What is cached:**

- `fp:archives:list:` – Structured month/year list.
- `fp:archives:html:` – Pre-rendered HTML for the archive widget, including links.

Both store BLOG_BASEURL as a placeholder `%BLOG_BASEURL%` and expand it on read.

**Language and charset isolation:**

- The APCu key contains `sha1(lowercase(lang) . '|' . lowercase(charset))`.
- The request-local archive cache uses the same locale/charset context.
- Switching the configured FlatPress language or output charset therefore cannot reuse a previously rendered month list from another locale.
- This matters because archive labels contain translated month names from the active FlatPress language data.

**Invalidation:**

- Namespaced by `fp:archives:v` (integer version in APCu).
- `plugin_archives_cache_bump()`:
  - `apcu_incr('fp:archives:v', 1, …)`; falls back to `@apcu_set('fp:archives:v', 1);`.
  - Bound to comment/save hooks and (since FlatPress 1.5 „Stringendo“) also invoked from PrettyURLs when the URL mode changes.

**PrettyURLs dependency:**

- Yes. The cached HTML and URL paths depend on the current PrettyURLs mode.
- As of FlatPress `1.5 „Stringendo“`, PrettyURLs’ settings save (`onsubmit()`) calls `plugin_archives_cache_bump()` to keep this cache consistent with URL mode changes.

**Impact:**  
Medium. Reduces repeated archive computation and template rendering.

---

### 4.4 Calendar Plugin Caches – `fp:calendar:v` and `calendar:…`

**Prefixes:**

- `fp:calendar:v`
- `calendar:<sha1(normalized_params)>:vN`

**File:** `fp-plugins/calendar/plugin.calendar.php`  

**What is cached:**

- Full HTML calendar widget for a given (year, month, language, first-day-of-week).
- Day/month links constructed via `get_day_link()` and `get_month_link()`.

**Cache layers:**

- APCu (optional hot cache):
  - Stored with `@apcu_set($key, $html, max(60, $ttl));`
- File fallback (always written, used when APCu is off or misses):
  - File: `CACHE_DIR/calendar-<sha1(key)>.html` (typically `fp-content/cache/calendar-*.html`)
  - Freshness check uses the file mtime: valid if `(time() - filemtime) <= $ttl`.
- On cache hits (APCu or file), the HTML is passed through `plugin_calendar_cache_expand_baseurl()`,  
  which replaces `%BLOG_BASEURL%` placeholders with the current `BLOG_BASEURL` (safe no-op if absent).

**Invalidation:**

- `plugin_calendar_cache_ns()` uses `fp:calendar:v` to generate a `:vN` suffix.
- `plugin_calendar_cache_bump()`:
  - If APCu is on: `apcu_incr('fp:calendar:v', …)` + fallback initialization.
  - Always purges the file fallback: deletes `calendar-*.html` in `CACHE_DIR`.
  - Bound to entry publish/edit/delete hooks and invoked from PrettyURLs when the URL mode changes.

**PrettyURLs dependency:**

- Yes. Calendar cell links and navigation (prev/next month) depend on the PrettyURLs mode.
- PrettyURLs bumps this cache when its mode changes.

**Impact:**  
Medium–High in widgets-heavy setups. Calendar widgets are often present on every page.

---

### 4.5 Storage Plugin Caches – `fp:storage:*`

**Prefixes:**

- `fp:storage:v`
- `fp:storage:aggregate:vN`
- `fp:storage:dirsize:<channel>[:nth][:ncc]:vN`
- `fp:storage:quota:vN`
- `fp:storage:dirsize:root:<sha1(root)>:vN` (FlatPress folder total size)

**File:** `fp-plugins/storage/plugin.storage.php`  

**What is cached:**

- Storage aggregates (entry/comment counters and byte sizes, plus the optional Top-10 comment list).
- Directory size computations:
  - Per storage channel (e.g. `images`, `attachs`), optionally excluding `.thumbs` (`:nth`)  
    and/or `.captions.conf` (`:ncc`).
  - Total FlatPress folder size (recursive sum of `BASE_DIR`).
- Quota information (if configured / detectable).

**Cache layers and TTLs:**

- Aggregate:
  - APCu: `fp:storage:aggregate:vN` with TTL 300s.
  - File fallback: `fp-content/cache/storage.aggregate.json` with TTL 120s (based on file mtime).
- Dir size per channel:
  - APCu: `fp:storage:dirsize:<channel>[:nth][:ncc]:vN` with default TTL 120s (values include `ts`).
  - File fallback: `fp-content/cache/storage.dirsize.<channel>[.nth][.ncc].json` with TTL = `$ttl`.
- Quota:
  - APCu: `fp:storage:quota:vN` with default TTL 3600s (payload includes `ts`).
  - File fallback: `fp-content/cache/storage.quota.json` with TTL = `$ttl` (based on file mtime).
- FlatPress folder total size:
  - APCu: `fp:storage:dirsize:root:<sha1(root)>:vN` with TTL 120s.
  - File fallback: `fp-content/cache/storage.dirsize.json` with TTL 120s.

**Cold rebuild path:**

- The Storage panel resolves the aggregate cache and FlatPress-root-size cache before traversing the filesystem.
- If both caches are cold and `CONTENT_DIR` is below `BASE_DIR`, one `RecursiveDirectoryIterator` traversal of the FlatPress root computes both result sets.
- If only the aggregate is cold, only `CONTENT_DIR` is streamed.
- If only the root-size cache is cold, only the root-size metric is collected.
- Entry and comment files are counted and sized directly from `SplFileInfo`; the Storage scanner does **not** call `bdb_parse_entry()` and does not materialize a complete `fs_pathlister` array.
- Symbolic links are not followed. `RecursiveIteratorIterator::CATCH_GET_CHILD` and per-file exception handling preserve best-effort behavior on restrictive/shared-host filesystems.

**Invalidation:**

- APCu data is namespaced through `plugin_storage_cache_ns()` and `fp:storage:v`.
- The namespace value is memoized for the current request so an in-progress scan remains tied to the generation it started with. After a successful bump, the mutation request refreshes its own memoized namespace immediately.
- `plugin_storage_cache_bump()` is bound only to post-success hooks:
  - `entry_saved`
  - `entry_deleted`
  - `comment_saved`
  - `comment_deleted`
- The file fallbacks `storage.aggregate.json` and `storage.dirsize.json` are purged on every bump **even when APCu is unavailable**.
- When APCu is available, the same bump increments `fp:storage:v`, invalidating aggregate, root-size, channel-size and quota APCu keys by generation.
- Channel JSON fallbacks (`storage.dirsize.*.json`) and `storage.quota.json` are not purged by content mutations; they retain their own TTL-based refresh because entry/comment writes do not change image/attachment content or hosting quota limits directly.

**Write safety:**

- Before an APCu-backed cold scan is persisted, the Storage plugin rechecks the generation it started with. If a concurrent successful mutation bumped `fp:storage:v`, that scan result is used only for the current response and is **not** written into the newer APCu generation or its JSON fallback.
- Aggregate and root-size JSON fallbacks are written through `io_write_file()`, i.e. temp-file + rename atomic replacement.
- Cache write or cache purge failures are best effort and never participate in the success/failure decision of entry or comment writes.
- APCu remains optional. With APCu disabled, the same Storage metrics and invalidation semantics continue to work through the JSON fallbacks; cross-request generation detection is not available without a shared in-memory backend, so the short file TTL remains the final stale-data bound for external/concurrent changes that bypass normal hooks.

**Impact:**  
Medium–High for large installations in the Storage admin panel. The warm path stays cache-backed, while a complete aggregate/root cache miss avoids per-entry parsing, full path-list materialization and a second full FlatPress-tree traversal.

---

### 4.6 PrettyURLs Caches – `prettyurls:*`

**Prefixes:**

- `prettyurls:<auto_detection_result_key>`
- `prettyurls:auto:v3:g<gen>:<md5(flags)>` (internal logical key; stored as `'prettyurls:' . $key`)

**File:** `fp-plugins/prettyurls/plugin.prettyurls.php`  

**What is cached:**

- Results of automatic PrettyURLs mode detection (3=Pretty, 1=PATH_INFO, 2=GET).
- Results of `auto_mode_detect_preview()` for index.php preview.

**Invalidation:**

- Namespaced via plugin option `apcu_gen`:
  - On relevant config changes (`mode` change or successful `.htaccess` regeneration), PrettyURLs increments `apcu_gen`.
  - Effective key becomes `prettyurls:auto:v3:g<gen>:…` so old results go cold.

**Impact:**  
Medium. Avoids repeated expensive environment probing when switching between URL modes.

---

### 4.7 SEO MetaTag Info `og:image` Caches – `seometa:og:imageinfo:*`, `seometa:og:imagebin:*`

**Prefixes (logical plugin keys):**

- `seometa:og:imageinfo:v1:<md5(abs_path|mtime|size)>`
- `seometa:og:imagebin:v1:<md5(abs_path|type|mtime|size|target_width x target_height)>`

**File:** `fp-plugins/seometataginfo/plugin.seometataginfo.php`

**Effective APCu keys:**

- Both keys are written through FlatPress' `apcu_get()` / `apcu_set()` wrappers.
- When APCu is enabled, they therefore live under the current instance namespace as:
  - `fp:<NS>:seometa:og:imageinfo:v1:...`
  - `fp:<NS>:seometa:og:imagebin:v1:...`

**What is cached:**

- `seometa:og:imageinfo:*`
  - validated source image metadata for the currently selected `og:image` source:
    - absolute/relative source path is resolved outside the cache key,
    - width,
    - height,
    - image type (`IMAGETYPE_JPEG` / `IMAGETYPE_PNG`),
    - MIME type.
  - This avoids repeated `getimagesize()` calls for the same preview/fallback image.
- `seometa:og:imagebin:*`
  - the already transformed Open Graph image body generated via GD for the dynamic `1200x630` endpoint,
  - plus its MIME type.
  - The cached binary stays in APCu only; FlatPress does **not** write resized images to disk.

**Source selection and fallback order:**

- Active style preview image (for example `preview.png`, `preview.jpg`, `preview.jpeg`)
- active theme preview image
- bundled plugin fallback image (`fp-plugins/seometataginfo/imgs/og-image.png`)

**Invalidation:**

- `seometa:og:imageinfo:*`
  - key includes absolute path, source file `mtime`, and source file size,
  - therefore any replacement or edit of the source image automatically yields a new cache key on the next request.
- `seometa:og:imagebin:*`
  - key includes absolute path, image type, source `mtime`, source size, and target dimensions,
  - therefore source image updates or future target size changes automatically invalidate prior transformed variants.
- Both caches are also naturally invalidated by FlatPress APCu namespace rotation (`FP_APCU_NS`), for example after an APCu clear/reset.

**TTL:**

- `seometa:og:imageinfo:*`
  - controlled by `SEOMETA_OGIMAGE_INFO_APCU_TTL`,
  - default: `max(60, (int)($_ENV['FP_APCU_IO_TTL'] ?? 3600))`.
- `seometa:og:imagebin:*`
  - controlled by `SEOMETA_OGIMAGE_BINARY_APCU_TTL`,
  - default: `max(60, (int)($_ENV['FP_APCU_IO_TTL'] ?? 3600))`.
- Binary cache entries are additionally size-limited by `SEOMETA_OGIMAGE_BINARY_APCU_MAX_BYTES` (default `1572864` bytes). Oversized transformed images are served normally but are **not** inserted into APCu.

**Fallback behavior when APCu or GD is unavailable:**

- Without APCu:
  - the plugin still uses request-local static caching for image metadata during the current request,
  - but follow-up requests must re-evaluate metadata and re-render the dynamic image when needed.
- Without GD:
  - the plugin does not attempt in-memory transformation,
  - `og:image` falls back to the original preview image or the bundled plugin fallback image.

**Relationship to early config loading:**

- The plugin reads runtime configuration through `seometataginfo_get_runtime_config()`.
- This first uses the normal `$fp_config` array.
- If that is not available yet, it falls back to `$GLOBALS['EARLY_FP_CONFIG']`, which is prepared in `fp-includes/core/core.connection.php`.
- This is **not** an APCu cache key of its own, but it complements the APCu-backed image caches by avoiding an unnecessary second config load during early execution paths.

**Impact:**  
Medium–High for repeated social crawler hits and repeated requests to the dynamic `og:image` endpoint. The biggest win is avoiding repeated GD resampling/encoding work once a transformed image body is already present in APCu.

---

### 4.8 Smarty Block Fragment Cache – `fp:smarty:block:<group>:<hash>`

**Files:**

- `fp-includes/fp-smartyplugins/block.cache.php`
- `fp-interface/themes/leggero/widgetstop.tpl`
- `fp-interface/themes/leggero/widgetsbottom.tpl`
- `fp-plugins/categories/tpls/widget.tpl`
- `fp-interface/sharedtpls/rss.tpl`
- `fp-interface/sharedtpls/atom.tpl`
- `fp-interface/sharedtpls/comment-rss.tpl`
- `fp-interface/sharedtpls/comment-atom.tpl`
- `fp-plugins/lastcomments/tpls/plugin.lastcomments-feed.tpl`
- `fp-plugins/lastcomments/tpls/plugin.lastcomments-atom.tpl`

**What is cached:**

FlatPress' custom Smarty `{cache}{/cache}` block stores rendered fragment payloads in APCu **when APCu is available**. The payload mirrors the filesystem fallback and contains:

- creation timestamp
- TTL
- template timestamp
- rendered fragment content

**Logical key format:**

- `smarty:block:<group>:<hash>`

Because all wrapper calls pass through `apcu_key()`, the effective APCu key is:

- `fp:<NS>:smarty:block:<group>:<hash>`

**Current fragment groups:**

- `theme-leggero`
- `widget-categories`
- `feeds-main`
- `feeds-comments`
- `feeds-lastcomments`

**Invalidation:**

- APCu TTL expiry via `apcu_store(..., $ttl)`
- template source timestamp changing
- malformed payloads
- natural APCu eviction under memory pressure

**Fallback behavior:**

If APCu is not available for the current request, the same fragment cache automatically falls back to the existing file-backed storage below `CACHE_DIR/smarty-block-cache/...`.

**Impact:**  
Medium–High. For hot widget and feed fragments this removes the filesystem read/write step entirely and replaces it with an in-memory APCu fetch/store round-trip.

---

### 4.9 Mastodon Plugin Instance Snapshot Cache – `mastodon:instance_document:<sha1(instance_url)>`

**File:** `fp-plugins/mastodon/plugin.mastodon.php`  

The Mastodon plugin uses APCu as a short-lived hot cache for the compact instance-information document derived from `GET /api/v2/instance`.

**Logical key:**

- `mastodon:instance_document:<sha1(instance_url)>`

**Effective APCu key:**

- Stored through the FlatPress APCu wrappers, so when APCu is enabled the runtime key becomes  
  `fp:<NS>:mastodon:instance_document:<sha1(instance_url)>`

**Payload:**

A compacted subset of the Mastodon instance document, for example:

- `domain`, `title`, `version`, `api_versions`
- selected `configuration` limits such as status length, media limits, and URL metadata
- registration/contact/usage details used by the Mastodon admin diagnostics table

The plugin intentionally stores only the compacted document, not the full raw `/api/v2/instance` response.

**When it is written / refreshed:**

- When a saved instance snapshot from plugin options is decoded and seeded back into APCu
- After a successful manual instance-information refresh in the Mastodon admin area
- After a successful live fetch through `plugin_mastodon_instance_document()`
- After saving Mastodon plugin options that already contain a valid instance snapshot

**Read path / cache layering:**

`plugin_mastodon_instance_document()` resolves data in this order:

1. request-local runtime cache
2. persisted snapshot from Mastodon plugin options
3. APCu hot cache
4. live `GET /api/v2/instance` request (only when network access is allowed)

This means APCu is **not** the authoritative storage layer. The durable source is the plugin configuration snapshot; APCu only reduces repeated decode/network work on later requests.

**TTL / invalidation:**

- APCu TTL: `900` seconds (15 minutes)
- Explicit invalidation when the configured Mastodon `instance_url` changes
- Explicit deletion when the current configuration no longer contains a valid saved instance snapshot
- Naturally invalidated by APCu eviction/reset and FlatPress namespace rotation

**Fallback behavior:**

- Without APCu, the plugin still works by using the saved snapshot in plugin options and, if allowed, a live network fetch.
- Without a saved snapshot and with network access disabled, the admin table shows that no cached instance information is currently available.

**Impact:**  
Low–Medium. This cache does not affect every request, but it avoids repeated `/api/v2/instance` fetches and repeated snapshot decoding in the Mastodon admin diagnostics and in capability/limit lookups that reuse instance metadata.

---

### 4.10 Mastodon Scheduler Summary and Synchronization Guards - `fp:io:*`, `mastodon:sync_guard:*`

**File:** `fp-plugins/mastodon/plugin.mastodon.php`  

For normal request-time scheduler checks, the plugin uses a compact file-backed summary:

- `fp-content/plugin_mastodon/scheduler-state.json`

This summary is read through the FlatPress/APCu-capable file I/O path, so small valid reads can benefit from the core `fp:io:*` hotcache instead of decoding the large full `state.json` mapping file on every frontend request.

**Logical keys:**

- `mastodon:sync_guard:content:v1`
- `mastodon:sync_guard:deletion:v1`

The scheduler summary has no dedicated Mastodon APCu key. When cached, it is cached by the central file I/O layer using the normal `fp:io:*` key shape for the concrete file, mtime, and size.

**Effective dedicated Mastodon APCu keys:**

The guard keys are stored through the Mastodon APCu wrapper, which calls the FlatPress APCu wrappers. With APCu enabled, the runtime keys therefore become:

- `fp:<NS>:mastodon:sync_guard:content:v1`
- `fp:<NS>:mastodon:sync_guard:deletion:v1`

**What is cached:**

- `scheduler-state.json` through core `fp:io:*`
  - compact scheduler metadata such as last run timestamps, deletion follow-up timing, last error/status information, and current statistics,
  - no large entry/comment/media mapping arrays,
  - rebuilt from `state.json` when missing, invalid, or stale.
- `mastodon:sync_guard:content:v1`
  - a small guard payload for non-forced scheduled content synchronization.
- `mastodon:sync_guard:deletion:v1`
  - a small guard payload for non-forced scheduled deletion synchronization.

**TTL / invalidation:**

- Scheduler summary file reads follow the central file I/O APCu settings, especially `FP_APCU_IO_TTL` and `FP_APCU_IO_MAX_BYTES`.
- Synchronization guard TTL: `PLUGIN_MASTODON_COOLDOWN_TTL`, currently `300` seconds (5 minutes).
- The scheduler summary is refreshed after successful full-state writes and is invalidated by file mtime/size changes through the core I/O cache key.
- Guard entries expire naturally after the cooldown window.
- Guard entries can also be cleared explicitly by `plugin_mastodon_sync_guard_clear()`.
- APCu-backed entries are naturally invalidated by APCu eviction/reset and FlatPress namespace rotation.

**File-backed companion layer:**

- Durable state remains `fp-content/plugin_mastodon/state.json`.
- Scheduler summary remains `fp-content/plugin_mastodon/scheduler-state.json`.
- The cooldown guard also has a file companion: `fp-content/plugin_mastodon/sync.guard.json`.
- `sync.log` is append-only with rotation and is not an APCu cache. High-volume skip messages are aggregated before they are flushed to the log.
- The file guard is intentionally tiny and TTL-based. It protects hosts where APCu is unavailable or where PHP workers do not share the same APCu pool.

**Runtime behavior:**

- Forced/manual synchronization bypasses the cooldown guards.
- Non-forced scheduled content and deletion synchronization check the APCu guard first.
- If APCu misses, the plugin checks `sync.guard.json`.
- If the file guard is still active, it is seeded back into APCu for the remaining TTL.
- This prevents repeated expensive Mastodon synchronization attempts on every or every second web request when `state.json` cannot be persisted reliably.

**Impact:**  
Medium for Mastodon-enabled sites under unfavorable hosting conditions. The APCu entries are small: scheduler summary reads use the shared file hotcache, while the dedicated guards prevent repeated media/status work during the cooldown window.

---

### 4.11 Media Manager Usage Index – `mediamanager:usage-index:v1`

**Logical APCu key:** `mediamanager:usage-index:v1`  
**Effective APCu key:** `fp:<NS>:mediamanager:usage-index:v1`  
**Files:**

- `fp-plugins/mediamanager/inc/usage-index.php`
- `fp-plugins/mediamanager/panels/panel.mediamanager.file.php`

**Portable file-backed layer:**

- `fp-content/cache/mediamanager.useindex.json`
- `fp-content/cache/mediamanager.useindex.lock`
- `fp-content/cache/mediamanager.useindex.dirty`

The JSON index is a **regenerable runtime artifact**. Entry files remain the source of truth. It replaces the historical Media Manager `usecount` array in `settings.conf.php`; after the first successful index persistence, the obsolete `usecount` plugin option is removed best-effort.

**What is cached:**

- Direct image-reference counts per normalized relative image path.
- Explicit `[gallery]` reference counts per gallery.
- Per-gallery counts for entries that use the gallery either explicitly or through an image in that gallery.
- Direct-image/explicit-gallery overlap counts, so one entry is never double-counted when it references both.
- Compact per-entry media contributions. These make `entry_saved` and `entry_deleted` updates idempotent, including concurrent cases where a recovery rebuild completes before a waiting hook updates the cache.

**Build and update behavior:**

- Missing, corrupt, or dirty state triggers one locked full rebuild.
- The rebuild performs one lightweight `FPDB_Query` over the entry index with `fullparse => false`.
- Each returned entry ID is then loaded exactly once with `entry_parse()`. This reads the entry content without constructing `FPDB_CommentList` objects or changing the historical `FPDB_QueryParams` semantics in core.
- Successful entry writes update only the changed entry through the `entry_saved` hook.
- Successful entry deletions remove only the deleted contribution through the `entry_deleted` hook.
- Preview and other `content_save_pre` paths no longer invalidate the complete Media Manager usage state.
- If no Media Manager index exists yet, entry saves do not force an O(N) rebuild; the first Media Manager request builds it once.

**Write and concurrency safety:**

- Writers serialize with `flock()` on `mediamanager.useindex.lock`.
- The JSON payload is written with core `io_write_file()` using the same-directory temp-file/rename path and `fsync` when available.
- A tokenized dirty marker prevents readers from trusting an index while a committed entry change is being folded in.
- One writer never clears a newer writer's dirty token.
- Per-entry contributions make repeated or reordered commit application idempotent.
- If locking or persistence is unavailable, entry saving still succeeds. The Media Manager falls back to a correct in-memory rebuild and never treats the cache as authoritative content.

**APCu layer:**

- The plugin uses only the central helpers from `core.apcu.php`:
  - `apcu_get()`
  - `apcu_set()`
  - `apcu_delete_key()`
- The logical key is automatically isolated by `apcu_key()` under the current FlatPress instance namespace.
- APCu stores the complete validated index together with the file signature.
- File signature fields are device, inode, mtime, ctime, size, and the index generation read from the small JSON header.
- A signature mismatch causes a JSON reload, so the file-backed layer remains authoritative across workers or hosting setups that do not share one APCu pool.
- TTL: `600` seconds. The TTL limits retention; file signature validation provides freshness.

**Media-count semantics:**

For an image inside a gallery, the displayed use count is the set union of:

- entries that reference that exact image, and
- entries that explicitly reference its gallery.

The stored overlap counter subtracts entries present in both sets. Gallery use counts likewise count each entry once whether the gallery is referenced explicitly or only through an image inside it. This preserves the existing Media Manager UX while allowing newly added files in an already-used gallery to show the correct count without rescanning every entry.

**Fallback behavior:**

- APCu unavailable: JSON index remains the normal portable cache.
- JSON index missing/corrupt/dirty: one locked full rebuild from entry files.
- JSON persistence unavailable: correct in-memory rebuild for the current request.
- Cache failure never blocks or rolls back an entry write.

**Impact:**  
High on large FlatPress installations. Normal entry edits change the usage index in O(media references of one entry) instead of invalidating an O(all entries) rebuild. APCu reduces JSON decoding and disk reads further but is not required for correctness or performance scaling.

---

## 5. Miscellaneous and Meta Caches

### 5.1 Instance Namespace Bootstrap – `fp:ns:*`

**Prefix:** `fp:ns:<sha1(base_path)>`  
**File:** `fp-includes/core/core.apcu.php`  

- Holds the current APCu namespace ID (`FP_APCU_NS`) for this FlatPress instance.
- Written once per namespace rotation; rarely changes.

**Impact:**  
Low individually, but foundational for all namespaced cores.

---

### 5.2 HTTPS and CIDR Probes – `fp:https:v2:*`, `fp:net:in_cidrs:*`

Already covered in section 2.8, but worth summarizing:

- **Usage:** optimize repeated checks for HTTPS detection and IP-in-subnet matching.
- **Scope:** no URLs or content; purely environment-level booleans.
- **Impact:** Low individually.

---

### 5.3 Maintain Panel APCu Tools

**File:** `admin/panels/maintain/admin.maintain.php`  

- Uses APCu for:
  - Checking availability (`is_apcu_on()`).
  - Reading APCu statistics via `apcu_cache_info(false)` (user cache) and `apcu_sma_info()`.
  - Clearing FlatPress-related keys via the `apcu_clear_fp` action.

**Clear behavior (`apcu_clear_fp`):**

- Targets keys matching `^fp:` (pattern `/^fp:/`).
- Best-effort strategies (depending on host capabilities):
  - `APCUIterator` + batched `apcu_delete()` (preferred).
  - `apcu_cache_info(false)` enumeration + batched `apcu_delete()`.
  - Last resort: `apcu_clear_cache()` to clear the entire APCu **user cache** when iteration/introspection APIs are unavailable.
- Note: This action does **not** explicitly delete non-`fp:` caches like `prettyurls:*`.

**Impact:**  
Admin-only, but critical for debugging and manual cache reset.

---

### 5.4 File Fallback Layers (Calendar, Storage, Mastodon)

Some features use a **dual-layer cache** (APCu + file fallback) to stay fast even when APCu is unavailable.

- **Calendar** (`fp-plugins/calendar/plugin.calendar.php`)
  - File cache: `CACHE_DIR/calendar-<sha1(key)>.html`
  - Invalidation: version bump (`fp:calendar:v`) plus file purge (`calendar-*.html`) on `plugin_calendar_cache_bump()`.

- **Storage** (`fp-plugins/storage/plugin.storage.php`)
  - Aggregate JSON: `fp-content/cache/storage.aggregate.json`.
  - FlatPress root-size JSON: `fp-content/cache/storage.dirsize.json`.
  - Both files are purged by `plugin_storage_cache_bump()` after successful entry/comment mutations, regardless of APCu availability.
  - Additional JSON fallbacks:
    - `storage.dirsize.*.json`, `storage.quota.json`  
      (TTL-based; refreshed on demand; not purged by entry/comment cache bumps).
  - Storage JSON writes use the core atomic `io_write_file()` path.

- **Mastodon** (`fp-plugins/mastodon/plugin.mastodon.php`)
  - Durable state: `fp-content/plugin_mastodon/state.json`.
  - The former full-state APCu fallback has been removed; no large Mastodon mapping state is stored in APCu.
  - Compact scheduler summary: `fp-content/plugin_mastodon/scheduler-state.json`, read through the central APCu-capable `fp:io:*` file I/O path.
  - File cooldown guard: `fp-content/plugin_mastodon/sync.guard.json`.
  - APCu cooldown guards: `mastodon:sync_guard:content:v1` and `mastodon:sync_guard:deletion:v1`, each with TTL 300s.
  - Rotated append-only log: `fp-content/plugin_mastodon/sync.log` plus retained rotated log files; repeated skip events are aggregated before logging.
  - The file guard is used as a small shared fallback when APCu is unavailable or not shared between workers.

---

### 5.5 Setup Hide Result Cache – `admin:setup_hide_report`

**Prefix:** `fp:<NS>:admin:setup_hide_report` via `apcu_key()`  
**File:** `admin.php`

**Purpose:**

- Minimizes filesystem I/O on `admin.php` by caching the outcome of the “hide setup entry points” routine.
- Avoids repeated `is_file()` / `is_dir()` checks and, more importantly, avoids repeating expensive recursive permission walks on hosts with slow storage.

**What is cached:**

A small report array:

- `ts` (int): Unix timestamp when the report was produced
- `state` (`"ok"` / `"fail"`): whether setup entry points are hidden successfully
- `errors` (string[]): remaining visible entry points (e.g. `setup.php`, `setup/`)

**When it is written:**

- Only after setup completion (`LOCKFILE` exists).
- Written on cache miss; on cache hit the routine returns early (no I/O).

**TTL / invalidation:**

- `"ok"` state: default **86400s (1 day)**  
  - configurable via `FP_APCU_SETUP_HIDE_TTL_OK` or env `FP_APCU_SETUP_HIDE_TTL_OK`  
  - `0` means “no expiry” (until APCu eviction/restart)
- `"fail"` state: default **300s (5 minutes)**, minimum **30s**  
  - configurable via `FP_APCU_SETUP_HIDE_TTL_FAIL` or env `FP_APCU_SETUP_HIDE_TTL_FAIL`
- No explicit invalidation hook; the cache is naturally cleared on APCu reset and can be cleared manually from the Maintain panel.

**Enable/disable:**

- Enabled automatically when APCu is on (`is_apcu_on()`).
- Can be disabled via:
  - `FP_APCU_SETUP_HIDE_CACHE` (constant)
  - `FP_APCU_SETUP_HIDE_CACHE` (env)

**Impact:**  
Low–Medium (admin-only), but noticeable on slow disks or network filesystems where repeated stat/chmod recursion is costly.

---

## 6. Weighting and Relevance Summary

The following table summarizes each logical cache group:

| Area                        | Key prefixes (logical)                                                              | Depends on PrettyURLs?   | Invalidation driver                                                | Approx. impact          |
|-----------------------------|-------------------------------------------------------------------------------------|--------------------------|--------------------------------------------------------------------|-------------------------|
| APCu core helpers           | `fp:ns:*`, `apcu_ns()`, `apcu_key()`                                                | No                       | N/A (meta only)                                                    | High (foundational)     |
| Base URL Config             | `fp:config:settings:*`                                                              | No                       | File mtime/size via `stat()`, TTL 1h                               | Medium                  |
| File I/O                    | `fp:io:*`                                                                           | No                       | File mtime/size, TTL (default 1h)                                  | High                    |
| Entries                     | `fp:entry:parsed:*`                                                                 | No                       | Entry file mtime/size                                              | High                    |
| Stream offset anchors       | `fp:fpdb:offset-anchors:v1:*`                                                       | No                       | Main-index generation + mtime/size/length                          | High on deep pages      |
| Comments                    | `fp:comments:list:*`, `fp:comments:count:*`                                         | No                       | Comment dir mtime, TTL 300s (APCu) + file fallback                 | Medium–High             |
| Static pages                | `fp:statics:list:*`                                                                 | No                       | Static dir mtime/size, TTL 600s                                    | Medium                  |
| Categories                  | `fp:cats:list:*`, `fp:cats:encoded:*`                                               | No                       | Categories file mtime/size, TTL 600s                               | Medium                  |
| Language                    | `fp:lang:*`                                                                         | No                       | Language file mtime/size, locale                                   | Medium–High             |
| INI parsing (SEO plugin)    | `fp:ini:*`                                                                          | No                       | INI file mtime/size                                                | Low–Medium              |
| SEO `og:image` (SEO plugin) | `fp:seometa:og:imageinfo:*`, `seometa:og:imagebin:*`                                | No                       | Source path/type/mtime/size, target size, TTL                      | Medium–High             |
| Smarty block fragments      | `fp:smarty:block:*`                                                                 | No                       | TTL, template timestamp, APCu eviction or file fallback            | Medium–High             |
| HTTPS/IP env                | `fp:https:v2:*`, `fp:net:in_cidrs:*`                                                | No                       | TTL (≈3600s) and local process                                     | Low–Medium              |
| Plugin discovery            | `fp:plugin:*`, `fp:plugins:*`                                                       | No                       | Plugin dir/config mtimes                                           | Medium                  |
| Smarty plugin index         | `fp:spi:*`                                                                          | No                       | Dir+token hash, TTL 300s                                           | Medium                  |
| Search                      | `fp:search:rev`, `fp:search:v*`                                                     | No                       | Content rev + TTL (5s / 900s)                                      | Medium                  |
| BBCode                      | `fp:bbcode:*`                                                                       | No                       | Parser/img/meta mtimes, TTL 300–7200s                              | Medium–High             |
| Archives                    | `fp:archives:v`, `fp:archives:list*`, `fp:archives:html*`                           | **Yes**                  | Generation/PrettyURLs bump + language/charset key                  | Medium                  |
| Calendar                    | `fp:calendar:v`, `calendar:*:vN`                                                    | **Yes**                  | `plugin_calendar_cache_bump()` + PrettyURLs bump                   | Medium–High             |
| Storage plugin              | `fp:storage:v`, `fp:storage:aggregate*`, `fp:storage:dirsize*`, `fp:storage:quota*` | No                       | Post-success hooks + generation bump + JSON purge/TTL              | Medium–High             |
| Mastodon instance snapshot  | `fp:mastodon:instance_document:<sha1(instance_url)>`                                | No                       | TTL 900s, `instance_url` change, snapshot refresh                  | Low–Medium              |
| Media Manager usage index   | `mediamanager:usage-index:v1`                                                       | No                       | `entry_saved`/`entry_deleted`, dirty recovery                      | High (large sites)      |
| Mastodon scheduler summary  | core `fp:io:*` for `scheduler-state.json`                                           | No                       | File mtime/size via core I/O, rebuilt from `state.json` when stale | Medium                  |
| Mastodon sync guards        | `fp:mastodon:sync_guard:content:v1`, `fp:mastodon:sync_guard:deletion:v1`           | No                       | TTL 300s + file guard `sync.guard.json`                            | Medium                  |
| Admin setup hide            | `fp:admin:setup_hide_report`                                                        | No                       | TTL (ok 86400s, fail 300s) + manual APCu clear                     | Low–Medium (admin only) |
| PrettyURLs auto-detection   | `prettyurls:*`, `prettyurls:auto:v3:g*:*`                                           | No (but influences URLs) | `apcu_gen` bump on mode/.htaccess changes                          | Medium                  |
| Maintain panel tools        | Uses APCu to clear and inspect all keys, no own namespace                           | No                       | Manual admin action                                                | N/A (admin only)        |

---

## 7. Reference: All APCu Key Prefixes

For completeness, the following logical prefixes are used by FlatPress `1.6.dev`:

- `fp:archives:html`
- `fp:archives:list`
- `fp:archives:v`
- `fp:bbcode:commentparser:v1:`
- `fp:bbcode:imginfo:v1:`
- `fp:bbcode:iptc:v1:`
- `fp:bbcode:obf:v1:`
- `fp:bbcode:parser:v1:`
- `fp:bbcode:toolbar:attachs:v1:`
- `fp:bbcode:toolbar:galleries:v1:`
- `fp:bbcode:toolbar:images:v1:`
- `fp:calendar:v`
- `fp:cats:encoded:`
- `fp:cats:list:`
- `fp:comments:list:`
- `fp:comments:count:`
- `fp:config:settings:`
- `fp:entry:parsed:`
- `fp:fpdb:offset-anchors:v1:`
- `fp:https:v2:`
- `fp:ini:`
- `fp:io:`
- `fp:lang:`
- `fp:net:in_cidrs:`
- `fp:mediamanager:usage-index:v1`
- `fp:mastodon:instance_document:`
- `fp:mastodon:sync_guard:content:v1`
- `fp:mastodon:sync_guard:deletion:v1`
- `fp:ns:`
- `fp:plugin:dir:v2:`
- `fp:plugin:exists:v2:`
- `fp:plugin:info:v2:`
- `fp:plugin:url:v2:`
- `fp:plugins:checkfile:v2:`
- `fp:plugins:enableds:list:v1:`
- `fp:plugins:list:v1:`
- `fp:search:rev`
- `fp:search:v`
- `fp:seometa:og:imageinfo:v1:`
- `fp:seometa:og:imagebin:v1:`
- `fp:spi:`
- `fp:smarty:block:`
- `fp:statics:list:`
- `fp:storage:aggregate`
- `fp:storage:dirsize:`
- `fp:storage:dirsize:root:`
- `fp:storage:quota`
- `fp:storage:v`
- `fp:admin:setup_hide_report`
- `prettyurls:`
- `prettyurls:auto:v3:g`
- `calendar:` (calendar cache key before namespacing via `fp:calendar:v` suffix)

All of these are either:

- Wrapped through `apcu_key()` and thus effectively live under `fp:<NS>:`; or
- Intentionally global/“self-namespaced” (e.g. `calendar:`, `prettyurls:`) with their own versioning and hashing schemes.

---

## 8. FlatPress 1.5 RC1 Burnout Report

These two measurements show a direct comparison of the performance differences on a shared web host.

- [FlatPress on PHP8.5 with OPCache without APCu cache](https://fraenkiman.github.io/flatpress/docs/FlatPress-Burnout-Report/bench-20260111-125627-report-without-APCu.html)
- [FlatPress on PHP8.5 with OPCache with APCu cache](https://fraenkiman.github.io/flatpress/docs/FlatPress-Burnout-Report/bench-20260111-124607-report-with-APCu.html)

<i>Many thanks to Lubomír Ludvík, who provided me with a test instance on milesweb.com.</i>

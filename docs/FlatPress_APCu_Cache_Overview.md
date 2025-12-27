# APCu Cache Overview in FlatPress 1.5 „Stringendo“

This document summarizes all APCu-backed caches used in FlatPress `1.5 „Stringendo“`, their purpose, lifetime, invalidation strategy, and rough performance impact.

It is intended for maintainers, plugin authors, and performance testing.

---

## 1. Core APCu Helpers and Namespacing

### 1.1 `is_apcu_on()`

**File:** `fp-includes/core/core.fileio.php`  
**Purpose:** Central runtime check if APCu should be used.

- Verifies:
  - `apcu_fetch()` exists.
  - APCu is enabled (`apcu_enabled()` or `apc.enabled`).
  - In CLI/phpdbg, `apc.enable_cli` must be true.
- Result is memoized per request in a static variable.

**Impact:**  
High. Every APCu-aware function uses this as a guard, so misconfiguration here would disable all caches.

---

### 1.2 `apcu_ns()` and `apcu_key()`

**File:** `fp-includes/core/core.fileio.php`  

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

**File:** `fp-includes/core/core.fileio.php`  

- `apcu_get($key, &$ok = null)`  
- `apcu_set($key, $val, $ttl = 120)`  
- `apcu_incr($key, $step = 1, &$success = null)`

All three:

- Short-circuit if `is_apcu_on()` is false.
- Normalize keys through `apcu_key()`, except where raw `apcu_*` functions are used deliberately.
- Respect TTL (`$ttl`) for `apcu_set`.

**Impact:**  
High. Most APCu usage flows through these helpers.

---

## 2. Core Data Caches

### 2.1 File Content Cache – `fp:io:…`

**Prefix:** `fp:io:<filename>:<mtime>:<size>`  
**File:** `fp-includes/core/core.fileio.php`  

- Caches results of `io_load_file()` on top-level core files.
- Key includes:
  - Absolute filename.
  - File mtime.
  - File size.
- Invalidation:
  - Automatic when the file changes (mtime/size).
  - No global version bump needed.
- TTL:
  - Controlled via env `FP_APCU_IO_TTL` (seconds). Default 3600s (1h).
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

### 2.3a Comment Count Cache – `fp:comments:count:…` (new)

**Prefix:** `fp:comments:count:<entryId>:<dirMtime>`  
**File:** `fp-includes/core/core.comment.php`  

**What is cached:**

- The **comment count only** (no list building), intended for entry streams where templates typically only need `{comments}` as a number.

**Invalidation:**

- Same as the list cache: **comment directory mtime** (`filemtime($comment_dir)`).

**TTL:**

- `@apcu_set(..., 300);` (5 minutes).

**File fallback (APCu off / small hosts):**

- Cache file: `fp-content/cache/<entryId>.txt`
- Format: `<dirMtime>:<count>`
- Invalidation:
  - Automatically cold when `dirMtime` changes.
  - Additionally deleted on comment save/delete hooks (`unlink()`).

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

**Impact:**  
High on multi-language setups; otherwise medium.

---

### 2.7 INI Parser Cache – `fp:ini:…`

**Prefix:** `fp:ini:<sha1(real_path|mtime|size)>`  
**File:** `fp-plugins/seometataginfo/inc/class.iniparser.php`  

**What is cached:**

- Parsed INI config arrays used by the SEO MetaTag Info plugin.

**Invalidation:**

- mtime + size of the INI file.

**Impact:**  
Low–Medium. Useful for avoiding repeated disk + parsing cost on high-traffic sites using this plugin.

---

### 2.8 Network/Environment Caches – `fp:https:v2:*`, `fp:net:in_cidrs:*`

**Prefixes:**

- `fp:https:v2:<sha1(env_state)>`
- `fp:net:in_cidrs:<sha1(input)>`

**File:** `fp-includes/core/core.connection.php`  

**What is cached:**

- `fp:https:v2:` – Result of “are we effectively running under HTTPS?” considering proxies and server vars.
- `fp:net:in_cidrs:` – Boolean results of “IP is in these CIDRs” checks.

**Invalidation:**

- TTL-based (typically 3600 seconds for HTTPS detection; shared `ttl` for CIDR checks).
- No PrettyURLs dependency.

**Impact:**  
Low–Medium. Reduces repeated environment probing, especially under reverse proxies.

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

- “Smarty Plugin Index” mapping of templates to directories/plugins.

**Invalidation:**

- Key incorporates directory and a token including relevant mtimes.
- TTL: `@apcu_set(..., 300);` (5 minutes).

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
- `fp:bbcode:obf:v1:<md5(string)>`
- `fp:bbcode:toolbar:images:v1:<md5(IMAGES_DIR|mtime)>`
- `fp:bbcode:toolbar:galleries:v1:<md5(IMAGES_DIR|mtime)>`
- `fp:bbcode:toolbar:attachs:v1:<md5(UPLOADS_DIR|mtime)>`

**File:** `fp-plugins/bbcode/plugin.bbcode.php`  

**What is cached:**

- Parser instances for entries and comments.
- Image metadata (`getimagesize()`, IPTC, etc.).
- Obfuscated email strings.
- Toolbar dropdown lists (images, galleries, attachments) for the editor.

**Invalidation:**

- File mtimes (parser sources, image dirs, upload dirs).
- TTLs:
  - Parsers and toolbars: typically 300 seconds.
  - Image info: 600 seconds.
  - Obfuscation: up to 7200 seconds (2 hours).

**Impact:**  
Medium. Particularly useful when image metadata is frequently queried.

---

### 4.3 Archives Plugin Caches – `fp:archives:*`

**Prefixes:**

- `fp:archives:v`
- `fp:archives:list:vN:<sig>`
- `fp:archives:html:vN:<sig>`

**File:** `fp-plugins/archives/plugin.archives.php`  

**What is cached:**

- `fp:archives:list:` – Structured month/year list.
- `fp:archives:html:` – Pre-rendered HTML for the archive widget, including links.

Both store BLOG_BASEURL as a placeholder `%BLOG_BASEURL%` and expand it on read.

**Invalidation:**

- Namespaced by `fp:archives:v` (integer version in APCu).
- `plugin_archives_cache_bump()`:
  - `apcu_incr('fp:archives:v', 1, …)`; falls back to `@apcu_set('fp:archives:v', 1);`.
  - Bound to comment/save hooks and (since FlatPress 1.5 „Stringendo“) also invoked from PrettyURLs when the URL mode changes.

**PrettyURLs dependency:**

- Yes. The cached HTML and URL paths depend on the current PrettyURLs mode.
- As of `flatpress-master`, PrettyURLs’ settings save (`onsubmit()`) calls `plugin_archives_cache_bump()` to keep this cache consistent with URL mode changes.

**Impact:**  
Medium. Reduces repeated archive computation and template rendering.

---

### 4.4 Calendar Plugin Caches – `fp:calendar:v` and `calendar:…`

**Prefixes:**

- `fp:calendar:v`
- `calendar:<sha1(normalized_params)>:vN`

**File:** `fp-plugins/calendar/plugin.calendar.php`  

**What is cached:**

- Full HTML calendar widget for a given (year, month, language, first_day), containing links constructed via `get_day_link()` and `get_month_link()`.

**Invalidation:**

- `plugin_calendar_cache_ns()` uses `fp:calendar:v` to generate a `:vN` suffix.
- `plugin_calendar_cache_bump()`:
  - `apcu_incr('fp:calendar:v', …)` + fallback and file cache cleanup.
  - Bound to entry publish/edit/delete hooks and (since FlatPress 1.5 „Stringendo“) invoked from PrettyURLs when the URL mode changes.

**PrettyURLs dependency:**

- Yes. Calendar cell links and navigation (prev/next month) depend on the PrettyURLs mode.
- PrettyURLs now explicitly bumps this cache when its mode changes.

**Impact:**  
Medium–High in widgets-heavy setups. Calendar widgets are often present on every page.

---

### 4.5 Storage Plugin Caches – `fp:storage:*`

**Prefixes:**

- `fp:storage:v`
- `fp:storage:aggregate:vN`
- `fp:storage:dirsize:<path|meta>`
- `fp:storage:dirsize:v1:<signature>`
- `fp:storage:quota`
- (Commented in code: `fp:storage:quota:vN` as conceptual name.)

**File:** `fp-plugins/storage/plugin.storage.php`  

**What is cached:**

- Storage aggregates (entries count, comments count, top lists).
- Directory size computations.
- Quota information (if configured).

**Invalidation:**

- Namespaced via `plugin_storage_cache_ns()` reading `fp:storage:v`.
- Version bump via `apcu_incr('fp:storage:v', …)` when storage data is recomputed (plugin-internal).

**Impact:**  
Low–Medium. Mostly relevant for admin and diagnostics; not on every frontend request.

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

## 5. Miscellaneous and Meta Caches

### 5.1 Instance Namespace Bootstrap – `fp:ns:*`

**Prefix:** `fp:ns:<sha1(base_path)>`  
**File:** `fp-includes/core/core.fileio.php`  

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
  - Reading APCu statistics via `apcu_cache_info()` and `apcu_sma_info()`.
  - Clearing FlatPress-related keys (usually all keys starting with `fp:` and `prettyurls:`) via the `apcu_clear_fp` action.

**Impact:**  
Admin-only, but critical for debugging and manual cache reset.

---

### 5.4 Calendar and Archives File Fallback

Both Calendar and Archives plugins also maintain a file-based cache (e.g. `calendar-*.html`, `storage.aggregate.json`) used when APCu is off.

- The APCu and file caches share the same invalidation strategy (version bumps + mtimes).
- On APCu-enabled hosts, the file fallback is rarely hit.

---

## 6. Weighting and Relevance Summary

The following table summarizes each logical cache group:

| Area                         | Key prefixes (logical)                                                               | Depends on PrettyURLs?   | Invalidation driver                                  | Approx. impact      |
|------------------------------|--------------------------------------------------------------------------------------|--------------------------|------------------------------------------------------|---------------------|
| APCu core helpers            | `fp:ns:*`, `apcu_ns()`, `apcu_key()`                                                 | No                       | N/A (meta only)                                      | High (foundational) |
| File I/O                     | `fp:io:*`                                                                            | No                       | File mtime/size, TTL (default 1h)                    | High                |
| Entries                      | `fp:entry:parsed:*`                                                                  | No                       | Entry file mtime/size                                | High                |
| Comments                     | `fp:comments:list:*`, `fp:comments:count:*`                                          | No                       | Comment dir mtime, TTL 300s (APCu) + file fallback   | Medium–High         |
| Static pages                 | `fp:statics:list:*`                                                                  | No                       | Static dir mtime/size, TTL 600s                      | Medium              |
| Categories                   | `fp:cats:list:*`, `fp:cats:encoded:*`                                                | No                       | Categories file mtime/size, TTL 600s                 | Medium              |
| Language                     | `fp:lang:*`                                                                          | No                       | Language file mtime/size, locale                     | Medium–High         |
| INI parsing (SEO plugin)     | `fp:ini:*`                                                                           | No                       | INI file mtime/size                                  | Low–Medium          |
| HTTPS/IP env                 | `fp:https:v2:*`, `fp:net:in_cidrs:*`                                                 | No                       | TTL (≈3600s) and local process                       | Low–Medium          |
| Plugin discovery             | `fp:plugin:*`, `fp:plugins:*`                                                        | No                       | Plugin dir/config mtimes                             | Medium              |
| Smarty plugin index          | `fp:spi:*`                                                                           | No                       | Dir+token hash, TTL 300s                             | Medium              |
| Search                       | `fp:search:rev`, `fp:search:v*`                                                      | No                       | Content rev + TTL (5s / 900s)                        | Medium              |
| BBCode                       | `fp:bbcode:*`                                                                        | No                       | Parser/img/meta mtimes, TTL 300–7200s                | Medium–High         |
| Archives                     | `fp:archives:v`, `fp:archives:list*`, `fp:archives:html*`                            | **Yes**                  | `plugin_archives_cache_bump()` + PrettyURLs bump     | Medium              |
| Calendar                     | `fp:calendar:v`, `calendar:*:vN`                                                     | **Yes**                  | `plugin_calendar_cache_bump()` + PrettyURLs bump     | Medium–High         |
| Storage plugin               | `fp:storage:v`, `fp:storage:aggregate*`, `fp:storage:dirsize*`, `fp:storage:quota*`  | No                       | Storage rescan + TTL                                 | Low–Medium          |
| PrettyURLs auto-detection    | `prettyurls:*`, `prettyurls:auto:v3:g*:*`                                            | No (but influences URLs) | `apcu_gen` bump on mode/.htaccess changes            | Medium              |
| Maintain panel tools         | Uses APCu to clear and inspect all keys, no own namespace                            | No                       | Manual admin action                                  | N/A (admin only)    |

---

## 7. Reference: All APCu Key Prefixes

For completeness, the following logical prefixes are used by FlatPress `1.5 „Stringendo“`:

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
- `fp:entry:parsed:`
- `fp:https:v2:`
- `fp:ini:`
- `fp:io:`
- `fp:lang:`
- `fp:net:in_cidrs:`
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
- `fp:spi:`
- `fp:statics:list:`
- `fp:storage:aggregate`
- `fp:storage:dirsize:`
- `fp:storage:dirsize:v1:`
- `fp:storage:quota`
- `fp:storage:v`
- `prettyurls:`
- `prettyurls:auto:v3:g`
- `calendar:` (calendar cache key before namespacing via `fp:calendar:v` suffix)

All of these are either:

- Wrapped through `apcu_key()` and thus effectively live under `fp:<NS>:`; or
- Intentionally global/“self-namespaced” (e.g. `calendar:`, `prettyurls:`) with their own versioning and hashing schemes.


# Metadata Storage and Administration

## 1. Storage layout

SEO metadata is stored under:

```text
CONTENT_DIR . seometa/
```

Constants:

```text
SEOMETA_DIR
├── default/       -> SEOMETA_DEFAULT_DIR
├── entries/       -> SEOMETA_ENTRY_DIR
├── statics/       -> SEOMETA_STATIC_DIR
├── categories/    -> SEOMETA_CATEGORY_DIR
├── tags/          -> SEOMETA_TAG_DIR
└── archives/      -> SEOMETA_ARCHIVE_DIR
```

Typical file names:

| Context | File |
|---|---|
| default | `default/metatags.ini` |
| entry | `entries/<entry-id>_metatags.ini` |
| static page | `statics/<page-id>_metatags.ini` |
| blog page | `statics/blog_metatags.ini` |
| contact | `statics/contact_metatags.ini` |
| category | `categories/cat-<id>_metatags.ini` |
| tag | `tags/tag-<tag>_metatags.ini` |
| archive | `archives/archive-20YY[-MM][-DD]_metatags.ini` |

These files are persistent instance data and should not be treated like regenerable Smarty compile/cache files.

## 2. INI schema

The plugin writes a `[meta]` section containing:

```ini
[meta]
description=
keywords=
noindex=0
nofollow=0
noarchive=0
nosnippet=0
```

`seometataginfo_ensure_metafile()` creates missing page-specific files by copying the default metadata content when possible. If even the default file cannot be produced, the head callback still emits basic metadata from in-memory defaults.

## 3. `iniParser`

`inc/class.iniparser.php` is a local INI reader/writer with request and optional APCu caching.

### Read path

- resolves the filename with `realpath()` when possible;
- checks existence;
- when APCu is active, includes mtime and file size in cache identity;
- uses a request-local static cache;
- optionally uses APCu;
- opens the file in binary mode;
- uses a shared lock where possible;
- falls back to `io_load_file()` if direct locked reading is unavailable;
- tolerates a UTF-8 BOM on the first line;
- parses section headers and `key=value` lines.

### Write path

`save()`:

- writes to a unique temporary file;
- requires a writable destination directory;
- uses a side lock file when possible;
- exclusively locks the temporary file;
- flushes and closes it;
- renames the temporary file into place;
- applies `FILE_PERMISSIONS`;
- clears stat cache.

Most current SEO save paths use `io_write_file()` directly; `iniParser::save()` remains part of the helper API.

## 4. Entry/static editor integration

When the admin-panel compatibility condition is met, `plugin_seometatags_entry` is instantiated.

Hooks:

| Hook | Method |
|---|---|
| `simple_metatag_info` | `simple()` |
| `admin_entry_write_onsave` | `post()` |
| `admin_entry_write_onsavecontinue` | `post()` |
| `publish_post` | `save()` registered by `post()` |
| `title_save_pre` | `save_static()` |

`simple()` renders:

- description text field;
- keyword text field;
- `noindex`;
- `nofollow`;
- `noarchive`;
- `nosnippet`;
- hidden metadata-file path.

## 5. Field sanitization

`sanitizeSeoField()`:

1. requires a string;
2. HTML-decodes the input;
3. removes HTML-like tags;
4. removes encoded angle brackets;
5. removes inline `on...="..."` / `on...='...'` patterns;
6. filters remaining characters through the plugin's Unicode/extra-character allow pattern;
7. trims the result.

The character regex is built from Unicode categories for letters, numbers, punctuation, spaces, and marks plus an explicit extra-character list.

Checkbox values default to `"0"` when absent.

## 6. Save routing

`do_save()` chooses the destination as follows:

- existing non-default metadata path → overwrite that metadata file;
- new entry (`$_REQUEST['p'] === 'entry'`) → derive entry ID from timestamp;
- new static page (`$_REQUEST['p'] === 'static'`) → use request page ID.

The plugin writes the `[meta]` section using `io_write_file()`.

## 7. Head metadata output

`output_metatags()` can emit:

### Standard meta

- `meta name="title"` when enabled;
- `meta name="description"`;
- `meta name="keywords"`;
- `meta name="robots"` when at least one restriction is active;
- `meta name="author"` on single entries.

### Open Graph

When Open Graph is enabled:

- `og:title`
- `og:image`
- `og:image:url`
- optional `og:image:secure_url`
- optional `og:image:type`
- `og:image:alt`
- optional `og:image:width`
- optional `og:image:height`
- `og:description`
- `og:type`
- `og:locale`
- `og:site_name`
- `og:url`

`og:image:alt` is not stored in the SEO INI schema. It is derived per request from the selected content image:

- explicit BBCode `title` for `[img]` / `[photoswipeimage]`;
- persisted Gallery caption for the exact selected gallery file;
- otherwise `$fp_config['general']['title']`.

Gallery captions remain gallery data managed through `gallery_write_captions()` / `gallery_read_captions()`, not SEO metadata under `CONTENT_DIR . seometa/`.

Single entries use `og:type=article`; other contexts use `og:type=website`.

### Article metadata for single entries

When available:

- `article:author`
- `article:published_time`
- `article:section`
- repeated `article:tag`

## 8. Article section

`seometataginfo_get_article_section()` uses the first valid non-zero category assigned to the current entry.

`seometataginfo_get_category_path()` walks category parents and emits a readable hierarchy joined by `/`.

Loop protection is implemented with a `seen` set. Invalid/non-numeric category IDs are ignored.

## 9. Article tags

`seometataginfo_get_article_tags()` is active only when:

- current context is a single entry;
- `tag` is listed in `$fp_plugins`;
- `plugin_exists('tag')` does not report the plugin missing.

It prefers the tag plugin's `entryTags($entry_id)` API. If needed, it can use the entry content and `tag_list()` while preserving/restoring the tag plugin's previous internal tag state.

Returned tags are trimmed, empty entries removed, and duplicates collapsed.

## 10. Published time

`seometataginfo_get_article_published_time()`:

1. prefers the entry's numeric `date` through `date_iso8601()`;
2. falls back to converting the entry ID with `date_id_to_iso8601()`.

## 11. Canonical URL

`seometataginfo_build_public_url()` prefers the configured base URL (`general.www`) over a URL assembled solely from the request host.

It combines that base with the current request path/query and then calls `seometataginfo_strip_tracking_params()`.

Stripped tracking keys currently include:

- `fbclid`
- `gclid`
- `yclid`
- `mc_cid`
- `mc_eid`
- `igshid`
- `_hsenc`
- `_hsmi`
- `utm_source`
- `utm_medium`
- `utm_campaign`
- `utm_term`
- `utm_content`

When `SEOMETA_HIDECOMMENTS` is enabled, comment-path/comment-fragment portions are additionally removed by `output_metatags()` before canonical/`og:url` output.

## 12. Context titles

`makePageTitle()` adds localized labels for:

- contact;
- static home;
- blog home;
- blog page;
- tag;
- archive year/month/day;
- category;
- search;
- paginated page number.

It is registered on `wp_title` only when `SEOMETA_GEN_TITLE` is enabled.

## 13. Smarty variables

At `init` priority 0:

```text
seo_desc = ""
seo_keywords = ""
```

At `entry_block` priority 0, `seometataginfo_assign_entry_vars($id)` resolves per-entry metadata and assigns those two variables.

A request-local memo and the plugin's global entry cache reduce duplicate INI reads.

This interface is intentionally simple and works with the template-level `assign()` API used by both supported Smarty branches.

## 14. robots.txt administration

The admin panel is registered as:

```text
plugin / seometataginfo
```

The template is:

```text
tpls/admin.plugin.seometataginfo.tpl
```

The implementation uses:

```text
$_SERVER['DOCUMENT_ROOT'] . '/robots.txt'
```

Important operational consequences:

- `DOCUMENT_ROOT` must be available;
- the document root must be writable to create the file;
- an existing `robots.txt` must be writable to edit it;
- this path is host-root based, not FlatPress-subdirectory based.

The default generated content disallows FlatPress admin/login/setup and selected internal paths, and adds a sitemap line when `.htaccess` exists.

## 15. Migration

Migration is disabled by default:

```php
SEOMETA_MIGRATE_DATA = false
```

When explicitly enabled, `migrate_old()` moves/copies older metadata layouts into the current SEO directories and creates default/blog/contact metadata where missing.

Because migration changes persistent instance data, it should not be enabled casually or left enabled indefinitely.

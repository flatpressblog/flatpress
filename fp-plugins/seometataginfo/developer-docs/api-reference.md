# API Reference

This reference documents functions/classes in the SEO Meta Tag Info plugin as implemented in the current snapshot. Most functions are plugin-internal; FlatPress does not enforce visibility boundaries for global PHP functions, so names should still be treated as part of the integration surface.

## 1. Main plugin: metadata and flags

### `seometataginfo_flag($name)`

Reads a defined feature-switch constant and returns its boolean value.

### `output_metatags($seo_desc, $seo_keywords, $seo_noindex, $seo_nofollow, $seo_noarchive, $seo_nosnippet)`

Central normal-page metadata emitter. Builds Open Graph image metadata, title, description, keywords, robots, article metadata, canonical URL, and Open Graph URL/type/locale/site name.

### `makePageTitle($title, $sep)`

Adds context-aware localized title information.

### `plugin_seometataginfo_head($file_meta)`

`wp_head` entry point. Resolves metadata source for the current context and calls the output pipeline.

### `plugin_seometataginfo_init()`

`init` entry point. Serves dynamic OG image requests or registers the title filter for normal page requests.

## 2. Query and URL helpers

### `seometataginfo_append_query_args($url, $args)`

Adds RFC3986 query parameters while preserving a URL fragment.

### `seometataginfo_normalize_query_parameter_name($name)`

Removes repeated leading literal `amp;` fragments from a query parameter name.

### `seometataginfo_get_query_parameter($name)`

Returns:

```text
present
valid
value
```

Exact key has precedence; escaped aliases are fallback names.

### `seometataginfo_url_join($baseUrl, $path)`

Joins a base URL and relative path with one slash.

### `seometataginfo_build_public_url($baseUrl)`

Builds canonical/public URL from configured base URL plus request path/query, then strips tracking parameters.

### `seometataginfo_strip_tracking_params($url)`

Removes the plugin's fixed set of known tracking query keys and rebuilds the URL.

## 3. Runtime configuration and APCu

### `seometataginfo_get_runtime_config()`

Returns `$fp_config`, `EARLY_FP_CONFIG`, or an empty array.

### `seometataginfo_apcu_available()`

Returns true only when FlatPress's APCu helper exists and reports APCu enabled.

### `seometataginfo_normalize_cache_ttl($ttl, $defaultTtl)`

Ensures a positive TTL, with fallback to default and finally 3600 seconds.

### `seometataginfo_get_og_image_binary_apcu_max_bytes()`

Returns the configured binary cache limit, clamped so negative values become zero.

## 4. Generic image metadata/caching

### `seometataginfo_build_image_info(...)`

Builds the shared image-info array containing relative/absolute path, URL, MIME, dimensions, type, mtime, and file size.

### `seometataginfo_get_image_info_apcu_key($absolutePath, $mtime, $sizeBytes)`

Builds APCu key for source metadata.

### `seometataginfo_get_og_image_binary_cache_key($imageInfo, $targetWidth, $targetHeight)`

Builds APCu key for transformed image bytes.

### `seometataginfo_get_cached_og_image_binary(...)`

Reads a cached transformed image body/MIME pair.

### `seometataginfo_store_og_image_binary_cache(...)`

Stores transformed bytes if APCu is active and the configured size limit allows it.

### `seometataginfo_send_image_content_headers($mime, $contentLength)`

Sets MIME and content length, removing a stale content-length header first when possible.

### `seometataginfo_output_binary_image(...)`

Sends cache/content headers and echoes cached/generated image bytes.

### `seometataginfo_capture_image_resource_output($image, $imageInfo)`

Captures JPEG/PNG encoder output from a GD image handle.

### `seometataginfo_get_supported_image_info($baseUrl, $relativePath)`

Validates a local JPEG/PNG theme/fallback source and returns cached metadata.

## 5. OG fallback source

### `seometataginfo_get_theme_preview_image_info($baseUrl)`

Checks style-specific and theme-level preview candidates in this order:

```text
theme/style/preview.png
theme/style/preview.jpg
theme/style/preview.jpeg
theme/preview.png
theme/preview.jpg
theme/preview.jpeg
```

### `seometataginfo_get_plugin_fallback_image_info($baseUrl)`

Returns metadata for the bundled fallback image.

### `seometataginfo_get_og_image_source_info($baseUrl)`

Returns theme preview if valid, otherwise plugin fallback.

## 6. OG metadata and dynamic endpoint

### `seometataginfo_can_transform_og_image($imageInfo)`

Checks local path/type plus required GD functions for JPEG/PNG transformation.

### `seometataginfo_build_og_image_url($baseUrl, $imageInfo, $contentSource = '')`

Builds public dynamic endpoint URL with `seometa_ogimage`, `v`, and optional validated `seometa_ogsource`.

### `seometataginfo_prepare_og_image_meta($baseUrl, $imageInfo, $contentSource = '')`

Returns public OG metadata. Transformable local sources use the dynamic 1200 × 630 endpoint; otherwise the direct source URL is used.

### `seometataginfo_get_requested_content_og_image_info($baseUrl)`

Rehydrates a content source from the endpoint query and returns both request-presence state and validated metadata.

### `seometataginfo_get_og_image_meta($baseUrl)`

Top-level normal-page OG image chooser: content first, then fallback source.

### `seometataginfo_is_og_image_request()`

Recognizes the endpoint query flag. Accepted scalar values are empty string, `1`, `true`, and `yes`.

### `seometataginfo_send_status($code)`

Sends HTTP status using `http_response_code()` when available; explicit header fallback exists for 304/404.

### `seometataginfo_send_image_cache_headers(...)`

Sends cache-control, ETag, Last-Modified and handles conditional 304 responses.

### `seometataginfo_output_image_file($imageInfo)`

Streams a validated original local file.

### `seometataginfo_image_create_from_file($imageInfo)`

Creates GD source handle for JPEG/PNG.

### `seometataginfo_output_image_resource($image, $imageInfo)`

Encodes GD resource/object according to JPEG/PNG source type.

### `seometataginfo_destroy_image_resource(&$image)`

Cross-version cleanup helper for GD handles.

### `seometataginfo_calculate_og_contain_box(...)`

Pure geometry helper for centered proportional contain-fit.

### `seometataginfo_render_og_image(...)`

Transforms a validated image onto the target canvas, optionally using/storing APCu binary cache.

### `seometataginfo_serve_og_image()`

Dynamic endpoint controller. Explicit content source has priority; invalid explicit source does not become a theme fallback.

## 7. Current entry/article metadata

### `seometataginfo_get_current_single_entry_data()`

Returns cached current entry ID/data without advancing the primary query.

### `seometataginfo_get_category_path($category_id, $separator = '/')`

Builds a parent-to-leaf category path.

### `seometataginfo_get_article_section()`

Returns first valid current-entry category hierarchy.

### `seometataginfo_is_tag_plugin_enabled()`

Checks active/installed Tag plugin state.

### `seometataginfo_get_article_tags()`

Returns unique current-entry tags.

### `seometataginfo_get_article_published_time()`

Returns ISO 8601 entry publication time.

## 8. Metadata-file routing

### `process_meta($file_meta, $type, $id, $sep)`

Shared tag/archive/category metadata-file creation/read/output helper.

### `process_tag_meta()`

Builds tag metadata file/context.

### `process_archive_meta()`

Builds archive metadata file/context.

### `process_category_meta()`

Validates category and builds category metadata file/context.

### `seometa_category_id_exists($cat_id)`

Checks `CONTENT_DIR/categories.txt` for a category ID.

### `seometataginfo_cache_set($id, $desc, $keys)`

Stores entry description/keywords in a request-global cache.

### `seometataginfo_cache_get($id)`

Reads request-global entry metadata cache.

### `seometataginfo_ensure_metafile(&$file_meta)`

Ensures requested metadata file/default metadata exist where possible.

## 9. Administration and Smarty

### `seometataginfo_get_admin_string($key, $default = '')`

Loads an admin translation key with request-local fallback cache.

### `plugin_seometatags_setup()`

Reports robots.txt setup/writability state.

### `seometataginfo_assign_defaults()`

Assigns empty SEO variables to Smarty.

### `seometataginfo_assign_entry_vars($id)`

Loads/memoizes entry SEO metadata and assigns Smarty variables.

### `admin_plugin_seometataginfo`

Admin panel class for robots.txt.

Methods:

- `setup()`
- `onsubmit($data = null)`

## 10. Entry-editor class

### `plugin_seometatags_entry`

Methods:

- `simple()` — renders SEO fields;
- `sanitizeSeoField($input)` — sanitizes description/keywords;
- `do_save()` — writes metadata;
- `post($data)` — registers publish save callback;
- `save($id, $arr)` — entry save bridge;
- `save_static($title)` — static-page save bridge;
- `__construct()` — registers editor hooks.

## 11. Content image resolver (`inc/og-content-image.php`)

### `seometataginfo_content_empty_image_meta()`

Returns canonical empty image metadata structure.

### `seometataginfo_content_unset_global($key)`

Unsets a global key through a helper so static analysis does not incorrectly infer constructor side effects.

### Probe callbacks

- `seometataginfo_content_probe_media_callback(...)`
- `seometataginfo_content_probe_img(...)`
- `seometataginfo_content_probe_photoswipeimage(...)`
- `seometataginfo_content_probe_gallery(...)`
- `seometataginfo_content_probe_photoswipegallery(...)`

These create ordered markers/tokens rather than rendering media.

### `seometataginfo_content_probe_replace_code($parser, $tag, $callback)`

Replaces one existing cloned parser code while preserving callback params, content type, nesting rules, and flags.

### `seometataginfo_content_probe_media($content)`

Runs the cloned active BBCode parser and returns:

```text
html
tokens
```

### `seometataginfo_content_remote_image_meta($url)`

Validates but does not fetch remote HTTP(S) source.

### `seometataginfo_content_path_is_within($path, $root)`

Canonical path containment predicate.

### `seometataginfo_content_normalize_local_image_path($source)`

Normalizes local image namespace/path and rejects traversal/query/fragment/control characters.

### `seometataginfo_content_local_image_meta($source, $baseUrl)`

Validates local image through realpath, root containment, file checks, and `getimagesize()`.

### `seometataginfo_content_image_meta($source, $baseUrl)`

Dispatches remote versus local image resolution.

### `seometataginfo_content_gallery_meta($source, $baseUrl)`

Validates gallery and selects first valid original image according to `gallery_read_images()`.

### `seometataginfo_content_resolve_token($token, $baseUrl)`

Dispatches image versus gallery marker.

### `seometataginfo_find_first_content_image_meta($content, $baseUrl, $applyReadMore)`

Finds first visible valid original media item in one content string.

### `seometataginfo_get_current_static_content()`

Resolves current static raw content.

### `seometataginfo_get_stream_query_params($query)`

Copies relevant current stream window parameters.

### `seometataginfo_get_stream_content_image_meta($baseUrl)`

Scans a secondary query without consuming the page's primary iterator.

### `seometataginfo_get_content_og_image_meta($baseUrl)`

Top-level context dispatcher for static/single/search/stream content image selection.

## 12. Helper file (`inc/hw-helpers.php`)

Context helpers are conditionally defined when FlatPress has not already supplied them:

- `is_single`
- `is_comments`
- `is_static`
- `is_static_home`
- `is_blog_home`
- `is_blog_page`
- `is_paging`
- `is_category`
- `is_tag`
- `is_feed`
- `is_search`
- `is_contact`
- `is_archive`
- `is_archive_year`
- `is_archive_month`
- `is_archive_day`
- `get_category_name`
- `pathinfo_filename`
- `currentPageURL`

Utility helpers:

- `rrmdir`
- `rcopy`
- `is_empty_dir`
- `echoPre`

## 13. Migration helpers (`inc/migrate_data.php`)

- `create_defaults()`
- `rmigrate_entries($cur)`
- `migrate_old()`

These operate on persistent metadata and should be treated as migration tooling rather than normal request-path logic.

## 14. `iniParser` class

Methods:

- `__construct($filename)`
- `getSection($key)`
- `getValue($section, $key)`
- `get($section, $key = null)`
- `setSection($section, $array)`
- `setValue($section, $key, $value)`
- `set($section, $key, $value = null)`
- `save($filename = null)`

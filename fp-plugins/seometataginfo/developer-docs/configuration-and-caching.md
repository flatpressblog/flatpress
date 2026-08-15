# Configuration, Caching, and HTTP Behavior

## 1. Feature constants

The plugin defines defaults only when a constant is not already defined.

| Constant | Default | Purpose |
|---|---:|---|
| `SEOMETA_MIGRATE_DATA` | `false` | Enable legacy SEO metadata migration |
| `SEOMETA_GEN_OPEN_GRAPH` | `true` | Emit Open Graph tags |
| `SEOMETA_GEN_TITLE` | `true` | Generate context-aware page titles |
| `SEOMETA_GEN_TITLE_META` | `true` | Emit `meta name="title"` and `og:title` |
| `SEOMETA_GEN_IMAGE_META` | `true` | Emit Open Graph image metadata |
| `SEOMETA_OGIMAGE_TARGET_WIDTH` | `1200` | Dynamic OG canvas width |
| `SEOMETA_OGIMAGE_TARGET_HEIGHT` | `630` | Dynamic OG canvas height |
| `SEOMETA_OGIMAGE_QUERY_VAR` | `seometa_ogimage` | Dynamic endpoint selector |
| `SEOMETA_OGIMAGE_SOURCE_QUERY_VAR` | `seometa_ogsource` | Optional validated local content source |
| `SEOMETA_OGIMAGE_FALLBACK_RELATIVE_PATH` | `fp-plugins/seometataginfo/imgs/og-image.png` | Bundled fallback |
| `SEOMETA_OGIMAGE_INFO_APCU_TTL` | at least 60, normally `FP_APCU_IO_TTL`/3600 | Image-info cache TTL |
| `SEOMETA_OGIMAGE_BINARY_APCU_TTL` | at least 60, normally `FP_APCU_IO_TTL`/3600 | Rendered-image cache TTL |
| `SEOMETA_OGIMAGE_BINARY_APCU_MAX_BYTES` | `1572864` | Maximum rendered body cached in APCu; `0` means no size limit |
| `SEOMETA_GEN_CANONICAL` | `true` | Emit canonical link and `og:url` |
| `SEOMETA_HIDECOMMENTS` | `true` | Canonicalize comment URLs back to page URL |

`seometataginfo_flag()` reads these switch constants as booleans.

## 2. Storage constants

Derived from `CONTENT_DIR`:

```text
SEOMETA_DIR
SEOMETA_DEFAULT_DIR
SEOMETA_ENTRY_DIR
SEOMETA_STATIC_DIR
SEOMETA_CATEGORY_DIR
SEOMETA_TAG_DIR
SEOMETA_ARCHIVE_DIR
```

These point to persistent SEO metadata, not cache data.

## 3. Runtime configuration lookup

`seometataginfo_get_runtime_config()` prefers:

1. populated global `$fp_config`;
2. `$GLOBALS['EARLY_FP_CONFIG']`;
3. empty array.

This is important for early dynamic image requests, which may run before every normal page subsystem is fully initialized.

## 4. Image metadata cache

`seometataginfo_get_supported_image_info()` uses two cache layers:

### Request-local static cache

Identity includes:

```text
absolute path | mtime | size | base URL
```

### APCu

APCu key:

```text
seometa:og:imageinfo:v1:<md5(...)>
```

The cached record contains:

- MIME;
- width;
- height;
- image type.

The key includes file mtime and size, so a changed source naturally creates a different cache identity.

## 5. Rendered binary cache

Dynamic JPEG/PNG output can be captured and stored in APCu.

Key form:

```text
seometa:og:imagebin:v1:<md5(path|type|mtime|size|targetWidthxTargetHeight)>
```

This means changing:

- source file;
- source type;
- source mtime;
- source size;
- target dimensions

changes the binary cache key.

The default maximum cached binary size is 1.5 MiB.

If APCu is unavailable, rendering still works; output is simply generated directly.

## 6. `iniParser` caching

`iniParser` uses:

- a request-local static cache;
- optional APCu hot cache.

When APCu is active, mtime and file size contribute to the INI cache token. The APCu TTL used there is 600 seconds.

This cache is separate from the OG image caches.

Image-description metadata is intentionally **not** part of the transformed-image binary cache key or dynamic endpoint query. Two pages can therefore reference the same source image with different `og:image:alt` text without generating duplicate image bytes. The description travels only in normal page metadata.

## 7. Browser/crawler HTTP caching

`seometataginfo_send_image_cache_headers()` emits:

```text
Cache-Control: public, max-age=86400
ETag: W/"..."
Last-Modified: ...
```

The ETag source includes:

- mtime;
- target width/height;
- absolute path.

Conditional requests are supported through:

- `If-None-Match`;
- `If-Modified-Since`.

Matching conditions return HTTP 304 and exit.

## 8. Cache-busting query version

The public dynamic image URL contains:

```text
v=<source-mtime>
```

The endpoint does not use `v` as a trust boundary. Source validation is based on `seometa_ogsource` and filesystem checks.

`v` exists primarily to make the public URL change when the source's modification time changes.

## 9. MIME and output format

Theme preview candidates are accepted only through `seometataginfo_get_supported_image_info()`, which currently validates JPEG/PNG image types.

Content-local metadata can identify other `image/*` types, but `seometataginfo_can_transform_og_image()` transforms only JPEG and PNG.

For transformable types:

- JPEG source → JPEG output at quality 90;
- PNG source → PNG output.

The target canvas is a true-color image with a white background.

## 10. Optional GD

If the required GD functions do not exist:

- content selection still works;
- remote images remain direct URLs;
- unsupported/untransformable local content metadata uses the direct original URL in normal OG metadata generation;
- the dynamic endpoint can stream a validated original file if transformation is unavailable.

Do not make GD a hard dependency unless FlatPress's platform requirements explicitly change.

## 11. Optional APCu

`seometataginfo_apcu_available()` requires the FlatPress `is_apcu_on()` helper and an enabled APCu state.

No feature depends on APCu for correctness.

APCu only reduces repeated metadata/image processing.

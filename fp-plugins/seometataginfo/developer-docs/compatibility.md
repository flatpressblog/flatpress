# Compatibility

## 1. Target range

Project target:

- PHP **7.2 through 8.5**
- Smarty **4.5.5 through 5.8.4**

The current snapshot's PHPStan configuration declares:

```yaml
phpVersion:
  min: 70200
  max: 80500
level: 5
```

This expresses a static-analysis target range. It is not a substitute for executing runtime tests on every PHP version.

## 2. PHP language constraints

When modifying SEO Meta Tag Info, avoid introducing syntax unavailable on PHP 7.2, such as:

- `match`;
- union/intersection types;
- named arguments;
- attributes;
- nullsafe operator;
- arrow functions if PHP 7.2 support must be retained;
- PHP 8-only string helpers without compatibility wrappers.

The current plugin uses constructs available in the supported baseline, including:

- arrays;
- closures;
- `try/finally`;
- null-coalescing operator;
- scalar casts;
- `parse_url`, `getimagesize`, `realpath`;
- `html_entity_decode`, `strip_tags`, `str_replace`, and scalar checks for image-description normalization;
- GD functions behind `function_exists()` checks.

## 3. GD object/resource difference

GD image handles changed from resources to objects in newer PHP branches.

The SEO implementation generally accepts both:

```php
is_object($image) || is_resource($image)
```

`seometataginfo_destroy_image_resource()` also guards `imagedestroy()` with the FlatPress `is_php85_plus()` compatibility helper when available.

Keep this cross-version distinction in mind for image pipeline changes.

## 4. Smarty compatibility

The OG resolver is independent of Smarty rendering.

Smarty usage is limited to stable APIs:

- `getTemplateVars('static_page')`;
- `assign(...)`;
- existing FlatPress form/template helpers in the admin template.

No new Smarty plugin syntax is required by the content-image feature or the `og:image:alt` title/caption feature.

The current `admin.plugin.seometataginfo.tpl` uses ordinary variable interpolation, `{include}`, `{html_form}`, `{if}`, and `escape`.

Gallery-caption lookup happens in PHP through `gallery_read_captions()` and does not introduce any Smarty dependency.

## 5. Webserver portability

The dynamic OG image endpoint is an `index.php` query request:

```text
index.php?seometa_ogimage=1...
```

It does not require:

- Apache rewrite rules;
- nginx-specific location blocks;
- IIS rewrite rules.

Correct public URL construction still depends on FlatPress's configured public base URL and normal PHP request environment.

## 6. Shared hosting

Design choices that support shared hosting:

- no background worker;
- no external image fetch;
- GD optional;
- APCu optional;
- no required shell command for runtime;
- dynamic image served by normal PHP entry point;
- filesystem checks based on FlatPress constants.

Potential constraints:

- memory required to decode/resample very large source images;
- write permission required for SEO metadata and robots operations;
- APCu may be disabled in web or CLI SAPIs independently.

## 7. Image formats

### Content selection

Any local file recognized by `getimagesize()` as `image/*` can be selected as original content metadata.

### Dynamic 1200 × 630 transformation

Currently limited to:

- JPEG;
- PNG;

and only when the corresponding GD functions exist.

Other local formats use the direct source URL during normal `og:image` metadata preparation.

### Thumb is independent

The Thumb plugin can support a wider preview set depending on GD, including GIF/WebP. This does not automatically expand the SEO dynamic formatter.

## 8. APCu

APCu is a performance enhancement only.

Correct behavior must remain when:

- APCu extension is missing;
- `is_apcu_on()` returns false;
- cache get/set fails;
- rendered image exceeds the configured cache-size threshold.

## 9. Filesystem/path separators

The content image normalizer converts `\` to `/` before namespace and traversal checks.

Canonical filesystem boundaries are then checked through `realpath()`.

This is relevant for Windows/IIS-style paths while retaining public URL slash conventions.

## 10. CLI versus web tests

Regression harnesses define FlatPress/GD stubs when necessary, so a passing CLI test can validate decision logic without proving that the host's web SAPI has GD/APCu enabled.

For releases, combine:

1. PHPStan;
2. CLI regressions;
3. at least one web-SAPI smoke test of `og:image`;
4. runtime checks on the oldest/newest supported PHP versions when those interpreters are available.

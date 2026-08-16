# Maintenance and Extension Guide

## 1. Preserve the separation of concerns

When adding features, keep these layers distinct:

1. **context selection** — which content is relevant to this request;
2. **media probing** — where media occurs in parser/source order;
3. **visibility** — whether ReadMore exposes it in a stream;
4. **source resolution** — original local/remote/gallery media;
5. **image description** — explicit image `title` or exact selected gallery caption;
6. **public OG metadata** — dynamic endpoint versus direct URL plus final site-title fallback;
7. **dynamic response** — validation, caching, transform, output.

Do not collapse these stages into one HTML-scraping function.

## 2. Adding a new BBCode media tag

If another plugin introduces a new media tag that should participate in `og:image` selection:

1. verify when/how that tag is registered in the active BBCode parser;
2. add a marker callback;
3. add the tag/callback pair in the canonical `content_media_probe_media()` implementation;
4. extend `content_media_resolve_token()` with its original-source semantics;
5. keep the cloned parser's original content type, nesting rules, callback params, and flags;
6. test behavior when the providing plugin is enabled and disabled;
7. test placement inside `[code]` or other restricted parser contexts;
8. verify probing does not invoke the media plugin's rendering side effects.

Do not recognize a tag solely by regex when the active parser does not register it.

## 3. Adding a transformable local image format

Transformation support currently requires coordinated changes.

Review at least:

- source metadata/type validation;
- `seometataginfo_can_transform_og_image()`;
- `seometataginfo_image_create_from_file()`;
- `seometataginfo_capture_image_resource_output()`;
- `seometataginfo_output_image_resource()`;
- MIME output;
- APCu binary cache;
- regression harness stubs;
- real GD runtime support across PHP target versions.

Do not infer support from Thumb. Thumb and SEO have separate transformation pipelines.

## 4. Changing target dimensions

The target is configured by:

```text
SEOMETA_OGIMAGE_TARGET_WIDTH
SEOMETA_OGIMAGE_TARGET_HEIGHT
```

The binary cache key already contains target dimensions.

If defaults change:

- update social metadata expectations;
- update geometry regression cases;
- verify `og:image:width`/`height`;
- verify cache headers/ETag behavior;
- verify image memory usage;
- update this documentation.

The contain-fit algorithm is dimension-agnostic and should remain proportional.

## 5. Changing background policy

Current transformed images are centered on **white**.

If introducing transparent, blurred, colored, or crop-based backgrounds, treat that as a behavioral change. Verify:

- JPEG cannot preserve transparency;
- PNG output semantics;
- social-media preview appearance;
- memory/caching impact;
- whether "no distortion" remains true;
- whether cropping is acceptable.

## 6. Changing ReadMore

SEO intentionally reuses `plugin_readmore_get_stream_excerpt()`.

If ReadMore's chopping behavior changes:

1. keep the helper and normal renderer aligned;
2. rerun `compare_readmore_behavior.php` against the intended reference;
3. rerun content-image visibility tests;
4. test media immediately before/after boundaries;
5. test all modes;
6. verify `$_GET['page']` behavior.

Never copy a new ReadMore algorithm into SEO unless there is no reusable contract.

## 7. Changing BBCode or PhotoSwipe

Any change to parser initialization, callback flags, nesting rules, or PhotoSwipe tag registration can affect the marker probe.

After such changes run:

```bash
php fp-plugins/seometataginfo/regression-test/validate_target_parser.php
php fp-plugins/seometataginfo/regression-test/simulate_og_content_image.php
```

Also verify that PhotoSwipe's internal `lastusedDataIndex` is not changed by an SEO head request.

### Image-title contract

If BBCode, PhotoSwipe, or Gallery captions changes how titles are parsed or persisted, preserve these SEO rules:

1. source selection happens before title fallback;
2. `[img]` uses only the explicit parsed `title` attribute;
3. Gallery uses the caption keyed by the exact selected valid filename;
4. missing title/caption never advances to a later image;
5. `general.title` fallback is applied only at metadata output;
6. the description does not participate in transformed-image cache identity.

After such changes rerun both parser validation and content-image simulation.

## 8. Changing Thumb

SEO should continue to ignore `.thumbs`.

If Thumb changes its directory name or rendering contract, the shared content-media resolver usually should **not** need to change, because it selects from original tag attributes.

A requirement to modify SEO because of a thumbnail-path change is a warning sign that selection may have become coupled to rendered HTML.

## 9. Changing query handling

Be careful with `FPDB_Query`.

Important target-code facts:

- constructor sets global `current_query`;
- `getEntry()` advances the pointer/walker;
- `peekEntry()` does not advance the entry window;
- query preparation can happen lazily through `hasMore()`/`peekEntry()`.

Any stream scanner must preserve the primary template query.

## 10. Changing dynamic endpoint query names

If `SEOMETA_OGIMAGE_QUERY_VAR` or `SEOMETA_OGIMAGE_SOURCE_QUERY_VAR` changes:

- `seometataginfo_build_og_image_url()` and request parsing use the constants already;
- update manual smoke-test URLs;
- verify literal `&amp;` alias handling;
- keep exact-key precedence;
- retain array-value rejection.

## 11. Query-entity robustness

Do not "fix" the HTML output by emitting unescaped `&` inside attributes. HTML escaping is correct.

The robustness logic belongs in query-parameter normalization for literal copied-source requests.

Regression cases should include:

```text
&seometa_ogsource=...
&amp;seometa_ogsource=...
&amp;amp;seometa_ogsource=...
```

plus an exact-key/alias conflict.

## 12. Changing local-path policy

Any relaxation should be security-reviewed.

Current invariant:

> a local content source must normalize into the `IMAGES_DIR` namespace and its canonical real path must remain inside the canonical image root.

Do not replace the canonical containment check with a simple substring test on the untrusted raw source.

## 13. Remote-image feature requests

The current design intentionally does not fetch remote images.

If a future feature proposes resizing remote images, it introduces a new security/operations domain:

- SSRF;
- DNS rebinding;
- private/link-local IP filtering;
- redirects;
- timeouts;
- maximum body size;
- MIME validation;
- decompression bombs;
- TLS handling;
- cache policy;
- proxy/privacy expectations.

That should be designed as a separate reviewed feature, not a small extension of `seometataginfo_content_remote_image_meta()`.

## 14. Generated test results

The current snapshot demonstrates why generated JSON must not be assumed current: the format-validation script contains more assertions than its stored result JSON.

Recommended policy after every regression change:

```text
change test script
→ run script
→ capture JSON
→ verify total/passed/failed
→ update result artifact if the project tracks it
```

A CI job should preferably run the scripts rather than trusting pre-generated result files.

## 15. Version bump discipline

Behavioral plugin changes should update the `Version:` header in `plugin.seometataginfo.php`.

If a shared helper contract in ReadMore changes, update the ReadMore version as well.

Avoid unrelated version bumps in BBCode/PhotoSwipe/Thumb when those files are not changed.

## 16. Documentation update triggers

Update these developer docs when any of the following changes:

- plugin version;
- OG source priority;
- supported media tags;
- ReadMore integration;
- target dimensions/background policy;
- supported transformed formats;
- endpoint query variables;
- path-security rules;
- APCu/cache headers;
- metadata storage schema;
- test matrix or commands;
- dependency plugin versions or relevant hook behavior.

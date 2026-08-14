# Testing and Static Analysis

## 1. Test assets

The plugin contains four executable regression scripts:

```text
regression-test/
├── simulate_og_content_image.php
├── validate_og_image_format.php
├── validate_target_parser.php
└── compare_readmore_behavior.php
```

Generated JSON result files are also present, but the PHP scripts are the authoritative test definitions.

## 2. Generated result artifacts

In the current `flatpress-master-3415-05` snapshot, the checked-in result JSON files were refreshed after the regression run:

- `simulate_og_content_image.php`: 39 assertions, 39 passed;
- `validate_target_parser.php`: 6 assertions, 6 passed;
- `compare_readmore_behavior.php`: 75 comparisons, 75 passed;
- `validate_og_image_format.php`: 17 assertions, 17 passed.

The PHP scripts remain the authoritative test definitions. Result JSON files are generated artifacts and can become stale after a test script changes.

Therefore:

> Never infer the current regression matrix solely from checked-in `*-results.json`. Re-run the PHP scripts after changes and regenerate result artifacts when the project tracks them.

## 3. Content-image simulation

Run from the FlatPress root:

```bash
php fp-plugins/seometataginfo/regression-test/simulate_og_content_image.php
```

Current test definitions cover:

- first original image in a single entry;
- thumbnail exclusion;
- transform-source metadata;
- first gallery image in a single entry;
- static image/gallery;
- source ordering between gallery and image;
- multiple images;
- empty/invalid gallery fallthrough;
- PhotoSwipe aliases;
- invalid image then valid gallery;
- non-image file inside gallery;
- existing `.thumbs` preview;
- `popup=false`;
- remote image direct URL/no local transform source;
- ReadMore manual/auto/semiauto/sentence visibility;
- ReadMore disabled behavior;
- single/static media after `[more]`;
- `[code]` literal exclusion;
- stream scan across multiple entries;
- restoration of `current_query`;
- hidden first-entry media falling through to a later entry;
- PhotoSwipe disabled;
- path traversal rejection;
- `gallery_read_images()` ordering.

The harness uses isolated fixtures rather than installed instance content.

## 4. Target parser validation

Run:

```bash
php fp-plugins/seometataginfo/regression-test/validate_target_parser.php
```

This loads the target BBCode/PhotoSwipe parser behavior and checks:

- real `[img]` detection;
- PhotoSwipe gallery detection;
- legacy PhotoSwipe image/gallery aliases;
- nested image inside `[code]` does not become media;
- probe does not advance PhotoSwipe's internal image index.

This test is particularly important after BBCode or PhotoSwipe parser changes.

## 5. OG endpoint and 1200 × 630 validation

Run:

```bash
php fp-plugins/seometataginfo/regression-test/validate_og_image_format.php
```

### Instance-only reproduction asset

The originally reported failure used `avm-gelaende.png`. That file is an image uploaded to a specific installed FlatPress test instance; it is **not part of the FlatPress repository/distribution** and must never be a prerequisite for the regression suite.

The regression therefore creates its own valid PNG under the temporary fixture `ABS_PATH` and exercises the same URL/query shape with the neutral name `repo-independent-og-source.png`. The test is valid on a clean repository checkout, on an installed instance with unrelated user images, and on CI without any user content.

The **current script** includes assertions for:

- transformable local PNG metadata;
- dynamic content endpoint URL selection;
- advertised 1200 × 630 dimensions;
- rehydration of validated content source;
- proof that the HTML-entity query test uses a self-contained temporary fixture rather than repository/user content;
- literal `&amp;` query copied from HTML source, using a repository-independent temporary image fixture;
- double escaped `&amp;amp;`;
- escaped OG flag;
- exact parameter precedence over escaped alias;
- `BLOG_ROOT` path normalization;
- traversal rejection through escaped alias;
- normal traversal rejection;
- contain geometry for 16:9 landscape;
- contain geometry for portrait;
- contain geometry for square;
- contain geometry for already-1200×630 input;
- remote image remains direct and is not server-fetched.

When GD is absent in the CLI SAPI, the harness stubs only the missing functions needed to exercise transform-selection logic. Pixel rendering is not falsely claimed; geometry is tested as pure logic.

## 6. ReadMore equivalence

Usage:

```bash
php fp-plugins/seometataginfo/regression-test/compare_readmore_behavior.php \
  /path/to/original-flatpress \
  /path/to/patched-flatpress
```

The test launches isolated child PHP processes because `PLUGIN_READMORE_MODE` is a constant.

Matrix:

- modes: `manual`, `auto`, `semiauto`, `sentence`, invalid-mode fallback;
- contexts: stream, single, stream with `page` parameter;
- cases: short text, long text, manual marker, sentence sequence, image-like HTML.

Total:

```text
5 modes × 3 contexts × 5 cases = 75 comparisons
```

The goal is byte-identical output between the chosen reference tree and the patched tree.

## 7. PHPStan

The repository workflow runs level 5 with:

```bash
php phpstan.phar analyse \
  --configuration=.dist/phpstan.neon.dist \
  --error-format=table
```

The provided local tree also contains `.dist/phpstan.phar`; when using that copy from the repository root:

```bash
php .dist/phpstan.phar analyse \
  --configuration=.dist/phpstan.neon.dist \
  --error-format=table
```

The configuration targets PHP 7.2–8.5.

### Regression stubs and symbol collisions

Several regression harnesses intentionally define global FlatPress or GD stubs. If PHPStan scans those scripts as production symbol providers, their simplified signatures can shadow real FlatPress functions and generate misleading project-wide diagnostics.

When maintaining PHPStan configuration:

- analyze production code with production symbols;
- do not let test-only stub declarations redefine production signatures;
- still execute regression scripts separately.

Do not suppress a genuine production diagnostic merely because test stubs also exist.

## 8. Syntax checks

A practical plugin-focused syntax pass:

```bash
find fp-plugins/seometataginfo \
     fp-plugins/bbcode \
     fp-plugins/photoswipe \
     fp-plugins/thumb \
     fp-plugins/readmore \
     -name '*.php' -print0 |
while IFS= read -r -d '' file; do
    php -l "$file" || exit 1
done
```

On shells without the same `find`/`read -d` behavior, use an equivalent platform-specific loop.

## 9. Manual web smoke test

After automated tests, verify a real installed instance.

Recommended minimum cases:

1. single entry with `[img="images/example.png"]`;
2. single entry with `[gallery="images/example-gallery"]`;
3. static page with image;
4. stream where first visible media is in the second entry;
5. stream where first entry's media is after `[more]`;
6. a resized BBCode image with `.thumbs` present;
7. `popup=false`;
8. dynamic OG endpoint URL copied from rendered HTML;
9. literal `&amp;` copy of that endpoint URL;
10. invalid/traversal source returns failure rather than theme preview.

Verify both:

- `<meta property="og:image" ...>`;
- the image bytes returned by the endpoint.

## 10. Aspect-ratio verification

For a transformable source, verify that the endpoint response is exactly the configured target canvas and that the source is not stretched.

Current default target:

```text
1200 × 630
```

Because the implementation uses contain-fit, portrait/square images will have white side areas; very wide images can have white top/bottom areas.

## 11. Release-quality checklist

Before merging changes to the image subsystem:

- PHPStan level 5 is clean for affected production scope;
- all four regression scripts pass;
- result JSONs are regenerated if they are intended to be versioned;
- syntax checks pass;
- a real web endpoint test passes;
- no `.thumbs` URL becomes selected as `og:image`;
- active query and PhotoSwipe state remain unchanged after probe;
- invalid local source does not fall back to theme preview;
- remote URLs are not fetched;
- PHP 7.2 syntax compatibility is preserved.

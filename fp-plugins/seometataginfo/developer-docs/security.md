# Security Model

## 1. Trust boundaries

The plugin handles data from several trust domains:

- administrator/editor form input;
- persisted SEO INI files;
- URL query parameters;
- BBCode content written by site authors;
- persisted Gallery captions written through the Gallery captions admin feature;
- local filesystem image/gallery paths;
- remote image URLs;
- request headers used for conditional caching;
- host/document-root configuration.

The Open Graph endpoint is publicly reachable and must therefore treat its query parameters as untrusted.

## 2. Local OG source containment

The security model is intentionally layered.

### Lexical normalization

`seometataginfo_content_normalize_local_image_path()` rejects:

- control characters;
- `?` query data;
- `#` fragments;
- `..` segments.

It normalizes separators and only permits the FlatPress image namespace.

### Canonical filesystem validation

`seometataginfo_content_local_image_meta()` resolves both the image root and candidate file with `realpath()`.

`seometataginfo_content_path_is_within()` then requires the canonical candidate to equal the root or start with `<root>/`.

This protects against path traversal and also reduces symlink-based escape risk because comparison happens after canonicalization.

### File and content validation

The candidate must be:

- a regular file;
- readable;
- recognized by `getimagesize()`;
- reported with an `image/*` MIME.

## 3. Gallery containment

Gallery resolution performs its own normalization and canonical directory check before calling `gallery_read_images()`.

Filename values returned by the gallery helper are also constrained:

```php
basename($file) === $file
```

A gallery item cannot inject a nested path into the subsequent image resolver.

## 4. Remote image policy and SSRF

Remote image metadata accepts only URLs with:

- `http` or `https` scheme;
- a host component;
- no control characters.

The resolver does **not** fetch the remote URL.

This policy intentionally avoids SSRF, remote timeout, DNS, certificate, and bandwidth concerns in the SEO request path.

Because the server does not inspect remote pixels, remote width/height/MIME remain unknown in this layer.

## 5. Query-parameter alias tolerance

Literal HTML entity prefixes are handled only at the **parameter-name** level.

Example:

```text
amp;seometa_ogsource
```

can normalize to:

```text
seometa_ogsource
```

Security properties retained:

- exact key has precedence;
- array values are invalid;
- normalized value still passes the full local-path validation;
- invalid explicit content source does not fall back to a theme image.

Repeated `amp;` handling exists for robustness when source HTML has been copied or double-escaped.

## 6. Dynamic endpoint response behavior

An explicit invalid content source results in the no-valid-image path and HTTP 404.

This is safer and easier to debug than returning the theme preview, because a malformed source cannot be mistaken for a successfully served requested image.

## 7. Parser side effects

The SEO probe clones the BBCode parser and replaces media callbacks.

Security/stability benefit:

- it does not execute image/gallery rendering side effects merely because a crawler requests the page head;
- it does not create thumbnails;
- it does not advance PhotoSwipe's media index;
- it retains parser restrictions, including code-block behavior.

The temporary global marker context is restored in `finally`.

## 8. Query side effects

An independent `FPDB_Query` changes global query state by design.

The SEO stream scan saves and restores:

- `current_query`;
- `post`.

Do not remove the `try/finally` restoration during refactoring.

## 9. Metadata form sanitization

Editor-supplied description/keyword fields pass through `sanitizeSeoField()` before storage.

Output also uses `htmlspecialchars()` or FlatPress escaping helpers in many metadata contexts.

When adding new metadata values, preserve the distinction between:

- storage sanitization;
- HTML attribute escaping at output.

Do not rely on only one of those layers for all contexts.

The same output-layer rule applies to `og:image:alt`. BBCode `title` and Gallery captions are normalized to plain text by `seometataginfo_content_normalize_image_alt()` and are escaped with `htmlspecialchars()` only when the meta attribute is emitted. This avoids both markup injection and accidental double escaping of already entity-encoded captions.

## 10. robots.txt write surface

The robots admin panel writes to:

```text
$_SERVER['DOCUMENT_ROOT'] . '/robots.txt'
```

This is intentionally outside the FlatPress content tree in many installations.

Requirements:

- non-empty/valid `DOCUMENT_ROOT`;
- writable document root for creation;
- writable existing file for editing.

Changes to this feature should be reviewed as host-level filesystem operations, not ordinary plugin-content writes.

## 11. Canonical host handling

`seometataginfo_build_public_url()` prefers the configured public base URL over raw request host reconstruction when possible.

This helps avoid deriving public canonical URLs solely from request host data.

The fallback helper `currentPageURL()` still exists for contexts where the configured base URL cannot be applied.

## 12. Caching safety

Image APCu identities include filesystem state such as mtime/size.

HTTP cache identity includes path, dimensions, and mtime.

Do not simplify these keys to user-provided source strings alone.

## 13. Security regression cases that must remain

At minimum preserve tests for:

- local `..` traversal rejection;
- traversal through literal `amp;seometa_ogsource`;
- exact query parameter precedence over escaped aliases;
- invalid explicit source not falling back to theme preview;
- code-block content not becoming media;
- remote image no-fetch behavior;
- exact gallery-caption/file binding;
- missing title/caption preserving image selection and using site-title fallback at output;
- active query/global-state restoration.

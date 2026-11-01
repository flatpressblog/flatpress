# 08 - Media Limits and Safe Downloads

## Implementation order and responsibilities

`plugin.mastodon.php` loads `inc/media-safety.php`. Bounded downloads with a shared
budget were implemented first, followed by poster validation, automatic instance
refresh and video metadata validation. Existing media-family rules, asynchronous
uploads and the media API remain in place. The existing `[more]` boundary for
entry exports is applied before media selection; media beyond that boundary
is not additionally exported.

## Import: streamed downloads

cURL writes through a write callback; the PHP stream fallback reads chunks of
at most 65536 bytes. Both use the same bounded file writer. Production responses
contain a file path and an empty `body`, never the complete media file in PHP
memory. The importer moves the completed file into the status staging directory.

| Limit                                    | Default                     | Configuration before loading the plugin |
| ---------------------------------------- | --------------------------- | --------------------------------------- |
| Image or poster per download             | 16777216 bytes (16 MiB)     | `PLUGIN_MASTODON_DOWNLOAD_IMAGE_BYTES`  |
| Audio or video per download              | 103809024 bytes (99 MiB)    | `PLUGIN_MASTODON_DOWNLOAD_AV_BYTES`     |
| Media response bytes per content run     | 536870912 bytes (512 MiB)   | `PLUGIN_MASTODON_DOWNLOAD_TOTAL_BYTES`  |
| Media requests per content run           | 64                          | `PLUGIN_MASTODON_DOWNLOAD_REQUESTS`     |
| Redirects per download                   | At most 5 (6 HTTP requests) | Fixed                                   |
| Download time window including redirects | 180 seconds                 | Fixed                                   |

The four defaults are defined in the opening constants block of
`plugin.mastodon.php`, each guarded by `if (!defined(...))`. Values defined
before the plugin loads are preserved. `inc/media-safety.php` reads these
constants without maintaining a second set of defaults. The isolated transport
fixture defines its own small constants to exercise their use directly.
The four configurable constants are listed in PHPStan's `dynamicConstantNames`,
so the combined analysis does not confuse fixture values with production defaults.

The four constants accept positive byte or request counts. These import limits
are local safety limits, not a claim about every Mastodon instance's upload
limits. Larger remote files can therefore be deliberately rejected. A file
exactly at the size limit is accepted; one additional byte aborts the download.
Failed candidates, retries and posters share the budget with every other media
download in the content run. A received but rejected transport chunk still
counts, so the counter can exceed the limit by that chunk. The rejected chunk
is not written. A new content run resets the budget. No new global time budget
is imposed across all 64 possible requests.

Only HTTP/HTTPS URLs without embedded user information are allowed. Bearer tokens
are sent only to the configured instance origin; redirects to other origins
remove Authorization and Cookie headers. The plugin handles redirects explicitly,
rather than enabling automatic redirects in cURL or the stream wrapper.
Missing, empty, truncated or oversized responses leave no partial download file.
When Content-Length is present, it must match the number of bytes written.

## Import failures and retries

A failed primary media download discards the temporary status directory.
Previously published media for that status remain unchanged on this download
failure. No entry is created or updated and no new mapping is written for the
failed status. The import coordinator stops before advancing
`last_remote_status_id`. Previously successful imports remain in place.
A missing optional video poster does not prevent the primary import.

`entries[entryId].import_fingerprint` is the SHA-1 of the JSON status, not a
security guarantee. Together with the existing local `hash`, it prevents
unchanged successful imports from consuming the download budget again on retry.
Changed statuses or local content are checked normally. The existing publication
path copies media directories; it is not a transaction spanning media, entry
and state. Filesystem failures during that later publication stage are a
separate concern.

## Export: poster validation

Before upload, a video poster is checked using its actual MIME type, file size
and `getimagesize()`. The instance's `supported_mime_types`, `image_size_limit`
and `image_matrix_limit` apply. If poster limits are not advertised, the
fallbacks are 16 MiB and 33177600 pixels. An invalid or oversized poster is
removed from the upload; a valid video remains exportable and Mastodon can
generate its preview. The poster is still sent as `thumbnail` and does not
count as a second status attachment.

## Instance information and limits

At the beginning of each content synchronization run, a missing instance snapshot
or one at least an hour old is refreshed through `/api/v2/instance`. A fresh
snapshot causes no additional request. The success timestamp is persisted even
when the JSON is unchanged. Failures retain the last known limits; the next
automatic attempt waits at least five minutes. A successful refresh clears the
request-local failure cache. Manual refresh remains available. Ordinary page
requests do not gain an additional forced instance lookup.

## Export: video metadata

`ffprobe` is located through an absolute path in `PLUGIN_MASTODON_FFPROBE` or
through PATH. The probe reads the first video stream, width, height,
`avg_frame_rate` and `r_frame_rate`. Width multiplied by height is compared with
`video_matrix_limit`; the larger valid frame rate is rounded down and checked
against `video_frame_rate_limit`. Missing or invalid dimensions/frame rates,
probe errors and exceeded limits cause the video to be skipped. Results are
cached within the request by file path, modification time and size.

The process allows only local `file,pipe` protocols, is terminated after ten
seconds and has a monitored output threshold of 65536 bytes each for stdout
and stderr. File sizes are checked every ten milliseconds; this threshold is
not an operating-system-enforced hard write limit. Temporary probe files are
removed afterwards.

Without an executable ffprobe or process support, the existing server-validated
video export remains available by default. Setting
`PLUGIN_MASTODON_REQUIRE_VIDEO_METADATA=true` instead rejects the video with
`video_metadata_unavailable`. An unknown instance limit is not replaced with
an invented fixed video resolution or frame-rate limit. Audio retains MIME
and byte-size validation without video-stream inspection. The new checks do
not prove complete decodability or validate every codec; final media processing
remains Mastodon's responsibility.

## Export without cURL: memory-aware multipart fallback

The cURL upload path continues to use file-backed uploads. Without cURL, the
PHP HTTP stream still needs a complete multipart body in memory. The fallback
now calls `plugin_mastodon_build_multipart_body()` before creating its HTTP
context. The builder accounts for every file, optional poster, text field,
header, delimiter and closing boundary before reading payload contents.

| Setting                                    | Default                 | Purpose                                                                          |
| ------------------------------------------ | ----------------------- | -------------------------------------------------------------------------------- |
| `PLUGIN_MASTODON_MULTIPART_MAX_BYTES`      | 67108864 bytes (64 MiB) | Maximum complete fallback request body, including framing.                       |
| `PLUGIN_MASTODON_MULTIPART_MEMORY_RESERVE` | 8388608 bytes (8 MiB)   | Headroom for other plugin work and the response; values below 8 MiB are clamped. |

Both constants are defined conditionally in the opening block of
`plugin.mastodon.php`. A finite PHP `memory_limit` must leave at least four
times the complete estimated body size plus the reserve after subtracting
`memory_get_usage(true)`. The check is repeated while building and before
returning the body, so available headroom can shrink as the body grows.
This is deliberately conservative and may reject a request that would fit
under ideal allocation conditions. It does not increase PHP's memory limit.

`plugin_mastodon_memory_limit_bytes()` accepts byte counts and K/M/G suffixes.
`-1` disables the PHP-derived restriction, but the local body cap still applies.
Unknown or malformed memory limits fail closed. `upload_max_filesize` and
`post_max_size` are not used as outbound limits: they govern incoming uploads.
Instance upload limits still apply earlier in the export plan. A file accepted
by Mastodon may therefore be rejected locally when the fallback lacks memory.

Files are read in chunks of at most 65536 bytes; no additional full-file string
is retained. Regular-file type and size are checked again after opening, and
short reads or growth beyond the planned size fail without returning a partial
body. This is size-change detection, not a lock against same-size content edits.
The final multipart body remains buffered; this change does not introduce a
fully streaming HTTP upload transport. The reserve is not a hard limit on
remote response size or a guarantee against unrelated allocations.

Failures return an empty body and a specific error before sending the request:
`multipart_body_limit_exceeded`, `multipart_memory_budget_exceeded`,
`multipart_memory_limit_unknown`, `multipart_file_unreadable` or
`multipart_file_changed`. Existing upload error handling receives these errors.
PHPStan treats the two configurable constants as dynamic values.

Seven simulation groups cover quantity parsing, exact memory boundaries,
32/64/128 MiB configurations, unlimited and invalid limits, multipart binary
and poster contents, missing files, an actual temporary reduction of PHP's
memory limit, and combined file/poster/framing size using sparse fixtures.

## Verification and evidence limitations

The baseline run passed 294 checks. The extended simulator has 321 regular
checks; one additional authentication check remains explicitly optional.
27 new groups cover media limits, failure and retry paths, posters, video
metadata and instance refresh.

`regression-test/media-transport-test.py` starts only a local HTTP test server
and runs `media-transport-test.ph_`. Another 14 checks use real cURL and PHP
stream downloads: the exact byte boundary, oversized responses, chunked
responses, incomplete Content-Length, redirects, redirect loops and an exhausted
total budget. Only the header adapter is supplied by this isolated transport
fixture; no Mastodon credentials are loaded.

```text
python fp-plugins/mastodon/regression-test/media-transport-test.py --php <php.exe> --extension-dir <php/ext>
php fp-plugins/mastodon/regression-test/simulate_mastodon_plugin.ph_ --summary
php -d memory_limit=4800M .dist/phpstan.phar analyse -c .dist/phpstan.neon.dist --memory-limit=4800M --debug --no-progress
php fp-plugins/mastodon/developer-docs/check-consistency.php
```

The PHPStan configuration additionally scans `.setup/lib/permissions.lib.php`
so the existing setup functions are discoverable. This developer analysis
requires the complete source tree, including setup. The optional `.ph_`
simulator must be passed explicitly as a file argument for its own static
analysis; the normal project search discovers PHP files.
Run the simulator and PHPStan sequentially: concurrent symbol discovery can
otherwise select files from a temporary simulator copy that is removed before
analysis finishes.

Runtime checks use PHP 8.5.5; the PHPStan configuration describes PHP 7.2 through
8.5. This does not replace a runtime matrix covering every PHP version.
Video parameters are injected deterministically in the simulator. ffprobe is
not installed on this test system: an actual codec/ffprobe run and uploads to
a production Mastodon instance are outside this evidence. No specific nightly
version is declared fully tested solely on the basis of its version name.

## Functions in the new helper file

All functions below are defined in `fp-plugins/mastodon/inc/media-safety.php`.
The existing main-file catalog in `07-Function-Organigram.md` does not include
them in its counts.

| Function                                    | Responsibility                                            |
| ------------------------------------------- | --------------------------------------------------------- |
| `plugin_mastodon_download_budget_start()`   | Reset the run budget and import error marker.             |
| `plugin_mastodon_download_limit()`          | Read local file, byte and request limits.                 |
| `plugin_mastodon_download_write()`          | Account for transport chunks and write them completely.   |
| `plugin_mastodon_media_origin()`            | Determine an allowed URL origin.                          |
| `plugin_mastodon_media_redirect()`          | Resolve a redirect target.                                |
| `plugin_mastodon_download_to_file()`        | Manage transport, redirects, deadlines and partial files. |
| `plugin_mastodon_probe_video()`             | Run a bounded local ffprobe process and cache its result. |
| `plugin_mastodon_video_rate()`              | Parse numeric or rational frame rates.                    |
| `plugin_mastodon_validate_video_metadata()` | Validate video dimensions and frame rate.                 |
| `plugin_mastodon_refresh_instance_if_due()` | Coordinate refresh and failure cooldown.                  |

| Additional function                        | Responsibility                                                |
| ------------------------------------------ | ------------------------------------------------------------- |
| `plugin_mastodon_memory_limit_bytes()`     | Parse PHP memory quantities with PHP 7.2 compatibility.       |
| `plugin_mastodon_multipart_memory_error()` | Check the complete body cap and remaining PHP memory.         |
| `plugin_mastodon_build_multipart_body()`   | Preflight all parts and assemble a bounded multipart request. |

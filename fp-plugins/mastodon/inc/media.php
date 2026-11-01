<?php
/**
 * Mastodon media declarations. Loaded by plugin.mastodon.php.
 * Runtime constants and registrations remain in the main plugin file.
 */

/**
 * Sanitize a string so it can be used as a path component.
 * @param string $value
 * @return string
 */
function plugin_mastodon_safe_path_component($value) {
	$value = strtolower(trim((string) $value));
	if ($value === '') {
		return 'item';
	}
	$value = preg_replace('/[^a-z0-9._-]+/i', '-', $value);
	$value = trim((string) $value, '-.');
	return $value !== '' ? $value : 'item';
}

/**
 * Sanitize a file name for local storage.
 * @param string $filename
 * @return string
 */
function plugin_mastodon_safe_filename($filename) {
	$filename = trim((string) $filename);
	$filename = str_replace(array('\\', '/'), '-', $filename);
	$filename = preg_replace('/[^A-Za-z0-9._-]+/u', '-', $filename);
	$filename = trim((string) $filename, '-.');
	return $filename !== '' ? $filename : 'file';
}

/**
 * Normalize a FlatPress media path relative to fp-content.
 * @param string $relativePath
 * @return string
 */
function plugin_mastodon_normalize_media_relative_path($relativePath) {
	$relativePath = trim(str_replace('\\', '/', (string) $relativePath));
	if ($relativePath === '' || preg_match('!^(?:https?:)?//!i', $relativePath) || preg_match('!^[a-z][a-z0-9+.-]*:!i', $relativePath)) {
		return '';
	}
	$relativePath = ltrim($relativePath, '/');
	if (strpos($relativePath, 'fp-content/') === 0) {
		$relativePath = substr($relativePath, strlen('fp-content/'));
	}
	$parts = explode('/', $relativePath);
	foreach ($parts as $part) {
		if ($part === '' || $part === '.' || $part === '..') {
			return '';
		}
	}
	return $relativePath;
}

/**
 * Resolve a FlatPress media path to an absolute file path.
 * @param string $relativePath
 * @return string
 */
function plugin_mastodon_media_relative_to_absolute($relativePath) {
	$relativePath = plugin_mastodon_normalize_media_relative_path($relativePath);
	if ($relativePath === '') {
		return '';
	}
	return ABS_PATH . FP_CONTENT . $relativePath;
}

/**
 * Ensure that a media directory exists.
 * @param string $path
 * @return bool
 */
function plugin_mastodon_media_prepare_directory($path) {
	$path = rtrim((string) $path, '/\\');
	if ($path === '') {
		return false;
	}
	if (is_dir($path)) {
		return true;
	}
	return @mkdir($path, DIR_PERMISSIONS, true) || is_dir($path);
}

/**
 * Delete a directory tree used for imported media.
 * @param string $path
 * @return void
 */
function plugin_mastodon_media_delete_tree($path) {
	$path = rtrim((string) $path, '/\\');
	if ($path === '' || (!file_exists($path) && !is_link($path))) {
		return;
	}
	if (is_file($path) || is_link($path)) {
		@unlink($path);
		return;
	}
	$items = @scandir($path);
	if (is_array($items)) {
		foreach ($items as $item) {
			if ($item === '.' || $item === '..') {
				continue;
			}
			plugin_mastodon_media_delete_tree($path . DIRECTORY_SEPARATOR . $item);
		}
	}
	@rmdir($path);
}

/**
 * Copy a directory tree used for media synchronization.
 * @param string $source
 * @param string $target
 * @return bool
 */
function plugin_mastodon_media_copy_tree($source, $target) {
	if (!is_dir($source)) {
		return false;
	}
	if (!plugin_mastodon_media_prepare_directory($target)) {
		return false;
	}
	$items = @scandir($source);
	if (!is_array($items)) {
		return false;
	}
	foreach ($items as $item) {
		if ($item === '.' || $item === '..') {
			continue;
		}
		$sourcePath = $source . DIRECTORY_SEPARATOR . $item;
		$targetPath = $target . DIRECTORY_SEPARATOR . $item;
		if (is_dir($sourcePath)) {
			if (!plugin_mastodon_media_copy_tree($sourcePath, $targetPath)) {
				return false;
			}
			continue;
		}
		if (!@copy($sourcePath, $targetPath)) {
			$data = plugin_mastodon_io_read_file($sourcePath, plugin_mastodon_file_prestat($sourcePath));
			if (!is_string($data) || !plugin_mastodon_io_write_file($targetPath, $data)) {
				return false;
			}
		}
	}
	return true;
}

/**
 * Guess the MIME type of a local media file.
 * @param string $path
 * @return string
 */
function plugin_mastodon_media_guess_mime_type($path) {
	$path = (string) $path;
	if ($path === '') {
		return 'application/octet-stream';
	}
	$signature = (string) @filemtime($path) . ':' . (string) @filesize($path);
	$cacheKey = $path . '|' . $signature;
	$cached = plugin_mastodon_runtime_cache_get('mime_type', $cacheKey, $hit);
	if ($hit && is_string($cached) && $cached !== '') {
		return $cached;
	}
	$fileInfoMime = '';
	if (function_exists('mime_content_type')) {
		$mime = @mime_content_type($path);
		if (is_string($mime) && $mime !== '' && $mime !== 'application/octet-stream') {
			$fileInfoMime = strtolower(trim($mime));
			if (strpos($fileInfoMime, 'image/') === 0 || strpos($fileInfoMime, 'video/') === 0 || strpos($fileInfoMime, 'audio/') === 0) {
				return plugin_mastodon_runtime_cache_set('mime_type', $cacheKey, $fileInfoMime);
			}
		}
	}
	$extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
	$map = array(
		'jpg' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpe' => 'image/jpeg',
		'png' => 'image/png',
		'gif' => 'image/gif',
		'webp' => 'image/webp',
		'avif' => 'image/avif',
		'heic' => 'image/heic',
		'heif' => 'image/heif',
		'bmp' => 'image/bmp',
		'svg' => 'image/svg+xml',
		'mp4' => 'video/mp4',
		'm4v' => 'video/mp4',
		'mov' => 'video/quicktime',
		'qt' => 'video/quicktime',
		'webm' => 'video/webm',
		'ogv' => 'video/ogg',
		'mpg' => 'video/mpeg',
		'mpeg' => 'video/mpeg',
		'avi' => 'video/x-msvideo',
		'asf' => 'video/x-ms-asf',
		'wmv' => 'video/x-ms-wmv',
		'mp3' => 'audio/mpeg',
		'wav' => 'audio/wav',
		'wave' => 'audio/wave',
		'oga' => 'audio/ogg',
		'ogg' => 'audio/ogg',
		'opus' => 'audio/ogg',
		'weba' => 'audio/webm',
		'flac' => 'audio/flac',
		'aac' => 'audio/aac',
		'm4a' => 'audio/m4a',
		'3gp' => 'audio/3gpp'
	);
	$mime = isset($map [$extension]) ? $map [$extension] : ($fileInfoMime !== '' ? $fileInfoMime : 'application/octet-stream');
	return plugin_mastodon_runtime_cache_set('mime_type', $cacheKey, $mime);
}

/**
 * Return a stable FlatPress/Mastodon media family for a MIME type.
 * @param string $mime
 * @param string $path
 * @return string
 */
function plugin_mastodon_media_type_from_mime($mime, $path = '') {
	$mime = strtolower(trim((string) $mime));
	if (strpos($mime, ';') !== false) {
		$parts = explode(';', $mime, 2);
		$mime = trim($parts [0]);
	}
	if (strpos($mime, 'image/') === 0) {
		return 'image';
	}
	if (strpos($mime, 'video/') === 0) {
		return 'video';
	}
	if (strpos($mime, 'audio/') === 0) {
		return 'audio';
	}
	$extension = strtolower((string) pathinfo((string) $path, PATHINFO_EXTENSION));
	if (in_array($extension, array('mp4', 'm4v', 'mov', 'qt', 'webm', 'ogv', 'mpg', 'mpeg', 'avi', 'asf', 'wmv'), true)) {
		return 'video';
	}
	if (in_array($extension, array('mp3', 'wav', 'wave', 'oga', 'ogg', 'opus', 'weba', 'flac', 'aac', 'm4a', '3gp'), true)) {
		return 'audio';
	}
	if (in_array($extension, array('jpg', 'jpeg', 'jpe', 'png', 'gif', 'webp', 'avif', 'heic', 'heif', 'bmp', 'svg'), true)) {
		return 'image';
	}
	return 'unknown';
}

/**
 * Return an appropriate file extension for a MIME type.
 * @param string $mime
 * @param string $fallback
 * @return string
 */
function plugin_mastodon_extension_from_mime_type($mime, $fallback = '') {
	$mime = strtolower(trim((string) $mime));
	if (strpos($mime, ';') !== false) {
		$parts = explode(';', $mime, 2);
		$mime = trim($parts [0]);
	}
	$map = array(
		'image/jpeg' => 'jpg',
		'image/png' => 'png',
		'image/gif' => 'gif',
		'image/webp' => 'webp',
		'image/avif' => 'avif',
		'image/heic' => 'heic',
		'image/heif' => 'heif',
		'video/mp4' => 'mp4',
		'video/quicktime' => 'mov',
		'video/webm' => 'webm',
		'video/ogg' => 'ogv',
		'audio/mpeg' => 'mp3',
		'audio/mp3' => 'mp3',
		'audio/wav' => 'wav',
		'audio/wave' => 'wav',
		'audio/x-wav' => 'wav',
		'audio/x-pn-wave' => 'wav',
		'audio/vnd.wave' => 'wav',
		'audio/ogg' => 'ogg',
		'audio/vorbis' => 'ogg',
		'audio/webm' => 'weba',
		'audio/flac' => 'flac',
		'audio/aac' => 'aac',
		'audio/m4a' => 'm4a',
		'audio/x-m4a' => 'm4a',
		'audio/mp4' => 'm4a',
		'audio/3gpp' => '3gp'
	);
	if (isset($map [$mime])) {
		return $map [$mime];
	}
	$fallback = strtolower(trim((string) $fallback, ". \t\n\r\0\x0B"));
	return preg_match('/^[a-z0-9]{1,8}$/', $fallback) ? $fallback : 'bin';
}

/**
 * Return instance-advertised supported media MIME types.
 * @param array<string, string> $options
 * @return array<int, string>
 */
function plugin_mastodon_instance_supported_media_mime_types($options) {
	$configuration = plugin_mastodon_instance_configuration($options);
	$types = array();
	if (!empty($configuration ['media_attachments'] ['supported_mime_types']) && is_array($configuration ['media_attachments'] ['supported_mime_types'])) {
		foreach ($configuration ['media_attachments'] ['supported_mime_types'] as $type) {
			$type = strtolower(trim((string) $type));
			if ($type !== '') {
				$types [] = $type;
			}
		}
	}
	return array_values(array_unique($types));
}

/**
 * Return the configured byte-size limit for a media family, or 0 if unknown.
 * @param array<string, string> $options
 * @param string $mediaType
 * @return int
 */
function plugin_mastodon_instance_media_size_limit($options, $mediaType) {
	$configuration = plugin_mastodon_instance_configuration($options);
	$media = (!empty($configuration ['media_attachments']) && is_array($configuration ['media_attachments'])) ? $configuration ['media_attachments'] : array();
	$mediaType = strtolower((string) $mediaType);
	if ($mediaType === 'image' && !empty($media ['image_size_limit'])) {
		return max(0, (int) $media ['image_size_limit']);
	}
	if (($mediaType === 'video' || $mediaType === 'gifv') && !empty($media ['video_size_limit'])) {
		return max(0, (int) $media ['video_size_limit']);
	}
	if ($mediaType === 'audio') {
		if (!empty($media ['audio_size_limit'])) {
			return max(0, (int) $media ['audio_size_limit']);
		}
		if (!empty($media ['video_size_limit'])) {
			return max(0, (int) $media ['video_size_limit']);
		}
	}
	return 0;
}

/**
 * Validate a local media item against known instance upload limits.
 * @param array<string, mixed> $item
 * @param array<string, string> $options
 * @return array{ok:bool,reason:string}
 */
function plugin_mastodon_validate_local_media_item($item, $options) {
	if (empty($item ['absolute_path']) || !is_file((string) $item ['absolute_path'])) {
		return array('ok' => false, 'reason' => 'missing_file');
	}
	$filePath = (string) $item ['absolute_path'];
	$mime = !empty($item ['mime_type']) ? strtolower(trim((string) $item ['mime_type'])) : strtolower(plugin_mastodon_media_guess_mime_type($filePath));
	$mediaType = !empty($item ['media_type']) ? strtolower((string) $item ['media_type']) : plugin_mastodon_media_type_from_mime($mime, $filePath);
	if ($mediaType === 'unknown') {
		return array('ok' => false, 'reason' => 'unknown_media_type');
	}
	$supportedTypes = plugin_mastodon_instance_supported_media_mime_types($options);
	if (!empty($supportedTypes) && $mime !== '' && !in_array($mime, $supportedTypes, true)) {
		return array('ok' => false, 'reason' => 'unsupported_mime:' . $mime);
	}
	$limit = plugin_mastodon_instance_media_size_limit($options, $mediaType);
	$fileSize = @filesize($filePath);
	if ($limit > 0 && is_numeric($fileSize) && (int) $fileSize > $limit) {
		return array('ok' => false, 'reason' => 'file_too_large:' . (int) $fileSize . '>' . $limit);
	}
	if ($mediaType === 'video' || $mediaType === 'gifv') {
		$reason = plugin_mastodon_validate_video_metadata($filePath, plugin_mastodon_instance_configuration($options));
		if ($reason !== '') {
			return array('ok' => false, 'reason' => $reason);
		}
	}
	return array('ok' => true, 'reason' => '');
}

/**
 * Parse key/value attributes from a FlatPress media tag.
 * @param string $text
 * @return array<string, string>
 */
function plugin_mastodon_media_parse_tag_attributes($text) {
	$text = (string) $text;
	$attributes = array();
	if ($text === '') {
		return $attributes;
	}
	if (preg_match_all('/([a-z0-9_-]+)\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s\]]+))/iu', $text, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			$key = strtolower((string) $match [1]);
			if ($key === '') {
				continue;
			}
			$value = '';
			if (isset($match [3]) && $match [3] !== '') {
				$value = $match [3];
			} elseif (isset($match [4]) && $match [4] !== '') {
				$value = $match [4];
			} elseif (isset($match [5])) {
				$value = $match [5];
			}
			$attributes [$key] = plugin_mastodon_html_entity_decode(trim((string) $value));
		}
	}
	return $attributes;
}

/**
 * Normalize optional AudioVideo BBCode content into a Mastodon media description.
 * @param mixed $content
 * @return string
 */
function plugin_mastodon_media_description_from_bbcode_content($content) {
	if (!is_scalar($content)) {
		return '';
	}
	$description = trim((string) $content);
	if ($description === '') {
		return '';
	}
	$description = strip_tags($description);
	$withoutBbcode = preg_replace('/\[[^\]]+\]/u', ' ', $description);
	if (is_string($withoutBbcode)) {
		$description = $withoutBbcode;
	}
	$description = plugin_mastodon_html_entity_decode($description);
	$normalized = preg_replace('/\s+/u', ' ', $description);
	if (is_string($normalized)) {
		$description = $normalized;
	}
	return trim($description);
}

/**
 * Extract the default path parameter from a FlatPress media tag attribute string.
 * @param string $attrText
 * @return string
 */
function plugin_mastodon_media_extract_default_path($attrText) {
	$attrText = trim((string) $attrText);
	if ($attrText === '') {
		return '';
	}
	if (preg_match('/^\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s\]]+))/u', $attrText, $match)) {
		if (isset($match [2]) && $match [2] !== '') {
			return plugin_mastodon_html_entity_decode(trim((string) $match [2]));
		}
		if (isset($match [3]) && $match [3] !== '') {
			return plugin_mastodon_html_entity_decode(trim((string) $match [3]));
		}
		if (isset($match [4])) {
			return plugin_mastodon_html_entity_decode(trim((string) $match [4]));
		}
	}
	return '';
}

/**
 * Append a local media item to the collection while avoiding duplicates.
 * @param array<int, array<string, mixed>> $media
 * @param array<string, bool> $seen
 * @param string $relativePath
 * @param string $description
 * @param string $expectedType
 * @param array<string, mixed> $extra
 * @return void
 */
function plugin_mastodon_add_local_media_item(&$media, &$seen, $relativePath, $description = '', $expectedType = '', $extra = array()) {
	$relativePath = plugin_mastodon_normalize_media_relative_path($relativePath);
	if ($relativePath === '') {
		return;
	}
	$absolutePath = plugin_mastodon_media_relative_to_absolute($relativePath);
	$key = strtolower($relativePath);
	if (!is_file($absolutePath) || isset($seen [$key])) {
		return;
	}
	$mimeType = plugin_mastodon_media_guess_mime_type($absolutePath);
	$mediaType = plugin_mastodon_media_type_from_mime($mimeType, $absolutePath);
	$expectedType = strtolower((string) $expectedType);
	if ($expectedType === 'video' && $mediaType === 'gifv') {
		$mediaType = 'video';
	}
	if ($expectedType !== '' && $mediaType !== $expectedType) {
		if ($expectedType === 'video' && strpos($mimeType, 'video/') === 0) {
			$mediaType = 'video';
		} elseif ($expectedType === 'audio' && strpos($mimeType, 'audio/') === 0) {
			$mediaType = 'audio';
		} elseif ($expectedType === 'image' && strpos($mimeType, 'image/') === 0) {
			$mediaType = 'image';
		} else {
			plugin_mastodon_log('Skipping local media ' . $relativePath . ' because MIME type ' . $mimeType . ' does not match expected ' . $expectedType . ' media.');
			return;
		}
	}
	$item = array(
		'relative_path' => $relativePath,
		'absolute_path' => $absolutePath,
		'description' => trim((string) $description),
		'mime_type' => $mimeType,
		'media_type' => $mediaType
	);
	foreach ((array) $extra as $extraKey => $extraValue) {
		if (is_string($extraKey) && $extraKey !== '') {
			$item [$extraKey] = $extraValue;
		}
	}
	$media [] = $item;
	$seen [$key] = true;
}

/**
 * Collect local images, galleries, audio and video referenced by an entry.
 * @param array<string, mixed> $entry
 * @return array<int, array<string, mixed>>
 */
function plugin_mastodon_collect_local_entry_media($entry) {
	$content = isset($entry ['content']) ? (string) $entry ['content'] : '';
	$media = array();
	$seen = array();

	if ($content === '') {
		return $media;
	}

	$cacheKey = sha1($content);
	$cached = plugin_mastodon_runtime_cache_get('entry_media', $cacheKey, $hit);
	if ($hit && is_array($cached)) {
		return $cached;
	}

	if (preg_match_all('/\[\s*gallery\b([^\]]*)\]/iu', $content, $galleryMatches, PREG_SET_ORDER)) {
		foreach ($galleryMatches as $match) {
			$attrText = (string) $match [1];
			$galleryDir = plugin_mastodon_media_extract_default_path($attrText);
			if ($galleryDir === '' && preg_match('/=\s*["\']?([^\s\]"\']+)/u', $attrText, $pathMatch)) {
				$galleryDir = trim((string) $pathMatch [1]);
			}
			$galleryDir = rtrim(plugin_mastodon_normalize_media_relative_path($galleryDir), '/');
			if ($galleryDir === '') {
				continue;
			}
			$galleryPath = plugin_mastodon_media_relative_to_absolute($galleryDir);
			if (!is_dir($galleryPath)) {
				continue;
			}
			$captions = function_exists('gallery_read_captions') ? gallery_read_captions($galleryDir) : array();
			$imageFiles = function_exists('gallery_read_images') ? gallery_read_images($galleryDir) : array();
			if (empty($imageFiles)) {
				$items = @scandir($galleryPath);
				$imageFiles = array();
				if (is_array($items)) {
					foreach ($items as $item) {
						if ($item === '.' || $item === '..' || $item === '.captions.conf' || $item === 'captions.conf' || $item === 'texte.conf') {
							continue;
						}
						if (is_file($galleryPath . DIRECTORY_SEPARATOR . $item)) {
							$imageFiles [] = $item;
						}
					}
				}
				sort($imageFiles);
			}
			foreach ($imageFiles as $imageFile) {
				$relativePath = $galleryDir . '/' . $imageFile;
				$description = isset($captions [$imageFile]) ? trim((string) $captions [$imageFile]) : '';
				plugin_mastodon_add_local_media_item($media, $seen, $relativePath, $description, 'image');
			}
		}
	}

	if (preg_match_all('/\[\s*img\b([^\]]*)\]/iu', $content, $imgMatches, PREG_SET_ORDER)) {
		foreach ($imgMatches as $match) {
			$attrText = (string) $match [1];
			$relativePath = plugin_mastodon_media_extract_default_path($attrText);
			if ($relativePath === '' && preg_match('/=\s*["\']?([^\s\]"\']+)/u', $attrText, $pathMatch)) {
				$relativePath = trim((string) $pathMatch [1]);
			}
			if ($relativePath === '' || preg_match('!^https?://!i', $relativePath)) {
				continue;
			}
			$attributes = plugin_mastodon_media_parse_tag_attributes($attrText);
			$description = '';
			if (!empty($attributes ['title'])) {
				$description = $attributes ['title'];
			} elseif (!empty($attributes ['alt'])) {
				$description = $attributes ['alt'];
			}
			plugin_mastodon_add_local_media_item($media, $seen, $relativePath, $description, 'image');
		}
	}

	if (preg_match_all('/\[img\](.*?)\[\/img\]/isu', $content, $inlineMatches, PREG_SET_ORDER)) {
		foreach ($inlineMatches as $match) {
			$relativePath = trim((string) $match [1]);
			if ($relativePath === '' || preg_match('!^https?://!i', $relativePath)) {
				continue;
			}
			plugin_mastodon_add_local_media_item($media, $seen, $relativePath, '', 'image');
		}
	}

	foreach (array('audioplayer' => 'audio', 'videoplayer' => 'video') as $tagName => $expectedType) {
		if (stripos($content, '[' . $tagName) === false) {
			continue;
		}
		if (!preg_match_all('/\[\s*' . $tagName . '\b([^\]]*)\](?:([\s\S]*?)\[\s*\/\s*' . $tagName . '\s*\])?/iu', $content, $matches, PREG_SET_ORDER)) {
			continue;
		}
		foreach ($matches as $match) {
			$attrText = (string) $match [1];
			$attributes = plugin_mastodon_media_parse_tag_attributes($attrText);
			$relativePath = plugin_mastodon_media_extract_default_path($attrText);
			if ($relativePath === '' && !empty($attributes ['src'])) {
				$relativePath = $attributes ['src'];
			}
			if ($relativePath === '' || preg_match('!^https?://!i', $relativePath)) {
				continue;
			}

			$description = isset($match [2]) ? plugin_mastodon_media_description_from_bbcode_content($match [2]) : '';
			if ($description === '') {
				foreach (array('description', 'title', 'alt') as $descriptionKey) {
					if (!empty($attributes [$descriptionKey])) {
						$description = $attributes [$descriptionKey];
						break;
					}
				}
			}

			$extra = array();
			if ($expectedType === 'video' && !empty($attributes ['poster'])) {
				$posterPath = plugin_mastodon_normalize_media_relative_path($attributes ['poster']);
				$posterAbsolute = $posterPath !== '' ? plugin_mastodon_media_relative_to_absolute($posterPath) : '';
				if ($posterAbsolute !== '' && is_file($posterAbsolute) && plugin_mastodon_media_type_from_mime(plugin_mastodon_media_guess_mime_type($posterAbsolute), $posterAbsolute) === 'image') {
					$extra ['thumbnail_relative_path'] = $posterPath;
					$extra ['thumbnail_absolute_path'] = $posterAbsolute;
					$extra ['thumbnail_mime_type'] = plugin_mastodon_media_guess_mime_type($posterAbsolute);
				}
			}

			plugin_mastodon_add_local_media_item($media, $seen, $relativePath, $description, $expectedType, $extra);
		}
	}

	return plugin_mastodon_runtime_cache_set('entry_media', $cacheKey, $media);
}

/**
 * Select a Mastodon-compatible media set for one status.
 *
 * Mastodon accepts multiple images, but only a single audio or video attachment per status.
 * To avoid 422 responses when a FlatPress entry mixes media classes, the exporter uses one
 * deterministic media family per status: images first, then one audio attachment, then one video.
 * Video poster images remain upload thumbnails on the video item and never become standalone
 * status media IDs through this selector.
 *
 * @param array<int, array<string, mixed>> $mediaItems
 * @param int $limit
 * @param int $skipped
 * @return array<int, array<string, mixed>>
 */
function plugin_mastodon_select_status_media_items($mediaItems, $limit, &$skipped) {
	$mediaItems = is_array($mediaItems) ? $mediaItems : array();
	$limit = max(0, (int) $limit);
	$skipped = max(0, (int) $skipped);
	if (empty($mediaItems)) {
		return array();
	}
	if ($limit < 1) {
		$skipped += count($mediaItems);
		return array();
	}

	$groups = array(
		'image' => array(),
		'audio' => array(),
		'video' => array()
	);
	foreach ($mediaItems as $item) {
		$mediaType = !empty($item ['media_type']) ? strtolower((string) $item ['media_type']) : '';
		if ($mediaType === 'gifv') {
			$mediaType = 'video';
		}
		if (!isset($groups [$mediaType])) {
			$skipped++;
			continue;
		}
		$groups [$mediaType] [] = $item;
	}

	$selected = array();
	$policy = '';
	$policySkipped = 0;
	if (!empty($groups ['image'])) {
		$selected = array_slice($groups ['image'], 0, $limit);
		$policy = 'image';
		$policySkipped = (count($groups ['image']) - count($selected)) + count($groups ['audio']) + count($groups ['video']);
	} elseif (!empty($groups ['audio'])) {
		$selected = array_slice($groups ['audio'], 0, 1);
		$policy = 'audio';
		$policySkipped = (count($groups ['audio']) - count($selected)) + count($groups ['video']);
	} elseif (!empty($groups ['video'])) {
		$selected = array_slice($groups ['video'], 0, 1);
		$policy = 'video';
		$policySkipped = count($groups ['video']) - count($selected);
	}

	if ($policySkipped > 0) {
		$skipped += $policySkipped;
		plugin_mastodon_log('Skipped ' . $policySkipped . ' local media attachment(s) to keep one Mastodon-compatible media family for this status; selected ' . $policy . ' media.');
	}

	return $selected;
}

/**
 * Normalize local entry media items for signature and export planning.
 * @param array<int, array<string, mixed>> $mediaItems
 * @param int $limit
 * @param array<string, string> $options
 * @return array{items:array<int, array<string, mixed>>, skipped:int}
 */
function plugin_mastodon_prepare_entry_media_items($mediaItems, $limit, $options = array()) {
	$mediaItems = is_array($mediaItems) ? $mediaItems : array();
	$options = is_array($options) ? $options : array();
	$limit = max(0, (int) $limit);
	$prepared = array();
	$skipped = 0;
	foreach ($mediaItems as $item) {
		if (empty($item ['absolute_path']) || !is_file((string) $item ['absolute_path'])) {
			continue;
		}
		$validation = plugin_mastodon_validate_local_media_item($item, $options);
		if (empty($validation ['ok'])) {
			$skipped++;
			$relativePath = isset($item ['relative_path']) ? (string) $item ['relative_path'] : (string) $item ['absolute_path'];
			plugin_mastodon_log('Skipped local media attachment ' . $relativePath . ' before upload: ' . (isset($validation ['reason']) ? (string) $validation ['reason'] : 'unsupported'));
			continue;
		}
		if (!empty($item ['thumbnail_absolute_path'])) {
			$poster = array('absolute_path' => $item ['thumbnail_absolute_path'], 'media_type' => 'image');
			$poster ['mime_type'] = plugin_mastodon_media_guess_mime_type((string) $item ['thumbnail_absolute_path']);
			$posterValidation = plugin_mastodon_validate_local_media_item($poster, $options);
			$size = @getimagesize((string) $poster ['absolute_path']);
			$config = plugin_mastodon_instance_configuration($options);
			$posterByteLimit = !empty($config ['media_attachments'] ['image_size_limit']) ? (int) $config ['media_attachments'] ['image_size_limit'] : 16777216;
			$matrixLimit = isset($config ['media_attachments'] ['image_matrix_limit']) ? (int) $config ['media_attachments'] ['image_matrix_limit'] : 33177600;
			if (empty($posterValidation ['ok']) || (float) @filesize((string) $poster ['absolute_path']) > $posterByteLimit || !is_array($size) || ($matrixLimit > 0 && (float) $size [0] * (float) $size [1] > $matrixLimit)) {
				plugin_mastodon_log('Omitting invalid or oversized video poster; the server will generate a preview.');
				unset($item ['thumbnail_absolute_path'], $item ['thumbnail_mime_type']);
			} else { $item ['thumbnail_mime_type'] = $poster ['mime_type']; }
		}
		$prepared [] = $item;
	}
	$prepared = plugin_mastodon_select_status_media_items($prepared, $limit, $skipped);
	return array('items' => $prepared, 'skipped' => $skipped);
}

/**
 * Build a signature for the actual media payload of local entry attachments.
 * @param array<int, array<string, mixed>> $mediaItems
 * @return string
 */
function plugin_mastodon_entry_media_attachment_signature_from_items($mediaItems) {
	$signatureParts = array();
	foreach ((array) $mediaItems as $item) {
		if (empty($item ['absolute_path']) || !is_file((string) $item ['absolute_path'])) {
			continue;
		}
		$signatureParts [] = implode('|', array(
			isset($item ['relative_path']) ? (string) $item ['relative_path'] : '',
			(string) @filesize((string) $item ['absolute_path']),
			(string) @filemtime((string) $item ['absolute_path'])
		));
	}
	if (empty($signatureParts)) {
		return '';
	}
	return sha1(implode("\n", $signatureParts));
}

/**
 * Build a signature for local entry media descriptions.
 * @param array<int, array<string, mixed>> $mediaItems
 * @return string
 */
function plugin_mastodon_entry_media_description_signature_from_items($mediaItems) {
	$signatureParts = array();
	foreach ((array) $mediaItems as $item) {
		$signatureParts [] = implode('|', array(
			isset($item ['relative_path']) ? (string) $item ['relative_path'] : '',
			isset($item ['description']) ? (string) $item ['description'] : ''
		));
	}
	if (empty($signatureParts)) {
		return '';
	}
	return sha1(implode("\n", $signatureParts));
}

/**
 * Build a signature for media references contained in entry content.
 * @param mixed $content
 * @return string
 */
function plugin_mastodon_entry_media_signature($content) {
	$content = (string) $content;
	if ($content === '') {
		return '';
	}
	$media = plugin_mastodon_collect_local_entry_media(array('content' => $content));
	return sha1(implode("\n", array(
		plugin_mastodon_entry_media_attachment_signature_from_items($media),
		plugin_mastodon_entry_media_description_signature_from_items($media)
	)));
}

/**
 * Return the normalized Mastodon attachment type.
 * @param array<string, mixed> $attachment
 * @return string
 */
function plugin_mastodon_remote_media_attachment_type($attachment) {
	$type = !empty($attachment ['type']) ? strtolower(trim((string) $attachment ['type'])) : '';
	if (in_array($type, array('image', 'video', 'gifv', 'audio'), true)) {
		return $type;
	}
	$sourceUrl = plugin_mastodon_remote_media_source_url($attachment);
	if ($sourceUrl !== '') {
		$extension = strtolower((string) pathinfo((string) parse_url($sourceUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
		if (in_array($extension, array('mp4', 'm4v', 'mov', 'qt', 'webm', 'ogv'), true)) {
			return 'video';
		}
		if (in_array($extension, array('mp3', 'wav', 'wave', 'oga', 'ogg', 'opus', 'weba', 'flac', 'aac', 'm4a', '3gp'), true)) {
			return 'audio';
		}
		if (in_array($extension, array('jpg', 'jpeg', 'jpe', 'png', 'gif', 'webp', 'avif', 'heic', 'heif'), true)) {
			return 'image';
		}
	}
	return '';
}

/**
 * Extract supported media attachments from a remote Mastodon status.
 * @param array<string, mixed> $remoteStatus
 * @param array<int, string> $allowedTypes
 * @return array<int, array<string, mixed>>
 */
function plugin_mastodon_remote_status_media_attachments($remoteStatus, $allowedTypes = array()) {
	$attachments = array();
	$allowed = array();
	foreach ((array) $allowedTypes as $allowedType) {
		$allowedType = strtolower((string) $allowedType);
		if ($allowedType !== '') {
			$allowed [$allowedType] = true;
		}
	}
	if (empty($remoteStatus ['media_attachments']) || !is_array($remoteStatus ['media_attachments'])) {
		return $attachments;
	}
	foreach ($remoteStatus ['media_attachments'] as $attachment) {
		if (!is_array($attachment)) {
			continue;
		}
		$type = plugin_mastodon_remote_media_attachment_type($attachment);
		if ($type === '' || (!empty($allowed) && !isset($allowed [$type]))) {
			continue;
		}
		$attachments [] = $attachment;
	}
	return $attachments;
}

/**
 * Resolve the best downloadable source URL for a remote attachment.
 * @param array<string, mixed> $attachment
 * @return string
 */
function plugin_mastodon_remote_media_source_url($attachment) {
	foreach (array('url', 'remote_url', 'preview_url', 'text_url') as $field) {
		if (!empty($attachment [$field]) && is_string($attachment [$field])) {
			$value = trim((string) $attachment [$field]);
			if ($value !== '') {
				return $value;
			}
		}
	}
	return '';
}

/**
 * Return direct-download candidate URLs for a remote media attachment.
 * @param array<string, mixed> $attachment
 * @param string $type
 * @return array<int, string>
 */
function plugin_mastodon_remote_media_source_urls($attachment, $type = '') {
	$type = strtolower((string) $type);
	$fields = ($type === 'audio' || $type === 'video' || $type === 'gifv') ? array('url', 'remote_url') : array('url', 'remote_url', 'preview_url');
	$urls = array();
	foreach ($fields as $field) {
		if (empty($attachment [$field]) || !is_string($attachment [$field])) {
			continue;
		}
		$url = trim((string) $attachment [$field]);
		if ($url === '' || isset($urls [$url])) {
			continue;
		}
		$urls [$url] = $url;
	}
	return array_values($urls);
}

/**
 * Resolve the best description for a remote attachment.
 * @param array<string, mixed> $attachment
 * @return string
 */
function plugin_mastodon_remote_media_description($attachment) {
	foreach (array('description', 'name') as $field) {
		if (!empty($attachment [$field]) && is_string($attachment [$field])) {
			return trim((string) $attachment [$field]);
		}
	}
	return '';
}

/**
 * Resolve the stored focus string for a remote attachment, if any.
 * @param array<string, mixed> $attachment
 * @return string
 */
function plugin_mastodon_remote_media_focus($attachment) {
	$attachment = is_array($attachment) ? $attachment : array();
	if (empty($attachment ['meta'] ['focus']) || !is_array($attachment ['meta'] ['focus'])) {
		return '';
	}
	$focus = $attachment ['meta'] ['focus'];
	if (!isset($focus ['x']) || !isset($focus ['y']) || !is_numeric($focus ['x']) || !is_numeric($focus ['y'])) {
		return '';
	}
	return sprintf('%.6F,%.6F', (float) $focus ['x'], (float) $focus ['y']);
}

/**
 * Build sanitized remote-media descriptors from a Mastodon status payload.
 * @param array<string, mixed> $remoteStatus
 * @return array<int, array<string, string>>
 */
function plugin_mastodon_remote_media_descriptors_from_status($remoteStatus) {
	$remoteMedia = array();
	foreach (plugin_mastodon_remote_status_media_attachments($remoteStatus) as $attachment) {
		$attachmentId = !empty($attachment ['id']) ? trim((string) $attachment ['id']) : '';
		if ($attachmentId === '') {
			continue;
		}
		$descriptor = array(
			'id' => $attachmentId,
			'description' => plugin_mastodon_remote_media_description($attachment)
		);
		$type = plugin_mastodon_remote_media_attachment_type($attachment);
		if ($type !== '') {
			$descriptor ['type'] = $type;
		}
		$focus = plugin_mastodon_remote_media_focus($attachment);
		if ($focus !== '') {
			$descriptor ['focus'] = $focus;
		}
		$remoteMedia [] = $descriptor;
	}
	return $remoteMedia;
}

/**
 * Build remote-media descriptors from freshly uploaded media IDs and the current local descriptions.
 * @param array<int, string> $mediaIds
 * @param array<int, array<string, mixed>> $mediaItems
 * @param array<string, string> $options
 * @return array<int, array<string, string>>
 */
function plugin_mastodon_remote_media_descriptors_from_media_ids($mediaIds, $mediaItems, $options) {
	$descriptionLimit = plugin_mastodon_instance_media_description_limit($options);
	$descriptors = array();
	$mediaIds = is_array($mediaIds) ? array_values($mediaIds) : array();
	$mediaItems = is_array($mediaItems) ? array_values($mediaItems) : array();
	$count = min(count($mediaIds), count($mediaItems));
	for ($index = 0; $index < $count; $index++) {
		$mediaId = trim((string) $mediaIds [$index]);
		if ($mediaId === '') {
			continue;
		}
		$description = isset($mediaItems [$index] ['description']) ? trim((string) $mediaItems [$index] ['description']) : '';
		if ($descriptionLimit > 0 && $description !== '') {
			$description = plugin_mastodon_limit_text($description, $descriptionLimit);
		}
		$descriptor = array(
			'id' => $mediaId,
			'description' => $description
		);
		if (!empty($mediaItems [$index] ['media_type'])) {
			$descriptor ['type'] = (string) $mediaItems [$index] ['media_type'];
		}
		$descriptors [] = $descriptor;
	}
	return $descriptors;
}

/**
 * Build media_attributes descriptors for PUT /api/v1/statuses/:id.
 * @param array<int, array<string, string>> $remoteMedia
 * @param array<int, array<string, mixed>> $mediaItems
 * @param array<string, string> $options
 * @return array<int, array<string, string>>
 */
function plugin_mastodon_status_media_attributes($remoteMedia, $mediaItems, $options) {
	$remoteMedia = is_array($remoteMedia) ? array_values($remoteMedia) : array();
	$mediaItems = is_array($mediaItems) ? array_values($mediaItems) : array();
	$descriptionLimit = plugin_mastodon_instance_media_description_limit($options);
	$attributes = array();
	$count = min(count($remoteMedia), count($mediaItems));
	for ($index = 0; $index < $count; $index++) {
		$mediaId = !empty($remoteMedia [$index] ['id']) ? trim((string) $remoteMedia [$index] ['id']) : '';
		if ($mediaId === '') {
			continue;
		}
		$description = isset($mediaItems [$index] ['description']) ? trim((string) $mediaItems [$index] ['description']) : '';
		if ($descriptionLimit > 0 && $description !== '') {
			$description = plugin_mastodon_limit_text($description, $descriptionLimit);
		}
		$attribute = array(
			'id' => $mediaId,
			'description' => $description
		);
		if (!empty($remoteMedia [$index] ['focus'])) {
			$attribute ['focus'] = trim((string) $remoteMedia [$index] ['focus']);
		}
		$attributes [] = $attribute;
	}
	return $attributes;
}

/**
 * Download a remote media asset into a bounded staging file.
 * @param string $url
 * @param array<int|string, string> $headers
 * @param string $destination
 * @param string $mediaType
 * @return array<string, mixed>
 */
function plugin_mastodon_media_download($url, $headers, $destination, $mediaType = 'video') {
	plugin_mastodon_extend_time_limit(180);
	return plugin_mastodon_download_to_file($url, is_array($headers) ? $headers : array(), $destination, $mediaType);
}

/**
 * Build a safe basename for a downloaded remote attachment.
 * @param array<string, mixed> $attachment
 * @param string $sourceUrl
 * @param array<string, mixed> $download
 * @param int $index
 * @param string $fallbackType
 * @return string
 */
function plugin_mastodon_remote_download_basename($attachment, $sourceUrl, $download, $index, $fallbackType = '') {
	$path = parse_url((string) $sourceUrl, PHP_URL_PATH);
	$basename = is_string($path) ? plugin_mastodon_safe_filename((string) basename($path)) : '';
	if ($basename === '' || strpos($basename, '.') === false) {
		$contentType = !empty($download ['headers'] ['content-type']) ? (string) $download ['headers'] ['content-type'] : '';
		$fallbackExtension = '';
		if (!empty($attachment ['type'])) {
			$fallbackType = (string) $attachment ['type'];
		}
		if ($fallbackType === 'image') {
			$fallbackExtension = 'jpg';
		} elseif ($fallbackType === 'audio') {
			$fallbackExtension = 'mp3';
		} elseif ($fallbackType === 'video' || $fallbackType === 'gifv') {
			$fallbackExtension = 'mp4';
		}
		$extension = plugin_mastodon_extension_from_mime_type($contentType, $fallbackExtension);
		$basename = sprintf('%02d.%s', $index, $extension);
	}
	return sprintf('%02d-', $index) . ltrim($basename, '-');
}

/**
 * Build FlatPress BBCode for imported remote media attachments.
 *
 * Images continue to be imported as PhotoSwipe-compatible [img]/[gallery] markup.
 * Audio and video are imported into fp-content/attachs/ and rendered through the
 * audiovideo plugin's [audioplayer]/[videoplayer] tags.
 *
 * @param array<string, string> $options
 * @param array<string, mixed> $remoteStatus
 * @return string
 */
function plugin_mastodon_build_imported_media_bbcode(&$options, $remoteStatus) {
	$GLOBALS ['plugin_mastodon_media_import_error'] = '';
	$attachments = plugin_mastodon_remote_status_media_attachments($remoteStatus);
	if (empty($attachments)) {
		return '';
	}
	$remoteId = !empty($remoteStatus ['id']) ? plugin_mastodon_safe_path_component($remoteStatus ['id']) : 'status';
	$imageFinalDir = ABS_PATH . IMAGES_DIR . 'mastodon' . DIRECTORY_SEPARATOR . 'status-' . $remoteId;
	$attachFinalDir = ABS_PATH . ATTACHS_DIR . 'mastodon' . DIRECTORY_SEPARATOR . 'status-' . $remoteId;
	$tempRoot = ABS_PATH . PLUGIN_MASTODON_STATE_DIR . 'tmp' . DIRECTORY_SEPARATOR . 'status-' . $remoteId;
	$tempImageDir = $tempRoot . DIRECTORY_SEPARATOR . 'images';
	$tempAttachDir = $tempRoot . DIRECTORY_SEPARATOR . 'attachs';

	plugin_mastodon_media_delete_tree($tempRoot);
	if (!plugin_mastodon_media_prepare_directory($tempImageDir) || !plugin_mastodon_media_prepare_directory($tempAttachDir)) {
		plugin_mastodon_log('Unable to prepare temporary media directories for remote status ' . $remoteId);
		$GLOBALS ['plugin_mastodon_media_import_error'] = 'media_download_open_failed';
		plugin_mastodon_media_delete_tree($tempRoot);
		return '';
	}

	$imageCaptions = array();
	$imageFiles = array();
	$avItems = array();
	$downloadHeaders = array();
	if (!empty($options ['access_token'])) {
		$downloadHeaders [] = 'Authorization: Bearer ' . $options ['access_token'];
	}

	$imageIndex = 0;
	$attachIndex = 0;
	foreach ($attachments as $attachment) {
		$type = plugin_mastodon_remote_media_attachment_type($attachment);
		if ($type === '') {
			continue;
		}
		$sourceUrls = plugin_mastodon_remote_media_source_urls($attachment, $type);
		if (empty($sourceUrls)) {
			continue;
		}
		$sourceUrl = '';
		$download = array('ok' => false, 'code' => 0, 'headers' => array(), 'body' => '', 'error' => 'missing_media_url');
		foreach ($sourceUrls as $candidateUrl) {
			$sourceUrl = (string) $candidateUrl;
			$safeHeaders = plugin_mastodon_media_origin($sourceUrl) === plugin_mastodon_media_origin(isset($options ['instance_url']) ? $options ['instance_url'] : '') ? $downloadHeaders : array();
			$download = plugin_mastodon_media_download($sourceUrl, $safeHeaders, $tempRoot . DIRECTORY_SEPARATOR . 'download.part', $type);
			if (!empty($download ['ok']) && !empty($download ['path'])) {
				break;
			}
			plugin_mastodon_log('Failed to download remote media candidate for status ' . $remoteId . ' from ' . $sourceUrl . ': ' . plugin_mastodon_response_error_message($download));
		}
		if (!$download ['ok'] || empty($download ['path'])) {
			plugin_mastodon_log('Failed to download remote media for status ' . $remoteId . ' from all candidate URLs');
			$GLOBALS ['plugin_mastodon_media_import_error'] = (string) $download ['error'];
			plugin_mastodon_media_delete_tree($tempRoot);
			return '';
		}

		if ($type === 'image') {
			$imageIndex++;
			$basename = plugin_mastodon_remote_download_basename($attachment, $sourceUrl, $download, $imageIndex, 'image');
			if (!@rename((string) $download ['path'], $tempImageDir . DIRECTORY_SEPARATOR . $basename)) {
				plugin_mastodon_log('Failed to store imported remote image file ' . $basename . ' for status ' . $remoteId);
				$GLOBALS ['plugin_mastodon_media_import_error'] = 'media_download_write_failed';
				plugin_mastodon_media_delete_tree($tempRoot);
				return '';
			}
			$imageFiles [] = $basename;
			$description = plugin_mastodon_remote_media_description($attachment);
			if ($description !== '') {
				$imageCaptions [$basename] = $description;
			}
			continue;
		}

		if ($type !== 'audio' && $type !== 'video' && $type !== 'gifv') {
			continue;
		}

		$attachIndex++;
		$basename = plugin_mastodon_remote_download_basename($attachment, $sourceUrl, $download, $attachIndex, $type);
		if (!@rename((string) $download ['path'], $tempAttachDir . DIRECTORY_SEPARATOR . $basename)) {
			plugin_mastodon_log('Failed to store imported remote audio/video file ' . $basename . ' for status ' . $remoteId);
			$GLOBALS ['plugin_mastodon_media_import_error'] = 'media_download_write_failed';
			plugin_mastodon_media_delete_tree($tempRoot);
			return '';
		}

		$posterRelative = '';
		if ($type !== 'audio' && !empty($attachment ['preview_url']) && is_string($attachment ['preview_url'])) {
			$posterUrl = trim((string) $attachment ['preview_url']);
			if ($posterUrl !== '' && $posterUrl !== $sourceUrl) {
				$safeHeaders = plugin_mastodon_media_origin($posterUrl) === plugin_mastodon_media_origin(isset($options ['instance_url']) ? $options ['instance_url'] : '') ? $downloadHeaders : array();
				$posterDownload = plugin_mastodon_media_download($posterUrl, $safeHeaders, $tempRoot . DIRECTORY_SEPARATOR . 'poster.part', 'image');
				if (!empty($posterDownload ['ok']) && !empty($posterDownload ['path'])) {
					$posterBase = preg_replace('/\.[A-Za-z0-9]{1,8}$/', '', $basename);
					$posterExtension = plugin_mastodon_extension_from_mime_type(isset($posterDownload ['headers'] ['content-type']) ? (string) $posterDownload ['headers'] ['content-type'] : '', 'jpg');
					$posterName = plugin_mastodon_safe_filename($posterBase . '-poster.' . $posterExtension);
					if (@rename((string) $posterDownload ['path'], $tempImageDir . DIRECTORY_SEPARATOR . $posterName)) {
						$posterRelative = 'images/mastodon/status-' . $remoteId . '/' . $posterName;
					}
				}
			}
		}

		$avItems [] = array(
			'type' => $type === 'audio' ? 'audio' : 'video',
			'relative_path' => 'attachs/mastodon/status-' . $remoteId . '/' . $basename,
			'poster' => $posterRelative,
			'loop' => $type === 'gifv' ? '1' : '',
			'description' => plugin_mastodon_remote_media_description($attachment)
		);
	}

	$bbcodeParts = array();

	if (!empty($imageFiles)) {
		plugin_mastodon_media_delete_tree($imageFinalDir);
		if (!plugin_mastodon_media_copy_tree($tempImageDir, $imageFinalDir)) {
			plugin_mastodon_media_delete_tree($tempRoot);
			plugin_mastodon_log('Failed to copy imported media into FlatPress images directory for status ' . $remoteId);
			return '';
		}

		if (count($imageFiles) > 1 && !empty($imageCaptions)) {
			$captionLines = array();
			foreach ($imageCaptions as $fileName => $caption) {
				$captionLines [] = $fileName . ' = ' . str_replace(array("\r", "\n"), ' ', $caption);
			}
			plugin_mastodon_io_write_file($imageFinalDir . DIRECTORY_SEPARATOR . '.captions.conf', implode(PHP_EOL, $captionLines) . PHP_EOL);
		}

		$galleryRelative = 'images/mastodon/status-' . $remoteId;
		if (count($imageFiles) > 1) {
			$bbcodeParts [] = '[gallery=' . $galleryRelative . ' width=' . PLUGIN_MASTODON_IMPORTED_MEDIA_WIDTH . ']';
		} else {
			$singleRelative = $galleryRelative . '/' . $imageFiles [0];
			$singleDescription = isset($imageCaptions [$imageFiles [0]]) ? trim((string) $imageCaptions [$imageFiles [0]]) : '';
			$tag = '[img=' . $singleRelative . ' width=' . PLUGIN_MASTODON_IMPORTED_MEDIA_WIDTH;
			if ($singleDescription !== '') {
				$tag .= ' title="' . plugin_mastodon_bbcode_attr_escape($singleDescription) . '"';
			}
			$tag .= ']';
			$bbcodeParts [] = $tag;
		}
	} elseif (!empty($avItems)) {
		$posterItems = @scandir($tempImageDir);
		if (is_array($posterItems) && count($posterItems) > 2) {
			plugin_mastodon_media_delete_tree($imageFinalDir);
			if (!plugin_mastodon_media_copy_tree($tempImageDir, $imageFinalDir)) {
				plugin_mastodon_log('Failed to copy imported video poster images for status ' . $remoteId);
			}
		}
	}

	if (!empty($avItems)) {
		plugin_mastodon_media_delete_tree($attachFinalDir);
		if (!plugin_mastodon_media_copy_tree($tempAttachDir, $attachFinalDir)) {
			plugin_mastodon_media_delete_tree($tempRoot);
			plugin_mastodon_log('Failed to copy imported audio/video media into FlatPress attachs directory for status ' . $remoteId);
			return implode("\n\n", $bbcodeParts);
		}
		foreach ($avItems as $item) {
			$relative = (string) $item ['relative_path'];
			$type = (string) $item ['type'];
			$description = !empty($item ['description']) ? plugin_mastodon_bbcode_text_escape($item ['description']) : '';
			if ($type === 'audio') {
				$tag = '[audioplayer="' . plugin_mastodon_bbcode_attr_escape($relative) . '" controls="1"]';
				if ($description !== '') {
					$tag .= $description . '[/audioplayer]';
				}
				$bbcodeParts [] = $tag;
			} else {
				$tag = '[videoplayer="' . plugin_mastodon_bbcode_attr_escape($relative) . '" controls="1"';
				if (!empty($item ['loop'])) {
					$tag .= ' loop="1"';
				}
				if (!empty($item ['poster'])) {
					$tag .= ' poster="' . plugin_mastodon_bbcode_attr_escape((string) $item ['poster']) . '"';
				}
				$tag .= ']';
				if ($description !== '') {
					$tag .= $description . '[/videoplayer]';
				}
				$bbcodeParts [] = $tag;
			}
		}
	}

	plugin_mastodon_media_delete_tree($tempRoot);
	if (empty($bbcodeParts)) {
		return '';
	}
	return "\n\n" . implode("\n\n", $bbcodeParts);
}

/**
 * Fetch the current processing status of a Mastodon media attachment.
 * @param array<string, string> $options
 * @param string $mediaId
 * @return array<string, mixed>
 */
function plugin_mastodon_fetch_media_attachment($options, $mediaId) {
	return plugin_mastodon_mastodon_json($options, 'GET', '/api/v1/media/' . rawurlencode((string) $mediaId), array(), true);
}

/**
 * Delete an uploaded Mastodon media attachment before it is attached to a final status.
 * @param array<string, string> $options
 * @param string $mediaId
 * @return array<string, mixed>
 */
function plugin_mastodon_delete_media_attachment($options, $mediaId) {
	$mediaId = trim((string) $mediaId);
	if ($mediaId === '') {
		return array('ok' => false, 'code' => 0, 'headers' => array(), 'body' => '', 'error' => 'missing_media_id');
	}
	$supportsMediaDelete = plugin_mastodon_instance_supports_media_delete($options);
	if ($supportsMediaDelete === false) {
		return array('ok' => true, 'code' => 0, 'headers' => array(), 'body' => '', 'error' => 'unsupported_media_delete', 'skipped' => true);
	}
	$response = plugin_mastodon_mastodon_api($options, 'DELETE', '/api/v1/media/' . rawurlencode($mediaId), array(), true);
	if ((isset($response ['code']) ? (int) $response ['code'] : 0) === 404) {
		$response ['ok'] = true;
		$response ['error'] = '';
	}
	return $response;
}

/**
 * Best-effort cleanup for uploaded Mastodon media that never reached a final status request.
 * @param array<string, string> $options
 * @param array<int, string> $mediaIds
 * @return array{ok:bool, deleted:array<int, string>, skipped:array<int, string>, failed:array<string, string>}
 */
function plugin_mastodon_cleanup_uploaded_media($options, $mediaIds) {
	$mediaIds = is_array($mediaIds) ? $mediaIds : array();
	$uniqueIds = array();
	foreach ($mediaIds as $mediaId) {
		$mediaId = trim((string) $mediaId);
		if ($mediaId === '' || isset($uniqueIds [$mediaId])) {
			continue;
		}
		$uniqueIds [$mediaId] = true;
	}
	$mediaIds = array_keys($uniqueIds);
	$deleted = array();
	$skipped = array();
	$failed = array();
	foreach ($mediaIds as $mediaId) {
		plugin_mastodon_extend_time_limit(60);
		$response = plugin_mastodon_delete_media_attachment($options, $mediaId);
		if (!empty($response ['ok'])) {
			if (!empty($response['skipped'])) {
				$skipped [] = $mediaId;
			} else {
				$deleted [] = $mediaId;
			}
			continue;
		}
		$failed[$mediaId] = plugin_mastodon_response_error_message($response);
		plugin_mastodon_log('Uploaded Mastodon media cleanup failed for ' . $mediaId . ': ' . $failed[$mediaId]);
	}
	return array(
		'ok' => empty($failed),
		'deleted' => $deleted,
		'skipped' => $skipped,
		'failed' => $failed
	);
}

/**
 * Determine how patiently the plugin should wait for Mastodon media processing.
 * @param string $mediaType
 * @param int|float|string $fileSize
 * @return int
 */
function plugin_mastodon_media_processing_attempts($mediaType, $fileSize = 0) {
	$mediaType = strtolower((string) $mediaType);
	$fileSize = max(0, (int) $fileSize);
	if ($mediaType === 'video' || $mediaType === 'gifv') {
		$attempts = 60;
		if ($fileSize > 52428800) {
			$attempts = 90;
		} elseif ($fileSize > 10485760) {
			$attempts = 75;
		}
		return $attempts;
	}
	if ($mediaType === 'audio') {
		return $fileSize > 52428800 ? 75 : 60;
	}
	return 12;
}

/**
 * Determine a practical transfer timeout for one media upload.
 * @param string $mediaType
 * @param int|float|string $fileSize
 * @return int
 */
function plugin_mastodon_media_transfer_timeout($mediaType, $fileSize = 0) {
	$mediaType = strtolower((string) $mediaType);
	$fileSize = max(0, (int) $fileSize);
	if ($mediaType === 'video' || $mediaType === 'gifv' || $mediaType === 'audio') {
		if ($fileSize > 52428800) {
			return 300;
		}
		return 180;
	}
	return 90;
}

/**
 * Wait briefly until an asynchronously uploaded Mastodon media attachment is ready.
 * @param array<string, string> $options
 * @param string $mediaId
 * @param int $maxAttempts
 * @param string $mediaType
 * @return array<string, mixed>
 */
function plugin_mastodon_wait_for_media_attachment($options, $mediaId, $maxAttempts = 8, $mediaType = '') {
	$mediaId = trim((string) $mediaId);
	$maxAttempts = max(1, (int) $maxAttempts);
	$mediaType = strtolower((string) $mediaType);
	$lastResponse = array('ok' => false, 'code' => 0, 'headers' => array(), 'body' => '', 'json' => array(), 'error' => 'media_processing_timeout');
	if ($mediaId === '') {
		return $lastResponse;
	}

	for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
		plugin_mastodon_extend_time_limit(($mediaType === 'audio' || $mediaType === 'video' || $mediaType === 'gifv') ? 120 : 60);
		$response = plugin_mastodon_fetch_media_attachment($options, $mediaId);
		$lastResponse = $response;
		if (!empty($response ['ok']) && !empty($response ['json'] ['url'])) {
			return $response;
		}
		$code = isset($response ['code']) ? (int) $response ['code'] : 0;
		$responseType = !empty($response ['json'] ['type']) ? strtolower((string) $response ['json'] ['type']) : $mediaType;
		$hasKnownAttachment = !empty($response ['json'] ['id']) && (string) $response ['json'] ['id'] === $mediaId;
		$stillProcessing = ($code === 202 || $code === 206) || (!empty($response ['ok']) && empty($response ['json'] ['url']) && (!empty($response ['json'] ['preview_url']) || $hasKnownAttachment || $responseType === 'audio' || $responseType === 'video' || $responseType === 'gifv'));
		if (!$stillProcessing) {
			return $response;
		}
		if ($attempt >= $maxAttempts || isset($GLOBALS ['plugin_mastodon_test_http_responses'])) {
			continue;
		}
		$retryAfter = min(5.0, 0.5 + ($attempt * 0.25));
		if (!empty($response ['headers'] ['retry-after'])) {
			$retryAfter = max(0.1, min(5.0, (float) $response ['headers'] ['retry-after']));
		}
		if (function_exists('usleep')) {
			usleep((int) round($retryAfter * 1000000));
		}
	}

	$lastResponse ['ok'] = false;
	$lastResponse ['error'] = 'media_processing_timeout';
	return $lastResponse;
}

/**
 * Upload local media items to Mastodon and collect the created media IDs.
 *
 * Note: Media uploads may intentionally fall back to text-only export when the current token lacks write:media.
 * @param array<string, string> $options
 * @param array<int, array<string, mixed>> $mediaItems
 * @param int $limit
 * @return array<string, mixed>
 */
function plugin_mastodon_upload_media_items($options, $mediaItems, $limit) {
	$mediaIds = array();
	$mediaItems = is_array($mediaItems) ? $mediaItems : array();
	$limit = max(0, (int) $limit);
	if ($limit < 1 || empty($mediaItems)) {
		return array('ok' => true, 'media_ids' => array(), 'uploaded' => 0, 'skipped' => 0, 'error' => '');
	}

	$descriptionLimit = plugin_mastodon_instance_media_description_limit($options);
	$skipped = 0;
	foreach ($mediaItems as $index => $item) {
		if (count($mediaIds) >= $limit) {
			$skipped++;
			continue;
		}
		if (empty($item ['absolute_path']) || !is_file($item ['absolute_path'])) {
			continue;
		}
		$filePath = (string) $item ['absolute_path'];
		$fileMime = !empty($item ['mime_type']) ? strtolower(trim((string) $item ['mime_type'])) : plugin_mastodon_media_guess_mime_type($filePath);
		$mediaType = !empty($item ['media_type']) ? strtolower((string) $item ['media_type']) : plugin_mastodon_media_type_from_mime($fileMime, $filePath);
		$fileSize = (int) @filesize($filePath);
		$description = isset($item ['description']) ? trim((string) $item ['description']) : '';
		if ($descriptionLimit > 0 && $description !== '') {
			$description = plugin_mastodon_limit_text($description, $descriptionLimit);
		}
		$fields = array(
			'file' => array(
				'__file_path' => $filePath,
				'__file_name' => basename($filePath),
				'__mime_type' => $fileMime
			)
		);
		if (!empty($item ['thumbnail_absolute_path']) && is_file((string) $item ['thumbnail_absolute_path'])) {
			$thumbnailPath = (string) $item ['thumbnail_absolute_path'];
			$thumbnailMime = !empty($item ['thumbnail_mime_type']) ? (string) $item ['thumbnail_mime_type'] : plugin_mastodon_media_guess_mime_type($thumbnailPath);
			if (plugin_mastodon_media_type_from_mime($thumbnailMime, $thumbnailPath) === 'image') {
				$fields ['thumbnail'] = array(
					'__file_path' => $thumbnailPath,
					'__file_name' => basename($thumbnailPath),
					'__mime_type' => $thumbnailMime
				);
			}
		}
		if ($description !== '') {
			$fields ['description'] = $description;
		}
		$headers = array('Accept: application/json');
		if (!empty($options ['access_token'])) {
			$headers [] = 'Authorization: Bearer ' . $options ['access_token'];
		}
		$rateLimitReason = plugin_mastodon_rate_limit_acquire('POST', '/api/v2/media');
		if ($rateLimitReason !== '') {
			$response = plugin_mastodon_rate_limit_blocked_response($rateLimitReason);
		} else {
			$response = plugin_mastodon_http_request_multipart('POST', plugin_mastodon_normalize_instance_url(isset($options ['instance_url']) ? $options ['instance_url'] : '') . '/api/v2/media', $headers, $fields, plugin_mastodon_media_transfer_timeout($mediaType, $fileSize));
			plugin_mastodon_rate_limit_observe_response($response);
		}
		$data = json_decode(isset($response ['body']) ? $response ['body'] : '', true);
		if (!is_array($data)) {
			$data = array();
		}
		$response ['json'] = $data;
		$currentMediaId = !empty($data ['id']) ? trim((string) $data ['id']) : '';
		if (!$response ['ok'] || $currentMediaId === '') {
			$cleanupIds = $mediaIds;
			if ($currentMediaId !== '') {
				$cleanupIds [] = $currentMediaId;
			}
			if (!empty($cleanupIds)) {
				plugin_mastodon_cleanup_uploaded_media($options, $cleanupIds);
			}
			return array(
				'ok' => false,
				'media_ids' => $mediaIds,
				'uploaded' => count($mediaIds),
				'skipped' => $skipped,
				'error' => plugin_mastodon_response_error_message($response)
			);
		}
		if ((isset($response ['code']) ? (int) $response ['code'] : 0) === 202 || (empty($data ['url']) && !empty($data ['preview_url']))) {
			$ready = plugin_mastodon_wait_for_media_attachment($options, $currentMediaId, plugin_mastodon_media_processing_attempts($mediaType, $fileSize), $mediaType);
			if (empty($ready ['ok']) || empty($ready ['json'] ['url'])) {
				$cleanupIds = $mediaIds;
				$cleanupIds [] = $currentMediaId;
				plugin_mastodon_cleanup_uploaded_media($options, $cleanupIds);
				return array(
					'ok' => false,
					'media_ids' => $mediaIds,
					'uploaded' => count($mediaIds),
					'skipped' => $skipped,
					'error' => plugin_mastodon_response_error_message($ready)
				);
			}
		}
		$mediaIds [] = $currentMediaId;
	}
	if ($skipped > 0) {
		plugin_mastodon_log('Skipped ' . $skipped . ' local media attachment(s) because the Mastodon instance allows only ' . $limit . ' attachment(s) per status.');
	}
	return array('ok' => true, 'media_ids' => $mediaIds, 'uploaded' => count($mediaIds), 'skipped' => $skipped, 'error' => '');
}

/**
 * Decide whether a local entry update can reuse already-uploaded Mastodon media or needs a fresh upload.
 *
 * The planner distinguishes between attachment-content changes and description-only changes.
 * When the actual files are unchanged, the plugin can reuse the stored Mastodon media IDs.
 * On instances that support `media_attributes` (Mastodon 4.1+), description-only edits can
 * be applied in-place; older or unknown instances safely fall back to re-uploading the media.
 *
 * @param array<string, string> $options
 * @param array<string, mixed> $entryMeta
 * @param array<int, array<string, mixed>> $mediaItems
 * @param int $mediaLimit
 * @return array<string, mixed>
 */
function plugin_mastodon_prepare_entry_media_sync_plan($options, $entryMeta, $mediaItems, $mediaLimit) {
	$prepared = plugin_mastodon_prepare_entry_media_items($mediaItems, $mediaLimit, $options);
	$effectiveMediaItems = isset($prepared ['items']) && is_array($prepared ['items']) ? $prepared ['items'] : array();
	$skipped = isset($prepared ['skipped']) ? (int) $prepared ['skipped'] : 0;
	$attachmentSignature = plugin_mastodon_entry_media_attachment_signature_from_items($effectiveMediaItems);
	$descriptionSignature = plugin_mastodon_entry_media_description_signature_from_items($effectiveMediaItems);
	$remoteMedia = plugin_mastodon_state_entry_remote_media($entryMeta);
	$remoteMediaIds = array();
	foreach ($remoteMedia as $remoteMediaItem) {
		if (!empty($remoteMediaItem ['id'])) {
			$remoteMediaIds [] = (string) $remoteMediaItem ['id'];
		}
	}

	$storedAttachmentSignature = plugin_mastodon_state_entry_media_attachment_signature($entryMeta);
	$storedDescriptionSignature = plugin_mastodon_state_entry_media_description_signature($entryMeta);
	$descriptionChanged = ($storedDescriptionSignature !== $descriptionSignature);
	$attachmentsChanged = ($storedAttachmentSignature !== $attachmentSignature);

	if (empty($effectiveMediaItems)) {
		return array(
			'mode' => 'none',
			'media_items' => array(),
			'media_ids' => array(),
			'media_attributes' => array(),
			'remote_media' => array(),
			'attachment_signature' => '',
			'description_signature' => '',
			'skipped' => $skipped,
			'description_changed' => false,
			'attachments_changed' => false
		);
	}

	$canReuseRemoteMedia = !empty($remoteMediaIds)
		&& count($remoteMediaIds) === count($effectiveMediaItems)
		&& $storedAttachmentSignature !== ''
		&& !$attachmentsChanged;

	if (!$canReuseRemoteMedia) {
		return array(
			'mode' => 'upload',
			'media_items' => $effectiveMediaItems,
			'media_ids' => array(),
			'media_attributes' => array(),
			'remote_media' => array(),
			'attachment_signature' => $attachmentSignature,
			'description_signature' => $descriptionSignature,
			'skipped' => $skipped,
			'description_changed' => $descriptionChanged,
			'attachments_changed' => $attachmentsChanged
		);
	}

	if ($descriptionChanged && !plugin_mastodon_instance_supports_status_media_attributes($options)) {
		return array(
			'mode' => 'upload',
			'media_items' => $effectiveMediaItems,
			'media_ids' => array(),
			'media_attributes' => array(),
			'remote_media' => array(),
			'attachment_signature' => $attachmentSignature,
			'description_signature' => $descriptionSignature,
			'skipped' => $skipped,
			'description_changed' => true,
			'attachments_changed' => false
		);
	}

	return array(
		'mode' => 'reuse',
		'media_items' => $effectiveMediaItems,
		'media_ids' => $remoteMediaIds,
		'media_attributes' => $descriptionChanged ? plugin_mastodon_status_media_attributes($remoteMedia, $effectiveMediaItems, $options) : array(),
		'remote_media' => $remoteMedia,
		'attachment_signature' => $attachmentSignature,
		'description_signature' => $descriptionSignature,
		'skipped' => $skipped,
		'description_changed' => $descriptionChanged,
		'attachments_changed' => false
	);
}
?>

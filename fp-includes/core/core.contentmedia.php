<?php
/**
 * FlatPress content media resolver.
 *
 * Provides side-effect-free discovery of the first original image represented
 * by active BBCode/PhotoSwipe media tags. Consumers such as SEO metadata and
 * sitemap generation share this implementation so thumbnail and gallery
 * semantics cannot drift apart.
 */

/**
 * Return an empty content-image metadata structure.
 *
 * @return array
 */
function content_media_empty_image_meta() {
	return array(
		'url' => '',
		'secure_url' => '',
		'mime' => '',
		'width' => 0,
		'height' => 0,
		'alt' => '',
		'relative_path' => '',
		'absolute_path' => '',
		'type' => 0,
		'mtime' => 0,
		'size_bytes' => 0
	);
}

/**
 * Normalize a user-provided image title/caption for image metadata.
 *
 * BBCode title attributes and Gallery captions values may already contain
 * HTML entities. Decode at most twice (matching PhotoSwipe's compatibility
 * handling), remove markup and line breaks, and leave final HTML escaping to
 * the meta-tag output layer.
 *
 * @param mixed $value
 * @return string
 */
function content_media_normalize_image_alt($value) {
	if (!is_scalar($value)) {
		return '';
	}

	$charset = 'UTF-8';
	if (isset($GLOBALS ['fp_config']) && is_array($GLOBALS ['fp_config'])
		&& isset($GLOBALS ['fp_config'] ['locale']) && is_array($GLOBALS ['fp_config'] ['locale'])
		&& isset($GLOBALS ['fp_config'] ['locale'] ['charset'])
		&& is_string($GLOBALS ['fp_config'] ['locale'] ['charset'])
		&& trim($GLOBALS ['fp_config'] ['locale'] ['charset']) !== '') {
		$charset = strtoupper(trim($GLOBALS ['fp_config'] ['locale'] ['charset']));
	}

	$text = (string)$value;
	for ($i = 0; $i < 2; $i++) {
		$decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, $charset);
		if ($decoded === $text) {
			break;
		}
		$text = $decoded;
	}

	$text = strip_tags($text);
	$text = str_replace(array("\r", "\n"), ' ', $text);

	return trim($text);
}

/**
 * Remove a global key without teaching static analysis that the key is absent.
 *
 * FPDB_Query updates $GLOBALS['current_query'] as a constructor side effect,
 * which PHPStan cannot infer from the caller.
 *
 * @param string $key
 * @return void
 */
function content_media_unset_global($key) {
	unset($GLOBALS [$key]);
}

/**
 * Store one media occurrence during a side-effect-free BBCode probe.
 *
 * @param string $tag
 * @param string|null $action
 * @param mixed $attributes
 * @return string|true
 */
function content_media_probe_media_callback($tag, $action, $attributes) {
	if ($action === 'validate') {
		return true;
	}

	if (!isset($GLOBALS ['content_media_probe_context']) || !is_array($GLOBALS ['content_media_probe_context'])) {
		return '';
	}

	$context = &$GLOBALS ['content_media_probe_context'];
	$context ['counter'] = isset($context ['counter']) ? ((int)$context ['counter'] + 1) : 1;
	$marker = '__FPCONTENTMEDIA_' . $context ['nonce'] . '_' . str_pad((string)$context ['counter'], 6, '0', STR_PAD_LEFT) . '__';

	$context ['tokens'] [] = array(
		'tag' => strtolower((string)$tag),
		'attributes' => is_array($attributes) ? $attributes : array(),
		'marker' => $marker
	);

	return $marker;
}

function content_media_probe_img($action, $attributes, $content, $params, $node_object) {
	return content_media_probe_media_callback('img', $action, $attributes);
}

function content_media_probe_photoswipeimage($action, $attributes, $content, $params, $node_object) {
	return content_media_probe_media_callback('photoswipeimage', $action, $attributes);
}

function content_media_probe_gallery($action, $attributes, $content, $params, $node_object) {
	return content_media_probe_media_callback('gallery', $action, $attributes);
}

function content_media_probe_photoswipegallery($action, $attributes, $content, $params, $node_object) {
	return content_media_probe_media_callback('photoswipegallery', $action, $attributes);
}

/**
 * Replace an existing BBCode definition on a cloned parser while preserving
 * its callback parameters, content type, nesting rules and flags.
 *
 * @param object $parser
 * @param string $tag
 * @param string $callback
 * @return bool
 */
function content_media_probe_replace_code($parser, $tag, $callback) {
	if (!is_object($parser) || !isset($parser->_codes) || !is_array($parser->_codes) || !isset($parser->_codes [$tag])) {
		return false;
	}

	$definition = $parser->_codes [$tag];
	if (!is_array($definition)) {
		return false;
	}

	$required = array('callback_type', 'callback_params', 'content_type', 'allowed_within', 'not_allowed_within');
	foreach ($required as $key) {
		if (!array_key_exists($key, $definition)) {
			return false;
		}
	}

	$flags = isset($definition ['flags']) && is_array($definition ['flags']) ? $definition ['flags'] : array();

	$parser->removeCode($tag);
	$added = $parser->addCode(
		$tag,
		$definition ['callback_type'],
		$callback,
		$definition ['callback_params'],
		$definition ['content_type'],
		$definition ['allowed_within'],
		$definition ['not_allowed_within']
	);

	if (!$added) {
		return false;
	}

	foreach ($flags as $flag => $value) {
		$parser->setCodeFlag($tag, $flag, $value);
	}

	return true;
}

/**
 * Parse content with the active BBCode grammar, replacing only actually
 * registered media tags by unique markers.
 *
 * @param string $content
 * @return array{html:string,tokens:array}
 */
function content_media_probe_media($content) {
	$result = array(
		'html' => '',
		'tokens' => array()
	);

	$content = (string)$content;
	if ($content === '' || !function_exists('plugin_bbcode_init')) {
		return $result;
	}

	$baseParser = &plugin_bbcode_init();
	if (!is_object($baseParser) || !method_exists($baseParser, 'parse')) {
		return $result;
	}

	$parser = clone $baseParser;
	$callbacks = array(
		'img' => 'content_media_probe_img',
		'photoswipeimage' => 'content_media_probe_photoswipeimage',
		'gallery' => 'content_media_probe_gallery',
		'photoswipegallery' => 'content_media_probe_photoswipegallery'
	);

	$replaced = 0;
	foreach ($callbacks as $tag => $callback) {
		if (content_media_probe_replace_code($parser, $tag, $callback)) {
			$replaced++;
		}
	}

	if ($replaced < 1) {
		return $result;
	}

	$hadPreviousContext = array_key_exists('content_media_probe_context', $GLOBALS);
	$previousContext = $hadPreviousContext ? $GLOBALS ['content_media_probe_context'] : null;

	$GLOBALS ['content_media_probe_context'] = array(
		'counter' => 0,
		'nonce' => substr(sha1($content), 0, 12),
		'tokens' => array()
	);

	try {
		$parsed = $parser->parse($content);
		$result ['html'] = is_string($parsed) ? $parsed : '';
		$probeContext = $GLOBALS ['content_media_probe_context'];
		$result ['tokens'] = $probeContext ['tokens'];
	} finally {
		if ($hadPreviousContext) {
			$GLOBALS ['content_media_probe_context'] = $previousContext;
		} else {
			unset($GLOBALS ['content_media_probe_context']);
		}
	}

	return $result;
}

/**
 * Validate a remote image URL without fetching it.
 *
 * @param string $url
 * @return array
 */
function content_media_remote_image_meta($url) {
	$empty = content_media_empty_image_meta();
	$url = trim((string)$url);
	if ($url === '') {
		return $empty;
	}

	$url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
	if (strpos($url, 'www.') === 0) {
		$url = 'https://' . $url;
	}

	if (preg_match('/[\x00-\x1F\x7F]/', $url)) {
		return $empty;
	}

	$parts = parse_url($url);
	if ($parts === false || empty($parts ['scheme']) || empty($parts ['host'])) {
		return $empty;
	}

	$scheme = strtolower((string)$parts ['scheme']);
	if ($scheme !== 'http' && $scheme !== 'https') {
		return $empty;
	}

	return array(
		'url' => $url,
		'secure_url' => $scheme === 'https' ? $url : '',
		'mime' => '',
		'width' => 0,
		'height' => 0,
		'alt' => '',
		'relative_path' => '',
		'absolute_path' => '',
		'type' => 0,
		'mtime' => 0,
		'size_bytes' => 0
	);
}

/**
 * Return true when a canonical filesystem path is inside another canonical
 * directory path.
 *
 * @param string $path
 * @param string $root
 * @return bool
 */
function content_media_path_is_within($path, $root) {
	$path = str_replace('\\', '/', (string)$path);
	$root = rtrim(str_replace('\\', '/', (string)$root), '/');

	if ($path === '' || $root === '') {
		return false;
	}

	return $path === $root || strpos($path, $root . '/') === 0;
}

/**
 * Normalize the source of a local [img] tag to the FlatPress images directory.
 *
 * @param string $source
 * @return string
 */
function content_media_normalize_local_image_path($source) {
	$source = html_entity_decode(trim((string)$source), ENT_QUOTES, 'UTF-8');
	$source = str_replace('\\', '/', $source);

	if ($source === '' || preg_match('/[\x00-\x1F\x7F]/', $source)) {
		return '';
	}

	// Query strings/fragments are not part of local FlatPress image filenames.
	if (strpos($source, '?') !== false || strpos($source, '#') !== false) {
		return '';
	}

	while (strpos($source, './') === 0) {
		$source = substr($source, 2);
	}

	$source = ltrim($source, '/');

	if (defined('BLOG_ROOT')) {
		$blogRoot = trim(str_replace('\\', '/', (string)BLOG_ROOT), '/');
		if ($blogRoot !== '' && strpos($source, $blogRoot . '/') === 0) {
			$source = substr($source, strlen($blogRoot) + 1);
		}
	}

	$segments = explode('/', $source);
	$cleanSegments = array();
	foreach ($segments as $segment) {
		if ($segment === '' || $segment === '.') {
			continue;
		}
		if ($segment === '..') {
			return '';
		}
		$cleanSegments [] = $segment;
	}
	$source = implode('/', $cleanSegments);

	if ($source === '') {
		return '';
	}

	$imagesDir = defined('IMAGES_DIR') ? trim(str_replace('\\', '/', (string)IMAGES_DIR), '/') . '/' : 'fp-content/images/';
	if (strpos($source, 'images/') === 0) {
		return $imagesDir . substr($source, 7);
	}

	$imagesDirNoSlash = rtrim($imagesDir, '/');
	if ($source === $imagesDirNoSlash || strpos($source, $imagesDir) === 0) {
		return $source;
	}

	return '';
}

/**
 * Resolve one local image to validated source metadata.
 *
 * The direct original URL is retained while filesystem/type metadata lets
 * consumers validate or transform the source. Such transformations must not
 * change which original image was selected.
 *
 * @param string $source
 * @param string $baseUrl
 * @return array
 */
function content_media_local_image_meta($source, $baseUrl) {
	$empty = content_media_empty_image_meta();
	$relativePath = content_media_normalize_local_image_path($source);
	if ($relativePath === '' || !defined('ABS_PATH')) {
		return $empty;
	}

	$imagesDir = defined('IMAGES_DIR') ? (string)IMAGES_DIR : 'fp-content/images/';
	$imagesRoot = @realpath(ABS_PATH . $imagesDir);
	$absolutePath = @realpath(ABS_PATH . $relativePath);

	if ($imagesRoot === false || $absolutePath === false) {
		return $empty;
	}
	if (!content_media_path_is_within($absolutePath, $imagesRoot)) {
		return $empty;
	}
	if (!is_file($absolutePath) || !is_readable($absolutePath)) {
		return $empty;
	}

	$size = @getimagesize($absolutePath);
	if (!is_array($size) || empty($size [0]) || empty($size [1])) {
		return $empty;
	}

	$mime = strtolower(trim((string)$size ['mime']));
	if ($mime === '' || strpos($mime, 'image/') !== 0) {
		return $empty;
	}

	$type = (int)$size [2];
	$stat = @stat($absolutePath);
	$mtime = is_array($stat) && isset($stat ['mtime']) ? (int)$stat ['mtime'] : 0;
	$sizeBytes = is_array($stat) && isset($stat ['size']) ? (int)$stat ['size'] : 0;

	$baseUrl = trim((string)$baseUrl);
	if ($baseUrl === '' && defined('BLOG_BASEURL')) {
		$baseUrl = (string)BLOG_BASEURL;
	}

	$url = $baseUrl === ''
		? $relativePath
		: rtrim($baseUrl, '/') . '/' . ltrim($relativePath, '/');

	return array(
		'url' => $url,
		'secure_url' => stripos($url, 'https://') === 0 ? $url : '',
		'mime' => $mime,
		'width' => (int)$size [0],
		'height' => (int)$size [1],
		'alt' => '',
		'relative_path' => $relativePath,
		'absolute_path' => $absolutePath,
		'type' => $type,
		'mtime' => $mtime,
		'size_bytes' => $sizeBytes
	);
}

/**
 * Resolve an [img]/[photoswipeimage] source.
 *
 * @param string $source
 * @param string $baseUrl
 * @return array
 */
function content_media_image_meta($source, $baseUrl) {
	$source = trim((string)$source);
	if ($source === '') {
		return content_media_empty_image_meta();
	}

	$decoded = html_entity_decode($source, ENT_QUOTES, 'UTF-8');
	if (preg_match('~^(?:https?://|www\.)~i', $decoded)) {
		return content_media_remote_image_meta($decoded);
	}

	return content_media_local_image_meta($decoded, $baseUrl);
}

/**
 * Rehydrate an explicitly requested local content source for the dynamic
 * content image endpoint.
 *
 * The query helper also accepts literal HTML-escaped parameter names such as
 * "amp;seometa_ogsource". Presence is kept separate from validity so an
 * explicit invalid source cannot silently fall back to the theme preview.
 *
 * @param string $baseUrl
 * @return array{requested:bool,image_info:array}
 */
function content_media_gallery_meta($source, $baseUrl) {
	$empty = content_media_empty_image_meta();
	$source = html_entity_decode(trim((string)$source), ENT_QUOTES, 'UTF-8');
	$source = str_replace('\\', '/', $source);

	if ($source === '' || preg_match('/[\x00-\x1F\x7F]/', $source) || preg_match('~^[a-z][a-z0-9+.-]*:~i', $source)) {
		return $empty;
	}

	$source = ltrim($source, '/');
	while (strpos($source, './') === 0) {
		$source = substr($source, 2);
	}

	$segments = explode('/', $source);
	$clean = array();
	foreach ($segments as $segment) {
		if ($segment === '' || $segment === '.') {
			continue;
		}
		if ($segment === '..') {
			return $empty;
		}
		$clean [] = $segment;
	}
	$source = implode('/', $clean);

	// PhotoSwipe's gallery implementation is defined for images/<gallery>.
	if ($source === '' || strpos($source, 'images/') !== 0 || !function_exists('gallery_read_images')) {
		return $empty;
	}

	$galleryDir = rtrim($source, '/') . '/';
	if (!defined('ABS_PATH') || !defined('IMAGES_DIR')) {
		return $empty;
	}

	$imagesRoot = @realpath(ABS_PATH . IMAGES_DIR);
	$galleryRelative = IMAGES_DIR . substr(rtrim($source, '/'), 7);
	$galleryAbsolute = @realpath(ABS_PATH . $galleryRelative);

	if ($imagesRoot === false || $galleryAbsolute === false || !is_dir($galleryAbsolute)) {
		return $empty;
	}
	if (!content_media_path_is_within($galleryAbsolute, $imagesRoot)) {
		return $empty;
	}

	$imageFiles = gallery_read_images($galleryDir);
	if (!is_array($imageFiles)) {
		return $empty;
	}

	foreach ($imageFiles as $file) {
		if (!is_scalar($file)) {
			continue;
		}
		$file = (string)$file;
		if ($file === '' || basename($file) !== $file) {
			continue;
		}

		$meta = content_media_image_meta($galleryDir . $file, $baseUrl);
		if (empty($meta ['url'])) {
			continue;
		}

		// Bind the caption to the exact valid image selected above. A missing
		// caption never changes image selection; output_metatags() will fall back
		// to the configured site title.
		if (function_exists('gallery_read_captions')) {
			$captions = gallery_read_captions($galleryDir);
			if (is_array($captions) && array_key_exists($file, $captions)) {
				$meta ['alt'] = content_media_normalize_image_alt($captions [$file]);
			}
		}

		return $meta;
	}

	return $empty;
}

/**
 * Resolve one media token collected by the BBCode probe.
 *
 * @param array $token
 * @param string $baseUrl
 * @return array
 */
function content_media_resolve_token($token, $baseUrl) {
	$empty = content_media_empty_image_meta();
	if (!is_array($token) || empty($token ['tag']) || empty($token ['attributes']) || !is_array($token ['attributes'])) {
		return $empty;
	}

	$attributes = $token ['attributes'];
	if (!isset($attributes ['default']) || !is_scalar($attributes ['default'])) {
		return $empty;
	}

	$source = trim((string)$attributes ['default']);
	if ($source === '') {
		return $empty;
	}

	$tag = strtolower((string)$token ['tag']);
	if ($tag === 'gallery' || $tag === 'photoswipegallery') {
		return content_media_gallery_meta($source, $baseUrl);
	}
	if ($tag === 'img' || $tag === 'photoswipeimage') {
		$meta = content_media_image_meta($source, $baseUrl);
		if (!empty($meta ['url']) && array_key_exists('title', $attributes)) {
			$meta ['alt'] = content_media_normalize_image_alt($attributes ['title']);
		}
		return $meta;
	}

	return $empty;
}

/**
 * Find the first valid original image in one content string.
 *
 * @param string $content
 * @param string $baseUrl
 * @param bool $applyReadMore Whether stream visibility rules must be applied
 * @return array
 */
function content_media_find_first_image_meta($content, $baseUrl, $applyReadMore) {
	$empty = content_media_empty_image_meta();
	$probe = content_media_probe_media((string)$content);

	if (empty($probe ['tokens']) || !is_array($probe ['tokens'])) {
		return $empty;
	}

	$visibleProbe = isset($probe ['html']) && is_string($probe ['html']) ? $probe ['html'] : '';
	if ($applyReadMore && !isset($_GET ['page']) && function_exists('plugin_readmore_get_stream_excerpt')) {
		$excerpt = plugin_readmore_get_stream_excerpt($visibleProbe);
		if (is_array($excerpt) && isset($excerpt ['content'])) {
			$visibleProbe = (string)$excerpt ['content'];
		}
	}

	foreach ($probe ['tokens'] as $token) {
		if (!is_array($token) || empty($token ['marker'])) {
			continue;
		}

		// Tokens are collected in parser/source order. Once ReadMore removed a
		// marker, all following media occurrences are outside the visible prefix.
		if (strpos($visibleProbe, (string)$token ['marker']) === false) {
			if ($applyReadMore) {
				break;
			}
			continue;
		}

		$meta = content_media_resolve_token($token, $baseUrl);
		if (!empty($meta ['url'])) {
			return $meta;
		}
	}

	return $empty;
}

/**
 * Read the raw content of the current static page.
 *
 * @return string
 */
function content_media_get_current_static_content() {
	global $smarty, $fp_params, $fp_config;

	if (isset($smarty) && is_object($smarty) && method_exists($smarty, 'getTemplateVars')) {
		$page = $smarty->getTemplateVars('static_page');
		if (is_array($page) && isset($page ['content'])) {
			return (string)$page ['content'];
		}
	}

	$id = '';
	if (!empty($fp_params ['page']) && is_scalar($fp_params ['page'])) {
		$id = (string)$fp_params ['page'];
	} elseif (empty($fp_params) && !empty($fp_config ['general'] ['startpage']) && is_scalar($fp_config ['general'] ['startpage'])) {
		$id = (string)$fp_config ['general'] ['startpage'];
	}

	if ($id !== '' && function_exists('static_parse')) {
		$page = static_parse($id);
		if (is_array($page) && isset($page ['content'])) {
			return (string)$page ['content'];
		}
	}

	return '';
}

/**
 * Copy the active stream window into a lightweight independent FPDB query.
 *
 * @param object $query
 * @return array
 */
function content_media_get_stream_query_params($query) {
	if (!is_object($query) || !isset($query->params) || !is_object($query->params)) {
		return array();
	}

	$p = $query->params;
	$params = array(
		'fullparse' => false,
		'start' => isset($p->start) ? (int)$p->start : 0,
		'count' => isset($p->count) ? (int)$p->count : 0
	);

	foreach (array('y', 'm', 'd') as $key) {
		if (isset($p->{$key}) && $p->{$key} !== null && $p->{$key} !== '') {
			$params [$key] = $p->{$key};
		}
	}
	if (isset($p->category) && (int)$p->category !== 0) {
		$params ['category'] = (int)$p->category;
	}
	if (isset($p->exclude) && $p->exclude !== '') {
		$params ['exclude'] = (int)$p->exclude;
	}

	return $params;
}

/**
 * Scan the current multi-entry query without consuming its iterator.
 *
 * @param string $baseUrl
 * @return array
 */
function content_media_get_stream_image_meta($baseUrl) {
	global $fpdb;

	$empty = content_media_empty_image_meta();
	if (!isset($fpdb) || !is_object($fpdb) || !method_exists($fpdb, 'getQuery')) {
		return $empty;
	}

	$query = &$fpdb->getQuery();
	if (!is_object($query)) {
		return $empty;
	}

	// Random/id queries are single internally. They are not chopped by ReadMore,
	// so inspect exactly the already selected entry rather than choosing another
	// random item in a second query.
	if (!empty($query->single)) {
		if (method_exists($query, 'peekEntry')) {
			$peek = @$query->peekEntry();
			if (is_array($peek) && !empty($peek [0]) && function_exists('entry_parse')) {
				$entry = entry_parse($peek [0]);
				if (is_array($entry) && isset($entry ['content'])) {
					return content_media_find_first_image_meta($entry ['content'], $baseUrl, false);
				}
			}
		}
		return $empty;
	}

	if (!class_exists('FPDB_Query') || !function_exists('entry_parse')) {
		return $empty;
	}

	$params = content_media_get_stream_query_params($query);
	if (empty($params) || !isset($params ['count']) || (int)$params ['count'] === 0) {
		return $empty;
	}

	$hadCurrentQuery = array_key_exists('current_query', $GLOBALS);
	$savedCurrentQuery = $hadCurrentQuery ? $GLOBALS ['current_query'] : null;
	$hadPost = array_key_exists('post', $GLOBALS);
	$savedPost = $hadPost ? $GLOBALS ['post'] : null;
	$result = $empty;

	try {
		$scan = new FPDB_Query($params, -2147483000);

		while ($scan->hasMore()) {
			$couplet = &$scan->getEntry();
			if (!is_array($couplet) || empty($couplet [0])) {
				break;
			}

			$entry = entry_parse($couplet [0]);
			if (!is_array($entry) || !isset($entry ['content'])) {
				continue;
			}

			$meta = content_media_find_first_image_meta($entry ['content'], $baseUrl, true);
			if (!empty($meta ['url'])) {
				$result = $meta;
				break;
			}
		}
	} finally {
		if ($hadCurrentQuery) {
			$GLOBALS ['current_query'] = $savedCurrentQuery;
		} else {
			content_media_unset_global('current_query');
		}

		if ($hadPost) {
			$GLOBALS ['post'] = $savedPost;
		} else {
			content_media_unset_global('post');
		}
	}

	return $result;
}

/**
 * Resolve the first image shown on the public front page.
 *
 * A configured static start page is inspected as a complete static page.
 * Otherwise only the first ordinary entry-stream window is scanned; FPDB's
 * default count therefore follows the configured general.maxentries value.
 *
 * @param string $baseUrl
 * @return array
 */
function content_media_get_frontpage_image_meta($baseUrl) {
	global $fp_config;

	$empty = content_media_empty_image_meta();

	if (isset($fp_config) && is_array($fp_config)
		&& !empty($fp_config ['general']) && is_array($fp_config ['general'])
		&& !empty($fp_config ['general'] ['startpage'])
		&& is_scalar($fp_config ['general'] ['startpage'])
		&& function_exists('static_parse')) {
		$page = static_parse((string)$fp_config ['general'] ['startpage']);
		if (is_array($page) && isset($page ['content'])) {
			return content_media_find_first_image_meta((string)$page ['content'], $baseUrl, false);
		}
		return $empty;
	}

	if (!class_exists('FPDB_Query') || !function_exists('entry_parse')) {
		return $empty;
	}

	$hadCurrentQuery = array_key_exists('current_query', $GLOBALS);
	$savedCurrentQuery = $hadCurrentQuery ? $GLOBALS ['current_query'] : null;
	$hadPost = array_key_exists('post', $GLOBALS);
	$savedPost = $hadPost ? $GLOBALS ['post'] : null;
	$result = $empty;

	try {
		// Omit count deliberately: FPDB_QueryParams uses general.maxentries.
		$scan = new FPDB_Query(array(
			'fullparse' => false,
			'start' => 0,
			'page' => 1
		), -2147482999);

		while ($scan->hasMore()) {
			$couplet = &$scan->getEntry();
			if (!is_array($couplet) || empty($couplet [0])) {
				break;
			}

			$entry = entry_parse($couplet [0]);
			if (!is_array($entry) || !isset($entry ['content'])) {
				continue;
			}

			$meta = content_media_find_first_image_meta((string)$entry ['content'], $baseUrl, true);
			if (!empty($meta ['url'])) {
				$result = $meta;
				break;
			}
		}
	} finally {
		if ($hadCurrentQuery) {
			$GLOBALS ['current_query'] = $savedCurrentQuery;
		} else {
			content_media_unset_global('current_query');
		}

		if ($hadPost) {
			$GLOBALS ['post'] = $savedPost;
		} else {
			content_media_unset_global('post');
		}
	}

	return $result;
}
?>

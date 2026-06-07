<?php
/**
 * Smarty is_external_url modifier plugin
 *
 * Type: modifier
 * Name: is_external_url
 * Purpose: Return true when an URL leaves the configured FlatPress blog.
 *
 * Relative URLs and absolute URLs that point into the configured blog base are
 * treated as internal. Absolute http(s) URLs outside the configured blog base
 * are treated as external so templates can add target="_blank" safely and only
 * where it is needed.
 *
 * @param mixed $url The URL to classify.
 * @return bool
 */
function smarty_modifier_is_external_url($url) {
	if (!is_string($url) && !is_numeric($url)) {
		return false;
	}

	$url = trim((string) $url);
	if ($url === '' || preg_match('/[\x00-\x1F\x7F]/', $url)) {
		return false;
	}

	$parts = @parse_url($url);
	if (!is_array($parts)) {
		return false;
	}

	if (empty($parts ['host'])) {
		// Relative and root-relative links stay inside the current blog.
		return false;
	}

	$scheme = strtolower((string) ($parts ['scheme'] ?? ''));
	if ($scheme !== '' && $scheme !== 'http' && $scheme !== 'https') {
		return false;
	}

	$host = smarty_modifier_is_external_url_normalize_host((string) $parts ['host']);
	if ($host === '') {
		return false;
	}

	$path = smarty_modifier_is_external_url_normalize_path((string) ($parts ['path'] ?? '/'));
	$port = smarty_modifier_is_external_url_port($parts, $scheme);

	foreach (smarty_modifier_is_external_url_blog_bases() as $base) {
		if ($host !== $base ['host']) {
			continue;
		}

		if ($port > 0 && $base ['port'] > 0 && $port !== $base ['port']) {
			continue;
		}

		if (!smarty_modifier_is_external_url_path_is_inside($path, $base ['path'])) {
			continue;
		}

		if ($base ['path'] !== '/' || smarty_modifier_is_external_url_root_blog_url_is_internal($parts)) {
			return false;
		}
	}

	return true;
}

/**
 * Normalize a host value for case-insensitive comparison.
 *
 * @param string $host
 * @return string
 */
function smarty_modifier_is_external_url_normalize_host($host) {
	$host = trim((string) $host);
	if ($host === '') {
		return '';
	}
	$host = trim($host, '[]');
	$host = rtrim($host, '.');
	if ($host === '') {
		return '';
	}
	return strtolower($host);
}

/**
 * Normalize an URL path for blog-base prefix comparison.
 *
 * @param string $path
 * @return string
 */
function smarty_modifier_is_external_url_normalize_path($path) {
	$path = (string) $path;
	if ($path === '') {
		return '/';
	}
	if ($path [0] !== '/') {
		$path = '/' . $path;
	}
	return $path;
}

/**
 * Return a default port for a http(s) scheme, or 0 when unknown.
 *
 * @param string $scheme
 * @return int
 */
function smarty_modifier_is_external_url_default_port($scheme) {
	$scheme = strtolower((string) $scheme);
	if ($scheme === 'https') {
		return 443;
	}
	if ($scheme === 'http') {
		return 80;
	}
	return 0;
}

/**
 * Return the effective URL port for comparison.
 *
 * @param array<string, mixed> $parts
 * @param string $scheme
 * @return int
 */
function smarty_modifier_is_external_url_port(array $parts, $scheme) {
	if (isset($parts ['port']) && is_numeric($parts ['port'])) {
		$port = (int) $parts ['port'];
		return ($port > 0 && $port <= 65535) ? $port : 0;
	}
	return smarty_modifier_is_external_url_default_port((string) $scheme);
}

/**
 * Build normalized absolute blog bases from runtime configuration.
 *
 * @return array<int, array{host: string, port: int, path: string}>
 */
function smarty_modifier_is_external_url_blog_bases() {
	$bases = array();
	$urls = array();

	if (isset($GLOBALS ['fp_config']) && is_array($GLOBALS ['fp_config'])
		&& isset($GLOBALS ['fp_config'] ['general']) && is_array($GLOBALS ['fp_config'] ['general'])
		&& !empty($GLOBALS ['fp_config'] ['general'] ['www'])) {
		$urls [] = (string) $GLOBALS ['fp_config'] ['general'] ['www'];
	}

	if (defined('BLOG_BASEURL')) {
		$urls [] = (string) BLOG_BASEURL;
	}

	if (function_exists('configured_blog_baseurl')) {
		$configuredBase = configured_blog_baseurl();
		if (is_string($configuredBase) && $configuredBase !== '') {
			$urls [] = $configuredBase;
		}
	}

	if (function_exists('system_guessbaseurl')) {
		$guessedBase = system_guessbaseurl();
		if (is_string($guessedBase) && $guessedBase !== '') {
			$urls [] = $guessedBase;
		}
	}

	foreach ($urls as $url) {
		$base = smarty_modifier_is_external_url_normalize_base((string) $url);
		if ($base === null) {
			continue;
		}
		$key = $base ['host'] . ':' . (string) $base ['port'] . ':' . $base ['path'];
		$bases [$key] = $base;
	}

	return array_values($bases);
}

/**
 * Normalize one absolute blog-base URL.
 *
 * @param string $url
 * @return array{host: string, port: int, path: string}|null
 */
function smarty_modifier_is_external_url_normalize_base($url) {
	$url = trim((string) $url);
	if ($url === '') {
		return null;
	}

	if (function_exists('normalize_baseurl')) {
		$normalized = normalize_baseurl($url);
		if (is_string($normalized) && $normalized !== '') {
			$url = $normalized;
		}
	}

	$parts = @parse_url($url);
	if (!is_array($parts) || empty($parts ['host'])) {
		return null;
	}

	$scheme = strtolower((string) ($parts ['scheme'] ?? ''));
	if ($scheme !== 'http' && $scheme !== 'https') {
		return null;
	}

	$host = smarty_modifier_is_external_url_normalize_host((string) $parts ['host']);
	if ($host === '') {
		return null;
	}

	return array(
		'host' => $host,
		'port' => smarty_modifier_is_external_url_port($parts, $scheme),
		'path' => smarty_modifier_is_external_url_normalize_blog_base_path((string) ($parts ['path'] ?? '/'))
	);
}

/**
 * Normalize a blog base path to a trailing-slash prefix.
 *
 * @param string $path
 * @return string
 */
function smarty_modifier_is_external_url_normalize_blog_base_path($path) {
	$path = smarty_modifier_is_external_url_normalize_path($path);
	if ($path !== '/' && substr($path, -1) !== '/') {
		$path .= '/';
	}
	return $path;
}

/**
 * Decide whether a same-host absolute URL belongs to a FlatPress blog installed
 * directly in the domain root.
 *
 * A root installation has no path prefix that can separate the blog from other
 * applications on the same host. This helper therefore accepts only known
 * FlatPress entry points, known FlatPress-owned directories and recognized
 * FlatPress route shapes. Unknown same-host root paths are treated as external.
 *
 * @param array<string, mixed> $parts Parsed URL parts.
 * @return bool
 */
function smarty_modifier_is_external_url_root_blog_url_is_internal(array $parts) {
	$path = smarty_modifier_is_external_url_normalize_path((string) ($parts ['path'] ?? '/'));
	$query = (string) ($parts ['query'] ?? '');

	if ($path === '/' || $path === '') {
		if ($query === '') {
			return true;
		}
		$routeFromQuery = smarty_modifier_is_external_url_route_from_query($query);
		return $routeFromQuery !== '' && smarty_modifier_is_external_url_path_matches_flatpress_route($routeFromQuery);
	}

	if (smarty_modifier_is_external_url_path_is_flatpress_entrypoint($path)) {
		return true;
	}

	if (smarty_modifier_is_external_url_path_is_flatpress_owned_directory($path)) {
		return true;
	}

	if (strpos($path, '/index.php/') === 0) {
		$route = substr($path, strlen('/index.php'));
		return smarty_modifier_is_external_url_path_matches_flatpress_route($route);
	}

	return smarty_modifier_is_external_url_path_matches_flatpress_route($path);
}

/**
 * Extract a PrettyURLs HTTP-GET route from a query string.
 *
 * @param string $query
 * @return string
 */
function smarty_modifier_is_external_url_route_from_query($query) {
	$query = (string) $query;
	if ($query === '') {
		return '';
	}

	$parameters = array();
	parse_str($query, $parameters);
	if (!isset($parameters ['u']) || is_array($parameters ['u'])) {
		return '';
	}

	return smarty_modifier_is_external_url_normalize_path((string) $parameters ['u']);
}

/**
 * Return true for FlatPress root-level PHP entry points.
 *
 * @param string $path
 * @return bool
 */
function smarty_modifier_is_external_url_path_is_flatpress_entrypoint($path) {
	$path = smarty_modifier_is_external_url_normalize_path($path);
	$entrypoints = array(
		'/admin.php',
		'/blog.php',
		'/comments.php',
		'/contact.php',
		'/get.php',
		'/index.php',
		'/login.php',
		'/rss.php',
		'/search.php',
		'/sitemap.php',
		'/static.php'
	);

	return in_array(rtrim($path, '/'), $entrypoints, true);
}

/**
 * Return true for paths below FlatPress-owned root directories.
 *
 * @param string $path
 * @return bool
 */
function smarty_modifier_is_external_url_path_is_flatpress_owned_directory($path) {
	$path = smarty_modifier_is_external_url_normalize_path($path);
	$prefixes = array(
		'/admin/',
		'/docs/',
		'/fp-content/',
		'/fp-defaults/',
		'/fp-interface/',
		'/fp-plugins/'
	);

	foreach ($prefixes as $prefix) {
		if (strpos($path, $prefix) === 0) {
			return true;
		}
	}

	return false;
}

/**
 * Return true for route shapes produced or consumed by FlatPress and PrettyURLs.
 *
 * @param string $path
 * @return bool
 */
function smarty_modifier_is_external_url_path_matches_flatpress_route($path) {
	$path = smarty_modifier_is_external_url_normalize_path($path);
	$path = preg_replace('!/{2,}!', '/', $path);
	if (!is_string($path) || $path === '' || $path === '/') {
		return true;
	}

	if (preg_match('!^/(?:page|paged)/[0-9]+/?$!i', $path)) {
		return true;
	}
	if (preg_match('!^/lastcomments/feed/(?:rss2|atom)/?$!i', $path)) {
		return true;
	}
	if (preg_match('!^/feed/(?:rss2|atom)/?$!i', $path)) {
		return true;
	}
	if (preg_match('!^/(?:category|tag)/[^/]+/?$!i', $path)) {
		return true;
	}
	if (preg_match('!^/(?:archive|archives)/[0-9]{4}(?:/[0-9]{1,2})?/?$!i', $path)) {
		return true;
	}
	if (preg_match('!^/[0-9]{4}(?:/[0-9]{1,2}(?:/[0-9]{1,2}(?:/[^/]+(?:/comments(?:/feed/(?:rss2|atom))?)?)?)?)?/?$!i', $path)) {
		return true;
	}
	if (preg_match('!^/(?:static|entry)/([A-Za-z0-9_-]+)/?$!i', $path)) {
		return true;
	}
	if (preg_match('!^/([A-Za-z0-9_-]+)/?$!i', $path, $matches)) {
		return smarty_modifier_is_external_url_static_page_exists((string) $matches [1]);
	}

	return false;
}

/**
 * Return true when a single-segment PrettyURLs path is an existing static page.
 *
 * @param string $slug
 * @return bool
 */
function smarty_modifier_is_external_url_static_page_exists($slug) {
	$slug = trim((string) $slug);
	if ($slug === '' || strpos($slug, '/') !== false || strpos($slug, '\\') !== false || strpos($slug, '.') !== false) {
		return false;
	}

	if (function_exists('static_exists') && static_exists($slug)) {
		return true;
	}

	if (defined('CONTENT_DIR') && defined('EXT')) {
		$file = (string) CONTENT_DIR . 'static/' . $slug . (string) EXT;
		if (is_file($file)) {
			return true;
		}
		if (defined('ABS_PATH')) {
			$absoluteFile = rtrim((string) ABS_PATH, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
			return is_file($absoluteFile);
		}
	}

	return false;
}

/**
 * Return true when the target path stays below the configured blog base path.
 *
 * @param string $targetPath
 * @param string $basePath
 * @return bool
 */
function smarty_modifier_is_external_url_path_is_inside($targetPath, $basePath) {
	$targetPath = smarty_modifier_is_external_url_normalize_path($targetPath);
	$basePath = smarty_modifier_is_external_url_normalize_blog_base_path($basePath);

	if ($basePath === '/') {
		return true;
	}

	$baseWithoutTrailingSlash = rtrim($basePath, '/');

	return $targetPath === $baseWithoutTrailingSlash || strpos($targetPath, $basePath) === 0;
}
?>

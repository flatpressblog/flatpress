<?php
/**
 * Mastodon profile declarations. Loaded by plugin.mastodon.php.
 * Runtime constants and registrations remain in the main plugin file.
 */

/**
 * Normalize the configured Mastodon username for HTML head metadata.
 *
 * Note: The administrator typically stores the local username without a leading
 * at-sign, but the helper also accepts values such as "@user" or
 * "@user@example.social" and reduces them to the local username part.
 *
 * @param string $username
 * @return string
 */
function plugin_mastodon_normalize_head_username($username) {
	$username = trim((string) $username);
	if ($username === '') {
		return '';
	}

	$username = ltrim($username, '@');
	if ($username === '') {
		return '';
	}

	$parts = explode('@', $username, 2);
	$username = trim((string) $parts [0]);
	if ($username === '') {
		return '';
	}

	$username = preg_replace('/\s+/', '', $username);
	if (!is_string($username)) {
		return '';
	}

	$username = trim($username, "/\\");
	if ($username === '') {
		return '';
	}

	return $username;
}

/**
 * Return the Mastodon instance authority used in fediverse creator metadata.
 *
 * Note: The authority intentionally ignores any optional path component because
 * the fediverse creator meta tag uses the server name, not a profile path.
 *
 * @param string $instanceUrl
 * @return string
 */
function plugin_mastodon_instance_authority($instanceUrl) {
	$instanceUrl = plugin_mastodon_normalize_instance_url($instanceUrl);
	if ($instanceUrl === '') {
		return '';
	}

	$parts = @parse_url($instanceUrl);
	if (!is_array($parts) || empty($parts ['host'])) {
		return '';
	}

	$host = strtolower((string) $parts ['host']);
	$port = isset($parts ['port']) ? (int) $parts ['port'] : 0;
	$scheme = isset($parts ['scheme']) ? strtolower((string) $parts ['scheme']) : 'https';
	if ($port > 0) {
		$defaultPort = ($scheme === 'http') ? 80 : 443;
		if ($port !== $defaultPort) {
			return $host . ':' . $port;
		}
	}

	return $host;
}

/**
 * Build the public Mastodon profile URL used for the rel-me link.
 * @param string $instanceUrl
 * @param string $username
 * @return string
 */
function plugin_mastodon_profile_url($instanceUrl, $username) {
	$instanceUrl = plugin_mastodon_normalize_instance_url($instanceUrl);
	$username = plugin_mastodon_normalize_head_username($username);
	if ($instanceUrl === '' || $username === '') {
		return '';
	}

	return rtrim($instanceUrl, '/') . '/@' . rawurlencode($username);
}

/**
 * Build the fediverse creator meta value.
 * @param string $instanceUrl
 * @param string $username
 * @return string
 */
function plugin_mastodon_fediverse_creator_value($instanceUrl, $username) {
	$username = plugin_mastodon_normalize_head_username($username);
	$authority = plugin_mastodon_instance_authority($instanceUrl);
	if ($username === '' || $authority === '') {
		return '';
	}

	return '@' . $username . '@' . $authority;
}

/**
 * Print Mastodon profile metadata into the HTML head.
 *
 * Note: The metadata is only emitted when a username is configured. This keeps
 * the markup quiet on installations that only use the plugin for synchronization.
 *
 * @return void
 */
function plugin_mastodon_head() {
	if (defined('ADMIN_PANEL')) {
		return;
	}

	$headMarkup = array();

	$cachedProfile = plugin_mastodon_profile_cache_read(true);
	if ($cachedProfile !== array() && function_exists('plugin_geturl')) {
		$pdir = plugin_geturl('mastodon');
		if (is_string($pdir) && $pdir !== '') {
			$css = $pdir . 'res/mastodon.css';
			if (function_exists('utils_asset_ver')) {
				$css = utils_asset_ver($css, defined('SYSTEM_VER') ? (string) SYSTEM_VER : null);
			}
			if ($css !== '') {
				$headMarkup [] = '<link rel="stylesheet" href="' . htmlspecialchars($css, ENT_QUOTES, 'UTF-8') . '">';
			}
		}
	}

	$options = plugin_mastodon_get_options();
	$username = isset($options ['username']) ? (string) $options ['username'] : '';
	if (plugin_mastodon_normalize_head_username($username) !== '') {
		$instanceUrl = isset($options ['instance_url']) ? (string) $options ['instance_url'] : '';
		$profileUrl = plugin_mastodon_profile_url($instanceUrl, $username);
		$creator = plugin_mastodon_fediverse_creator_value($instanceUrl, $username);
		if ($profileUrl !== '' && $creator !== '') {
			$headMarkup [] = '<link rel="me" href="' . htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') . '">';
			$headMarkup [] = '<meta name="fediverse:creator" content="' . htmlspecialchars($creator, ENT_QUOTES, 'UTF-8') . '">';
		}
	}

	if ($headMarkup === array()) {
		return;
	}

	echo "\n\t\t<!-- BOF Mastodon head -->\n\t\t" . implode("\n\t\t", $headMarkup) . "\n\t\t<!-- EOF Mastodon head -->\n\t";
}

/**
 * Ensure that the public Mastodon profile-cache directory exists.
 * @return bool
 */
function plugin_mastodon_ensure_profile_dir() {
	if (!plugin_mastodon_ensure_state_dir()) {
		return false;
	}
	if (!fs_mkdir(PLUGIN_MASTODON_PROFILE_DIR)) {
		return false;
	}
	$indexFile = PLUGIN_MASTODON_PROFILE_DIR . 'index.html';
	if (!@file_exists($indexFile)) {
		plugin_mastodon_io_write_file($indexFile, '');
	}
	return true;
}

/**
 * Return a Mastodon widget/profile language string or a provided fallback.
 * @param string $key
 * @param string $default
 * @return string
 */
function plugin_mastodon_widget_lang_string($key, $default) {
	static $strings = null;

	if ($strings === null) {
		$strings = array();
		if (function_exists('lang_load')) {
			$lang = lang_load('plugin:mastodon');
			if (isset($lang ['plugin'] ['mastodon']) && is_array($lang ['plugin'] ['mastodon'])) {
				$strings = $lang ['plugin'] ['mastodon'];
			}
		}
	}

	return isset($strings [$key]) && is_string($strings [$key]) && $strings [$key] !== '' ? $strings [$key] : (string) $default;
}

/**
 * Normalize public account text for local profile-cache storage.
 * @param mixed $value
 * @param int $limit
 * @return string
 */
function plugin_mastodon_profile_text($value, $limit = 300) {
	$value = trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8'));
	$value = preg_replace('/\s+/u', ' ', $value);
	$value = is_string($value) ? trim($value) : '';
	if ($value === '') {
		return '';
	}
	return plugin_mastodon_limit_text($value, max(1, (int) $limit));
}

/**
 * Return whether a URL is safe enough to store as a public Mastodon profile/avatar URL.
 * @param string $url
 * @return bool
 */
function plugin_mastodon_profile_public_url_allowed($url) {
	$url = trim((string) $url);
	if ($url === '' || preg_match('/[\x00-\x1F\x7F]/', $url)) {
		return false;
	}
	$parts = @parse_url($url);
	if (!is_array($parts) || empty($parts ['scheme']) || empty($parts ['host'])) {
		return false;
	}
	$scheme = strtolower((string) $parts ['scheme']);
	return $scheme === 'https' || $scheme === 'http';
}

/**
 * Convert a profile-cache path relative to fp-content into an absolute path.
 * @param string $relativePath
 * @return string
 */
function plugin_mastodon_profile_relative_to_absolute($relativePath) {
	$relativePath = trim(str_replace('\\', '/', (string) $relativePath));
	if ($relativePath === '' || strpos($relativePath, '..') !== false || preg_match('!^(?:https?:)?//!i', $relativePath) || preg_match('!^[a-z][a-z0-9+.-]*:!i', $relativePath)) {
		return '';
	}
	$relativePath = ltrim($relativePath, '/');
	if (strpos($relativePath, 'plugin_mastodon/profile/') !== 0) {
		return '';
	}
	return FP_CONTENT . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
}

/**
 * Convert a profile-cache path relative to fp-content into a public URL.
 * @param string $relativePath
 * @return string
 */
function plugin_mastodon_profile_relative_to_url($relativePath) {
	$relativePath = trim(str_replace('\\', '/', (string) $relativePath));
	if ($relativePath === '' || strpos($relativePath, '..') !== false || preg_match('!^(?:https?:)?//!i', $relativePath) || preg_match('!^[a-z][a-z0-9+.-]*:!i', $relativePath)) {
		return '';
	}
	$relativePath = ltrim($relativePath, '/');
	if (strpos($relativePath, 'plugin_mastodon/profile/') !== 0) {
		return '';
	}
	return rtrim(BLOG_BASEURL, '/') . '/fp-content/' . $relativePath;
}

/**
 * Read the public Mastodon profile cache.
 * @param bool $requireAvatarFile
 * @return array<string, mixed>
 */
function plugin_mastodon_profile_cache_read($requireAvatarFile = true) {
	if (!@is_file(PLUGIN_MASTODON_PROFILE_FILE)) {
		return array();
	}
	$raw = plugin_mastodon_io_read_file(PLUGIN_MASTODON_PROFILE_FILE);
	if (!is_string($raw) || trim($raw) === '') {
		return array();
	}
	$data = json_decode($raw, true);
	if (!is_array($data)) {
		return array();
	}

	$displayName = isset($data ['display_name']) ? plugin_mastodon_profile_text($data ['display_name'], 300) : '';
	$acct = isset($data ['acct']) ? plugin_mastodon_profile_text($data ['acct'], 300) : '';
	$profileUrl = isset($data ['profile_url']) ? trim((string) $data ['profile_url']) : '';
	$avatarFile = isset($data ['avatar_file']) ? trim((string) $data ['avatar_file']) : '';
	if ($displayName === '' || $acct === '' || !plugin_mastodon_profile_public_url_allowed($profileUrl) || $avatarFile === '') {
		return array();
	}
	$absoluteAvatar = plugin_mastodon_profile_relative_to_absolute($avatarFile);
	if ($absoluteAvatar === '') {
		return array();
	}
	if ($requireAvatarFile && !@is_file($absoluteAvatar)) {
		return array();
	}

	$data ['display_name'] = $displayName;
	$data ['acct'] = ltrim($acct, '@');
	$data ['profile_url'] = $profileUrl;
	$data ['avatar_file'] = str_replace('\\', '/', $avatarFile);
	$data ['avatar_absolute'] = $absoluteAvatar;
	return $data;
}

/**
 * Write the public Mastodon profile cache.
 * @param array<string, mixed> $profile
 * @return bool
 */
function plugin_mastodon_profile_cache_write($profile) {
	if (!plugin_mastodon_ensure_profile_dir()) {
		return false;
	}
	$json = json_encode($profile, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if (!is_string($json)) {
		return false;
	}
	return plugin_mastodon_io_write_file(PLUGIN_MASTODON_PROFILE_FILE, $json . PHP_EOL);
}

/**
 * Return the best profile URL for an authenticated account response.
 * @param array<string, string> $options
 * @param array<string, mixed> $account
 * @param string $username
 * @return string
 */
function plugin_mastodon_profile_url_from_account($options, $account, $username) {
	$url = !empty($account ['url']) ? trim((string) $account ['url']) : '';
	if (plugin_mastodon_profile_public_url_allowed($url)) {
		return $url;
	}
	return plugin_mastodon_profile_url(isset($options ['instance_url']) ? (string) $options ['instance_url'] : '', $username);
}

/**
 * Return the preferred avatar URL from an authenticated account response.
 * @param array<string, mixed> $account
 * @return string
 */
function plugin_mastodon_profile_avatar_url_from_account($account) {
	foreach (array('avatar_static', 'avatar') as $key) {
		$url = !empty($account [$key]) ? trim((string) $account [$key]) : '';
		if (plugin_mastodon_profile_public_url_allowed($url)) {
			return $url;
		}
	}
	return '';
}

/**
 * Resolve a safe image MIME type and extension for a downloaded avatar.
 * @param string $body
 * @param array<string, mixed> $headers
 * @return array<string, string>
 */
function plugin_mastodon_profile_avatar_type($body, $headers) {
	$contentType = '';
	if (!empty($headers ['content-type'])) {
		$contentType = strtolower(trim((string) $headers ['content-type']));
		if (strpos($contentType, ';') !== false) {
			$parts = explode(';', $contentType, 2);
			$contentType = trim($parts [0]);
		}
	}

	$map = array(
		'image/jpeg' => 'jpg',
		'image/png' => 'png',
		'image/gif' => 'gif',
		'image/webp' => 'webp'
	);
	if (isset($map [$contentType])) {
		return array('mime' => $contentType, 'extension' => $map [$contentType]);
	}

	if (function_exists('getimagesizefromstring')) {
		$info = @getimagesizefromstring($body);
		if (is_array($info) && !empty($info ['mime'])) {
			$detected = strtolower((string) $info ['mime']);
			if (isset($map [$detected])) {
				return array('mime' => $detected, 'extension' => $map [$detected]);
			}
		}
	}

	if (function_exists('finfo_open')) {
		$finfo = @finfo_open(FILEINFO_MIME_TYPE);
		if ($finfo) {
			// @phpstan-ignore argument.type (PHPStan 2.2.10 reports the PHP 7.x fileinfo resource arm as incompatible in ranged phpVersion analysis)
			$detected = @finfo_buffer($finfo, $body);
			@finfo_close($finfo);
			$detected = is_string($detected) ? strtolower(trim($detected)) : '';
			if (isset($map [$detected])) {
				return array('mime' => $detected, 'extension' => $map [$detected]);
			}
		}
	}

	return array();
}

/**
 * Download and store a Mastodon avatar image in fp-content/plugin_mastodon/profile/.
 * @param string $avatarUrl
 * @param string $oldAvatarFile
 * @return array<string, string>
 */
function plugin_mastodon_profile_download_avatar($avatarUrl, $oldAvatarFile = '') {
	$avatarUrl = trim((string) $avatarUrl);
	if (!plugin_mastodon_profile_public_url_allowed($avatarUrl) || !plugin_mastodon_ensure_profile_dir()) {
		return array();
	}

	$response = plugin_mastodon_http_request('GET', $avatarUrl, array('Accept: image/avif,image/webp,image/png,image/jpeg,image/gif;q=0.8,*/*;q=0.1'), '', '', PLUGIN_MASTODON_PROFILE_REQUEST_TIMEOUT);
	if (isset($GLOBALS ['plugin_mastodon_test_profile_avatar_responses']) && is_array($GLOBALS ['plugin_mastodon_test_profile_avatar_responses'])) {
		$GLOBALS ['plugin_mastodon_test_profile_avatar_responses'] [] = $response;
	}
	if (empty($response ['ok']) || !isset($response ['body']) || $response ['body'] === '') {
		return array();
	}
	$body = (string) $response ['body'];
	if (strlen($body) > PLUGIN_MASTODON_PROFILE_AVATAR_MAX_BYTES) {
		return array();
	}
	$type = plugin_mastodon_profile_avatar_type($body, isset($response ['headers']) && is_array($response ['headers']) ? $response ['headers'] : array());
	if (empty($type ['extension']) || empty($type ['mime'])) {
		return array();
	}

	$relative = 'plugin_mastodon/profile/avatar.' . plugin_mastodon_safe_filename($type ['extension']);
	$target = plugin_mastodon_profile_relative_to_absolute($relative);
	if ($target === '') {
		return array();
	}
	$temp = PLUGIN_MASTODON_PROFILE_DIR . 'avatar-' . sha1($avatarUrl . '|' . microtime(true)) . '.tmp';
	if (!plugin_mastodon_io_write_file($temp, $body)) {
		return array();
	}
	if (@is_file($target)) {
		@unlink($target);
	}
	if (!@rename($temp, $target)) {
		$copied = @copy($temp, $target);
		@unlink($temp);
		if (!$copied) {
			return array();
		}
	}
	plugin_mastodon_apply_file_permissions($target);

	$oldAvatarPath = plugin_mastodon_profile_relative_to_absolute($oldAvatarFile);
	if ($oldAvatarPath !== '' && $oldAvatarPath !== $target && @is_file($oldAvatarPath)) {
		@unlink($oldAvatarPath);
	}

	return array(
		'avatar_file' => $relative,
		'avatar_mime_type' => $type ['mime'],
		'avatar_bytes' => (string) strlen($body)
	);
}

/**
 * Build the public widget cache from a Mastodon verify_credentials account payload.
 * @param array<string, string> $options
 * @param array<string, mixed> $account
 * @return bool
 */
function plugin_mastodon_refresh_profile_cache_from_account($options, $account) {
	if (!is_array($account) || empty($account ['id'])) {
		return false;
	}

	$username = !empty($account ['username']) ? plugin_mastodon_profile_text($account ['username'], 300) : '';
	$acct = !empty($account ['acct']) ? plugin_mastodon_profile_text($account ['acct'], 300) : $username;
	$acct = ltrim($acct, '@');
	if ($acct === '') {
		return false;
	}
	if ($username === '') {
		$username = $acct;
	}
	$displayName = !empty($account ['display_name']) ? plugin_mastodon_profile_text($account ['display_name'], 300) : '';
	if ($displayName === '') {
		$displayName = $acct;
	}

	$profileUrl = plugin_mastodon_profile_url_from_account($options, $account, $username);
	$avatarUrl = plugin_mastodon_profile_avatar_url_from_account($account);
	if (!plugin_mastodon_profile_public_url_allowed($profileUrl) || $avatarUrl === '') {
		return false;
	}

	$existing = plugin_mastodon_profile_cache_read(false);
	$existingAvatarFile = !empty($existing ['avatar_file']) ? (string) $existing ['avatar_file'] : '';
	$existingAvatarUrl = !empty($existing ['avatar_source_url']) ? (string) $existing ['avatar_source_url'] : '';
	$avatarData = array();
	if ($existingAvatarFile !== '' && $existingAvatarUrl === $avatarUrl) {
		$existingPath = plugin_mastodon_profile_relative_to_absolute($existingAvatarFile);
		if ($existingPath !== '' && @is_file($existingPath)) {
			$avatarData = array(
				'avatar_file' => $existingAvatarFile,
				'avatar_mime_type' => !empty($existing ['avatar_mime_type']) ? (string) $existing ['avatar_mime_type'] : '',
				'avatar_bytes' => !empty($existing ['avatar_bytes']) ? (string) $existing ['avatar_bytes'] : ''
			);
		}
	}
	if (empty($avatarData)) {
		$avatarData = plugin_mastodon_profile_download_avatar($avatarUrl, $existingAvatarFile);
	}
	if (empty($avatarData ['avatar_file'])) {
		if ($existingAvatarFile !== '') {
			$existingPath = plugin_mastodon_profile_relative_to_absolute($existingAvatarFile);
			$existingAccountId = !empty($existing ['account_id']) ? (string) $existing ['account_id'] : '';
			if ($existingPath !== '' && @is_file($existingPath) && $existingAccountId === (string) $account ['id']) {
				$avatarData = array(
					'avatar_file' => $existingAvatarFile,
					'avatar_mime_type' => !empty($existing ['avatar_mime_type']) ? (string) $existing ['avatar_mime_type'] : '',
					'avatar_bytes' => !empty($existing ['avatar_bytes']) ? (string) $existing ['avatar_bytes'] : ''
				);
				$avatarUrl = $existingAvatarUrl !== '' ? $existingAvatarUrl : $avatarUrl;
			}
		}
		if (empty($avatarData ['avatar_file'])) {
			return false;
		}
	}

	$avatarDescription = isset($account ['avatar_description']) ? plugin_mastodon_profile_text($account ['avatar_description'], 1000) : '';

	$profile = array(
		'version' => 1,
		'account_id' => (string) $account ['id'],
		'display_name' => $displayName,
		'acct' => $acct,
		'username' => $username,
		'profile_url' => $profileUrl,
		'avatar_file' => $avatarData ['avatar_file'],
		'avatar_source_url' => $avatarUrl,
		'avatar_mime_type' => isset($avatarData ['avatar_mime_type']) ? (string) $avatarData ['avatar_mime_type'] : '',
		'avatar_bytes' => isset($avatarData ['avatar_bytes']) ? (string) $avatarData ['avatar_bytes'] : '',
		'avatar_description' => $avatarDescription,
		'fetched_at' => gmdate('Y-m-d H:i:s')
	);
	$written = plugin_mastodon_profile_cache_write($profile);
	if ($written && isset($GLOBALS ['plugin_mastodon_test_profile_cache_events']) && is_array($GLOBALS ['plugin_mastodon_test_profile_cache_events'])) {
		$GLOBALS ['plugin_mastodon_test_profile_cache_events'] [] = $profile;
	}
	return $written;
}

/**
 * Refresh the public Mastodon profile cache through verify_credentials.
 * @param array<string, string> $options
 * @return bool
 */
function plugin_mastodon_refresh_profile_cache($options) {
	$response = plugin_mastodon_mastodon_json($options, 'GET', '/api/v1/accounts/verify_credentials', array(), true, PLUGIN_MASTODON_PROFILE_REQUEST_TIMEOUT);
	if (empty($response ['ok']) || empty($response ['json']) || !is_array($response ['json'])) {
		return false;
	}
	return plugin_mastodon_refresh_profile_cache_from_account($options, $response ['json']);
}

/**
 * Refresh the public profile-widget cache during a content synchronization.
 *
 * The synchronization workflow uses plugin_mastodon_verify_credentials() so
 * Mastodon-to-FlatPress imports can reuse the runtime-cached account payload
 * instead of issuing a second verify_credentials request. A failed widget cache
 * refresh is logged but does not decide the content-sync result by itself.
 *
 * @param array<string, string> $options
 * @return bool
 */
function plugin_mastodon_refresh_profile_cache_for_sync($options) {
	$response = plugin_mastodon_verify_credentials($options);
	if (!empty($response ['ok']) && !empty($response ['json']) && is_array($response ['json'])) {
		$profile = plugin_mastodon_profile_cache_read(true);
		if ($profile !== array()) {
			plugin_mastodon_log('Mastodon profile widget cache refreshed during content synchronization');
			return true;
		}
		plugin_mastodon_log('Mastodon profile widget cache refresh did not produce a complete local profile cache');
		return false;
	}

	$rateLimitError = plugin_mastodon_rate_limit_state_error();
	if ($rateLimitError !== '') {
		plugin_mastodon_log('Mastodon profile widget cache refresh skipped during content synchronization: ' . $rateLimitError);
	} else {
		plugin_mastodon_log('Mastodon profile widget cache refresh failed during content synchronization');
	}
	return false;
}

/**
 * Build the localized avatar alt text for the widget.
 * @param array<string, mixed> $profile
 * @return string
 */
function plugin_mastodon_widget_avatar_alt($profile) {
	$description = isset($profile ['avatar_description']) ? plugin_mastodon_profile_text($profile ['avatar_description'], 1000) : '';
	if ($description !== '') {
		return $description;
	}
	$name = isset($profile ['display_name']) ? plugin_mastodon_profile_text($profile ['display_name'], 300) : '';
	if ($name === '') {
		$name = isset($profile ['acct']) ? '@' . ltrim((string) $profile ['acct'], '@') : 'Mastodon';
	}
	return sprintf(plugin_mastodon_widget_lang_string('avatar_alt_format', 'Profile picture of %s'), $name);
}

/**
 * Render the compact Mastodon profile widget from the local cache only.
 *
 * This callback intentionally performs no Mastodon API requests and no remote
 * avatar downloads. Missing or incomplete cache data hides the widget instead
 * of delaying a frontend request.
 *
 * @return array<string, string>
 */
function plugin_mastodon_widget() {
	if (defined('ADMIN_PANEL')) {
		return array();
	}

	$profile = plugin_mastodon_profile_cache_read(true);
	if ($profile === array()) {
		return array();
	}

	$avatarUrl = plugin_mastodon_profile_relative_to_url(isset($profile ['avatar_file']) ? (string) $profile ['avatar_file'] : '');
	$profileUrl = isset($profile ['profile_url']) ? (string) $profile ['profile_url'] : '';
	if ($avatarUrl === '' || !plugin_mastodon_profile_public_url_allowed($profileUrl)) {
		return array();
	}

	$displayName = isset($profile ['display_name']) ? plugin_mastodon_profile_text($profile ['display_name'], 300) : '';
	$acct = isset($profile ['acct']) ? ltrim(plugin_mastodon_profile_text($profile ['acct'], 300), '@') : '';
	if ($displayName === '' || $acct === '') {
		return array();
	}

	$subject = plugin_mastodon_widget_lang_string('subject', 'Mastodon');
	$alt = plugin_mastodon_widget_avatar_alt($profile);
	$title = plugin_mastodon_widget_lang_string('profile_link_title', 'Open Mastodon profile');

	$content = '<div class="mastodon-profile-widget">' .
		'<a class="mastodon-profile-widget-avatar-link" target="_blank" href="' . htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') . '" rel="me noopener noreferrer" title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">' .
		'<img src="' . htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" loading="lazy" decoding="async">' .
		'</a>' .
		'<div class="mastodon-profile-widget-name"><strong>' . htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . '</strong></div>' .
		'<div class="mastodon-profile-widget-acct">@' . htmlspecialchars($acct, ENT_QUOTES, 'UTF-8') . '</div>' .
		'</div>';

	return array(
		'subject' => $subject,
		'content' => $content
	);
}
?>

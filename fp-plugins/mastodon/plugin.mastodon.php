<?php
/**
 * Plugin Name: Mastodon
 * Plugin URI: https://www.flatpress.org
 * Description: Synchronizes FlatPress entries and comments with Mastodon. <a href="./fp-plugins/mastodon/doc_mastodon.txt" title="Instructions" target="_blank">[Instructions]</a>
 * Version: 2.2.0
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 */

defined('ABSPATH') or define('ABSPATH', dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR);
defined('FP_CONTENT') or exit;

if (!defined('PLUGIN_MASTODON_DIR')) {
	define('PLUGIN_MASTODON_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}
if (!defined('PLUGIN_MASTODON_STATE_DIR')) {
	define('PLUGIN_MASTODON_STATE_DIR', FP_CONTENT . 'plugin_mastodon' . DIRECTORY_SEPARATOR);
}
if (!defined('PLUGIN_MASTODON_STATE_FILE')) {
	define('PLUGIN_MASTODON_STATE_FILE', PLUGIN_MASTODON_STATE_DIR . 'state.json');
}
if (!defined('PLUGIN_MASTODON_LOCK_FILE')) {
	define('PLUGIN_MASTODON_LOCK_FILE', PLUGIN_MASTODON_STATE_DIR . 'sync.lock');
}
if (!defined('PLUGIN_MASTODON_LOG_FILE')) {
	define('PLUGIN_MASTODON_LOG_FILE', PLUGIN_MASTODON_STATE_DIR . 'sync.log');
}
if (!defined('PLUGIN_MASTODON_APP_NAME')) {
	define('PLUGIN_MASTODON_APP_NAME', 'FlatPress Mastodon');
}
if (!defined('PLUGIN_MASTODON_DEFAULT_SYNC_TIME')) {
	define('PLUGIN_MASTODON_DEFAULT_SYNC_TIME', '03:00');
}
if (!defined('PLUGIN_MASTODON_MAX_STATUS_PAGES')) {
	define('PLUGIN_MASTODON_MAX_STATUS_PAGES', 5);
}
if (!defined('PLUGIN_MASTODON_IMPORTED_MEDIA_WIDTH')) {
	define('PLUGIN_MASTODON_IMPORTED_MEDIA_WIDTH', 320);
}
if (!defined('PLUGIN_MASTODON_PENDING_COMMENT_RECHECK_LIMIT')) {
	define('PLUGIN_MASTODON_PENDING_COMMENT_RECHECK_LIMIT', 3);
}

/**
 * Internal runtime data structures:
 * - plugin options are normalized associative arrays with string values
 * - runtime state is a nested associative array persisted as JSON
 */


/**
 * Return the default plugin option values.
 * @return array<string, string>
 */
function plugin_mastodon_default_options() {
	return array(
		'instance_url' => '',
		'username' => '',
		'password' => '',
		'sync_time' => PLUGIN_MASTODON_DEFAULT_SYNC_TIME,
		'sync_start_date' => '',
		'update_local_from_remote' => '0',
		'import_synced_comments_as_entries' => '0',
		'quote_imported_reply_parent' => '1',
		'delete_sync_enabled' => '1',
		'client_id' => '',
		'client_secret' => '',
		'access_token' => '',
		'authorization_code' => '',
		'last_authorize_url' => '',
		'oauth_registered_scopes' => '',
		'instance_info_url' => '',
		'instance_info_json' => '',
		'instance_info_fetched_at' => '',
		'instance_info_error' => '',
		'instance_info_error_at' => ''
	);
}

/**
 * Remove any stored Mastodon instance information from the plugin options.
 * @param array<string, string> $options
 * @return array<string, string>
 */
function plugin_mastodon_clear_saved_instance_info($options) {
	$options = is_array($options) ? $options : array();
	foreach (array('instance_info_url', 'instance_info_json', 'instance_info_fetched_at', 'instance_info_error', 'instance_info_error_at') as $key) {
		$options [$key] = '';
	}
	return $options;
}

/**
 * Reduce a live Mastodon instance document to the stable subset that is useful for the plugin.
 *
 * The cached snapshot intentionally keeps only the fields that are used for capability
 * checks, export budgeting, and the admin diagnostics table. This keeps the FlatPress
 * configuration compact while still preserving the exact Mastodon version string and the
 * most relevant instance limits.
 *
 * @param array<string, mixed> $document
 * @return array<string, mixed>
 */
function plugin_mastodon_compact_instance_document($document) {
	if (!is_array($document)) {
		return array();
	}

	$compact = array();
	foreach (array('domain', 'title', 'version', 'source_url', 'description') as $key) {
		if (isset($document [$key])) {
			$value = trim((string) $document [$key]);
			if ($value !== '') {
				$compact [$key] = $value;
			}
		}
	}

	if (!empty($document ['languages']) && is_array($document ['languages'])) {
		$languages = array();
		foreach ($document ['languages'] as $language) {
			$language = trim((string) $language);
			if ($language !== '') {
				$languages [] = $language;
			}
		}
		if (!empty($languages)) {
			$compact ['languages'] = array_values(array_unique($languages));
		}
	}

	if (!empty($document ['usage']) && is_array($document ['usage']) && !empty($document ['usage'] ['users']) && is_array($document ['usage'] ['users'])) {
		$usage = array();
		if (isset($document ['usage'] ['users'] ['active_month'])) {
			$usage ['users'] = array('active_month' => max(0, (int) $document ['usage'] ['users'] ['active_month']));
		}
		if (!empty($usage)) {
			$compact ['usage'] = $usage;
		}
	}

	if (!empty($document ['api_versions']) && is_array($document ['api_versions'])) {
		$apiVersions = array();
		foreach ($document ['api_versions'] as $apiName => $apiVersion) {
			$key = trim((string) $apiName);
			if ($key === '') {
				continue;
			}
			if (is_int($apiVersion) || is_float($apiVersion) || (is_string($apiVersion) && preg_match('/^-?\d+(?:\.\d+)?$/', trim($apiVersion)))) {
				$apiVersions [$key] = (int) $apiVersion;
			} else {
				$apiVersion = trim((string) $apiVersion);
				if ($apiVersion !== '') {
					$apiVersions [$key] = $apiVersion;
				}
			}
		}
		if (!empty($apiVersions)) {
			$compact ['api_versions'] = $apiVersions;
		}
	}

	if (!empty($document ['contact']) && is_array($document ['contact'])) {
		$contact = array();
		if (!empty($document ['contact'] ['email'])) {
			$contact ['email'] = trim((string) $document ['contact'] ['email']);
		}
		if (!empty($document ['contact'] ['account']) && is_array($document ['contact'] ['account'])) {
			$account = array();
			foreach (array('acct', 'url') as $key) {
				if (!empty($document ['contact'] ['account'] [$key])) {
					$account [$key] = trim((string) $document ['contact'] ['account'] [$key]);
				}
			}
			if (!empty($account)) {
				$contact ['account'] = $account;
			}
		}
		if (!empty($contact)) {
			$compact ['contact'] = $contact;
		}
	}

	if (isset($document ['rules']) && is_array($document ['rules'])) {
		$compact ['rules_count'] = count($document ['rules']);
	}

	if (!empty($document ['registrations']) && is_array($document ['registrations'])) {
		$registrations = array();
		foreach (array('enabled', 'approval_required', 'reason_required') as $boolKey) {
			if (array_key_exists($boolKey, $document ['registrations'])) {
				$registrations [$boolKey] = (bool) $document ['registrations'] [$boolKey];
			}
		}
		foreach (array('message', 'url') as $stringKey) {
			if (isset($document ['registrations'] [$stringKey])) {
				$value = trim((string) $document ['registrations'] [$stringKey]);
				if ($value !== '') {
					$registrations [$stringKey] = $value;
				}
			}
		}
		if (isset($document ['registrations'] ['min_age']) && $document ['registrations'] ['min_age'] !== null && $document ['registrations'] ['min_age'] !== '') {
			$registrations ['min_age'] = max(0, (int) $document ['registrations'] ['min_age']);
		}
		if (!empty($registrations)) {
			$compact ['registrations'] = $registrations;
		}
	}

	if (!empty($document ['configuration']) && is_array($document ['configuration'])) {
		$configuration = array();

		if (!empty($document ['configuration'] ['urls']) && is_array($document ['configuration'] ['urls'])) {
			$urls = array();
			foreach (array('streaming', 'status', 'about', 'privacy_policy', 'terms_of_service') as $urlKey) {
				if (isset($document ['configuration'] ['urls'] [$urlKey])) {
					$value = trim((string) $document ['configuration'] ['urls'] [$urlKey]);
					if ($value !== '') {
						$urls [$urlKey] = $value;
					}
				}
			}
			if (!empty($urls)) {
				$configuration ['urls'] = $urls;
			}
		}

		if (!empty($document ['configuration'] ['statuses']) && is_array($document ['configuration'] ['statuses'])) {
			$statuses = array();
			foreach (array('max_characters', 'max_media_attachments', 'characters_reserved_per_url') as $intKey) {
				if (isset($document ['configuration'] ['statuses'] [$intKey]) && $document ['configuration'] ['statuses'] [$intKey] !== '') {
					$statuses [$intKey] = max(0, (int) $document ['configuration'] ['statuses'] [$intKey]);
				}
			}
			if (!empty($statuses)) {
				$configuration ['statuses'] = $statuses;
			}
		}

		if (!empty($document ['configuration'] ['media_attachments']) && is_array($document ['configuration'] ['media_attachments'])) {
			$media = array();
			foreach (array('description_limit', 'image_size_limit', 'image_matrix_limit', 'video_size_limit', 'video_frame_rate_limit', 'video_matrix_limit', 'audio_size_limit') as $intKey) {
				if (isset($document ['configuration'] ['media_attachments'] [$intKey]) && $document ['configuration'] ['media_attachments'] [$intKey] !== '') {
					$media [$intKey] = max(0, (int) $document ['configuration'] ['media_attachments'] [$intKey]);
				}
			}
			if (!empty($document ['configuration'] ['media_attachments'] ['supported_mime_types']) && is_array($document ['configuration'] ['media_attachments'] ['supported_mime_types'])) {
				$mimeTypes = array();
				foreach ($document ['configuration'] ['media_attachments'] ['supported_mime_types'] as $mimeType) {
					$mimeType = trim((string) $mimeType);
					if ($mimeType !== '') {
						$mimeTypes [] = $mimeType;
					}
				}
				if (!empty($mimeTypes)) {
					$media ['supported_mime_types'] = array_values(array_unique($mimeTypes));
				}
			}
			if (!empty($media)) {
				$configuration ['media_attachments'] = $media;
			}
		}

		if (!empty($document ['configuration'] ['translation']) && is_array($document ['configuration'] ['translation'])) {
			$translation = array();
			if (array_key_exists('enabled', $document ['configuration'] ['translation'])) {
				$translation ['enabled'] = (bool) $document ['configuration'] ['translation'] ['enabled'];
			}
			if (!empty($translation)) {
				$configuration ['translation'] = $translation;
			}
		}

		if (!empty($document ['configuration'] ['timelines_access']) && is_array($document ['configuration'] ['timelines_access'])) {
			$timelinesAccess = array();
			foreach (array('live_feeds', 'hashtag_feeds') as $groupKey) {
				if (empty($document ['configuration'] ['timelines_access'] [$groupKey]) || !is_array($document ['configuration'] ['timelines_access'] [$groupKey])) {
					continue;
				}
				$group = array();
				foreach (array('local', 'remote') as $accessKey) {
					if (!empty($document ['configuration'] ['timelines_access'] [$groupKey] [$accessKey])) {
						$group [$accessKey] = trim((string) $document ['configuration'] ['timelines_access'] [$groupKey] [$accessKey]);
					}
				}
				if (!empty($group)) {
					$timelinesAccess [$groupKey] = $group;
				}
			}
			if (!empty($timelinesAccess)) {
				$configuration ['timelines_access'] = $timelinesAccess;
			}
		}

		if (!empty($configuration)) {
			$compact ['configuration'] = $configuration;
		}
	}

	return $compact;
}

/**
 * Read a previously stored Mastodon instance snapshot from the plugin options.
 * @param array<string, string> $options
 * @return array<string, mixed>
 */
function plugin_mastodon_saved_instance_document($options) {
	$options = is_array($options) ? $options : array();
	$instanceUrl = plugin_mastodon_normalize_instance_url(isset($options ['instance_url']) ? $options ['instance_url'] : '');
	if ($instanceUrl === '') {
		return array();
	}
	$storedUrl = plugin_mastodon_normalize_instance_url(isset($options ['instance_info_url']) ? $options ['instance_info_url'] : '');
	if ($storedUrl !== '' && $storedUrl !== $instanceUrl) {
		return array();
	}
	$json = trim((string) (isset($options ['instance_info_json']) ? $options ['instance_info_json'] : ''));
	if ($json === '') {
		return array();
	}

	$cacheKey = sha1($instanceUrl . '|' . $json);
	$cached = plugin_mastodon_runtime_cache_get('instance_document_saved', $cacheKey, $hit);
	if ($hit && is_array($cached)) {
		return $cached;
	}

	$decoded = json_decode($json, true);
	if (!is_array($decoded)) {
		return array();
	}
	$document = plugin_mastodon_compact_instance_document($decoded);
	if ($document === array()) {
		return array();
	}
	plugin_mastodon_runtime_cache_set('instance_document_saved', $cacheKey, $document);
	plugin_mastodon_apcu_store('instance_document:' . sha1($instanceUrl), $document, 900);
	return $document;
}

/**
 * Persist a compact Mastodon instance snapshot inside the plugin configuration.
 *
 * Keeping the snapshot in the FlatPress plugin configuration makes the data available on
 * every supported host, survives PHP worker restarts, and lets the plugin reuse the values
 * immediately on later requests without adding a mandatory network round-trip.
 *
 * @param array<string, string> $options
 * @param array<string, mixed> $document
 * @return bool
 */
function plugin_mastodon_store_instance_document($options, $document) {
	$options = is_array($options) ? $options : array();
	$instanceUrl = plugin_mastodon_normalize_instance_url(isset($options ['instance_url']) ? $options ['instance_url'] : '');
	$document = plugin_mastodon_compact_instance_document($document);
	if ($instanceUrl === '' || $document === array()) {
		return false;
	}

	$json = json_encode($document, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if (!is_string($json) || $json === '') {
		return false;
	}

	$normalizedOptions = plugin_mastodon_clear_saved_instance_info($options);
	$normalizedOptions ['instance_info_url'] = $instanceUrl;
	$normalizedOptions ['instance_info_json'] = $json;
	$normalizedOptions ['instance_info_fetched_at'] = gmdate('c');
	$normalizedOptions ['instance_info_error'] = '';
	$normalizedOptions ['instance_info_error_at'] = '';

	$storedDocument = plugin_mastodon_saved_instance_document($normalizedOptions);
	$changed = (
		trim((string) (isset($options ['instance_info_url']) ? $options ['instance_info_url'] : '')) !== $instanceUrl
		|| trim((string) (isset($options ['instance_info_json']) ? $options ['instance_info_json'] : '')) !== $json
		|| $storedDocument !== $document
	);
	if (!$changed && trim((string) (isset($options ['instance_info_fetched_at']) ? $options ['instance_info_fetched_at'] : '')) !== '') {
		plugin_mastodon_apcu_store('instance_document:' . sha1($instanceUrl), $document, 900);
		plugin_mastodon_runtime_cache_set('instance_document', $instanceUrl, $document);
		return true;
	}

	$result = plugin_mastodon_save_options($normalizedOptions);
	if ($result) {
		plugin_mastodon_apcu_store('instance_document:' . sha1($instanceUrl), $document, 900);
		plugin_mastodon_runtime_cache_set('instance_document', $instanceUrl, $document);
	}
	return $result;
}

/**
 * Persist the latest instance-information refresh error for the admin diagnostics view.
 * @param array<string, string> $options
 * @param string $message
 * @return bool
 */
function plugin_mastodon_store_instance_error($options, $message) {
	$options = is_array($options) ? $options : array();
	$message = trim((string) $message);
	if ($message === '') {
		$message = 'request_failed';
	}
	$options ['instance_info_error'] = $message;
	$options ['instance_info_error_at'] = gmdate('c');
	return plugin_mastodon_save_options($options);
}

/**
 * Force a live refresh of the Mastodon instance information and persist the compact snapshot.
 * @param array<string, string> $options
 * @return array<string, mixed>
 */
function plugin_mastodon_refresh_instance_information($options) {
	$response = plugin_mastodon_mastodon_json($options, 'GET', '/api/v2/instance', array(), false);
	if (!empty($response ['ok']) && !empty($response ['json']) && is_array($response ['json'])) {
		$document = plugin_mastodon_compact_instance_document($response ['json']);
		if ($document !== array()) {
			plugin_mastodon_store_instance_document($options, $document);
			$response ['json'] = $document;
			return $response;
		}
		$response ['ok'] = false;
		$response ['error'] = 'invalid_instance_document';
	}
	plugin_mastodon_store_instance_error($options, plugin_mastodon_response_error_message($response));
	return $response;
}

/**
 * Return the default counters for the last content synchronization.
 * @return array<string, int>
 */
function plugin_mastodon_default_content_stats() {
	return array(
		'imported_entries' => 0,
		'updated_entries' => 0,
		'exported_entries' => 0,
		'updated_remote_entries' => 0,
		'imported_comments' => 0,
		'updated_local_comments' => 0,
		'exported_comments' => 0,
		'updated_remote_comments' => 0
	);
}

/**
 * Return the default counters for the last deletion synchronization.
 * @return array<string, int>
 */
function plugin_mastodon_default_deletion_stats() {
	return array(
		'deleted_local_entries' => 0,
		'deleted_local_comments' => 0,
		'deleted_remote_entries' => 0,
		'deleted_remote_comments' => 0
	);
}

/**
 * Return the default runtime state structure.
 * @return array<string, mixed>
 */
function plugin_mastodon_default_state() {
	return array(
		'version' => 5,
		'last_run' => '',
		'last_deletion_run' => '',
		'deletions_pending' => 0,
		'deletions_pending_scope' => 'full',
		'last_error' => '',
		'last_remote_status_id' => '',
		'entries' => array(),
		'entries_remote' => array(),
		'comments' => array(),
		'comments_remote' => array(),
		'comment_tombstones' => array(),
		'pending_comment_remote_rechecks' => array(),
		'content_stats' => plugin_mastodon_default_content_stats(),
		'deletion_stats' => plugin_mastodon_default_deletion_stats()
	);
}

/**
 * Return the legacy OAuth scopes used before scope discovery was added.
 * @return string
 */
function plugin_mastodon_oauth_legacy_scopes() {
	return 'read:accounts read:statuses write:statuses write:media';
}

/**
 * Return the stricter OAuth scopes preferred on current Mastodon instances.
 * @return string
 */
function plugin_mastodon_oauth_profile_scopes() {
	return 'profile read:statuses write:statuses write:media';
}

/**
 * Fetch OAuth authorization server metadata for the configured Mastodon instance.
 * @param array<string, string> $options
 * @return array<string, mixed>
 */
function plugin_mastodon_oauth_server_metadata($options) {
	$instanceUrl = plugin_mastodon_normalize_instance_url(isset($options ['instance_url']) ? $options ['instance_url'] : '');
	if ($instanceUrl === '') {
		return array('ok' => false, 'code' => 0, 'headers' => array(), 'body' => '', 'json' => array(), 'error' => 'Missing instance URL');
	}
	$cacheKey = sha1($instanceUrl);
	$cached = plugin_mastodon_runtime_cache_get('oauth_server_metadata', $cacheKey, $hit);
	if ($hit && is_array($cached)) {
		return $cached;
	}
	$response = plugin_mastodon_mastodon_json($options, 'GET', '/.well-known/oauth-authorization-server', array(), false);
	return plugin_mastodon_runtime_cache_set('oauth_server_metadata', $cacheKey, $response);
}

/**
 * Return the OAuth scopes supported by the configured Mastodon instance, if discoverable.
 * @param array<string, string> $options
 * @return array<int, string>
 */
function plugin_mastodon_oauth_supported_scopes($options) {
	$response = plugin_mastodon_oauth_server_metadata($options);
	if (empty($response ['ok']) || empty($response ['json']) || !is_array($response ['json'])) {
		return array();
	}
	$scopes = array();
	if (!empty($response ['json'] ['scopes_supported']) && is_array($response ['json'] ['scopes_supported'])) {
		foreach ($response ['json'] ['scopes_supported'] as $scope) {
			$scope = trim((string) $scope);
			if ($scope !== '') {
				$scopes [] = $scope;
			}
		}
	} elseif (!empty($response ['json'] ['scopes_supported']) && is_string($response ['json'] ['scopes_supported'])) {
		$rawScopes = preg_split('/\s+/', trim((string) $response ['json'] ['scopes_supported']));
		if (is_array($rawScopes)) {
			foreach ($rawScopes as $scope) {
				$scope = trim((string) $scope);
				if ($scope !== '') {
					$scopes [] = $scope;
				}
			}
		}
	}
	if (empty($scopes)) {
		return array();
	}
	$scopes = array_values(array_unique($scopes));
	return $scopes;
}

/**
 * Determine whether the configured Mastodon instance advertises support for a scope.
 * @param array<string, string> $options
 * @param string $scope
 * @return bool
 */
function plugin_mastodon_oauth_scope_supported($options, $scope) {
	$scope = trim((string) $scope);
	if ($scope === '') {
		return false;
	}
	$supportedScopes = plugin_mastodon_oauth_supported_scopes($options);
	if (empty($supportedScopes)) {
		return false;
	}
	return in_array($scope, $supportedScopes, true);
}

/**
 * Return the preferred OAuth scopes for the configured Mastodon instance.
 *
 * Newer instances that advertise the `profile` scope use it to keep the token narrower.
 * Older instances or discovery failures fall back to `read:accounts` for compatibility.
 *
 * @param array<string, string> $options
 * @return string
 */
function plugin_mastodon_oauth_preferred_scopes($options) {
	if (plugin_mastodon_oauth_scope_supported($options, 'profile')) {
		return plugin_mastodon_oauth_profile_scopes();
	}
	return plugin_mastodon_oauth_legacy_scopes();
}

/**
 * Return the OAuth scopes that the currently registered app may safely request.
 *
 * Existing installations without a stored registration scope are treated as legacy
 * registrations so that the plugin does not suddenly request `profile` for an app
 * that was originally registered with `read:accounts`.
 *
 * @param array<string, string> $options
 * @return string
 */
function plugin_mastodon_oauth_scopes($options = array()) {
	$options = is_array($options) ? $options : array();
	$storedScopes = trim((string) (isset($options ['oauth_registered_scopes']) ? $options ['oauth_registered_scopes'] : ''));
	if ($storedScopes !== '') {
		return $storedScopes;
	}
	if (!empty($options ['client_id']) || !empty($options ['client_secret'])) {
		return plugin_mastodon_oauth_legacy_scopes();
	}
	return plugin_mastodon_oauth_preferred_scopes($options);
}

/**
 * Return a value from the request-local plugin cache.
 *
 * @param string $bucket Cache bucket name.
 * @param string $key Cache entry key.
 * @param-out bool $hit Whether the requested cache entry was found.
 * @return mixed
 */
function plugin_mastodon_runtime_cache_get($bucket, $key, &$hit = false) {
	$bucket = (string) $bucket;
	$key = (string) $key;
	$hit = false;
	if (!isset($GLOBALS ['plugin_mastodon_runtime_cache']) || !is_array($GLOBALS ['plugin_mastodon_runtime_cache'])) {
		$GLOBALS ['plugin_mastodon_runtime_cache'] = array();
	}
	if (!isset($GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket]) || !is_array($GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket])) {
		return null;
	}
	if (array_key_exists($key, $GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket])) {
		$hit = true;
		return $GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket] [$key];
	}
	return null;
}

/**
 * Store a value in the request-local plugin cache.
 * @param string $bucket
 * @param string $key
 * @param mixed $value
 * @return mixed
 */
function plugin_mastodon_runtime_cache_set($bucket, $key, $value) {
	$bucket = (string) $bucket;
	$key = (string) $key;
	if (!isset($GLOBALS ['plugin_mastodon_runtime_cache']) || !is_array($GLOBALS ['plugin_mastodon_runtime_cache'])) {
		$GLOBALS ['plugin_mastodon_runtime_cache'] = array();
	}
	if (!isset($GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket]) || !is_array($GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket])) {
		$GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket] = array();
	}
	$GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket] [$key] = $value;
	return $value;
}

/**
 * Clear one request-local plugin cache bucket or the complete cache.
 * @param string $bucket
 * @return void
 */
function plugin_mastodon_runtime_cache_clear($bucket = '') {
	$bucket = (string) $bucket;
	if ($bucket === '') {
		unset($GLOBALS ['plugin_mastodon_runtime_cache']);
		return;
	}
	if (isset($GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket])) {
		unset($GLOBALS ['plugin_mastodon_runtime_cache'] [$bucket]);
	}
}

/**
 * Return the FlatPress configuration, preferring the early-loaded core cache.
 * @return array<string, mixed>
 */
function plugin_mastodon_fp_config() {
	$cached = plugin_mastodon_runtime_cache_get('core', 'fp_config', $hit);
	if ($hit && is_array($cached)) {
		return plugin_mastodon_state_normalize($cached);
	}

	$config = array();
	if (isset($GLOBALS ['EARLY_FP_CONFIG']) && is_array($GLOBALS ['EARLY_FP_CONFIG'])) {
		$config = $GLOBALS ['EARLY_FP_CONFIG'];
	} else {
		global $fp_config;
		if (isset($fp_config) && is_array($fp_config)) {
			$config = $fp_config;
		} elseif (function_exists('config_load')) {
			$loaded = config_load();
			if (is_array($loaded)) {
				$config = $loaded;
			}
		}
	}

	if (!isset($GLOBALS ['EARLY_FP_CONFIG']) && $config !== array()) {
		$GLOBALS ['EARLY_FP_CONFIG'] = $config;
	}
	if (!isset($GLOBALS ['fp_config']) && $config !== array()) {
		$GLOBALS ['fp_config'] = $config;
	}
	return plugin_mastodon_runtime_cache_set('core', 'fp_config', $config);
}

/**
 * Read a nested FlatPress configuration value.
 * @param list<string> $path
 * @param mixed $default
 * @return mixed
 */
function plugin_mastodon_fp_config_value(array $path, $default = null) {
	$config = plugin_mastodon_fp_config();
	$cursor = $config;
	foreach ($path as $segment) {
		if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
			return $default;
		}
		$cursor = $cursor [$segment];
	}
	return $cursor;
}

/**
 * Return the configured FlatPress time offset in seconds.
 * @return int
 */
function plugin_mastodon_fp_timeoffset_seconds() {
	$offset = plugin_mastodon_fp_config_value(array('locale', 'timeoffset'), 0);
	if (!is_numeric($offset)) {
		return 0;
	}
	return (int) round(((float) $offset) * 3600);
}

/**
 * Return the configured FlatPress time offset in whole hours.
 * @return int
 */
function plugin_mastodon_fp_timeoffset_hours() {
	return (int) floor(plugin_mastodon_fp_timeoffset_seconds() / 3600);
}

/**
 * Format the configured FlatPress time offset as a UTC label for admin users.
 * @return string
 */
function plugin_mastodon_fp_timeoffset_label() {
	$seconds = plugin_mastodon_fp_timeoffset_seconds();
	$sign = $seconds < 0 ? '-' : '+';
	$seconds = abs($seconds);
	$hours = (int) floor($seconds / 3600);
	$minutes = (int) floor(($seconds % 3600) / 60);
	return sprintf('UTC%s%02d:%02d', $sign, $hours, $minutes);
}

/**
 * Convert a real Unix timestamp into FlatPress's offset-adjusted timestamp model.
 * @param int|float|string $timestamp
 * @return int
 */
function plugin_mastodon_timestamp_to_flatpress_time($timestamp) {
	if (!is_numeric($timestamp)) {
		return 0;
	}
	$timestamp = (int) $timestamp;
	if ($timestamp <= 0) {
		return 0;
	}
	return $timestamp + plugin_mastodon_fp_timeoffset_seconds();
}

/**
 * Convert a HH:MM time value into minutes after midnight.
 * @param string $time
 * @return int
 */
function plugin_mastodon_sync_time_to_minutes($time) {
	$time = plugin_mastodon_normalize_sync_time($time);
	list($hour, $minute) = array_map('intval', explode(':', $time, 2));
	return ($hour * 60) + $minute;
}

/**
 * Convert minutes after midnight into a normalized HH:MM time value.
 * @param int $minutes
 * @return string
 */
function plugin_mastodon_minutes_to_sync_time($minutes) {
	$minutes = (int) $minutes;
	$minutes = $minutes % 1440;
	if ($minutes < 0) {
		$minutes += 1440;
	}
	$hour = (int) floor($minutes / 60);
	$minute = $minutes % 60;
	return sprintf('%02d:%02d', $hour, $minute);
}

/**
 * Convert the stored UTC synchronization time to the FlatPress-local admin time.
 * @param string $time
 * @return string
 */
function plugin_mastodon_sync_time_utc_to_local($time) {
	$minutes = plugin_mastodon_sync_time_to_minutes($time);
	$offsetMinutes = (int) round(plugin_mastodon_fp_timeoffset_seconds() / 60);
	return plugin_mastodon_minutes_to_sync_time($minutes + $offsetMinutes);
}

/**
 * Convert a FlatPress-local admin synchronization time back to stored UTC time.
 * @param string $time
 * @return string
 */
function plugin_mastodon_sync_time_local_to_utc($time) {
	$minutes = plugin_mastodon_sync_time_to_minutes($time);
	$offsetMinutes = (int) round(plugin_mastodon_fp_timeoffset_seconds() / 60);
	return plugin_mastodon_minutes_to_sync_time($minutes - $offsetMinutes);
}

/**
 * Format a stored UTC timestamp for the admin panel using FlatPress local time and configured formats.
 * @param string $value
 * @return string
 */
function plugin_mastodon_format_admin_datetime($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}
	$timestamp = strtotime($value);
	if ($timestamp === false) {
		return $value;
	}
	$localTimestamp = (int) $timestamp + plugin_mastodon_fp_timeoffset_seconds();
	$dateFormat = plugin_mastodon_fp_config_value(array('locale', 'dateformat'), '%Y-%m-%d');
	$timeFormat = plugin_mastodon_fp_config_value(array('locale', 'timeformat'), '%H:%M:%S');
	$format = trim((string) $dateFormat . ' ' . (string) $timeFormat);
	if ($format === '') {
		$format = '%Y-%m-%d %H:%M:%S';
	}
	if (function_exists('date_strformat')) {
		try {
			$formatted = date_strformat($format, $localTimestamp);
		} catch (Exception $exception) {
			$formatted = date('Y-m-d H:i:s', $localTimestamp);
		}
	} else {
		$formatted = date('Y-m-d H:i:s', $localTimestamp);
	}
	return trim((string) $formatted) . ' (' . plugin_mastodon_fp_timeoffset_label() . ')';
}

/**
 * Read a file through the FlatPress I/O layer when available.
 * @param string $file
 * @param mixed $prestat
 * @return string|false
 */
function plugin_mastodon_io_read_file($file, $prestat = null) {
	if (function_exists('io_load_file')) {
		return io_load_file($file, $prestat);
	}
	return @file_get_contents($file);
}

/**
 * Read a file without FlatPress request-local caches.
 * @param string $file
 * @param bool $assumeExists
 * @return string|false
 */
function plugin_mastodon_io_read_file_uncached($file, $assumeExists = false) {
	if (function_exists('io_load_file_uncached')) {
		return io_load_file_uncached($file, (bool) $assumeExists);
	}
	if (!$assumeExists && !@file_exists($file)) {
		return false;
	}
	clearstatcache(true, $file);
	return @file_get_contents($file);
}

/**
 * Write a file through the FlatPress I/O layer when available.
 * @param string $file
 * @param string $payload
 * @return bool
 */
function plugin_mastodon_io_write_file($file, $payload) {
	if (function_exists('io_write_file')) {
		return (bool) io_write_file($file, $payload);
	}
	return (@file_put_contents($file, $payload) !== false);
}

/**
 * Append to a file via the FlatPress I/O layer.
 * @param string $file
 * @param string $payload
 * @return bool
 */
function plugin_mastodon_io_append_file($file, $payload) {
	$existing = '';
	$prestat = plugin_mastodon_file_prestat($file);
	if (!empty($prestat ['exists'])) {
		$loaded = plugin_mastodon_io_read_file($file, $prestat);
		if (is_string($loaded)) {
			$existing = $loaded;
		}
	}
	return plugin_mastodon_io_write_file($file, $existing . (string) $payload);
}

/**
 * Check whether shared APCu caching is available for the plugin.
 * @return bool
 */
function plugin_mastodon_apcu_enabled() {
	return function_exists('is_apcu_on') && is_apcu_on();
}

/**
 * Build the namespaced APCu key used by this plugin.
 * @param string $suffix
 * @return string
 */
function plugin_mastodon_apcu_cache_key($suffix) {
	$suffix = 'mastodon:' . (string) $suffix;
	if (function_exists('apcu_key')) {
		return apcu_key($suffix);
	}
	return $suffix;
}

/**
 * Fetch a value from APCu through the FlatPress namespace helper.
 * @param string $suffix
 * @param bool|null $hit
 * @return mixed
 */
function plugin_mastodon_apcu_fetch($suffix, &$hit = null) {
	$hit = false;
	if (!plugin_mastodon_apcu_enabled() || !function_exists('apcu_get')) {
		return null;
	}
	return apcu_get('mastodon:' . (string) $suffix, $hit);
}

/**
 * Store a value in APCu through the FlatPress namespace helper.
 * @param string $suffix
 * @param mixed $value
 * @param int $ttl
 * @return bool
 */
function plugin_mastodon_apcu_store($suffix, $value, $ttl) {
	if (!plugin_mastodon_apcu_enabled() || !function_exists('apcu_set')) {
		return false;
	}
	$ttl = max(0, (int) $ttl);
	return (bool) apcu_set('mastodon:' . (string) $suffix, $value, $ttl);
}

/**
 * Delete a value from APCu using the FlatPress namespace key builder.
 * @param string $suffix
 * @return void
 */
function plugin_mastodon_apcu_delete($suffix) {
	if (!plugin_mastodon_apcu_enabled() || !function_exists('apcu_delete')) {
		return;
	}
	$cacheKey = plugin_mastodon_apcu_cache_key($suffix);
	@apcu_delete($cacheKey);
}

/**
 * Read a cheap file metadata snapshot for cache validation.
 * @param string $path
 * @return array{exists:bool,mt:int|null,sz:int|null}
 */
function plugin_mastodon_file_prestat($path) {
	$path = (string) $path;
	clearstatcache(true, $path);
	if (!@file_exists($path)) {
		return array('exists' => false, 'mt' => null, 'sz' => null);
	}
	$mtime = @filemtime($path);
	$size = @filesize($path);
	return array(
		'exists' => true,
		'mt' => ($mtime === false ? null : (int) $mtime),
		'sz' => ($size === false ? null : (int) $size)
	);
}

/**
 * Convert a file metadata snapshot into a stable cache signature.
 * @param array{exists:bool,mt:int|null,sz:int|null} $prestat
 * @return string
 */
function plugin_mastodon_file_prestat_signature($prestat) {
	if (empty($prestat ['exists'])) {
		return 'missing';
	}
	return (isset($prestat ['mt']) && $prestat ['mt'] !== null ? (string) $prestat ['mt'] : 'na') . ':' . (isset($prestat ['sz']) && $prestat ['sz'] !== null ? (string) $prestat ['sz'] : 'na');
}

/**
 * Load the saved plugin options and merge them with defaults.
 * @return array<string, string>
 */
function plugin_mastodon_get_options() {
	$defaults = plugin_mastodon_default_options();
	$cached = plugin_mastodon_runtime_cache_get('options', 'normalized', $hit);
	if ($hit && is_array($cached)) {
		$options = array_merge($defaults, $cached);
		foreach (array_keys($defaults) as $optionKey) {
			$options [$optionKey] = isset($options [$optionKey]) ? (string) $options [$optionKey] : (string) $defaults [$optionKey];
		}
		$options ['instance_url'] = plugin_mastodon_normalize_instance_url($options ['instance_url']);
		$options ['sync_time'] = plugin_mastodon_normalize_sync_time($options ['sync_time']);
		$options ['sync_start_date'] = plugin_mastodon_normalize_sync_start_date($options ['sync_start_date']);
		$options ['update_local_from_remote'] = plugin_mastodon_normalize_update_local_from_remote($options ['update_local_from_remote']);
		$options ['import_synced_comments_as_entries'] = plugin_mastodon_normalize_import_synced_comments_as_entries($options ['import_synced_comments_as_entries']);
		$options ['quote_imported_reply_parent'] = plugin_mastodon_normalize_quote_imported_reply_parent($options ['quote_imported_reply_parent']);
		$options ['delete_sync_enabled'] = plugin_mastodon_normalize_delete_sync_enabled($options ['delete_sync_enabled']);
		$options ['oauth_registered_scopes'] = trim((string) $options ['oauth_registered_scopes']);
		return $options;
	}

	$config = plugin_mastodon_fp_config_value(array('plugins', 'mastodon'), array());
	if (!is_array($config)) {
		$config = array();
	}
	$normalizedConfig = array();
	foreach (array_keys($defaults) as $optionKey) {
		if (isset($config [$optionKey])) {
			$normalizedConfig [$optionKey] = (string) $config [$optionKey];
		}
	}
	$config = $normalizedConfig;

	foreach (array('password', 'client_secret', 'access_token', 'authorization_code') as $secretKey) {
		if (isset($config [$secretKey]) && $config [$secretKey] !== '') {
			$config [$secretKey] = plugin_mastodon_secret_decode($config [$secretKey]);
		}
	}

	$options = array_merge($defaults, $config);
	$options ['instance_url'] = plugin_mastodon_normalize_instance_url($options ['instance_url']);
	$options ['sync_time'] = plugin_mastodon_normalize_sync_time($options ['sync_time']);
	$options ['sync_start_date'] = plugin_mastodon_normalize_sync_start_date($options ['sync_start_date']);
	$options ['update_local_from_remote'] = plugin_mastodon_normalize_update_local_from_remote($options ['update_local_from_remote']);
	$options ['import_synced_comments_as_entries'] = plugin_mastodon_normalize_import_synced_comments_as_entries($options ['import_synced_comments_as_entries']);
	$options ['quote_imported_reply_parent'] = plugin_mastodon_normalize_quote_imported_reply_parent($options ['quote_imported_reply_parent']);
	$options ['delete_sync_enabled'] = plugin_mastodon_normalize_delete_sync_enabled($options ['delete_sync_enabled']);
	$options ['oauth_registered_scopes'] = trim((string) $options ['oauth_registered_scopes']);
	plugin_mastodon_runtime_cache_set('options', 'normalized', $options);
	return $options;
}

/**
 * Persist plugin options.
 * @param array<string, string> $options
 * @return bool
 */
function plugin_mastodon_save_options($options) {
	$defaults = plugin_mastodon_default_options();
	$previousOptions = plugin_mastodon_get_options();
	$merged = array_merge($defaults, is_array($options) ? $options : array());

	$previousInstanceUrl = plugin_mastodon_normalize_instance_url(isset($previousOptions ['instance_url']) ? $previousOptions ['instance_url'] : '');
	$merged ['instance_url'] = plugin_mastodon_normalize_instance_url(isset($merged ['instance_url']) ? $merged ['instance_url'] : '');
	$mergedInstanceInfoUrl = plugin_mastodon_normalize_instance_url(isset($merged ['instance_info_url']) ? $merged ['instance_info_url'] : '');
	if ($previousInstanceUrl !== '' && $merged ['instance_url'] !== $previousInstanceUrl && $mergedInstanceInfoUrl !== $merged ['instance_url']) {
		$merged = plugin_mastodon_clear_saved_instance_info($merged);
	}

	foreach (array('instance_url', 'username', 'sync_time', 'sync_start_date', 'update_local_from_remote', 'import_synced_comments_as_entries', 'quote_imported_reply_parent', 'delete_sync_enabled', 'last_authorize_url', 'oauth_registered_scopes', 'instance_info_url', 'instance_info_json', 'instance_info_fetched_at', 'instance_info_error', 'instance_info_error_at') as $plainKey) {
		plugin_addoption('mastodon', $plainKey, (string) $merged [$plainKey]);
	}

	foreach (array('password', 'client_id', 'client_secret', 'access_token', 'authorization_code') as $secretKey) {
		$value = (string) $merged [$secretKey];
		if ($secretKey === 'client_id') {
			plugin_addoption('mastodon', $secretKey, $value);
		} else {
			plugin_addoption('mastodon', $secretKey, plugin_mastodon_secret_encode($value));
		}
	}

	$result = plugin_saveoptions('mastodon');
	plugin_mastodon_runtime_cache_clear('options');
	plugin_mastodon_runtime_cache_clear('core');
	plugin_mastodon_runtime_cache_clear('instance_document');
	plugin_mastodon_runtime_cache_clear('instance_document_saved');
	if ($result) {
		global $fp_config;
		if (isset($fp_config) && is_array($fp_config)) {
			$GLOBALS ['EARLY_FP_CONFIG'] = $fp_config;
		}
		$merged ['instance_url'] = plugin_mastodon_normalize_instance_url((string) $merged ['instance_url']);
		$merged ['sync_time'] = plugin_mastodon_normalize_sync_time((string) $merged ['sync_time']);
		$merged ['sync_start_date'] = plugin_mastodon_normalize_sync_start_date((string) $merged ['sync_start_date']);
		$merged ['update_local_from_remote'] = plugin_mastodon_normalize_update_local_from_remote((string) $merged ['update_local_from_remote']);
		$merged ['import_synced_comments_as_entries'] = plugin_mastodon_normalize_import_synced_comments_as_entries((string) $merged ['import_synced_comments_as_entries']);
		$merged ['quote_imported_reply_parent'] = plugin_mastodon_normalize_quote_imported_reply_parent((string) $merged ['quote_imported_reply_parent']);
		$merged ['delete_sync_enabled'] = plugin_mastodon_normalize_delete_sync_enabled((string) $merged ['delete_sync_enabled']);
		$merged ['oauth_registered_scopes'] = trim((string) $merged ['oauth_registered_scopes']);
		if ($previousInstanceUrl !== '' && $previousInstanceUrl !== $merged ['instance_url']) {
			plugin_mastodon_apcu_delete('instance_document:' . sha1($previousInstanceUrl));
		}
		if ($merged ['instance_url'] !== '') {
			if (!empty($merged ['instance_info_json'])) {
				$storedDocument = plugin_mastodon_saved_instance_document($merged);
				if ($storedDocument !== array()) {
					plugin_mastodon_apcu_store('instance_document:' . sha1($merged ['instance_url']), $storedDocument, 900);
				}
			} else {
				plugin_mastodon_apcu_delete('instance_document:' . sha1($merged ['instance_url']));
			}
		}
		plugin_mastodon_runtime_cache_set('core', 'fp_config', is_array($GLOBALS ['EARLY_FP_CONFIG']) ? $GLOBALS ['EARLY_FP_CONFIG'] : array());
		plugin_mastodon_runtime_cache_set('options', 'normalized', $merged);
		if ($merged ['delete_sync_enabled'] !== '1') {
			$state = plugin_mastodon_state_read();
			$stateChanged = false;
			if (!empty($state ['deletions_pending'])) {
				$state ['deletions_pending'] = 0;
				$stateChanged = true;
			}
			if (!empty($state ['pending_comment_remote_rechecks'])) {
				$state ['pending_comment_remote_rechecks'] = array();
				$stateChanged = true;
			}
			if (!isset($state ['deletions_pending_scope']) || (string) $state ['deletions_pending_scope'] !== 'full') {
				$state ['deletions_pending_scope'] = 'full';
				$stateChanged = true;
			}
			if ($stateChanged) {
				plugin_mastodon_state_write($state);
			}
		}
	}
	return $result;
}

/**
 * Build the encryption key used for stored secrets.
 * @return string
 */
function plugin_mastodon_secret_key() {
	$key = plugin_mastodon_fp_config_value(array('general', 'blogid'), '');
	$key = is_string($key) ? $key : '';
	if ($key === '' && defined('BLOG_BASEURL')) {
		$key = BLOG_BASEURL;
	}
	if ($key === '') {
		$key = 'flatpress-mastodon';
	}
	return hash('sha256', $key, true);
}

/**
 * Encode a secret value before storing it in the configuration.
 * @param string $value
 * @return string
 */
function plugin_mastodon_secret_encode($value) {
	$value = (string) $value;
	if ($value === '') {
		return '';
	}
	if (function_exists('openssl_encrypt') && function_exists('openssl_cipher_iv_length')) {
		$ivLength = openssl_cipher_iv_length('AES-256-CBC');
		if (is_int($ivLength) && $ivLength > 0) {
			$iv = function_exists('random_bytes') ? random_bytes($ivLength) : openssl_random_pseudo_bytes($ivLength);
			$cipher = openssl_encrypt($value, 'AES-256-CBC', plugin_mastodon_secret_key(), OPENSSL_RAW_DATA, $iv);
			if ($cipher !== false) {
				return 'enc:' . base64_encode($iv . $cipher);
			}
		}
	}
	return 'plain:' . base64_encode($value);
}

/**
 * Decode a previously stored secret value.
 * @param string $value
 * @return string
 */
function plugin_mastodon_secret_decode($value) {
	$value = (string) $value;
	if ($value === '') {
		return '';
	}
	if (strpos($value, 'enc:') === 0) {
		$blob = base64_decode(substr($value, 4), true);
		if ($blob === false) {
			return '';
		}
		if (function_exists('openssl_decrypt') && function_exists('openssl_cipher_iv_length')) {
			$ivLength = openssl_cipher_iv_length('AES-256-CBC');
			$iv = substr($blob, 0, $ivLength);
			$cipher = substr($blob, $ivLength);
			$plain = openssl_decrypt($cipher, 'AES-256-CBC', plugin_mastodon_secret_key(), OPENSSL_RAW_DATA, $iv);
			return ($plain === false) ? '' : (string) $plain;
		}
		return '';
	}
	if (strpos($value, 'plain:') === 0) {
		$plain = base64_decode(substr($value, 6), true);
		return ($plain === false) ? '' : (string) $plain;
	}
	return $value;
}

/**
 * Normalize the configured Mastodon instance URL.
 * @param string $url
 * @return string
 */
function plugin_mastodon_normalize_instance_url($url) {
	$url = trim((string) $url);
	if ($url === '') {
		return '';
	}
	if (!preg_match('!^https?://!i', $url)) {
		$url = 'https://' . $url;
	}
	$parts = @parse_url($url);
	if (!is_array($parts) || empty($parts ['host'])) {
		return '';
	}
	$scheme = isset($parts ['scheme']) ? strtolower($parts ['scheme']) : 'https';
	$host = strtolower($parts ['host']);
	$port = isset($parts ['port']) ? ':' . (int) $parts ['port'] : '';
	$path = isset($parts ['path']) ? trim($parts ['path'], '/') : '';
	$normalized = $scheme . '://' . $host . $port;
	if ($path !== '') {
		$normalized .= '/' . $path;
	}
	return rtrim($normalized, '/');
}

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

	$options = plugin_mastodon_get_options();
	$username = isset($options ['username']) ? (string) $options ['username'] : '';
	if (plugin_mastodon_normalize_head_username($username) === '') {
		return;
	}

	$instanceUrl = isset($options ['instance_url']) ? (string) $options ['instance_url'] : '';
	$profileUrl = plugin_mastodon_profile_url($instanceUrl, $username);
	$creator = plugin_mastodon_fediverse_creator_value($instanceUrl, $username);
	if ($profileUrl === '' || $creator === '') {
		return;
	}

	$profileUrl = htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8');
	$creator = htmlspecialchars($creator, ENT_QUOTES, 'UTF-8');

	echo '
		<!-- BOF Mastodon head -->
		<link rel="me" href="' . $profileUrl . '">
		<meta name="fediverse:creator" content="' . $creator . '">
		<!-- EOF Mastodon head -->
	';
}

add_action('wp_head', 'plugin_mastodon_head', 10);

/**
 * Normalize a FlatPress locale string to a Mastodon-compatible ISO 639-1 code.
 *
 * Note: FlatPress stores locale identifiers such as "en-us" or "de-de",
 * while Mastodon expects the primary language code only.
 *
 * @param string $locale
 * @return string
 */
function plugin_mastodon_normalize_status_language($locale) {
	$locale = strtolower(trim((string) $locale));
	if ($locale === '') {
		return '';
	}

	$locale = str_replace('_', '-', $locale);
	if (!preg_match('/^([a-z]{2})(?:-[a-z0-9]{1,8})*$/', $locale, $matches)) {
		return '';
	}

	return isset($matches [1]) ? (string) $matches [1] : '';
}

/**
 * Read the configured FlatPress locale and return the Mastodon language code.
 *
 * Note: The FlatPress core already loads the configuration before plugins are
 * executed, therefore the global configuration is the authoritative source.
 *
 * @return string
 */
function plugin_mastodon_configured_status_language() {
	$cached = plugin_mastodon_runtime_cache_get('settings', 'status_language', $hit);
	if ($hit && is_string($cached)) {
		return $cached;
	}

	$locale = plugin_mastodon_fp_config_value(array('locale', 'lang'), '');
	$locale = is_string($locale) ? $locale : '';
	if ($locale === '' && defined('LANG_DEFAULT')) {
		$locale = (string) LANG_DEFAULT;
	}

	return (string) plugin_mastodon_runtime_cache_set('settings', 'status_language', plugin_mastodon_normalize_status_language($locale));
}

/**
 * Normalize the configured daily sync time.
 * @param string $time
 * @return string
 */
function plugin_mastodon_normalize_sync_time($time) {
	$time = trim((string) $time);
	if (!preg_match('/^\d{1,2}:\d{2}$/', $time)) {
		return PLUGIN_MASTODON_DEFAULT_SYNC_TIME;
	}
	list($hour, $minute) = array_map('intval', explode(':', $time, 2));
	if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
		return PLUGIN_MASTODON_DEFAULT_SYNC_TIME;
	}
	return str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) $minute, 2, '0', STR_PAD_LEFT);
}

/**
 * Normalize the configured sync start date.
 * @param string $value
 * @return string
 */
function plugin_mastodon_normalize_sync_start_date($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}
	if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
		return '';
	}
	$parts = explode('-', $value);
	if (count($parts) !== 3) {
		return '';
	}
	$year = (int) $parts [0];
	$month = (int) $parts [1];
	$day = (int) $parts [2];
	if (!checkdate($month, $day, $year)) {
		return '';
	}
	return sprintf('%04d-%02d-%02d', $year, $month, $day);
}

/**
 * Normalize a boolean-like option value to the stored string representation.
 * @param mixed $value
 * @return string
 */
function plugin_mastodon_normalize_boolean_option($value) {
	if (is_bool($value)) {
		return $value ? '1' : '0';
	}
	$value = strtolower(trim((string) $value));
	if ($value === '1' || $value === 'true' || $value === 'yes' || $value === 'on') {
		return '1';
	}
	return '0';
}

/**
 * Normalize the toggle that controls whether existing local content may be updated from remote Mastodon data.
 * @param mixed $value
 * @return string
 */
function plugin_mastodon_normalize_update_local_from_remote($value) {
	return plugin_mastodon_normalize_boolean_option($value);
}

/**
 * Check whether remote Mastodon updates may overwrite already existing local FlatPress content.
 * @param array<string, string> $options
 * @return bool
 */
function plugin_mastodon_should_update_local_from_remote($options) {
	return plugin_mastodon_normalize_update_local_from_remote(isset($options ['update_local_from_remote']) ? $options ['update_local_from_remote'] : '') === '1';
}

/**
 * Normalize the toggle that allows importing already synchronized local comments as entries.
 * @param mixed $value
 * @return string
 */
function plugin_mastodon_normalize_import_synced_comments_as_entries($value) {
	return plugin_mastodon_normalize_boolean_option($value);
}

/**
 * Check whether a remote Mastodon status that is already mapped to a local FlatPress comment may also be imported as an entry.
 * @param array<string, string> $options
 * @return bool
 */
function plugin_mastodon_should_import_synced_comments_as_entries($options) {
	return plugin_mastodon_normalize_import_synced_comments_as_entries(isset($options ['import_synced_comments_as_entries']) ? $options ['import_synced_comments_as_entries'] : '') === '1';
}

/**
 * Normalize the toggle that controls whether imported Mastodon replies should quote the replied-to comment in FlatPress.
 * @param mixed $value
 * @return string
 */
function plugin_mastodon_normalize_quote_imported_reply_parent($value) {
	return plugin_mastodon_normalize_boolean_option($value);
}

/**
 * Check whether imported Mastodon replies should include a quote of the replied-to comment.
 * @param array<string, string> $options
 * @return bool
 */
function plugin_mastodon_should_quote_imported_reply_parent($options) {
	return plugin_mastodon_normalize_quote_imported_reply_parent(isset($options ['quote_imported_reply_parent']) ? $options ['quote_imported_reply_parent'] : '') === '1';
}

/**
 * Normalize the toggle that enables the follow-up deletion synchronization.
 * @param mixed $value
 * @return string
 */
function plugin_mastodon_normalize_delete_sync_enabled($value) {
	return plugin_mastodon_normalize_boolean_option($value);
}

/**
 * Check whether the follow-up deletion synchronization is enabled.
 * @param array<string, string> $options
 * @return bool
 */
function plugin_mastodon_should_run_deletion_sync($options) {
	return plugin_mastodon_normalize_delete_sync_enabled(isset($options ['delete_sync_enabled']) ? $options ['delete_sync_enabled'] : '') === '1';
}

/**
 * Convert a FlatPress-adjusted timestamp into a stable date key.
 * @param int $timestamp
 * @return string
 */
function plugin_mastodon_timestamp_date_key($timestamp) {
	$timestamp = (int) $timestamp;
	if ($timestamp <= 0) {
		return '';
	}
	return gmdate('Y-m-d', $timestamp);
}

/**
 * Determine the date key of a local FlatPress entry or comment.
 * @param array<string, mixed> $item
 * @param string $fallbackId
 * @return string
 */
function plugin_mastodon_local_item_date_key($item, $fallbackId = '') {
	$item = is_array($item) ? $item : array();
	if (isset($item ['date']) && is_numeric($item ['date'])) {
		return plugin_mastodon_timestamp_date_key((int) $item ['date']);
	}
	$fallbackId = trim((string) $fallbackId);
	if ($fallbackId !== '' && function_exists('date_from_id')) {
		$fallbackTimestamp = date_from_id($fallbackId);
		if (is_numeric($fallbackTimestamp)) {
			return plugin_mastodon_timestamp_date_key((int) $fallbackTimestamp);
		}
	}
	return '';
}

/**
 * Determine the date key of a remote Mastodon status.
 * @param array<string, mixed> $remoteStatus
 * @return string
 */
function plugin_mastodon_remote_status_date_key($remoteStatus) {
	$remoteStatus = is_array($remoteStatus) ? $remoteStatus : array();
	foreach (array('created_at', 'published', 'date', 'timestamp', 'edited_at') as $field) {
		if (empty($remoteStatus [$field])) {
			continue;
		}
		$value = trim((string) $remoteStatus [$field]);
		if ($value === '') {
			continue;
		}
		$timestamp = plugin_mastodon_parse_iso_timestamp($value);
		if ($timestamp > 0) {
			return plugin_mastodon_timestamp_date_key(plugin_mastodon_timestamp_to_flatpress_time($timestamp));
		}
		if (preg_match('/^(\d{4}-\d{2}-\d{2})$/', $value, $matches)) {
			return $matches [1];
		}
	}
	return '';
}

/**
 * Normalize a date/datetime string to a sync-start date key.
 * @param string $value
 * @return string
 */
function plugin_mastodon_datetime_date_key($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}
	if (preg_match('/^(\d{4}-\d{2}-\d{2})$/', $value, $matches)) {
		return plugin_mastodon_normalize_sync_start_date($matches [1]);
	}
	$timestamp = plugin_mastodon_parse_iso_timestamp($value);
	if ($timestamp <= 0) {
		$timestamp = @strtotime($value);
		if ($timestamp === false) {
			return '';
		}
	}
	return plugin_mastodon_timestamp_date_key(plugin_mastodon_timestamp_to_flatpress_time((int) $timestamp));
}

/**
 * Determine whether a content date passes the configured sync start date.
 * @param array<string, string> $options
 * @param string $dateKey
 * @return bool
 */
function plugin_mastodon_date_matches_sync_start($options, $dateKey) {
	$startDate = plugin_mastodon_normalize_sync_start_date(isset($options ['sync_start_date']) ? $options ['sync_start_date'] : '');
	if ($startDate === '') {
		return true;
	}
	$dateKey = plugin_mastodon_normalize_sync_start_date($dateKey);
	if ($dateKey === '') {
		return false;
	}
	return strcmp($dateKey, $startDate) >= 0;
}

/**
 * Determine whether a local FlatPress item should be synchronized.
 * @param array<string, string> $options
 * @param array<string, mixed> $item
 * @param string $fallbackId
 * @return bool
 */
function plugin_mastodon_local_item_matches_sync_start($options, $item, $fallbackId = '') {
	return plugin_mastodon_date_matches_sync_start($options, plugin_mastodon_local_item_date_key($item, $fallbackId));
}

/**
 * Determine whether a remote Mastodon status should be synchronized.
 * @param array<string, string> $options
 * @param array<string, mixed> $remoteStatus
 * @return bool
 */
function plugin_mastodon_remote_status_matches_sync_start($options, $remoteStatus) {
	return plugin_mastodon_date_matches_sync_start($options, plugin_mastodon_remote_status_date_key($remoteStatus));
}

/**
 * Determine whether a synchronized mapping should participate in the deletion follow-up for the current sync start date.
 * @param array<string, string> $options
 * @param array<string, mixed> $meta
 * @param string $localFallbackId
 * @return bool
 */
function plugin_mastodon_mapping_matches_sync_start($options, $meta, $localFallbackId = '') {
	$startDate = plugin_mastodon_normalize_sync_start_date(isset($options ['sync_start_date']) ? $options ['sync_start_date'] : '');
	if ($startDate === '') {
		return true;
	}
	$meta = is_array($meta) ? $meta : array();
	$source = isset($meta ['source']) ? strtolower(trim((string) $meta ['source'])) : '';
	$localDateKey = '';
	if (!empty($meta ['local_date_key'])) {
		$localDateKey = plugin_mastodon_normalize_sync_start_date((string) $meta ['local_date_key']);
	}
	if ($localDateKey === '') {
		$localDateKey = plugin_mastodon_local_item_date_key(array(), $localFallbackId);
	}
	$remoteDateKey = '';
	if (!empty($meta ['remote_date_key'])) {
		$remoteDateKey = plugin_mastodon_normalize_sync_start_date((string) $meta ['remote_date_key']);
	}
	if ($remoteDateKey === '') {
		$remoteDateKey = plugin_mastodon_datetime_date_key(isset($meta ['remote_updated_at']) ? (string) $meta ['remote_updated_at'] : '');
	}
	if ($source === 'remote' && $remoteDateKey !== '') {
		return plugin_mastodon_date_matches_sync_start($options, $remoteDateKey);
	}
	if ($source === 'local' && $localDateKey !== '') {
		return plugin_mastodon_date_matches_sync_start($options, $localDateKey);
	}
	if ($localDateKey !== '') {
		return plugin_mastodon_date_matches_sync_start($options, $localDateKey);
	}
	if ($remoteDateKey !== '') {
		return plugin_mastodon_date_matches_sync_start($options, $remoteDateKey);
	}
	return false;
}

/**
 * Ensure that the plugin runtime directory exists.
 * @return bool
 */
function plugin_mastodon_ensure_state_dir() {
	return fs_mkdir(PLUGIN_MASTODON_STATE_DIR);
}

/**
 * Append a line to the plugin sync log.
 * @param string $message
 * @return void
 */
function plugin_mastodon_log($message) {
	plugin_mastodon_ensure_state_dir();
	$line = '[' . gmdate('Y-m-d H:i:s') . ' UTC] ' . trim((string) $message) . PHP_EOL;
		plugin_mastodon_io_append_file(PLUGIN_MASTODON_LOG_FILE, $line);
}

/**
 * Load the persisted runtime state from disk.
 * @return array<string, mixed>
 */
function plugin_mastodon_state_read() {
	plugin_mastodon_ensure_state_dir();
	$defaults = plugin_mastodon_default_state();
	$prestat = plugin_mastodon_file_prestat(PLUGIN_MASTODON_STATE_FILE);
	if (empty($prestat ['exists'])) {
		plugin_mastodon_runtime_cache_clear('state');
		return $defaults;
	}

	$signature = plugin_mastodon_file_prestat_signature($prestat);
	$cached = plugin_mastodon_runtime_cache_get('state', $signature, $hit);
	if ($hit && is_array($cached)) {
		return plugin_mastodon_state_normalize($cached);
	}

	$legacySignature = plugin_mastodon_runtime_cache_get('state', '__signature__', $legacyHit);
	if ($legacyHit && $legacySignature !== $signature) {
		plugin_mastodon_runtime_cache_clear('state');
	}

	$json = plugin_mastodon_io_read_file_uncached(PLUGIN_MASTODON_STATE_FILE, !empty($prestat ['exists']));
	if (!is_string($json) || trim($json) === '') {
		return $defaults;
	}
	$data = json_decode($json, true);
	if (!is_array($data)) {
		return $defaults;
	}
	$state = plugin_mastodon_state_normalize($data);
	plugin_mastodon_runtime_cache_set('state', '__signature__', $signature);
	plugin_mastodon_runtime_cache_set('state', $signature, $state);
	return $state;
}

/**
 * Persist the runtime state to disk.
 * @param array<string, mixed> $state
 * @return bool
 */
function plugin_mastodon_state_write($state) {
	plugin_mastodon_ensure_state_dir();
	$state = plugin_mastodon_state_normalize($state);
	$json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if (!is_string($json)) {
		return false;
	}
	$payload = $json . PHP_EOL;
	$written = plugin_mastodon_io_write_file(PLUGIN_MASTODON_STATE_FILE, $payload);
	plugin_mastodon_runtime_cache_clear('state');
	if ($written) {
		$prestat = plugin_mastodon_file_prestat(PLUGIN_MASTODON_STATE_FILE);
		$signature = plugin_mastodon_file_prestat_signature($prestat);
		plugin_mastodon_runtime_cache_set('state', '__signature__', $signature);
		plugin_mastodon_runtime_cache_set('state', $signature, $state);
	}
	return $written;
}

/**
 * Normalize the pending deletion scope marker.
 * @param mixed $scope
 * @return string
 */
function plugin_mastodon_normalize_deletions_pending_scope($scope) {
	$scope = trim((string) $scope);
	if ($scope === 'comment_rechecks') {
		return 'comment_rechecks';
	}
	return 'full';
}

/**
 * Normalize a runtime state array and fill in missing keys.
 * @param array<string, mixed> $state
 * @return array<string, mixed>
 */
function plugin_mastodon_state_normalize($state) {
	$defaults = plugin_mastodon_default_state();
	$input = is_array($state) ? $state : array();
	$legacyStats = isset($input ['stats']) && is_array($input ['stats']) ? $input ['stats'] : array();
	$hasContentStats = isset($input ['content_stats']) && is_array($input ['content_stats']);
	$hasDeletionStats = isset($input ['deletion_stats']) && is_array($input ['deletion_stats']);
	$state = array_merge($defaults, $input);
	foreach (array('entries', 'entries_remote', 'comments', 'comments_remote', 'comment_tombstones', 'pending_comment_remote_rechecks') as $key) {
		if (!isset($state [$key]) || !is_array($state [$key])) {
			$state [$key] = $defaults [$key];
		}
	}
	$state ['version'] = (int) (isset($state ['version']) ? $state ['version'] : $defaults ['version']);
	$state ['last_run'] = isset($state ['last_run']) ? (string) $state ['last_run'] : '';
	$state ['last_deletion_run'] = isset($state ['last_deletion_run']) ? (string) $state ['last_deletion_run'] : '';
	$state ['deletions_pending'] = !empty($state ['deletions_pending']) ? 1 : 0;
	$state ['deletions_pending_scope'] = plugin_mastodon_normalize_deletions_pending_scope(isset($state ['deletions_pending_scope']) ? $state ['deletions_pending_scope'] : $defaults ['deletions_pending_scope']);
	$state ['last_error'] = isset($state ['last_error']) ? (string) $state ['last_error'] : '';
	$state ['last_remote_status_id'] = isset($state ['last_remote_status_id']) ? (string) $state ['last_remote_status_id'] : '';
	$legacyContentStats = array();
	foreach (array_keys($defaults ['content_stats']) as $key) {
		if (isset($legacyStats [$key])) {
			$legacyContentStats [$key] = (int) $legacyStats [$key];
		}
	}
	$legacyDeletionStats = array();
	foreach (array_keys($defaults ['deletion_stats']) as $key) {
		if (isset($legacyStats [$key])) {
			$legacyDeletionStats [$key] = (int) $legacyStats [$key];
		}
	}
	$contentStats = $hasContentStats ? $input ['content_stats'] : array();
	$deletionStats = $hasDeletionStats ? $input ['deletion_stats'] : array();
	$state ['content_stats'] = array_merge($defaults ['content_stats'], $legacyContentStats, is_array($contentStats) ? $contentStats : array());
	$state ['deletion_stats'] = array_merge($defaults ['deletion_stats'], $legacyDeletionStats, is_array($deletionStats) ? $deletionStats : array());
	if (isset($state ['stats'])) {
		unset($state ['stats']);
	}
	return $state;
}

/**
 * Build the compound state key used for comment mappings.
 * @param string $entryId
 * @param string $commentId
 * @return string
 */
function plugin_mastodon_state_comment_key($entryId, $commentId) {
	return (string) $entryId . ':' . (string) $commentId;
}

/**
 * Store the mapping between a local entry and a remote status.
 * @param array<string, mixed> $state
 * @param string $localId
 * @param string $remoteId
 * @param string $source
 * @param string $hash
 * @param string $remoteUrl
 * @param string $remoteUpdatedAt
 * @return void
 */
function plugin_mastodon_state_set_entry_mapping(&$state, $localId, $remoteId, $source, $hash, $remoteUrl, $remoteUpdatedAt, $localDateKey = '', $remoteDateKey = '') {
	$localId = (string) $localId;
	$remoteId = (string) $remoteId;
	$localDateKey = plugin_mastodon_normalize_sync_start_date($localDateKey);
	if ($localDateKey === '') {
		$localDateKey = plugin_mastodon_local_item_date_key(array(), $localId);
	}
	$remoteDateKey = plugin_mastodon_normalize_sync_start_date($remoteDateKey);
	if ($remoteDateKey === '') {
		$remoteDateKey = plugin_mastodon_datetime_date_key($remoteUpdatedAt);
	}
	$state ['entries'] [$localId] = array(
		'remote_id' => $remoteId,
		'source' => $source,
		'hash' => $hash,
		'remote_url' => (string) $remoteUrl,
		'remote_updated_at' => (string) $remoteUpdatedAt,
		'local_date_key' => $localDateKey,
		'remote_date_key' => $remoteDateKey
	);
	$state ['entries_remote'] [$remoteId] = $localId;
}

/**
 * Store the mapping between a local comment and a remote status.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @param string $remoteId
 * @param string $source
 * @param string $hash
 * @param string $remoteUrl
 * @param string $remoteUpdatedAt
 * @param string $parentCommentId
 * @param string $inReplyToRemoteId
 * @return void
 */
function plugin_mastodon_state_set_comment_mapping(&$state, $entryId, $commentId, $remoteId, $source, $hash, $remoteUrl, $remoteUpdatedAt, $parentCommentId = '', $inReplyToRemoteId = '', $localDateKey = '', $remoteDateKey = '') {
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	$localDateKey = plugin_mastodon_normalize_sync_start_date($localDateKey);
	if ($localDateKey === '') {
		$localDateKey = plugin_mastodon_local_item_date_key(array(), $commentId);
	}
	$remoteDateKey = plugin_mastodon_normalize_sync_start_date($remoteDateKey);
	if ($remoteDateKey === '') {
		$remoteDateKey = plugin_mastodon_datetime_date_key($remoteUpdatedAt);
	}
	$state ['comments'] [$key] = array(
		'entry_id' => (string) $entryId,
		'comment_id' => (string) $commentId,
		'remote_id' => (string) $remoteId,
		'source' => (string) $source,
		'hash' => (string) $hash,
		'remote_url' => (string) $remoteUrl,
		'remote_updated_at' => (string) $remoteUpdatedAt,
		'parent_comment_id' => (string) $parentCommentId,
		'in_reply_to_remote_id' => (string) $inReplyToRemoteId,
		'local_date_key' => $localDateKey,
		'remote_date_key' => $remoteDateKey
	);
	$state ['comments_remote'] [(string) $remoteId] = array(
		'entry_id' => (string) $entryId,
		'comment_id' => (string) $commentId
	);
}

/**
 * Remove the mapping between a local entry and a remote status.
 * @param array<string, mixed> $state
 * @param string $localId
 * @return void
 */
function plugin_mastodon_state_remove_entry_mapping(&$state, $localId) {
	$localId = (string) $localId;
	if ($localId === '' || empty($state ['entries'] [$localId]) || !is_array($state ['entries'] [$localId])) {
		return;
	}
	$remoteId = !empty($state ['entries'] [$localId] ['remote_id']) ? (string) $state ['entries'] [$localId] ['remote_id'] : '';
	unset($state ['entries'] [$localId]);
	if ($remoteId !== '' && isset($state ['entries_remote'] [$remoteId]) && (string) $state ['entries_remote'] [$remoteId] === $localId) {
		unset($state ['entries_remote'] [$remoteId]);
	}
}

/**
 * Remove the mapping between a local comment and a remote status.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @return void
 */
function plugin_mastodon_state_remove_comment_mapping(&$state, $entryId, $commentId) {
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	if (!isset($state ['comments'] [$key]) || !is_array($state ['comments'] [$key])) {
		return;
	}
	$remoteId = !empty($state ['comments'] [$key] ['remote_id']) ? (string) $state ['comments'] [$key] ['remote_id'] : '';
	unset($state ['comments'] [$key]);
	if ($remoteId !== '' && isset($state ['comments_remote'] [$remoteId]) && is_array($state ['comments_remote'] [$remoteId])) {
		$remoteRef = $state ['comments_remote'] [$remoteId];
		if ((isset($remoteRef ['entry_id']) ? (string) $remoteRef ['entry_id'] : '') === (string) $entryId && (isset($remoteRef ['comment_id']) ? (string) $remoteRef ['comment_id'] : '') === (string) $commentId) {
			unset($state ['comments_remote'] [$remoteId]);
		}
	}
}

/**
 * Return mapping metadata for a local entry.
 * @param array<string, mixed> $state
 * @param string $localId
 * @return array<string, mixed>
 */
function plugin_mastodon_state_get_entry_meta($state, $localId) {
	$localId = (string) $localId;
	return isset($state ['entries'] [$localId]) && is_array($state ['entries'] [$localId]) ? $state ['entries'] [$localId] : array();
}

/**
 * Persist media metadata for a synchronized local entry.
 * @param array<string, mixed> $state
 * @param string $localId
 * @param array<int, array<string, string>> $remoteMedia
 * @param string $attachmentSignature
 * @param string $descriptionSignature
 * @return void
 */
function plugin_mastodon_state_set_entry_media_meta(&$state, $localId, $remoteMedia, $attachmentSignature, $descriptionSignature) {
	$localId = (string) $localId;
	if ($localId === '' || empty($state ['entries'] [$localId]) || !is_array($state ['entries'] [$localId])) {
		return;
	}
	if (!empty($remoteMedia) && is_array($remoteMedia)) {
		$state ['entries'] [$localId] ['remote_media'] = array_values($remoteMedia);
	} else {
		unset($state ['entries'] [$localId] ['remote_media']);
	}
	$attachmentSignature = trim((string) $attachmentSignature);
	if ($attachmentSignature !== '') {
		$state ['entries'] [$localId] ['local_media_attachment_signature'] = $attachmentSignature;
	} else {
		unset($state ['entries'] [$localId] ['local_media_attachment_signature']);
	}
	$descriptionSignature = trim((string) $descriptionSignature);
	if ($descriptionSignature !== '') {
		$state ['entries'] [$localId] ['local_media_description_signature'] = $descriptionSignature;
	} else {
		unset($state ['entries'] [$localId] ['local_media_description_signature']);
	}
}

/**
 * Return sanitized remote-media descriptors stored inside one entry mapping.
 * @param array<string, mixed> $meta
 * @return array<int, array<string, string>>
 */
function plugin_mastodon_state_entry_remote_media($meta) {
	$meta = is_array($meta) ? $meta : array();
	$remoteMedia = array();
	if (empty($meta ['remote_media']) || !is_array($meta ['remote_media'])) {
		return $remoteMedia;
	}
	foreach ($meta ['remote_media'] as $item) {
		if (!is_array($item) || empty($item ['id'])) {
			continue;
		}
		$descriptor = array(
			'id' => trim((string) $item ['id']),
			'description' => isset($item ['description']) ? trim((string) $item ['description']) : ''
		);
		if ($descriptor ['id'] === '') {
			continue;
		}
		if (isset($item ['focus'])) {
			$descriptor ['focus'] = trim((string) $item ['focus']);
		}
		$remoteMedia [] = $descriptor;
	}
	return $remoteMedia;
}

/**
 * Return the stored attachment-signature for one entry mapping.
 * @param array<string, mixed> $meta
 * @return string
 */
function plugin_mastodon_state_entry_media_attachment_signature($meta) {
	$meta = is_array($meta) ? $meta : array();
	return !empty($meta ['local_media_attachment_signature']) ? trim((string) $meta ['local_media_attachment_signature']) : '';
}

/**
 * Return the stored description-signature for one entry mapping.
 * @param array<string, mixed> $meta
 * @return string
 */
function plugin_mastodon_state_entry_media_description_signature($meta) {
	$meta = is_array($meta) ? $meta : array();
	return !empty($meta ['local_media_description_signature']) ? trim((string) $meta ['local_media_description_signature']) : '';
}

/**
 * Return mapping metadata for a local comment.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @return array<string, mixed>
 */
function plugin_mastodon_state_get_comment_meta($state, $entryId, $commentId) {
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	return isset($state ['comments'] [$key]) && is_array($state ['comments'] [$key]) ? $state ['comments'] [$key] : array();
}

/**
 * Store a tombstone that blocks re-importing a deleted remote comment status.
 * @param array<string, mixed> $state
 * @param string $remoteId
 * @param string $entryId
 * @param string $commentId
 * @param string $reason
 * @return void
 */
function plugin_mastodon_state_set_comment_tombstone(&$state, $remoteId, $entryId = '', $commentId = '', $reason = '') {
	$remoteId = trim((string) $remoteId);
	if ($remoteId === '') {
		return;
	}
	$state ['comment_tombstones'] [$remoteId] = array(
		'remote_id' => $remoteId,
		'entry_id' => (string) $entryId,
		'comment_id' => (string) $commentId,
		'reason' => trim((string) $reason),
		'deleted_at' => date('Y-m-d H:i:s')
	);
}

/**
 * Check whether one remote Mastodon comment status was tombstoned locally.
 * @param array<string, mixed> $state
 * @param string $remoteId
 * @return bool
 */
function plugin_mastodon_state_has_comment_tombstone($state, $remoteId) {
	$remoteId = trim((string) $remoteId);
	return $remoteId !== '' && !empty($state ['comment_tombstones'] [$remoteId]) && is_array($state ['comment_tombstones'] [$remoteId]);
}

/**
 * Protect locally deleted exported FlatPress comments from stale Mastodon re-imports before deletion sync runs.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @return int
 */
function plugin_mastodon_protect_locally_deleted_exported_comments($options, &$state) {
	$protected = 0;
	$commentMappings = isset($state ['comments']) && is_array($state ['comments']) ? array_keys($state ['comments']) : array();
	foreach ($commentMappings as $commentKey) {
		$commentKey = (string) $commentKey;
		$meta = isset($state ['comments'] [$commentKey]) && is_array($state ['comments'] [$commentKey]) ? $state ['comments'] [$commentKey] : array();
		if (empty($meta ['entry_id']) || empty($meta ['comment_id']) || empty($meta ['remote_id'])) {
			continue;
		}
		if (!isset($meta ['source']) || (string) $meta ['source'] !== 'local') {
			continue;
		}
		$entryId = (string) $meta ['entry_id'];
		$commentId = (string) $meta ['comment_id'];
		$remoteId = (string) $meta ['remote_id'];
		if ($entryId === '' || $commentId === '' || $remoteId === '') {
			continue;
		}
		if (!plugin_mastodon_mapping_matches_sync_start($options, $meta, $commentId)) {
			continue;
		}
		if (comment_exists($entryId, $commentId)) {
			continue;
		}
		if (plugin_mastodon_state_has_comment_tombstone($state, $remoteId)) {
			continue;
		}
		plugin_mastodon_state_set_comment_tombstone($state, $remoteId, $entryId, $commentId, 'local_deleted_pending_remote_delete');
		plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
		$protected++;
		plugin_mastodon_log('Protecting remote reply ' . $remoteId . ' from stale re-import because exported local comment ' . $entryId . '/' . $commentId . ' was deleted before deletion synchronization ran');
	}
	if ($protected > 0) {
		plugin_mastodon_state_set_deletions_pending($state, true, 'full');
	}
	return $protected;
}

/**
 * Reattach one imported local comment to the synchronized entry status after its remote parent reply disappeared.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @param string $ancestorRemoteId
 * @return bool
 */
function plugin_mastodon_reattach_local_comment_to_entry_status(&$state, $entryId, $commentId, $ancestorRemoteId = '') {
	$entryId = trim((string) $entryId);
	$commentId = trim((string) $commentId);
	$ancestorRemoteId = trim((string) $ancestorRemoteId);
	if ($entryId === '' || $commentId === '') {
		return false;
	}
	$file = comment_exists($entryId, $commentId);
	if (!$file) {
		return false;
	}
	$commentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $commentId);
	if ($commentMeta === array()) {
		return false;
	}
	$entryMeta = plugin_mastodon_state_get_entry_meta($state, $entryId);
	$entryRemoteId = !empty($entryMeta ['remote_id']) ? trim((string) $entryMeta ['remote_id']) : '';
	$comment = comment_parse($entryId, $commentId);
	if (!is_array($comment)) {
		return false;
	}
	$hadParentReference = false;
	foreach (plugin_mastodon_comment_parent_fields() as $parentField) {
		if (array_key_exists($parentField, $comment) && $comment [$parentField] !== '') {
			$hadParentReference = true;
		}
		unset($comment [$parentField]);
	}
	if (!$hadParentReference && (!empty($commentMeta ['parent_comment_id']) || (!empty($commentMeta ['in_reply_to_remote_id']) && (string) $commentMeta ['in_reply_to_remote_id'] !== $entryRemoteId))) {
		$hadParentReference = true;
	}
	if (!$hadParentReference && (empty($commentMeta ['parent_comment_id']) && ((string) (isset($commentMeta ['in_reply_to_remote_id']) ? $commentMeta ['in_reply_to_remote_id'] : '') === $entryRemoteId))) {
		return true;
	}
	$storedComment = array_change_key_case($comment, CASE_UPPER);
	if (!plugin_mastodon_io_write_file($file, utils_kimplode($storedComment))) {
		return false;
	}
	$remoteId = !empty($commentMeta ['remote_id']) ? (string) $commentMeta ['remote_id'] : '';
	if ($remoteId === '') {
		return false;
	}
	$source = !empty($commentMeta ['source']) ? (string) $commentMeta ['source'] : 'remote';
	$remoteUrl = !empty($commentMeta ['remote_url']) ? (string) $commentMeta ['remote_url'] : '';
	$remoteUpdatedAt = !empty($commentMeta ['remote_updated_at']) ? (string) $commentMeta ['remote_updated_at'] : '';
	$remoteDateKey = !empty($commentMeta ['remote_date_key']) ? (string) $commentMeta ['remote_date_key'] : '';
	$localDateKey = plugin_mastodon_local_item_date_key($comment, $commentId);
	$hash = plugin_mastodon_comment_hash($comment);
	plugin_mastodon_state_set_comment_mapping($state, $entryId, $commentId, $remoteId, $source, $hash, $remoteUrl, $remoteUpdatedAt, '', $entryRemoteId, $localDateKey, $remoteDateKey);
	plugin_mastodon_log('Reattached local comment ' . $entryId . '/' . $commentId . ' to synchronized entry status ' . ($entryRemoteId !== '' ? $entryRemoteId : '[missing-entry-remote-id]') . ' after remote parent reply ' . ($ancestorRemoteId !== '' ? $ancestorRemoteId : '[unknown-parent]') . ' disappeared');
	return true;
}

/**
 * Remove one pending descendant recheck marker.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @return void
 */
function plugin_mastodon_state_remove_pending_comment_remote_recheck(&$state, $entryId, $commentId) {
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	if (isset($state ['pending_comment_remote_rechecks'] [$key])) {
		unset($state ['pending_comment_remote_rechecks'] [$key]);
	}
}

/**
 * Return one pending descendant recheck marker.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @return array<string, mixed>
 */
function plugin_mastodon_state_get_pending_comment_remote_recheck($state, $entryId, $commentId) {
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	return isset($state ['pending_comment_remote_rechecks'] [$key]) && is_array($state ['pending_comment_remote_rechecks'] [$key]) ? $state ['pending_comment_remote_rechecks'] [$key] : array();
}

/**
 * Mark one local comment for follow-up verification after an ancestor status disappeared remotely.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @param string $remoteId
 * @param string $ancestorRemoteId
 * @param int|null $attempts
 * @return void
 */
function plugin_mastodon_state_set_pending_comment_remote_recheck(&$state, $entryId, $commentId, $remoteId, $ancestorRemoteId, $attempts = null) {
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	$existing = plugin_mastodon_state_get_pending_comment_remote_recheck($state, $entryId, $commentId);
	$currentAttempts = isset($existing ['attempts']) ? (int) $existing ['attempts'] : 0;
	if ($attempts !== null) {
		$currentAttempts = max(0, (int) $attempts);
	}
	$state ['pending_comment_remote_rechecks'] [$key] = array(
		'entry_id' => (string) $entryId,
		'comment_id' => (string) $commentId,
		'remote_id' => trim((string) $remoteId),
		'ancestor_remote_id' => trim((string) $ancestorRemoteId),
		'attempts' => $currentAttempts,
		'queued_at' => !empty($existing ['queued_at']) ? (string) $existing ['queued_at'] : date('Y-m-d H:i:s'),
		'last_checked_at' => !empty($existing ['last_checked_at']) ? (string) $existing ['last_checked_at'] : ''
	);
}

/**
 * Update the runtime marker that tells the scheduler whether another deletion follow-up request is required.
 * @param array<string, mixed> $state
 * @param bool $pending
 * @param string $scope
 * @return void
 */
function plugin_mastodon_state_set_deletions_pending(&$state, $pending, $scope) {
	$pending = (bool) $pending;
	$scope = plugin_mastodon_normalize_deletions_pending_scope($scope);
	$state ['deletions_pending'] = $pending ? 1 : 0;
	$state ['deletions_pending_scope'] = $pending ? $scope : 'full';
}

/**
 * Check whether the current deletion follow-up request should focus on pending descendant reply rechecks only.
 * @param array<string, mixed> $state
 * @return bool
 */
function plugin_mastodon_state_has_comment_recheck_scope($state) {
	return !empty($state ['deletions_pending']) && plugin_mastodon_normalize_deletions_pending_scope(isset($state ['deletions_pending_scope']) ? $state ['deletions_pending_scope'] : 'full') === 'comment_rechecks';
}

/**
 * Build an index of mapped local comments grouped by their direct remote parent status.
 * @param array<string, mixed> $state
 * @return array<string, array<int, array<string, mixed>>>
 */
function plugin_mastodon_build_comment_remote_child_index($state) {
	$index = array();
	if (empty($state ['comments']) || !is_array($state ['comments'])) {
		return $index;
	}
	foreach ($state ['comments'] as $commentMeta) {
		if (!is_array($commentMeta) || empty($commentMeta ['entry_id']) || empty($commentMeta ['comment_id']) || empty($commentMeta ['remote_id'])) {
			continue;
		}
		$parentRemoteId = !empty($commentMeta ['in_reply_to_remote_id']) ? trim((string) $commentMeta ['in_reply_to_remote_id']) : '';
		if ($parentRemoteId === '') {
			continue;
		}
		if (!isset($index [$parentRemoteId]) || !is_array($index [$parentRemoteId])) {
			$index [$parentRemoteId] = array();
		}
		$index [$parentRemoteId] [] = $commentMeta;
	}
	return $index;
}

/**
 * Queue the direct mapped local children of one deleted remote comment for additional verification passes.
 * @param array<string, mixed> $state
 * @param array<string, array<int, array<string, mixed>>> $childIndex
 * @param string $ancestorRemoteId
 * @return int
 */
function plugin_mastodon_queue_comment_descendant_remote_rechecks(&$state, $childIndex, $ancestorRemoteId) {
	$ancestorRemoteId = trim((string) $ancestorRemoteId);
	if ($ancestorRemoteId === '' || empty($childIndex [$ancestorRemoteId]) || !is_array($childIndex [$ancestorRemoteId])) {
		return 0;
	}
	$queued = 0;
	foreach ($childIndex [$ancestorRemoteId] as $commentMeta) {
		if (!is_array($commentMeta) || empty($commentMeta ['entry_id']) || empty($commentMeta ['comment_id']) || empty($commentMeta ['remote_id'])) {
			continue;
		}
		$entryId = (string) $commentMeta ['entry_id'];
		$commentId = (string) $commentMeta ['comment_id'];
		$remoteId = trim((string) $commentMeta ['remote_id']);
		if ($remoteId === '' || $remoteId === $ancestorRemoteId) {
			continue;
		}
		if (plugin_mastodon_state_has_comment_tombstone($state, $remoteId)) {
			continue;
		}
		if (!comment_exists($entryId, $commentId)) {
			continue;
		}
		$existing = plugin_mastodon_state_get_pending_comment_remote_recheck($state, $entryId, $commentId);
		plugin_mastodon_state_set_pending_comment_remote_recheck($state, $entryId, $commentId, $remoteId, $ancestorRemoteId);
		if ($existing === array()) {
			$queued++;
		}
	}
	return $queued;
}

/**
 * Process queued descendant reply rechecks using a small FIFO queue.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param array<string, array<int, array<string, mixed>>> $childIndex
 * @param bool $hadFailure
 * @return bool
 */
function plugin_mastodon_process_pending_comment_remote_rechecks($options, &$state, $childIndex, &$hadFailure) {
	$queue = array_keys(isset($state ['pending_comment_remote_rechecks']) && is_array($state ['pending_comment_remote_rechecks']) ? $state ['pending_comment_remote_rechecks'] : array());
	$queuedLookup = array();
	foreach ($queue as $key) {
		$queuedLookup [(string) $key] = true;
	}
	$pendingRemaining = false;
	while (!empty($queue)) {
		$commentKey = (string) array_shift($queue);
		unset($queuedLookup [$commentKey]);
		if ($commentKey === '' || empty($state ['pending_comment_remote_rechecks'] [$commentKey]) || !is_array($state ['pending_comment_remote_rechecks'] [$commentKey])) {
			continue;
		}
		plugin_mastodon_extend_time_limit(120);
		$pendingRecheck = $state ['pending_comment_remote_rechecks'] [$commentKey];
		$entryId = !empty($pendingRecheck ['entry_id']) ? (string) $pendingRecheck ['entry_id'] : '';
		$commentId = !empty($pendingRecheck ['comment_id']) ? (string) $pendingRecheck ['comment_id'] : '';
		$remoteId = !empty($pendingRecheck ['remote_id']) ? trim((string) $pendingRecheck ['remote_id']) : '';
		$ancestorRemoteId = !empty($pendingRecheck ['ancestor_remote_id']) ? trim((string) $pendingRecheck ['ancestor_remote_id']) : '';
		if ($entryId === '' || $commentId === '' || $remoteId === '') {
			unset($state ['pending_comment_remote_rechecks'] [$commentKey]);
			continue;
		}
		$commentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $commentId);
		if (empty($commentMeta ['remote_id']) || trim((string) $commentMeta ['remote_id']) !== $remoteId) {
			unset($state ['pending_comment_remote_rechecks'] [$commentKey]);
			continue;
		}
		if (!comment_exists($entryId, $commentId)) {
			$deleted = plugin_mastodon_delete_status($options, $remoteId, true);
			if (!empty($deleted ['ok']) || plugin_mastodon_status_missing_response($deleted)) {
				$queuedChildren = plugin_mastodon_queue_comment_descendant_remote_rechecks($state, $childIndex, $remoteId);
				plugin_mastodon_state_set_comment_tombstone($state, $remoteId, $entryId, $commentId, 'pending_local_deleted_remote_deleted');
				plugin_mastodon_state_remove_comment_mapping($state, $entryId, $commentId);
				$state ['deletion_stats'] ['deleted_remote_comments']++;
				unset($state ['pending_comment_remote_rechecks'] [$commentKey]);
				if ($queuedChildren > 0) {
					plugin_mastodon_log('Queued ' . $queuedChildren . ' direct local descendant comment(s) for follow-up verification after deleting remote reply ' . $remoteId . ' from the pending descendant queue');
					foreach ($state ['pending_comment_remote_rechecks'] as $nextKey => $nextPending) {
						$nextKey = (string) $nextKey;
						if (!isset($queuedLookup [$nextKey])) {
							$queue [] = $nextKey;
							$queuedLookup [$nextKey] = true;
						}
					}
				}
				continue;
			}
			$hadFailure = true;
			$state ['last_error'] = 'comment_remote_delete_failed: ' . $entryId . '/' . $commentId . ' (' . plugin_mastodon_response_error_message($deleted) . ')';
			plugin_mastodon_log('Deletion sync failed to delete remote status for local comment ' . $entryId . '/' . $commentId . ' from the pending descendant queue: ' . plugin_mastodon_response_error_message($deleted));
			continue;
		}
		$remote = plugin_mastodon_fetch_status($options, $remoteId);
		if (plugin_mastodon_status_missing_response($remote)) {
			$queuedChildren = plugin_mastodon_queue_comment_descendant_remote_rechecks($state, $childIndex, $remoteId);
			if (comment_exists($entryId, $commentId)) {
				comment_delete($entryId, $commentId);
			}
			plugin_mastodon_state_set_comment_tombstone($state, $remoteId, $entryId, $commentId, 'pending_remote_missing_local_deleted');
			plugin_mastodon_state_remove_comment_mapping($state, $entryId, $commentId);
			$state ['deletion_stats'] ['deleted_local_comments']++;
			unset($state ['pending_comment_remote_rechecks'] [$commentKey]);
			if ($queuedChildren > 0) {
				plugin_mastodon_log('Queued ' . $queuedChildren . ' direct local descendant comment(s) for follow-up verification after remote reply ' . $remoteId . ' disappeared in the pending descendant queue');
				foreach ($state ['pending_comment_remote_rechecks'] as $nextKey => $nextPending) {
					$nextKey = (string) $nextKey;
					if (!isset($queuedLookup [$nextKey])) {
						$queue [] = $nextKey;
						$queuedLookup [$nextKey] = true;
					}
				}
			}
			continue;
		}
		if (empty($remote ['ok'])) {
			$hadFailure = true;
			$state ['last_error'] = 'comment_remote_lookup_failed: ' . $entryId . '/' . $commentId . ' (' . plugin_mastodon_response_error_message($remote) . ')';
			plugin_mastodon_log('Deletion sync failed to fetch remote status for local comment ' . $entryId . '/' . $commentId . ' from the pending descendant queue: ' . plugin_mastodon_response_error_message($remote));
			continue;
		}
		$reattachedToEntryStatus = plugin_mastodon_reattach_local_comment_to_entry_status($state, $entryId, $commentId, $ancestorRemoteId);
		$attempts = isset($pendingRecheck ['attempts']) ? (int) $pendingRecheck ['attempts'] : 0;
		$attempts++;
		if ($attempts < (int) PLUGIN_MASTODON_PENDING_COMMENT_RECHECK_LIMIT) {
			plugin_mastodon_state_set_pending_comment_remote_recheck($state, $entryId, $commentId, $remoteId, $ancestorRemoteId, $attempts);
			$state ['pending_comment_remote_rechecks'] [$commentKey] ['last_checked_at'] = date('Y-m-d H:i:s');
			$pendingRemaining = true;
			plugin_mastodon_log('Keeping local comment ' . $entryId . '/' . $commentId . ' for one more follow-up verification because ancestor remote comment ' . $ancestorRemoteId . ' disappeared but reply ' . $remoteId . ' still exists remotely' . ($reattachedToEntryStatus ? ' and was reattached to the synchronized entry status' : '') . ' (attempt ' . $attempts . '/' . (int) PLUGIN_MASTODON_PENDING_COMMENT_RECHECK_LIMIT . ')');
		} else {
			unset($state ['pending_comment_remote_rechecks'] [$commentKey]);
			plugin_mastodon_log('Stopping repeated follow-up verification for local comment ' . $entryId . '/' . $commentId . ' because remote reply ' . $remoteId . ' still exists after ' . $attempts . ' checks' . ($reattachedToEntryStatus ? ' and remains attached to the synchronized entry status locally' : ''));
		}
	}
	if (!$pendingRemaining && !empty($state ['pending_comment_remote_rechecks'])) {
		$pendingRemaining = true;
	}
	return $pendingRemaining;
}

/**
 * Parse an ISO date/time string into FlatPress date format.
 * @param string $value
 * @return string
 */
function plugin_mastodon_parse_iso_datetime($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}
	try {
		$date = new DateTime($value);
		return $date->format('Y-m-d H:i:s');
	} catch (Exception $e) {
		return '';
	}
}

/**
 * Parse an ISO date/time value into a Unix timestamp.
 * @param string $value
 * @return int
 */
function plugin_mastodon_parse_iso_timestamp($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return 0;
	}
	if (ctype_digit($value)) {
		return (int) $value;
	}
	try {
		$date = new DateTime($value);
		return (int) $date->format('U');
	} catch (Exception $e) {
		$timestamp = @strtotime($value);
		return $timestamp === false ? 0 : (int) $timestamp;
	}
}

/**
 * Resolve the best timestamp for a remote Mastodon status.
 * @param array<string, mixed> $remoteStatus
 * @return int
 */
function plugin_mastodon_remote_status_timestamp($remoteStatus) {
	$remoteStatus = is_array($remoteStatus) ? $remoteStatus : array();
	foreach (array('created_at', 'published', 'date', 'timestamp', 'edited_at') as $field) {
		if (empty($remoteStatus [$field])) {
			continue;
		}
		$timestamp = plugin_mastodon_parse_iso_timestamp($remoteStatus [$field]);
		if ($timestamp > 0) {
			return plugin_mastodon_timestamp_to_flatpress_time($timestamp);
		}
	}
	return date_time();
}

/**
 * Return the normalized visibility of a remote Mastodon status.
 * @param array<string, mixed> $remoteStatus
 * @return string
 */
function plugin_mastodon_remote_status_visibility($remoteStatus) {
	$remoteStatus = is_array($remoteStatus) ? $remoteStatus : array();
	if (empty($remoteStatus ['visibility'])) {
		return '';
	}
	return strtolower(trim((string) $remoteStatus ['visibility']));
}

/**
 * Determine whether a remote Mastodon status may be imported.
 * @param array<string, mixed> $remoteStatus
 * @return bool
 */
function plugin_mastodon_remote_status_is_importable($remoteStatus) {
	$visibility = plugin_mastodon_remote_status_visibility($remoteStatus);
	if ($visibility === 'direct' || $visibility === 'private') {
		return false;
	}
	return true;
}

/**
 * Return the comment fields that may contain a parent reference.
 * @return array<int, string>
 */
function plugin_mastodon_comment_parent_fields() {
	return array('replyto', 'reply_to', 'parent', 'parent_id', 'in_reply_to', 'in_reply_to_id', 'replytoid', 'reply_to_id', 'inreplyto');
}

/**
 * Normalize a stored local comment parent identifier.
 * @param string $value
 * @return string
 */
function plugin_mastodon_normalize_comment_parent_id($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}
	if (preg_match('/^comment\d{6}-\d{6}$/', $value)) {
		return $value;
	}
	return '';
}

/**
 * Detect the local parent comment identifier from comment data.
 * @param string $entryId
 * @param array<string, mixed> $comment
 * @return string
 */
function plugin_mastodon_detect_local_comment_parent_id($entryId, $comment) {
	$comment = is_array($comment) ? $comment : array();
	foreach (plugin_mastodon_comment_parent_fields() as $field) {
		if (empty($comment [$field])) {
			continue;
		}
		$candidate = plugin_mastodon_normalize_comment_parent_id($comment [$field]);
		if ($candidate !== '' && comment_exists($entryId, $candidate)) {
			return $candidate;
		}
	}
	return '';
}

/**
 * Resolve the remote reply target for a local comment export.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param array<string, mixed> $comment
 * @param string $defaultRemoteId
 * @return array{remote_id:string, parent_comment_id:string}
 */
function plugin_mastodon_resolve_comment_reply_target($state, $entryId, $comment, $defaultRemoteId) {
	$parentCommentId = plugin_mastodon_detect_local_comment_parent_id($entryId, $comment);
	if ($parentCommentId !== '') {
		$parentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $parentCommentId);
		if (!empty($parentMeta ['remote_id'])) {
			return array(
				'remote_id' => (string) $parentMeta ['remote_id'],
				'parent_comment_id' => $parentCommentId
			);
		}
	}
	return array(
		'remote_id' => (string) $defaultRemoteId,
		'parent_comment_id' => $parentCommentId
	);
}


/**
 * Determine whether a local parent comment should be exported before its child reply.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $parentCommentId
 * @return bool
 */
function plugin_mastodon_local_comment_parent_export_pending($options, $state, $entryId, $parentCommentId) {
	$entryId = trim((string) $entryId);
	$parentCommentId = trim((string) $parentCommentId);
	if ($entryId === '' || $parentCommentId === '' || !comment_exists($entryId, $parentCommentId)) {
		return false;
	}

	$parentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $parentCommentId);
	if (!empty($parentMeta ['remote_id'])) {
		return false;
	}
	if (!empty($parentMeta ['source']) && strtolower((string) $parentMeta ['source']) === 'remote') {
		return false;
	}

	$parentComment = comment_parse($entryId, $parentCommentId);
	if (!$parentComment || !is_array($parentComment)) {
		return false;
	}
	$parentComment ['id'] = $parentCommentId;
	return plugin_mastodon_local_item_matches_sync_start($options, $parentComment, $parentCommentId);
}

/**
 * List local FlatPress comment identifiers by scanning the entry comment directory directly.
 * @param string $entryId
 * @return array<int, string>
 */
function plugin_mastodon_list_local_comment_ids($entryId) {
	$entryId = trim((string) $entryId);
	if ($entryId === '' || !defined('BDB_COMMENT')) {
		return array();
	}
	$dir = bdb_idtofile($entryId, BDB_COMMENT);
	if (!is_string($dir) || $dir === '' || !@is_dir($dir)) {
		return array();
	}

	$commentIds = array();
	$handle = @opendir($dir);
	if (!$handle) {
		return array();
	}
	while (($file = readdir($handle)) !== false) {
		if ($file === '' || $file [0] === '.') {
			continue;
		}
		if (!fnmatch('comment*' . EXT, $file)) {
			continue;
		}
		$commentId = basename($file, EXT);
		if (preg_match('/^comment\d{6}-\d{6}$/', $commentId)) {
			$commentIds [] = $commentId;
		}
	}
	@closedir($handle);
	sort($commentIds, SORT_STRING);
	return array_values(array_unique($commentIds));
}

/**
 * Guess a subject line from imported plain text.
 * @param string $text
 * @return string
 */
function plugin_mastodon_guess_subject($text) {
	$text = plugin_mastodon_plain_text_from_bbcode($text);
	if ($text === '') {
		return 'Mastodon';
	}

	$lines = preg_split("/\n+/u", $text);
	$candidate = '';
	foreach ($lines as $line) {
		$line = trim((string) $line);
		if ($line === '') {
			continue;
		}
		if ($candidate === '' && plugin_mastodon_subject_line_is_noise($line)) {
			continue;
		}
		$candidate = $line;
		break;
	}

	if ($candidate === '' && !empty($lines)) {
		$candidate = trim((string) reset($lines));
	}
	if ($candidate === '') {
		return 'Mastodon';
	}

	$candidate = preg_replace('/\s+/u', ' ', $candidate);
	if (function_exists('mb_substr')) {
		$candidate = mb_substr($candidate, 0, 72, 'UTF-8');
	} else {
		$candidate = substr($candidate, 0, 72);
	}

	return trim((string) $candidate);
}

/**
 * Decode HTML entities using the plugin defaults.
 * @param string $text
 * @return string
 */
function plugin_mastodon_html_entity_decode($text) {
	$text = (string) $text;
	return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Return the absolute base URL of the current FlatPress installation.
 * @return string
 */
function plugin_mastodon_blog_base_url() {
	$url = plugin_mastodon_fp_config_value(array('general', 'www'), '');
	$url = is_string($url) ? trim($url) : '';
	if ($url === '' && defined('BLOG_BASEURL')) {
		$url = (string) BLOG_BASEURL;
	}

	if ($url === '') {
		$scheme = 'http';
		if (!empty($_SERVER ['HTTPS']) && strtolower((string) $_SERVER ['HTTPS']) !== 'off') {
			$scheme = 'https';
		}
		$host = isset($_SERVER ['HTTP_HOST']) ? trim((string) $_SERVER ['HTTP_HOST']) : 'localhost';
		$script = isset($_SERVER ['SCRIPT_NAME']) ? (string) $_SERVER ['SCRIPT_NAME'] : '/';
		$basePath = rtrim(str_replace('\\', '/', dirname($script)), '/');
		$url = $scheme . '://' . $host . ($basePath !== '' ? $basePath . '/' : '/');
	}

	$parts = @parse_url($url);
	if (!is_array($parts) || empty($parts ['host'])) {
		return '';
	}

	$scheme = isset($parts ['scheme']) ? strtolower((string) $parts ['scheme']) : 'https';
	$host = strtolower((string) $parts ['host']);
	$port = isset($parts ['port']) ? ':' . (int) $parts ['port'] : '';
	$path = isset($parts ['path']) ? trim((string) $parts ['path'], '/') : '';

	return $scheme . '://' . $host . $port . ($path !== '' ? '/' . $path : '') . '/';
}

/**
 * Extract the URL token from a BBCode or attribute fragment.
 * @param string $value
 * @return string
 */
function plugin_mastodon_extract_url_token($value) {
	$value = plugin_mastodon_html_entity_decode(strip_tags((string) $value));
	$value = trim($value);
	if ($value === '') {
		return '';
	}

	$value = trim($value, " \t\n\r\0\x0B\"'");
	$parts = preg_split('/\s+/', $value);
	return isset($parts [0]) ? trim((string) $parts [0]) : '';
}

/**
 * Convert a URL or path into an absolute URL when possible.
 * @param string $url
 * @return string
 */
function plugin_mastodon_absolute_url($url) {
	$url = plugin_mastodon_extract_url_token($url);
	if ($url === '') {
		return '';
	}

	if (preg_match('!^(https?://|mailto:)!i', $url)) {
		return $url;
	}

	$base = plugin_mastodon_blog_base_url();
	if ($base === '') {
		return $url;
	}

	$baseParts = @parse_url($base);
	if (!is_array($baseParts) || empty($baseParts ['host'])) {
		return $url;
	}

	$scheme = isset($baseParts ['scheme']) ? strtolower((string) $baseParts ['scheme']) : 'https';
	$host = strtolower((string) $baseParts ['host']);
	$port = isset($baseParts ['port']) ? ':' . (int) $baseParts ['port'] : '';

	if (strpos($url, '//') === 0) {
		return $scheme . ':' . $url;
	}

	if (strpos($url, '/') === 0) {
		return $scheme . '://' . $host . $port . $url;
	}

	$url = preg_replace('!^\./!u', '', $url);
	return $base . ltrim((string) $url, '/');
}

/**
 * Return a localized plugin string or a provided fallback.
 * @param string $key
 * @param string $default
 * @return string
 */
function plugin_mastodon_lang_string($key, $default) {
	static $strings = null;

	if ($strings === null) {
		$strings = array();
		if (function_exists('lang_load')) {
			$lang = lang_load('plugin:mastodon');
			if (is_array($lang) && isset($lang ['admin'] ['plugin'] ['mastodon']) && is_array($lang ['admin'] ['plugin'] ['mastodon'])) {
				$strings = $lang ['admin'] ['plugin'] ['mastodon'];
			}
		}
	}

	return isset($strings [$key]) && is_string($strings [$key]) && $strings [$key] !== '' ? $strings [$key] : (string) $default;
}

/**
 * Determine whether a FlatPress plugin is enabled in the centralized plugin configuration.
 *
 * FlatPress core resolves the enabled plugin IDs from the plugin configuration via
 * plugin_indexer::getEnableds(), caches that list under the APCu key prefix
 * fp:plugins:enableds:list:v1:<confMtime> and exposes the resulting IDs as the global
 * $fp_plugins array for plugins that need to inspect activation state. When that global is
 * not available yet, the same plugins.conf.php file is read directly as a lightweight fallback,
 * because core.plugins.php currently has no public getter that returns only the enabled IDs.
 *
 * @param string $pluginId
 * @return bool|null Returns true/false when the central activation state is known, or null
 * when neither the global state nor the plugin configuration could be resolved.
 */
function plugin_mastodon_enabled_plugin_state($pluginId) {
	static $plugins = null;
	static $pluginsResolved = false;

	$pluginId = trim((string) $pluginId);
	if ($pluginId === '') {
		return null;
	}

	if (!$pluginsResolved) {
		if (isset($GLOBALS ['fp_plugins']) && is_array($GLOBALS ['fp_plugins'])) {
			$plugins = $GLOBALS ['fp_plugins'];
			$pluginsResolved = true;
		} else {
			$configFile = '';
			if (defined('ABS_PATH') && defined('CONFIG_DIR')) {
				$configFile = ABS_PATH . CONFIG_DIR . 'plugins.conf.php';
			} elseif (defined('CONFIG_DIR')) {
				$configFile = CONFIG_DIR . 'plugins.conf.php';
			}
			if ($configFile !== '' && @file_exists($configFile)) {
				$fp_plugins = array();
				include $configFile;
				$plugins = $fp_plugins;
				$pluginsResolved = true;
			}
		}
	}

	if (!is_array($plugins)) {
		return null;
	}

	if (!in_array($pluginId, $plugins, true)) {
		return false;
	}

	$pluginFile = '';
	if (defined('ABS_PATH') && defined('PLUGINS_DIR')) {
		$pluginFile = ABS_PATH . PLUGINS_DIR . $pluginId . '/plugin.' . $pluginId . '.php';
	} elseif (defined('PLUGINS_DIR')) {
		$pluginFile = PLUGINS_DIR . $pluginId . '/plugin.' . $pluginId . '.php';
	}

	$pluginFileExists = ($pluginFile !== '') ? @file_exists($pluginFile) : true;
	if (function_exists('plugin_exists')) {
		if (!plugin_exists($pluginId) && !$pluginFileExists) {
			return false;
		}
	} elseif (!$pluginFileExists) {
		return false;
	}

	return true;
}

/**
 * Determine whether the Tag plugin is active for the current FlatPress request.
 *
 * The Tag plugin exposes entry tags through [tag]...[/tag] BBCode and updates its
 * tag database on the publish_post hook. The Mastodon plugin only syncs tags when
 * the Tag plugin is actually active, otherwise entry content stays untouched.
 *
 * @return bool
 */
function plugin_mastodon_tag_plugin_active() {
	$enabled = plugin_mastodon_enabled_plugin_state('tag');
	if ($enabled !== null) {
		return $enabled;
	}

	return class_exists('plugin_tag_entry') && isset($GLOBALS ['plugin_tag']) && is_object($GLOBALS ['plugin_tag']);
}

/**
 * Determine whether the BBCode plugin is active for the current FlatPress request.
 *
 * Imported Mastodon content is stored as FlatPress BBCode. Without the BBCode plugin,
 * URLs, images, galleries and formatting remain visible as raw tags instead of being rendered.
 *
 * @return bool
 */
function plugin_mastodon_bbcode_plugin_active() {
	$enabled = plugin_mastodon_enabled_plugin_state('bbcode');
	if ($enabled !== null) {
		return $enabled;
	}

	return function_exists('plugin_bbcode_startup') || function_exists('do_bbcode_url') || function_exists('do_bbcode_img');
}

/**
 * Determine whether the PhotoSwipe plugin is active for the current FlatPress request.
 *
 * Imported and exported gallery/image tags work without PhotoSwipe, but the plugin provides
 * the richer image and gallery presentation that Mastodon media synchronization expects.
 *
 * @return bool
 */
function plugin_mastodon_photoswipe_plugin_active() {
	$enabled = plugin_mastodon_enabled_plugin_state('photoswipe');
	if ($enabled !== null) {
		return $enabled;
	}

	return class_exists('PhotoSwipeFunctions');
}

/**
 * Determine whether the Audio/Video plugin is active for the current FlatPress request.
 *
 * Imported Mastodon audio and video attachments are stored as FlatPress audioplayer/videoplayer
 * BBCode. Without the companion plugin, those tags remain visible as text.
 *
 * @return bool
 */
function plugin_mastodon_audiovideo_plugin_active() {
	$enabled = plugin_mastodon_enabled_plugin_state('audiovideo');
	if ($enabled !== null) {
		return $enabled;
	}

	return class_exists('AudioVideoPlugin');
}

/**
 * Determine whether the Emoticons plugin is active for the current FlatPress request.
 *
 * The Mastodon plugin can map emoji to FlatPress shortcode syntax, but the Emoticons plugin
 * is needed to render those shortcodes as icons in the FlatPress frontend and editor.
 *
 * @return bool
 */
function plugin_mastodon_emoticons_plugin_active() {
	$enabled = plugin_mastodon_enabled_plugin_state('emoticons');
	if ($enabled !== null) {
		return $enabled;
	}

	$emoticons = isset($GLOBALS ['plugin_emoticons']) ? $GLOBALS ['plugin_emoticons'] : null;
	return is_array($emoticons) && !empty($emoticons);
}

/**
 * Return the status of companion FlatPress plugins used for the full Mastodon feature set.
 *
 * @return array<int, array<string, mixed>>
 */
function plugin_mastodon_companion_plugins_status() {
	$statusActive = plugin_mastodon_lang_string('companion_status_active', 'Active');
	$statusMissing = plugin_mastodon_lang_string('companion_status_missing', 'Not active');
	return array(
		array(
			'slug' => 'bbcode',
			'label' => plugin_mastodon_lang_string('companion_bbcode_label', 'BBCode'),
			'active' => plugin_mastodon_bbcode_plugin_active(),
			'status_label' => plugin_mastodon_bbcode_plugin_active() ? $statusActive : $statusMissing,
			'description' => plugin_mastodon_lang_string('companion_bbcode_desc', 'Required to render imported URLs, images, galleries and formatting as FlatPress markup instead of raw BBCode.')
		),
		array(
			'slug' => 'photoswipe',
			'label' => plugin_mastodon_lang_string('companion_photoswipe_label', 'PhotoSwipe'),
			'active' => plugin_mastodon_photoswipe_plugin_active(),
			'status_label' => plugin_mastodon_photoswipe_plugin_active() ? $statusActive : $statusMissing,
			'description' => plugin_mastodon_lang_string('companion_photoswipe_desc', 'Recommended for the expected FlatPress presentation of imported and synchronized images and galleries.')
		),
		array(
			'slug' => 'audiovideo',
			'label' => plugin_mastodon_lang_string('companion_audiovideo_label', 'Audio/Video'),
			'active' => plugin_mastodon_audiovideo_plugin_active(),
			'status_label' => plugin_mastodon_audiovideo_plugin_active() ? $statusActive : $statusMissing,
			'description' => plugin_mastodon_lang_string('companion_audiovideo_desc', 'Required to render imported and synchronized Mastodon audio and video attachments as FlatPress HTML5 media players.')
		),
		array(
			'slug' => 'tag',
			'label' => plugin_mastodon_lang_string('companion_tag_label', 'Tag'),
			'active' => plugin_mastodon_tag_plugin_active(),
			'status_label' => plugin_mastodon_tag_plugin_active() ? $statusActive : $statusMissing,
			'description' => plugin_mastodon_lang_string('companion_tag_desc', 'Enables synchronized FlatPress tags and Mastodon hashtags in both directions.')
		),
		array(
			'slug' => 'emoticons',
			'label' => plugin_mastodon_lang_string('companion_emoticons_label', 'Emoticons'),
			'active' => plugin_mastodon_emoticons_plugin_active(),
			'status_label' => plugin_mastodon_emoticons_plugin_active() ? $statusActive : $statusMissing,
			'description' => plugin_mastodon_lang_string('companion_emoticons_desc', 'Renders imported Mastodon emoji shortcodes as FlatPress emoticons in entries and comments.')
		)
	);
}

/**
 * Normalize a list of tag labels.
 *
 * @param array<int, string> $tags
 * @return array<int, string>
 */
function plugin_mastodon_normalize_tag_list($tags) {
	$normalized = array();
	$seen = array();

	foreach ((array) $tags as $tag) {
		$tag = trim((string) $tag);
		$tag = ltrim($tag, "# \t\n\r\0\x0B");
		if ($tag === '') {
			continue;
		}
		$key = function_exists('mb_strtolower') ? mb_strtolower($tag, 'UTF-8') : strtolower($tag);
		if (isset($seen [$key])) {
			continue;
		}
		$seen [$key] = true;
		$normalized [] = $tag;
	}

	return $normalized;
}

/**
 * Extract FlatPress Tag plugin labels from an entry body.
 *
 * The Tag plugin stores tags inside [tag]...[/tag] BBCode blocks. Each block
 * contains a comma-separated list. The same semantics are used here so export
 * and import match the Tag plugin's own publish_post processing.
 *
 * @param string $content
 * @return array<int, string>
 */
function plugin_mastodon_extract_flatpress_tags($content) {
	$content = (string) $content;
	if ($content === '' || stripos($content, '[tag') === false) {
		return array();
	}

	$tags = array();
	if (preg_match_all('!\[tag\](.*?)\[/tag\]!is', $content, $matches) && !empty($matches[1])) {
		foreach ($matches [1] as $tagGroup) {
			$parts = explode(',', (string) $tagGroup);
			foreach ($parts as $part) {
				$tags [] = (string) $part;
			}
		}
	}

	return plugin_mastodon_normalize_tag_list($tags);
}

/**
 * Remove Tag plugin BBCode blocks from entry content.
 *
 * @param string $content
 * @return string
 */
function plugin_mastodon_strip_flatpress_tag_bbcode($content) {
	$content = preg_replace('!\n?\[tag\].*?\[/tag\]\n?!is', "\n", (string) $content);
	$content = str_replace(array("\r\n", "\r"), "\n", (string) $content);
	$content = preg_replace("/\n{3,}/", "\n\n", (string) $content);
	return trim((string) $content);
}

/**
 * Convert FlatPress tag labels into a Mastodon hashtag footer line.
 *
 * Mastodon exposes status tags as strings without the leading hash sign, while
 * the visible status text uses #hashtag tokens. Local FlatPress tags are therefore
 * normalized into safe hashtag tokens and emitted as a single footer line.
 *
 * @param array<int, string> $tags
 * @return string
 */
function plugin_mastodon_mastodon_hashtag_footer($tags) {
	$tokens = array();

	foreach (plugin_mastodon_normalize_tag_list($tags) as $tag) {
		$token = preg_replace('/\s+/u', '', $tag);
		$token = trim((string) $token);
		if ($token === '') {
			continue;
		}
		$tokens [] = '#' . $token;
	}

	return trim(implode(' ', array_unique($tokens)));
}

/**
 * Collect remote Mastodon tags from a status entity.
 *
 * @param array<string, mixed> $remoteStatus
 * @return array<int, string>
 */
function plugin_mastodon_remote_status_tags($remoteStatus) {
	$tags = array();

	if (!empty($remoteStatus ['tags']) && is_array($remoteStatus ['tags'])) {
		foreach ($remoteStatus ['tags'] as $remoteTag) {
			if (is_array($remoteTag) && !empty($remoteTag ['name'])) {
				$tags [] = (string) $remoteTag ['name'];
			} elseif (is_string($remoteTag)) {
				$tags [] = $remoteTag;
			}
		}
	}

	return plugin_mastodon_normalize_tag_list($tags);
}

/**
 * Remove a trailing Mastodon hashtag footer from imported plain text.
 *
 * Exported FlatPress tags are appended as a dedicated hashtag line. When such a
 * status is imported back, that trailing hashtag-only line is converted back into
 * Tag plugin metadata instead of staying visible in the FlatPress entry body.
 *
 * @param string $content
 * @param array<int, string> $remoteTags
 * @return string
 */
function plugin_mastodon_strip_trailing_mastodon_hashtag_footer($content, $remoteTags) {
	$content = trim((string) $content);
	$remoteTags = plugin_mastodon_normalize_tag_list($remoteTags);
	if ($content === '' || empty($remoteTags)) {
		return $content;
	}

	$normalizedRemote = array();
	foreach ($remoteTags as $tag) {
		$key = function_exists('mb_strtolower') ? mb_strtolower($tag, 'UTF-8') : strtolower($tag);
		$normalizedRemote [$key] = true;
	}

	$lines = preg_split("/\r\n|\r|\n/", $content);
	if (!is_array($lines) || empty($lines)) {
		return $content;
	}

	$index = count($lines) - 1;
	while ($index >= 0 && trim((string) $lines [$index]) === '') {
		$index--;
	}
	if ($index < 0) {
		return '';
	}

	$footerLine = trim((string) $lines [$index]);
	if ($footerLine === '') {
		return $content;
	}

	$tokens = preg_split('/\s+/u', $footerLine);
	if (!is_array($tokens) || empty($tokens)) {
		return $content;
	}

	$matched = array();
	foreach ($tokens as $token) {
		$token = trim((string) $token);
		if ($token === '' || strpos($token, '#') !== 0) {
			return $content;
		}
		$token = ltrim($token, '#');
		if ($token === '') {
			return $content;
		}
		$key = function_exists('mb_strtolower') ? mb_strtolower($token, 'UTF-8') : strtolower($token);
		if (!isset($normalizedRemote [$key])) {
			return $content;
		}
		$matched [$key] = true;
	}

	array_splice($lines, $index, 1);
	return trim((string) preg_replace("/\n{3,}/", "\n\n", implode("\n", $lines)));
}

/**
 * Build Tag plugin BBCode from a list of remote Mastodon tags.
 *
 * @param array<int, string> $tags
 * @return string
 */
function plugin_mastodon_build_flatpress_tag_bbcode($tags) {
	$tags = plugin_mastodon_normalize_tag_list($tags);
	if (empty($tags)) {
		return '';
	}
	return '[tag]' . implode(', ', $tags) . '[/tag]';
}

/**
 * Convert an emoticon HTML entity into a Unicode character.
 * @param string $value
 * @return string
 */
function plugin_mastodon_emoticon_entity_to_unicode($value) {
	$value = trim((string) $value);
	if ($value === '') {
		return '';
	}

	return html_entity_decode($value, defined('ENT_HTML5') ? ENT_QUOTES | ENT_HTML5 : ENT_QUOTES, 'UTF-8');
}

/**
 * Return the FlatPress emoticon-to-Unicode lookup map.
 * @return array<string, string>
 */
function plugin_mastodon_emoticon_map() {
	static $map = null;

	if ($map !== null) {
		return $map;
	}

	$map = array(
		':smile:' => '😄',
		':smiley:' => '😃',
		':wink:' => '😉',
		':blush:' => '😊',
		':grin:' => '😁',
		':smirk:' => '😏',
		':heart_eyes:' => '😍',
		':sunglasses:' => '😎',
		':laughing:' => '😆',
		':joy:' => '😂',
		':neutral_face:' => '😐',
		':flushed:' => '😳',
		':hushed:' => '😮',
		':dizzy_face:' => '😵',
		':cry:' => '😢',
		':persevere:' => '😣',
		':worried:' => '😟',
		':angry:' => '😠',
		':mag:' => '🔍',
		':hot_beverage:' => '☕',
		':exclamation:' => '❗',
		':question:' => '❓'
	);

	if (isset($GLOBALS ['plugin_emoticons']) && is_array($GLOBALS ['plugin_emoticons'])) {
		$detected = array();
		foreach ($GLOBALS ['plugin_emoticons'] as $shortcode => $entity) {
			$shortcode = trim((string) $shortcode);
			$unicode = plugin_mastodon_emoticon_entity_to_unicode($entity);
			if ($shortcode !== '' && $unicode !== '') {
				$detected [$shortcode] = $unicode;
			}
		}
		if (!empty($detected)) {
			$map = $detected;
		}
	}

	return $map;
}

/**
 * Replace FlatPress emoticon shortcodes with Unicode glyphs.
 * @param string $text
 * @return string
 */
function plugin_mastodon_replace_emoticon_shortcodes_with_unicode($text) {
	$text = (string) $text;
	$map = plugin_mastodon_emoticon_map();
	return empty($map) ? $text : strtr($text, $map);
}

/**
 * Convert FlatPress emoticon shortcodes to Mastodon-safe Unicode glyphs when the plugin is active.
 *
 * Mastodon status creation accepts plain-text status bodies, so FlatPress emoticon shortcodes need
 * to be converted before export if the Emoticons plugin is enabled for the current instance.
 *
 * @param string $text
 * @return string
 */
function plugin_mastodon_prepare_emoticons_for_mastodon($text) {
	$text = (string) $text;
	if ($text === '' || !plugin_mastodon_emoticons_plugin_active()) {
		return $text;
	}
	return plugin_mastodon_replace_emoticon_shortcodes_with_unicode($text);
}

/**
 * Replace Unicode emoticons with FlatPress shortcodes.
 * @param string $text
 * @return string
 */
function plugin_mastodon_replace_unicode_emoticons_with_shortcodes($text) {
	$text = (string) $text;
	$map = plugin_mastodon_emoticon_map();
	if (empty($map)) {
		return $text;
	}

	$reverse = array();
	foreach ($map as $shortcode => $unicode) {
		if ($unicode !== '') {
			$reverse [$unicode] = $shortcode;
		}
	}

	return empty($reverse) ? $text : strtr($text, $reverse);
}

/**
 * Determine whether a host name resolves to a public endpoint.
 * @param string $host
 * @return bool
 */
function plugin_mastodon_is_public_host($host) {
	$host = strtolower(trim((string) $host, ". \t\n\r\0\x0B"));
	if ($host === '') {
		return false;
	}

	if ($host === 'localhost' || substr($host, -10) === '.localhost' || substr($host, -6) === '.local' || substr($host, -5) === '.test' || substr($host, -8) === '.invalid' || substr($host, -8) === '.example') {
		return false;
	}

	$ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : '';
	if ($ip !== '') {
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
	}

	return strpos($host, '.') !== false;
}

/**
 * Return a Mastodon-safe public URL or an empty string.
 * @param string $url
 * @return string
 */
function plugin_mastodon_public_url_for_mastodon($url) {
	$url = plugin_mastodon_absolute_url($url);
	if ($url === '' || preg_match('!^mailto:!i', $url)) {
		return '';
	}

	$parts = @parse_url($url);
	if (!is_array($parts) || empty($parts ['host']) || !plugin_mastodon_is_public_host($parts ['host'])) {
		return '';
	}

	return $url;
}

/**
 * Convert FlatPress BBCode into plain text for Mastodon export.
 * @param string $text
 * @return string
 */
function plugin_mastodon_plain_text_from_bbcode($text) {
	$text = (string) $text;
	if ($text === '') {
		return '';
	}

	$text = preg_replace_callback(
		'!\[url=([^\]]+)\](.*?)\[/url\]!is',
		function ($matches) {
			$label = trim(plugin_mastodon_html_entity_decode(strip_tags((string) $matches [2])));
			if ($label !== '') {
				return $label;
			}
			return plugin_mastodon_absolute_url($matches [1]);
		},
		$text
	);
	$text = preg_replace_callback(
		'!\[url\](.*?)\[/url\]!is',
		function ($matches) {
			return plugin_mastodon_absolute_url($matches [1]);
		},
		$text
	);
	$text = preg_replace('!\[img\](.*?)\[/img\]!is', '', $text);
	$text = preg_replace('!\[\s*img\b[^\]]*\]!is', '', $text);
	$text = preg_replace('!\[\s*gallery\b[^\]]*\]!is', '', $text);
	$text = preg_replace('!\[\s*(?:audio|video)player\b[^\]]*\]!is', '', $text);
	$text = preg_replace('!\[(\/?)(b|i|u|h1|h2|h3|h4|list|\*|left|right|center|justify|color|size|font|flash|youtube|video|audio|mail|html|raw|more|table|tr|td|th|caption|tbody|thead|tfoot|quote|code)(=[^\]]*)?\]!is', '', $text);
	$text = strip_tags($text);
	$text = plugin_mastodon_html_entity_decode($text);
	$text = str_replace(array("\r\n", "\r"), "\n", $text);
	$text = preg_replace("/[ \t]+\n/u", "\n", $text);
	$text = preg_replace("/\n{3,}/", "\n\n", $text);

	return trim((string) $text);
}

/**
 * Determine whether an extracted line should be ignored as a subject.
 * @param mixed $line
 * @return bool
 */
function plugin_mastodon_subject_line_is_noise($line) {
	$line = trim((string) $line);
	if ($line === '') {
		return true;
	}

	if (preg_match('!^(https?://\S+|www\.\S+)$!iu', $line)) {
		return true;
	}
	if (preg_match('/^(?:@[\pL\pN._-]+(?:@[\pL\pN.-]+)?\s*)+$/u', $line)) {
		return true;
	}
	if (preg_match('/^@[^\s]+(?:\s+[A-Za-z0-9.-]+\.[A-Za-z]{2,})$/u', $line)) {
		return true;
	}
	if (preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/u', $line)) {
		return true;
	}

	return false;
}

/**
 * Determine whether two host names belong to the same domain family.
 * @param mixed $left
 * @param mixed $right
 * @return bool
 */
function plugin_mastodon_domains_match($left, $right) {
	$left = strtolower(trim((string) $left, ". \t\n\r\0\x0B"));
	$right = strtolower(trim((string) $right, ". \t\n\r\0\x0B"));
	if ($left === '' || $right === '') {
		return false;
	}
	return $left === $right || substr($left, -strlen($right) - 1) === '.' . $right || substr($right, -strlen($left) - 1) === '.' . $left;
}

/**
 * Clean imported text before saving it to FlatPress.
 * @param string $text
 * @return string
 */
function plugin_mastodon_cleanup_imported_text($text) {
	$text = str_replace(array("\r\n", "\r"), "\n", (string) $text);
	$text = preg_replace("/\n{3,}/", "\n\n", $text);
	$lines = explode("\n", $text);
	$clean = array();

	foreach ($lines as $line) {
		$line = preg_replace('/[ \t]+/u', ' ', rtrim((string) $line));
		$trimmed = trim((string) $line);

		if ($trimmed !== '' && !empty($clean)) {
			$lastIndex = null;
			for ($i = count($clean) - 1; $i >= 0; $i--) {
				if (trim((string) $clean [$i]) !== '') {
					$lastIndex = $i;
					break;
				}
			}

			if ($lastIndex !== null) {
				$previous = trim((string) $clean [$lastIndex]);

				if (preg_match('!^\[url=([^\]]+)\](@[^\[]+)\[/url\]$!u', $previous, $matches) && preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/u', $trimmed)) {
					$host = @parse_url(plugin_mastodon_absolute_url($matches [1]), PHP_URL_HOST);
					if (is_string($host) && plugin_mastodon_domains_match($host, $trimmed) && strpos($matches [2], '@' . $trimmed) === false) {
						$clean [$lastIndex] = '[url=' . plugin_mastodon_absolute_url($matches [1]) . ']' . $matches [2] . '@' . $trimmed . ' [/url]';
						continue;
					}
				}

				if (preg_match('!^\[url\](https?://[^\[]+)\[/url\]$!u', $previous, $matches) && preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/u', $trimmed)) {
					$host = @parse_url(plugin_mastodon_absolute_url($matches [1]), PHP_URL_HOST);
					if (is_string($host) && plugin_mastodon_domains_match($host, $trimmed)) {
						continue;
					}
				}
			}
		}

		if ($trimmed === '' && (!empty($clean) && end($clean) === '')) {
			continue;
		}

		$clean [] = $trimmed;
	}

	return trim(implode("\n", $clean));
}

/**
 * Convert DOM child nodes into FlatPress BBCode text.
 * @param DOMNode $node
 * @return string
 */
function plugin_mastodon_dom_children_to_flatpress($node) {
	$output = '';
	if (!$node || !$node->hasChildNodes()) {
		return $output;
	}

	foreach ($node->childNodes as $child) {
		$output .= plugin_mastodon_dom_node_to_flatpress($child);
	}

	return $output;
}

/**
 * Convert a single DOM node into FlatPress BBCode text.
 * @param DOMNode $node
 * @return string
 */
function plugin_mastodon_dom_node_to_flatpress($node) {
	if (!$node) {
		return '';
	}

	if ($node->nodeType === XML_TEXT_NODE || $node->nodeType === XML_CDATA_SECTION_NODE) {
		return plugin_mastodon_html_entity_decode($node->nodeValue);
	}

	if ($node->nodeType !== XML_ELEMENT_NODE) {
		return '';
	}

	$name = strtolower((string) $node->nodeName);
	if (in_array($name, array('script', 'style', 'template'), true)) {
		return '';
	}

	if ($name === 'br') {
		return "\n";
	}
	if ($name === 'hr') {
		return "\n----\n";
	}
	if ($name === 'img') {
		if (!($node instanceof DOMElement)) {
			return '';
		}
		$alt = trim((string) $node->getAttribute('alt'));
		$class = ' ' . strtolower(trim((string) $node->getAttribute('class'))) . ' ';
		if ($alt !== '' && preg_match('/^:[A-Za-z0-9_+\-]+:$/', $alt)) {
			return $alt;
		}
		if ($alt !== '' && (strpos($class, ' emoji ') !== false || strpos($class, ' emojione ') !== false || strpos($class, ' custom-emoji ') !== false)) {
			return $alt;
		}
		return '';
	}
	if ($name === 'blockquote') {
		$inner = trim(plugin_mastodon_dom_children_to_flatpress($node));
		return $inner === '' ? '' : "\n[quote]\n" . $inner . "\n[/quote]\n";
	}
	if ($name === 'strong' || $name === 'b') {
		return '[b]' . trim(plugin_mastodon_dom_children_to_flatpress($node)) . '[/b]';
	}
	if ($name === 'em' || $name === 'i') {
		return '[i]' . trim(plugin_mastodon_dom_children_to_flatpress($node)) . '[/i]';
	}
	if ($name === 'pre') {
		$inner = plugin_mastodon_html_entity_decode($node->textContent);
		$inner = str_replace(array("\r\n", "\r"), "\n", $inner);
		return "\n[code]\n" . trim($inner) . "\n[/code]\n";
	}
	if ($name === 'code') {
		$parentName = ($node->parentNode && isset($node->parentNode->nodeName)) ? strtolower((string) $node->parentNode->nodeName) : '';
		if ($parentName === 'pre') {
			return plugin_mastodon_html_entity_decode($node->textContent);
		}
		return '[code]' . trim(plugin_mastodon_html_entity_decode($node->textContent)) . '[/code]';
	}
	if ($name === 'ul' || $name === 'ol') {
		$items = trim(plugin_mastodon_dom_children_to_flatpress($node));
		return $items === '' ? '' : "\n[list]\n" . $items . "\n[/list]\n";
	}
	if ($name === 'li') {
		$item = trim(plugin_mastodon_dom_children_to_flatpress($node));
		return $item === '' ? '' : '[*] ' . $item . "\n";
	}
	if ($name === 'a') {
		if (!($node instanceof DOMElement)) {
			return plugin_mastodon_plain_text_from_bbcode(plugin_mastodon_dom_children_to_flatpress($node));
		}
		$href = plugin_mastodon_absolute_url($node->getAttribute('href'));
		$label = plugin_mastodon_plain_text_from_bbcode(plugin_mastodon_dom_children_to_flatpress($node));
		$label = trim(preg_replace('/\s+/u', ' ', $label));

		if ($href === '') {
			return $label;
		}
		if ($label === '') {
			return '[url]' . $href . '[/url]';
		}

		$normalizedLabel = preg_replace('!^https?://!iu', '', rtrim($label, '/'));
		$normalizedHref = preg_replace('!^https?://!iu', '', rtrim($href, '/'));
		if ($normalizedLabel === $normalizedHref) {
			return '[url]' . $href . '[/url]';
		}

		return '[url=' . $href . ']' . $label . '[/url]';
	}
	if (in_array($name, array('p', 'div', 'section', 'article', 'header', 'footer'), true)) {
		$inner = trim(plugin_mastodon_dom_children_to_flatpress($node));
		return $inner === '' ? '' : $inner . "\n\n";
	}
	if (in_array($name, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'), true)) {
		$inner = trim(plugin_mastodon_dom_children_to_flatpress($node));
		return $inner === '' ? '' : '[b]' . $inner . '[/b]' . "\n\n";
	}

	return plugin_mastodon_dom_children_to_flatpress($node);
}

/**
 * Return the public URL for a FlatPress entry.
 * @param string $entryId
 * @param array<string, mixed> $entry
 * @return string
 */
function plugin_mastodon_public_entry_url($entryId, $entry) {
	$entryId = (string) $entryId;
	if ($entryId === '') {
		return '';
	}

	$entry = is_array($entry) ? $entry : array();
	$currentPost = isset($GLOBALS ['post']) ? $GLOBALS ['post'] : null;
	$hadPost = array_key_exists('post', $GLOBALS);

	$GLOBALS ['post'] = $entry;
	$link = function_exists('get_permalink') ? get_permalink($entryId) : '';
	if ($hadPost) {
		$GLOBALS ['post'] = $currentPost;
	} else {
		unset($GLOBALS ['post']);
	}

	return plugin_mastodon_absolute_url($link);
}

/**
 * Return the public comments URL for a FlatPress entry.
 * @param string $entryId
 * @param array<string, mixed> $entry
 * @return string
 */
function plugin_mastodon_public_comments_url($entryId, $entry) {
	$entryId = (string) $entryId;
	if ($entryId === '') {
		return '';
	}

	$entry = is_array($entry) ? $entry : array();
	$currentPost = isset($GLOBALS ['post']) ? $GLOBALS ['post'] : null;
	$hadPost = array_key_exists('post', $GLOBALS);

	$GLOBALS ['post'] = $entry;
	$link = function_exists('get_comments_link') ? get_comments_link($entryId) : '';
	if ($hadPost) {
		$GLOBALS ['post'] = $currentPost;
	} else {
		unset($GLOBALS ['post']);
	}

	return plugin_mastodon_absolute_url($link);
}

/**
 * Return the public URL for a specific FlatPress comment.
 * @param string $entryId
 * @param array<string, mixed> $entry
 * @param string $commentId
 * @return string
 */
function plugin_mastodon_public_comment_url($entryId, $entry, $commentId) {
	$baseUrl = plugin_mastodon_public_comments_url($entryId, $entry);
	$commentId = trim((string) $commentId);
	if ($baseUrl === '' || $commentId === '') {
		return '';
	}

	return $baseUrl . '#' . $commentId;
}

/**
 * Convert Mastodon HTML content into FlatPress BBCode.
 * @param string $html
 * @return string
 */
function plugin_mastodon_mastodon_html_to_flatpress($html) {
	$html = (string) $html;
	if ($html === '') {
		return '';
	}

	$text = '';
	if (class_exists('DOMDocument')) {
		$internalErrors = function_exists('libxml_use_internal_errors') ? libxml_use_internal_errors(true) : false;
		$doc = new DOMDocument('1.0', 'UTF-8');
		$flags = 0;
		if (defined('LIBXML_NONET')) {
			$flags |= LIBXML_NONET;
		}
		$loaded = @$doc->loadHTML('<?xml encoding="UTF-8"><!DOCTYPE html><html><body>' . $html . '</body></html>', $flags);
		if ($loaded) {
			$body = $doc->getElementsByTagName('body')->item(0);
			if ($body) {
				$text = trim(plugin_mastodon_dom_children_to_flatpress($body));
			}
		}
		if (function_exists('libxml_clear_errors')) {
			libxml_clear_errors();
		}
		if (function_exists('libxml_use_internal_errors')) {
			libxml_use_internal_errors($internalErrors);
		}
	}

	if ($text === '') {
		$replacements = array(
			'!<br\s*/?>!i' => "\n",
			'!</p>\s*<p[^>]*>!i' => "\n\n",
			'!<p[^>]*>!i' => '',
			'!</p>!i' => '',
			'!<blockquote\b[^>]*>!i' => '[quote]',
			'!</blockquote>!i' => '[/quote]',
			'!<(strong|b)\b[^>]*>!i' => '[b]',
			'!</(strong|b)>!i' => '[/b]',
			'!<(em|i)\b[^>]*>!i' => '[i]',
			'!</(em|i)>!i' => '[/i]',
			'!<pre\b[^>]*><code\b[^>]*>!i' => '[code]',
			'!</code></pre>!i' => '[/code]',
			'!<pre\b[^>]*>!i' => '[code]',
			'!</pre>!i' => '[/code]',
			'!<code\b[^>]*>!i' => '[code]',
			'!</code>!i' => '[/code]',
			'!<li\b[^>]*>!i' => "\n[*] ",
			'!</li>!i' => '',
			'!<ul\b[^>]*>!i' => "\n[list]\n",
			'!</ul>!i' => "\n[/list]\n",
			'!<ol\b[^>]*>!i' => "\n[list]\n",
			'!</ol>!i' => "\n[/list]\n"
		);

		foreach ($replacements as $pattern => $replacement) {
			$html = preg_replace($pattern, $replacement, $html);
		}

		$html = preg_replace_callback(
			'!<img\s[^>]*alt=(["\'])(.*?)\1[^>]*>!is',
			function ($matches) {
				$alt = trim(plugin_mastodon_html_entity_decode((string) $matches [2]));
				return preg_match('/^:[A-Za-z0-9_+\-]+:$/', $alt) ? $alt : '';
			},
			$html
		);

		$text = preg_replace_callback(
			'!<a\s[^>]*href=(["\'])(.*?)\1[^>]*>(.*?)</a>!is',
			function ($matches) {
				$href = plugin_mastodon_absolute_url($matches [2]);
				$label = trim(plugin_mastodon_html_entity_decode(strip_tags($matches [3])));
				if ($href === '') {
					return $label;
				}
				if ($label === '') {
					return '[url]' . $href . '[/url]';
				}
				return '[url=' . $href . ']' . $label . '[/url]';
			},
			$html
		);

		$text = strip_tags($text);
		$text = plugin_mastodon_html_entity_decode($text);
	}

	$text = plugin_mastodon_replace_unicode_emoticons_with_shortcodes($text);
	$text = str_replace(array("\r\n", "\r"), "\n", $text);
	$text = preg_replace("/[ \t]+\n/u", "\n", $text);
	$text = preg_replace("/\n{3,}/", "\n\n", $text);
	$text = plugin_mastodon_cleanup_imported_text($text);

	return trim((string) $text);
}

/**
 * Convert FlatPress content into Mastodon-ready plain text.
 * @param string $text
 * @return string
 */
function plugin_mastodon_flatpress_to_mastodon($text) {
	$text = plugin_mastodon_prepare_emoticons_for_mastodon((string) $text);
	if ($text === '') {
		return '';
	}
	if (plugin_mastodon_tag_plugin_active()) {
		$text = plugin_mastodon_strip_flatpress_tag_bbcode($text);
	}

	$text = preg_replace_callback(
		'!<a\s[^>]*href=(["\'])(.*?)\1[^>]*>(.*?)</a>!is',
		function ($matches) {
			$url = plugin_mastodon_public_url_for_mastodon($matches [2]);
			$label = trim(plugin_mastodon_html_entity_decode(strip_tags((string) $matches [3])));
			if ($url === '') {
				return $label;
			}
			if ($label === '' || $label === $url) {
				return $url;
			}
			return $label . ' ' . $url;
		},
		$text
	);

	$text = preg_replace_callback(
		'!\[url=([^\]]+)\](.*?)\[/url\]!is',
		function ($matches) {
			$url = plugin_mastodon_public_url_for_mastodon($matches [1]);
			$label = trim(plugin_mastodon_html_entity_decode(strip_tags((string) $matches [2])));
			if ($url === '') {
				return $label;
			}
			if ($label === '' || $label === $url) {
				return $url;
			}
			return $label . ' ' . $url;
		},
		$text
	);

	$text = preg_replace_callback(
		'!\[url\](.*?)\[/url\]!is',
		function ($matches) {
			return plugin_mastodon_public_url_for_mastodon($matches [1]);
		},
		$text
	);

	$text = preg_replace_callback(
		'!\[img\](.*?)\[/img\]!is',
		function ($matches) {
			return plugin_mastodon_public_url_for_mastodon($matches [1]);
		},
		$text
	);

	$text = preg_replace('!\[\s*img\b[^\]]*\]!is', '', $text);
	$text = preg_replace('!\[\s*gallery\b[^\]]*\]!is', '', $text);
	$text = preg_replace('!\[\s*(?:audio|video)player\b[^\]]*\]!is', '', $text);

	$text = preg_replace_callback(
		'!\[list(?:=[^\]]*)?\](.*?)\[/list\]!is',
		function ($matches) {
			$body = str_replace(array("\r\n", "\r"), "\n", (string) $matches [1]);
			$body = preg_replace('/\[\*\]\s*/iu', "\n• ", $body);
			return "\n" . trim($body) . "\n";
		},
		$text
	);

	$text = preg_replace_callback(
		'!\[quote\](.*?)\[/quote\]!is',
		function ($matches) {
			$body = trim((string) $matches [1]);
			$body = preg_replace('/^\s+/m', '', $body);
			$body = preg_replace('/^/m', '> ', $body);
			return "\n" . $body . "\n";
		},
		$text
	);

	$text = preg_replace_callback(
		'!\[code\](.*?)\[/code\]!is',
		function ($matches) {
			return "\n```\n" . trim((string) $matches [1]) . "\n```\n";
		},
		$text
	);

	$text = preg_replace_callback(
		'!\[(h1|h2|h3|h4)\](.*?)\[/\1\]!is',
		function ($matches) {
			$heading = trim(plugin_mastodon_html_entity_decode(strip_tags((string) $matches [2])));
			return $heading === '' ? '' : "\n" . $heading . "\n";
		},
		$text
	);

	$text = preg_replace('!\[more\]!is', "\n", $text);
	$text = preg_replace('!\[(\/?)(b|i|u|left|right|center|justify|color|size|font|flash|youtube|video|audio|mail|html|raw|table|tr|td|th|caption|tbody|thead|tfoot)(=[^\]]*)?\]!is', '', $text);
	$text = strip_tags($text);
	$text = plugin_mastodon_html_entity_decode($text);
	$text = str_replace(array("\r\n", "\r"), "\n", $text);
	$text = preg_replace("/[ \t]+\n/u", "\n", $text);
	$text = preg_replace("/\n{3,}/", "\n\n", $text);

	return trim((string) $text);
}

/**
 * Limit text to a maximum number of characters.
 * @param string $text
 * @param int $limit
 * @return string
 */
function plugin_mastodon_limit_text($text, $limit) {
	$text = trim((string) $text);
	$limit = (int) $limit;
	if ($limit <= 0) {
		return $text;
	}
	$length = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
	if ($length <= $limit) {
		return $text;
	}
	$suffix = '…';
	if (function_exists('mb_substr')) {
		return rtrim(mb_substr($text, 0, $limit - 1, 'UTF-8')) . $suffix;
	}
	return rtrim(substr($text, 0, $limit - 1)) . $suffix;
}

/**
 * Build a change-detection hash for a FlatPress entry.
 * @param array<string, mixed> $entry
 * @return string
 */
function plugin_mastodon_entry_hash($entry) {
	$subject = isset($entry ['subject']) ? (string) $entry ['subject'] : '';
	$content = isset($entry ['content']) ? (string) $entry ['content'] : '';
	$mediaSignature = plugin_mastodon_entry_media_signature($content);
	return sha1($subject . "\n" . $content . "\n" . $mediaSignature);
}

/**
 * Build a change-detection hash for a FlatPress comment.
 * @param array<string, mixed> $comment
 * @return string
 */
function plugin_mastodon_comment_hash($comment) {
	$name = isset($comment ['name']) ? (string) $comment ['name'] : '';
	$content = isset($comment ['content']) ? (string) $comment ['content'] : '';
	$parent = '';
	foreach (array('replyto', 'reply_to', 'parent', 'parent_id', 'in_reply_to', 'in_reply_to_id', 'replytoid', 'reply_to_id', 'inreplyto') as $parentKey) {
		if (!empty($comment [$parentKey])) {
			$parent = trim((string) $comment [$parentKey]);
			break;
		}
	}
	return sha1($name . "\n" . $content . "\n" . $parent);
}

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
 * Resolve a FlatPress media path to an absolute file path.
 * @param string $relativePath
 * @return string
 */
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
 * Escape a value for safe BBCode attribute usage.
 * @param string $value
 * @return string
 */
function plugin_mastodon_bbcode_attr_escape($value) {
	$value = str_replace(array("\r", "\n"), ' ', (string) $value);
	$value = trim((string) $value);
	$value = str_replace(array('"', '[', ']'), array('&quot;', '(', ')'), $value);
	return $value;
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
			$attrText = isset($match [1]) ? (string) $match [1] : '';
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
			$attrText = isset($match [1]) ? (string) $match [1] : '';
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
		if (!preg_match_all('/\[\s*' . $tagName . '\b([^\]]*)\]/iu', $content, $matches, PREG_SET_ORDER)) {
			continue;
		}
		foreach ($matches as $match) {
			$attrText = isset($match [1]) ? (string) $match [1] : '';
			$attributes = plugin_mastodon_media_parse_tag_attributes($attrText);
			$relativePath = plugin_mastodon_media_extract_default_path($attrText);
			if ($relativePath === '' && !empty($attributes ['src'])) {
				$relativePath = $attributes ['src'];
			}
			if ($relativePath === '' || preg_match('!^https?://!i', $relativePath)) {
				continue;
			}

			$description = '';
			foreach (array('description', 'title', 'alt') as $descriptionKey) {
				if (!empty($attributes [$descriptionKey])) {
					$description = $attributes [$descriptionKey];
					break;
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
 * Normalize local entry media items for signature and export planning.
 * @param array<int, array<string, mixed>> $mediaItems
 * @param int $limit
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
		if ($limit > 0 && count($prepared) >= $limit) {
			$skipped++;
			continue;
		}
		$prepared [] = $item;
	}
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
 * Extract image attachments from a remote Mastodon status.
 * @param array<string, mixed> $remoteStatus
 * @return array<int, array<string, mixed>>
 */
function plugin_mastodon_remote_status_image_attachments($remoteStatus) {
	return plugin_mastodon_remote_status_media_attachments($remoteStatus, array('image'));
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
 * Download a remote media asset.
 * @param string $url
 * @param array<int|string, string> $headers
 * @return array<string, mixed>
 */
function plugin_mastodon_media_download($url, $headers) {
	plugin_mastodon_extend_time_limit(180);
	return plugin_mastodon_http_request('GET', $url, is_array($headers) ? $headers : array(), '', '', 180);
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
 * Download and store one remote media URL.
 * @param string $sourceUrl
 * @param array<int, string> $downloadHeaders
 * @param string $targetDir
 * @param string $basename
 * @return bool
 */
function plugin_mastodon_store_remote_media_url($sourceUrl, $downloadHeaders, $targetDir, $basename) {
	$download = plugin_mastodon_media_download($sourceUrl, $downloadHeaders);
	if (!$download ['ok'] || $download ['body'] === '') {
		return false;
	}
	if (!plugin_mastodon_media_prepare_directory($targetDir)) {
		return false;
	}
	return plugin_mastodon_io_write_file($targetDir . DIRECTORY_SEPARATOR . $basename, (string) $download ['body']);
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
			$download = plugin_mastodon_media_download($sourceUrl, $downloadHeaders);
			if (!empty($download ['ok']) && $download ['body'] !== '') {
				break;
			}
			plugin_mastodon_log('Failed to download remote media candidate for status ' . $remoteId . ' from ' . $sourceUrl . ': ' . plugin_mastodon_response_error_message($download));
		}
		if (!$download ['ok'] || $download ['body'] === '') {
			plugin_mastodon_log('Failed to download remote media for status ' . $remoteId . ' from all candidate URLs');
			continue;
		}

		if ($type === 'image') {
			$imageIndex++;
			$basename = plugin_mastodon_remote_download_basename($attachment, $sourceUrl, $download, $imageIndex, 'image');
			if (!plugin_mastodon_io_write_file($tempImageDir . DIRECTORY_SEPARATOR . $basename, (string) $download ['body'])) {
				plugin_mastodon_log('Failed to store imported remote image file ' . $basename . ' for status ' . $remoteId);
				continue;
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
		if (!plugin_mastodon_io_write_file($tempAttachDir . DIRECTORY_SEPARATOR . $basename, (string) $download ['body'])) {
			plugin_mastodon_log('Failed to store imported remote audio/video file ' . $basename . ' for status ' . $remoteId);
			continue;
		}

		$posterRelative = '';
		if ($type !== 'audio' && !empty($attachment ['preview_url']) && is_string($attachment ['preview_url'])) {
			$posterUrl = trim((string) $attachment ['preview_url']);
			if ($posterUrl !== '' && $posterUrl !== $sourceUrl) {
				$posterDownload = plugin_mastodon_media_download($posterUrl, $downloadHeaders);
				if (!empty($posterDownload ['ok']) && $posterDownload ['body'] !== '') {
					$posterBase = preg_replace('/\.[A-Za-z0-9]{1,8}$/', '', $basename);
					$posterExtension = plugin_mastodon_extension_from_mime_type(isset($posterDownload ['headers'] ['content-type']) ? (string) $posterDownload ['headers'] ['content-type'] : '', 'jpg');
					$posterName = plugin_mastodon_safe_filename($posterBase . '-poster.' . $posterExtension);
					if (plugin_mastodon_io_write_file($tempImageDir . DIRECTORY_SEPARATOR . $posterName, (string) $posterDownload ['body'])) {
						$posterRelative = 'images/mastodon/status-' . $remoteId . '/' . $posterName;
					}
				}
			}
		}

		$avItems [] = array(
			'type' => $type === 'audio' ? 'audio' : 'video',
			'relative_path' => 'attachs/mastodon/status-' . $remoteId . '/' . $basename,
			'poster' => $posterRelative,
			'loop' => $type === 'gifv' ? '1' : ''
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
			$relative = isset($item ['relative_path']) ? (string) $item ['relative_path'] : '';
			if ($relative === '') {
				continue;
			}
			$type = isset($item ['type']) ? (string) $item ['type'] : '';
			if ($type === 'audio') {
				$bbcodeParts [] = '[audioplayer="' . plugin_mastodon_bbcode_attr_escape($relative) . '" controls="1"]';
			} else {
				$tag = '[videoplayer="' . plugin_mastodon_bbcode_attr_escape($relative) . '" controls="1"';
				if (!empty($item ['loop'])) {
					$tag .= ' loop="1"';
				}
				if (!empty($item ['poster'])) {
					$tag .= ' poster="' . plugin_mastodon_bbcode_attr_escape((string) $item ['poster']) . '"';
				}
				$tag .= ']';
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
 * Best-effort refresh/increase of the PHP execution time budget for long-running Mastodon work.
 *
 * The helper never lowers an existing higher or unlimited execution limit. It primarily protects
 * shared FlatPress web requests from hitting the default 30-second PHP timeout on platforms where
 * real elapsed wall time is counted (notably Windows/IIS) while remaining a no-op on hosts that
 * disallow runtime timeout changes.
 *
 * @param int $minimumSeconds
 * @return int
 */
function plugin_mastodon_extend_time_limit($minimumSeconds = 60) {
	$minimumSeconds = max(0, (int) $minimumSeconds);
	$currentValue = @ini_get('max_execution_time');
	$currentLimit = is_numeric($currentValue) ? (int) $currentValue : 0;
	$targetLimit = $minimumSeconds;
	if ($currentLimit === 0) {
		$targetLimit = 0;
	} elseif ($currentLimit > $targetLimit) {
		$targetLimit = $currentLimit;
	}
	if (isset($GLOBALS ['plugin_mastodon_test_timeout_calls']) && is_array($GLOBALS ['plugin_mastodon_test_timeout_calls'])) {
		$GLOBALS ['plugin_mastodon_test_timeout_calls'] [] = array(
			'minimum' => $minimumSeconds,
			'current' => $currentLimit,
			'target' => $targetLimit
		);
	}
	if (function_exists('set_time_limit')) {
		@set_time_limit($targetLimit);
	}
	if (function_exists('ini_set')) {
		@ini_set('max_execution_time', (string) $targetLimit);
	}
	return $targetLimit;
}

/**
 * Load and cache the full Mastodon instance document returned by /api/v2/instance.
 * @param array<string, string> $options
 * @return array<string, mixed>
 */
function plugin_mastodon_instance_document($options, $allowNetwork = true) {
	$base = plugin_mastodon_normalize_instance_url(isset($options ['instance_url']) ? $options ['instance_url'] : '');
	if ($base === '') {
		return array();
	}

	$cached = plugin_mastodon_runtime_cache_get('instance_document', $base, $hit);
	if ($hit && is_array($cached) && $cached !== array()) {
		return $cached;
	}

	$stored = plugin_mastodon_saved_instance_document($options);
	if ($stored !== array()) {
		return plugin_mastodon_runtime_cache_set('instance_document', $base, $stored);
	}

	$apcuKey = 'instance_document:' . sha1($base);
	$apcuValue = plugin_mastodon_apcu_fetch($apcuKey, $apcuHit);
	if ($apcuHit && is_array($apcuValue) && $apcuValue !== array()) {
		$document = plugin_mastodon_compact_instance_document($apcuValue);
		if ($document !== array()) {
			return plugin_mastodon_runtime_cache_set('instance_document', $base, $document);
		}
	}

	if (!$allowNetwork) {
		return array();
	}

	$response = plugin_mastodon_mastodon_json($options, 'GET', '/api/v2/instance', array(), false);
	$document = (!empty($response ['ok']) && !empty($response ['json']) && is_array($response ['json'])) ? plugin_mastodon_compact_instance_document($response ['json']) : array();
	if ($document !== array()) {
		plugin_mastodon_apcu_store($apcuKey, $document, 900);
		plugin_mastodon_store_instance_document($options, $document);
		return plugin_mastodon_runtime_cache_set('instance_document', $base, $document);
	}
	return array();
}

/**
 * Return the Mastodon version string advertised by /api/v2/instance, if any.
 * @param array<string, string> $options
 * @return string
 */
function plugin_mastodon_instance_version($options) {
	$document = plugin_mastodon_instance_document($options);
	if (!empty($document ['version']) && is_string($document ['version'])) {
		return trim((string) $document ['version']);
	}
	return '';
}

/**
 * Determine whether the configured Mastodon instance should support media description updates on already-posted statuses.
 *
 * Mastodon added `media_attributes` support on `PUT /api/v1/statuses/:id` in 4.1.0.
 * When the instance version cannot be determined, the helper safely returns false so
 * the caller can fall back to re-uploading media for description-only changes.
 *
 * @param array<string, string> $options
 * @return bool
 */
function plugin_mastodon_instance_supports_status_media_attributes($options) {
	$version = plugin_mastodon_instance_version($options);
	if ($version === '') {
		return false;
	}
	$normalized = preg_replace('/[^0-9.].*$/', '', $version);
	if (!is_string($normalized) || $normalized === '' || !preg_match('/^\d+(?:\.\d+){0,3}$/', $normalized)) {
		return false;
	}
	return version_compare($normalized, '4.1.0', '>=');
}

/**
 * Load and cache the Mastodon instance configuration document.
 * @param array<string, string> $options
 * @return array<string, mixed>
 */
function plugin_mastodon_instance_configuration($options) {
	$document = plugin_mastodon_instance_document($options);
	return (!empty($document ['configuration']) && is_array($document ['configuration'])) ? $document ['configuration'] : array();
}

/**
 * Return the media attachment limit of the configured instance.
 * @param array<string, string> $options
 * @return int
 */
function plugin_mastodon_instance_media_limit($options) {
	$configuration = plugin_mastodon_instance_configuration($options);
	if (!empty($configuration ['statuses'] ['max_media_attachments'])) {
		return max(1, (int) $configuration ['statuses'] ['max_media_attachments']);
	}
	return 4;
}

/**
 * Return the media description length limit of the configured instance.
 * @param array<string, string> $options
 * @return int
 */
function plugin_mastodon_instance_media_description_limit($options) {
	$configuration = plugin_mastodon_instance_configuration($options);
	if (!empty($configuration ['media_attachments'] ['description_limit'])) {
		return max(0, (int) $configuration ['media_attachments'] ['description_limit']);
	}
	return 1500;
}

/**
 * Return the per-URL character budget used by the configured instance.
 * @param array<string, string> $options
 * @return int
 */
function plugin_mastodon_instance_url_reserved_length($options) {
	$configuration = plugin_mastodon_instance_configuration($options);
	if (!empty($configuration ['statuses'] ['characters_reserved_per_url'])) {
		return max(1, (int) $configuration ['statuses'] ['characters_reserved_per_url']);
	}
	return 23;
}

/**
 * Calculate the Mastodon-visible length of a plain-text status.
 * @param string $text
 * @param int $urlReservedLength
 * @return int
 */
function plugin_mastodon_status_text_length($text, $urlReservedLength = 23) {
	$text = trim((string) $text);
	$urlReservedLength = max(1, (int) $urlReservedLength);
	if ($text === '') {
		return 0;
	}
	if (!preg_match_all('~https?://[^\s<>"]+~iu', $text, $matches, PREG_OFFSET_CAPTURE)) {
		return function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
	}

	$length = 0;
	$offset = 0;
	foreach ($matches [0] as $match) {
		$url = isset($match [0]) ? (string) $match [0] : '';
		$position = isset($match [1]) ? (int) $match [1] : 0;
		$segment = substr($text, $offset, $position - $offset);
		$length += function_exists('mb_strlen') ? mb_strlen($segment, 'UTF-8') : strlen($segment);
		$length += $urlReservedLength;
		$offset = $position + strlen($url);
	}
	$tail = substr($text, $offset);
	$length += function_exists('mb_strlen') ? mb_strlen($tail, 'UTF-8') : strlen($tail);
	return $length;
}

/**
 * Limit Mastodon plain text while respecting the instance URL budget.
 * @param string $text
 * @param int $limit
 * @param int $urlReservedLength
 * @return string
 */
function plugin_mastodon_limit_status_text($text, $limit, $urlReservedLength = 23) {
	$text = trim((string) $text);
	$limit = (int) $limit;
	$urlReservedLength = max(1, (int) $urlReservedLength);
	if ($limit <= 0 || $text === '') {
		return $text;
	}
	if (plugin_mastodon_status_text_length($text, $urlReservedLength) <= $limit) {
		return $text;
	}
	if ($limit === 1) {
		return '…';
	}

	$budget = $limit - 1;
	$result = '';
	$consumed = 0;
	$matches = array();
	if (!preg_match_all('~https?://[^\s<>"]+~iu', $text, $matches, PREG_OFFSET_CAPTURE)) {
		return plugin_mastodon_limit_text($text, $limit);
	}

	$offset = 0;
	$truncated = false;
	foreach ($matches [0] as $match) {
		$url = isset($match [0]) ? (string) $match [0] : '';
		$position = isset($match [1]) ? (int) $match [1] : 0;
		$segment = substr($text, $offset, $position - $offset);
		$segmentLength = function_exists('mb_strlen') ? mb_strlen($segment, 'UTF-8') : strlen($segment);
		$available = $budget - $consumed;
		if ($segmentLength > $available) {
			if ($available > 0) {
				$result .= function_exists('mb_substr') ? mb_substr($segment, 0, $available, 'UTF-8') : substr($segment, 0, $available);
			}
			$truncated = true;
			break;
		}
		$result .= $segment;
		$consumed += $segmentLength;
		$available = $budget - $consumed;
		if ($urlReservedLength > $available) {
			$truncated = true;
			break;
		}
		$result .= $url;
		$consumed += $urlReservedLength;
		$offset = $position + strlen($url);
	}

	if (!$truncated) {
		$tail = substr($text, $offset);
		$tailLength = function_exists('mb_strlen') ? mb_strlen($tail, 'UTF-8') : strlen($tail);
		$available = $budget - $consumed;
		if ($tailLength > $available) {
			if ($available > 0) {
				$result .= function_exists('mb_substr') ? mb_substr($tail, 0, $available, 'UTF-8') : substr($tail, 0, $available);
			}
			$truncated = true;
		} else {
			$result .= $tail;
		}
	}

	if (!$truncated) {
		return trim((string) $result);
	}
	$result = rtrim((string) $result);
	return $result === '' ? '…' : $result . '…';
}

/**
 * Perform a multipart HTTP request.
 * @param string $method
 * @param string $url
 * @param array<int|string, string> $headers
 * @param array<string, mixed> $fields
 * @param int $timeout
 * @return array<string, mixed>
 */
function plugin_mastodon_http_request_multipart($method, $url, $headers, $fields, $timeout = 90) {
	$method = strtoupper((string) $method);
	$url = (string) $url;
	$headers = is_array($headers) ? $headers : array();
	$fields = is_array($fields) ? $fields : array();
	$timeout = max(30, (int) $timeout);
	plugin_mastodon_extend_time_limit(max(120, $timeout));

	if (isset($GLOBALS ['plugin_mastodon_test_http_requests']) && is_array($GLOBALS ['plugin_mastodon_test_http_requests'])) {
		$GLOBALS ['plugin_mastodon_test_http_requests'] [] = array(
			'method' => $method,
			'url' => $url,
			'headers' => $headers,
			'body' => '',
			'content_type' => 'multipart/form-data',
			'multipart' => $fields
		);
	}

	$testKey = $method . ' ' . $url;
	if (isset($GLOBALS ['plugin_mastodon_test_http_responses'] [$testKey])) {
		$mock = $GLOBALS ['plugin_mastodon_test_http_responses'] [$testKey];
		if (is_array($mock) && isset($mock [0]) && is_array($mock [0])) {
			$next = array_shift($mock);
			$GLOBALS ['plugin_mastodon_test_http_responses'] [$testKey] = $mock;
			$mock = $next;
		}
		return array(
			'ok' => !empty($mock ['ok']),
			'code' => isset($mock ['code']) ? (int) $mock ['code'] : 200,
			'headers' => isset($mock ['headers']) && is_array($mock ['headers']) ? $mock ['headers'] : array(),
			'body' => isset($mock ['body']) ? (string) $mock ['body'] : '',
			'error' => isset($mock ['error']) ? (string) $mock ['error'] : ''
		);
	}
	if (!empty($GLOBALS ['plugin_mastodon_test_http_no_network'])) {
		return array('ok' => false, 'code' => 599, 'headers' => array(), 'body' => '', 'error' => 'missing_test_http_response');
	}

	if (function_exists('curl_init')) {
		$payload = array();
		foreach ($fields as $name => $value) {
			if (is_array($value) && !empty($value ['__file_path'])) {
				if (function_exists('curl_file_create')) {
					$payload [$name] = curl_file_create($value ['__file_path'], isset($value ['__mime_type']) ? $value ['__mime_type'] : plugin_mastodon_media_guess_mime_type($value ['__file_path']), isset($value ['__file_name']) ? $value ['__file_name'] : basename($value ['__file_path']));
				} else {
					$payload [$name] = '@' . $value ['__file_path'];
				}
			} else {
				$payload [$name] = is_scalar($value) ? (string) $value : '';
			}
		}
		$responseHeaders = array();
		$ch = curl_init($url);
		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 5,
			CURLOPT_CONNECTTIMEOUT => 15,
			CURLOPT_TIMEOUT => $timeout,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POSTFIELDS => $payload,
			CURLOPT_HEADERFUNCTION => function ($curl, $headerLine) use (&$responseHeaders) {
				$len = strlen($headerLine);
				$headerLine = trim($headerLine);
				if ($headerLine !== '' && strpos($headerLine, ':') !== false) {
					list($name, $value) = explode(':', $headerLine, 2);
					$responseHeaders [strtolower(trim($name))] = trim($value);
				}
				return $len;
			},
			CURLOPT_USERAGENT => 'FlatPress-Mastodon/0.1'
		);
		curl_setopt_array($ch, $options);
		$responseBody = curl_exec($ch);
		$errorNo = curl_errno($ch);
		$error = curl_error($ch);
		$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (!is_php85_plus()) {
			curl_close($ch);
		}
		return array(
			'ok' => ($errorNo === 0 && $code >= 200 && $code < 300),
			'code' => $code,
			'headers' => $responseHeaders,
			'body' => is_string($responseBody) ? $responseBody : '',
			'error' => (string) $error
		);
	}

	$boundary = '----FlatPressMastodon' . md5(uniqid('', true));
	$body = '';
	foreach ($fields as $name => $value) {
		$body .= '--' . $boundary . "\r\n";
		if (is_array($value) && !empty($value ['__file_path'])) {
			$filePath = $value ['__file_path'];
			$fileName = isset($value ['__file_name']) ? $value ['__file_name'] : basename($filePath);
			$mimeType = isset($value ['__mime_type']) ? $value ['__mime_type'] : plugin_mastodon_media_guess_mime_type($filePath);
			$fileData = plugin_mastodon_io_read_file($filePath, plugin_mastodon_file_prestat($filePath));
			if (!is_string($fileData)) {
				return array('ok' => false, 'code' => 0, 'headers' => array(), 'body' => '', 'error' => 'Unable to read upload file');
			}
			$body .= 'Content-Disposition: form-data; name="' . addcslashes((string) $name, "\"\\") . '"; filename="' . addcslashes($fileName, "\"\\") . '"' . "\r\n";
			$body .= 'Content-Type: ' . $mimeType . "\r\n\r\n";
			$body .= $fileData . "\r\n";
		} else {
			$body .= 'Content-Disposition: form-data; name="' . addcslashes((string) $name, "\"\\") . '"' . "\r\n\r\n" . (is_scalar($value) ? (string) $value : '') . "\r\n";
		}
	}
	$body .= '--' . $boundary . "--\r\n";

	$headers [] = 'Content-Type: multipart/form-data; boundary=' . $boundary;
	$headers [] = 'Content-Length: ' . strlen($body);
	$headerString = implode("\r\n", $headers);
	$context = stream_context_create(array(
		'http' => array(
			'method' => $method,
			'timeout' => $timeout,
			'ignore_errors' => true,
			'header' => $headerString,
			'content' => $body
		)
	));
	return plugin_mastodon_stream_context_request($url, $context);
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
	$response = plugin_mastodon_mastodon_api($options, 'DELETE', '/api/v1/media/' . rawurlencode($mediaId), array(), true);
	if ((isset($response['code']) ? (int) $response['code'] : 0) === 404) {
		$response['ok'] = true;
		$response['error'] = '';
	}
	return $response;
}

/**
 * Best-effort cleanup for uploaded Mastodon media that never reached a final status request.
 * @param array<string, string> $options
 * @param array<int, string> $mediaIds
 * @return array{ok:bool, deleted:array<int, string>, failed:array<string, string>}
 */
function plugin_mastodon_cleanup_uploaded_media($options, $mediaIds) {
	$mediaIds = is_array($mediaIds) ? $mediaIds : array();
	$uniqueIds = array();
	foreach ($mediaIds as $mediaId) {
		$mediaId = trim((string) $mediaId);
		if ($mediaId === '' || isset($uniqueIds[$mediaId])) {
			continue;
		}
		$uniqueIds[$mediaId] = true;
	}
	$mediaIds = array_keys($uniqueIds);
	$deleted = array();
	$failed = array();
	foreach ($mediaIds as $mediaId) {
		plugin_mastodon_extend_time_limit(60);
		$response = plugin_mastodon_delete_media_attachment($options, $mediaId);
		if (!empty($response['ok'])) {
			$deleted[] = $mediaId;
			continue;
		}
		$failed[$mediaId] = plugin_mastodon_response_error_message($response);
		plugin_mastodon_log('Uploaded Mastodon media cleanup failed for ' . $mediaId . ': ' . $failed[$mediaId]);
	}
	return array(
		'ok' => empty($failed),
		'deleted' => $deleted,
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
		$stillProcessing = ($code === 202 || $code === 206)
			|| (!empty($response ['ok']) && empty($response ['json'] ['url']) && (!empty($response ['json'] ['preview_url']) || $hasKnownAttachment || $responseType === 'audio' || $responseType === 'video' || $responseType === 'gifv'));
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
		$response = plugin_mastodon_http_request_multipart('POST', plugin_mastodon_normalize_instance_url(isset($options ['instance_url']) ? $options ['instance_url'] : '') . '/api/v2/media', $headers, $fields, plugin_mastodon_media_transfer_timeout($mediaType, $fileSize));
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

/**
 * Collect entry files recursively from the FlatPress content tree.
 * @param string $dir
 * @param array<int, string> $files
 * @return void
 */
function plugin_mastodon_collect_entry_files($dir, &$files) {
	$items = @scandir($dir);
	if (!is_array($items)) {
		return;
	}
	foreach ($items as $item) {
		if ($item === '.' || $item === '..') {
			continue;
		}
		$path = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $item;
		if (is_dir($path)) {
			if (basename($path) === 'drafts' || basename($path) === 'static' || basename($path) === 'seometa') {
				continue;
			}
			plugin_mastodon_collect_entry_files($path, $files);
		} elseif (preg_match('/^entry\d{6}-\d{6}\.txt$/', $item)) {
			$files [] = $path;
		}
	}
}

/**
 * Resolve the best timestamp for a local FlatPress item.
 * @param array<string, mixed> $item
 * @param string $fallbackId
 * @return int
 */
function plugin_mastodon_local_item_timestamp($item, $fallbackId = '') {
	$item = is_array($item) ? $item : array();
	if (isset($item ['date']) && is_numeric($item ['date'])) {
		return (int) $item ['date'];
	}
	$fallbackId = trim((string) $fallbackId);
	if ($fallbackId !== '' && function_exists('date_from_id')) {
		$fallbackTimestamp = date_from_id($fallbackId);
		if (is_numeric($fallbackTimestamp)) {
			return (int) $fallbackTimestamp;
		}
	}
	return 0;
}

/**
 * Compare local FlatPress entries for Mastodon export order.
 *
 * Note: Mastodon sorts posts by the time they are created on Mastodon. Exporting older local
 * entries first ensures that newer local entries are posted later and therefore stay above older
 * ones in the Mastodon timeline after a batch synchronization.
 *
 * @param array{id:string, entry:array<string, mixed>, timestamp:int} $left
 * @param array{id:string, entry:array<string, mixed>, timestamp:int} $right
 * @return int
 */
function plugin_mastodon_compare_local_entries_for_export($left, $right) {
	$leftTimestamp = isset($left ['timestamp']) && is_numeric($left ['timestamp']) ? (int) $left ['timestamp'] : 0;
	$rightTimestamp = isset($right ['timestamp']) && is_numeric($right ['timestamp']) ? (int) $right ['timestamp'] : 0;
	if ($leftTimestamp < $rightTimestamp) {
		return -1;
	}
	if ($leftTimestamp > $rightTimestamp) {
		return 1;
	}
	$leftId = isset($left ['id']) ? (string) $left ['id'] : '';
	$rightId = isset($right ['id']) ? (string) $right ['id'] : '';
	return strcmp($leftId, $rightId);
}

/**
 * List local FlatPress entries ordered for export.
 * @return array<string, array<string, mixed>>
 */
function plugin_mastodon_list_local_entries() {
	$files = array();
	plugin_mastodon_collect_entry_files(CONTENT_DIR, $files);
	sort($files, SORT_STRING);
	$entryRecords = array();
	foreach ($files as $file) {
		$id = basename($file, EXT);
		$entry = entry_parse($id);
		if (!$entry || !is_array($entry)) {
			continue;
		}
		if (isset($entry ['categories']) && is_array($entry ['categories']) && in_array('draft', $entry ['categories'], true)) {
			continue;
		}
		$entryRecords [] = array(
			'id' => $id,
			'entry' => $entry,
			'timestamp' => plugin_mastodon_local_item_timestamp($entry, $id)
		);
	}
	usort($entryRecords, 'plugin_mastodon_compare_local_entries_for_export');
	$entries = array();
	foreach ($entryRecords as $entryRecord) {
		$entryId = isset($entryRecord ['id']) ? (string) $entryRecord ['id'] : '';
		if ($entryId === '') {
			continue;
		}
		$entries [$entryId] = isset($entryRecord ['entry']) && is_array($entryRecord ['entry']) ? $entryRecord ['entry'] : array();
	}
	return $entries;
}

/**
 * Parse raw HTTP response headers.
 * @param array<int, string> $rawHeaders
 * @return array{code:int, headers:array<string, string>}
 */
function plugin_mastodon_parse_http_response_headers($rawHeaders) {
	$responseHeaders = array();
	$code = 0;
	foreach ($rawHeaders as $line) {
		if (!is_string($line)) {
			continue;
		}
		if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $matches)) {
			$code = (int) $matches [1];
			continue;
		}
		if (strpos($line, ':') !== false) {
			list($name, $value) = explode(':', $line, 2);
			$responseHeaders [strtolower(trim($name))] = trim($value);
		}
	}
	return array(
		'code' => $code,
		'headers' => $responseHeaders
	);
}

/**
 * Perform an HTTP request through a stream context fallback.
 *
 * Note: This stream-based fallback avoids PHP 8.4 deprecations around locally scoped response headers.
 * @param string $url
 * @param mixed $context
 * @return array<string, mixed>
 */
function plugin_mastodon_stream_context_request($url, $context) {
	$rawHeaders = array();
	$responseBody = '';
	$error = '';

	$stream = @fopen($url, 'rb', false, $context);
	if (is_resource($stream)) {
		$meta = stream_get_meta_data($stream);
		if (isset($meta ['wrapper_data']) && is_array($meta ['wrapper_data'])) {
			$rawHeaders = $meta ['wrapper_data'];
		}
		$body = stream_get_contents($stream);
		if (is_string($body)) {
			$responseBody = $body;
		}
		fclose($stream);
	} else {
		$error = 'Unable to open HTTP stream';
		if (function_exists('http_get_last_response_headers')) {
			$headersFromRuntime = http_get_last_response_headers();
			$rawHeaders = is_array($headersFromRuntime) ? $headersFromRuntime : array();
		}
	}

	$parsedHeaders = plugin_mastodon_parse_http_response_headers($rawHeaders);
	return array(
		'ok' => ($parsedHeaders ['code'] >= 200 && $parsedHeaders ['code'] < 300),
		'code' => $parsedHeaders ['code'],
		'headers' => $parsedHeaders ['headers'],
		'body' => $responseBody,
		'error' => $error
	);
}

/**
 * Detect whether a value is a numerically indexed list.
 *
 * @param mixed $value Candidate value to inspect.
 * @return bool
 */
function plugin_mastodon_array_is_list($value) {
	if (!is_array($value)) {
		return false;
	}
	$expected = 0;
	foreach ($value as $key => $unused) {
		if ($key !== $expected) {
			return false;
		}
		$expected++;
	}
	return true;
}

/**
 * Detect whether a list contains only scalar-compatible form values.
 *
 * @param mixed $value Candidate value to inspect.
 * @return bool
 */
function plugin_mastodon_array_contains_only_form_scalars($value) {
	if (!is_array($value)) {
		return false;
	}
	foreach ($value as $item) {
		if (is_array($item) || is_object($item)) {
			return false;
		}
	}
	return true;
}

/**
 * Build an application/x-www-form-urlencoded query string for Mastodon requests.
 *
 * @param mixed $params Request parameters.
 * @return string
 */
function plugin_mastodon_http_build_query($params) {
	if (!is_array($params) || empty($params)) {
		return '';
	}

	$parts = array();
	$append = function ($key, $value) use (&$parts, &$append) {
		if (is_array($value)) {
			if (plugin_mastodon_array_is_list($value) && plugin_mastodon_array_contains_only_form_scalars($value)) {
				foreach ($value as $childValue) {
					$append($key . '[]', $childValue);
				}
				return;
			}
			if (plugin_mastodon_array_is_list($value)) {
				foreach ($value as $childValue) {
					if (is_array($childValue)) {
						foreach ($childValue as $childKey => $nestedValue) {
							$append($key . '[][' . (string) $childKey . ']', $nestedValue);
						}
					} else {
						$append($key . '[]', $childValue);
					}
				}
				return;
			}
			foreach ($value as $childKey => $childValue) {
				$append($key . '[' . (string) $childKey . ']', $childValue);
			}
			return;
		}
		if (is_bool($value)) {
			$value = $value ? '1' : '0';
		} elseif ($value === null) {
			$value = '';
		}
		$parts [] = rawurlencode((string) $key) . '=' . rawurlencode((string) $value);
	};

	foreach ($params as $key => $value) {
		$append((string) $key, $value);
	}

	return implode('&', $parts);
}

/**
 * Perform an HTTP request using cURL or the stream fallback.
 * @param string $method
 * @param string $url
 * @param array<int|string, string> $headers
 * @param string $body
 * @param string $contentType
 * @param int $timeout
 * @return array<string, mixed>
 */
function plugin_mastodon_http_request($method, $url, $headers, $body, $contentType, $timeout = 45) {
	$method = strtoupper((string) $method);
	$url = (string) $url;
	$headers = is_array($headers) ? $headers : array();
	$contentType = (string) $contentType;
	$body = ($body === null) ? '' : (string) $body;
	$timeout = max(15, (int) $timeout);
	plugin_mastodon_extend_time_limit(max(60, $timeout));

	if (isset($GLOBALS ['plugin_mastodon_test_http_requests']) && is_array($GLOBALS ['plugin_mastodon_test_http_requests'])) {
		$GLOBALS ['plugin_mastodon_test_http_requests'] [] = array(
			'method' => $method,
			'url' => $url,
			'headers' => $headers,
			'body' => $body,
			'content_type' => $contentType
		);
	}

	$testKey = $method . ' ' . $url;
	if (isset($GLOBALS ['plugin_mastodon_test_http_responses'] [$testKey])) {
		$mock = $GLOBALS ['plugin_mastodon_test_http_responses'] [$testKey];
		if (is_array($mock) && isset($mock [0]) && is_array($mock [0])) {
			$next = array_shift($mock);
			$GLOBALS ['plugin_mastodon_test_http_responses'] [$testKey] = $mock;
			$mock = $next;
		}
		return array(
			'ok' => !empty($mock ['ok']),
			'code' => isset($mock ['code']) ? (int) $mock ['code'] : 200,
			'headers' => isset($mock ['headers']) && is_array($mock ['headers']) ? $mock ['headers'] : array(),
			'body' => isset($mock ['body']) ? (string) $mock ['body'] : '',
			'error' => isset($mock ['error']) ? (string) $mock ['error'] : ''
		);
	}
	if (!empty($GLOBALS ['plugin_mastodon_test_http_no_network'])) {
		return array('ok' => false, 'code' => 599, 'headers' => array(), 'body' => '', 'error' => 'missing_test_http_response');
	}

	if ($contentType !== '') {
		$headers [] = 'Content-Type: ' . $contentType;
	}

	if (function_exists('curl_init')) {
		$responseHeaders = array();
		$ch = curl_init($url);
		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 5,
			CURLOPT_CONNECTTIMEOUT => 15,
			CURLOPT_TIMEOUT => $timeout,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_HEADERFUNCTION => function ($curl, $headerLine) use (&$responseHeaders) {
				$len = strlen($headerLine);
				$headerLine = trim($headerLine);
				if ($headerLine !== '' && strpos($headerLine, ':') !== false) {
					list($name, $value) = explode(':', $headerLine, 2);
					$responseHeaders [strtolower(trim($name))] = trim($value);
				}
				return $len;
			},
			CURLOPT_USERAGENT => 'FlatPress-Mastodon/0.1'
		);

		if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH' || $method === 'DELETE') {
			$options [CURLOPT_POSTFIELDS] = $body;
		}
		curl_setopt_array($ch, $options);
		$responseBody = curl_exec($ch);
		$errorNo = curl_errno($ch);
		$error = curl_error($ch);
		$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (!is_php85_plus()) {
			curl_close($ch);
		}
		return array(
			'ok' => ($errorNo === 0 && $code >= 200 && $code < 300),
			'code' => $code,
			'headers' => $responseHeaders,
			'body' => is_string($responseBody) ? $responseBody : '',
			'error' => (string) $error
		);
	}

	if (ini_get('allow_url_fopen')) {
		$headerString = implode("\r\n", $headers);
		$context = stream_context_create(array(
			'http' => array(
				'method' => $method,
				'timeout' => $timeout,
				'ignore_errors' => true,
				'header' => $headerString,
				'content' => $body
			)
		));
		return plugin_mastodon_stream_context_request($url, $context);
	}

	return array(
		'ok' => false,
		'code' => 0,
		'headers' => array(),
		'body' => '',
		'error' => 'No HTTP transport available'
	);
}

/**
 * Call the Mastodon API and return the raw HTTP response.
 * @param array<string, string> $options
 * @param string $method
 * @param string $path
 * @param array<string, mixed> $params
 * @param bool $auth
 * @return array<string, mixed>
 */
function plugin_mastodon_mastodon_api($options, $method, $path, $params, $auth) {
	$base = plugin_mastodon_normalize_instance_url(isset($options ['instance_url']) ? $options ['instance_url'] : '');
	if ($base === '') {
		return array('ok' => false, 'code' => 0, 'body' => '', 'headers' => array(), 'error' => 'Missing instance URL');
	}

	$url = $base . $path;
	$headers = array('Accept: application/json');
	$body = '';
	$contentType = 'application/x-www-form-urlencoded; charset=UTF-8';

	if ($auth && !empty($options ['access_token'])) {
		$headers [] = 'Authorization: Bearer ' . $options ['access_token'];
	}

	if (is_array($params) && !empty($params)) {
		if (strtoupper($method) === 'GET') {
			$url .= '?' . plugin_mastodon_http_build_query($params);
			$contentType = '';
		} else {
			$body = plugin_mastodon_http_build_query($params);
		}
	} else {
		if (strtoupper($method) === 'GET') {
			$contentType = '';
		}
	}

	$response = plugin_mastodon_http_request($method, $url, $headers, $body, $contentType);
	if (!$response ['ok']) {
		plugin_mastodon_log('HTTP ' . $method . ' ' . $url . ' failed: ' . $response ['code'] . ' ' . $response ['error'] . ' ' . plugin_mastodon_limit_text($response ['body'], 400));
	}
	return $response;
}

/**
 * Call the Mastodon API and decode a JSON response.
 * @param array<string, string> $options
 * @param string $method
 * @param string $path
 * @param array<string, mixed> $params
 * @param bool $auth
 * @return array<string, mixed>
 */
function plugin_mastodon_mastodon_json($options, $method, $path, $params, $auth) {
	$response = plugin_mastodon_mastodon_api($options, $method, $path, $params, $auth);
	$data = json_decode($response ['body'], true);
	if (!is_array($data)) {
		$data = array();
	}
	$response ['json'] = $data;
	return $response;
}

/**
 * Extract the most useful error message from an API response.
 * @param array<string, mixed> $response
 * @return string
 */
function plugin_mastodon_response_error_message($response) {
	$response = is_array($response) ? $response : array();
	if (!empty($response ['json'] ['error'])) {
		return trim((string) $response ['json'] ['error']);
	}
	if (!empty($response ['error'])) {
		return trim((string) $response ['error']);
	}
	if (!empty($response ['body']) && is_string($response ['body'])) {
		$decoded = json_decode($response ['body'], true);
		if (is_array($decoded) && !empty($decoded ['error'])) {
			return trim((string) $decoded ['error']);
		}
		$body = trim(strip_tags($response ['body']));
		if ($body !== '') {
			return $body;
		}
	}
	if (!empty($response ['code'])) {
		return 'HTTP ' . (int) $response ['code'];
	}
	return 'request_failed';
}

/**
 * Register the FlatPress application on the configured Mastodon instance.
 * @param array<string, string> $options
 * @return array<string, mixed>
 */
function plugin_mastodon_register_app(&$options) {
	$params = array(
		'client_name' => PLUGIN_MASTODON_APP_NAME,
		'redirect_uris' => 'urn:ietf:wg:oauth:2.0:oob',
		'scopes' => plugin_mastodon_oauth_preferred_scopes($options),
		'website' => defined('BLOG_BASEURL') ? BLOG_BASEURL : ''
	);
	$response = plugin_mastodon_mastodon_json($options, 'POST', '/api/v1/apps', $params, false);
	if (!empty($response ['json'] ['client_id']) && !empty($response ['json'] ['client_secret'])) {
		$options ['client_id'] = (string) $response ['json'] ['client_id'];
		$options ['client_secret'] = (string) $response ['json'] ['client_secret'];
		$options ['oauth_registered_scopes'] = plugin_mastodon_oauth_preferred_scopes($options);
		$options ['last_authorize_url'] = plugin_mastodon_build_authorize_url($options);
		plugin_mastodon_save_options($options);
	}
	return $response;
}

/**
 * Build the OAuth authorization URL.
 * @param array<string, string> $options
 * @return string
 */
function plugin_mastodon_build_authorize_url($options) {
	$base = plugin_mastodon_normalize_instance_url(isset($options ['instance_url']) ? $options ['instance_url'] : '');
	if ($base === '' || empty($options ['client_id'])) {
		return '';
	}
	$query = array(
		'client_id' => $options ['client_id'],
		'redirect_uri' => 'urn:ietf:wg:oauth:2.0:oob',
		'response_type' => 'code',
		'scope' => plugin_mastodon_oauth_scopes($options)
	);
	return $base . '/oauth/authorize?' . http_build_query($query, '', '&');
}

/**
 * Exchange an OAuth authorization code for an access token.
 * @param array<string, string> $options
 * @param string $code
 * @return array<string, mixed>
 */
function plugin_mastodon_exchange_code_for_token(&$options, $code) {
	$code = trim((string) $code);
	if ($code === '') {
		return array('ok' => false, 'code' => 0, 'json' => array(), 'body' => '', 'headers' => array(), 'error' => 'Missing authorization code');
	}
	$params = array(
		'grant_type' => 'authorization_code',
		'client_id' => isset($options ['client_id']) ? $options ['client_id'] : '',
		'client_secret' => isset($options ['client_secret']) ? $options ['client_secret'] : '',
		'redirect_uri' => 'urn:ietf:wg:oauth:2.0:oob',
		'code' => $code,
		'scope' => plugin_mastodon_oauth_scopes($options)
	);
	$response = plugin_mastodon_mastodon_json($options, 'POST', '/oauth/token', $params, false);
	if (!empty($response ['json'] ['access_token'])) {
		$options ['access_token'] = (string) $response ['json'] ['access_token'];
		$options ['authorization_code'] = '';
		if (empty($options ['oauth_registered_scopes'])) {
			$options ['oauth_registered_scopes'] = plugin_mastodon_oauth_scopes($options);
		}
		plugin_mastodon_save_options($options);
	}
	return $response;
}

/**
 * Verify the currently configured access token.
 * @param array<string, string> $options
 * @return array<string, mixed>
 */
function plugin_mastodon_verify_credentials($options) {
	$instanceUrl = plugin_mastodon_normalize_instance_url(isset($options ['instance_url']) ? $options ['instance_url'] : '');
	$accessToken = isset($options ['access_token']) ? (string) $options ['access_token'] : '';
	$cacheKey = sha1($instanceUrl . '|' . $accessToken);
	$cached = plugin_mastodon_runtime_cache_get('verify_credentials', $cacheKey, $hit);
	if ($hit && is_array($cached)) {
		return $cached;
	}
	$response = plugin_mastodon_mastodon_json($options, 'GET', '/api/v1/accounts/verify_credentials', array(), true);
	return plugin_mastodon_runtime_cache_set('verify_credentials', $cacheKey, $response);
}

/**
 * Return the status character limit of the configured instance.
 * @param array<string, string> $options
 * @return int
 */
function plugin_mastodon_instance_character_limit($options) {
	$configuration = plugin_mastodon_instance_configuration($options);
	if (!empty($configuration ['statuses'] ['max_characters'])) {
		return (int) $configuration ['statuses'] ['max_characters'];
	}
	return 500;
}

/**
 * Fetch statuses for the authenticated Mastodon account.
 * @param array<string, string> $options
 * @param string $accountId
 * @param string $sinceId
 * @return array<int, array<string, mixed>>
 */
function plugin_mastodon_fetch_account_statuses($options, $accountId, $sinceId) {
	$statuses = array();
	$params = array(
		'limit' => 40,
		'exclude_reblogs' => 'true',
		'exclude_replies' => 'true'
	);
	if ($sinceId !== '') {
		$params ['since_id'] = $sinceId;
	}

	$page = 0;
	$maxId = '';
	do {
		$page++;
		if ($maxId !== '') {
			$params ['max_id'] = $maxId;
		}
		$response = plugin_mastodon_mastodon_json($options, 'GET', '/api/v1/accounts/' . rawurlencode($accountId) . '/statuses', $params, true);
		if (!$response ['ok']) {
			break;
		}
		$pageItems = isset($response ['json']) && is_array($response ['json']) ? $response ['json'] : array();
		if (empty($pageItems)) {
			break;
		}
		foreach ($pageItems as $item) {
			if (is_array($item) && !empty($item ['id'])) {
				$statuses [] = $item;
			}
		}
		$lastItem = end($pageItems);
		if (!is_array($lastItem) || empty($lastItem ['id']) || $sinceId !== '') {
			break;
		}
		$maxId = (string) $lastItem ['id'];
	} while ($page < PLUGIN_MASTODON_MAX_STATUS_PAGES);

	return $statuses;
}

/**
 * Fetch the conversation context for a Mastodon status.
 * @param array<string, string> $options
 * @param string $statusId
 * @return array<string, mixed>
 */
function plugin_mastodon_fetch_status_context($options, $statusId) {
	$response = plugin_mastodon_mastodon_json($options, 'GET', '/api/v1/statuses/' . rawurlencode($statusId) . '/context', array(), true);
	return ($response ['ok'] && isset($response ['json'])) ? $response ['json'] : array();
}

/**
 * Fetch a single Mastodon status.
 * @param array<string, string> $options
 * @param string $statusId
 * @return array<string, mixed>
 */
function plugin_mastodon_fetch_status($options, $statusId) {
	return plugin_mastodon_mastodon_json($options, 'GET', '/api/v1/statuses/' . rawurlencode((string) $statusId), array(), true);
}

/**
 * Delete a Mastodon status.
 * @param array<string, string> $options
 * @param string $statusId
 * @param bool $deleteMedia
 * @return array<string, mixed>
 */
function plugin_mastodon_delete_status($options, $statusId, $deleteMedia = true) {
	$path = '/api/v1/statuses/' . rawurlencode((string) $statusId);
	if ($deleteMedia) {
		$path .= '?delete_media=1';
	}
	return plugin_mastodon_mastodon_json($options, 'DELETE', $path, array(), true);
}

/**
 * Check whether an API response means that the referenced Mastodon status is missing.
 * @param array<string, mixed> $response
 * @return bool
 */
function plugin_mastodon_status_missing_response($response) {
	$code = isset($response ['code']) ? (int) $response ['code'] : 0;
	return $code === 404 || $code === 410;
}

/**
 * Create a Mastodon status.
 * @param array<string, string> $options
 * @param string $text
 * @param string $inReplyToId
 * @param array<int, string> $mediaIds
 * @return array<string, mixed>
 */
function plugin_mastodon_create_status($options, $text, $inReplyToId, $mediaIds) {
	$params = array('status' => $text, 'visibility' => 'public');
	$mediaIds = is_array($mediaIds) ? array_values(array_filter($mediaIds, function ($mediaId) {
		return (string) $mediaId !== '';
	})) : array();
	$language = plugin_mastodon_configured_status_language();
	if ($inReplyToId !== '') {
		$params ['in_reply_to_id'] = $inReplyToId;
	}
	if ($language !== '') {
		$params ['language'] = $language;
	}
	if (!empty($mediaIds)) {
		$params ['media_ids'] = $mediaIds;
	}
	return plugin_mastodon_mastodon_json($options, 'POST', '/api/v1/statuses', $params, true);
}

/**
 * Update an existing Mastodon status.
 * @param array<string, string> $options
 * @param string $remoteId
 * @param string $text
 * @param array<int, string> $mediaIds
 * @param array<int, array<string, string>> $mediaAttributes
 * @return array<string, mixed>
 */
function plugin_mastodon_update_status($options, $remoteId, $text, $mediaIds, $mediaAttributes = array()) {
	$params = array('status' => $text);
	$mediaIds = is_array($mediaIds) ? array_values(array_filter($mediaIds, function ($mediaId) {
		return (string) $mediaId !== '';
	})) : array();
	$mediaAttributes = is_array($mediaAttributes) ? array_values($mediaAttributes) : array();
	$language = plugin_mastodon_configured_status_language();
	if ($language !== '') {
		$params ['language'] = $language;
	}
	if (!empty($mediaIds)) {
		$params ['media_ids'] = $mediaIds;
	}
	if (!empty($mediaAttributes)) {
		$params ['media_attributes'] = $mediaAttributes;
	}
	return plugin_mastodon_mastodon_json($options, 'PUT', '/api/v1/statuses/' . rawurlencode($remoteId), $params, true);
}

/**
 * Build the status body used when exporting a FlatPress entry.
 * @param string $entryId
 * @param array<string, mixed> $entry
 * @param int $charLimit
 * @return string
 */
function plugin_mastodon_build_entry_status_text($entryId, $entry, $charLimit) {
	$subject = plugin_mastodon_prepare_emoticons_for_mastodon(isset($entry ['subject']) ? trim((string) $entry ['subject']) : '');
	$content = isset($entry ['content']) ? trim((string) $entry ['content']) : '';
	$urlReservedLength = plugin_mastodon_instance_url_reserved_length(plugin_mastodon_get_options());
	$parts = array();

	if ($subject !== '') {
		$parts [] = $subject;
	}
	if ($content !== '') {
		$body = plugin_mastodon_flatpress_to_mastodon($content);
		if ($body !== '') {
			$parts [] = $body;
		}
	}

	$text = trim(implode("\n\n", $parts));
	$link = plugin_mastodon_public_url_for_mastodon(plugin_mastodon_public_entry_url($entryId, $entry));
	$hashtags = '';
	if (plugin_mastodon_tag_plugin_active()) {
		$hashtags = plugin_mastodon_mastodon_hashtag_footer(plugin_mastodon_extract_flatpress_tags($content));
	}

	if ($hashtags !== '') {
		$suffixParts = array();
		if ($link !== '') {
			$suffixParts [] = $link;
		}
		$suffixParts [] = $hashtags;
		$suffix = implode("\n", $suffixParts);
		$suffixLength = plugin_mastodon_status_text_length($suffix, $urlReservedLength);
		$separatorLength = $text !== '' ? 1 : 0;
		$available = $charLimit - $separatorLength - $suffixLength;
		if ($available >= 32) {
			$text = trim((string) plugin_mastodon_limit_status_text($text, $available, $urlReservedLength));
			$text = $text === '' ? $suffix : $text . "\n" . $suffix;
			return trim((string) $text);
		}

		$suffixLength = plugin_mastodon_status_text_length($hashtags, $urlReservedLength);
		$available = $charLimit - $separatorLength - $suffixLength;
		if ($available >= 32) {
			$text = trim((string) plugin_mastodon_limit_status_text($text, $available, $urlReservedLength));
			$text = $text === '' ? $hashtags : $text . "\n" . $hashtags;
			return trim((string) $text);
		}
	}

	if ($link !== '') {
		$available = $charLimit - 1 - plugin_mastodon_status_text_length($link, $urlReservedLength);
		if ($available < 32) {
			return trim((string) plugin_mastodon_limit_status_text($text, max(0, $charLimit), $urlReservedLength));
		}
		$text = plugin_mastodon_limit_status_text($text, $available, $urlReservedLength) . "\n" . $link;
		return trim((string) $text);
	}

	return trim((string) plugin_mastodon_limit_status_text($text, $charLimit, $urlReservedLength));
}

/**
 * Build the status body used when exporting a FlatPress comment.
 * @param string $entryId
 * @param array<string, mixed> $entry
 * @param array<string, mixed> $comment
 * @param int $charLimit
 * @return string
 */
function plugin_mastodon_build_comment_status_text($entryId, $entry, $comment, $charLimit) {
	$name = plugin_mastodon_prepare_emoticons_for_mastodon(isset($comment ['name']) ? trim((string) $comment ['name']) : '');
	$urlReservedLength = plugin_mastodon_instance_url_reserved_length(plugin_mastodon_get_options());
	$content = isset($comment ['content']) ? trim((string) $comment ['content']) : '';
	$commentId = isset($comment ['id']) ? trim((string) $comment ['id']) : '';
	$body = plugin_mastodon_flatpress_to_mastodon($content);
	$text = $body;

	if ($name !== '' && $name !== 'Anonymous' && $name !== 'Mastodon') {
		$format = plugin_mastodon_lang_string('comment_by_format', 'Comment by %s:');
		if (strpos($format, '%s') !== false) {
			$text = sprintf($format, $name) . "\n\n" . $body;
		} else {
			$text = rtrim($format) . ' ' . $name . "\n\n" . $body;
		}
	}

	$text = trim((string) plugin_mastodon_limit_status_text($text, $charLimit, $urlReservedLength));
	$link = !empty($comment ['public_url']) ? plugin_mastodon_public_url_for_mastodon((string) $comment ['public_url']) : plugin_mastodon_public_url_for_mastodon(plugin_mastodon_public_comment_url($entryId, $entry, $commentId));
	if ($link !== '') {
		$available = $charLimit - 1 - plugin_mastodon_status_text_length($link, $urlReservedLength);
		if ($available >= 32) {
			$text = trim((string) plugin_mastodon_limit_status_text($text, $available, $urlReservedLength));
			$text = $text === '' ? $link : $text . "\n" . $link;
			return trim((string) $text);
		}
	}

	return $text;
}

/**
 * Import a remote Mastodon status into FlatPress as an entry.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param array<string, mixed> $remoteStatus
 * @return bool|string|array<string, mixed>
 */
function plugin_mastodon_import_remote_entry(&$options, &$state, $remoteStatus) {
	$remoteId = isset($remoteStatus ['id']) ? (string) $remoteStatus ['id'] : '';
	if ($remoteId === '' || !plugin_mastodon_remote_status_is_importable($remoteStatus)) {
		return false;
	}
	$inReplyToRemoteId = isset($remoteStatus ['in_reply_to_id']) ? trim((string) $remoteStatus ['in_reply_to_id']) : '';
	if (!plugin_mastodon_should_import_synced_comments_as_entries($options) && isset($state ['comments_remote'] [$remoteId]) && is_array($state ['comments_remote'] [$remoteId])) {
		$commentRef = $state ['comments_remote'] [$remoteId];
		plugin_mastodon_log('Skipping remote status ' . $remoteId . ' as entry import because it already maps to local comment ' . (isset($commentRef ['entry_id']) ? (string) $commentRef ['entry_id'] : '') . '/' . (isset($commentRef ['comment_id']) ? (string) $commentRef ['comment_id'] : '') . ' and import_synced_comments_as_entries is disabled');
		return false;
	}
	if (!plugin_mastodon_should_import_synced_comments_as_entries($options) && $inReplyToRemoteId !== '') {
		plugin_mastodon_log('Skipping remote reply status ' . $remoteId . ' as entry import because import_synced_comments_as_entries is disabled; reply will be handled via thread context import when possible (parent remote id ' . $inReplyToRemoteId . ')');
		return false;
	}

	$content = plugin_mastodon_mastodon_html_to_flatpress(isset($remoteStatus ['content']) ? $remoteStatus ['content'] : '');
	$remoteTags = plugin_mastodon_remote_status_tags($remoteStatus);
	if (plugin_mastodon_tag_plugin_active()) {
		$content = plugin_mastodon_strip_trailing_mastodon_hashtag_footer($content, $remoteTags);
	}
	$mediaBbcode = plugin_mastodon_build_imported_media_bbcode($options, $remoteStatus);
	if ($mediaBbcode !== '') {
		$content = trim($content . $mediaBbcode);
	}
	$subject = plugin_mastodon_guess_subject($content);
	$author = '';
	if (!empty($remoteStatus ['account'] ['display_name'])) {
		$author = plugin_mastodon_html_entity_decode(strip_tags($remoteStatus ['account'] ['display_name']));
	}
	if ($author === '' && !empty($remoteStatus ['account'] ['acct'])) {
		$author = '@' . $remoteStatus ['account'] ['acct'];
	}
	if ($author === '') {
		$author = 'Mastodon';
	}

	$url = isset($remoteStatus ['url']) ? (string) $remoteStatus ['url'] : '';
	$footer = '';
	if ($url !== '') {
		$footer = "[url=" . $url . ']Mastodon[/url]';
	}
	$entryContent = trim((string) $content);
	if (plugin_mastodon_tag_plugin_active()) {
		$tagBbcode = plugin_mastodon_build_flatpress_tag_bbcode($remoteTags);
		if ($tagBbcode !== '') {
			$entryContent = $entryContent === '' ? $tagBbcode : $entryContent . "\n\n" . $tagBbcode;
		}
	}
	if ($footer !== '') {
		$entryContent = $entryContent === '' ? $footer : $entryContent . "\n" . $footer;
	}
	$entry = array(
		'version' => system_ver(),
		'subject' => $subject,
		'content' => $entryContent,
		'author' => $author,
		'date' => plugin_mastodon_remote_status_timestamp($remoteStatus)
	);
	$hash = plugin_mastodon_entry_hash($entry);
	$remoteUpdatedAt = isset($remoteStatus ['edited_at']) && $remoteStatus ['edited_at'] ? plugin_mastodon_parse_iso_datetime($remoteStatus ['edited_at']) : plugin_mastodon_parse_iso_datetime(isset($remoteStatus ['created_at']) ? $remoteStatus ['created_at'] : '');

	if (isset($state ['entries_remote'] [$remoteId])) {
		$localId = $state ['entries_remote'] [$remoteId];
		$currentMeta = plugin_mastodon_state_get_entry_meta($state, $localId);
		if (!empty($currentMeta ['hash']) && $currentMeta ['hash'] === $hash) {
			return $localId;
		}
		$localEntryFile = entry_exists($localId);
		if ($localEntryFile && !plugin_mastodon_should_update_local_from_remote($options)) {
			plugin_mastodon_log('Skipping remote update for existing local entry ' . $localId . ' because update_local_from_remote is disabled');
			return $localId;
		}
		$existing = entry_parse($localId);
		if (is_array($existing) && !empty($existing ['date'])) {
			$entry ['date'] = $existing ['date'];
		}
		$result = entry_save($entry, $localId);
		if (is_string($result) && $result !== '') {
			plugin_mastodon_state_set_entry_mapping($state, $result, $remoteId, 'remote', $hash, $url, $remoteUpdatedAt, plugin_mastodon_local_item_date_key($entry, $result), plugin_mastodon_remote_status_date_key($remoteStatus));
			$state ['content_stats'] ['updated_entries']++;
			return $result;
		}
		return false;
	}

	$result = entry_save($entry, null);
	if (is_string($result) && $result !== '') {
		plugin_mastodon_state_set_entry_mapping($state, $result, $remoteId, 'remote', $hash, $url, $remoteUpdatedAt, plugin_mastodon_local_item_date_key($entry, $result), plugin_mastodon_remote_status_date_key($remoteStatus));
		$state ['content_stats'] ['imported_entries']++;
		return $result;
	}
	return false;
}

/**
 * Build a readable account label for a Mastodon status author.
 * @param array<string, mixed> $status
 * @return string
 */
function plugin_mastodon_remote_status_author_label($status) {
	$displayName = '';
	if (!empty($status ['account'] ['display_name'])) {
		$displayName = plugin_mastodon_html_entity_decode(strip_tags((string) $status ['account'] ['display_name']));
		$displayName = trim((string) preg_replace('/\s+/u', ' ', $displayName));
	}
	$acct = '';
	if (!empty($status ['account'] ['acct'])) {
		$acct = trim((string) $status ['account'] ['acct']);
		if ($acct !== '' && strpos($acct, '@') !== 0) {
			$acct = '@' . $acct;
		}
	}
	if ($displayName !== '' && $acct !== '') {
		if (strcasecmp(ltrim($displayName, '@'), ltrim($acct, '@')) === 0) {
			return $acct;
		}
		return $displayName . ' (' . $acct . ')';
	}
	if ($displayName !== '') {
		return $displayName;
	}
	if ($acct !== '') {
		return $acct;
	}
	return 'Mastodon';
}

/**
 * Remove one leading BBCode quote block from imported comment text.
 * @param string $content
 * @return string
 */
function plugin_mastodon_strip_leading_quote_block($content) {
	$content = ltrim((string) $content);
	if ($content === '' || stripos($content, '[quote]') !== 0) {
		return trim((string) $content);
	}
	if (!preg_match_all('/\[(\/?)quote\]/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
		return trim((string) $content);
	}
	$depth = 0;
	foreach ($matches [0] as $match) {
		$token = strtolower((string) $match [0]);
		$offset = isset($match [1]) ? (int) $match [1] : 0;
		if ($token === '[quote]') {
			$depth++;
		} else {
			$depth--;
			if ($depth === 0) {
				$after = substr($content, $offset + strlen((string) $match [0]));
				return trim((string) $after);
			}
		}
	}
	return trim((string) $content);
}

/**
 * Resolve the author and body that should be quoted for an imported Mastodon reply.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $parentRemoteId
 * @param array<string, array<string, mixed>> $contextStatuses
 * @return array{author:string,content:string}
 */
function plugin_mastodon_imported_reply_quote_payload($state, $entryId, $parentRemoteId, $contextStatuses = array()) {
	$entryId = (string) $entryId;
	$parentRemoteId = (string) $parentRemoteId;
	if ($entryId === '' || $parentRemoteId === '') {
		return array('author' => '', 'content' => '');
	}

	$payload = array('author' => '', 'content' => '');
	if (isset($state ['comments_remote'] [$parentRemoteId]) && is_array($state ['comments_remote'] [$parentRemoteId])) {
		$ref = $state ['comments_remote'] [$parentRemoteId];
		if (!empty($ref ['entry_id']) && (string) $ref ['entry_id'] === $entryId && !empty($ref ['comment_id'])) {
			$parentComment = comment_parse($entryId, (string) $ref ['comment_id']);
			if (is_array($parentComment)) {
				$payload ['author'] = isset($parentComment ['name']) ? trim((string) $parentComment ['name']) : '';
				$payload ['content'] = plugin_mastodon_strip_leading_quote_block(isset($parentComment ['content']) ? (string) $parentComment ['content'] : '');
			}
			$parentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, (string) $ref ['comment_id']);
			$parentSource = !empty($parentMeta ['source']) ? strtolower((string) $parentMeta ['source']) : '';
			if ($parentSource !== 'local' && isset($contextStatuses [$parentRemoteId]) && is_array($contextStatuses [$parentRemoteId])) {
				$payload ['author'] = plugin_mastodon_remote_status_author_label($contextStatuses [$parentRemoteId]);
				$payload ['content'] = trim((string) plugin_mastodon_mastodon_html_to_flatpress(isset($contextStatuses [$parentRemoteId] ['content']) ? $contextStatuses [$parentRemoteId] ['content'] : ''));
			}
		}
	}

	if (($payload ['author'] === '' && $payload ['content'] === '') && isset($contextStatuses [$parentRemoteId]) && is_array($contextStatuses [$parentRemoteId])) {
		$payload ['author'] = plugin_mastodon_remote_status_author_label($contextStatuses [$parentRemoteId]);
		$payload ['content'] = trim((string) plugin_mastodon_mastodon_html_to_flatpress(isset($contextStatuses [$parentRemoteId] ['content']) ? $contextStatuses [$parentRemoteId] ['content'] : ''));
	}

	return $payload;
}

/**
 * Build an optional BBCode quote block for an imported Mastodon reply.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $parentCommentId
 * @param string $parentRemoteId
 * @param array<string, array<string, mixed>> $contextStatuses
 * @return string
 */
function plugin_mastodon_build_imported_reply_quote($options, $state, $entryId, $parentCommentId, $parentRemoteId, $contextStatuses = array()) {
	if (!plugin_mastodon_should_quote_imported_reply_parent($options)) {
		return '';
	}
	$entryId = (string) $entryId;
	$parentCommentId = trim((string) $parentCommentId);
	$parentRemoteId = trim((string) $parentRemoteId);
	if ($entryId === '' || $parentCommentId === '' || $parentRemoteId === '') {
		return '';
	}
	$payload = plugin_mastodon_imported_reply_quote_payload($state, $entryId, $parentRemoteId, $contextStatuses);
	$author = isset($payload ['author']) ? trim((string) $payload ['author']) : '';
	$content = isset($payload ['content']) ? trim((string) $payload ['content']) : '';
	if ($author === '' && $content === '') {
		return '';
	}
	$quoteLines = array();
	if ($author !== '') {
		$format = plugin_mastodon_lang_string('reply_quote_author_format', '%s wrote:');
		$quoteLines [] = strpos($format, '%s') !== false ? sprintf($format, $author) : rtrim((string) $format) . ' ' . $author;
	}
	if ($content !== '') {
		$quoteLines [] = $content;
	}
	$quoteBody = trim(implode("\n", $quoteLines));
	if ($quoteBody === '') {
		return '';
	}
	return "[quote]\n" . $quoteBody . "\n[/quote]";
}

/**
 * Import a remote Mastodon reply into FlatPress as a comment.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param array<string, mixed> $remoteComment
 * @param string $parentCommentId
 * @param string $inReplyToRemoteId
 * @param array<string, array<string, mixed>> $contextStatuses
 * @return bool|string|array<string, mixed>
 */
function plugin_mastodon_import_remote_comment(&$options, &$state, $entryId, $remoteComment, $parentCommentId = '', $inReplyToRemoteId = '', $contextStatuses = array()) {
	$remoteId = isset($remoteComment ['id']) ? (string) $remoteComment ['id'] : '';
	if ($remoteId === '' || $entryId === '' || !plugin_mastodon_remote_status_is_importable($remoteComment)) {
		return false;
	}
	if (plugin_mastodon_state_has_comment_tombstone($state, $remoteId)) {
		plugin_mastodon_log('Skipping remote reply ' . $remoteId . ' because it was deleted locally earlier and is protected by a tombstone');
		return false;
	}
	if ($inReplyToRemoteId !== '' && plugin_mastodon_state_has_comment_tombstone($state, $inReplyToRemoteId)) {
		plugin_mastodon_log('Skipping remote reply ' . $remoteId . ' because the parent reply ' . $inReplyToRemoteId . ' was deleted locally and must not be re-imported');
		return false;
	}
	$content = plugin_mastodon_mastodon_html_to_flatpress(isset($remoteComment ['content']) ? $remoteComment ['content'] : '');
	$quoteBlock = plugin_mastodon_build_imported_reply_quote($options, $state, $entryId, $parentCommentId, $inReplyToRemoteId, $contextStatuses);
	if ($quoteBlock !== '') {
		$content = trim($quoteBlock . ($content !== '' ? "\n\n" . ltrim((string) $content) : ''));
	}
	$name = '';
	if (!empty($remoteComment ['account'] ['display_name'])) {
		$name = plugin_mastodon_html_entity_decode(strip_tags($remoteComment ['account'] ['display_name']));
	}
	if ($name === '' && !empty($remoteComment ['account'] ['acct'])) {
		$name = '@' . $remoteComment ['account'] ['acct'];
	}
	if ($name === '') {
		$name = 'Mastodon';
	}
	$comment = array(
		'version' => system_ver(),
		'loggedin' => '0',
		'name' => $name,
		'url' => !empty($remoteComment ['account'] ['url']) ? (string) $remoteComment ['account'] ['url'] : '',
		'content' => $content,
		'date' => plugin_mastodon_remote_status_timestamp($remoteComment)
	);
	if ($parentCommentId !== '') {
		$comment ['replyto'] = $parentCommentId;
	}
	$hash = plugin_mastodon_comment_hash($comment);
	$remoteUpdatedAt = isset($remoteComment ['edited_at']) && $remoteComment ['edited_at'] ? plugin_mastodon_parse_iso_datetime($remoteComment ['edited_at']) : plugin_mastodon_parse_iso_datetime(isset($remoteComment ['created_at']) ? $remoteComment ['created_at'] : '');

	if (isset($state ['comments_remote'] [$remoteId]) && is_array($state ['comments_remote'] [$remoteId])) {
		$ref = $state ['comments_remote'] [$remoteId];
		$currentMeta = plugin_mastodon_state_get_comment_meta($state, $ref ['entry_id'], $ref ['comment_id']);
		if (!empty($currentMeta ['hash']) && $currentMeta ['hash'] === $hash && (empty($currentMeta ['parent_comment_id']) || $currentMeta ['parent_comment_id'] === (string) $parentCommentId) && (empty($currentMeta ['in_reply_to_remote_id']) || $currentMeta ['in_reply_to_remote_id'] === (string) $inReplyToRemoteId)) {
			return $ref ['comment_id'];
		}
		$file = comment_exists($ref ['entry_id'], $ref ['comment_id']);
		if ($file && !plugin_mastodon_should_update_local_from_remote($options)) {
			plugin_mastodon_log('Skipping remote update for existing local comment ' . $ref ['entry_id'] . '/' . $ref ['comment_id'] . ' because update_local_from_remote is disabled');
			return $ref ['comment_id'];
		}
		if ($file) {
			$existing = comment_parse($ref ['entry_id'], $ref ['comment_id']);
			if (is_array($existing) && !empty($existing ['date'])) {
				$comment ['date'] = $existing ['date'];
			}
			$stored = array_change_key_case($comment, CASE_UPPER);
			plugin_mastodon_io_write_file($file, utils_kimplode($stored));
			plugin_mastodon_state_set_comment_mapping($state, $ref ['entry_id'], $ref ['comment_id'], $remoteId, 'remote', $hash, isset($remoteComment ['url']) ? (string) $remoteComment ['url'] : '', $remoteUpdatedAt, $parentCommentId, $inReplyToRemoteId, plugin_mastodon_local_item_date_key($comment, $ref ['comment_id']), plugin_mastodon_remote_status_date_key($remoteComment));
			$state ['content_stats'] ['updated_local_comments']++;
			return $ref ['comment_id'];
		}
	}

	$result = comment_save($entryId, $comment);
	if (is_string($result) && $result !== '') {
		plugin_mastodon_state_set_comment_mapping($state, $entryId, $result, $remoteId, 'remote', $hash, isset($remoteComment ['url']) ? (string) $remoteComment ['url'] : '', $remoteUpdatedAt, $parentCommentId, $inReplyToRemoteId, plugin_mastodon_local_item_date_key($comment, $result), plugin_mastodon_remote_status_date_key($remoteComment));
		$state ['content_stats'] ['imported_comments']++;
		return $result;
	}
	return false;
}

/**
 * Import remote Mastodon replies from a fetched thread context.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $statusId
 * @param array<string, mixed> $context
 * @return void
 */
function plugin_mastodon_import_remote_context_descendants(&$options, &$state, $entryId, $statusId, $context) {
	$entryId = (string) $entryId;
	$statusId = (string) $statusId;
	if ($entryId === '' || $statusId === '' || empty($context ['descendants']) || !is_array($context ['descendants'])) {
		return;
	}

	$pending = array_values($context ['descendants']);
	$contextStatuses = array();
	foreach ($context ['descendants'] as $contextStatus) {
		if (is_array($contextStatus) && !empty($contextStatus ['id'])) {
			$contextStatuses [(string) $contextStatus ['id']] = $contextStatus;
		}
	}
	$importedRemoteIds = array();
	$blockedRemoteIds = array();
	$guard = 0;
	while (!empty($pending) && $guard < 50) {
		$guard++;
		$remaining = array();
		$progress = false;
		foreach ($pending as $descendant) {
			plugin_mastodon_extend_time_limit(120);
			if (!is_array($descendant) || empty($descendant ['id'])) {
				continue;
			}
			$descendantId = (string) $descendant ['id'];
			if (plugin_mastodon_state_has_comment_tombstone($state, $descendantId)) {
				$blockedRemoteIds [$descendantId] = true;
				$progress = true;
				plugin_mastodon_log('Skipping remote reply ' . $descendantId . ' because it was deleted locally earlier and is protected by a tombstone');
				continue;
			}
			if (!plugin_mastodon_remote_status_is_importable($descendant)) {
				$blockedRemoteIds [$descendantId] = true;
				$progress = true;
				plugin_mastodon_log('Skipping non-public remote reply ' . $descendantId . ' with visibility ' . plugin_mastodon_remote_status_visibility($descendant));
				continue;
			}
			if (!plugin_mastodon_remote_status_matches_sync_start($options, $descendant)) {
				$blockedRemoteIds [$descendantId] = true;
				$progress = true;
				plugin_mastodon_log('Skipping remote reply ' . $descendantId . ' because it is older than the configured sync start date');
				continue;
			}
			$parentRemoteId = isset($descendant ['in_reply_to_id']) ? (string) $descendant ['in_reply_to_id'] : '';
			if ($parentRemoteId !== '' && plugin_mastodon_state_has_comment_tombstone($state, $parentRemoteId)) {
				$blockedRemoteIds [$descendantId] = true;
				$progress = true;
				plugin_mastodon_log('Skipping remote reply ' . $descendantId . ' because the parent reply ' . $parentRemoteId . ' is protected by a tombstone');
				continue;
			}
			if ($parentRemoteId !== '' && isset($blockedRemoteIds [$parentRemoteId])) {
				$blockedRemoteIds [$descendantId] = true;
				$progress = true;
				plugin_mastodon_log('Skipping remote reply ' . $descendantId . ' because parent reply ' . $parentRemoteId . ' is not importable');
				continue;
			}
			$parentCommentId = '';
			$canImportNow = ($parentRemoteId === '' || $parentRemoteId === $statusId);
			if (!$canImportNow && isset($state ['comments_remote'] [$parentRemoteId]) && is_array($state ['comments_remote'] [$parentRemoteId])) {
				$parentRef = $state ['comments_remote'] [$parentRemoteId];
				if (!empty($parentRef ['entry_id']) && (string) $parentRef ['entry_id'] === $entryId && !empty($parentRef ['comment_id'])) {
					$parentCommentId = (string) $parentRef ['comment_id'];
					$canImportNow = true;
				}
			}
			if (!$canImportNow && isset($importedRemoteIds [$parentRemoteId])) {
				$parentCommentId = (string) $importedRemoteIds [$parentRemoteId];
				$canImportNow = true;
			}
			if (!$canImportNow) {
				$remaining [] = $descendant;
				continue;
			}
			$commentId = plugin_mastodon_import_remote_comment($options, $state, $entryId, $descendant, $parentCommentId, $parentRemoteId, $contextStatuses);
			if ($commentId) {
				$importedRemoteIds [$descendantId] = $commentId;
				$progress = true;
			} else {
				$remaining [] = $descendant;
			}
		}
		if (!$progress) {
			foreach ($remaining as $descendant) {
				if (empty($descendant ['id'])) {
					continue;
				}
				$parentRemoteId = isset($descendant ['in_reply_to_id']) ? (string) $descendant ['in_reply_to_id'] : '';
				plugin_mastodon_import_remote_comment($options, $state, $entryId, $descendant, '', $parentRemoteId, $contextStatuses);
			}
			$remaining = array();
		}
		$pending = $remaining;
	}
}

/**
 * Collect known synchronized entry threads that should have their Mastodon reply context refreshed.
 * @param array<string, mixed> $state
 * @param array<int, string> $skipRemoteIds
 * @return array<string, string>
 */
function plugin_mastodon_collect_known_entry_context_targets($state, $skipRemoteIds = array()) {
	$targets = array();
	$skipLookup = array();
	if (is_array($skipRemoteIds)) {
		foreach ($skipRemoteIds as $skipRemoteId) {
			$skipRemoteId = (string) $skipRemoteId;
			if ($skipRemoteId !== '') {
				$skipLookup [$skipRemoteId] = true;
			}
		}
	}
	if (empty($state ['entries']) || !is_array($state ['entries'])) {
		return $targets;
	}
	foreach ($state ['entries'] as $localEntryId => $meta) {
		$localEntryId = (string) $localEntryId;
		if ($localEntryId === '' || !is_array($meta) || empty($meta ['remote_id'])) {
			continue;
		}
		$remoteId = (string) $meta ['remote_id'];
		if ($remoteId === '' || isset($skipLookup [$remoteId]) || !entry_exists($localEntryId)) {
			continue;
		}
		$targets [$remoteId] = $localEntryId;
	}
	return $targets;
}

/**
 * Synchronize remote Mastodon content into FlatPress.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @return bool
 */
function plugin_mastodon_sync_remote_to_local(&$options, &$state) {
	plugin_mastodon_extend_time_limit(180);
	$verify = plugin_mastodon_verify_credentials($options);
	if (!$verify ['ok'] || empty($verify ['json'] ['id'])) {
		$state ['last_error'] = 'verify_credentials_failed';
		return false;
	}

	$accountId = (string) $verify ['json'] ['id'];
	$sinceId = isset($state ['last_remote_status_id']) ? (string) $state ['last_remote_status_id'] : '';
	$statuses = plugin_mastodon_fetch_account_statuses($options, $accountId, $sinceId);
	$refreshedContextIds = array();

	$maxRemoteId = $sinceId;
	foreach ($statuses as $status) {
		plugin_mastodon_extend_time_limit(120);
		if (!is_array($status) || empty($status ['id'])) {
			continue;
		}
		$statusId = (string) $status ['id'];
		if ($maxRemoteId === '' || strcmp($statusId, $maxRemoteId) > 0) {
			$maxRemoteId = $statusId;
		}
		if (!plugin_mastodon_remote_status_is_importable($status)) {
			plugin_mastodon_log('Skipping non-public remote status ' . $statusId . ' with visibility ' . plugin_mastodon_remote_status_visibility($status));
			continue;
		}
		if (!plugin_mastodon_remote_status_matches_sync_start($options, $status)) {
			plugin_mastodon_log('Skipping remote status ' . $statusId . ' because it is older than the configured sync start date');
			continue;
		}
		$entryId = plugin_mastodon_import_remote_entry($options, $state, $status);
		if (!$entryId) {
			continue;
		}
		$context = plugin_mastodon_fetch_status_context($options, $statusId);
		plugin_mastodon_import_remote_context_descendants($options, $state, $entryId, $statusId, $context);
		$refreshedContextIds [$statusId] = true;
	}

	$contextTargets = plugin_mastodon_collect_known_entry_context_targets($state, array_keys($refreshedContextIds));
	foreach ($contextTargets as $remoteEntryId => $localEntryId) {
		plugin_mastodon_extend_time_limit(120);
		$context = plugin_mastodon_fetch_status_context($options, $remoteEntryId);
		plugin_mastodon_import_remote_context_descendants($options, $state, $localEntryId, $remoteEntryId, $context);
	}

	if ($maxRemoteId !== '') {
		$state ['last_remote_status_id'] = $maxRemoteId;
	}
	return true;
}

/**
 * Synchronize local FlatPress content to Mastodon.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @return bool
 */
function plugin_mastodon_sync_local_to_remote(&$options, &$state) {
	plugin_mastodon_extend_time_limit(180);
	$charLimit = plugin_mastodon_instance_character_limit($options);
	$mediaLimit = plugin_mastodon_instance_media_limit($options);
	$entries = plugin_mastodon_list_local_entries();
	$hadFailure = false;
	foreach ($entries as $entryId => $entry) {
		plugin_mastodon_extend_time_limit(120);
		$meta = plugin_mastodon_state_get_entry_meta($state, $entryId);
		$entrySource = !empty($meta ['source']) ? strtolower((string) $meta ['source']) : 'local';
		$skipEntryStatusSync = ($entrySource === 'remote');
		$entryMatchesSyncStart = plugin_mastodon_local_item_matches_sync_start($options, $entry, $entryId);
		if ($skipEntryStatusSync) {
			plugin_mastodon_log('Skipping local entry status export for remote-sourced entry ' . $entryId . ' while still evaluating local FlatPress comments for reply export');
		} elseif ($entryMatchesSyncStart) {
			$hash = plugin_mastodon_entry_hash($entry);
			$text = plugin_mastodon_build_entry_status_text($entryId, $entry, $charLimit);
			if ($text === '') {
				continue;
			}

			$mediaItems = plugin_mastodon_collect_local_entry_media($entry);
			$mediaPlan = plugin_mastodon_prepare_entry_media_sync_plan($options, $meta, $mediaItems, $mediaLimit);
			$mediaIds = isset($mediaPlan ['media_ids']) && is_array($mediaPlan ['media_ids']) ? $mediaPlan ['media_ids'] : array();
			$mediaAttributes = isset($mediaPlan ['media_attributes']) && is_array($mediaPlan ['media_attributes']) ? $mediaPlan ['media_attributes'] : array();
			$effectiveMediaItems = isset($mediaPlan ['media_items']) && is_array($mediaPlan ['media_items']) ? $mediaPlan ['media_items'] : array();
			$mediaAttachmentSignature = isset($mediaPlan ['attachment_signature']) ? (string) $mediaPlan ['attachment_signature'] : '';
			$mediaDescriptionSignature = isset($mediaPlan ['description_signature']) ? (string) $mediaPlan ['description_signature'] : '';
			$resolvedRemoteMedia = isset($mediaPlan ['remote_media']) && is_array($mediaPlan ['remote_media']) ? $mediaPlan ['remote_media'] : array();
			$uploadedNewMedia = false;
			if (!empty($mediaPlan ['skipped'])) {
				plugin_mastodon_log('Skipped ' . (int) $mediaPlan ['skipped'] . ' local media attachment(s) because the Mastodon instance allows only ' . $mediaLimit . ' attachment(s) per status.');
			}

			if (($mediaPlan ['mode'] === 'upload') && !empty($effectiveMediaItems)) {
				$upload = plugin_mastodon_upload_media_items($options, $effectiveMediaItems, count($effectiveMediaItems));
				if (!$upload ['ok']) {
					$hadFailure = true;
					$state ['last_error'] = 'local_entry_media_upload_failed: ' . $entryId . ' (' . $upload ['error'] . ')';
					plugin_mastodon_log('Local entry media upload failed for ' . $entryId . ': ' . $upload ['error']);
					continue;
				}
				$mediaIds = isset($upload ['media_ids']) && is_array($upload ['media_ids']) ? $upload ['media_ids'] : array();
				$resolvedRemoteMedia = plugin_mastodon_remote_media_descriptors_from_media_ids($mediaIds, $effectiveMediaItems, $options);
				$uploadedNewMedia = !empty($mediaIds);
			}

			if (!empty($meta ['remote_id'])) {
				if (!empty($meta ['hash']) && $meta ['hash'] === $hash) {
					// no change
				} else {
					$updated = plugin_mastodon_update_status($options, $meta ['remote_id'], $text, $mediaIds, $mediaAttributes);
					if ($updated ['ok'] && !empty($updated ['json'] ['id'])) {
						plugin_mastodon_state_set_entry_mapping($state, $entryId, $meta ['remote_id'], 'local', $hash, isset($updated ['json'] ['url']) ? $updated ['json'] ['url'] : '', plugin_mastodon_parse_iso_datetime(isset($updated ['json'] ['edited_at']) ? $updated ['json'] ['edited_at'] : ''), plugin_mastodon_local_item_date_key($entry, $entryId), plugin_mastodon_remote_status_date_key(isset($updated ['json']) && is_array($updated ['json']) ? $updated ['json'] : array()));
						$updatedRemoteMedia = plugin_mastodon_remote_media_descriptors_from_status(isset($updated ['json']) && is_array($updated ['json']) ? $updated ['json'] : array());
						if (!empty($updatedRemoteMedia)) {
							$resolvedRemoteMedia = $updatedRemoteMedia;
						}
						plugin_mastodon_state_set_entry_media_meta($state, $entryId, $resolvedRemoteMedia, $mediaAttachmentSignature, $mediaDescriptionSignature);
						$state ['content_stats'] ['updated_remote_entries']++;
					} else {
						if ($uploadedNewMedia && !empty($mediaIds)) {
							plugin_mastodon_cleanup_uploaded_media($options, $mediaIds);
						}
						$hadFailure = true;
						$state ['last_error'] = 'local_entry_update_failed: ' . $entryId . ' (' . plugin_mastodon_response_error_message($updated) . ')';
						plugin_mastodon_log('Local entry update failed for ' . $entryId . ': ' . plugin_mastodon_response_error_message($updated));
						continue;
					}
				}
			} else {
				$created = plugin_mastodon_create_status($options, $text, '', $mediaIds);
				if ($created ['ok'] && !empty($created ['json'] ['id'])) {
					plugin_mastodon_state_set_entry_mapping($state, $entryId, $created ['json'] ['id'], 'local', $hash, isset($created ['json'] ['url']) ? $created ['json'] ['url'] : '', plugin_mastodon_parse_iso_datetime(isset($created ['json'] ['created_at']) ? $created ['json'] ['created_at'] : ''), plugin_mastodon_local_item_date_key($entry, $entryId), plugin_mastodon_remote_status_date_key(isset($created ['json']) && is_array($created ['json']) ? $created ['json'] : array()));
					$createdRemoteMedia = plugin_mastodon_remote_media_descriptors_from_status(isset($created ['json']) && is_array($created ['json']) ? $created ['json'] : array());
					if (!empty($createdRemoteMedia)) {
						$resolvedRemoteMedia = $createdRemoteMedia;
					}
					plugin_mastodon_state_set_entry_media_meta($state, $entryId, $resolvedRemoteMedia, $mediaAttachmentSignature, $mediaDescriptionSignature);
					$state ['content_stats'] ['exported_entries']++;
					$meta = plugin_mastodon_state_get_entry_meta($state, $entryId);
				} else {
					if ($uploadedNewMedia && !empty($mediaIds)) {
						plugin_mastodon_cleanup_uploaded_media($options, $mediaIds);
					}
					$hadFailure = true;
					$state ['last_error'] = 'local_entry_export_failed: ' . $entryId . ' (' . plugin_mastodon_response_error_message($created) . ')';
					plugin_mastodon_log('Local entry export failed for ' . $entryId . ': ' . plugin_mastodon_response_error_message($created));
					continue;
				}
			}
		} else {
			plugin_mastodon_log('Skipping local entry ' . $entryId . ' because it is older than the configured sync start date');
		}

		$entryMeta = plugin_mastodon_state_get_entry_meta($state, $entryId);
		$entryRemoteId = !empty($entryMeta ['remote_id']) ? $entryMeta ['remote_id'] : '';
		if ($entryRemoteId === '') {
			continue;
		}

		$commentIds = plugin_mastodon_list_local_comment_ids($entryId);
		$pendingComments = array();
		foreach ($commentIds as $commentId) {
			plugin_mastodon_extend_time_limit(120);
			$comment = comment_parse($entryId, $commentId);
			if (!$comment || !is_array($comment)) {
				continue;
			}
			$comment ['id'] = (string) $commentId;
			$comment ['public_url'] = plugin_mastodon_public_comment_url($entryId, $entry, $commentId);
			$commentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $commentId);
			if (!empty($commentMeta ['source']) && $commentMeta ['source'] === 'remote') {
				continue;
			}
			if (!plugin_mastodon_local_item_matches_sync_start($options, $comment, $commentId)) {
				plugin_mastodon_log('Skipping local comment ' . $entryId . '/' . $commentId . ' because it is older than the configured sync start date');
				continue;
			}
			$pendingComments [] = array(
				'comment_id' => (string) $commentId,
				'comment' => $comment
			);
		}
		$commentGuard = 0;
		while (!empty($pendingComments) && $commentGuard < 50) {
			$commentGuard++;
			$remainingComments = array();
			$processedComments = false;
			foreach ($pendingComments as $commentRecord) {
				plugin_mastodon_extend_time_limit(120);
				$commentId = isset($commentRecord ['comment_id']) ? (string) $commentRecord ['comment_id'] : '';
				$comment = isset($commentRecord ['comment']) && is_array($commentRecord ['comment']) ? $commentRecord ['comment'] : array();
				if ($commentId === '' || $comment === array()) {
					$processedComments = true;
					continue;
				}
				$parentCommentId = plugin_mastodon_detect_local_comment_parent_id($entryId, $comment);
				if ($parentCommentId !== '' && plugin_mastodon_local_comment_parent_export_pending($options, $state, $entryId, $parentCommentId)) {
					$remainingComments [] = $commentRecord;
					continue;
				}
				$processedComments = true;
				$commentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $commentId);
				$commentHash = plugin_mastodon_comment_hash($comment);
				$text = plugin_mastodon_build_comment_status_text($entryId, $entry, $comment, $charLimit);
				if ($text === '') {
					continue;
				}
				$replyTarget = plugin_mastodon_resolve_comment_reply_target($state, $entryId, $comment, $entryRemoteId);
				$replyToRemoteId = !empty($replyTarget ['remote_id']) ? (string) $replyTarget ['remote_id'] : $entryRemoteId;
				$parentCommentId = !empty($replyTarget ['parent_comment_id']) ? (string) $replyTarget ['parent_comment_id'] : '';
				if (!empty($commentMeta ['remote_id'])) {
					if (!empty($commentMeta ['hash']) && $commentMeta ['hash'] === $commentHash && ((isset($commentMeta ['parent_comment_id']) ? (string) $commentMeta ['parent_comment_id'] : '') === $parentCommentId) && ((isset($commentMeta ['in_reply_to_remote_id']) ? (string) $commentMeta ['in_reply_to_remote_id'] : '') === $replyToRemoteId)) {
						continue;
					}
					$updated = plugin_mastodon_update_status($options, $commentMeta ['remote_id'], $text, array());
					if ($updated ['ok'] && !empty($updated ['json'] ['id'])) {
						plugin_mastodon_state_set_comment_mapping($state, $entryId, $commentId, $commentMeta ['remote_id'], 'local', $commentHash, isset($updated ['json'] ['url']) ? $updated ['json'] ['url'] : '', plugin_mastodon_parse_iso_datetime(isset($updated ['json'] ['edited_at']) ? $updated ['json'] ['edited_at'] : ''), $parentCommentId, $replyToRemoteId, plugin_mastodon_local_item_date_key($comment, $commentId), plugin_mastodon_remote_status_date_key(isset($updated ['json']) && is_array($updated ['json']) ? $updated ['json'] : array()));
						$state ['content_stats'] ['updated_remote_comments']++;
					} else {
						$hadFailure = true;
						$state ['last_error'] = 'local_comment_update_failed: ' . $entryId . '/' . $commentId . ' (' . plugin_mastodon_response_error_message($updated) . ')';
						plugin_mastodon_log('Local comment update failed for ' . $entryId . '/' . $commentId . ': ' . plugin_mastodon_response_error_message($updated));
					}
				} else {
					$created = plugin_mastodon_create_status($options, $text, $replyToRemoteId, array());
					if ($created ['ok'] && !empty($created ['json'] ['id'])) {
						plugin_mastodon_state_set_comment_mapping($state, $entryId, $commentId, $created ['json'] ['id'], 'local', $commentHash, isset($created ['json'] ['url']) ? $created ['json'] ['url'] : '', plugin_mastodon_parse_iso_datetime(isset($created ['json'] ['created_at']) ? $created ['json'] ['created_at'] : ''), $parentCommentId, $replyToRemoteId, plugin_mastodon_local_item_date_key($comment, $commentId), plugin_mastodon_remote_status_date_key(isset($created ['json']) && is_array($created ['json']) ? $created ['json'] : array()));
						$state ['content_stats'] ['exported_comments']++;
					} else {
						$hadFailure = true;
						$state ['last_error'] = 'local_comment_export_failed: ' . $entryId . '/' . $commentId . ' (' . plugin_mastodon_response_error_message($created) . ')';
						plugin_mastodon_log('Local comment export failed for ' . $entryId . '/' . $commentId . ': ' . plugin_mastodon_response_error_message($created));
					}
				}
			}
			if (!$processedComments) {
				break;
			}
			$pendingComments = $remainingComments;
		}
		if (!empty($pendingComments)) {
			foreach ($pendingComments as $commentRecord) {
				$commentId = isset($commentRecord ['comment_id']) ? (string) $commentRecord ['comment_id'] : '';
				$parentCommentId = plugin_mastodon_detect_local_comment_parent_id($entryId, isset($commentRecord ['comment']) && is_array($commentRecord ['comment']) ? $commentRecord ['comment'] : array());
				plugin_mastodon_log('Deferred local comment export for ' . $entryId . '/' . $commentId . ' because parent comment ' . $parentCommentId . ' is not synchronized yet');
			}
		}
	}

	return !$hadFailure;
}

/**
 * Run the deletion synchronization in a follow-up request after content sync completed.
 * @param bool $force
 * @return array<string, mixed>
 */
function plugin_mastodon_run_deletion_sync($force) {
	plugin_mastodon_extend_time_limit(180);
	$options = plugin_mastodon_get_options();
	$state = plugin_mastodon_state_read();

	if ($options ['instance_url'] === '') {
		$state ['last_error'] = 'missing_instance_url';
		plugin_mastodon_state_write($state);
		return array('ok' => false, 'state' => $state, 'message' => 'missing_instance_url');
	}
	if ($options ['access_token'] === '') {
		$state ['last_error'] = 'missing_access_token';
		plugin_mastodon_state_write($state);
		return array('ok' => false, 'state' => $state, 'message' => 'missing_access_token');
	}
	if (!plugin_mastodon_should_run_deletion_sync($options)) {
		plugin_mastodon_state_set_deletions_pending($state, false, 'full');
		$state ['pending_comment_remote_rechecks'] = array();
		$state ['last_error'] = '';
		plugin_mastodon_state_write($state);
		return array('ok' => true, 'state' => $state, 'message' => 'deletion_sync_disabled');
	}
	if (!$force && empty($state ['deletions_pending'])) {
		return array('ok' => true, 'state' => $state, 'message' => 'no_deletions_pending');
	}

	plugin_mastodon_ensure_state_dir();
	$lockHandle = @fopen(PLUGIN_MASTODON_LOCK_FILE, 'c+');
	if (!$lockHandle) {
		$state ['last_error'] = 'lock_open_failed';
		plugin_mastodon_state_write($state);
		return array('ok' => false, 'state' => $state, 'message' => 'lock_open_failed');
	}
	if (!@flock($lockHandle, LOCK_EX | LOCK_NB)) {
		return array('ok' => true, 'state' => $state, 'message' => 'sync_locked');
	}

	$state ['last_error'] = '';
	$state ['deletion_stats'] = plugin_mastodon_default_state() ['deletion_stats'];
	$hadFailure = false;
	$childIndex = plugin_mastodon_build_comment_remote_child_index($state);
	$commentRecheckOnly = plugin_mastodon_state_has_comment_recheck_scope($state) && !empty($state ['pending_comment_remote_rechecks']);

	if (!$commentRecheckOnly) {
		$entryMappings = isset($state ['entries']) && is_array($state ['entries']) ? array_keys($state ['entries']) : array();
		foreach ($entryMappings as $localEntryId) {
			plugin_mastodon_extend_time_limit(120);
			$localEntryId = (string) $localEntryId;
			$meta = plugin_mastodon_state_get_entry_meta($state, $localEntryId);
			if (empty($meta ['remote_id'])) {
				plugin_mastodon_state_remove_entry_mapping($state, $localEntryId);
				continue;
			}
			if (!plugin_mastodon_mapping_matches_sync_start($options, $meta, $localEntryId)) {
				plugin_mastodon_log('Skipping deletion sync for entry ' . $localEntryId . ' because it is older than the configured sync start date');
				continue;
			}
			$remoteId = (string) $meta ['remote_id'];
			$localExists = (bool) entry_exists($localEntryId);
			if (!$localExists) {
				$deleted = plugin_mastodon_delete_status($options, $remoteId, true);
				if (!empty($deleted ['ok']) || plugin_mastodon_status_missing_response($deleted)) {
					plugin_mastodon_state_remove_entry_mapping($state, $localEntryId);
					$state ['deletion_stats'] ['deleted_remote_entries']++;
					continue;
				}
				$hadFailure = true;
				$state ['last_error'] = 'entry_remote_delete_failed: ' . $localEntryId . ' (' . plugin_mastodon_response_error_message($deleted) . ')';
				plugin_mastodon_log('Deletion sync failed to delete remote status for local entry ' . $localEntryId . ': ' . plugin_mastodon_response_error_message($deleted));
				continue;
			}

			$remote = plugin_mastodon_fetch_status($options, $remoteId);
			if (plugin_mastodon_status_missing_response($remote)) {
				if (entry_exists($localEntryId)) {
					entry_delete($localEntryId);
				}
				plugin_mastodon_state_remove_entry_mapping($state, $localEntryId);
				$state ['deletion_stats'] ['deleted_local_entries']++;
				continue;
			}
			if (empty($remote ['ok'])) {
				$hadFailure = true;
				$state ['last_error'] = 'entry_remote_lookup_failed: ' . $localEntryId . ' (' . plugin_mastodon_response_error_message($remote) . ')';
				plugin_mastodon_log('Deletion sync failed to fetch remote status for local entry ' . $localEntryId . ': ' . plugin_mastodon_response_error_message($remote));
			}
		}

		$commentMappings = isset($state ['comments']) && is_array($state ['comments']) ? array_keys($state ['comments']) : array();
		foreach ($commentMappings as $commentKey) {
			plugin_mastodon_extend_time_limit(120);
			$commentKey = (string) $commentKey;
			$meta = isset($state ['comments'] [$commentKey]) && is_array($state ['comments'] [$commentKey]) ? $state ['comments'] [$commentKey] : array();
			if (empty($meta ['entry_id']) || empty($meta ['comment_id'])) {
				if ($commentKey !== '' && isset($state ['pending_comment_remote_rechecks'] [$commentKey])) {
					unset($state ['pending_comment_remote_rechecks'] [$commentKey]);
				}
				continue;
			}
			$entryId = (string) $meta ['entry_id'];
			$commentId = (string) $meta ['comment_id'];
			$commentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $commentId);
			if (empty($commentMeta ['remote_id'])) {
				plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
				plugin_mastodon_state_remove_comment_mapping($state, $entryId, $commentId);
				continue;
			}
			if (!plugin_mastodon_mapping_matches_sync_start($options, $commentMeta, $commentId)) {
				plugin_mastodon_log('Skipping deletion sync for comment ' . $entryId . '/' . $commentId . ' because it is older than the configured sync start date');
				plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
				continue;
			}
			if (plugin_mastodon_state_get_pending_comment_remote_recheck($state, $entryId, $commentId) !== array()) {
				continue;
			}
			$remoteId = (string) $commentMeta ['remote_id'];
			$localExists = (bool) comment_exists($entryId, $commentId);
			if (!$localExists) {
				$deleted = plugin_mastodon_delete_status($options, $remoteId, true);
				if (!empty($deleted ['ok']) || plugin_mastodon_status_missing_response($deleted)) {
					$queuedDescendants = plugin_mastodon_queue_comment_descendant_remote_rechecks($state, $childIndex, $remoteId);
					plugin_mastodon_state_set_comment_tombstone($state, $remoteId, $entryId, $commentId, 'local_deleted_remote_deleted');
					plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
					plugin_mastodon_state_remove_comment_mapping($state, $entryId, $commentId);
					$state ['deletion_stats'] ['deleted_remote_comments']++;
					if ($queuedDescendants > 0) {
						plugin_mastodon_log('Queued ' . $queuedDescendants . ' direct local descendant comment(s) for follow-up verification after deleting remote comment ' . $remoteId);
					}
					continue;
				}
				$hadFailure = true;
				$state ['last_error'] = 'comment_remote_delete_failed: ' . $entryId . '/' . $commentId . ' (' . plugin_mastodon_response_error_message($deleted) . ')';
				plugin_mastodon_log('Deletion sync failed to delete remote status for local comment ' . $entryId . '/' . $commentId . ': ' . plugin_mastodon_response_error_message($deleted));
				continue;
			}

			$remote = plugin_mastodon_fetch_status($options, $remoteId);
			if (plugin_mastodon_status_missing_response($remote)) {
				$queuedDescendants = plugin_mastodon_queue_comment_descendant_remote_rechecks($state, $childIndex, $remoteId);
				if (comment_exists($entryId, $commentId)) {
					comment_delete($entryId, $commentId);
				}
				plugin_mastodon_state_set_comment_tombstone($state, $remoteId, $entryId, $commentId, 'remote_missing_local_deleted');
				plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
				plugin_mastodon_state_remove_comment_mapping($state, $entryId, $commentId);
				$state ['deletion_stats'] ['deleted_local_comments']++;
				if ($queuedDescendants > 0) {
					plugin_mastodon_log('Queued ' . $queuedDescendants . ' direct local descendant comment(s) for follow-up verification after remote comment ' . $remoteId . ' disappeared');
				}
				continue;
			}
			if (empty($remote ['ok'])) {
				$hadFailure = true;
				$state ['last_error'] = 'comment_remote_lookup_failed: ' . $entryId . '/' . $commentId . ' (' . plugin_mastodon_response_error_message($remote) . ')';
				plugin_mastodon_log('Deletion sync failed to fetch remote status for local comment ' . $entryId . '/' . $commentId . ': ' . plugin_mastodon_response_error_message($remote));
			}
		}
	} else {
		plugin_mastodon_log('Deletion synchronization is running in targeted descendant recheck mode');
	}

	$pendingCommentRechecksRemaining = plugin_mastodon_process_pending_comment_remote_rechecks($options, $state, $childIndex, $hadFailure);

	if (!$hadFailure) {
		$state ['last_deletion_run'] = date('Y-m-d H:i:s');
		plugin_mastodon_state_set_deletions_pending($state, $pendingCommentRechecksRemaining, $pendingCommentRechecksRemaining ? 'comment_rechecks' : 'full');
		$state ['last_error'] = '';
		if ($pendingCommentRechecksRemaining) {
			plugin_mastodon_log('Deletion synchronization completed with pending descendant reply rechecks');
		} else {
			plugin_mastodon_log('Deletion synchronization completed successfully');
		}
	} else {
		if ($pendingCommentRechecksRemaining) {
			plugin_mastodon_state_set_deletions_pending($state, true, 'comment_rechecks');
		} else {
			plugin_mastodon_state_set_deletions_pending($state, true, $commentRecheckOnly ? 'comment_rechecks' : 'full');
		}
		if ($state ['last_error'] === '') {
			$state ['last_error'] = 'deletion_sync_failed';
		}
		plugin_mastodon_log('Deletion synchronization failed');
	}

	plugin_mastodon_state_write($state);
	@flock($lockHandle, LOCK_UN);
	@fclose($lockHandle);

	return array('ok' => !$hadFailure, 'state' => $state, 'message' => $state ['last_error'] === '' ? 'ok' : $state ['last_error']);
}

/**
 * Determine whether the scheduled synchronization is currently due.

 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param int $timestamp
 * @return bool
 */
function plugin_mastodon_sync_due($options, $state, $timestamp) {
	$timestamp = (int) $timestamp;
	if (empty($state ['last_run'])) {
		return true;
	}
	$syncTime = plugin_mastodon_normalize_sync_time(isset($options ['sync_time']) ? $options ['sync_time'] : '');
	$target = strtotime(date('Y-m-d', $timestamp) . ' ' . $syncTime . ':00');
	if ($target === false || $timestamp < $target) {
		return false;
	}
	$lastRun = strtotime((string) $state ['last_run']);
	if ($lastRun === false) {
		return true;
	}
	return date('Y-m-d', $lastRun) !== date('Y-m-d', $timestamp);
}

/**
 * Run a full synchronization cycle.
 * @param bool $force
 * @return array<string, mixed>
 */
function plugin_mastodon_run_sync($force) {
	plugin_mastodon_extend_time_limit(180);
	$options = plugin_mastodon_get_options();
	$state = plugin_mastodon_state_read();

	if ($options ['instance_url'] === '') {
		$state ['last_error'] = 'missing_instance_url';
		plugin_mastodon_state_write($state);
		return array('ok' => false, 'state' => $state, 'message' => 'missing_instance_url');
	}
	if ($options ['access_token'] === '') {
		$state ['last_error'] = 'missing_access_token';
		plugin_mastodon_state_write($state);
		return array('ok' => false, 'state' => $state, 'message' => 'missing_access_token');
	}
	if (!$force && !plugin_mastodon_sync_due($options, $state, time())) {
		return array('ok' => true, 'state' => $state, 'message' => 'not_due');
	}

	plugin_mastodon_ensure_state_dir();
	$lockHandle = @fopen(PLUGIN_MASTODON_LOCK_FILE, 'c+');
	if (!$lockHandle) {
		$state ['last_error'] = 'lock_open_failed';
		plugin_mastodon_state_write($state);
		return array('ok' => false, 'state' => $state, 'message' => 'lock_open_failed');
	}
	if (!@flock($lockHandle, LOCK_EX | LOCK_NB)) {
		return array('ok' => true, 'state' => $state, 'message' => 'sync_locked');
	}

	$state ['last_error'] = '';
	$state ['content_stats'] = plugin_mastodon_default_state() ['content_stats'];

	$protectedDeletedExportedComments = plugin_mastodon_protect_locally_deleted_exported_comments($options, $state);
	if ($protectedDeletedExportedComments > 0) {
		plugin_mastodon_log('Protected ' . $protectedDeletedExportedComments . ' locally deleted exported FlatPress comment mapping(s) from stale Mastodon context re-imports before content synchronization');
	}

	$okRemote = plugin_mastodon_sync_remote_to_local($options, $state);
	$okLocal = false;
	if ($okRemote) {
		$okLocal = plugin_mastodon_sync_local_to_remote($options, $state);
	}

	if ($okRemote && $okLocal) {
		$state ['last_run'] = date('Y-m-d H:i:s');
		plugin_mastodon_state_set_deletions_pending($state, plugin_mastodon_should_run_deletion_sync($options), 'full');
		$state ['last_error'] = '';
		plugin_mastodon_log('Synchronization completed successfully');
	} elseif ($state ['last_error'] === '') {
		$state ['last_error'] = 'sync_failed';
		plugin_mastodon_log('Synchronization failed');
	}

	plugin_mastodon_state_write($state);
	@flock($lockHandle, LOCK_UN);
	@fclose($lockHandle);

	return array('ok' => ($okRemote && $okLocal), 'state' => $state, 'message' => $state ['last_error'] === '' ? 'ok' : $state ['last_error']);
}

/**
 * Run the scheduled synchronization when the current request is due.
 * @return void
 */
function plugin_mastodon_maybe_sync() {
	if (PHP_SAPI === 'cli') {
		return;
	}
	if (isset($_SERVER ['REQUEST_METHOD']) && strtoupper($_SERVER ['REQUEST_METHOD']) === 'POST') {
		return;
	}

	$options = plugin_mastodon_get_options();
	$state = plugin_mastodon_state_read();
	if ($options ['instance_url'] === '' || $options ['access_token'] === '') {
		return;
	}
	if (plugin_mastodon_sync_due($options, $state, time())) {
		plugin_mastodon_run_sync(false);
		return;
	}
	if (!empty($state ['deletions_pending']) && plugin_mastodon_should_run_deletion_sync($options)) {
		plugin_mastodon_run_deletion_sync(false);
	}
}
add_action('init', 'plugin_mastodon_maybe_sync', 20);

/**
 * Return a localized yes/no/unknown label for admin diagnostics.
 * @param mixed $value
 * @return string
 */
function plugin_mastodon_admin_boolean_label($value) {
	if ($value === null || $value === '') {
		return plugin_mastodon_lang_string('bool_unknown', 'Unknown');
	}
	return !empty($value) ? plugin_mastodon_lang_string('bool_yes', 'Yes') : plugin_mastodon_lang_string('bool_no', 'No');
}

/**
 * Add one admin diagnostics row when the value is available.
 * @param array<int, array<string, string>> $rows
 * @param string $label
 * @param string $value
 * @param string $url
 * @return array<int, array<string, string>>
 */
function plugin_mastodon_admin_add_info_row($rows, $label, $value, $url = '') {
	$label = trim((string) $label);
	$value = trim((string) $value);
	$url = trim((string) $url);
	if ($label === '' || $value === '') {
		return is_array($rows) ? $rows : array();
	}
	$rows = is_array($rows) ? $rows : array();
	$rows [] = array(
		'label' => $label,
		'value' => $value,
		'url' => $url
	);
	return $rows;
}

/**
 * Summarize the registration state advertised by the Mastodon instance.
 * @param array<string, mixed> $document
 * @return string
 */
function plugin_mastodon_instance_registration_summary($document) {
	$registrations = (!empty($document ['registrations']) && is_array($document ['registrations'])) ? $document ['registrations'] : array();
	if ($registrations === array()) {
		return plugin_mastodon_lang_string('bool_unknown', 'Unknown');
	}
	$parts = array();
	if (array_key_exists('enabled', $registrations)) {
		$parts [] = !empty($registrations ['enabled']) ? plugin_mastodon_lang_string('instance_info_registrations_open', 'Open') : plugin_mastodon_lang_string('instance_info_registrations_closed', 'Closed');
	}
	if (!empty($registrations ['approval_required'])) {
		$parts [] = plugin_mastodon_lang_string('instance_info_registrations_approval_required', 'Approval required');
	}
	if (isset($registrations ['min_age']) && $registrations ['min_age'] !== '') {
		$parts [] = sprintf(plugin_mastodon_lang_string('instance_info_registrations_min_age', 'Minimum age: %d'), (int) $registrations ['min_age']);
	}
	if (array_key_exists('reason_required', $registrations) && !empty($registrations ['reason_required'])) {
		$parts [] = plugin_mastodon_lang_string('instance_info_registrations_reason_required', 'Reason required');
	}
	return empty($parts) ? plugin_mastodon_lang_string('bool_unknown', 'Unknown') : implode(' · ', $parts);
}

/**
 * Build the rows for the admin instance-information diagnostics table.
 * @param array<string, string> $options
 * @return array<int, array<string, string>>
 */
function plugin_mastodon_admin_instance_info_rows($options) {
	$options = is_array($options) ? $options : array();
	$document = plugin_mastodon_instance_document($options, false);
	$rows = array();
	$rows = plugin_mastodon_admin_add_info_row(
		$rows,
		plugin_mastodon_lang_string('instance_info_cache_state', 'Cache state'),
		$document !== array() ? plugin_mastodon_lang_string('instance_info_cache_state_cached', 'Saved snapshot available') : plugin_mastodon_lang_string('instance_info_cache_state_missing', 'No saved snapshot available')
	);
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_last_refresh', 'Last refresh'), isset($options ['instance_info_fetched_at']) ? (string) $options ['instance_info_fetched_at'] : '');
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_last_error', 'Last refresh error'), isset($options ['instance_info_error']) ? (string) $options ['instance_info_error'] : '');

	if ($document === array()) {
		return $rows;
	}

	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_domain', 'Domain'), isset($document ['domain']) ? (string) $document ['domain'] : '');
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_title', 'Title'), isset($document ['title']) ? (string) $document ['title'] : '');
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_version', 'Exact version'), isset($document ['version']) ? (string) $document ['version'] : '');
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_api_version', 'API compatibility version'), isset($document ['api_versions'] ['mastodon']) ? (string) $document ['api_versions'] ['mastodon'] : '');
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_description', 'Description'), isset($document ['description']) ? (string) $document ['description'] : '');
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_source_url', 'Source URL'), isset($document ['source_url']) ? (string) $document ['source_url'] : '', isset($document ['source_url']) ? (string) $document ['source_url'] : '');

	if (!empty($document ['languages']) && is_array($document ['languages'])) {
		$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_languages', 'Languages'), implode(', ', $document ['languages']));
	}
	if (isset($document ['usage'] ['users'] ['active_month'])) {
		$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_active_month', 'Active users (4 weeks)'), (string) (int) $document ['usage'] ['users'] ['active_month']);
	}
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_registrations', 'Registrations'), plugin_mastodon_instance_registration_summary($document));
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_registration_url', 'Registration URL'), !empty($document ['registrations'] ['url']) ? (string) $document ['registrations'] ['url'] : '', !empty($document ['registrations'] ['url']) ? (string) $document ['registrations'] ['url'] : '');
	if (isset($document ['rules_count'])) {
		$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_rules', 'Published rules'), (string) (int) $document ['rules_count']);
	}
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_contact_email', 'Contact email'), !empty($document ['contact'] ['email']) ? (string) $document ['contact'] ['email'] : '');
	$contactAccountValue = '';
	$contactAccountUrl = '';
	if (!empty($document ['contact'] ['account']) && is_array($document ['contact'] ['account'])) {
		$contactAccountValue = !empty($document ['contact'] ['account'] ['acct']) ? (string) $document ['contact'] ['account'] ['acct'] : '';
		$contactAccountUrl = !empty($document ['contact'] ['account'] ['url']) ? (string) $document ['contact'] ['account'] ['url'] : '';
		if ($contactAccountValue === '' && $contactAccountUrl !== '') {
			$contactAccountValue = $contactAccountUrl;
		}
	}
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_contact_account', 'Contact account'), $contactAccountValue, $contactAccountUrl);
	foreach (array(
		'about' => 'instance_info_about_url',
		'status' => 'instance_info_status_url',
		'privacy_policy' => 'instance_info_privacy_policy_url',
		'terms_of_service' => 'instance_info_terms_of_service_url',
		'streaming' => 'instance_info_streaming_url'
	) as $urlKey => $labelKey) {
		$url = !empty($document ['configuration'] ['urls'] [$urlKey]) ? (string) $document ['configuration'] ['urls'] [$urlKey] : '';
		$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string($labelKey, ucwords(str_replace('_', ' ', $urlKey)) . ' URL'), $url, $url);
	}
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_status_character_limit', 'Status character limit'), isset($document ['configuration'] ['statuses'] ['max_characters']) ? (string) (int) $document ['configuration'] ['statuses'] ['max_characters'] : '');
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_media_limit', 'Media attachments per status'), isset($document ['configuration'] ['statuses'] ['max_media_attachments']) ? (string) (int) $document ['configuration'] ['statuses'] ['max_media_attachments'] : '');
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_media_description_limit', 'Media description limit'), isset($document ['configuration'] ['media_attachments'] ['description_limit']) ? (string) (int) $document ['configuration'] ['media_attachments'] ['description_limit'] : '');
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_url_reserved_length', 'Reserved characters per URL'), isset($document ['configuration'] ['statuses'] ['characters_reserved_per_url']) ? (string) (int) $document ['configuration'] ['statuses'] ['characters_reserved_per_url'] : '');
	$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_translation', 'Translations API available'), isset($document ['configuration'] ['translation'] ['enabled']) ? plugin_mastodon_admin_boolean_label($document ['configuration'] ['translation'] ['enabled']) : '');
	if (!empty($document ['configuration'] ['media_attachments'] ['supported_mime_types']) && is_array($document ['configuration'] ['media_attachments'] ['supported_mime_types'])) {
		$rows = plugin_mastodon_admin_add_info_row($rows, plugin_mastodon_lang_string('instance_info_supported_mime_types', 'Supported media MIME types'), implode(', ', $document ['configuration'] ['media_attachments'] ['supported_mime_types']));
	}

	return $rows;
}

/**
 * Assign plugin data to Smarty for the admin panel.
 * @param mixed $smarty
 * @return void
 */
function plugin_mastodon_admin_assign(&$smarty) {
	$options = plugin_mastodon_get_options();
	$state = plugin_mastodon_state_read();
	$authorizeUrl = plugin_mastodon_build_authorize_url($options);
	if ($authorizeUrl === '' && !empty($options ['last_authorize_url'])) {
		$authorizeUrl = $options ['last_authorize_url'];
	}

	$smarty->assign('mastodon_cfg', array(
		'instance_url' => $options ['instance_url'],
		'username' => $options ['username'],
		'password' => $options ['password'],
		'sync_time' => plugin_mastodon_sync_time_utc_to_local($options ['sync_time']),
		'sync_time_utc' => $options ['sync_time'],
		'sync_time_offset_label' => plugin_mastodon_fp_timeoffset_label(),
		'sync_start_date' => $options ['sync_start_date'],
		'update_local_from_remote' => $options ['update_local_from_remote'],
		'import_synced_comments_as_entries' => $options ['import_synced_comments_as_entries'],
		'quote_imported_reply_parent' => $options ['quote_imported_reply_parent'],
		'delete_sync_enabled' => $options ['delete_sync_enabled'],
		'client_id' => $options ['client_id'],
		'client_secret' => $options ['client_secret'] !== '' ? '••••••••' : '',
		'access_token' => $options ['access_token'] !== '' ? '••••••••' : '',
		'authorization_code' => $options ['authorization_code']
	));
	$adminState = $state;
	$adminState ['last_run_local'] = plugin_mastodon_format_admin_datetime(isset($state ['last_run']) ? $state ['last_run'] : '');
	$adminState ['last_deletion_run_local'] = plugin_mastodon_format_admin_datetime(isset($state ['last_deletion_run']) ? $state ['last_deletion_run'] : '');

	$smarty->assign('mastodon_state', $adminState);
	$smarty->assign('mastodon_authorize_url', $authorizeUrl);
	$smarty->assign('mastodon_temp_dir', PLUGIN_MASTODON_STATE_DIR);
	$smarty->assign('mastodon_instance_info_rows', plugin_mastodon_admin_instance_info_rows($options));
	$smarty->assign('mastodon_instance_info_available', plugin_mastodon_instance_document($options, false) !== array());
	$smarty->assign('mastodon_companion_plugins_head', plugin_mastodon_lang_string('companion_plugins_head', 'Companion FlatPress plugins'));
	$smarty->assign('mastodon_companion_plugins_intro', plugin_mastodon_lang_string('companion_plugins_intro', 'For the full Mastodon feature set, activate these FlatPress plugins as well.'));
	$smarty->assign('mastodon_companion_plugins', plugin_mastodon_companion_plugins_status());
}

if (class_exists('AdminPanelAction')) {

	class admin_plugin_mastodon extends AdminPanelAction {

		var $langres = 'plugin:mastodon';

		function setup() {
			$this->smarty->assign('admin_resource', 'plugin:mastodon/admin.plugin.mastodon');
			plugin_mastodon_admin_assign($this->smarty);
		}

		function main() {
			return 0;
		}

		function onsubmit($data = null) {
			$options = plugin_mastodon_get_options();

			if (isset($_POST ['mastodon_save'])) {
				$options ['instance_url'] = plugin_mastodon_normalize_instance_url(isset($_POST ['instance_url']) ? $_POST ['instance_url'] : '');
				$options ['username'] = trim(isset($_POST ['username']) ? (string) $_POST ['username'] : '');
				$options ['password'] = trim(isset($_POST ['password']) ? (string) $_POST ['password'] : '');
				$options ['sync_time'] = plugin_mastodon_sync_time_local_to_utc(isset($_POST ['sync_time']) ? (string) $_POST ['sync_time'] : '');
				$options ['sync_start_date'] = plugin_mastodon_normalize_sync_start_date(isset($_POST ['sync_start_date']) ? $_POST ['sync_start_date'] : '');
				$options ['update_local_from_remote'] = plugin_mastodon_normalize_update_local_from_remote(isset($_POST ['update_local_from_remote']) ? $_POST ['update_local_from_remote'] : '');
				$options ['import_synced_comments_as_entries'] = plugin_mastodon_normalize_import_synced_comments_as_entries(isset($_POST ['import_synced_comments_as_entries']) ? $_POST ['import_synced_comments_as_entries'] : '');
				$options ['quote_imported_reply_parent'] = plugin_mastodon_normalize_quote_imported_reply_parent(isset($_POST ['quote_imported_reply_parent']) ? $_POST ['quote_imported_reply_parent'] : '');
				$options ['delete_sync_enabled'] = plugin_mastodon_normalize_delete_sync_enabled(isset($_POST ['delete_sync_enabled']) ? $_POST ['delete_sync_enabled'] : '');
				$options ['authorization_code'] = trim(isset($_POST ['authorization_code']) ? (string) $_POST ['authorization_code'] : '');
				plugin_mastodon_save_options($options);
				$this->smarty->assign('success', 1);
			} elseif (isset($_POST ['mastodon_register_app'])) {
				$options ['instance_url'] = plugin_mastodon_normalize_instance_url(isset($_POST ['instance_url']) ? $_POST ['instance_url'] : $options ['instance_url']);
				plugin_mastodon_save_options($options);
				$response = plugin_mastodon_register_app($options);
				$this->smarty->assign('success', $response ['ok'] ? 2 : -2);
			} elseif (isset($_POST ['mastodon_exchange_code'])) {
				$code = trim(isset($_POST ['authorization_code']) ? (string) $_POST ['authorization_code'] : '');
				$response = plugin_mastodon_exchange_code_for_token($options, $code);
				$this->smarty->assign('success', $response ['ok'] ? 3 : -3);
			} elseif (isset($_POST ['mastodon_refresh_instance_info'])) {
				$options ['instance_url'] = plugin_mastodon_normalize_instance_url(isset($_POST ['instance_url']) ? $_POST ['instance_url'] : $options ['instance_url']);
				if ($options ['instance_url'] === '') {
					$options = plugin_mastodon_clear_saved_instance_info($options);
					plugin_mastodon_save_options($options);
					$this->smarty->assign('success', -6);
				} else {
					if ($options ['instance_url'] !== plugin_mastodon_normalize_instance_url(plugin_mastodon_get_options()['instance_url'])) {
						plugin_mastodon_save_options($options);
					}
					$response = plugin_mastodon_refresh_instance_information($options);
					$this->smarty->assign('success', !empty($response ['ok']) ? 6 : -6);
				}
			} elseif (isset($_POST ['mastodon_run_now'])) {
				$result = plugin_mastodon_run_sync(true);
				$this->smarty->assign('success', $result ['ok'] ? 4 : -4);
			} elseif (isset($_POST ['mastodon_clear_token'])) {
				$options ['access_token'] = '';
				$options ['authorization_code'] = '';
				plugin_mastodon_save_options($options);
				$this->smarty->assign('success', 5);
			}

			plugin_mastodon_admin_assign($this->smarty);
			return 0;
		}
	}

	admin_addpanelaction('plugin', 'mastodon', true);
}
?>

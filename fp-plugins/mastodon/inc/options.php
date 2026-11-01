<?php
/**
 * Mastodon options declarations. Loaded by plugin.mastodon.php.
 * Runtime constants and registrations remain in the main plugin file.
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
		'sync_scheduled_window_days' => '14',
		'update_local_from_remote' => '0',
		'disable_remote_import' => '0',
		'disable_comment_reply_sync' => '0',
		'import_synced_comments_as_entries' => '0',
		'quote_imported_reply_parent' => '1',
		'old_thread_reply_check' => '1',
		'old_thread_context_limit' => '3',
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
		$options ['sync_scheduled_window_days'] = plugin_mastodon_normalize_scheduled_window_days($options ['sync_scheduled_window_days']);
		$options ['update_local_from_remote'] = plugin_mastodon_normalize_update_local_from_remote($options ['update_local_from_remote']);
		$options ['disable_remote_import'] = plugin_mastodon_normalize_disable_remote_import($options ['disable_remote_import']);
		$options ['disable_comment_reply_sync'] = plugin_mastodon_normalize_disable_comment_reply_sync($options ['disable_comment_reply_sync']);
		$options ['import_synced_comments_as_entries'] = plugin_mastodon_normalize_import_synced_comments_as_entries($options ['import_synced_comments_as_entries']);
		$options ['quote_imported_reply_parent'] = plugin_mastodon_normalize_quote_imported_reply_parent($options ['quote_imported_reply_parent']);
		$options ['old_thread_reply_check'] = plugin_mastodon_normalize_old_thread_reply_check($options ['old_thread_reply_check']);
		$options ['old_thread_context_limit'] = plugin_mastodon_normalize_old_thread_context_limit($options ['old_thread_context_limit']);
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
	$options ['sync_scheduled_window_days'] = plugin_mastodon_normalize_scheduled_window_days($options ['sync_scheduled_window_days']);
	$options ['update_local_from_remote'] = plugin_mastodon_normalize_update_local_from_remote($options ['update_local_from_remote']);
	$options ['disable_remote_import'] = plugin_mastodon_normalize_disable_remote_import($options ['disable_remote_import']);
	$options ['disable_comment_reply_sync'] = plugin_mastodon_normalize_disable_comment_reply_sync($options ['disable_comment_reply_sync']);
	$options ['import_synced_comments_as_entries'] = plugin_mastodon_normalize_import_synced_comments_as_entries($options ['import_synced_comments_as_entries']);
	$options ['quote_imported_reply_parent'] = plugin_mastodon_normalize_quote_imported_reply_parent($options ['quote_imported_reply_parent']);
	$options ['old_thread_reply_check'] = plugin_mastodon_normalize_old_thread_reply_check($options ['old_thread_reply_check']);
	$options ['old_thread_context_limit'] = plugin_mastodon_normalize_old_thread_context_limit($options ['old_thread_context_limit']);
	$options ['delete_sync_enabled'] = plugin_mastodon_normalize_delete_sync_enabled($options ['delete_sync_enabled']);
	$options ['oauth_registered_scopes'] = trim((string) $options ['oauth_registered_scopes']);
	plugin_mastodon_runtime_cache_set('options', 'normalized', $options);
	return $options;
}

/**
 * Save Mastodon plugin options.
 * @param array<string, mixed> $options
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

	$merged ['sync_scheduled_window_days'] = plugin_mastodon_normalize_scheduled_window_days(isset($merged ['sync_scheduled_window_days']) ? $merged ['sync_scheduled_window_days'] : '');
	$merged ['old_thread_reply_check'] = plugin_mastodon_normalize_old_thread_reply_check(isset($merged ['old_thread_reply_check']) ? $merged ['old_thread_reply_check'] : '');
	$merged ['old_thread_context_limit'] = plugin_mastodon_normalize_old_thread_context_limit(isset($merged ['old_thread_context_limit']) ? $merged ['old_thread_context_limit'] : '');
	$merged ['disable_remote_import'] = plugin_mastodon_normalize_disable_remote_import(isset($merged ['disable_remote_import']) ? $merged ['disable_remote_import'] : '');
	$merged ['disable_comment_reply_sync'] = plugin_mastodon_normalize_disable_comment_reply_sync(isset($merged ['disable_comment_reply_sync']) ? $merged ['disable_comment_reply_sync'] : '');

	foreach (array('instance_url', 'username', 'sync_time', 'sync_start_date', 'sync_scheduled_window_days', 'update_local_from_remote', 'disable_remote_import', 'disable_comment_reply_sync', 'import_synced_comments_as_entries', 'quote_imported_reply_parent', 'old_thread_reply_check', 'old_thread_context_limit', 'delete_sync_enabled', 'last_authorize_url', 'oauth_registered_scopes', 'instance_info_url', 'instance_info_json', 'instance_info_fetched_at', 'instance_info_error', 'instance_info_error_at') as $plainKey) {
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
		$merged ['disable_remote_import'] = plugin_mastodon_normalize_disable_remote_import((string) $merged ['disable_remote_import']);
		$merged ['disable_comment_reply_sync'] = plugin_mastodon_normalize_disable_comment_reply_sync((string) $merged ['disable_comment_reply_sync']);
		$merged ['import_synced_comments_as_entries'] = plugin_mastodon_normalize_import_synced_comments_as_entries((string) $merged ['import_synced_comments_as_entries']);
		$merged ['quote_imported_reply_parent'] = plugin_mastodon_normalize_quote_imported_reply_parent((string) $merged ['quote_imported_reply_parent']);
		$merged ['old_thread_context_limit'] = plugin_mastodon_normalize_old_thread_context_limit((string) $merged ['old_thread_context_limit']);
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
			$state = plugin_mastodon_state_read(array());
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
			if (!empty($state ['deletions_not_before'])) {
				$state ['deletions_not_before'] = '';
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

	return (string) $matches [1];
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
 * Normalize the automatic scheduled synchronization window.
 * @param mixed $value
 * @return string
 */
function plugin_mastodon_normalize_scheduled_window_days($value) {
	$value = trim((string) $value);
	if (in_array($value, array('7', '14', '30'), true)) {
		return $value;
	}
	return '14';
}

/**
 * Return the admin radio choices for the scheduled synchronization window.
 * @return array<int, array<string, string>>
 */
function plugin_mastodon_scheduled_window_choices() {
	return array(
		array('value' => '7', 'label' => plugin_mastodon_lang_string('sync_scheduled_window_7_days', '7 days')),
		array('value' => '14', 'label' => plugin_mastodon_lang_string('sync_scheduled_window_14_days', '14 days')),
		array('value' => '30', 'label' => plugin_mastodon_lang_string('sync_scheduled_window_30_days', '30 days'))
	);
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
 * Normalize the one-way mode toggle that blocks Mastodon-to-FlatPress imports.
 * @param mixed $value
 * @return string
 */
function plugin_mastodon_normalize_disable_remote_import($value) {
	return plugin_mastodon_normalize_boolean_option($value);
}

/**
 * Check whether remote Mastodon statuses may create, update or delete local FlatPress content.
 * @param array<string, string> $options
 * @return bool
 */
function plugin_mastodon_should_import_remote_to_local($options) {
	return plugin_mastodon_normalize_disable_remote_import(isset($options ['disable_remote_import']) ? $options ['disable_remote_import'] : '') !== '1';
}

/**
 * Normalize the toggle that disables comment and reply synchronization in both directions.
 * @param mixed $value
 * @return string
 */
function plugin_mastodon_normalize_disable_comment_reply_sync($value) {
	return plugin_mastodon_normalize_boolean_option($value);
}

/**
 * Check whether FlatPress comments and Mastodon replies may be synchronized.
 * @param array<string, string> $options
 * @return bool
 */
function plugin_mastodon_should_sync_comments_and_replies($options) {
	return plugin_mastodon_normalize_disable_comment_reply_sync(isset($options ['disable_comment_reply_sync']) ? $options ['disable_comment_reply_sync'] : '') !== '1';
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
 * Normalize the toggle that enables rotating context checks for known synchronized Mastodon threads.
 * @param mixed $value
 * @return string
 */
function plugin_mastodon_normalize_old_thread_reply_check($value) {
	return plugin_mastodon_normalize_boolean_option($value);
}

/**
 * Check whether known synchronized Mastodon threads should be checked for replies in rotating batches.
 * @param array<string, string> $options
 * @return bool
 */
function plugin_mastodon_should_check_old_thread_replies($options) {
	return plugin_mastodon_normalize_old_thread_reply_check(isset($options ['old_thread_reply_check']) ? $options ['old_thread_reply_check'] : '') === '1';
}

/**
 * Normalize the number of old synchronized threads refreshed per content sync run.
 * @param mixed $value
 * @return string
 */
function plugin_mastodon_normalize_old_thread_context_limit($value) {
	$value = (int) trim((string) $value);
	if ($value < 1) {
		return '1';
	}
	if ($value > 10) {
		return '10';
	}
	return (string) $value;
}

/**
 * Return the admin choices for the old-thread context refresh limit.
 * @return array<int, array<string, string>>
 */
function plugin_mastodon_old_thread_context_limit_choices() {
	$choices = array();
	for ($limit = 1; $limit <= 10; $limit++) {
		$choices [] = array('value' => (string) $limit, 'label' => (string) $limit);
	}
	return $choices;
}

/**
 * Check whether reply-notification polling may be used for old-thread hints.
 * @param array<string, string> $options
 * @return bool
 */
function plugin_mastodon_should_poll_reply_notifications($options) {
	return plugin_mastodon_should_sync_comments_and_replies($options) && plugin_mastodon_should_check_old_thread_replies($options) && plugin_mastodon_oauth_notifications_scope_available($options);
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
 * Return the status of companion FlatPress plugins used by the current synchronization mode.
 *
 * Import-only render helpers are hidden in explicit one-way mode because Mastodon-to-FlatPress
 * imports are disabled there. Export helpers stay visible with export-only descriptions.
 *
 * @param array<string, mixed>|null $options
 * @return array<int, array<string, mixed>>
 */
function plugin_mastodon_companion_plugins_status($options = null) {
	$options = $options === null ? plugin_mastodon_get_options() : array_merge(plugin_mastodon_default_options(), is_array($options) ? $options : array());
	$remoteImportEnabled = plugin_mastodon_should_import_remote_to_local($options);

	$statusActive = plugin_mastodon_lang_string('companion_status_active', 'Active');
	$statusMissing = plugin_mastodon_lang_string('companion_status_missing', 'Not active');
	$plugins = array();

	if ($remoteImportEnabled) {
		$bbcodeActive = plugin_mastodon_bbcode_plugin_active();
		$photoswipeActive = plugin_mastodon_photoswipe_plugin_active();
		$audiovideoActive = plugin_mastodon_audiovideo_plugin_active();

		$plugins [] = array(
			'slug' => 'bbcode',
			'label' => plugin_mastodon_lang_string('companion_bbcode_label', 'BBCode'),
			'active' => $bbcodeActive,
			'status_label' => $bbcodeActive ? $statusActive : $statusMissing,
			'description' => plugin_mastodon_lang_string('companion_bbcode_desc', 'Required to render imported URLs, images, galleries and formatting as FlatPress markup instead of raw BBCode.')
		);
		$plugins [] = array(
			'slug' => 'photoswipe',
			'label' => plugin_mastodon_lang_string('companion_photoswipe_label', 'PhotoSwipe'),
			'active' => $photoswipeActive,
			'status_label' => $photoswipeActive ? $statusActive : $statusMissing,
			'description' => plugin_mastodon_lang_string('companion_photoswipe_desc', 'Recommended for the expected FlatPress presentation of imported and synchronized images and galleries.')
		);
		$plugins [] = array(
			'slug' => 'audiovideo',
			'label' => plugin_mastodon_lang_string('companion_audiovideo_label', 'Audio/Video'),
			'active' => $audiovideoActive,
			'status_label' => $audiovideoActive ? $statusActive : $statusMissing,
			'description' => plugin_mastodon_lang_string('companion_audiovideo_desc', 'Required to render imported and synchronized Mastodon audio and video attachments as FlatPress HTML5 media players.')
		);
	}

	$tagActive = plugin_mastodon_tag_plugin_active();
	$emoticonsActive = plugin_mastodon_emoticons_plugin_active();

	$plugins [] = array(
		'slug' => 'tag',
		'label' => plugin_mastodon_lang_string('companion_tag_label', 'Tag'),
		'active' => $tagActive,
		'status_label' => $tagActive ? $statusActive : $statusMissing,
		'description' => $remoteImportEnabled ? plugin_mastodon_lang_string('companion_tag_desc', 'Enables synchronized FlatPress tags and Mastodon hashtags in both directions.') : plugin_mastodon_lang_string('companion_tag_desc_one_way', 'Exports FlatPress tags as Mastodon hashtags.')
	);
	$plugins [] = array(
		'slug' => 'emoticons',
		'label' => plugin_mastodon_lang_string('companion_emoticons_label', 'Emoticons'),
		'active' => $emoticonsActive,
		'status_label' => $emoticonsActive ? $statusActive : $statusMissing,
		'description' => $remoteImportEnabled ? plugin_mastodon_lang_string('companion_emoticons_desc', 'Renders imported Mastodon emoji shortcodes as FlatPress emoticons in entries and comments.') : plugin_mastodon_lang_string('companion_emoticons_desc_one_way', 'Converts FlatPress Emoticons shortcodes to Unicode emoji before exporting to Mastodon.')
	);

	return $plugins;
}
?>

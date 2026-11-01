<?php
/**
 * Mastodon api declarations. Loaded by plugin.mastodon.php.
 * Runtime constants and registrations remain in the main plugin file.
 */

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
		if (isset($document ['registrations'] ['min_age']) && $document ['registrations'] ['min_age'] !== '') {
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

		if (!empty($document ['configuration'] ['accounts']) && is_array($document ['configuration'] ['accounts'])) {
			$accounts = array();
			foreach (array('max_display_name_length', 'max_note_length', 'max_featured_tags', 'max_pinned_statuses', 'max_profile_fields', 'profile_field_name_limit', 'profile_field_value_limit') as $intKey) {
				if (isset($document ['configuration'] ['accounts'] [$intKey]) && $document ['configuration'] ['accounts'] [$intKey] !== '') {
					$accounts [$intKey] = max(0, (int) $document ['configuration'] ['accounts'] [$intKey]);
				}
			}
			if (!empty($accounts)) {
				$configuration ['accounts'] = $accounts;
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
	if (!is_string($json)) {
		return false;
	}

	$normalizedOptions = plugin_mastodon_clear_saved_instance_info($options);
	$normalizedOptions ['instance_info_url'] = $instanceUrl;
	$normalizedOptions ['instance_info_json'] = $json;
	$normalizedOptions ['instance_info_fetched_at'] = gmdate('c');
	$normalizedOptions ['instance_info_error'] = '';
	$normalizedOptions ['instance_info_error_at'] = '';

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
	$response = plugin_mastodon_mastodon_json($options, 'GET', '/api/v2/instance', array(), false, PLUGIN_MASTODON_INSTANCE_REQUEST_TIMEOUT);
	if (!empty($response ['ok']) && !empty($response ['json']) && is_array($response ['json'])) {
		$document = plugin_mastodon_compact_instance_document($response ['json']);
		if ($document !== array()) {
			plugin_mastodon_store_instance_document($options, $document);
			plugin_mastodon_runtime_cache_set('instance_document_failed', plugin_mastodon_normalize_instance_url($options ['instance_url']), false);
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
	return 'profile read:statuses read:notifications write:statuses write:media';
}

/**
 * Return the broad OAuth scopes used for new registrations when scope discovery is unavailable.
 * @return string
 */
function plugin_mastodon_oauth_compatible_scopes() {
	return 'read:accounts read:statuses read:notifications write:statuses write:media';
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
	return plugin_mastodon_oauth_compatible_scopes();
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
 * Split an OAuth scope string into unique normalized scope tokens.
 * @param string $scopes
 * @return array<int, string>
 */
function plugin_mastodon_oauth_scope_list($scopes) {
	$rawScopes = preg_split('/\s+/', trim((string) $scopes));
	if (!is_array($rawScopes)) {
		return array();
	}
	$list = array();
	foreach ($rawScopes as $scope) {
		$scope = trim((string) $scope);
		if ($scope !== '') {
			$list [$scope] = true;
		}
	}
	ksort($list, SORT_STRING);
	return array_keys($list);
}

/**
 * Determine whether a scope set contains a required OAuth scope token.
 * @param string $scopes
 * @param string $requiredScope
 * @return bool
 */
function plugin_mastodon_oauth_scopes_include($scopes, $requiredScope) {
	$requiredScope = trim((string) $requiredScope);
	return $requiredScope !== '' && in_array($requiredScope, plugin_mastodon_oauth_scope_list($scopes), true);
}

/**
 * Determine whether the configured app/token scope set can use notification polling.
 * @param array<string, string> $options
 * @return bool
 */
function plugin_mastodon_oauth_notifications_scope_available($options) {
	return plugin_mastodon_oauth_scopes_include(plugin_mastodon_oauth_scopes($options), 'read:notifications');
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

	$failed = plugin_mastodon_runtime_cache_get('instance_document_failed', $base, $failedHit);
	if ($failedHit && !empty($failed)) {
		return array();
	}

	$response = plugin_mastodon_mastodon_json($options, 'GET', '/api/v2/instance', array(), false, PLUGIN_MASTODON_INSTANCE_REQUEST_TIMEOUT);
	$document = (!empty($response ['ok']) && !empty($response ['json']) && is_array($response ['json'])) ? plugin_mastodon_compact_instance_document($response ['json']) : array();
	if ($document !== array()) {
		plugin_mastodon_apcu_store($apcuKey, $document, 900);
		plugin_mastodon_store_instance_document($options, $document);
		plugin_mastodon_runtime_cache_set('instance_document_failed', $base, false);
		return plugin_mastodon_runtime_cache_set('instance_document', $base, $document);
	}
	plugin_mastodon_runtime_cache_set('instance_document_failed', $base, true);
	return array();
}

/**
 * Normalize a human-readable Mastodon version string for version_compare().
 *
 * @param mixed $version
 * @return string
 */
function plugin_mastodon_normalized_instance_version($version) {
	$version = trim((string) $version);
	if ($version === '') {
		return '';
	}
	$normalized = preg_replace('/[^0-9.].*$/', '', $version);
	if (!is_string($normalized)) {
		return '';
	}
	$normalized = trim($normalized, '.');
	if ($normalized === '' || !preg_match('/^\d+(?:\.\d+){0,3}$/', $normalized)) {
		return '';
	}
	return $normalized;
}

/**
 * Return the machine-readable Mastodon API compatibility version from an instance document.
 *
 * @param array<string, mixed> $document
 * @param string $apiName
 * @return int|null
 */
function plugin_mastodon_instance_document_api_version($document, $apiName = 'mastodon') {
	if (!is_array($document) || empty($document ['api_versions']) || !is_array($document ['api_versions'])) {
		return null;
	}
	$apiName = trim((string) $apiName);
	if ($apiName === '' || !array_key_exists($apiName, $document ['api_versions'])) {
		return null;
	}
	$apiVersion = $document ['api_versions'] [$apiName];
	if (is_int($apiVersion) || is_float($apiVersion) || (is_string($apiVersion) && preg_match('/^\d+$/', trim($apiVersion)))) {
		$apiVersion = (int) $apiVersion;
		return $apiVersion >= 0 ? $apiVersion : null;
	}
	return null;
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
	$document = plugin_mastodon_instance_document($options);
	$apiVersion = plugin_mastodon_instance_document_api_version($document, 'mastodon');
	if ($apiVersion !== null) {
		return $apiVersion >= 1;
	}
	if (empty($document ['version']) || !is_string($document ['version'])) {
		return false;
	}
	$normalized = plugin_mastodon_normalized_instance_version($document ['version']);
	if ($normalized === '') {
		return false;
	}
	return version_compare($normalized, '4.1.0', '>=');
}

/**
 * Determine whether cached instance information confirms Mastodon API v4 / 4.4 capability support.
 *
 * This shared helper intentionally uses cached or stored instance information
 * only, so capability checks for cleanup or deletion paths do not spend an
 * additional network request merely to decide which API shape to use.
 *
 * @param array<string, string> $options
 * @return bool|null True/false when cached capability information is known, null when it is unknown.
 */
function plugin_mastodon_instance_supports_mastodon_api_v4($options) {
	$document = plugin_mastodon_instance_document($options, false);
	$apiVersion = plugin_mastodon_instance_document_api_version($document, 'mastodon');
	if ($apiVersion !== null) {
		return $apiVersion >= 4;
	}
	if (empty($document ['version']) || !is_string($document ['version'])) {
		return null;
	}
	$normalized = plugin_mastodon_normalized_instance_version($document ['version']);
	if ($normalized === '') {
		return null;
	}
	return version_compare($normalized, '4.4.0', '>=');
}

/**
 * Determine whether cached instance information confirms support for the
 * exclude_direct query parameter on GET /api/v1/accounts/:id/statuses.
 *
 * Mastodon added this privacy-focused filter in 4.6.0. When a machine-readable
 * Mastodon API version is available, API version 10 or newer is treated as
 * supporting the parameter; otherwise the exact version string is normalized so
 * nightly/fork suffixes such as 4.6.0-nightly remain comparable. Unknown
 * capability returns null so callers can decide whether to keep the legacy
 * request or deliberately probe with fallback.
 *
 * @param array<string, string> $options
 * @return bool|null True/false when cached capability information is known, null when it is unknown.
 */
function plugin_mastodon_instance_supports_account_statuses_exclude_direct($options) {
	$document = plugin_mastodon_instance_document($options, false);
	$apiVersion = plugin_mastodon_instance_document_api_version($document, 'mastodon');
	if ($apiVersion !== null) {
		return $apiVersion >= 10;
	}
	if (empty($document ['version']) || !is_string($document ['version'])) {
		return null;
	}
	$normalized = plugin_mastodon_normalized_instance_version($document ['version']);
	if ($normalized === '') {
		return null;
	}
	return version_compare($normalized, '4.6.0', '>=');
}

/**
 * Check whether a failed account-status request may be caused by Mastodon
 * versions before 4.6.0 or compatible forks not understanding exclude_direct.
 *
 * @param array<string, mixed> $response
 * @return bool
 */
function plugin_mastodon_account_statuses_exclude_direct_should_retry_without($response) {
	if (plugin_mastodon_rate_limit_state_error() !== '') {
		return false;
	}
	$code = isset($response ['code']) ? (int) $response ['code'] : 0;
	if ($code === 400 || $code === 405 || $code === 422) {
		return true;
	}
	if ($code === 0) {
		$message = strtolower(plugin_mastodon_response_error_message($response));
		return $message !== '' && strpos($message, 'exclude_direct') !== false;
	}
	return false;
}

/**
 * Determine whether cached instance information confirms support for the
 * delete_media query parameter on DELETE /api/v1/statuses/:id.
 *
 * Mastodon added the optional delete_media parameter in 4.4.0 (`mastodon` API
 * version 4). The shared API-v4 capability helper keeps this decision aligned
 * with the unattached-media cleanup capability while avoiding extra network
 * requests on deletion paths.
 *
 * @param array<string, string> $options
 * @return bool|null True/false when the cached version is known, null when it is unknown.
 */
function plugin_mastodon_instance_supports_status_delete_media($options) {
	return plugin_mastodon_instance_supports_mastodon_api_v4($options);
}

/**
 * Determine whether cached instance information confirms support for deleting
 * unattached uploaded media through DELETE /api/v1/media/:id.
 *
 * Mastodon added this endpoint in 4.4.0 (`mastodon` API version 4). The shared
 * API-v4 capability helper keeps this decision aligned with status deletion's
 * delete_media support while avoiding extra instance-information network
 * requests on cleanup paths.
 *
 * @param array<string, string> $options
 * @return bool|null True/false when the cached version is known, null when it is unknown.
 */
function plugin_mastodon_instance_supports_media_delete($options) {
	return plugin_mastodon_instance_supports_mastodon_api_v4($options);
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
		if ($ch !== false) {
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
			// @phpstan-ignore argument.type (PHPStan 2.2.10 reports the PHP 7.x cURL resource arm as incompatible in ranged phpVersion analysis)
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
	}

	$boundary = '----FlatPressMastodon' . md5(uniqid('', true));
	$preparedBody = plugin_mastodon_build_multipart_body($fields, $boundary);
	if (!$preparedBody ['ok']) {
		return array('ok' => false, 'code' => 0, 'headers' => array(), 'body' => '', 'error' => $preparedBody ['error']);
	}
	$body = $preparedBody ['body'];
	unset($preparedBody);

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
		if ($ch !== false) {
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
			// @phpstan-ignore argument.type (PHPStan 2.2.10 reports the PHP 7.x cURL resource arm as incompatible in ranged phpVersion analysis)
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
 * @param int $timeout
 * @return array<string, mixed>
 */
function plugin_mastodon_mastodon_api($options, $method, $path, $params, $auth, $timeout = 45) {
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

	$rateLimitReason = plugin_mastodon_rate_limit_acquire($method, $path);
	if ($rateLimitReason !== '') {
		return plugin_mastodon_rate_limit_blocked_response($rateLimitReason);
	}
	$response = plugin_mastodon_http_request($method, $url, $headers, $body, $contentType, $timeout);
	plugin_mastodon_rate_limit_observe_response($response);
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
 * @param int $timeout
 * @return array<string, mixed>
 */
function plugin_mastodon_mastodon_json($options, $method, $path, $params, $auth, $timeout = 45) {
	$response = plugin_mastodon_mastodon_api($options, $method, $path, $params, $auth, $timeout);
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
	if (!empty($response ['ok']) && !empty($response ['json']) && is_array($response ['json'])) {
		plugin_mastodon_refresh_profile_cache_from_account($options, $response ['json']);
	}
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
	$excludeDirectSupport = plugin_mastodon_instance_supports_account_statuses_exclude_direct($options);
	$useExcludeDirect = ($excludeDirectSupport === true);
	if ($useExcludeDirect) {
		$params ['exclude_direct'] = 'true';
	}
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
		if (!$response ['ok'] && $useExcludeDirect && plugin_mastodon_account_statuses_exclude_direct_should_retry_without($response)) {
			plugin_mastodon_log('Retrying Mastodon account-status import without exclude_direct after response ' . (isset($response ['code']) ? (string) (int) $response ['code'] : '0'));
			$useExcludeDirect = false;
			unset($params ['exclude_direct']);
			$response = plugin_mastodon_mastodon_json($options, 'GET', '/api/v1/accounts/' . rawurlencode($accountId) . '/statuses', $params, true);
		}
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
 * Fetch recent mention notifications that may point to replies on older synchronized threads.
 * @param array<string, string> $options
 * @param string $sinceId
 * @return array<string, mixed>
 */
function plugin_mastodon_fetch_reply_notifications($options, $sinceId) {
	$params = array(
		'limit' => PLUGIN_MASTODON_NOTIFICATION_PAGE_LIMIT,
		'types' => array('mention')
	);
	$sinceId = trim((string) $sinceId);
	if ($sinceId !== '') {
		$params ['since_id'] = $sinceId;
	}
	$response = plugin_mastodon_mastodon_json($options, 'GET', '/api/v1/notifications', $params, true);
	if (!$response ['ok']) {
		return array('ok' => false, 'items' => array(), 'error' => isset($response ['error']) ? (string) $response ['error'] : '');
	}
	$items = isset($response ['json']) && is_array($response ['json']) ? $response ['json'] : array();
	return array('ok' => true, 'items' => $items, 'error' => '');
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
	$deleteMediaSupported = $deleteMedia ? plugin_mastodon_instance_supports_status_delete_media($options) : false;
	$useDeleteMediaParameter = $deleteMedia && $deleteMediaSupported !== false;
	$requestPath = $path;
	if ($useDeleteMediaParameter) {
		$requestPath .= '?delete_media=1';
	}

	$response = plugin_mastodon_mastodon_json($options, 'DELETE', $requestPath, array(), true);
	if ($useDeleteMediaParameter && empty($response ['ok']) && plugin_mastodon_delete_status_should_retry_without_delete_media($response)) {
		plugin_mastodon_log('Retrying Mastodon status deletion without delete_media for status ' . (string) $statusId . ' after response ' . (isset($response ['code']) ? (string) (int) $response ['code'] : '0'));
		$fallback = plugin_mastodon_mastodon_json($options, 'DELETE', $path, array(), true);
		$fallback ['delete_media_fallback_attempted'] = true;
		$fallback ['delete_media_first_code'] = isset($response ['code']) ? (int) $response ['code'] : 0;
		$fallback ['delete_media_first_error'] = plugin_mastodon_response_error_message($response);
		return $fallback;
	}
	return $response;
}

/**
 * Check whether a failed status deletion may be caused by Mastodon versions
 * before 4.4.0 not understanding the optional delete_media query parameter.
 *
 * @param array<string, mixed> $response
 * @return bool
 */
function plugin_mastodon_delete_status_should_retry_without_delete_media($response) {
	if (plugin_mastodon_rate_limit_state_error() !== '') {
		return false;
	}
	$code = isset($response ['code']) ? (int) $response ['code'] : 0;
	if ($code === 400 || $code === 405 || $code === 422) {
		return true;
	}
	if ($code === 0) {
		$message = strtolower(plugin_mastodon_response_error_message($response));
		return $message !== '' && strpos($message, 'delete_media') !== false;
	}
	return false;
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
?>

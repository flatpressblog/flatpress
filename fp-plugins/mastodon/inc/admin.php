<?php
/**
 * Mastodon admin declarations. Loaded by plugin.mastodon.php.
 * Runtime constants and registrations remain in the main plugin file.
 */

/**
 * Apply submitted admin settings while preserving hidden remote-import options.
 *
 * When one-way mode hides Mastodon-to-FlatPress import controls, their previous
 * values must survive the save request so disabling one-way mode later restores
 * the administrator's former bidirectional-import preferences.
 *
 * @param array<string, mixed> $options
 * @param array<string, mixed> $post
 * @return array<string, mixed>
 */
function plugin_mastodon_admin_apply_save_post($options, $post) {
	$options = array_merge(plugin_mastodon_default_options(), is_array($options) ? $options : array());
	$post = is_array($post) ? $post : array();
	$remoteImportOptionsHidden = plugin_mastodon_normalize_disable_remote_import(isset($options ['disable_remote_import']) ? $options ['disable_remote_import'] : '') === '1'
		&& !empty($post ['mastodon_remote_import_options_hidden']);

	$options ['instance_url'] = plugin_mastodon_normalize_instance_url(isset($post ['instance_url']) ? $post ['instance_url'] : '');
	$options ['username'] = trim(isset($post ['username']) ? (string) $post ['username'] : '');
	$options ['password'] = trim(isset($post ['password']) ? (string) $post ['password'] : '');
	$options ['sync_time'] = plugin_mastodon_sync_time_local_to_utc(isset($post ['sync_time']) ? (string) $post ['sync_time'] : '');
	$options ['sync_start_date'] = plugin_mastodon_normalize_sync_start_date(isset($post ['sync_start_date']) ? $post ['sync_start_date'] : '');
	$options ['sync_scheduled_window_days'] = plugin_mastodon_normalize_scheduled_window_days(isset($post ['sync_scheduled_window_days']) ? $post ['sync_scheduled_window_days'] : '');
	$options ['disable_remote_import'] = plugin_mastodon_normalize_disable_remote_import(isset($post ['disable_remote_import']) ? $post ['disable_remote_import'] : '');
	$options ['disable_comment_reply_sync'] = plugin_mastodon_normalize_disable_comment_reply_sync(isset($post ['disable_comment_reply_sync']) ? $post ['disable_comment_reply_sync'] : '');

	if (!$remoteImportOptionsHidden) {
		$options ['update_local_from_remote'] = plugin_mastodon_normalize_update_local_from_remote(isset($post ['update_local_from_remote']) ? $post ['update_local_from_remote'] : '');
		$options ['import_synced_comments_as_entries'] = plugin_mastodon_normalize_import_synced_comments_as_entries(isset($post ['import_synced_comments_as_entries']) ? $post ['import_synced_comments_as_entries'] : '');
		$options ['quote_imported_reply_parent'] = plugin_mastodon_normalize_quote_imported_reply_parent(isset($post ['quote_imported_reply_parent']) ? $post ['quote_imported_reply_parent'] : '');
		$options ['old_thread_reply_check'] = plugin_mastodon_normalize_old_thread_reply_check(isset($post ['old_thread_reply_check']) ? $post ['old_thread_reply_check'] : '');
		$options ['old_thread_context_limit'] = plugin_mastodon_normalize_old_thread_context_limit(isset($post ['old_thread_context_limit']) ? $post ['old_thread_context_limit'] : '');
	} else {
		$options ['update_local_from_remote'] = plugin_mastodon_normalize_update_local_from_remote(isset($options ['update_local_from_remote']) ? $options ['update_local_from_remote'] : '');
		$options ['import_synced_comments_as_entries'] = plugin_mastodon_normalize_import_synced_comments_as_entries(isset($options ['import_synced_comments_as_entries']) ? $options ['import_synced_comments_as_entries'] : '');
		$options ['quote_imported_reply_parent'] = plugin_mastodon_normalize_quote_imported_reply_parent(isset($options ['quote_imported_reply_parent']) ? $options ['quote_imported_reply_parent'] : '');
		$options ['old_thread_reply_check'] = plugin_mastodon_normalize_old_thread_reply_check(isset($options ['old_thread_reply_check']) ? $options ['old_thread_reply_check'] : '');
		$options ['old_thread_context_limit'] = plugin_mastodon_normalize_old_thread_context_limit(isset($options ['old_thread_context_limit']) ? $options ['old_thread_context_limit'] : '');
	}

	$options ['delete_sync_enabled'] = plugin_mastodon_normalize_delete_sync_enabled(isset($post ['delete_sync_enabled']) ? $post ['delete_sync_enabled'] : '');
	$options ['authorization_code'] = trim(isset($post ['authorization_code']) ? (string) $post ['authorization_code'] : '');

	return $options;
}

/**
 * Return Mastodon admin-language strings with guaranteed maintenance fallbacks.
 *
 * Hidden FlatPress admin actions do not always expose the plugin language array
 * through $plang in the same way as the visible plugin settings page. The
 * maintenance templates therefore use this dedicated, explicitly assigned array
 * so Smarty 5.8 never emits undefined-array-key warnings when optional language
 * keys are missing from the current runtime context.
 *
 * @return array<string, string>
 */
function plugin_mastodon_admin_language_strings() {
	global $lang;

	$defaults = array(
		'description' => 'Description',
		'output' => 'Output',
		'state_maintenance_head' => 'State diagnostics and repair',
		'state_maintenance_desc' => 'Check and repair the per-entry comment-shard metadata and the global remote-comment index. Repair only rewrites local plugin state files.',
		'state_maintenance_desc_short' => 'Advanced state diagnostics and repair are available on a separate maintenance page.',
		'state_maintenance_open' => 'Open state maintenance',
		'state_maintenance_back' => 'Back to Mastodon settings',
		'state_maintenance_actions_head' => 'State maintenance actions',
		'state_maintenance_actions_desc' => 'Diagnostics are read-only. Repair rewrites only local plugin state files.',
		'state_maintenance_diagnose' => 'Diagnose comment shards',
		'state_maintenance_repair' => 'Repair comment-shard state',
		'state_maintenance_result_head' => 'State diagnostics result',
		'state_maintenance_status' => 'Diagnostics status',
		'state_maintenance_status_ok' => 'Consistent',
		'state_maintenance_status_attention' => 'Needs attention',
		'state_maintenance_warnings' => 'Warnings',
		'state_maintenance_errors' => 'Errors',
		'state_maintenance_stat_shard_files' => 'Shard files',
		'state_maintenance_stat_shard_metadata_entries' => 'Shard metadata entries',
		'state_maintenance_stat_comments' => 'Stored comments',
		'state_maintenance_stat_comments_remote' => 'Remote comment index entries',
		'state_maintenance_stat_repaired' => 'Repair performed',
		'bool_yes' => 'Yes',
		'bool_no' => 'No',
		'bool_unknown' => 'Unknown'
	);
	$defaultMsgs = array(
		7 => 'Comment-shard diagnostics completed.',
		8 => 'Comment-shard state repaired.',
		-7 => 'Comment-shard diagnostics found warnings or errors.',
		-8 => 'Comment-shard repair could not complete cleanly.'
	);
	$panelStrings = $defaults;
	$panelStrings ['msgs'] = $defaultMsgs;

	if (function_exists('lang_load')) {
		$loadedLang = lang_load('plugin:mastodon');
		if (isset($loadedLang ['admin'] ['plugin'] ['mastodon']) && is_array($loadedLang ['admin'] ['plugin'] ['mastodon'])) {
			foreach ($loadedLang ['admin'] ['plugin'] ['mastodon'] as $key => $value) {
				if (!is_string($key)) {
					continue;
				}
				if ($key === 'msgs' && is_array($value)) {
					$panelStrings ['msgs'] = $value + $defaultMsgs;
					continue;
				}
				$panelStrings [$key] = $value;
				if (is_string($value) && $value !== '') {
					$defaults [$key] = $value;
				}
			}
		}
	}

	if (!isset($lang) || !is_array($lang)) {
		$lang = array();
	}
	if (!isset($lang ['admin']) || !is_array($lang ['admin'])) {
		$lang ['admin'] = array();
	}
	if (!isset($lang ['admin'] ['plugin']) || !is_array($lang ['admin'] ['plugin'])) {
		$lang ['admin'] ['plugin'] = array();
	}
	$lang ['admin'] ['plugin'] ['mastodon_maintenance'] = $panelStrings;

	return $defaults;
}

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
 * Build a local admin-panel link for Mastodon plugin pages.
 * @param string $action
 * @return string
 */
function plugin_mastodon_admin_panel_link($action) {
	$action = preg_replace('/[^a-z0-9_\\-]/i', '', (string) $action);
	if ($action === '') {
		$action = 'mastodon';
	}
	if (function_exists('admin_getpanellink')) {
		$link = admin_getpanellink('plugin', $action);
		if (function_exists('htmlspecialchars_decode')) {
			return htmlspecialchars_decode($link, ENT_QUOTES);
		}
		return str_replace(array('&amp;', '&#038;'), '&', $link);
	}
	$baseUrl = defined('BLOG_BASEURL') ? BLOG_BASEURL : '';
	return $baseUrl . 'admin.php?p=plugin&action=' . rawurlencode($action);
}

/**
 * Return an empty admin maintenance result container.
 * @return array<string, mixed>
 */
function plugin_mastodon_admin_empty_state_maintenance_result() {
	return array(
		'available' => false,
		'ok' => false,
		'repaired' => false,
		'stats_rows' => array(),
		'errors' => array(),
		'warnings' => array()
	);
}

/**
 * Convert comment-shard diagnostics into template-friendly admin rows.
 * @param array<string, mixed> $diagnostics
 * @return array<string, mixed>
 */
function plugin_mastodon_admin_state_maintenance_result($diagnostics) {
	$diagnostics = is_array($diagnostics) ? $diagnostics : array();
	$stats = isset($diagnostics ['stats']) && is_array($diagnostics ['stats']) ? $diagnostics ['stats'] : array();
	$rows = array();
	foreach (array(
		'shard_files' => plugin_mastodon_lang_string('state_maintenance_stat_shard_files', 'Shard files'),
		'shard_metadata_entries' => plugin_mastodon_lang_string('state_maintenance_stat_shard_metadata_entries', 'Shard metadata entries'),
		'comments' => plugin_mastodon_lang_string('state_maintenance_stat_comments', 'Stored comments'),
		'comments_remote' => plugin_mastodon_lang_string('state_maintenance_stat_comments_remote', 'Remote comment index entries')
	) as $key => $label) {
		$rows [] = array(
			'label' => $label,
			'value' => isset($stats [$key]) ? (string) (int) $stats [$key] : '0'
		);
	}
	$rows [] = array(
		'label' => plugin_mastodon_lang_string('state_maintenance_stat_repaired', 'Repair performed'),
		'value' => !empty($diagnostics ['repaired']) ? plugin_mastodon_lang_string('bool_yes', 'Yes') : plugin_mastodon_lang_string('bool_no', 'No')
	);
	$errors = isset($diagnostics ['errors']) && is_array($diagnostics ['errors']) ? array_values($diagnostics ['errors']) : array();
	$warnings = isset($diagnostics ['warnings']) && is_array($diagnostics ['warnings']) ? array_values($diagnostics ['warnings']) : array();
	return array(
		'available' => true,
		'ok' => !empty($diagnostics ['ok']) && $errors === array() && $warnings === array(),
		'repaired' => !empty($diagnostics ['repaired']),
		'stats_rows' => $rows,
		'errors' => $errors,
		'warnings' => $warnings
	);
}

/**
 * Assign plugin data to Smarty for the admin panel.
 * @param mixed $smarty
 * @return void
 */
function plugin_mastodon_admin_assign(&$smarty) {
	$options = plugin_mastodon_get_options();
	$state = plugin_mastodon_scheduler_state_read();
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
		'sync_scheduled_window_days' => $options ['sync_scheduled_window_days'],
		'update_local_from_remote' => $options ['update_local_from_remote'],
		'disable_remote_import' => $options ['disable_remote_import'],
		'disable_comment_reply_sync' => $options ['disable_comment_reply_sync'],
		'import_synced_comments_as_entries' => $options ['import_synced_comments_as_entries'],
		'quote_imported_reply_parent' => $options ['quote_imported_reply_parent'],
		'old_thread_reply_check' => $options ['old_thread_reply_check'],
		'old_thread_context_limit' => $options ['old_thread_context_limit'],
		'reply_notifications_available' => plugin_mastodon_should_poll_reply_notifications($options) ? '1' : '0',
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
	$smarty->assign('mastodon_scheduled_window_choices', plugin_mastodon_scheduled_window_choices());
	$smarty->assign('mastodon_old_thread_context_limit_choices', plugin_mastodon_old_thread_context_limit_choices());
	$smarty->assign('mastodon_authorize_url', $authorizeUrl);
	$smarty->assign('mastodon_temp_dir', PLUGIN_MASTODON_STATE_DIR);
	$smarty->assign('mastodon_instance_info_rows', plugin_mastodon_admin_instance_info_rows($options));
	$smarty->assign('mastodon_instance_info_available', plugin_mastodon_instance_document($options, false) !== array());
	$smarty->assign('mastodon_companion_plugins_head', plugin_mastodon_lang_string('companion_plugins_head', 'Companion FlatPress plugins'));
	$remoteImportEnabled = plugin_mastodon_should_import_remote_to_local($options);
	$smarty->assign('mastodon_companion_plugins_intro', $remoteImportEnabled ? plugin_mastodon_lang_string('companion_plugins_intro', 'For the full Mastodon feature set, activate these FlatPress plugins as well.') : plugin_mastodon_lang_string('companion_plugins_intro_one_way', 'In one-way mode, only FlatPress-to-Mastodon export helpers are shown.'));
	$smarty->assign('mastodon_companion_plugins', plugin_mastodon_companion_plugins_status($options));
	$smarty->assign('mastodon_state_maintenance_result', plugin_mastodon_admin_empty_state_maintenance_result());
	$smarty->assign('mastodon_lang', plugin_mastodon_admin_language_strings());
	$smarty->assign('mastodon_admin_main_url', plugin_mastodon_admin_panel_link('mastodon'));
	$smarty->assign('mastodon_admin_maintenance_url', plugin_mastodon_admin_panel_link('mastodon_maintenance'));
}
?>

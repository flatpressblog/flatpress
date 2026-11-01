<?php
/**
 * Mastodon state declarations. Loaded by plugin.mastodon.php.
 * Runtime constants and registrations remain in the main plugin file.
 */

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
		'deletions_not_before' => '',
		'deletion_retry_not_before' => '',
		'local_entry_deletions' => array(),
		'last_error' => '',
		'last_remote_status_id' => '',
		'last_remote_notification_id' => '',
		'entries' => array(),
		'entries_remote' => array(),
		'comments' => array(),
		'comments_remote' => array(),
		'comment_shards' => array(
			'version' => 1,
			'storage' => 'inline',
			'count' => 0,
			'entries' => array()
		),
		'dirty_entries' => array(),
		'dirty_comments' => array(),
		'comment_tombstones' => array(),
		'comment_reply_optins' => array(),
		'pending_comment_remote_rechecks' => array(),
		'old_thread_context_cursor' => '',
		'deletion_cursor_entries' => '',
		'deletion_cursor_comments' => '',
		'content_stats' => plugin_mastodon_default_content_stats(),
		'deletion_stats' => plugin_mastodon_default_deletion_stats()
	);
}

/**
 * Ensure that the plugin runtime directory exists.
 * @return bool
 */
function plugin_mastodon_ensure_state_dir() {
	return fs_mkdir(PLUGIN_MASTODON_STATE_DIR);
}

/**
 * Ensure that the comment-shard directory exists.
 * @return bool
 */
function plugin_mastodon_ensure_comment_shard_dir() {
	return plugin_mastodon_ensure_state_dir() && fs_mkdir(PLUGIN_MASTODON_COMMENT_SHARD_DIR);
}

/**
 * Return whether a string looks like a FlatPress entry id.
 * @param string $entryId
 * @return bool
 */
function plugin_mastodon_state_is_entry_id($entryId) {
	return (bool) preg_match('/^entry[0-9]{6}-[0-9]{6}$/', (string) $entryId);
}

/**
 * Return the entry id portion from a compound comment-state key.
 * @param string $commentKey
 * @return string
 */
function plugin_mastodon_state_entry_id_from_comment_key($commentKey) {
	$parts = explode(':', (string) $commentKey, 2);
	$entryId = isset($parts [0]) ? (string) $parts [0] : '';
	return plugin_mastodon_state_is_entry_id($entryId) ? $entryId : '';
}

/**
 * Return the shard path for an entry's comment mappings.
 * @param string $entryId
 * @return string
 */
function plugin_mastodon_state_comment_shard_file($entryId) {
	$entryId = (string) $entryId;
	if (!plugin_mastodon_state_is_entry_id($entryId)) {
		return '';
	}
	$year = substr($entryId, 5, 2);
	$month = substr($entryId, 7, 2);
	return PLUGIN_MASTODON_COMMENT_SHARD_DIR . $year . DIRECTORY_SEPARATOR . $month . DIRECTORY_SEPARATOR . $entryId . '.json';
}

/**
 * Return the relative shard path used in state metadata.
 * @param string $entryId
 * @return string
 */
function plugin_mastodon_state_comment_shard_relative_path($entryId) {
	$entryId = (string) $entryId;
	if (!plugin_mastodon_state_is_entry_id($entryId)) {
		return '';
	}
	return substr($entryId, 5, 2) . '/' . substr($entryId, 7, 2) . '/' . $entryId . '.json';
}

/**
 * Write a JSON payload atomically when possible.
 * @param string $file
 * @param string $payload
 * @return bool
 */
function plugin_mastodon_state_write_json_file($file, $payload) {
	$file = (string) $file;
	if (isset($GLOBALS ['plugin_mastodon_test_write_json_log']) && is_array($GLOBALS ['plugin_mastodon_test_write_json_log'])) {
		$GLOBALS ['plugin_mastodon_test_write_json_log'] [] = $file;
	}
	if (isset($GLOBALS ['plugin_mastodon_test_write_json_fail_for'])) {
		$failFor = $GLOBALS ['plugin_mastodon_test_write_json_fail_for'];
		if (!is_array($failFor)) {
			$failFor = array($failFor);
		}
		foreach ($failFor as $needle) {
			$needle = (string) $needle;
			if ($needle !== '' && (basename($file) === $needle || strpos($file, $needle) !== false)) {
				return false;
			}
		}
	}
	$dir = dirname($file);
	if (!is_dir($dir) && !@mkdir($dir, DIR_PERMISSIONS, true)) {
		return false;
	}
	$tmp = $file . '.tmp.' . str_replace('.', '', uniqid('', true));
	if (@file_put_contents($tmp, $payload) === false) {
		@unlink($tmp);
		return false;
	}
	plugin_mastodon_apply_file_permissions($tmp);
	if (!@rename($tmp, $file)) {
		@unlink($tmp);
		return false;
	}
	plugin_mastodon_apply_file_permissions($file);
	return true;
}

/**
 * Acquire the short state-write lock used for multi-file state mutations.
 * @return resource|false
 */
function plugin_mastodon_state_write_lock_acquire() {
	plugin_mastodon_ensure_state_dir();
	$handle = @fopen(PLUGIN_MASTODON_STATE_WRITE_LOCK_FILE, 'c+');
	if (!$handle) {
		return false;
	}
	plugin_mastodon_apply_file_permissions(PLUGIN_MASTODON_STATE_WRITE_LOCK_FILE);
	$deadline = microtime(true) + 5.0;
	do {
		if (@flock($handle, LOCK_EX | LOCK_NB)) {
			return $handle;
		}
		usleep(50000);
	} while (microtime(true) < $deadline);
	@fclose($handle);
	return false;
}

/**
 * Release a state-write lock handle.
 * @param resource|false $handle
 * @return void
 */
function plugin_mastodon_state_write_lock_release($handle) {
	if (is_resource($handle)) {
		@flock($handle, LOCK_UN);
		@fclose($handle);
	}
}

/**
 * Decode a JSON string literal starting at the current offset.
 * @param string $json
 * @param int $offset
 * @return string|null
 */
function plugin_mastodon_json_decode_string_at($json, &$offset) {
	$length = strlen($json);
	if ($offset >= $length || $json[$offset] !== '"') {
		return null;
	}
	$start = $offset;
	$offset++;
	$escaped = false;
	while ($offset < $length) {
		$char = $json[$offset];
		if ($escaped) {
			$escaped = false;
			$offset++;
			continue;
		}
		if ($char === '\\') {
			$escaped = true;
			$offset++;
			continue;
		}
		if ($char === '"') {
			$offset++;
			$literal = substr($json, $start, $offset - $start);
			$value = json_decode($literal, true);
			return is_string($value) ? $value : null;
		}
		$offset++;
	}
	return null;
}

/**
 * Skip JSON whitespace in a string scanner.
 * @param string $json
 * @param int $offset
 * @return void
 */
function plugin_mastodon_json_skip_ws($json, &$offset) {
	$length = strlen($json);
	while ($offset < $length) {
		$char = $json[$offset];
		if ($char !== ' ' && $char !== "\t" && $char !== "\r" && $char !== "\n") {
			break;
		}
		$offset++;
	}
}

/**
 * Find a top-level JSON property value in an object payload without decoding the whole object.
 * @param string $json
 * @param string $property
 * @return array<string, int>
 */
function plugin_mastodon_json_top_level_property_bounds($json, $property) {
	$length = strlen($json);
	$offset = 0;
	plugin_mastodon_json_skip_ws($json, $offset);
	if ($offset >= $length || $json[$offset] !== '{') {
		return array();
	}
	$offset++;
	while ($offset < $length) {
		plugin_mastodon_json_skip_ws($json, $offset);
		if ($offset < $length && $json[$offset] === '}') {
			return array();
		}
		$nameStart = $offset;
		$name = plugin_mastodon_json_decode_string_at($json, $offset);
		if ($name === null) {
			return array();
		}
		plugin_mastodon_json_skip_ws($json, $offset);
		if ($offset >= $length || $json[$offset] !== ':') {
			return array();
		}
		$offset++;
		plugin_mastodon_json_skip_ws($json, $offset);
		$valueStart = $offset;
		$depth = 0;
		$inString = false;
		$escaped = false;
		while ($offset < $length) {
			$char = $json[$offset];
			if ($inString) {
				if ($escaped) {
					$escaped = false;
				} elseif ($char === '\\') {
					$escaped = true;
				} elseif ($char === '"') {
					$inString = false;
				}
				$offset++;
				continue;
			}
			if ($char === '"') {
				$inString = true;
				$offset++;
				continue;
			}
			if ($char === '{' || $char === '[') {
				$depth++;
				$offset++;
				continue;
			}
			if ($char === '}' || $char === ']') {
				if ($depth === 0) {
					break;
				}
				$depth--;
				$offset++;
				continue;
			}
			if ($char === ',' && $depth === 0) {
				break;
			}
			$offset++;
		}
		$valueEnd = $offset;
		if ((string) $name === (string) $property) {
			$propertyStart = $nameStart;
			$propertyEnd = $valueEnd;
			$scan = $offset;
			plugin_mastodon_json_skip_ws($json, $scan);
			if ($scan < $length && $json[$scan] === ',') {
				$propertyEnd = $scan + 1;
			} else {
				$scan = $propertyStart - 1;
				while ($scan >= 0 && ($json[$scan] === ' ' || $json[$scan] === "\t" || $json[$scan] === "\r" || $json[$scan] === "\n")) {
					$scan--;
				}
				if ($scan >= 0 && $json[$scan] === ',') {
					$propertyStart = $scan;
				}
			}
			return array(
				'property_start' => $propertyStart,
				'property_end' => $propertyEnd,
				'value_start' => $valueStart,
				'value_end' => $valueEnd
			);
		}
		plugin_mastodon_json_skip_ws($json, $offset);
		if ($offset < $length && $json[$offset] === ',') {
			$offset++;
			continue;
		}
	}
	return array();
}

/**
 * Create a timestamped backup of the legacy main state before an inline-to-shard migration mutates it.
 * @return string|false Backup path on success, false on failure
 */
function plugin_mastodon_state_create_migration_backup() {
	if (!@is_file(PLUGIN_MASTODON_STATE_FILE)) {
		return '';
	}
	$dir = dirname(PLUGIN_MASTODON_STATE_FILE);
	if (!@is_dir($dir) && !@mkdir($dir, DIR_PERMISSIONS, true)) {
		return false;
	}
	$suffix = gmdate('Ymd-His') . '-' . str_replace('.', '', uniqid('', true));
	$backupFile = $dir . DIRECTORY_SEPARATOR . 'state.json.migration-backup-' . $suffix . '.json';
	if (!@copy(PLUGIN_MASTODON_STATE_FILE, $backupFile)) {
		return false;
	}
	plugin_mastodon_apply_file_permissions($backupFile);
	return $backupFile;
}

/**
 * Iterate a legacy inline comments object and persist per-entry shards without building one giant comments array.
 * @param string $commentsJson
 * @return array<string, array<string, mixed>>|false
 */
function plugin_mastodon_state_migrate_inline_comments_to_shards($commentsJson) {
	$commentsJson = trim((string) $commentsJson);
	if ($commentsJson === '' || $commentsJson === '{}') {
		return array();
	}
	$length = strlen($commentsJson);
	$offset = 0;
	plugin_mastodon_json_skip_ws($commentsJson, $offset);
	if ($offset >= $length || $commentsJson[$offset] !== '{') {
		return false;
	}
	$offset++;
	$currentEntryId = '';
	$currentComments = array();
	$entries = array();

	$flush = function () use (&$currentEntryId, &$currentComments, &$entries) {
		if (strlen((string) $currentEntryId) === 0 || count($currentComments) === 0) {
			$currentEntryId = '';
			$currentComments = array();
			return true;
		}
		$file = plugin_mastodon_state_comment_shard_file($currentEntryId);
		if ($file === '') {
			return false;
		}
		$existingComments = plugin_mastodon_state_read_comment_shard($currentEntryId);
		if ($existingComments !== array()) {
			$currentComments = array_merge($existingComments, $currentComments);
		}
		ksort($currentComments, SORT_STRING);
		$payload = array(
			'version' => 1,
			'entry_id' => $currentEntryId,
			'count' => count($currentComments),
			'updated_at' => gmdate('Y-m-d H:i:s'),
			'comments' => $currentComments
		);
		$json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		if (!is_string($json) || !plugin_mastodon_state_write_json_file($file, $json . PHP_EOL)) {
			return false;
		}
		$entries[$currentEntryId] = array(
			'path' => plugin_mastodon_state_comment_shard_relative_path($currentEntryId),
			'count' => count($currentComments),
			'mtime' => (int) @filemtime($file)
		);
		$currentEntryId = '';
		$currentComments = array();
		return true;
	};

	while ($offset < $length) {
		plugin_mastodon_json_skip_ws($commentsJson, $offset);
		if ($offset < $length && $commentsJson[$offset] === '}') {
			break;
		}
		$key = plugin_mastodon_json_decode_string_at($commentsJson, $offset);
		if ($key === null) {
			return false;
		}
		plugin_mastodon_json_skip_ws($commentsJson, $offset);
		if ($offset >= $length || $commentsJson[$offset] !== ':') {
			return false;
		}
		$offset++;
		plugin_mastodon_json_skip_ws($commentsJson, $offset);
		$valueStart = $offset;
		$depth = 0;
		$inString = false;
		$escaped = false;
		while ($offset < $length) {
			$char = $commentsJson[$offset];
			if ($inString) {
				if ($escaped) {
					$escaped = false;
				} elseif ($char === '\\') {
					$escaped = true;
				} elseif ($char === '"') {
					$inString = false;
				}
				$offset++;
				continue;
			}
			if ($char === '"') {
				$inString = true;
				$offset++;
				continue;
			}
			if ($char === '{' || $char === '[') {
				$depth++;
				$offset++;
				continue;
			}
			if ($char === '}' || $char === ']') {
				if ($depth === 0) {
					break;
				}
				$depth--;
				$offset++;
				continue;
			}
			if ($char === ',' && $depth === 0) {
				break;
			}
			$offset++;
		}
		$valueJson = substr($commentsJson, $valueStart, $offset - $valueStart);
		$meta = json_decode($valueJson, true);
		if (is_array($meta)) {
			$entryId = !empty($meta['entry_id']) ? (string) $meta['entry_id'] : plugin_mastodon_state_entry_id_from_comment_key((string) $key);
			if (plugin_mastodon_state_is_entry_id($entryId)) {
				if ($currentEntryId !== '' && $entryId !== $currentEntryId) {
					if (!$flush()) {
						return false;
					}
				}
				$currentEntryId = $entryId;
				$currentComments[(string) $key] = $meta;
			}
		}
		plugin_mastodon_json_skip_ws($commentsJson, $offset);
		if ($offset < $length && $commentsJson[$offset] === ',') {
			$offset++;
			continue;
		}
	}
	if (!$flush()) {
		return false;
	}
	ksort($entries, SORT_STRING);
	return $entries;
}

/**
 * Migrate legacy inline comment mappings to per-entry shards without decoding the high-volume comments array.
 * @param string $json
 * @return array<string, mixed>
 */
function plugin_mastodon_state_try_streaming_legacy_migration($json) {
	$commentsBounds = plugin_mastodon_json_top_level_property_bounds($json, 'comments');
	if ($commentsBounds === array()) {
		return array();
	}
	$commentsJson = substr($json, $commentsBounds['value_start'], $commentsBounds['value_end'] - $commentsBounds['value_start']);
	if (trim($commentsJson) === '{}' || trim($commentsJson) === '[]') {
		return array();
	}
	$commentsRemovedJson = substr($json, 0, $commentsBounds['value_start']) . '{}' . substr($json, $commentsBounds['value_end']);
	$main = json_decode($commentsRemovedJson, true);
	if (!is_array($main)) {
		return array();
	}
	$lock = plugin_mastodon_state_write_lock_acquire();
	if (!$lock) {
		return array();
	}
	$backupFile = plugin_mastodon_state_create_migration_backup();
	if ($backupFile === false) {
		plugin_mastodon_state_write_lock_release($lock);
		return array();
	}
	$entries = plugin_mastodon_state_migrate_inline_comments_to_shards($commentsJson);
	if ($entries === false) {
		plugin_mastodon_state_write_lock_release($lock);
		return array();
	}
	$main = plugin_mastodon_state_normalize($main);
	$main['comments'] = array();
	$main['comment_shards'] = array(
		'version' => 1,
		'storage' => 'per_entry',
		'count' => count($entries),
		'entries' => $entries
	);
	$mainJson = json_encode($main, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if (!is_string($mainJson) || !plugin_mastodon_state_write_json_file(PLUGIN_MASTODON_STATE_FILE, $mainJson . PHP_EOL)) {
		plugin_mastodon_state_write_lock_release($lock);
		return array();
	}
	plugin_mastodon_state_write_lock_release($lock);
	plugin_mastodon_runtime_cache_clear('state');
	return $main;
}

/**
 * Return the entry ids referenced by pending comment rechecks.
 * @param array<string, mixed> $state
 * @return array<int, string>
 */
function plugin_mastodon_state_pending_recheck_entry_ids($state) {
	$ids = array();
	$queue = isset($state['pending_comment_remote_rechecks']) && is_array($state['pending_comment_remote_rechecks']) ? $state['pending_comment_remote_rechecks'] : array();
	foreach ($queue as $record) {
		if (is_array($record) && !empty($record['entry_id'])) {
			$entryId = (string) $record['entry_id'];
			if (plugin_mastodon_state_is_entry_id($entryId)) {
				$ids[$entryId] = true;
			}
		}
	}
	ksort($ids, SORT_STRING);
	return array_keys($ids);
}

/**
 * Drop one loaded comment shard from a partial state's in-memory working set.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @return void
 */
function plugin_mastodon_state_unload_comment_shard_from_memory(&$state, $entryId) {
	$entryId = (string) $entryId;
	if ($entryId === '' || !isset($state['comments']) || !is_array($state['comments'])) {
		return;
	}
	foreach (array_keys($state['comments']) as $commentKey) {
		if (plugin_mastodon_state_entry_id_from_comment_key((string) $commentKey) === $entryId) {
			unset($state['comments'][$commentKey]);
		}
	}
	$loaded = array();
	foreach (plugin_mastodon_state_loaded_comment_entry_ids($state) as $loadedEntryId) {
		if ((string) $loadedEntryId !== $entryId) {
			$loaded[] = (string) $loadedEntryId;
		}
	}
	$state['__comment_shards_partial'] = true;
	$state['__comment_shard_loaded_entry_ids'] = $loaded;
}

/**
 * Group comment mappings by their parent entry id.
 * @param array<string, mixed> $state
 * @return array<string, array<string, array<string, mixed>>>
 */
function plugin_mastodon_state_group_comments_by_entry($state) {
	$groups = array();
	$comments = plugin_mastodon_state_comment_mappings($state);
	foreach ($comments as $commentKey => $meta) {
		if (!is_array($meta)) {
			continue;
		}
		$entryId = !empty($meta ['entry_id']) ? (string) $meta ['entry_id'] : plugin_mastodon_state_entry_id_from_comment_key((string) $commentKey);
		if (!plugin_mastodon_state_is_entry_id($entryId)) {
			continue;
		}
		if (!isset($groups [$entryId])) {
			$groups [$entryId] = array();
		}
		$groups [$entryId] [(string) $commentKey] = $meta;
	}
	ksort($groups, SORT_STRING);
	foreach (array_keys($groups) as $entryId) {
		ksort($groups [$entryId], SORT_STRING);
	}
	return $groups;
}

/**
 * Load one per-entry comment shard.
 * @param string $entryId
 * @return array<string, array<string, mixed>>
 */
function plugin_mastodon_state_read_comment_shard($entryId) {
	if (isset($GLOBALS ['plugin_mastodon_test_comment_shard_read_count'])) {
		$GLOBALS ['plugin_mastodon_test_comment_shard_read_count'] = (int) $GLOBALS ['plugin_mastodon_test_comment_shard_read_count'] + 1;
	}
	if (isset($GLOBALS ['plugin_mastodon_test_comment_shard_read_ids']) && is_array($GLOBALS ['plugin_mastodon_test_comment_shard_read_ids'])) {
		$GLOBALS ['plugin_mastodon_test_comment_shard_read_ids'] [] = (string) $entryId;
	}
	$file = plugin_mastodon_state_comment_shard_file($entryId);
	if ($file === '' || !@is_file($file)) {
		return array();
	}
	$json = plugin_mastodon_io_read_file_uncached($file, true);
	if (!is_string($json) || trim($json) === '') {
		return array();
	}
	$data = json_decode($json, true);
	if (!is_array($data)) {
		return array();
	}
	$comments = isset($data ['comments']) && is_array($data ['comments']) ? $data ['comments'] : array();
	$normalized = array();
	foreach ($comments as $commentKey => $meta) {
		if (is_array($meta)) {
			$normalized [(string) $commentKey] = $meta;
		}
	}
	ksort($normalized, SORT_STRING);
	return $normalized;
}

/**
 * Write all per-entry comment shards.
 * @param array<string, mixed> $state
 * @return array{ok:bool,entries:array<string, array<string, mixed>>,error:string}
 */
function plugin_mastodon_state_write_comment_shards($state) {
	$groups = plugin_mastodon_state_group_comments_by_entry($state);
	$partial = plugin_mastodon_state_comment_shards_partial($state);
	$loaded = array();
	foreach (plugin_mastodon_state_loaded_comment_entry_ids($state) as $entryId) {
		$loaded [(string) $entryId] = true;
	}
	$existingShards = plugin_mastodon_state_comment_shard_entries($state);
	$entries = $partial ? $existingShards : array();
	if (!plugin_mastodon_ensure_comment_shard_dir()) {
		return array('ok' => false, 'entries' => array(), 'error' => 'comment_shard_write_failed');
	}
	if ($partial) {
		foreach (array_keys($loaded) as $entryId) {
			if (!isset($groups [$entryId])) {
				$file = plugin_mastodon_state_comment_shard_file($entryId);
				if ($file !== '' && @is_file($file)) {
					@unlink($file);
				}
				unset($entries [$entryId]);
			}
		}
	}
	foreach ($groups as $entryId => $comments) {
		if ($partial && empty($loaded [$entryId])) {
			continue;
		}
		$file = plugin_mastodon_state_comment_shard_file($entryId);
		if ($file === '') {
			return array('ok' => false, 'entries' => array(), 'error' => 'comment_shard_file_invalid: ' . $entryId);
		}
		$payload = array(
			'version' => 1,
			'entry_id' => $entryId,
			'count' => count($comments),
			'updated_at' => gmdate('Y-m-d H:i:s'),
			'comments' => $comments
		);
		$json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		if (!is_string($json) || !plugin_mastodon_state_write_json_file($file, $json . PHP_EOL)) {
			return array('ok' => false, 'entries' => array(), 'error' => 'comment_shard_write_failed: ' . $entryId);
		}
		$entries [$entryId] = array(
			'path' => plugin_mastodon_state_comment_shard_relative_path($entryId),
			'count' => count($comments),
			'mtime' => (int) @filemtime($file)
		);
	}
	ksort($entries, SORT_STRING);
	return array('ok' => true, 'entries' => $entries, 'error' => '');
}

/**
 * Build the main state payload without high-volume inline comment mappings.
 * @param array<string, mixed> $state
 * @param array<string, array<string, mixed>> $shardEntries
 * @return array<string, mixed>
 */
function plugin_mastodon_state_main_payload($state, $shardEntries) {
	$main = $state;
	unset($main ['__comment_shards_partial'], $main ['__comment_shard_loaded_entry_ids']);
	$main ['comments'] = array();
	$main ['comment_shards'] = array(
		'version' => 1,
		'storage' => 'per_entry',
		'count' => count($shardEntries),
		'entries' => $shardEntries
	);
	return $main;
}

/**
 * Load comment shards into a state array.
 * @param array<string, mixed> $state
 * @param array<int, string>|null $entryIds
 * @return array<string, mixed>
 */
function plugin_mastodon_state_load_comment_shards($state, $entryIds = null) {
	if (!isset($state ['comments']) || !is_array($state ['comments'])) {
		plugin_mastodon_state_set_comment_mappings($state, array());
	}
	$entries = plugin_mastodon_state_comment_shard_entries($state);
	$partial = is_array($entryIds);
	$wanted = array();
	if ($partial) {
		foreach ($entryIds as $entryId) {
			$entryId = (string) $entryId;
			if ($entryId !== '') {
				$wanted [$entryId] = true;
			}
		}
		ksort($wanted, SORT_STRING);
		$state ['__comment_shards_partial'] = true;
		$state ['__comment_shard_loaded_entry_ids'] = array_keys($wanted);
	}
	if ($entries === array()) {
		return $state;
	}
	foreach ($entries as $entryId => $meta) {
		$entryId = (string) $entryId;
		if ($partial && empty($wanted [$entryId])) {
			continue;
		}
		$comments = plugin_mastodon_state_read_comment_shard($entryId);
		foreach ($comments as $commentKey => $commentMeta) {
			$state ['comments'] [$commentKey] = $commentMeta;
		}
	}
	ksort($state ['comments'], SORT_STRING);
	return $state;
}

/**
 * Return whether a state array only contains a subset of comment shards.
 * @param array<string, mixed> $state
 * @return bool
 */
function plugin_mastodon_state_comment_shards_partial($state) {
	return !empty($state ['__comment_shards_partial']);
}

/**
 * Return the entry ids whose comment shards are loaded in a partial state.
 * @param array<string, mixed> $state
 * @return array<int, string>
 */
function plugin_mastodon_state_loaded_comment_entry_ids($state) {
	$loaded = isset($state ['__comment_shard_loaded_entry_ids']) && is_array($state ['__comment_shard_loaded_entry_ids']) ? $state ['__comment_shard_loaded_entry_ids'] : array();
	$ids = array();
	foreach ($loaded as $entryId) {
		$entryId = (string) $entryId;
		if ($entryId !== '') {
			$ids [$entryId] = true;
		}
	}
	ksort($ids, SORT_STRING);
	return array_keys($ids);
}

/**
 * Check whether one entry's comment shard is loaded into the state array.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @return bool
 */
function plugin_mastodon_state_comment_entry_loaded($state, $entryId) {
	if (!plugin_mastodon_state_comment_shards_partial($state)) {
		return true;
	}
	$entryId = (string) $entryId;
	return $entryId !== '' && in_array($entryId, plugin_mastodon_state_loaded_comment_entry_ids($state), true);
}

/**
 * Mark one entry's comment shard as loaded in a partial state.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @return void
 */
function plugin_mastodon_state_mark_comment_entry_loaded(&$state, $entryId) {
	$entryId = (string) $entryId;
	if ($entryId === '') {
		return;
	}
	$loaded = plugin_mastodon_state_loaded_comment_entry_ids($state);
	if (!in_array($entryId, $loaded, true)) {
		$loaded [] = $entryId;
		sort($loaded, SORT_STRING);
	}
	$state ['__comment_shards_partial'] = true;
	$state ['__comment_shard_loaded_entry_ids'] = $loaded;
}

/**
 * Load one entry comment shard into a partial state when needed.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @return void
 */
function plugin_mastodon_state_load_comment_shard_into(&$state, $entryId) {
	$entryId = (string) $entryId;
	if ($entryId === '' || plugin_mastodon_state_comment_entry_loaded($state, $entryId)) {
		return;
	}
	if (!isset($state ['comments']) || !is_array($state ['comments'])) {
		plugin_mastodon_state_set_comment_mappings($state, array());
	}
	$comments = plugin_mastodon_state_read_comment_shard($entryId);
	foreach ($comments as $commentKey => $commentMeta) {
		$state ['comments'] [(string) $commentKey] = $commentMeta;
	}
	ksort($state ['comments'], SORT_STRING);
	plugin_mastodon_state_mark_comment_entry_loaded($state, $entryId);
}

/**
 * Return entry ids with known comment shards.
 * @param array<string, mixed> $state
 * @return array<int, string>
 */
function plugin_mastodon_state_comment_shard_entry_ids($state) {
	$entries = plugin_mastodon_state_comment_shard_entries($state);
	$ids = array();
	foreach (array_keys($entries) as $entryId) {
		$entryId = (string) $entryId;
		if ($entryId !== '') {
			$ids [$entryId] = true;
		}
	}
	ksort($ids, SORT_STRING);
	return array_keys($ids);
}

/**
 * Return all comment shard files currently present on disk keyed by entry id.
 * @return array<string, string>
 */
function plugin_mastodon_state_comment_shard_files() {
	$filesByEntry = array();
	$dir = PLUGIN_MASTODON_COMMENT_SHARD_DIR;
	if (!@is_dir($dir)) {
		return $filesByEntry;
	}
	$years = @scandir($dir);
	if (!is_array($years)) {
		return $filesByEntry;
	}
	foreach ($years as $year) {
		if ($year === '.' || $year === '..') {
			continue;
		}
		$yearDir = $dir . $year . DIRECTORY_SEPARATOR;
		if (!@is_dir($yearDir)) {
			continue;
		}
		$months = @scandir($yearDir);
		if (!is_array($months)) {
			continue;
		}
		foreach ($months as $month) {
			if ($month === '.' || $month === '..') {
				continue;
			}
			$monthDir = $yearDir . $month . DIRECTORY_SEPARATOR;
			if (!@is_dir($monthDir)) {
				continue;
			}
			$files = @scandir($monthDir);
			if (!is_array($files)) {
				continue;
			}
			foreach ($files as $fileName) {
				if (!preg_match('/^(entry[0-9]{6}-[0-9]{6})\.json$/', $fileName, $match)) {
					continue;
				}
				$entryId = (string) $match [1];
				$filesByEntry [$entryId] = $monthDir . $fileName;
			}
		}
	}
	ksort($filesByEntry, SORT_STRING);
	return $filesByEntry;
}

/**
 * Scan shard files and compare them with the main-state metadata and reverse comment index.
 * @param array<string, mixed>|null $state
 * @return array<string, mixed>
 */
function plugin_mastodon_state_diagnose_comment_shards($state = null) {
	if ($state === null) {
		$state = plugin_mastodon_state_read(array());
	} else {
		$state = plugin_mastodon_state_normalize(is_array($state) ? $state : array());
	}
	$errors = array();
	$warnings = array();
	$entries = array();
	$rebuiltRemote = array();
	$knownShards = plugin_mastodon_state_comment_shard_entries($state);
	$filesByEntry = plugin_mastodon_state_comment_shard_files();
	$commentCount = 0;

	foreach ($filesByEntry as $entryId => $file) {
		$json = plugin_mastodon_io_read_file_uncached($file, true);
		if (!is_string($json) || trim($json) === '') {
			$errors [] = 'empty shard: ' . $entryId;
			continue;
		}
		$data = json_decode($json, true);
		if (!is_array($data)) {
			$errors [] = 'invalid shard JSON: ' . $entryId;
			continue;
		}
		if (isset($data ['entry_id']) && (string) $data ['entry_id'] !== (string) $entryId) {
			$errors [] = 'entry_id mismatch in shard: ' . $entryId;
			continue;
		}
		$comments = isset($data ['comments']) && is_array($data ['comments']) ? $data ['comments'] : array();
		if (isset($data ['count']) && (int) $data ['count'] !== count($comments)) {
			$warnings [] = 'count mismatch in shard: ' . $entryId;
		}
		foreach ($comments as $commentKey => $meta) {
			if (!is_array($meta)) {
				$warnings [] = 'non-array comment metadata in shard: ' . $entryId . '/' . (string) $commentKey;
				continue;
			}
			$commentEntryId = !empty($meta ['entry_id']) ? (string) $meta ['entry_id'] : plugin_mastodon_state_entry_id_from_comment_key((string) $commentKey);
			if ($commentEntryId !== (string) $entryId) {
				$warnings [] = 'comment entry mismatch in shard: ' . $entryId . '/' . (string) $commentKey;
			}
			$commentId = !empty($meta ['comment_id']) ? (string) $meta ['comment_id'] : '';
			if ($commentId === '') {
				$keyParts = explode(':', (string) $commentKey, 2);
				$commentId = isset($keyParts [1]) ? (string) $keyParts [1] : '';
			}
			$remoteId = !empty($meta ['remote_id']) ? (string) $meta ['remote_id'] : '';
			if ($remoteId !== '' && $commentId !== '') {
				$rebuiltRemote [$remoteId] = array(
					'entry_id' => (string) $entryId,
					'comment_id' => $commentId
				);
			}
			$commentCount++;
		}
		$entries [$entryId] = array(
			'path' => plugin_mastodon_state_comment_shard_relative_path($entryId),
			'count' => count($comments),
			'mtime' => (int) @filemtime($file)
		);
		if (!isset($knownShards [$entryId])) {
			$warnings [] = 'shard file missing from main metadata: ' . $entryId;
		}
	}

	foreach (array_keys($knownShards) as $entryId) {
		$entryId = (string) $entryId;
		if ($entryId !== '' && !isset($filesByEntry [$entryId])) {
			$warnings [] = 'main metadata references missing shard: ' . $entryId;
		}
	}

	$currentRemote = plugin_mastodon_state_comment_remote_mappings($state);
	foreach ($currentRemote as $remoteId => $ref) {
		$remoteId = (string) $remoteId;
		if (!is_array($ref)) {
			$warnings [] = 'non-array comments_remote reference: ' . $remoteId;
			continue;
		}
		if (!isset($rebuiltRemote [$remoteId])) {
			$warnings [] = 'comments_remote reference without shard comment: ' . $remoteId;
			continue;
		}
		$expected = $rebuiltRemote [$remoteId];
		$currentEntryId = isset($ref ['entry_id']) ? (string) $ref ['entry_id'] : '';
		$currentCommentId = isset($ref ['comment_id']) ? (string) $ref ['comment_id'] : '';
		if ($currentEntryId !== (string) $expected ['entry_id'] || $currentCommentId !== (string) $expected ['comment_id']) {
			$warnings [] = 'comments_remote reference mismatch: ' . $remoteId;
		}
	}

	ksort($entries, SORT_STRING);
	ksort($rebuiltRemote, SORT_STRING);
	return array(
		'ok' => $errors === array() && $warnings === array(),
		'errors' => $errors,
		'warnings' => $warnings,
		'entries' => $entries,
		'comments_remote' => $rebuiltRemote,
		'stats' => array(
			'shard_files' => count($filesByEntry),
			'shard_metadata_entries' => count($knownShards),
			'comments' => $commentCount,
			'comments_remote' => count($rebuiltRemote)
		)
	);
}

/**
 * Rebuild the main shard metadata and global comments_remote reverse index from shard files.
 * @param array<string, mixed>|null $state
 * @param bool $persist
 * @return array<string, mixed>
 */
function plugin_mastodon_state_repair_comment_shards($state = null, $persist = false) {
	if ($state === null) {
		$state = plugin_mastodon_state_read(array());
	} else {
		$state = plugin_mastodon_state_normalize(is_array($state) ? $state : array());
	}
	$diagnostics = plugin_mastodon_state_diagnose_comment_shards($state);
	$diagnostics ['repaired'] = false;
	if (!$persist || !empty($diagnostics ['errors'])) {
		return $diagnostics;
	}
	$lockHandle = plugin_mastodon_state_write_lock_acquire();
	if (!$lockHandle) {
		$diagnostics ['errors'] [] = 'state-write lock unavailable';
		return $diagnostics;
	}
	try {
		plugin_mastodon_state_set_comment_mappings($state, array());
		plugin_mastodon_state_set_comment_remote_mappings($state, isset($diagnostics ['comments_remote']) && is_array($diagnostics ['comments_remote']) ? $diagnostics ['comments_remote'] : array());
		$entries = isset($diagnostics ['entries']) && is_array($diagnostics ['entries']) ? $diagnostics ['entries'] : array();
		$mainState = plugin_mastodon_state_main_payload($state, $entries);
		$json = json_encode($mainState, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		if (is_string($json) && plugin_mastodon_state_write_json_file(PLUGIN_MASTODON_STATE_FILE, $json . PHP_EOL)) {
			$diagnostics ['repaired'] = true;
			plugin_mastodon_runtime_cache_clear('state');
			plugin_mastodon_scheduler_state_write(plugin_mastodon_scheduler_state_from_state($mainState));
		} else {
			$diagnostics ['errors'] [] = 'main state repair write failed';
		}
	} finally {
		plugin_mastodon_state_write_lock_release($lockHandle);
	}
	return $diagnostics;
}

/**
 * Remove shard files that are no longer referenced by the main state.
 * @param array<string, array<string, mixed>> $referencedEntries
 * @return void
 */
function plugin_mastodon_state_cleanup_stale_comment_shards($referencedEntries) {
	$dir = PLUGIN_MASTODON_COMMENT_SHARD_DIR;
	if (!@is_dir($dir)) {
		return;
	}
	$referenced = array();
	foreach (array_keys($referencedEntries) as $entryId) {
		$file = plugin_mastodon_state_comment_shard_file($entryId);
		if ($file !== '') {
			$referenced [$file] = true;
		}
	}
	$years = @scandir($dir);
	if (!is_array($years)) {
		return;
	}
	foreach ($years as $year) {
		if ($year === '.' || $year === '..') {
			continue;
		}
		$yearDir = $dir . $year . DIRECTORY_SEPARATOR;
		if (!@is_dir($yearDir)) {
			continue;
		}
		$months = @scandir($yearDir);
		if (!is_array($months)) {
			continue;
		}
		foreach ($months as $month) {
			if ($month === '.' || $month === '..') {
				continue;
			}
			$monthDir = $yearDir . $month . DIRECTORY_SEPARATOR;
			if (!@is_dir($monthDir)) {
				continue;
			}
			$files = @scandir($monthDir);
			if (!is_array($files)) {
				continue;
			}
			foreach ($files as $fileName) {
				if ($fileName === '.' || $fileName === '..' || substr($fileName, -5) !== '.json') {
					continue;
				}
				$file = $monthDir . $fileName;
				if (empty($referenced [$file])) {
					@unlink($file);
				}
			}
		}
	}
}

/**
 * Return a recovered state if comment shards were written but the main state is missing.
 * @return array<string, mixed>
 */
function plugin_mastodon_state_recover_from_comment_shards() {
	$state = plugin_mastodon_default_state();
	$entries = array();
	$dir = PLUGIN_MASTODON_COMMENT_SHARD_DIR;
	if (!@is_dir($dir)) {
		return $state;
	}
	$years = @scandir($dir);
	if (!is_array($years)) {
		return $state;
	}
	foreach ($years as $year) {
		if ($year === '.' || $year === '..') {
			continue;
		}
		$yearDir = $dir . $year . DIRECTORY_SEPARATOR;
		if (!@is_dir($yearDir)) {
			continue;
		}
		$months = @scandir($yearDir);
		if (!is_array($months)) {
			continue;
		}
		foreach ($months as $month) {
			if ($month === '.' || $month === '..') {
				continue;
			}
			$monthDir = $yearDir . $month . DIRECTORY_SEPARATOR;
			if (!@is_dir($monthDir)) {
				continue;
			}
			$files = @scandir($monthDir);
			if (!is_array($files)) {
				continue;
			}
			foreach ($files as $fileName) {
				if (!preg_match('/^(entry[0-9]{6}-[0-9]{6})\.json$/', $fileName, $match)) {
					continue;
				}
				$entryId = (string) $match [1];
				$entries [$entryId] = array(
					'path' => plugin_mastodon_state_comment_shard_relative_path($entryId),
					'count' => count(plugin_mastodon_state_read_comment_shard($entryId)),
					'mtime' => (int) @filemtime($monthDir . $fileName)
				);
			}
		}
	}
	if ($entries === array()) {
		return $state;
	}
	$state ['comment_shards'] = array(
		'version' => 1,
		'storage' => 'per_entry',
		'count' => count($entries),
		'entries' => $entries
	);
	return plugin_mastodon_state_load_comment_shards($state);
}

/**
 * Load the persisted runtime state from disk.
 * @return array<string, mixed>
 */
function plugin_mastodon_state_read($commentEntryIds = null) {
	plugin_mastodon_ensure_state_dir();
	$defaults = plugin_mastodon_default_state();
	$prestat = plugin_mastodon_file_prestat(PLUGIN_MASTODON_STATE_FILE);
	if (empty($prestat ['exists'])) {
		plugin_mastodon_runtime_cache_clear('state');
		return plugin_mastodon_state_normalize(plugin_mastodon_state_recover_from_comment_shards());
	}

	$signature = plugin_mastodon_file_prestat_signature($prestat);
	$cacheSuffix = is_array($commentEntryIds) ? ('subset:' . md5(implode('|', array_map('strval', $commentEntryIds)))) : 'full';
	$cached = plugin_mastodon_runtime_cache_get('state', $signature . '|' . $cacheSuffix, $hit);
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
	$migrated = plugin_mastodon_state_try_streaming_legacy_migration($json);
	if ($migrated !== array()) {
		$state = plugin_mastodon_state_normalize($migrated);
	} else {
		$data = json_decode($json, true);
		if (!is_array($data)) {
			return $defaults;
		}
		$state = plugin_mastodon_state_normalize($data);
	}
	$state = plugin_mastodon_state_load_comment_shards($state, is_array($commentEntryIds) ? $commentEntryIds : null);
	plugin_mastodon_runtime_cache_set('state', '__signature__', $signature);
	plugin_mastodon_runtime_cache_set('state', $signature . '|' . $cacheSuffix, $state);
	return $state;
}

/**
 * Return an empty scheduler state derived from the full default state.
 * @return array<string, mixed>
 */
function plugin_mastodon_scheduler_state_default() {
	$defaults = plugin_mastodon_default_state();
	return array(
		'version' => (int) $defaults ['version'],
		'last_run' => '',
		'last_deletion_run' => '',
		'deletions_pending' => 0,
		'deletions_pending_scope' => $defaults ['deletions_pending_scope'],
		'deletions_not_before' => '',
		'deletion_retry_not_before' => '',
		'local_entry_deletion_not_before' => '',
		'last_error' => '',
		'content_stats' => $defaults ['content_stats'],
		'deletion_stats' => $defaults ['deletion_stats'],
		'source_state_signature' => '',
		'updated_at' => ''
	);
}

/**
 * Return the current stat-based signature of the full state file.
 * @return string
 */
function plugin_mastodon_scheduler_source_signature() {
	return plugin_mastodon_file_prestat_signature(plugin_mastodon_file_prestat(PLUGIN_MASTODON_STATE_FILE));
}

/**
 * Normalize a scheduler summary without touching full mapping arrays.
 * @param array<string, mixed> $state
 * @return array<string, mixed>
 */
function plugin_mastodon_scheduler_state_normalize($state) {
	$defaults = plugin_mastodon_scheduler_state_default();
	$input = is_array($state) ? $state : array();
	$normalized = array_merge($defaults, $input);
	$normalized ['version'] = (int) (isset($normalized ['version']) ? $normalized ['version'] : $defaults ['version']);
	$normalized ['last_run'] = isset($normalized ['last_run']) ? (string) $normalized ['last_run'] : '';
	$normalized ['last_deletion_run'] = isset($normalized ['last_deletion_run']) ? (string) $normalized ['last_deletion_run'] : '';
	$normalized ['deletions_pending'] = !empty($normalized ['deletions_pending']) ? 1 : 0;
	$normalized ['deletions_pending_scope'] = plugin_mastodon_normalize_deletions_pending_scope(isset($normalized ['deletions_pending_scope']) ? $normalized ['deletions_pending_scope'] : $defaults ['deletions_pending_scope']);
	$normalized ['deletions_not_before'] = plugin_mastodon_parse_iso_datetime(isset($normalized ['deletions_not_before']) ? (string) $normalized ['deletions_not_before'] : '');
	$normalized ['deletion_retry_not_before'] = plugin_mastodon_parse_iso_datetime(isset($normalized ['deletion_retry_not_before']) ? (string) $normalized ['deletion_retry_not_before'] : '');
	$normalized ['local_entry_deletion_not_before'] = plugin_mastodon_parse_iso_datetime(isset($normalized ['local_entry_deletion_not_before']) ? (string) $normalized ['local_entry_deletion_not_before'] : '');
	$normalized ['last_error'] = isset($normalized ['last_error']) ? (string) $normalized ['last_error'] : '';
	$normalized ['source_state_signature'] = isset($normalized ['source_state_signature']) ? (string) $normalized ['source_state_signature'] : '';
	$normalized ['updated_at'] = isset($normalized ['updated_at']) ? (string) $normalized ['updated_at'] : '';

	$contentStats = isset($input ['content_stats']) && is_array($input ['content_stats']) ? $input ['content_stats'] : array();
	$normalized ['content_stats'] = $defaults ['content_stats'];
	foreach (array_keys($defaults ['content_stats']) as $key) {
		if (isset($contentStats [$key])) {
			$normalized ['content_stats'] [$key] = (int) $contentStats [$key];
		}
	}

	$deletionStats = isset($input ['deletion_stats']) && is_array($input ['deletion_stats']) ? $input ['deletion_stats'] : array();
	$normalized ['deletion_stats'] = $defaults ['deletion_stats'];
	foreach (array_keys($defaults ['deletion_stats']) as $key) {
		if (isset($deletionStats [$key])) {
			$normalized ['deletion_stats'] [$key] = (int) $deletionStats [$key];
		}
	}

	return $normalized;
}

/**
 * Build the lightweight scheduler summary from a full runtime state.
 * @param array<string, mixed> $state
 * @return array<string, mixed>
 */
function plugin_mastodon_scheduler_state_from_state($state) {
	$state = plugin_mastodon_state_normalize(is_array($state) ? $state : array());
	return plugin_mastodon_scheduler_state_normalize(array(
		'version' => $state ['version'],
		'last_run' => $state ['last_run'],
		'last_deletion_run' => $state ['last_deletion_run'],
		'deletions_pending' => $state ['deletions_pending'],
		'deletions_pending_scope' => $state ['deletions_pending_scope'],
		'deletions_not_before' => $state ['deletions_not_before'],
		'deletion_retry_not_before' => $state ['deletion_retry_not_before'],
		'local_entry_deletion_not_before' => plugin_mastodon_state_local_entry_deletion_deadline($state),
		'last_error' => $state ['last_error'],
		'content_stats' => $state ['content_stats'],
		'deletion_stats' => $state ['deletion_stats']
	));
}

/**
 * Persist the lightweight scheduler state. Failure only disables the request-time optimization.
 * @param array<string, mixed> $state
 * @return bool
 */
function plugin_mastodon_scheduler_state_write($state) {
	plugin_mastodon_ensure_state_dir();
	$schedulerState = plugin_mastodon_scheduler_state_normalize(is_array($state) ? $state : array());
	$schedulerState ['source_state_signature'] = plugin_mastodon_scheduler_source_signature();
	$schedulerState ['updated_at'] = gmdate('Y-m-d H:i:s');
	$json = json_encode($schedulerState, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if (!is_string($json)) {
		return false;
	}
	$written = plugin_mastodon_io_write_file(PLUGIN_MASTODON_SCHEDULER_STATE_FILE, $json . PHP_EOL);
	plugin_mastodon_runtime_cache_clear('scheduler_state');
	if ($written) {
		$prestat = plugin_mastodon_file_prestat(PLUGIN_MASTODON_SCHEDULER_STATE_FILE);
		$signature = plugin_mastodon_file_prestat_signature($prestat) . '|' . $schedulerState ['source_state_signature'];
		plugin_mastodon_runtime_cache_set('scheduler_state', '__signature__', $signature);
		plugin_mastodon_runtime_cache_set('scheduler_state', $signature, $schedulerState);
	}
	return $written;
}

/**
 * Decode a scheduler-state JSON payload only when it matches the current full-state signature.
 * @param string|false|null $json
 * @param string $sourceSignature
 * @return array<string, mixed>
 */
function plugin_mastodon_scheduler_state_decode_fresh($json, $sourceSignature) {
	if (!is_string($json) || trim($json) === '') {
		return array();
	}
	$data = json_decode($json, true);
	if (!is_array($data)) {
		return array();
	}
	$schedulerState = plugin_mastodon_scheduler_state_normalize($data);
	if ($schedulerState ['source_state_signature'] !== (string) $sourceSignature) {
		return array();
	}
	return $schedulerState;
}

/**
 * Load the lightweight scheduler summary and rebuild it conservatively when stale.
 * @return array<string, mixed>
 */
function plugin_mastodon_scheduler_state_read() {
	plugin_mastodon_ensure_state_dir();
	$sourceSignature = plugin_mastodon_scheduler_source_signature();
	$prestat = plugin_mastodon_file_prestat(PLUGIN_MASTODON_SCHEDULER_STATE_FILE);
	if (!empty($prestat ['exists'])) {
		$signature = plugin_mastodon_file_prestat_signature($prestat) . '|' . $sourceSignature;
		$cached = plugin_mastodon_runtime_cache_get('scheduler_state', $signature, $hit);
		if ($hit && is_array($cached)) {
			return plugin_mastodon_scheduler_state_normalize($cached);
		}

		$legacySignature = plugin_mastodon_runtime_cache_get('scheduler_state', '__signature__', $legacyHit);
		if ($legacyHit && $legacySignature !== $signature) {
			plugin_mastodon_runtime_cache_clear('scheduler_state');
		}

		$json = plugin_mastodon_io_read_file(PLUGIN_MASTODON_SCHEDULER_STATE_FILE, $prestat);
		$schedulerState = plugin_mastodon_scheduler_state_decode_fresh($json, $sourceSignature);
		if ($schedulerState !== array()) {
			plugin_mastodon_runtime_cache_set('scheduler_state', '__signature__', $signature);
			plugin_mastodon_runtime_cache_set('scheduler_state', $signature, $schedulerState);
			return $schedulerState;
		}

		$uncachedJson = plugin_mastodon_io_read_file_uncached(PLUGIN_MASTODON_SCHEDULER_STATE_FILE, true);
		if ($uncachedJson !== $json) {
			$schedulerState = plugin_mastodon_scheduler_state_decode_fresh($uncachedJson, $sourceSignature);
			if ($schedulerState !== array()) {
				plugin_mastodon_runtime_cache_set('scheduler_state', '__signature__', $signature);
				plugin_mastodon_runtime_cache_set('scheduler_state', $signature, $schedulerState);
				return $schedulerState;
			}
		}
	}

	$state = plugin_mastodon_state_read(array());
	$schedulerState = plugin_mastodon_scheduler_state_from_state($state);
	plugin_mastodon_scheduler_state_write($schedulerState);
	return $schedulerState;
}

/**
 * Persist the runtime state to disk.
 * @param array<string, mixed> $state
 * @param bool $preserveLocalEntryDeletions
 * @return bool
 */
function plugin_mastodon_state_write($state, $preserveLocalEntryDeletions = false) {
	plugin_mastodon_ensure_state_dir();
	plugin_mastodon_state_write_error_clear();
	$lockHandle = plugin_mastodon_state_write_lock_acquire();
	if (!$lockHandle) {
		plugin_mastodon_state_write_error_set('state_write_lock_unavailable');
		plugin_mastodon_log('State write skipped because the state-write lock could not be acquired');
		return false;
	}
	$written = false;
	try {
		$state = plugin_mastodon_state_normalize($state);
		if ($preserveLocalEntryDeletions && is_file(PLUGIN_MASTODON_STATE_FILE)) {
			// A delete hook may have committed after this content run read its initial state.
			$currentJson = @file_get_contents(PLUGIN_MASTODON_STATE_FILE);
			$current = is_string($currentJson) ? json_decode($currentJson, true) : null;
			if (!is_array($current)) {
				plugin_mastodon_state_write_error_set('state_local_deletion_merge_failed');
				return false;
			}
			$currentQueue = isset($current ['local_entry_deletions']) && is_array($current ['local_entry_deletions']) ? $current ['local_entry_deletions'] : array();
			foreach ($currentQueue as $entryId => $deadline) {
				$deadline = is_string($deadline) ? plugin_mastodon_parse_iso_datetime($deadline) : '';
				if ($deadline === '' || empty($state ['entries'] [$entryId] ['remote_id']) || (string) $state ['entries'] [$entryId] ['remote_id'] !== (isset($current ['entries'] [$entryId] ['remote_id']) ? (string) $current ['entries'] [$entryId] ['remote_id'] : '') || entry_exists((string) $entryId)) {
					continue;
				}
				if (empty($state ['local_entry_deletions'] [$entryId]) || strcmp($deadline, (string) $state ['local_entry_deletions'] [$entryId]) < 0) {
					$state ['local_entry_deletions'] [(string) $entryId] = $deadline;
				}
			}
			$currentRetry = plugin_mastodon_parse_iso_datetime(isset($current ['deletion_retry_not_before']) ? (string) $current ['deletion_retry_not_before'] : '');
			if (strcmp($currentRetry, $state ['deletion_retry_not_before']) > 0) {
				$state ['deletion_retry_not_before'] = $currentRetry;
			}
			if (plugin_mastodon_state_local_entry_deletion_deadline($state) !== '' && (!empty($state ['deletions_pending']) || !empty($current ['deletions_pending']))) {
				plugin_mastodon_state_set_deletions_pending($state, true, 'full', plugin_mastodon_state_local_entry_deletion_deadline($state));
			}
		}
		$shardWrite = plugin_mastodon_state_write_comment_shards($state);
		if (empty($shardWrite ['ok'])) {
			$error = !empty($shardWrite ['error']) ? (string) $shardWrite ['error'] : 'comment_shard_write_failed';
			plugin_mastodon_state_write_error_set($error);
			plugin_mastodon_log('State write failed while writing comment shards: ' . $error);
			return false;
		}
		$shardEntries = isset($shardWrite ['entries']) && is_array($shardWrite ['entries']) ? $shardWrite ['entries'] : array();
		$mainState = plugin_mastodon_state_main_payload($state, $shardEntries);
		$json = json_encode($mainState, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		if (!is_string($json)) {
			plugin_mastodon_state_write_error_set('state_json_encode_failed');
			plugin_mastodon_log('State write failed because the main state could not be JSON-encoded');
			return false;
		}
		$payload = $json . PHP_EOL;
		$written = plugin_mastodon_state_write_json_file(PLUGIN_MASTODON_STATE_FILE, $payload);
		plugin_mastodon_runtime_cache_clear('state');
		if ($written) {
			plugin_mastodon_state_write_error_clear();
			if (!plugin_mastodon_state_comment_shards_partial($state)) {
				plugin_mastodon_state_cleanup_stale_comment_shards($shardEntries);
			}
			$prestat = plugin_mastodon_file_prestat(PLUGIN_MASTODON_STATE_FILE);
			$signature = plugin_mastodon_file_prestat_signature($prestat);
			plugin_mastodon_runtime_cache_set('state', '__signature__', $signature);
			if (!plugin_mastodon_state_comment_shards_partial($state)) {
				plugin_mastodon_runtime_cache_set('state', $signature . '|full', $state);
			}
			plugin_mastodon_scheduler_state_write(plugin_mastodon_scheduler_state_from_state($state));
		} else {
			plugin_mastodon_state_write_error_set('state_main_write_failed');
			plugin_mastodon_log('State write failed while replacing the main state file');
		}
	} finally {
		plugin_mastodon_state_write_lock_release($lockHandle);
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
	foreach (array('entries', 'entries_remote', 'comments', 'comments_remote', 'comment_shards', 'dirty_entries', 'dirty_comments', 'comment_tombstones', 'comment_reply_optins', 'pending_comment_remote_rechecks') as $key) {
		if (!isset($state [$key]) || !is_array($state [$key])) {
			$state [$key] = $defaults [$key];
		}
	}
	$state ['version'] = (int) (isset($state ['version']) ? $state ['version'] : $defaults ['version']);
	$state ['last_run'] = isset($state ['last_run']) ? (string) $state ['last_run'] : '';
	$state ['last_deletion_run'] = isset($state ['last_deletion_run']) ? (string) $state ['last_deletion_run'] : '';
	$state ['deletions_pending'] = !empty($state ['deletions_pending']) ? 1 : 0;
	$state ['deletions_pending_scope'] = plugin_mastodon_normalize_deletions_pending_scope(isset($state ['deletions_pending_scope']) ? $state ['deletions_pending_scope'] : $defaults ['deletions_pending_scope']);
	$state ['deletions_not_before'] = plugin_mastodon_parse_iso_datetime(isset($state ['deletions_not_before']) ? (string) $state ['deletions_not_before'] : '');
	$state ['deletion_retry_not_before'] = plugin_mastodon_parse_iso_datetime(isset($state ['deletion_retry_not_before']) ? (string) $state ['deletion_retry_not_before'] : '');
	$localEntryDeletions = isset($state ['local_entry_deletions']) && is_array($state ['local_entry_deletions']) ? $state ['local_entry_deletions'] : array();
	$state ['local_entry_deletions'] = array();
	foreach ($localEntryDeletions as $entryId => $deadline) {
		$deadline = is_string($deadline) ? plugin_mastodon_parse_iso_datetime($deadline) : '';
		if ($deadline !== '' && isset($state ['entries'] [$entryId]) && is_array($state ['entries'] [$entryId]) && !empty($state ['entries'] [$entryId] ['remote_id'])) {
			$state ['local_entry_deletions'] [(string) $entryId] = $deadline;
		}
	}
	$state ['last_error'] = isset($state ['last_error']) ? (string) $state ['last_error'] : '';
	$state ['last_remote_status_id'] = isset($state ['last_remote_status_id']) ? (string) $state ['last_remote_status_id'] : '';
	$state ['last_remote_notification_id'] = isset($state ['last_remote_notification_id']) ? (string) $state ['last_remote_notification_id'] : '';
	$state ['old_thread_context_cursor'] = isset($state ['old_thread_context_cursor']) ? (string) $state ['old_thread_context_cursor'] : '';
	$state ['deletion_cursor_entries'] = isset($state ['deletion_cursor_entries']) ? (string) $state ['deletion_cursor_entries'] : '';
	$state ['deletion_cursor_comments'] = isset($state ['deletion_cursor_comments']) ? (string) $state ['deletion_cursor_comments'] : '';
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
 * Return normalized comment-to-reply opt-in markers from state.
 * @param array<string, mixed> $state
 * @return array<string, array<string, string>>
 */
function plugin_mastodon_state_comment_reply_optins($state) {
	$optins = isset($state ['comment_reply_optins']) && is_array($state ['comment_reply_optins']) ? $state ['comment_reply_optins'] : array();
	$result = array();
	foreach ($optins as $commentKey => $meta) {
		$commentKey = (string) $commentKey;
		if ($commentKey === ':') {
			continue;
		}
		if (is_array($meta)) {
			$entryId = isset($meta ['entry_id']) ? (string) $meta ['entry_id'] : plugin_mastodon_state_entry_id_from_comment_key($commentKey);
			$commentId = isset($meta ['comment_id']) ? (string) $meta ['comment_id'] : '';
			if ($commentId === '' && strpos($commentKey, ':') !== false) {
				$parts = explode(':', $commentKey, 2);
				$commentId = isset($parts [1]) ? (string) $parts [1] : '';
			}
			if ($entryId !== '' && $commentId !== '') {
				$pendingFile = isset($meta ['pending_file']) ? basename(str_replace('\\', '/', (string) $meta ['pending_file'])) : '';
				$result [$commentKey] = array(
					'entry_id' => $entryId,
					'comment_id' => $commentId,
					'granted_at' => isset($meta ['granted_at']) ? (string) $meta ['granted_at'] : '',
					'source' => isset($meta ['source']) ? (string) $meta ['source'] : 'frontend',
					'pending_file' => $pendingFile
				);
			}
		} elseif (!empty($meta)) {
			$entryId = plugin_mastodon_state_entry_id_from_comment_key($commentKey);
			$commentId = '';
			if (strpos($commentKey, ':') !== false) {
				$parts = explode(':', $commentKey, 2);
				$commentId = isset($parts [1]) ? (string) $parts [1] : '';
			}
			if ($entryId !== '' && $commentId !== '') {
				$result [$commentKey] = array(
					'entry_id' => $entryId,
					'comment_id' => $commentId,
					'granted_at' => '',
					'source' => 'frontend',
					'pending_file' => ''
				);
			}
		}
	}
	ksort($result, SORT_STRING);
	return $result;
}

/**
 * Persist a visitor opt-in marker for later local-comment export.
 *
 * CommentCenter can attach the basename of its pending file. The pending marker
 * is cleared when the final FlatPress comment is saved. Keeping only the
 * basename avoids host-specific absolute paths in state.json.
 *
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @param string $grantedAt
 * @param string $source
 * @param string $pendingFile
 * @return void
 */
function plugin_mastodon_state_set_comment_reply_optin(&$state, $entryId, $commentId, $grantedAt = '', $source = 'frontend', $pendingFile = '') {
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	if ($key === ':') {
		return;
	}
	$source = trim((string) $source);
	if ($source === '') {
		$source = 'frontend';
	}
	$pendingFile = basename(str_replace('\\', '/', trim((string) $pendingFile)));
	if ($pendingFile !== '' && !preg_match('/^eentry[0-9]{6}-[0-9]{6}_ccomment[0-9]{6}-[0-9]{6}\.txt$/', $pendingFile)) {
		$pendingFile = '';
	}
	$optins = plugin_mastodon_state_comment_reply_optins($state);
	$optins [$key] = array(
		'entry_id' => (string) $entryId,
		'comment_id' => (string) $commentId,
		'granted_at' => $grantedAt !== '' ? (string) $grantedAt : gmdate('Y-m-d H:i:s'),
		'source' => $source,
		'pending_file' => $pendingFile
	);
	$state ['comment_reply_optins'] = $optins;
}

/**
 * Mark a CommentCenter opt-in grant as belonging to a final FlatPress comment.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @return bool
 */
function plugin_mastodon_state_finalize_comment_reply_optin(&$state, $entryId, $commentId) {
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	$optins = plugin_mastodon_state_comment_reply_optins($state);
	if (!isset($optins [$key]) || !is_array($optins [$key]) || empty($optins [$key] ['pending_file'])) {
		return false;
	}
	$optins [$key] ['pending_file'] = '';
	$state ['comment_reply_optins'] = $optins;
	return true;
}

/**
 * Remove stale CommentCenter grants whose pending file and final comment are gone.
 *
 * Only records carrying pending_file trigger filesystem checks, so ordinary
 * visitor/admin grants do not add disk I/O to a synchronization run.
 *
 * @param array<string, mixed> $state
 * @return int Number of removed orphan grants.
 */
function plugin_mastodon_state_prune_orphaned_pending_comment_reply_optins(&$state) {
	$optins = plugin_mastodon_state_comment_reply_optins($state);
	$removed = 0;
	$changed = false;
	foreach ($optins as $key => $meta) {
		if (!is_array($meta) || empty($meta ['pending_file'])) {
			continue;
		}
		$entryId = isset($meta ['entry_id']) ? (string) $meta ['entry_id'] : '';
		$commentId = isset($meta ['comment_id']) ? (string) $meta ['comment_id'] : '';
		if ($entryId === '' || $commentId === '') {
			unset($optins [$key]);
			$removed++;
			$changed = true;
			continue;
		}
		if (function_exists('comment_exists') && comment_exists($entryId, $commentId)) {
			$optins [$key] ['pending_file'] = '';
			$changed = true;
			continue;
		}
		$pendingPath = defined('FP_CONTENT') ? (defined('ABS_PATH') ? ABS_PATH : '') . FP_CONTENT . 'plugin_commentcenter/' . basename((string) $meta ['pending_file']) : '';
		if ($pendingPath === '' || !is_file($pendingPath)) {
			unset($optins [$key]);
			$removed++;
			$changed = true;
		}
	}
	if ($changed) {
		$state ['comment_reply_optins'] = $optins;
	}
	return $removed;
}

/**
 * Remove a stored visitor opt-in marker.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @return void
 */
function plugin_mastodon_state_remove_comment_reply_optin(&$state, $entryId, $commentId) {
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	if (isset($state ['comment_reply_optins']) && is_array($state ['comment_reply_optins']) && isset($state ['comment_reply_optins'] [$key])) {
		unset($state ['comment_reply_optins'] [$key]);
	}
}

/**
 * Check whether a local comment has an explicit visitor opt-in marker.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @return bool
 */
function plugin_mastodon_state_has_comment_reply_optin($state, $entryId, $commentId) {
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	$optins = plugin_mastodon_state_comment_reply_optins($state);
	return isset($optins [$key]);
}

/**
 * Check whether a local comment may be newly exported as a Mastodon reply.
 *
 * Existing mappings are allowed so updates and cleanup of already-published
 * comments keep working. New local visitor comments require the explicit
 * opt-in marker written when the comment was saved.
 *
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @param array<string, mixed> $commentMeta
 * @return bool
 */
function plugin_mastodon_local_comment_export_allowed($state, $entryId, $commentId, $commentMeta, $comment = array()) {
	if (!empty($commentMeta ['remote_id'])) {
		return true;
	}
	if (plugin_mastodon_local_comment_from_authenticated_author($comment)) {
		return true;
	}
	return plugin_mastodon_state_has_comment_reply_optin($state, $entryId, $commentId);
}

/**
 * Return the loaded comment mapping array from a state object.
 * @param array<string, mixed> $state
 * @return array<string, array<string, mixed>>
 */
function plugin_mastodon_state_comment_mappings($state) {
	$comments = isset($state ['comments']) && is_array($state ['comments']) ? $state ['comments'] : array();
	$result = array();
	foreach ($comments as $commentKey => $meta) {
		if (is_array($meta)) {
			$result [(string) $commentKey] = $meta;
		}
	}
	ksort($result, SORT_STRING);
	return $result;
}

/**
 * Replace the loaded comment mapping array in a state object.
 * @param array<string, mixed> $state
 * @param array<string, mixed> $comments
 * @return void
 */
function plugin_mastodon_state_set_comment_mappings(&$state, $comments) {
	$normalized = array();
	if (is_array($comments)) {
		foreach ($comments as $commentKey => $meta) {
			if (is_array($meta)) {
				$normalized [(string) $commentKey] = $meta;
			}
		}
	}
	ksort($normalized, SORT_STRING);
	$state ['comments'] = $normalized;
}

/**
 * Return the global remote-comment reverse index.
 * @param array<string, mixed> $state
 * @return array<string, array<string, string>>
 */
function plugin_mastodon_state_comment_remote_mappings($state) {
	$remoteMappings = isset($state ['comments_remote']) && is_array($state ['comments_remote']) ? $state ['comments_remote'] : array();
	$result = array();
	foreach ($remoteMappings as $remoteId => $ref) {
		if (!is_array($ref)) {
			continue;
		}
		$entryId = isset($ref ['entry_id']) ? (string) $ref ['entry_id'] : '';
		$commentId = isset($ref ['comment_id']) ? (string) $ref ['comment_id'] : '';
		if ($entryId !== '' && $commentId !== '') {
			$result [(string) $remoteId] = array(
				'entry_id' => $entryId,
				'comment_id' => $commentId
			);
		}
	}
	ksort($result, SORT_STRING);
	return $result;
}

/**
 * Replace the global remote-comment reverse index in a state object.
 * @param array<string, mixed> $state
 * @param array<string, mixed> $remoteMappings
 * @return void
 */
function plugin_mastodon_state_set_comment_remote_mappings(&$state, $remoteMappings) {
	$normalized = array();
	if (is_array($remoteMappings)) {
		foreach ($remoteMappings as $remoteId => $ref) {
			if (!is_array($ref)) {
				continue;
			}
			$entryId = isset($ref ['entry_id']) ? (string) $ref ['entry_id'] : '';
			$commentId = isset($ref ['comment_id']) ? (string) $ref ['comment_id'] : '';
			if ($remoteId !== '' && $entryId !== '' && $commentId !== '') {
				$normalized [(string) $remoteId] = array(
					'entry_id' => $entryId,
					'comment_id' => $commentId
				);
			}
		}
	}
	ksort($normalized, SORT_STRING);
	$state ['comments_remote'] = $normalized;
}

/**
 * Return the main-state metadata for per-entry comment shards.
 * @param array<string, mixed> $state
 * @return array<string, array<string, mixed>>
 */
function plugin_mastodon_state_comment_shard_entries($state) {
	$shards = isset($state ['comment_shards']) && is_array($state ['comment_shards']) ? $state ['comment_shards'] : array();
	$entries = isset($shards ['entries']) && is_array($shards ['entries']) ? $shards ['entries'] : array();
	$result = array();
	foreach ($entries as $entryId => $meta) {
		if (is_array($meta) && (string) $entryId !== '') {
			$result [(string) $entryId] = $meta;
		}
	}
	ksort($result, SORT_STRING);
	return $result;
}

/**
 * Store a state write error for callers that need a last_error message even when persistence fails.
 * @param string $error
 * @return void
 */
function plugin_mastodon_state_write_error_set($error) {
	$GLOBALS ['plugin_mastodon_state_write_last_error'] = trim((string) $error);
}

/**
 * Clear the last state write error marker.
 * @return void
 */
function plugin_mastodon_state_write_error_clear() {
	$GLOBALS ['plugin_mastodon_state_write_last_error'] = '';
}

/**
 * Return the last state write error marker from the current request.
 * @return string
 */
function plugin_mastodon_state_write_last_error() {
	return isset($GLOBALS ['plugin_mastodon_state_write_last_error']) ? (string) $GLOBALS ['plugin_mastodon_state_write_last_error'] : '';
}

/**
 * Persist state and copy a failed write reason into the caller-visible last_error field.
 * @param array<string, mixed> $state
 * @param string $fallback
 * @param bool $preserveLocalEntryDeletions
 * @return bool
 */
function plugin_mastodon_state_write_with_last_error(&$state, $fallback = 'state_write_failed', $preserveLocalEntryDeletions = false) {
	$ok = plugin_mastodon_state_write($state, $preserveLocalEntryDeletions);
	if ($ok) {
		return true;
	}
	$error = plugin_mastodon_state_write_last_error();
	if ($error === '') {
		$error = trim((string) $fallback);
	}
	if ($error === '') {
		$error = 'state_write_failed';
	}
	if (!is_array($state)) {
		$state = array();
	}
	$state ['last_error'] = $error;
	return false;
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
 * Return an existing mapping that would conflict with assigning one remote status id to another local comment.
 *
 * The global comments_remote index is intentionally authoritative for duplicate prevention. The referenced
 * comment shard is loaded only to enrich diagnostics; a missing or damaged shard does not make it safe to
 * overwrite the reverse index silently.
 *
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @param string $remoteId
 * @return array<string, mixed>
 */
function plugin_mastodon_state_comment_mapping_conflict(&$state, $entryId, $commentId, $remoteId) {
	$entryId = trim((string) $entryId);
	$commentId = trim((string) $commentId);
	$remoteId = trim((string) $remoteId);
	if ($entryId === '' || $commentId === '' || $remoteId === '') {
		return array();
	}
	$remoteMappings = plugin_mastodon_state_comment_remote_mappings($state);
	if (!isset($remoteMappings [$remoteId]) || !is_array($remoteMappings [$remoteId])) {
		return array();
	}
	$ref = $remoteMappings [$remoteId];
	$existingEntryId = isset($ref ['entry_id']) ? trim((string) $ref ['entry_id']) : '';
	$existingCommentId = isset($ref ['comment_id']) ? trim((string) $ref ['comment_id']) : '';
	if ($existingEntryId === '' || $existingCommentId === '') {
		return array();
	}
	if ($existingEntryId === $entryId && $existingCommentId === $commentId) {
		return array();
	}
	plugin_mastodon_state_load_comment_shard_into($state, $existingEntryId);
	$existingMeta = plugin_mastodon_state_get_comment_meta($state, $existingEntryId, $existingCommentId);
	return array(
		'entry_id' => $existingEntryId,
		'comment_id' => $existingCommentId,
		'remote_id' => $remoteId,
		'source' => isset($existingMeta ['source']) ? (string) $existingMeta ['source'] : '',
		'mapping_present' => $existingMeta !== array() ? 1 : 0
	);
}

/**
 * Store the mapping between a local comment and a remote status.
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
 * @param string $localDateKey
 * @param string $remoteDateKey
 * @return bool
 */
function plugin_mastodon_state_set_comment_mapping(&$state, $entryId, $commentId, $remoteId, $source, $hash, $remoteUrl, $remoteUpdatedAt, $parentCommentId = '', $inReplyToRemoteId = '', $localDateKey = '', $remoteDateKey = '') {
	$entryId = trim((string) $entryId);
	$commentId = trim((string) $commentId);
	$remoteId = trim((string) $remoteId);
	if ($entryId === '' || $commentId === '' || $remoteId === '') {
		plugin_mastodon_log('Refusing to store an incomplete comment mapping for ' . ($entryId !== '' ? $entryId : '[missing-entry]') . '/' . ($commentId !== '' ? $commentId : '[missing-comment]') . ' and remote status ' . ($remoteId !== '' ? $remoteId : '[missing-remote-id]'));
		return false;
	}
	$conflict = plugin_mastodon_state_comment_mapping_conflict($state, $entryId, $commentId, $remoteId);
	if ($conflict !== array()) {
		plugin_mastodon_log('Refusing duplicate remote comment mapping for status ' . $remoteId . ': requested ' . $entryId . '/' . $commentId . ', existing ' . (string) $conflict ['entry_id'] . '/' . (string) $conflict ['comment_id']);
		return false;
	}
	if (plugin_mastodon_state_comment_shards_partial($state)) {
		plugin_mastodon_state_mark_comment_entry_loaded($state, $entryId);
	}
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	$localDateKey = plugin_mastodon_normalize_sync_start_date($localDateKey);
	if ($localDateKey === '') {
		$localDateKey = plugin_mastodon_local_item_date_key(array(), $commentId);
	}
	$remoteDateKey = plugin_mastodon_normalize_sync_start_date($remoteDateKey);
	if ($remoteDateKey === '') {
		$remoteDateKey = plugin_mastodon_datetime_date_key($remoteUpdatedAt);
	}
	$comments = plugin_mastodon_state_comment_mappings($state);
	$existing = isset($comments [$key]) && is_array($comments [$key]) ? $comments [$key] : array();
	$mapping = array(
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
	if (!empty($existing ['optin_comment_to_reply'])) {
		$mapping ['optin_comment_to_reply'] = '1';
		if (!empty($existing ['optin_comment_to_reply_at'])) {
			$mapping ['optin_comment_to_reply_at'] = (string) $existing ['optin_comment_to_reply_at'];
		}
	}
	if (plugin_mastodon_state_has_comment_reply_optin($state, $entryId, $commentId)) {
		$optins = plugin_mastodon_state_comment_reply_optins($state);
		if (isset($optins [$key]) && is_array($optins [$key])) {
			$mapping ['optin_comment_to_reply'] = '1';
			$mapping ['optin_comment_to_reply_at'] = isset($optins [$key] ['granted_at']) ? (string) $optins [$key] ['granted_at'] : '';
		}
	}
	$comments [$key] = $mapping;
	plugin_mastodon_state_set_comment_mappings($state, $comments);
	$remoteMappings = plugin_mastodon_state_comment_remote_mappings($state);
	$existingRemoteId = !empty($existing ['remote_id']) ? trim((string) $existing ['remote_id']) : '';
	if ($existingRemoteId !== '' && $existingRemoteId !== $remoteId && isset($remoteMappings [$existingRemoteId]) && is_array($remoteMappings [$existingRemoteId])) {
		$existingRemoteRef = $remoteMappings [$existingRemoteId];
		if ((isset($existingRemoteRef ['entry_id']) ? (string) $existingRemoteRef ['entry_id'] : '') === $entryId && (isset($existingRemoteRef ['comment_id']) ? (string) $existingRemoteRef ['comment_id'] : '') === $commentId) {
			unset($remoteMappings [$existingRemoteId]);
		}
	}
	$remoteMappings [$remoteId] = array(
		'entry_id' => $entryId,
		'comment_id' => $commentId
	);
	plugin_mastodon_state_set_comment_remote_mappings($state, $remoteMappings);
	return true;
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
	unset($state ['local_entry_deletions'] [$localId]);
	if ($remoteId !== '' && isset($state ['entries_remote'] [$remoteId]) && (string) $state ['entries_remote'] [$remoteId] === $localId) {
		unset($state ['entries_remote'] [$remoteId]);
	}
	if (isset($state ['dirty_entries'] [$localId])) {
		unset($state ['dirty_entries'] [$localId]);
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
	if (plugin_mastodon_state_comment_shards_partial($state) && !plugin_mastodon_state_comment_entry_loaded($state, $entryId)) {
		plugin_mastodon_state_load_comment_shard_into($state, $entryId);
	}
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	$comments = plugin_mastodon_state_comment_mappings($state);
	if (!isset($comments [$key])) {
		return;
	}
	$remoteId = !empty($comments [$key] ['remote_id']) ? (string) $comments [$key] ['remote_id'] : '';
	unset($comments [$key]);
	plugin_mastodon_state_set_comment_mappings($state, $comments);
	if ($remoteId !== '') {
		$remoteMappings = plugin_mastodon_state_comment_remote_mappings($state);
		if (isset($remoteMappings [$remoteId]) && is_array($remoteMappings [$remoteId])) {
			$remoteRef = $remoteMappings [$remoteId];
			if ((isset($remoteRef ['entry_id']) ? (string) $remoteRef ['entry_id'] : '') === (string) $entryId && (isset($remoteRef ['comment_id']) ? (string) $remoteRef ['comment_id'] : '') === (string) $commentId) {
				unset($remoteMappings [$remoteId]);
				plugin_mastodon_state_set_comment_remote_mappings($state, $remoteMappings);
			}
		}
	}
	if (isset($state ['dirty_comments'] [$key])) {
		unset($state ['dirty_comments'] [$key]);
	}
}

/**
 * Drop a stale remote entry mapping but keep the local entry queued for export.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @return void
 */
function plugin_mastodon_state_unlink_entry_remote_for_reexport(&$state, $entryId) {
	$entryId = (string) $entryId;
	if ($entryId === '') {
		return;
	}
	$entry = entry_exists($entryId) ? entry_parse($entryId) : array();
	$hash = is_array($entry) ? plugin_mastodon_entry_hash($entry) : '';
	plugin_mastodon_state_remove_entry_mapping($state, $entryId);
	if (is_array($entry) && $hash !== '') {
		plugin_mastodon_state_set_dirty_entry($state, $entryId, $hash);
	}
}

/**
 * Drop a stale remote comment mapping but keep the local comment queued for export.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @return void
 */
function plugin_mastodon_state_unlink_comment_remote_for_reexport(&$state, $entryId, $commentId) {
	$entryId = (string) $entryId;
	$commentId = (string) $commentId;
	if ($entryId === '' || $commentId === '') {
		return;
	}
	$comment = comment_exists($entryId, $commentId) ? comment_parse($entryId, $commentId) : array();
	$hash = is_array($comment) ? plugin_mastodon_comment_hash($comment) : '';
	plugin_mastodon_state_remove_comment_mapping($state, $entryId, $commentId);
	if (is_array($comment) && $hash !== '') {
		plugin_mastodon_state_set_dirty_comment($state, $entryId, $commentId, $hash);
	}
}

/**
 * Add an older changed entry to the persistent dirty queue.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $hash
 * @return void
 */
function plugin_mastodon_state_set_dirty_entry(&$state, $entryId, $hash) {
	$entryId = trim((string) $entryId);
	if ($entryId === '') {
		return;
	}
	if (!isset($state ['dirty_entries']) || !is_array($state ['dirty_entries'])) {
		$state ['dirty_entries'] = array();
	}
	$state ['dirty_entries'] [$entryId] = array(
		'hash' => (string) $hash,
		'queued_at' => gmdate('Y-m-d H:i:s')
	);
}

/**
 * Remove an entry from the dirty queue.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @return void
 */
function plugin_mastodon_state_remove_dirty_entry(&$state, $entryId) {
	$entryId = trim((string) $entryId);
	if ($entryId !== '' && isset($state ['dirty_entries'] [$entryId])) {
		unset($state ['dirty_entries'] [$entryId]);
	}
}

/**
 * Check whether an entry is queued for synchronization although it is outside the scheduled window.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @return bool
 */
function plugin_mastodon_state_has_dirty_entry($state, $entryId) {
	$entryId = trim((string) $entryId);
	return $entryId !== '' && isset($state ['dirty_entries']) && is_array($state ['dirty_entries']) && isset($state ['dirty_entries'] [$entryId]);
}

/**
 * Add an older changed comment to the persistent dirty queue.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @param string $hash
 * @return void
 */
function plugin_mastodon_state_set_dirty_comment(&$state, $entryId, $commentId, $hash) {
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	if ($key === ':') {
		return;
	}
	if (!isset($state ['dirty_comments']) || !is_array($state ['dirty_comments'])) {
		$state ['dirty_comments'] = array();
	}
	$state ['dirty_comments'] [$key] = array(
		'entry_id' => (string) $entryId,
		'comment_id' => (string) $commentId,
		'hash' => (string) $hash,
		'queued_at' => gmdate('Y-m-d H:i:s')
	);
}

/**
 * Remove a comment from the dirty queue.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @return void
 */
function plugin_mastodon_state_remove_dirty_comment(&$state, $entryId, $commentId) {
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	if (isset($state ['dirty_comments'] [$key])) {
		unset($state ['dirty_comments'] [$key]);
	}
}

/**
 * Remove all dirty-comment queue entries for one FlatPress entry.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @return void
 */
function plugin_mastodon_state_remove_dirty_comments_for_entry(&$state, $entryId) {
	$entryId = trim((string) $entryId);
	if ($entryId === '' || !isset($state ['dirty_comments']) || !is_array($state ['dirty_comments'])) {
		return;
	}
	foreach (array_keys($state ['dirty_comments']) as $key) {
		$dirtyComment = isset($state ['dirty_comments'] [$key]) && is_array($state ['dirty_comments'] [$key]) ? $state ['dirty_comments'] [$key] : array();
		if ((isset($dirtyComment ['entry_id']) ? (string) $dirtyComment ['entry_id'] : '') === $entryId) {
			unset($state ['dirty_comments'] [$key]);
			continue;
		}
		if (plugin_mastodon_state_entry_id_from_comment_key((string) $key) === $entryId) {
			unset($state ['dirty_comments'] [$key]);
		}
	}
}

/**
 * Check whether a comment is queued for synchronization although it is outside the scheduled window.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param string $commentId
 * @return bool
 */
function plugin_mastodon_state_has_dirty_comment($state, $entryId, $commentId) {
	$key = plugin_mastodon_state_comment_key($entryId, $commentId);
	return isset($state ['dirty_comments']) && is_array($state ['dirty_comments']) && isset($state ['dirty_comments'] [$key]);
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
	$comments = plugin_mastodon_state_comment_mappings($state);
	return isset($comments [$key]) ? $comments [$key] : array();
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
		'deleted_at' => gmdate('Y-m-d H:i:s')
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
 * Protect a missing source=local comment found through the global remote reverse index.
 *
 * This is the import-boundary fallback for legacy state, direct file removal or a deletion hook that could not
 * persist its tombstone. Only the referenced entry shard is loaded; no global shard scan is required.
 *
 * @param array<string, mixed> $state
 * @param string $remoteId
 * @return bool
 */
function plugin_mastodon_protect_missing_local_exported_comment_by_remote_id(&$state, $remoteId) {
	$remoteId = trim((string) $remoteId);
	if ($remoteId === '') {
		return false;
	}
	if (plugin_mastodon_state_has_comment_tombstone($state, $remoteId)) {
		return true;
	}
	$remoteMappings = plugin_mastodon_state_comment_remote_mappings($state);
	if (!isset($remoteMappings [$remoteId]) || !is_array($remoteMappings [$remoteId])) {
		return false;
	}
	$ref = $remoteMappings [$remoteId];
	$entryId = isset($ref ['entry_id']) ? trim((string) $ref ['entry_id']) : '';
	$commentId = isset($ref ['comment_id']) ? trim((string) $ref ['comment_id']) : '';
	if ($entryId === '' || $commentId === '') {
		return false;
	}
	plugin_mastodon_state_load_comment_shard_into($state, $entryId);
	$meta = plugin_mastodon_state_get_comment_meta($state, $entryId, $commentId);
	$mappedRemoteId = !empty($meta ['remote_id']) ? trim((string) $meta ['remote_id']) : '';
	$source = isset($meta ['source']) ? strtolower(trim((string) $meta ['source'])) : '';
	if ($mappedRemoteId !== $remoteId || $source !== 'local' || comment_exists($entryId, $commentId)) {
		return false;
	}
	plugin_mastodon_state_set_comment_tombstone($state, $remoteId, $entryId, $commentId, 'local_deleted_pending_remote_delete');
	plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
	plugin_mastodon_state_set_deletions_pending($state, true, 'full', '');
	plugin_mastodon_log('Defensively protected missing exported FlatPress comment ' . $entryId . '/' . $commentId . ' from remote reply ' . $remoteId . ' at the import boundary');
	return true;
}

/**
 * Protect locally deleted exported FlatPress comments from stale Mastodon re-imports before deletion sync runs.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @return int
 */
function plugin_mastodon_protect_locally_deleted_exported_comments($options, &$state) {
	$protected = 0;
	$commentSources = array();
	$loadedComments = isset($state ['comments']) && is_array($state ['comments']) ? $state ['comments'] : array();
	if ($loadedComments !== array()) {
		$commentSources [] = $loadedComments;
	} elseif (!plugin_mastodon_state_comment_shards_partial($state)) {
		$commentSources [] = $loadedComments;
	}
	foreach ($commentSources as $comments) {
		foreach ($comments as $commentKey => $meta) {
			$commentKey = (string) $commentKey;
			if (!is_array($meta) || empty($meta ['entry_id']) || empty($meta ['comment_id']) || empty($meta ['remote_id'])) {
				continue;
			}
			if (!isset($meta ['source']) || (string) $meta ['source'] !== 'local') {
				continue;
			}
			$entryId = (string) $meta ['entry_id'];
			$commentId = (string) $meta ['comment_id'];
			$remoteId = (string) $meta ['remote_id'];
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
	if (!plugin_mastodon_state_set_comment_mapping($state, $entryId, $commentId, $remoteId, $source, $hash, $remoteUrl, $remoteUpdatedAt, '', $entryRemoteId, $localDateKey, $remoteDateKey)) {
		plugin_mastodon_log('Could not reattach local comment ' . $entryId . '/' . $commentId . ' because remote status ' . $remoteId . ' is already mapped elsewhere');
		return false;
	}
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
		'queued_at' => !empty($existing ['queued_at']) ? (string) $existing ['queued_at'] : gmdate('Y-m-d H:i:s'),
		'last_checked_at' => !empty($existing ['last_checked_at']) ? (string) $existing ['last_checked_at'] : ''
	);
}

/**
 * Return the earliest fixed local-entry deadline, also from a compact scheduler summary.
 * @param array<string, mixed> $state
 * @return string
 */
function plugin_mastodon_state_local_entry_deletion_deadline($state) {
	if (!isset($state ['local_entry_deletions']) || !is_array($state ['local_entry_deletions'])) {
		return plugin_mastodon_parse_iso_datetime(isset($state ['local_entry_deletion_not_before']) ? (string) $state ['local_entry_deletion_not_before'] : '');
	}
	$earliest = '';
	foreach ($state ['local_entry_deletions'] as $deadline) {
		$deadline = is_string($deadline) ? plugin_mastodon_parse_iso_datetime($deadline) : '';
		if ($deadline !== '' && ($earliest === '' || strcmp($deadline, $earliest) < 0)) {
			$earliest = $deadline;
		}
	}
	return $earliest;
}

/**
 * Record an entry deletion once. A second entry gets its own complete five-minute delay.
 * @param array<string, mixed> $state
 * @param string $entryId
 * @param int|null $timestamp
 * @return void
 */
function plugin_mastodon_state_queue_local_entry_deletion(&$state, $entryId, $timestamp = null) {
	$entryId = trim((string) $entryId);
	if ($entryId === '' || empty($state ['entries'] [$entryId] ['remote_id'])) {
		return;
	}
	if (!isset($state ['local_entry_deletions']) || !is_array($state ['local_entry_deletions'])) {
		$state ['local_entry_deletions'] = array();
	}
	if (isset($state ['local_entry_deletions'] [$entryId]) && is_string($state ['local_entry_deletions'] [$entryId]) && plugin_mastodon_parse_iso_datetime($state ['local_entry_deletions'] [$entryId]) !== '') {
		return;
	}
	$timestamp = $timestamp === null ? time() : (int) $timestamp;
	$state ['local_entry_deletions'] [$entryId] = gmdate('Y-m-d H:i:s', $timestamp + PLUGIN_MASTODON_COOLDOWN_TTL);
	plugin_mastodon_state_set_deletions_pending($state, true, 'full', plugin_mastodon_state_local_entry_deletion_deadline($state));
}

/**
 * Schedule a content follow-up without postponing fixed entry deadlines or retry backoff.
 * @param array<string, mixed> $state
 * @param bool $enabled
 * @param int $timestamp
 * @return void
 */
function plugin_mastodon_state_schedule_content_deletions(&$state, $enabled, $timestamp) {
	$deadline = plugin_mastodon_state_local_entry_deletion_deadline($state);
	if ($deadline === '') {
		$deadline = gmdate('Y-m-d H:i:s', (int) $timestamp + PLUGIN_MASTODON_COOLDOWN_TTL);
	}
	plugin_mastodon_state_set_deletions_pending($state, $enabled, 'full', $deadline);
}

/**
 * Select the next automatic job. Due fixed entry deletions precede a daily content run.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param int $timestamp
 * @return string
 */
function plugin_mastodon_scheduled_job($options, $state, $timestamp) {
	$localDeadline = plugin_mastodon_state_local_entry_deletion_deadline($state);
	if (plugin_mastodon_should_run_deletion_sync($options) && $localDeadline !== '' && plugin_mastodon_parse_iso_timestamp($localDeadline) <= $timestamp && plugin_mastodon_deletion_sync_due($state, $timestamp)) {
		return 'deletion';
	}
	if (plugin_mastodon_sync_due($options, $state, $timestamp)) {
		return 'content';
	}
	if (plugin_mastodon_should_run_deletion_sync($options) && plugin_mastodon_deletion_sync_due($state, $timestamp)) {
		return 'deletion';
	}
	return '';
}

/**
 * Update the runtime marker that tells the scheduler whether another deletion follow-up request is required.
 * @param array<string, mixed> $state
 * @param bool $pending
 * @param string $scope
 * @param string $notBefore
 * @return void
 */
function plugin_mastodon_state_set_deletions_pending(&$state, $pending, $scope, $notBefore = '') {
	$pending = (bool) $pending;
	$scope = plugin_mastodon_normalize_deletions_pending_scope($scope);
	$state ['deletions_pending'] = $pending ? 1 : 0;
	$state ['deletions_pending_scope'] = $pending ? $scope : 'full';
	$state ['deletions_not_before'] = $pending ? plugin_mastodon_parse_iso_datetime($notBefore) : '';
}

/**
 * Determine whether a pending deletion synchronization may start now.
 * @param array<string, mixed> $state
 * @param int|null $timestamp
 * @return bool
 */
function plugin_mastodon_deletion_sync_due($state, $timestamp = null) {
	$state = is_array($state) ? $state : array();
	$localDeadline = plugin_mastodon_state_local_entry_deletion_deadline($state);
	if (empty($state ['deletions_pending']) && $localDeadline === '') {
		return false;
	}
	$timestamp = $timestamp === null ? time() : (int) $timestamp;
	$retryNotBefore = plugin_mastodon_parse_iso_timestamp(isset($state ['deletion_retry_not_before']) ? (string) $state ['deletion_retry_not_before'] : '');
	if ($retryNotBefore > $timestamp) {
		return false;
	}
	if ($localDeadline !== '' && plugin_mastodon_parse_iso_timestamp($localDeadline) <= $timestamp) {
		return true;
	}
	$notBefore = plugin_mastodon_parse_iso_datetime(isset($state ['deletions_not_before']) ? (string) $state ['deletions_not_before'] : '');
	if ($notBefore === '' && !empty($state ['last_run'])) {
		$lastRun = plugin_mastodon_parse_iso_timestamp((string) $state ['last_run']);
		if ($lastRun > 0) {
			$notBefore = gmdate('Y-m-d H:i:s', $lastRun + PLUGIN_MASTODON_COOLDOWN_TTL);
		}
	}
	if ($notBefore === '') {
		return true;
	}
	$notBeforeTimestamp = plugin_mastodon_parse_iso_timestamp($notBefore);
	if ($notBeforeTimestamp <= 0) {
		return true;
	}
	return $timestamp >= $notBeforeTimestamp;
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
 * Load comment shards needed for the current content synchronization workset.
 * @param array<string, string> $options
 * @param array<string, mixed> $state
 * @param bool $force
 * @return array<int, string>
 */
function plugin_mastodon_state_load_content_sync_comment_workset($options, &$state, $force) {
	$entryIds = plugin_mastodon_content_sync_comment_entry_ids($options, $state, $force);
	foreach ($entryIds as $entryId) {
		plugin_mastodon_state_load_comment_shard_into($state, $entryId);
	}
	return $entryIds;
}

/**
 * Print command-line comment-shard diagnostics or perform a repair.
 *
 * @param array<int, string>|null $arguments
 * @return int
 */
function plugin_mastodon_cli_comment_shard_maintenance($arguments = null) {
	$arguments = is_array($arguments) ? array_values($arguments) : (isset($_SERVER ['argv']) && is_array($_SERVER ['argv']) ? array_slice($_SERVER ['argv'], 1) : array());
	$command = isset($arguments [0]) ? (string) $arguments [0] : '';
	$repair = in_array($command, array('repair', 'repair-comment-shards', '--repair-comment-shards'), true);
	$diagnose = $repair || in_array($command, array('', 'diagnose', 'diagnose-comment-shards', '--diagnose-comment-shards'), true);
	if (!$diagnose) {
		echo 'Usage: php fp-plugins/mastodon/mastodon-state-cli.php [diagnose|repair]' . PHP_EOL;
		return 2;
	}
	$result = $repair ? plugin_mastodon_state_repair_comment_shards(null, true) : plugin_mastodon_state_diagnose_comment_shards(null);
	$stats = isset($result ['stats']) && is_array($result ['stats']) ? $result ['stats'] : array();
	echo ($repair ? 'Mastodon comment-shard repair' : 'Mastodon comment-shard diagnostics') . PHP_EOL;
	foreach (array('shard_files', 'shard_metadata_entries', 'comments', 'comments_remote') as $key) {
		echo $key . ': ' . (isset($stats [$key]) ? (int) $stats [$key] : 0) . PHP_EOL;
	}
	if (!empty($result ['repaired'])) {
		echo 'repaired: yes' . PHP_EOL;
	}
	$errors = isset($result ['errors']) && is_array($result ['errors']) ? $result ['errors'] : array();
	$warnings = isset($result ['warnings']) && is_array($result ['warnings']) ? $result ['warnings'] : array();
	foreach ($warnings as $warning) {
		echo 'warning: ' . (string) $warning . PHP_EOL;
	}
	foreach ($errors as $error) {
		echo 'error: ' . (string) $error . PHP_EOL;
	}
	return empty($errors) ? 0 : 1;
}
?>

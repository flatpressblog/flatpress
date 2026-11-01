<?php
/**
 * Mastodon deletion declarations. Loaded by plugin.mastodon.php.
 * Runtime constants and registrations remain in the main plugin file.
 */

/**
 * Build an index of mapped local comments grouped by their direct remote parent status.
 * @param array<string, mixed> $state
 * @return array<string, array<int, array<string, mixed>>>
 */
function plugin_mastodon_build_comment_remote_child_index($state) {
	$index = array();
	$commentSources = array();
	if (!empty($state ['comments']) && is_array($state ['comments'])) {
		$commentSources [] = $state ['comments'];
	}
	foreach ($commentSources as $comments) {
		foreach ($comments as $commentMeta) {
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
		if (isset($state ['local_entry_deletions'] [$entryId]) && plugin_mastodon_parse_iso_timestamp((string) $state ['local_entry_deletions'] [$entryId]) > time()) {
			$pendingRemaining = true;
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
			if (plugin_mastodon_should_import_remote_to_local($options)) {
				$queuedChildren = plugin_mastodon_queue_comment_descendant_remote_rechecks($state, $childIndex, $remoteId);
				if (comment_exists($entryId, $commentId)) {
					plugin_mastodon_local_write_guard_enter();
					try {
						comment_delete($entryId, $commentId);
					} finally {
						plugin_mastodon_local_write_guard_leave();
					}
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
			} else {
				plugin_mastodon_state_unlink_comment_remote_for_reexport($state, $entryId, $commentId);
				unset($state ['pending_comment_remote_rechecks'] [$commentKey]);
				plugin_mastodon_log('Remote reply ' . $remoteId . ' disappeared in the pending descendant queue; kept local comment ' . $entryId . '/' . $commentId . ' and queued it for re-export because Mastodon-to-FlatPress import is disabled');
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
			$state ['pending_comment_remote_rechecks'] [$commentKey] ['last_checked_at'] = gmdate('Y-m-d H:i:s');
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
 * Run deletion synchronization when fixed entry deadlines or general follow-up work are due.
 * @param bool $force
 * @return array<string, mixed>
 */
function plugin_mastodon_run_deletion_sync($force) {
	plugin_mastodon_extend_time_limit(180);
	$options = plugin_mastodon_get_options();
	$state = plugin_mastodon_state_read(array());
	$force = (bool) $force;
	$syncCommentsAndReplies = plugin_mastodon_should_sync_comments_and_replies($options);

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
		$state ['deletion_cursor_entries'] = '';
		$state ['deletion_cursor_comments'] = '';
		$state ['last_error'] = '';
		plugin_mastodon_state_write($state);
		return array('ok' => true, 'state' => $state, 'message' => 'deletion_sync_disabled');
	}
	if (!$force && empty($state ['deletions_pending']) && plugin_mastodon_state_local_entry_deletion_deadline($state) === '') {
		return array('ok' => true, 'state' => $state, 'message' => 'no_deletions_pending');
	}
	if (!plugin_mastodon_deletion_sync_due($state, time())) {
		return array('ok' => true, 'state' => $state, 'message' => 'deletion_sync_wait');
	}
	if (!$force && plugin_mastodon_sync_guard_active('deletion')) {
		return array('ok' => true, 'state' => $state, 'message' => 'deletion_sync_cooldown');
	}
	if (!$force) {
		plugin_mastodon_sync_guard_mark('deletion', 'scheduled_deletion_sync');
	}

	plugin_mastodon_ensure_state_dir();
	$lockHandle = @fopen(PLUGIN_MASTODON_LOCK_FILE, 'c+');
	if (!$lockHandle) {
		$state ['last_error'] = 'lock_open_failed';
		plugin_mastodon_state_write($state);
		return array('ok' => false, 'state' => $state, 'message' => 'lock_open_failed');
	}
	plugin_mastodon_apply_file_permissions(PLUGIN_MASTODON_LOCK_FILE);
	if (!@flock($lockHandle, LOCK_EX | LOCK_NB)) {
		@fclose($lockHandle);
		return array('ok' => true, 'state' => $state, 'message' => 'sync_locked');
	}

	plugin_mastodon_rate_limit_guard_start('deletion_sync');
	$state ['last_error'] = '';
	$state ['deletion_stats'] = plugin_mastodon_default_state() ['deletion_stats'];
	$hadFailure = false;
	$deletionInterrupted = false;
	$childIndex = array();
	$commentRecheckOnly = $syncCommentsAndReplies && plugin_mastodon_state_local_entry_deletion_deadline($state) === '' && plugin_mastodon_state_has_comment_recheck_scope($state) && !empty($state ['pending_comment_remote_rechecks']);

	if (!$commentRecheckOnly) {
		$entryMappingKeys = isset($state ['entries']) && is_array($state ['entries']) ? array_keys($state ['entries']) : array();
		$entryMappings = plugin_mastodon_mapping_keys_after_cursor($entryMappingKeys, isset($state ['deletion_cursor_entries']) ? (string) $state ['deletion_cursor_entries'] : '');
		$processedEntryMappings = 0;

		foreach ($entryMappings as $localEntryId) {
			if (plugin_mastodon_rate_limit_state_error() !== '') {
				$deletionInterrupted = true;
				break;
			}
			plugin_mastodon_extend_time_limit(120);
			$localEntryId = (string) $localEntryId;
			$entryCursorBefore = isset($state ['deletion_cursor_entries']) ? (string) $state ['deletion_cursor_entries'] : '';
			$state ['deletion_cursor_entries'] = $localEntryId;
			$processedEntryMappings++;
			$meta = plugin_mastodon_state_get_entry_meta($state, $localEntryId);
			if (empty($meta ['remote_id'])) {
				plugin_mastodon_state_remove_entry_mapping($state, $localEntryId);
				continue;
			}
			if (!plugin_mastodon_mapping_matches_sync_start($options, $meta, $localEntryId)) {
				unset($state ['local_entry_deletions'] [$localEntryId]);
				plugin_mastodon_log_skip('deletion_entry_before_sync_start', 'deletion entry mapping', 'deletion entry mappings', $localEntryId, 'because they are older than the configured sync start date');
				continue;
			}
			$remoteId = (string) $meta ['remote_id'];
			$localExists = (bool) entry_exists($localEntryId);
			if ($localExists) {
				unset($state ['local_entry_deletions'] [$localEntryId]);
			} elseif (isset($state ['local_entry_deletions'] [$localEntryId]) && plugin_mastodon_parse_iso_timestamp((string) $state ['local_entry_deletions'] [$localEntryId]) > time()) {
				// Even a forced/full sweep must not delete a newly queued entry early.
				continue;
			}
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
				if (plugin_mastodon_rate_limit_state_error() !== '') {
					$state ['deletion_cursor_entries'] = $entryCursorBefore;
					$deletionInterrupted = true;
					break;
				}
				continue;
			}

			if (!plugin_mastodon_mapping_matches_deletion_lookup_window($options, $meta, $localEntryId, $force)) {
				plugin_mastodon_log_skip('deletion_entry_outside_lookup_window', 'remote deletion entry lookup', 'remote deletion entry lookups', $localEntryId, 'because they are outside the scheduled deletion lookup window');
				continue;
			}

			$remote = plugin_mastodon_fetch_status($options, $remoteId);
			if (plugin_mastodon_status_missing_response($remote)) {
				if (plugin_mastodon_should_import_remote_to_local($options)) {
					if (entry_exists($localEntryId)) {
						plugin_mastodon_local_write_guard_enter();
						try {
							entry_delete($localEntryId);
						} finally {
							plugin_mastodon_local_write_guard_leave();
						}
					}
					plugin_mastodon_state_remove_entry_mapping($state, $localEntryId);
					$state ['deletion_stats'] ['deleted_local_entries']++;
				} else {
					plugin_mastodon_state_unlink_entry_remote_for_reexport($state, $localEntryId);
					plugin_mastodon_log('Remote status ' . $remoteId . ' is missing; kept local entry ' . $localEntryId . ' and queued it for re-export because Mastodon-to-FlatPress import is disabled');
				}
				continue;
			}
			if (empty($remote ['ok'])) {
				$hadFailure = true;
				$state ['last_error'] = 'entry_remote_lookup_failed: ' . $localEntryId . ' (' . plugin_mastodon_response_error_message($remote) . ')';
				plugin_mastodon_log('Deletion sync failed to fetch remote status for local entry ' . $localEntryId . ': ' . plugin_mastodon_response_error_message($remote));
				if (plugin_mastodon_rate_limit_state_error() !== '') {
					$state ['deletion_cursor_entries'] = $entryCursorBefore;
					$deletionInterrupted = true;
					break;
				}
			}
		}

		if (!$deletionInterrupted && $processedEntryMappings >= count($entryMappings)) {
			$state ['deletion_cursor_entries'] = '';
		}

		if ($syncCommentsAndReplies && !$deletionInterrupted) {
			$commentShardEntryIds = plugin_mastodon_state_comment_shard_entry_ids($state);
			$commentCursor = isset($state ['deletion_cursor_comments']) ? (string) $state ['deletion_cursor_comments'] : '';
			$processedCommentMappings = 0;
			$remainingCommentMappings = 0;

			$commentCursorEntryId = plugin_mastodon_state_entry_id_from_comment_key($commentCursor);
			foreach ($commentShardEntryIds as $commentShardEntryId) {
				if (isset($state ['local_entry_deletions'] [$commentShardEntryId]) && plugin_mastodon_parse_iso_timestamp((string) $state ['local_entry_deletions'] [$commentShardEntryId]) > time()) {
					continue;
				}
				if (plugin_mastodon_rate_limit_state_error() !== '') {
					$deletionInterrupted = true;
					break;
				}
				$commentShardEntryId = (string) $commentShardEntryId;
				if ($commentCursorEntryId !== '' && strcmp($commentShardEntryId, $commentCursorEntryId) < 0) {
					continue;
				}
				plugin_mastodon_state_load_comment_shard_into($state, $commentShardEntryId);
				$commentMappingKeys = array();
				$allComments = isset($state ['comments']) && is_array($state ['comments']) ? $state ['comments'] : array();
				foreach (array_keys($allComments) as $candidateCommentKey) {
					if (plugin_mastodon_state_entry_id_from_comment_key((string) $candidateCommentKey) === $commentShardEntryId) {
						$commentMappingKeys [] = (string) $candidateCommentKey;
					}
				}
				sort($commentMappingKeys, SORT_STRING);
				$remainingCommentMappings += count($commentMappingKeys);
				$commentMappings = plugin_mastodon_mapping_keys_after_cursor($commentMappingKeys, $commentCursor);
				$shardChanged = false;
				$childIndex = plugin_mastodon_build_comment_remote_child_index($state);

				foreach ($commentMappings as $commentKey) {
					if (plugin_mastodon_rate_limit_state_error() !== '') {
						$deletionInterrupted = true;
						break;
					}
					plugin_mastodon_extend_time_limit(120);
					$commentKey = (string) $commentKey;
					$commentCursorBefore = isset($state ['deletion_cursor_comments']) ? (string) $state ['deletion_cursor_comments'] : '';
					$state ['deletion_cursor_comments'] = $commentKey;
					$processedCommentMappings++;
					$meta = isset($state ['comments'] [$commentKey]) && is_array($state ['comments'] [$commentKey]) ? $state ['comments'] [$commentKey] : array();
					if (empty($meta ['entry_id']) || empty($meta ['comment_id'])) {
						if ($commentKey !== '' && isset($state ['pending_comment_remote_rechecks'] [$commentKey])) {
							unset($state ['pending_comment_remote_rechecks'] [$commentKey]);
							$shardChanged = true;
						}
						continue;
					}
					$entryId = (string) $meta ['entry_id'];
					$commentId = (string) $meta ['comment_id'];
					$commentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $commentId);
					if (empty($commentMeta ['remote_id'])) {
						plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
						plugin_mastodon_state_remove_comment_mapping($state, $entryId, $commentId);
						$shardChanged = true;
						continue;
					}
					if (!plugin_mastodon_mapping_matches_sync_start($options, $commentMeta, $commentId)) {
						plugin_mastodon_log_skip('deletion_comment_before_sync_start', 'deletion comment mapping', 'deletion comment mappings', $entryId . '/' . $commentId, 'because they are older than the configured sync start date');
						plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
						$shardChanged = true;
						continue;
					}
					if (plugin_mastodon_state_get_pending_comment_remote_recheck($state, $entryId, $commentId) !== array()) {
						continue;
					}
					$remoteId = (string) $commentMeta ['remote_id'];
					$localExists = (bool) comment_exists($entryId, $commentId);
					$source = isset($commentMeta ['source']) ? strtolower((string) $commentMeta ['source']) : '';
					if (!$localExists && $source === 'remote' && entry_exists($entryId)) {
						plugin_mastodon_state_set_comment_tombstone($state, $remoteId, $entryId, $commentId, 'local_deleted_imported_remote_ignored');
						plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
						plugin_mastodon_state_remove_comment_mapping($state, $entryId, $commentId);
						$shardChanged = true;
						plugin_mastodon_log('Marked imported remote reply ' . $remoteId . ' as locally ignored without attempting a Mastodon delete for comment ' . $entryId . '/' . $commentId);
						continue;
					}
					if (!$localExists) {
						$deleted = plugin_mastodon_delete_status($options, $remoteId, true);
						if (!empty($deleted ['ok']) || plugin_mastodon_status_missing_response($deleted)) {
							$queuedDescendants = plugin_mastodon_queue_comment_descendant_remote_rechecks($state, $childIndex, $remoteId);
							plugin_mastodon_state_set_comment_tombstone($state, $remoteId, $entryId, $commentId, 'local_deleted_remote_deleted');
							plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
							plugin_mastodon_state_remove_comment_mapping($state, $entryId, $commentId);
							$state ['deletion_stats'] ['deleted_remote_comments']++;
							$shardChanged = true;
							if ($queuedDescendants > 0) {
								plugin_mastodon_log('Queued ' . $queuedDescendants . ' direct local descendant comment(s) for follow-up verification after deleting remote comment ' . $remoteId);
							}
							continue;
						}
						$hadFailure = true;
						$state ['last_error'] = 'comment_remote_delete_failed: ' . $entryId . '/' . $commentId . ' (' . plugin_mastodon_response_error_message($deleted) . ')';
						plugin_mastodon_log('Deletion sync failed to delete remote status for local comment ' . $entryId . '/' . $commentId . ': ' . plugin_mastodon_response_error_message($deleted));
						if (plugin_mastodon_rate_limit_state_error() !== '') {
							$state ['deletion_cursor_comments'] = $commentCursorBefore;
							$deletionInterrupted = true;
							break;
						}
						continue;
					}

					if (!plugin_mastodon_mapping_matches_deletion_lookup_window($options, $commentMeta, $commentId, $force)) {
						plugin_mastodon_log_skip('deletion_comment_outside_lookup_window', 'remote deletion comment lookup', 'remote deletion comment lookups', $entryId . '/' . $commentId, 'because they are outside the scheduled deletion lookup window');
						continue;
					}

					$remote = plugin_mastodon_fetch_status($options, $remoteId);
					if (plugin_mastodon_status_missing_response($remote)) {
						if (plugin_mastodon_should_import_remote_to_local($options)) {
							$queuedDescendants = plugin_mastodon_queue_comment_descendant_remote_rechecks($state, $childIndex, $remoteId);
							if (comment_exists($entryId, $commentId)) {
								plugin_mastodon_local_write_guard_enter();
								try {
									comment_delete($entryId, $commentId);
								} finally {
									plugin_mastodon_local_write_guard_leave();
								}
							}
							plugin_mastodon_state_set_comment_tombstone($state, $remoteId, $entryId, $commentId, 'remote_missing_local_deleted');
							plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
							plugin_mastodon_state_remove_comment_mapping($state, $entryId, $commentId);
							$state ['deletion_stats'] ['deleted_local_comments']++;
							if ($queuedDescendants > 0) {
								plugin_mastodon_log('Queued ' . $queuedDescendants . ' direct local descendant comment(s) for follow-up verification after remote comment ' . $remoteId . ' disappeared');
							}
						} else {
							plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
							plugin_mastodon_state_unlink_comment_remote_for_reexport($state, $entryId, $commentId);
							plugin_mastodon_log('Remote reply ' . $remoteId . ' is missing; kept local comment ' . $entryId . '/' . $commentId . ' and queued it for re-export because Mastodon-to-FlatPress import is disabled');
						}
						$shardChanged = true;
						continue;
					}
					if (empty($remote ['ok'])) {
						$hadFailure = true;
						$state ['last_error'] = 'comment_remote_lookup_failed: ' . $entryId . '/' . $commentId . ' (' . plugin_mastodon_response_error_message($remote) . ')';
						plugin_mastodon_log('Deletion sync failed to fetch remote status for local comment ' . $entryId . '/' . $commentId . ': ' . plugin_mastodon_response_error_message($remote));
						if (plugin_mastodon_rate_limit_state_error() !== '') {
							$state ['deletion_cursor_comments'] = $commentCursorBefore;
							$deletionInterrupted = true;
							break;
						}
					}
				}

				if ($deletionInterrupted) {
					break;
				}
				if (!$shardChanged) {
					plugin_mastodon_state_unload_comment_shard_from_memory($state, $commentShardEntryId);
				}
			}

			if (!$deletionInterrupted && $processedCommentMappings >= $remainingCommentMappings) {
				$state ['deletion_cursor_comments'] = '';
			}
		}
	} else {
		plugin_mastodon_log('Deletion synchronization is running in targeted descendant recheck mode');
	}

	plugin_mastodon_log_flush_skip_summaries();

	$pendingCommentRechecksRemaining = false;
	if ($syncCommentsAndReplies) {
		foreach (plugin_mastodon_state_pending_recheck_entry_ids($state) as $pendingRecheckEntryId) {
			plugin_mastodon_state_load_comment_shard_into($state, $pendingRecheckEntryId);
		}
		$childIndex = plugin_mastodon_build_comment_remote_child_index($state);
		$pendingCommentRechecksRemaining = plugin_mastodon_process_pending_comment_remote_rechecks($options, $state, $childIndex, $hadFailure);
	} else {
		$state ['pending_comment_remote_rechecks'] = array();
		$state ['deletion_cursor_comments'] = '';
	}
	$rateLimitError = plugin_mastodon_rate_limit_state_error();
	if ($rateLimitError !== '') {
		$hadFailure = true;
		$state ['last_error'] = $rateLimitError;
	}

	if (!$hadFailure) {
		$deletionCompletionTimestamp = time();
		$state ['last_deletion_run'] = gmdate('Y-m-d H:i:s', $deletionCompletionTimestamp);
		$state ['deletion_cursor_entries'] = '';
		$state ['deletion_cursor_comments'] = '';
		$state ['deletion_retry_not_before'] = '';
		$localDeadline = plugin_mastodon_state_local_entry_deletion_deadline($state);
		$pending = $pendingCommentRechecksRemaining || $localDeadline !== '';
		$nextDeadline = $localDeadline !== '' ? $localDeadline : gmdate('Y-m-d H:i:s', $deletionCompletionTimestamp + PLUGIN_MASTODON_COOLDOWN_TTL);
		plugin_mastodon_state_set_deletions_pending($state, $pending, $localDeadline !== '' ? 'full' : ($pendingCommentRechecksRemaining ? 'comment_rechecks' : 'full'), $nextDeadline);
		$state ['last_error'] = '';
		if ($pendingCommentRechecksRemaining) {
			plugin_mastodon_log('Deletion synchronization completed with pending descendant reply rechecks');
		} else {
			plugin_mastodon_log('Deletion synchronization completed successfully');
		}
	} else {
		$rateSummary = plugin_mastodon_rate_limit_guard_summary();
		$remoteReset = plugin_mastodon_parse_iso_timestamp(isset($rateSummary ['remote_reset']) ? (string) $rateSummary ['remote_reset'] : '');
		$retryTimestamp = max(time() + PLUGIN_MASTODON_COOLDOWN_TTL, $remoteReset, plugin_mastodon_parse_iso_timestamp(isset($state ['deletion_retry_not_before']) ? (string) $state ['deletion_retry_not_before'] : ''));
		$deletionRetryNotBefore = gmdate('Y-m-d H:i:s', $retryTimestamp);
		$state ['deletion_retry_not_before'] = $deletionRetryNotBefore;
		if (plugin_mastodon_state_local_entry_deletion_deadline($state) !== '') {
			plugin_mastodon_state_set_deletions_pending($state, true, 'full', $deletionRetryNotBefore);
		} elseif ($pendingCommentRechecksRemaining) {
			plugin_mastodon_state_set_deletions_pending($state, true, 'comment_rechecks', $deletionRetryNotBefore);
		} else {
			plugin_mastodon_state_set_deletions_pending($state, true, $commentRecheckOnly ? 'comment_rechecks' : 'full', $deletionRetryNotBefore);
		}
		if ($state ['last_error'] === '') {
			$state ['last_error'] = 'deletion_sync_failed';
		}
		plugin_mastodon_log('Deletion synchronization failed');
	}

	$stateWriteOk = plugin_mastodon_state_write_with_last_error($state, 'state_write_failed_after_deletion_sync');
	if (!$stateWriteOk && !$hadFailure) {
		if ($state ['last_error'] === '') {
			$state ['last_error'] = 'state_write_failed_after_deletion_sync';
		}
		plugin_mastodon_log('Deletion synchronization completed but state.json could not be persisted: ' . $state ['last_error']);
	}
	@flock($lockHandle, LOCK_UN);
	@fclose($lockHandle);
	plugin_mastodon_rate_limit_guard_stop();

	return array('ok' => (!$hadFailure && $stateWriteOk), 'state' => $state, 'message' => $state ['last_error'] === '' ? 'ok' : $state ['last_error']);
}
?>

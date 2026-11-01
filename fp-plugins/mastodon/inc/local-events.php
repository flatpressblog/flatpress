<?php
/**
 * Mastodon local-events declarations. Loaded by plugin.mastodon.php.
 * Runtime constants and registrations remain in the main plugin file.
 */

/**
 * Check whether public comment authors should be offered the Mastodon reply opt-in.
 *
 * The opt-in is only relevant when the plugin is able to export local comments
 * as Mastodon replies. It intentionally stays active in one-way mode because
 * that mode blocks Mastodon-to-FlatPress imports, but still allows local
 * FlatPress comments to be exported unless comment/reply synchronization is
 * disabled separately.
 *
 * @param array<string, string>|null $options
 * @return bool
 */
function plugin_mastodon_comment_to_reply_optin_required($options = null) {
	if ($options === null) {
		$options = plugin_mastodon_get_options();
	}
	$options = is_array($options) ? $options : array();

	return plugin_mastodon_should_sync_comments_and_replies($options);
}

/**
 * Check whether the current comment POST explicitly opts into Mastodon/Fediverse export.
 * @return bool
 */
function plugin_mastodon_comment_to_reply_optin_submitted() {
	if (!isset($_POST ['mastodon_comment_optin'])) {
		return false;
	}
	$value = $_POST ['mastodon_comment_optin'];
	if (is_array($value)) {
		return false;
	}
	$value = strtolower(trim((string) $value));
	return $value !== '' && $value !== '0' && $value !== 'false' && $value !== 'off' && $value !== 'no';
}

/**
 * Check whether a saved/pending local comment carries a stored public Mastodon opt-in marker.
 *
 * This covers moderated comments that were captured by CommentCenter before
 * FlatPress wrote the final comment file. The actual export grant still lives
 * in the Mastodon state; the marker is only accepted as an explicit frontend
 * opt-in signal if another moderation path carries it through.
 *
 * @param array<string, mixed> $comment
 * @return bool
 */
function plugin_mastodon_local_comment_has_stored_reply_optin($comment) {
	if (!is_array($comment) || $comment === array()) {
		return false;
	}
	foreach (array('mastodon_comment_optin', 'MASTODON_COMMENT_OPTIN') as $key) {
		if (isset($comment [$key]) && plugin_mastodon_normalize_boolean_option($comment [$key]) === '1') {
			return true;
		}
	}
	return false;
}

/**
 * Check whether the current request is handled by an authenticated FlatPress user.
 * @return bool
 */
function plugin_mastodon_current_user_is_authenticated() {
	return function_exists('user_loggedin') && user_loggedin();
}

/**
 * Check whether saving this comment should create a local comment-to-reply export grant.
 *
 * Visitor grants come from the explicit public opt-in checkbox. Authenticated
 * grants are recorded only for comments that are either stored as logged-in
 * comments or newly saved while a FlatPress user is logged in. Updates of
 * existing visitor comments do not create an admin grant implicitly.
 *
 * @param array<string, string> $options
 * @param array<string, mixed> $comment
 * @param bool $isUpdate
 * @return string
 */
function plugin_mastodon_local_comment_export_grant_source($options, $comment, $isUpdate) {
	$comment = is_array($comment) ? $comment : array();
	if (plugin_mastodon_local_comment_from_authenticated_author($comment)) {
		return 'authenticated';
	}
	if (plugin_mastodon_local_comment_has_stored_reply_optin($comment)) {
		return 'frontend';
	}
	/**
	 * Do not grant from the current admin session alone: CommentCenter can publish visitor comments there.
	 * The stored comment itself must carry LOGGEDIN, otherwise a visitor opt-in state grant is required.
	 * This prevents moderated visitor comments without opt-in from being exported to Mastodon.
	 */
	if (plugin_mastodon_comment_to_reply_optin_required($options) && plugin_mastodon_comment_to_reply_optin_submitted()) {
		return 'frontend';
	}
	return '';
}

/**
 * Output the optional visitor opt-in for publishing a local comment as a Mastodon reply.
 * @return void
 */
function plugin_mastodon_optin_comment_to_reply() {
	if (!plugin_mastodon_comment_to_reply_optin_required(null)) {
		return;
	}

	$label = plugin_mastodon_widget_lang_string('optin_label', '<em>Your comment will be published via my Mastodon account and shared in the Fediverse.</em> I agree.');
	$checked = plugin_mastodon_comment_to_reply_optin_submitted() ? ' checked="checked"' : '';
	echo '<p class="mastodon-comment-optin"><label for="mastodon_comment_optin"><input type="checkbox" id="mastodon_comment_optin" name="mastodon_comment_optin" value="1"' . $checked . '> ' . $label . '</label></p>';
}

/**
 * Preserve a frontend Mastodon opt-in when CommentCenter stores a visitor
 * comment for later moderation instead of letting FlatPress save it immediately.
 *
 * CommentCenter writes its own pending file and returns false from
 * comment_validate(), therefore the normal comment_saved hook is not reached
 * during the visitor POST. This hook stores only the export grant in the
 * Mastodon state, without touching the final FlatPress comment file.
 *
 * @param string $entryId
 * @param array<string, mixed> $comment
 * @param string $why
 * @param string $file
 * @return void
 */
function plugin_mastodon_on_commentcenter_comment_logged($entryId, $comment = array(), $why = '', $file = '') {
	$options = plugin_mastodon_get_options();
	$options = is_array($options) ? $options : array();
	if (!plugin_mastodon_should_sync_comments_and_replies($options)) {
		return;
	}
	if (!plugin_mastodon_comment_to_reply_optin_required($options) || !plugin_mastodon_comment_to_reply_optin_submitted()) {
		return;
	}
	$entryId = trim((string) $entryId);
	$comment = is_array($comment) ? $comment : array();
	if ($entryId === '' || $comment === array()) {
		return;
	}
	$commentId = isset($comment ['id']) ? trim((string) $comment ['id']) : '';
	if ($commentId === '' && isset($comment ['date'])) {
		$commentId = bdb_idfromtime(BDB_COMMENT, $comment ['date']);
	}
	if ($commentId === '') {
		return;
	}
	$state = plugin_mastodon_state_read(array($entryId));
	plugin_mastodon_state_set_comment_reply_optin($state, $entryId, $commentId, '', 'frontend', $file);
	plugin_mastodon_state_write($state);
}

/**
 * Remove an opt-in grant when CommentCenter permanently discards its pending comment.
 *
 * If a final comment with the same id already exists, only the pending-file
 * marker is finalized. This avoids revoking a valid grant when an obsolete
 * duplicate pending file is removed after publication.
 *
 * @param string $entryId
 * @param string $commentId
 * @param array<string, mixed> $comment
 * @param string $file
 * @return void
 */
function plugin_mastodon_on_commentcenter_comment_discarded($entryId, $commentId, $comment = array(), $file = '') {
	$entryId = trim((string) $entryId);
	$commentId = trim((string) $commentId);
	if ($entryId === '' || $commentId === '') {
		return;
	}
	$state = plugin_mastodon_state_read(array($entryId));
	if (function_exists('comment_exists') && comment_exists($entryId, $commentId)) {
		if (plugin_mastodon_state_finalize_comment_reply_optin($state, $entryId, $commentId)) {
			plugin_mastodon_state_write($state);
		}
		return;
	}
	if (!plugin_mastodon_state_has_comment_reply_optin($state, $entryId, $commentId)) {
		return;
	}
	plugin_mastodon_state_remove_comment_reply_optin($state, $entryId, $commentId);
	plugin_mastodon_state_remove_dirty_comment($state, $entryId, $commentId);
	plugin_mastodon_state_write($state);
}

/**
 * Check whether a local FlatPress comment was written by an authenticated site user.
 *
 * Authenticated FlatPress users do not see the public visitor opt-in checkbox
 * in the shared comment form. Their comments are operator/admin authored
 * content and may therefore be exported without a visitor opt-in marker.
 *
 * @param array<string, mixed> $comment
 * @return bool
 */
function plugin_mastodon_local_comment_from_authenticated_author($comment) {
	if (!is_array($comment) || $comment === array()) {
		return false;
	}
	foreach (array('loggedin', 'LOGGEDIN') as $key) {
		if (isset($comment [$key]) && plugin_mastodon_normalize_boolean_option($comment [$key]) === '1') {
			return true;
		}
	}
	return false;
}

/**
 * Increase the local-write guard depth while the plugin mirrors remote Mastodon data into FlatPress.
 * @return void
 */
function plugin_mastodon_local_write_guard_enter() {
	if (!isset($GLOBALS ['plugin_mastodon_local_write_guard_depth']) || !is_numeric($GLOBALS ['plugin_mastodon_local_write_guard_depth'])) {
		$GLOBALS ['plugin_mastodon_local_write_guard_depth'] = 0;
	}
	$GLOBALS ['plugin_mastodon_local_write_guard_depth'] = (int) $GLOBALS ['plugin_mastodon_local_write_guard_depth'] + 1;
}

/**
 * Decrease the local-write guard depth after a plugin-owned FlatPress write.
 * @return void
 */
function plugin_mastodon_local_write_guard_leave() {
	if (!isset($GLOBALS ['plugin_mastodon_local_write_guard_depth']) || !is_numeric($GLOBALS ['plugin_mastodon_local_write_guard_depth'])) {
		$GLOBALS ['plugin_mastodon_local_write_guard_depth'] = 0;
		return;
	}
	$GLOBALS ['plugin_mastodon_local_write_guard_depth'] = max(0, (int) $GLOBALS ['plugin_mastodon_local_write_guard_depth'] - 1);
}

/**
 * Return whether FlatPress write hooks are currently triggered by Mastodon remote mirroring.
 * @return bool
 */
function plugin_mastodon_local_write_guard_active() {
	return isset($GLOBALS ['plugin_mastodon_local_write_guard_depth']) && (int) $GLOBALS ['plugin_mastodon_local_write_guard_depth'] > 0;
}

/**
 * Check whether dirty tracking should persist state for a local FlatPress write hook.
 * @return array<string, string>
 */
function plugin_mastodon_dirty_tracking_options() {
	if (plugin_mastodon_local_write_guard_active()) {
		return array();
	}
	$options = plugin_mastodon_get_options();
	if ($options ['instance_url'] === '' || $options ['access_token'] === '') {
		return array();
	}
	return $options;
}

/**
 * Mark a local FlatPress entry write as dirty for a later Mastodon sync.
 * @param string $entryId
 * @param array<string, mixed> $entry
 * @param array<string, mixed> $oldEntry
 * @param bool $isUpdate
 * @return void
 */
function plugin_mastodon_on_entry_saved($entryId, $entry, $oldEntry = array(), $isUpdate = false) {
	$options = plugin_mastodon_dirty_tracking_options();
	if ($options === array()) {
		return;
	}
	$entryId = trim((string) $entryId);
	$entry = is_array($entry) ? $entry : array();
	if ($entryId === '' || $entry === array()) {
		return;
	}
	if (isset($entry ['categories']) && is_array($entry ['categories']) && in_array('draft', $entry ['categories'], true)) {
		return;
	}
	$state = plugin_mastodon_state_read(array());
	if (isset($state ['local_entry_deletions'] [$entryId])) {
		unset($state ['local_entry_deletions'] [$entryId]);
		plugin_mastodon_state_write($state);
	}
	$meta = plugin_mastodon_state_get_entry_meta($state, $entryId);
	if (!empty($meta ['source']) && strtolower((string) $meta ['source']) === 'remote') {
		return;
	}
	if (!plugin_mastodon_local_item_matches_sync_start($options, $entry, $entryId)) {
		plugin_mastodon_state_remove_dirty_entry($state, $entryId);
		plugin_mastodon_state_write($state);
		return;
	}
	$entryHash = plugin_mastodon_entry_hash($entry);
	$hasRemoteMapping = !empty($meta ['remote_id']);
	$hashChanged = empty($meta ['hash']) || (string) $meta ['hash'] !== $entryHash;
	$insideActiveWindow = plugin_mastodon_local_item_matches_content_window($options, $entry, $entryId, false);
	if ($hasRemoteMapping && $hashChanged) {
		plugin_mastodon_state_set_dirty_entry($state, $entryId, $entryHash);
		plugin_mastodon_state_write($state);
		return;
	}
	if (!$hasRemoteMapping && !$insideActiveWindow) {
		plugin_mastodon_state_remove_dirty_entry($state, $entryId);
		plugin_mastodon_state_write($state);
		return;
	}
	if ($insideActiveWindow) {
		plugin_mastodon_state_remove_dirty_entry($state, $entryId);
		plugin_mastodon_state_write($state);
	}
}

/**
 * Mark a local FlatPress entry deletion for the Mastodon deletion sync.
 * @param string $entryId
 * @param array<string, mixed> $oldEntry
 * @return void
 */
function plugin_mastodon_on_entry_deleted($entryId, $oldEntry = array()) {
	$options = plugin_mastodon_dirty_tracking_options();
	if ($options === array()) {
		return;
	}
	$entryId = trim((string) $entryId);
	if ($entryId === '') {
		return;
	}
	$state = plugin_mastodon_state_read(array());
	$meta = plugin_mastodon_state_get_entry_meta($state, $entryId);
	plugin_mastodon_state_remove_dirty_entry($state, $entryId);
	if (!empty($meta ['remote_id'])) {
		plugin_mastodon_state_queue_local_entry_deletion($state, $entryId);
		plugin_mastodon_state_write($state);
	}
}

/**
 * Mark a local FlatPress comment write as dirty for a later Mastodon sync.
 * @param string $entryId
 * @param string $commentId
 * @param array<string, mixed> $comment
 * @param array<string, mixed> $oldComment
 * @param bool $isUpdate
 * @return void
 */
function plugin_mastodon_on_comment_saved($entryId, $commentId, $comment = array(), $oldComment = array(), $isUpdate = false) {
	if (plugin_mastodon_local_write_guard_active()) {
		return;
	}
	$options = plugin_mastodon_get_options();
	$options = is_array($options) ? $options : array();
	$entryId = trim((string) $entryId);
	$commentId = trim((string) $commentId);
	$comment = is_array($comment) ? $comment : array();
	$isUpdate = (bool) $isUpdate;
	if ($entryId === '' || $commentId === '' || $comment === array()) {
		return;
	}
	if (!plugin_mastodon_should_sync_comments_and_replies($options)) {
		$state = plugin_mastodon_state_read(array($entryId));
		plugin_mastodon_state_remove_dirty_comment($state, $entryId, $commentId);
		plugin_mastodon_state_remove_comment_reply_optin($state, $entryId, $commentId);
		plugin_mastodon_state_write($state);
		return;
	}
	if (!plugin_mastodon_local_item_matches_sync_start($options, $comment, $commentId)) {
		$state = plugin_mastodon_state_read(array($entryId));
		plugin_mastodon_state_remove_dirty_comment($state, $entryId, $commentId);
		plugin_mastodon_state_remove_comment_reply_optin($state, $entryId, $commentId);
		plugin_mastodon_state_write($state);
		return;
	}
	$entry = entry_parse($entryId);
	if (!$entry || !is_array($entry)) {
		return;
	}
	$state = plugin_mastodon_state_read(array($entryId));
	$commentMeta = plugin_mastodon_state_get_comment_meta($state, $entryId, $commentId);
	if (!empty($commentMeta ['source']) && strtolower((string) $commentMeta ['source']) === 'remote') {
		return;
	}
	$pendingGrantFinalized = plugin_mastodon_state_finalize_comment_reply_optin($state, $entryId, $commentId);
	$grantSource = plugin_mastodon_local_comment_export_grant_source($options, $comment, $isUpdate);
	if ($grantSource !== '') {
		plugin_mastodon_state_set_comment_reply_optin($state, $entryId, $commentId, '', $grantSource);
	}
	$mayExportNewLocalComment = $grantSource !== '' || plugin_mastodon_state_has_comment_reply_optin($state, $entryId, $commentId);
	if (trim((string) (isset($options ['instance_url']) ? $options ['instance_url'] : '')) === ''
		|| trim((string) (isset($options ['access_token']) ? $options ['access_token'] : '')) === '') {
		if ($grantSource !== '' || $pendingGrantFinalized) {
			plugin_mastodon_state_write($state);
		}
		return;
	}
	$entryMeta = plugin_mastodon_state_get_entry_meta($state, $entryId);
	$commentHash = plugin_mastodon_comment_hash($comment);
	$hasCommentRemoteMapping = !empty($commentMeta ['remote_id']);
	$commentHashChanged = empty($commentMeta ['hash']) || (string) $commentMeta ['hash'] !== $commentHash;
	$insideActiveWindow = plugin_mastodon_local_item_matches_content_window($options, $comment, $commentId, false);
	if ($hasCommentRemoteMapping && $commentHashChanged) {
		plugin_mastodon_state_set_dirty_comment($state, $entryId, $commentId, $commentHash);
		plugin_mastodon_state_write($state);
		return;
	}
	if (!$hasCommentRemoteMapping && $insideActiveWindow) {
		if ($mayExportNewLocalComment && !empty($entryMeta ['remote_id'])) {
			plugin_mastodon_state_set_dirty_comment($state, $entryId, $commentId, $commentHash);
		} elseif ($mayExportNewLocalComment && empty($entryMeta ['source']) && plugin_mastodon_local_item_matches_sync_start($options, $entry, $entryId)) {
			plugin_mastodon_state_set_dirty_comment($state, $entryId, $commentId, $commentHash);
			plugin_mastodon_state_set_dirty_entry($state, $entryId, plugin_mastodon_entry_hash($entry));
		} else {
			plugin_mastodon_state_remove_dirty_comment($state, $entryId, $commentId);
		}
		plugin_mastodon_state_write($state);
		return;
	}
	if (!$hasCommentRemoteMapping) {
		plugin_mastodon_state_remove_dirty_comment($state, $entryId, $commentId);
		plugin_mastodon_state_write($state);
		return;
	}
}

/**
 * Mark a local FlatPress comment deletion for the Mastodon deletion sync.
 * @param string $entryId
 * @param string $commentId
 * @param array<string, mixed> $oldComment
 * @return void
 */
function plugin_mastodon_on_comment_deleted($entryId, $commentId, $oldComment = array()) {
	$options = plugin_mastodon_dirty_tracking_options();
	if ($options === array()) {
		return;
	}
	$entryId = trim((string) $entryId);
	$commentId = trim((string) $commentId);
	if ($entryId === '' || $commentId === '') {
		return;
	}
	if (!plugin_mastodon_should_sync_comments_and_replies($options)) {
		$state = plugin_mastodon_state_read(array($entryId));
		plugin_mastodon_state_remove_dirty_comment($state, $entryId, $commentId);
		plugin_mastodon_state_remove_comment_reply_optin($state, $entryId, $commentId);
		plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
		plugin_mastodon_state_write($state);
		return;
	}
	$state = plugin_mastodon_state_read(array($entryId));
	$meta = plugin_mastodon_state_get_comment_meta($state, $entryId, $commentId);
	plugin_mastodon_state_remove_dirty_comment($state, $entryId, $commentId);
	plugin_mastodon_state_remove_comment_reply_optin($state, $entryId, $commentId);
	if (!empty($meta ['remote_id'])) {
		$remoteId = trim((string) $meta ['remote_id']);
		$source = isset($meta ['source']) ? strtolower((string) $meta ['source']) : '';
		if ($remoteId !== '' && $source === 'remote') {
			plugin_mastodon_state_set_comment_tombstone($state, $remoteId, $entryId, $commentId, 'local_deleted_imported_remote_ignored');
			plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
			plugin_mastodon_state_remove_comment_mapping($state, $entryId, $commentId);
			plugin_mastodon_log('Keeping locally deleted imported remote reply ' . $remoteId . ' ignored in FlatPress without attempting a Mastodon delete for comment ' . $entryId . '/' . $commentId);
			plugin_mastodon_state_write($state);
			return;
		}
		if ($remoteId !== '' && $source === 'local') {
			plugin_mastodon_state_set_comment_tombstone($state, $remoteId, $entryId, $commentId, 'local_deleted_pending_remote_delete');
			plugin_mastodon_state_remove_pending_comment_remote_recheck($state, $entryId, $commentId);
			plugin_mastodon_state_set_deletions_pending($state, true, 'full', '');
			plugin_mastodon_log('Protected locally deleted exported FlatPress comment ' . $entryId . '/' . $commentId . ' from stale re-import until remote reply ' . $remoteId . ' is processed by deletion synchronization');
			plugin_mastodon_state_write($state);
			return;
		}
		plugin_mastodon_state_set_deletions_pending($state, true, 'full', '');
		plugin_mastodon_state_write($state);
	}
}
?>

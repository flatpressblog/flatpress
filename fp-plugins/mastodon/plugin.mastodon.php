<?php
/**
 * Plugin Name: Mastodon
 * Plugin URI: https://www.flatpress.org
 * Description: Synchronizes FlatPress entries and comments with Mastodon and provides a Mastodon profile widget. <a href="./fp-plugins/mastodon/doc_mastodon.txt" title="Instructions" target="_blank">[Instructions]</a>
 * Version: 2.8.3
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
	/**
	 * Internal runtime data structures:
	 * - plugin options are normalized associative arrays with string values
	 * - runtime state is a nested associative array persisted as JSON
	 */
	define('PLUGIN_MASTODON_STATE_FILE', PLUGIN_MASTODON_STATE_DIR . 'state.json');
}
if (!defined('PLUGIN_MASTODON_SCHEDULER_STATE_FILE')) {
	/**
	 * Small request-time scheduler summary derived from state.json.
	 *
	 * This file intentionally contains only due-check and admin summary fields
	 * so regular frontend requests do not have to load the full synchronization
	 * mappings from state.json when no sync is due.
	 */
	define('PLUGIN_MASTODON_SCHEDULER_STATE_FILE', PLUGIN_MASTODON_STATE_DIR . 'scheduler-state.json');
}
if (!defined('PLUGIN_MASTODON_PROFILE_DIR')) {
	/**
	 * Public, cacheable profile data used by the compact FlatPress sidebar widget.
	 *
	 * The directory intentionally contains only public Mastodon account information
	 * and a locally cached avatar image. OAuth tokens and other secrets must never
	 * be written here because the avatar file is referenced from the frontend.
	 */
	define('PLUGIN_MASTODON_PROFILE_DIR', PLUGIN_MASTODON_STATE_DIR . 'profile' . DIRECTORY_SEPARATOR);
}
if (!defined('PLUGIN_MASTODON_PROFILE_FILE')) {
	define('PLUGIN_MASTODON_PROFILE_FILE', PLUGIN_MASTODON_PROFILE_DIR . 'profile.json');
}
if (!defined('PLUGIN_MASTODON_PROFILE_AVATAR_MAX_BYTES')) {
	define('PLUGIN_MASTODON_PROFILE_AVATAR_MAX_BYTES', 5242880);
}
if (!defined('PLUGIN_MASTODON_PROFILE_REQUEST_TIMEOUT')) {
	define('PLUGIN_MASTODON_PROFILE_REQUEST_TIMEOUT', 15);
}
if (!defined('PLUGIN_MASTODON_COMMENT_SHARD_DIR')) {
	/**
	 * Per-entry comment mapping shards.
	 *
	 * The authoritative state.json keeps global sync metadata and reverse indices.
	 * High-volume comment mappings are stored by parent entry so synchronization
	 * code can evolve toward bounded worksets without changing Mastodon API
	 * payloads.
	 */
	define('PLUGIN_MASTODON_COMMENT_SHARD_DIR', PLUGIN_MASTODON_STATE_DIR . 'state-comments' . DIRECTORY_SEPARATOR);
}
if (!defined('PLUGIN_MASTODON_LOCK_FILE')) {
	define('PLUGIN_MASTODON_LOCK_FILE', PLUGIN_MASTODON_STATE_DIR . 'sync.lock');
}
if (!defined('PLUGIN_MASTODON_STATE_WRITE_LOCK_FILE')) {
	/**
	 * Serializes all state.json/comment-shard mutations, including post-save
	 * dirty hooks that do not run under the long-lived synchronization lock.
	 */
	define('PLUGIN_MASTODON_STATE_WRITE_LOCK_FILE', PLUGIN_MASTODON_STATE_DIR . 'state-write.lock');
}
if (!defined('PLUGIN_MASTODON_GUARD_FILE')) {
	define('PLUGIN_MASTODON_GUARD_FILE', PLUGIN_MASTODON_STATE_DIR . 'sync.guard.json');
}
if (!defined('PLUGIN_MASTODON_RATE_LIMIT_WINDOW_FILE')) {
	define('PLUGIN_MASTODON_RATE_LIMIT_WINDOW_FILE', PLUGIN_MASTODON_STATE_DIR . 'rate-limit-windows.json');
}
if (!defined('PLUGIN_MASTODON_LOG_FILE')) {
	define('PLUGIN_MASTODON_LOG_FILE', PLUGIN_MASTODON_STATE_DIR . 'sync.log');
}
if (!defined('PLUGIN_MASTODON_LOG_MAX_BYTES')) {
	/**
	 * Rotate sync.log before appending when it reaches roughly one MiB.
	 */
	define('PLUGIN_MASTODON_LOG_MAX_BYTES', 1048576);
}
if (!defined('PLUGIN_MASTODON_LOG_ROTATE_FILES')) {
	define('PLUGIN_MASTODON_LOG_ROTATE_FILES', 3);
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
if (!defined('PLUGIN_MASTODON_OLD_THREAD_CONTEXT_ROTATION_LIMIT')) {
	define('PLUGIN_MASTODON_OLD_THREAD_CONTEXT_ROTATION_LIMIT', 3);
}
if (!defined('PLUGIN_MASTODON_NOTIFICATION_PAGE_LIMIT')) {
	define('PLUGIN_MASTODON_NOTIFICATION_PAGE_LIMIT', 30);
}
// ttl for sync guards
if (!defined('PLUGIN_MASTODON_STATE_FALLBACK_TTL')) {
	define('PLUGIN_MASTODON_STATE_FALLBACK_TTL', 300);
}
if (!defined('PLUGIN_MASTODON_COOLDOWN_TTL')) {
	define('PLUGIN_MASTODON_COOLDOWN_TTL', 300);
}
// Media download limits and per-content-run budgets.
if (!defined('PLUGIN_MASTODON_DOWNLOAD_IMAGE_BYTES')) {
	define('PLUGIN_MASTODON_DOWNLOAD_IMAGE_BYTES', 16777216);
}
if (!defined('PLUGIN_MASTODON_DOWNLOAD_AV_BYTES')) {
	define('PLUGIN_MASTODON_DOWNLOAD_AV_BYTES', 103809024);
}
if (!defined('PLUGIN_MASTODON_DOWNLOAD_TOTAL_BYTES')) {
	define('PLUGIN_MASTODON_DOWNLOAD_TOTAL_BYTES', 536870912);
}
if (!defined('PLUGIN_MASTODON_DOWNLOAD_REQUESTS')) {
	define('PLUGIN_MASTODON_DOWNLOAD_REQUESTS', 64);
}
// Bound the in-memory multipart fallback used when cURL is unavailable.
if (!defined('PLUGIN_MASTODON_MULTIPART_MAX_BYTES')) {
	define('PLUGIN_MASTODON_MULTIPART_MAX_BYTES', 67108864);
}
if (!defined('PLUGIN_MASTODON_MULTIPART_MEMORY_RESERVE')) {
	define('PLUGIN_MASTODON_MULTIPART_MEMORY_RESERVE', 8388608);
}
// Rate limit and budget guards
if (!defined('PLUGIN_MASTODON_RUN_REQUEST_BUDGET')) {
	define('PLUGIN_MASTODON_RUN_REQUEST_BUDGET', 240);
}
if (!defined('PLUGIN_MASTODON_RUN_MEDIA_UPLOAD_BUDGET')) {
	define('PLUGIN_MASTODON_RUN_MEDIA_UPLOAD_BUDGET', 24);
}
if (!defined('PLUGIN_MASTODON_RUN_DELETE_BUDGET')) {
	define('PLUGIN_MASTODON_RUN_DELETE_BUDGET', 24);
}
if (!defined('PLUGIN_MASTODON_WINDOW_MEDIA_UPLOAD_BUDGET')) {
	define('PLUGIN_MASTODON_WINDOW_MEDIA_UPLOAD_BUDGET', PLUGIN_MASTODON_RUN_MEDIA_UPLOAD_BUDGET);
}
if (!defined('PLUGIN_MASTODON_WINDOW_MEDIA_UPLOAD_TTL')) {
	define('PLUGIN_MASTODON_WINDOW_MEDIA_UPLOAD_TTL', 1800);
}
if (!defined('PLUGIN_MASTODON_WINDOW_DELETE_BUDGET')) {
	define('PLUGIN_MASTODON_WINDOW_DELETE_BUDGET', PLUGIN_MASTODON_RUN_DELETE_BUDGET);
}
if (!defined('PLUGIN_MASTODON_WINDOW_DELETE_TTL')) {
	define('PLUGIN_MASTODON_WINDOW_DELETE_TTL', 1800);
}
if (!defined('PLUGIN_MASTODON_WINDOW_STATUS_PAGE_BUDGET')) {
	define('PLUGIN_MASTODON_WINDOW_STATUS_PAGE_BUDGET', 300);
}
if (!defined('PLUGIN_MASTODON_WINDOW_STATUS_PAGE_TTL')) {
	define('PLUGIN_MASTODON_WINDOW_STATUS_PAGE_TTL', 900);
}
if (!defined('PLUGIN_MASTODON_RATE_LIMIT_REMAINING_FLOOR')) {
	define('PLUGIN_MASTODON_RATE_LIMIT_REMAINING_FLOOR', 10);
}
if (!defined('PLUGIN_MASTODON_INSTANCE_REQUEST_TIMEOUT')) {
	define('PLUGIN_MASTODON_INSTANCE_REQUEST_TIMEOUT', 15);
}

// Internal declarations only; constants above and registrations below stay centralized.
require_once __DIR__ . '/inc/runtime.php';
require_once __DIR__ . '/inc/options.php';
require_once __DIR__ . '/inc/state.php';
require_once __DIR__ . '/inc/text.php';
require_once __DIR__ . '/inc/api.php';
require_once __DIR__ . '/inc/media-safety.php';
require_once __DIR__ . '/inc/media.php';
require_once __DIR__ . '/inc/profile.php';
require_once __DIR__ . '/inc/local-events.php';
require_once __DIR__ . '/inc/deletion.php';
require_once __DIR__ . '/inc/sync.php';
require_once __DIR__ . '/inc/admin.php';

add_action('wp_head', 'plugin_mastodon_head', 10);

add_action('init', 'plugin_mastodon_maybe_sync', 20);
add_action('comment_mastodon', 'plugin_mastodon_optin_comment_to_reply', 1, 0);
add_action('commentcenter_comment_logged', 'plugin_mastodon_on_commentcenter_comment_logged', 10, 4);
add_action('commentcenter_comment_discarded', 'plugin_mastodon_on_commentcenter_comment_discarded', 10, 4);
add_action('entry_saved', 'plugin_mastodon_on_entry_saved', 10, 4);
add_action('entry_deleted', 'plugin_mastodon_on_entry_deleted', 10, 2);
add_action('comment_saved', 'plugin_mastodon_on_comment_saved', 10, 5);
add_action('comment_deleted', 'plugin_mastodon_on_comment_deleted', 10, 3);

if (function_exists('register_widget')) {
	register_widget('mastodon', 'Mastodon', 'plugin_mastodon_widget');
}

if (class_exists('AdminPanelAction')) {
	require_once __DIR__ . '/inc/admin-actions.php';
	admin_addpanelaction('plugin', 'mastodon', true);
	admin_addpanelaction('plugin', 'mastodon_maintenance', false);
}
?>

<?php
// The archived installer is not a second, active entry point.
if (basename(__FILE__) !== 'setup.php') {
	http_response_code(404);
	exit;
}
define('FP_SETUP_REQUEST', true);
require_once('defaults.php');
define('SETUPTEMP_FILE', FP_CONTENT . 'settingup.lock');
require_once __DIR__ . '/setup/lib/permissions.lib.php';

// Diagnose unreadable existing data before config_load()/plugins can fail during bootstrap.
$language = isset($_POST ['language']) && is_string($_POST ['language']) ? $_POST ['language'] : LANG_DEFAULT;
if (!preg_match('/^[a-z]{2}-[a-z]{2}$/D', $language) || !is_file(__DIR__ . '/setup/lang/lang.' . $language . '.php')) {
	$language = 'en-us';
}
$lang = []; $err = [];
include __DIR__ . '/setup/lang/lang.' . $language . '.php';
if (!setup_data_preflight() || !setup_core_permissions(false)) {
	setup_render_preflight_errors();
	exit;
}
define('FP_SETUP_ACTIVE', true);
require_once(INCLUDES_DIR . 'includes.php');

/**
 * The installer must keep its session when persisting a new blog ID.
 * Authentication sessions continue to use their own, blog-specific cookie.
 * A newly confirmed proxy/TLS configuration must not change the installer cookie name.
 */
define('SESS_COOKIE', 'fpsetup_' . substr(hash('sha256', ABS_PATH), 0, 24));

@system_init();
if (session_status() === PHP_SESSION_ACTIVE && session_name() === SESS_COOKIE) {
	// Refresh attributes too, e.g. upgrade Secure after confirming forwarded HTTPS.
	$GLOBALS ['fp_setup_cookie_error'] = !fp_setcookie(SESS_COOKIE, session_id());
}

require('./setup/main.php');
?>

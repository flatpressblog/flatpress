<?php

function cookie_setup() {
	global $fp_config;

	// Check whether the connection is secure and define the cookie prefix
	if (!defined('COOKIE_PREFIX')) {
		define('COOKIE_PREFIX', is_https() ? '__secure-' : '');
	}

	// Defines the value for the SameSite attribute
	if (!defined('SAMESITE_VALUE')) {
		define('SAMESITE_VALUE', 'Lax');
	}

	if (!defined('COOKIEHASH')) {
		define('COOKIEHASH', $fp_config ['general'] ['blogid']);
	}

	// Definition of the different cookies with prefix
	if (!defined('USER_COOKIE')) {
		define('USER_COOKIE', COOKIE_PREFIX . 'fpuser_' . COOKIEHASH);
	}
	if (!defined('PASS_COOKIE')) {
		define('PASS_COOKIE', COOKIE_PREFIX . 'fppass_' . COOKIEHASH);
	}
	if (!defined('SESS_COOKIE')) {
		define('SESS_COOKIE', COOKIE_PREFIX . 'fpsess_' . COOKIEHASH);
	}

	// Cookie paths and settings
	if (!defined('COOKIEPATH')) {
		define('COOKIEPATH', preg_replace('|https?://[^/]+(/.*?)/?$|i', '$1', BLOG_BASEURL) ?: '/');
	}
	if (!defined('SITECOOKIEPATH')) {
		define('SITECOOKIEPATH', preg_replace('|https?://[^/]+(/.*?)/?$|i', '$1', BLOG_BASEURL) ?: '/');
	}
	if (!defined('COOKIE_DOMAIN')) {
		define('COOKIE_DOMAIN', false);
	}
	if (!defined('COOKIE_SECURE')) {
		define('COOKIE_SECURE', is_https());
	}
	if (!defined('COOKIE_HTTPONLY')) {
		define('COOKIE_HTTPONLY', true);
	}
}

// Function for deleting cookies (FlatPress setup)
function cookie_clear() {
	$cookie_expiry = time() - 31536000;

	setcookie(USER_COOKIE, '', get_cookie_options($cookie_expiry));
	setcookie(PASS_COOKIE, '', get_cookie_options($cookie_expiry));

	if (version_compare(PHP_VERSION, '7.3', '<')) {
		// If PHP 7.2 or lower, force SameSite via headers
		header('Set-Cookie: ' . USER_COOKIE . //
			'=; Expires=' . gmdate('D, d-M-Y H:i:s T', $cookie_expiry) . //
			'; Path=' . COOKIEPATH . //
			'; Secure=' . (COOKIE_SECURE ? 'true' : 'false') . //
			'; HttpOnly; SameSite=' . SAMESITE_VALUE);
		header('Set-Cookie: ' . PASS_COOKIE . //
			'=; Expires=' . gmdate('D, d-M-Y H:i:s T', $cookie_expiry) . //
			'; Path=' . COOKIEPATH . //
			'; Secure=' . (COOKIE_SECURE ? 'true' : 'false') . //
			'; HttpOnly; SameSite=' . SAMESITE_VALUE);
	}
}

// Function for creating cookie options based on the PHP version
function get_cookie_options($expiry_time) {
	if (version_compare(PHP_VERSION, '7.3', '>=')) {
		// Options for PHP 7.3 and higher
		return [
			'expires' => $expiry_time,
			'path' => COOKIEPATH,
			'domain' => COOKIE_DOMAIN,
			'secure' => COOKIE_SECURE,
			'httponly' => COOKIE_HTTPONLY,
			'samesite' => SAMESITE_VALUE
		];
	} else {
		// Options for PHP 7.2 and lower
		return [$expiry_time, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY];
	}
}

/**
 * The session area starts here
 */

// Session-Setup
function sess_setup() {

	ini_set('session.cookie_httponly', 1);
	ini_set('session.use_only_cookies', 1);
	ini_set('session.cookie_samesite', SAMESITE_VALUE);
	ini_set('session.cookie_path', COOKIEPATH);

	if (is_https()) {
		ini_set('session.cookie_secure', 1);
	} else {
		ini_set('session.cookie_secure', 0);
	}

	if (session_status() === PHP_SESSION_NONE) {

		if (SESSION_PATH != '') {
			session_save_path(SESSION_PATH);
		}

		session_name(SESS_COOKIE);

		// Setting up the session cookie parameters
		set_session_cookie_params();

		// Start of the session
		session_start();

		// If PHP 7.2 or lower, force SameSite via headers
		if (version_compare(PHP_VERSION, '7.3', '<')) {
			header('Set-Cookie: ' . session_name() . '=' . session_id() . //
				'; Path=' . COOKIEPATH . //
				'; Secure=' . (COOKIE_SECURE ? 'true' : 'false') . //
				'; HttpOnly; SameSite=' . SAMESITE_VALUE);
		}
	}
}

// Function for setting up the session cookie parameters
function set_session_cookie_params() {
	if (version_compare(PHP_VERSION, '7.3', '>=')) {
		// PHP 7.3 and higher
		session_set_cookie_params([
			'lifetime' => 0,
			'path' => COOKIEPATH,
			'domain' => COOKIE_DOMAIN,
			'secure' => COOKIE_SECURE,
			'httponly' => COOKIE_HTTPONLY,
			'samesite' => SAMESITE_VALUE
		]);
	} else {
		// PHP 7.2 and lower
		session_set_cookie_params(0, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
	}
}

// Function for adding a value to the session
function sess_add($key, $val) {
	$_SESSION [$key] = $val;
}

// Function for removing a value from the session
function sess_remove($key) {
	if (isset($_SESSION [$key])) {
		$oldval = $_SESSION [$key];
		unset($_SESSION [$key]);
		return $oldval;
	}
}

// Function for retrieving a value from the session
function sess_get($key) {
	return isset($_SESSION [$key]) ? $_SESSION [$key] : false;
}

// Function for closing the session
function sess_close() {
	unset($_SESSION);
	if (isset($_COOKIE [session_name()])) {

		// Set the cookie for deletion
		setcookie(session_name(), '', get_cookie_options(time() - 42000));

		if (version_compare(PHP_VERSION, '7.3', '<')) {
			// If PHP 7.2 or lower, force SameSite via headers to delete the cookie
			header('Set-Cookie: ' . session_name() . //
				'=; Expires=' . gmdate('D, d-M-Y H:i:s T', time() - 42000) . //
				'; Path=' . COOKIEPATH . //
				'; Secure=' . (COOKIE_SECURE ? 'true' : 'false') . //
				'; HttpOnly; SameSite=' . SAMESITE_VALUE);
		}

	}
	session_destroy();
}
?>

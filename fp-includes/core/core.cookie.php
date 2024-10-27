<?php

function cookie_setup() {
	global $fp_config;

	// Check whether the connection is secure and define the COOKIE_PREFIX
	if (!defined('COOKIE_PREFIX')) {
		define('COOKIE_PREFIX', is_https() ? '__secure-' : '');
	}

	// Sets the value for the SameSite attribute to
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

	// PHP 7.3 or higher: Set session cookie parameters including SameSite
	if (version_compare(PHP_VERSION, '7.3', '>=')) {
		session_set_cookie_params([
			'lifetime' => 0,
			'path' => COOKIEPATH,
			'domain' => COOKIE_DOMAIN,
			'secure' => COOKIE_SECURE,
			'httponly' => COOKIE_HTTPONLY,
			'samesite' => SAMESITE_VALUE
		]);
	} else {
		// PHP 7.2 and lower: SameSite not natively supported
		session_set_cookie_params(0, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);

	}

}

// Function for deleting cookies (FlatPress setup)
function cookie_clear() {
	$cookie_expiry = time() - 31536000;

	if (version_compare(PHP_VERSION, '7.3', '>=')) {
		// PHP 7.3 and higher: Support for SameSite in setcookie()
		setcookie(USER_COOKIE, '', $cookie_expiry, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY, ['samesite' => SAMESITE_VALUE]);
		setcookie(PASS_COOKIE, '', $cookie_expiry, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY, ['samesite' => SAMESITE_VALUE]);
	} else {
		// PHP 7.2 and lower: Without SameSite option
		setcookie(USER_COOKIE, '', $cookie_expiry, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
		setcookie(PASS_COOKIE, '', $cookie_expiry, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);

		// Force SameSite via headers for older PHP versions
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

/**
 * Session part only
 */
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

	if (SESSION_PATH != '') {
		session_save_path(SESSION_PATH);
	}

	if (session_status() === PHP_SESSION_NONE) {

		session_name(SESS_COOKIE);

		// PHP 7.3 or higher
		if (version_compare(PHP_VERSION, '7.3', '>=')) {
			// Configure session cookies with SameSite attribute
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

		// Start of the session
		session_start();

		// If PHP 7.2 or lower, set SameSite via header
		if (version_compare(PHP_VERSION, '7.3', '<')) {
			header('Set-Cookie: ' . session_name() . '=' . session_id() . //
				'; Path=' . COOKIEPATH . //
				'; Secure=' . (COOKIE_SECURE ? 'true' : 'false') . //
				'; HttpOnly; SameSite=' . SAMESITE_VALUE);

		}
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

		if (version_compare(PHP_VERSION, '7.3', '>=')) {
			// PHP 7.3 and higher: set the cookie with SameSite for deletion
			setcookie(session_name(), '', time() - 42000, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY, ['samesite' => SAMESITE_VALUE]);
		} else {
			// PHP 7.2 and lower: without SameSite option
			setcookie(session_name(), '', time() - 42000, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);

			// Add SameSite via header to delete the cookie
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

<?php
// core.cookie.php
// This file manages the cookie and session parameters for authentication.

/**
 * Initializes the cookie configuration and sets global constants.
 */
function cookie_setup() {
	global $fp_config;

	// Set the cookie prefix depending on whether HTTPS is used
	if (!defined('COOKIE_PREFIX')) {
		define('COOKIE_PREFIX', is_https() ? '__secure-' : '');
	}

	// Set the SameSite attribute to Lax if not defined
	if (!defined('SAMESITE_VALUE')) {
		define('SAMESITE_VALUE', 'Lax');
	}

	// Initialize the cookie configuration
	if (!defined('COOKIEHASH')) {
		define('COOKIEHASH', $fp_config ['general'] ['blogid']);
	}

	if (!defined('SESS_COOKIE')) {
		define('SESS_COOKIE', COOKIE_PREFIX . 'fpsess_' . COOKIEHASH);
	}

	if (!defined('COOKIEPATH')) {
		define('COOKIEPATH', preg_replace('|https?://[^/]+(/.*?)/?$|i', '$1', BLOG_BASEURL) ?: '/');
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

/**
 * Returns the cookie options used for `setcookie`.
 * Distinguishes between options for `setcookie()` and `session_set_cookie_params()`.
 *
 * @param int $expiry Expiration time of the cookie (default: 0 for session cookies).
 * @param bool $is_session Flag whether the options for `session_set_cookie_params` are used.
 * @return array Associative array with cookie options.
 */
function get_cookie_options($expiry = 0, $is_session = false) {
	// For `session_set_cookie_params` `lifetime` is used instead of `expires`
	if ($is_session) {
		return [
			'lifetime' => $expiry,
			'path' => COOKIEPATH,
			'domain' => COOKIE_DOMAIN,
			'secure' => COOKIE_SECURE,
			'httponly' => COOKIE_HTTPONLY,
			'samesite' => version_compare(PHP_VERSION, '7.3', '>=') ? SAMESITE_VALUE : null,
		];
	}

	// For `setcookie()`
	$options = [
		'expires' => $expiry,
		'path' => COOKIEPATH,
		'domain' => COOKIE_DOMAIN,
		'secure' => COOKIE_SECURE,
		'httponly' => COOKIE_HTTPONLY,
	];

	if (version_compare(PHP_VERSION, '7.3', '>=')) {
		$options ['samesite'] = SAMESITE_VALUE;
	}

	return $options;
}

/**
 * Initializes the session with the correct cookie parameters for authentication.
 */
function sess_setup() {
	if (session_status() === PHP_SESSION_NONE) {
		// Distinguish between cookie options for sessions and normal cookies
		$session_cookie_options = get_cookie_options(0, true);

		// Different treatment based on the PHP version
		if (version_compare(PHP_VERSION, '7.3', '>=')) {
			// Use `session_set_cookie_params()` with the correct format
			session_set_cookie_params($session_cookie_options);
		} else {
			// For PHP versions < 7.3
			ini_set('session.cookie_httponly', 1);
			ini_set('session.cookie_secure', COOKIE_SECURE);
			ini_set('session.cookie_path', COOKIEPATH);
			session_set_cookie_params(0, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
		}

		session_name(SESS_COOKIE);
		session_start();

		// Set the SameSite attribute manually for PHP < 7.3
		if (version_compare(PHP_VERSION, '7.3', '<')) {
			header('Set-Cookie: ' . session_name() . '=' . session_id() . //
				'; Path=' . COOKIEPATH . //
				'; Secure=' . (COOKIE_SECURE ? 'true' : 'false') . //
				'; HttpOnly; SameSite=' . SAMESITE_VALUE);
		}
	}
}

/**
 * Adds a new value to the session.
 *
 * @param string $key The key to the session value.
 * @param mixed $val The value to be saved.
 */
function sess_add($key, $val) {
	$_SESSION [$key] = $val;
}

/**
 * Removes a value from the session.
 *
 * @param string $key The key to the session value.
 * @return mixed The removed value or zero if not available.
 */
function sess_remove($key) {
	if (isset($_SESSION [$key])) {
		$oldval = $_SESSION [$key];
		unset($_SESSION [$key]);
		return $oldval;
	}
	return null;
}

/**
 * Retrieves a value from the session.
 *
 * @param string $key The key to the session value.
 * @return mixed The value or zero if not available.
 */
function sess_get($key) {
	return isset($_SESSION [$key]) ? $_SESSION [$key] : null;
}

/**
 * Ends the session and deletes the associated session cookie.
 */
function sess_close() {
	if (session_status() === PHP_SESSION_ACTIVE) {
		session_unset();
		session_destroy();

		// Delete the session cookie
		$cookie_options = get_cookie_options(time() - 3600);
		setcookie(session_name(), '', $cookie_options);
	}
}

/**
 * Deletes all cookies for logging out.
 */
function cookie_clear() {
	$cookie_expiry = time() - 31536000;
	$cookie_options = get_cookie_options($cookie_expiry);

	if (version_compare(PHP_VERSION, '7.3', '>=')) {
		setcookie(SESS_COOKIE, '', $cookie_options);
	} else {
		// Manual setting of the cookie for PHP < 7.3
		setcookie(SESS_COOKIE, '', $cookie_expiry, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
		header('Set-Cookie: ' . SESS_COOKIE . '=; Expires=' . gmdate('D, d-M-Y H:i:s T', $cookie_expiry) . //
			'; Path=' . COOKIEPATH . //
			'; Secure=' . (COOKIE_SECURE ? 'true' : 'false') . //
			'; HttpOnly; SameSite=' . SAMESITE_VALUE);
	}
}
?>

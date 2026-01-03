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
		define('COOKIE_PREFIX', is_https() ? '__Secure-' : '');
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
		define('COOKIE_DOMAIN', '');
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
		$opts = [
			'lifetime' => $expiry,
			'path' => COOKIEPATH,
			'secure' => COOKIE_SECURE,
			'httponly' => COOKIE_HTTPONLY,
			'samesite' => version_compare(PHP_VERSION, '7.3', '>=') ? SAMESITE_VALUE : null,
		];
		if (is_string(COOKIE_DOMAIN) && COOKIE_DOMAIN !== '') {
			$opts ['domain'] = COOKIE_DOMAIN;
		}
		return $opts;
	}

	// For `setcookie()`
	$options = [
		'expires' => $expiry,
		'path' => COOKIEPATH,
		'secure' => COOKIE_SECURE,
		'httponly' => COOKIE_HTTPONLY,
	];

	if (is_string(COOKIE_DOMAIN) && COOKIE_DOMAIN !== '') {
		$options ['domain'] = COOKIE_DOMAIN;
	}

	if (version_compare(PHP_VERSION, '7.3', '>=')) {
		$options ['samesite'] = SAMESITE_VALUE;
	}

	return $options;
}

/**
 * Compatibility wrapper for setting cookies with modern options on PHP >= 7.3
 * and a safe SameSite workaround on PHP < 7.3.
 *
 * @param string $name
 * @param string $value
 * @param int $expires Unix timestamp (0 for session cookie).
 * @return bool
 */
function fp_setcookie($name, $value = '', $expires = 0) {
	// PHP >= 7.3 supports the options array (including SameSite)
	if (version_compare(PHP_VERSION, '7.3', '>=')) {
		$cookie_options = get_cookie_options($expires);
		return setcookie($name, $value, $cookie_options);
	}

	// PHP < 7.3: use the classic signature and inject SameSite into the path.
	$path = COOKIEPATH;
	if (defined('SAMESITE_VALUE') && SAMESITE_VALUE !== '') {
		$path .= '; SameSite=' . SAMESITE_VALUE;
	}

	$domain = (is_string(COOKIE_DOMAIN) && COOKIE_DOMAIN !== '') ? COOKIE_DOMAIN : '';

	return setcookie($name, $value, (int)$expires, $path, $domain, COOKIE_SECURE, COOKIE_HTTPONLY);
}

/**
 * Initializes the session with the correct cookie parameters for authentication.
 * Also handles session timeout based on inactivity.
 */
function sess_setup() {
	if (session_status() === PHP_SESSION_NONE) {
		// Activate strict mode to prevent session fixation attacks
		ini_set('session.use_strict_mode', 1);

		// Set session timeout duration (e.g., 3600 seconds = 60 minutes)
		$timeout_duration = 3600;
		// Override from config if set
		if (isset($GLOBALS ['fp_config'] ['auth'] ['session_timeout'])) {
			$cfg = (int)$GLOBALS ['fp_config'] ['auth'] ['session_timeout'];
			if ($cfg > 0) {
				$timeout_duration = $cfg;
			}
		}
		ini_set('session.gc_maxlifetime', $timeout_duration);

		// Optimize Garbage Collection (adjust to session load)
		ini_set('session.gc_probability', 1);
		ini_set('session.gc_divisor', 50);

		$session_cookie_options = get_cookie_options(0, true);

		// Set session cookie parameters based on PHP version
		if (version_compare(PHP_VERSION, '7.3', '>=')) {
			session_set_cookie_params($session_cookie_options);
		} else {
			$path = COOKIEPATH;
			if (defined('SAMESITE_VALUE') && SAMESITE_VALUE !== '') {
				$path .= '; SameSite=' . SAMESITE_VALUE;
			}
			ini_set('session.cookie_httponly', 1);
			ini_set('session.cookie_secure', COOKIE_SECURE);
			ini_set('session.cookie_path', $path);
			$domain = (is_string(COOKIE_DOMAIN) && COOKIE_DOMAIN !== '') ? COOKIE_DOMAIN : '';
			session_set_cookie_params(0, $path, $domain, COOKIE_SECURE, COOKIE_HTTPONLY);
		}

		session_name(SESS_COOKIE);
		session_start();

		if (isset($_SESSION ['last_activity'])) {
			// Check if the session has expired
			if (time() - $_SESSION ['last_activity'] > $timeout_duration) {
				// Session has expired, close it
				sess_close();
				// Stop further execution
				return;
			}
		}

		// Update last activity timestamp
		$_SESSION ['last_activity'] = time();
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
		fp_setcookie(session_name(), '', time() - 3600);
	}
}

/**
 * Deletes all cookies for logging out.
 */
function cookie_clear() {
	$cookie_expiry = time() - 31536000;
	fp_setcookie(SESS_COOKIE, '', $cookie_expiry);
}
?>

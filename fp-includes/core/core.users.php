<?php
// core.users.php
// This file manages the user login and authentication via PHP sessions.
// Contains functions for user administration, password hashing and session control.

/**
 * Class for managing the user list.
 * The class extends `fs_filelister` to load user profiles from the file system.
 */
class user_lister extends fs_filelister {
	var $_varname = 'cache';
	var $_cachefile = null;
	var $_directory = USERS_DIR;

	/**
	 * Constructor of the class.
	 * Initializes the cache for the user list.
	 */
	function bdb_entrylister() {
		$this->_cachefile = CACHE_DIR . 'userlist.php';
		parent::__construct();
	}

	/**
	 * Checks whether a file is a user profile.
	 * Used to create a list of user names.
	 *
	 * @param string $directory The directory in which to search for files.
	 * @param string $file The file name.
	 * @return int Returns 0 if the file is a user profile.
	 */
	function _checkFile($directory, $file) {
		if (fnmatch('*.php', $file) && is_valid_userid(basename($file, EXT))) {
			// If the file is a PHP file, add it to the list
			array_push($this->_list, basename($file, EXT));
		}
		return 0;
	}
}

/**
 * Returns the list of users.
 *
 * @return array A list of all users who are registered in the system.
 */
function user_list() {
	$obj = new user_lister();
	// Calls the `getList()` method of the `user_lister` class
	return $obj->getList();
}

/**
 * Creates a secure password hash.
 *
 * @param string $pwd The password to be hashed.
 * @return string The hashed password string.
 */
function user_pwd($pwd) {
	// Use `password_hash` for secure password storage
	return password_hash($pwd, PASSWORD_DEFAULT);
}

/**
 * Authenticates a user and starts a session.
 *
 * @param string $userid The user ID.
 * @param string $pwd The user's password.
 * @return bool Returns `true` if the login is successful, otherwise `false`.
 */
function user_login($userid, $pwd) {
	global $loggedin;
	$loggedin = false;
	if (!sess_setup()) {
		$_SESSION ['login_error'] = 'session';
		return false;
	}
	unset($_SESSION ['login_error']);

	// Validate user ID
	if (!is_valid_userid($userid)) {
		return false;
	}

	// Rate limiting using a temporary storage like $_SESSION
	if (!isset($_SESSION ['login_attempts'])) {
		$_SESSION ['login_attempts'] = [];
	}

	$now = time();
	$_SESSION ['login_attempts'] = array_filter($_SESSION ['login_attempts'], function($timestamp) use ($now) {
		// Keep attempts from the last 5 minutes
		return ($now - $timestamp) < 300;
	});

	if (count($_SESSION ['login_attempts']) >= 5) {
		// Too many attempts, deny login
		return false;
	}

	$user = user_get($userid);
	if (!$user || !isset($user ['password'])) {
		$_SESSION ['login_attempts'] [] = $now;
		return false;
	}
	// Use the profile's canonical spelling (important on case-insensitive filesystems).
	if (isset($user ['userid']) && is_string($user ['userid']) && is_valid_userid($user ['userid'])) {
		$userid = $user ['userid'];
	}

	if (user_check_password($userid, $pwd, $user ['password'])) {
		if (!session_regenerate_id(true)) {
			$_SESSION ['login_error'] = 'session';
			return false;
		}

		$_SESSION ['loggedin'] = true;
		$_SESSION ['userid'] = $userid;
		$_SESSION ['auth_fingerprint'] = hash('sha256', $user ['password']);
		unset($_SESSION ['login_attempts'], $_SESSION ['last_login_attempt']);
		return true;
	}

	// Log failed attempt
	$_SESSION ['login_attempts'] [] = $now;
	return false;
}

/**
 * Checks whether a user is logged in.
 *
 * @return bool Returns `true` if the user is logged in, otherwise `false`.
 */
function user_loggedin() {
	if (session_status() !== PHP_SESSION_ACTIVE || session_name() !== SESS_COOKIE) {
		return false;
	}
	if (empty($_SESSION ['loggedin']) || !isset($_SESSION ['userid']) || !is_string($_SESSION ['userid'])) {
		return false;
	}
	$user = user_get($_SESSION ['userid']);
	$fingerprint = $_SESSION ['auth_fingerprint'] ?? null;
	if (!is_array($user) || !isset($user ['password']) || !is_string($fingerprint)
		|| !hash_equals(hash('sha256', $user ['password']), $fingerprint)) {
		// Also invalidate pre-fingerprint sessions and sessions of removed accounts.
		unset($_SESSION ['loggedin'], $_SESSION ['userid'], $_SESSION ['auth_fingerprint']);
		return false;
	}
	return $_SESSION ['loggedin'] === true;
}

/**
 * Logs the user out and ends the session.
 */
function user_logout() {
	// Ends the session and deletes the session cookies
	sess_close();
}

/**
 * Retrieves the user data for the given user ID.
 *
 * @param string|null $userid The user ID. If `null`, the current session is used.
 * @return array|null Returns the user data or `null` if the user does not exist.
 */
function user_get($userid = null) {
	// If no user ID is specified, use the ID from the current session
	if ($userid === null && user_loggedin()) {
		$userid = $_SESSION ['userid'];
	}

	// Validate user ID
	if ($userid && is_valid_userid($userid)) {
		$userfile = USERS_DIR . $userid . '.php';

		// Check whether the user file exists
		if (file_exists($userfile)) {
			@include($userfile);

			// Check whether the user data has been loaded correctly
			if (isset($user) && is_array($user)) {
				return $user;
			}
		}
	}
	// User not found or invalid ID
	return null;
}


/**
 * Validates a user ID to ensure it contains only valid characters.
 *
 * @param string $userid The user ID to validate.
 * @return bool True if valid, otherwise false.
 */
function is_valid_userid($userid) {
	return preg_match('/^[a-zA-Z0-9_]+$/', $userid) === 1;
}

/**
 * Adds a new user or updates an existing user.
 *
 * @param array $user An associative array with the user data.
 * @return bool Returns `true` if the user was successfully saved, otherwise `false`.
 */
function user_add($user) {
	if (!isset($user ['userid'], $user ['password']) || !is_string($user ['userid'])
		|| !is_valid_userid($user ['userid']) || !is_string($user ['password'])) {
		return false;
	}
	// Check whether the password has already been hashed
	if (!password_get_info($user ['password']) ['algo']) {
		try {
			$user ['password'] = user_pwd($user ['password']);
		} catch (Throwable $exception) {
			return false;
		}
		if (!is_string($user ['password']) || $user ['password'] === '') {
			return false;
		}
	}
	// Saves the user data in a PHP file
	return system_save(USERS_DIR . $user ['userid'] . '.php', compact('user'), [
		'preserve_existing' => true, 'invalidate_opcache' => true
	]);
}

/**
 * Update the current account without deleting it before its replacement is safe.
 * The optional commit saves the associated configuration before removing the old name.
 * @param string $userid
 * @param array $user
 * @param callable|null $commit
 * @return string|null Localized error key, or null on success.
 */
function user_update($userid, $user, $commit = null) {
	if (!is_string($userid) || !is_valid_userid($userid) || !isset($user ['userid'])
		|| !is_string($user ['userid']) || !is_valid_userid($user ['userid'])) {
		return 'account_save';
	}
	$lock = @fopen(USERS_DIR . '.accounts.lock', 'c+b');
	if ($lock === false) {
		return 'account_save';
	}
	try {
		if (!flock($lock, LOCK_EX)) {
			return 'account_save';
		}
		$original = user_get($userid);
		if (!is_array($original)) {
			return 'account_save';
		}
		$renamed = $userid !== $user ['userid'];
		if ($renamed && file_exists(USERS_DIR . $user ['userid'] . '.php')) {
			return 'account_exists';
		}
		if (!user_add($user)) {
			return 'account_save';
		}
		if ($commit !== null && !$commit()) {
			// Preserve the original login if the settings could not be committed.
			if ($renamed) {
				user_del($user ['userid']);
			} else {
				user_add($original);
			}
			return 'account_config';
		}
		if ($renamed && !user_del($userid)) {
			user_del($user ['userid']);
			return 'account_rename';
		}
		return null;
	} finally {
		flock($lock, LOCK_UN);
		fclose($lock);
	}
}

/**
 * Check a real account's password under a persistent, per-account attempt lock.
 * Changing the browser session must not reset the login limit.
 * @param string $userid
 * @param string $pwd
 * @param string $hash
 * @return bool
 */
function user_check_password($userid, $pwd, $hash) {
	$file = USERS_DIR . '.login-' . hash('sha256', $userid) . '.php';
	$lock = @fopen($file, 'c+b');
	if ($lock === false) {
		$_SESSION ['login_error'] = 'storage';
		return false;
	}
	@chmod($file, 0600);
	try {
		if (!flock($lock, LOCK_EX)) {
			$_SESSION ['login_error'] = 'storage';
			return false;
		}
		$data = stream_get_contents($lock, 8192);
		$prefix = "<?php exit; ?>\n";
		$state = is_string($data) && strpos($data, $prefix) === 0 ? json_decode(substr($data, strlen($prefix)), true) : [];
		if ($data === false || ($data !== '' && (!is_array($state) || !isset($state ['attempts']) || !is_array($state ['attempts'])))) {
			$_SESSION ['login_error'] = 'storage';
			return false;
		}
		$now = time();
		$attempts = isset($state ['attempts']) && is_array($state ['attempts']) ? $state ['attempts'] : [];
		$attempts = array_values(array_filter($attempts, function ($time) use ($now) {
			return is_int($time) && $now - $time < 300;
		}));
		if (count($attempts) >= 5) {
			$_SESSION ['login_error'] = 'attempts';
			return false;
		}
		if ($attempts && $now - max($attempts) < 30) {
			$_SESSION ['login_error'] = 'timeout';
			return false;
		}
		$valid = password_verify($pwd, $hash);
		if ($valid) {
			$attempts = [];
		} else {
			$attempts [] = $now;
		}
		$encoded = $prefix . json_encode(['attempts' => $attempts]);
		rewind($lock);
		if (!ftruncate($lock, 0) || fwrite($lock, $encoded) !== strlen($encoded) || !fflush($lock)) {
			$_SESSION ['login_error'] = 'storage';
			return false;
		}
		return $valid;
	} finally {
		flock($lock, LOCK_UN);
		fclose($lock);
	}
}

/**
 * Deletes the user file for the given user ID.
 *
 * @param string $userid The user ID whose file should be deleted.
 * @return bool Returns true if the file was successfully deleted, false otherwise.
 */
function user_del($userid) {
	// Validate user ID
	if (!is_valid_userid($userid)) {
		return false;
	}

	// Construct the file path
	$userfile = USERS_DIR . $userid . '.php';

	// Check if the file exists and delete it
	if (file_exists($userfile)) {
		$deleted = @unlink($userfile);
		if ($deleted && function_exists('opcache_invalidate')) {
			@opcache_invalidate($userfile, true);
		}
		return $deleted;
	}

	return false;
}
?>

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
		if (fnmatch('*.php', $file)) {
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

	if (password_verify($pwd, $user ['password'])) {
		sess_setup();
		session_regenerate_id(true);

		$_SESSION ['loggedin'] = true;
		$_SESSION ['userid'] = $userid;
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
	return isset($_SESSION ['loggedin']) && $_SESSION ['loggedin'] === true;
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
	// Check whether the password has already been hashed
	if (!password_get_info($user ['password']) ['algo']) {
		$user ['password'] = user_pwd($user ['password']);
	}
	// Saves the user data in a PHP file
	return system_save(USERS_DIR . $user ['userid'] . '.php', compact('user'));
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
		return unlink($userfile);
	}

	return false;
}
?>

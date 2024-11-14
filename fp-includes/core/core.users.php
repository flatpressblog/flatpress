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
			return 0;
		}
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
 * @return bool Returns `true` if the registration is successful, otherwise `false`.
 */
function user_login($userid, $pwd) {
	global $loggedin;
	$loggedin = false;

	// Retrieving user data from the file
	$user = user_get($userid);
	if (!$user || !isset($user ['password'])) {
		// User not found or password missing
		return false;
	}

	// Check whether the password entered is correct
	if (password_verify($pwd, $user ['password'])) {
		// Initialize the session and save the user data
		sess_setup();
		$_SESSION ['loggedin'] = true;
		$_SESSION ['userid'] = $userid;
		return true;
	} 
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

	if ($userid) {
		$userfile = USERS_DIR . $userid . '.php';

		// Check whether the user file exists
		if (file_exists($userfile)) {
			include($userfile);

			// Check whether the user data has been loaded correctly
			if (isset($user) && is_array($user)) {
				return $user;
			}
		}
	}
	// User not found
	return null;
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
?>

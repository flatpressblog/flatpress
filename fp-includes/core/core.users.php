<?php

class user_lister extends fs_filelister {

	var $_varname = 'cache';

	var $_cachefile = null;

	var $_directory = USERS_DIR;

	function bdb_entrylister() {
		$this->_cachefile = CACHE_DIR . 'userlist.php';
		parent::__construct();
	}

	function _checkFile($directory, $file) {
		if (fnmatch('*.php', $file)) {
			array_push($this->_list, basename($file, EXT));
			return 0;
		}
	}

}

function user_list() {
	$obj = new user_lister();
	if ($users = $obj->getList()) {
		return $entry_arr;
	} else {
		return false;
	}
}

function user_pwd($userid, $pwd) {
	return password_hash($userid . $pwd, PASSWORD_DEFAULT);
}

function user_login($userid, $pwd, $params = null) {
	global $loggedin;
	$loggedin = false;

	// Get user data
	$user = user_get($userid);
	// User not found? get outta hier
	if (!isset($user) || !isset($user ['password'])) {
		return $loggedin;
	}

	// Check the password
	if (password_verify($userid . $pwd, $user ['password'])) {
		$loggedin = true;
	}
	// If this didn't work, the passwords may have been created with FlatPress 1.1 or earlier.
	// So we check the password the old-fashioned way (with wp_hash() which uses md5):
	elseif (wp_hash($userid . $pwd) == $user ['password']) {
		$loggedin = true;

		// re-hash password with current algorithm
		$user ['password'] = $pwd;
		// save in user file
		user_add($user);
		// and update user data from re-read user file
		$user = user_get($userid);
	}

	if ($loggedin) {
		// Generate hash of the user name
		$hashedUserId = hash('sha256', $userid);
		$expire = time() + 31536000;

		// Save user name as SHA-256 in the cookie
		setcookie(USER_COOKIE, $hashedUserId, $expire, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
		setcookie(PASS_COOKIE, $user ['password'], $expire, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
	}

	return $loggedin;
}

function user_logout() {
	global $loggedin;

	if (user_loggedin()) {

		setcookie(USER_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
		setcookie(PASS_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN, COOKIE_SECURE, COOKIE_HTTPONLY);
	}

	$loggedin = false;
}

function user_loggedin() {
	global $loggedin, $fp_user;

	if ($loggedin) {
		return $fp_user;
	}

	if (empty($_COOKIE [USER_COOKIE]) || empty($_COOKIE [PASS_COOKIE])) {
		$fp_user = null;
		return $loggedin = false;
	}

	// Recalculate hash to find the original user
	$hashedUserId = $_COOKIE [USER_COOKIE];

	// Search the user data to find the user with the hash
	$userfiles = array_slice(scandir(USERS_DIR), 2);
	foreach ($userfiles as $file) {
		// Removing the .php extension
		$userid = basename($file, '.php');
		if (hash('sha256', $userid) === $hashedUserId) {
			$fp_user = user_get($userid);
			break;
		}
	}

	if (!$fp_user) {
		return false;
	}

	if ($_COOKIE [PASS_COOKIE] == $fp_user ['password']) {
		$loggedin = true;
		return $fp_user;
	}

	$fp_user = null;
	$loggedin = false;
	return false;
}

/**
 * Returns the user information as associative array
 *
 * @param string $userid
 *        	optional: The ID (shortname) of a specific user
 * @return array the user information array
 */
function user_get($userid = null) {
	if ($userid == null && ($user = user_loggedin())) {
		return $user;
	}

	// We need to include the user file.
	// At first: Get files in fp_content/users (array_slice removes first elements "." and "..")
	$userfiles = array_slice(scandir(USERS_DIR), 2);
	// If PHP file for given user exists
	if (in_array($userid . '.php', $userfiles)) {
		// include it
		include (USERS_DIR . $userid . '.php');
		return $user;
	}
}

function user_add($user) {
	$user ['password'] = user_pwd($user ['userid'], $user ['password']);

	return system_save(USERS_DIR . $user ['userid'] . '.php', compact('user'));
}
?>

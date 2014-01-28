<?php

function cookie_setup() {

global $fp_config;

// md5(BLOG_BASEURL);

if ( !defined('COOKIEHASH') )
	define('COOKIEHASH', $fp_config['general']['blogid']);

if ( !defined('USER_COOKIE') )
        define('USER_COOKIE', 'fpuser_'. COOKIEHASH);
if ( !defined('PASS_COOKIE') )
        define('PASS_COOKIE', 'fppass_'. COOKIEHASH);
if ( !defined('SESS_COOKIE') )
        define('SESS_COOKIE', 'fpsess_'. COOKIEHASH);

if ( !defined('COOKIEPATH') )
        define('COOKIEPATH', preg_replace('|https?://[^/]+|i', '', BLOG_BASEURL ) );
if ( !defined('SITECOOKIEPATH') )
        define('SITECOOKIEPATH', preg_replace('|https?://[^/]+|i', '', BLOG_BASEURL ) );
if ( !defined('COOKIE_DOMAIN') )
        define('COOKIE_DOMAIN', false);
        

}

if ( !function_exists('wp_get_cookie_login') ):
function wp_get_cookie_login() {
	if ( empty($_COOKIE[USER_COOKIE]) || empty($_COOKIE[PASS_COOKIE]) )
		return false;

	return array('login' => $_COOKIE[USER_COOKIE],	'password' => $_COOKIE[PASS_COOKIE]);
}

endif;

function cookie_set($username, $password, $already_md5 = false, $home = '', $siteurl = '', $remember = false) {
	if ( !$already_md5 )
		$password = md5( md5($password) ); // Double hash the password in the cookie.

	if ( empty($home) )
		$cookiepath = COOKIEPATH;
	else
		$cookiepath = preg_replace('|https?://[^/]+|i', '', $home . '/' );

	if ( empty($siteurl) ) {
		$sitecookiepath = SITECOOKIEPATH;
		$cookiehash = COOKIEHASH;
	} else {
		$sitecookiepath = preg_replace('|https?://[^/]+|i', '', $siteurl . '/' );
		$cookiehash = md5($siteurl);
	}

	if ( $remember )
		$expire = time() + 31536000;
	else
		$expire = 0;

	setcookie(USER_COOKIE, $username, $expire, $cookiepath, COOKIE_DOMAIN);
	setcookie(PASS_COOKIE, $password, $expire, $cookiepath, COOKIE_DOMAIN);

	if ( $cookiepath != $sitecookiepath ) {
		setcookie(USER_COOKIE, $username, $expire, $sitecookiepath, COOKIE_DOMAIN);
		setcookie(PASS_COOKIE, $password, $expire, $sitecookiepath, COOKIE_DOMAIN);
	}
}

function cookie_clear() {
	setcookie(USER_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);
	setcookie(PASS_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);
	setcookie(USER_COOKIE, ' ', time() - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN);
	setcookie(PASS_COOKIE, ' ', time() - 31536000, SITECOOKIEPATH, COOKIE_DOMAIN);
}


if ( !function_exists('wp_login') ) :
function wp_login($username, $password, $already_md5 = false) {
	global $wpdb, $error;

	$username = sanitize_user($username);

	if ( '' == $username )
		return false;

	if ( '' == $password ) {
		$error = __('<strong>ERROR</strong>: The password field is empty.');
		return false;
	}

	$login = get_userdatabylogin($username);
	//$login = $wpdb->get_row("SELECT ID, user_login, user_pass FROM $wpdb->users WHERE user_login = '$username'");

	if (!$login) {
		$error = __('<strong>ERROR</strong>: Invalid username.');
		return false;
	} else {
		// If the password is already_md5, it has been double hashed.
		// Otherwise, it is plain text.
		if ( ($already_md5 && md5($login->user_pass) == $password) || ($login->user_login == $username && $login->user_pass == md5($password)) ) {
			return true;
		} else {
			$error = __('<strong>ERROR</strong>: Incorrect password.');
			$pwd = '';
			return false;
		}
	}
}
endif;

if ( !function_exists('is_user_logged_in') ) :
function is_user_logged_in() {
	$user = wp_get_current_user();

	if ( $user->id == 0 )
		return false;

	return true;
}
endif;

if ( !function_exists('auth_redirect') ) :
function auth_redirect() {
	// Checks if a user is logged in, if not redirects them to the login page
	if ( (!empty($_COOKIE[USER_COOKIE]) &&
				!wp_login($_COOKIE[USER_COOKIE], $_COOKIE[PASS_COOKIE], true)) ||
			 (empty($_COOKIE[USER_COOKIE])) ) {
		nocache_headers();

		wp_redirect(get_option('siteurl') . '/wp-login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
		exit();
	}
}
endif;


?>
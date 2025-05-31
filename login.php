<?php
require_once 'defaults.php';
require_once INCLUDES_DIR . 'includes.php';

$tpl = 'default.tpl';

function sanitize_user($input) {
	global $fp_config;
	$charset = strtoupper($fp_config ['locale'] ['charset'] ?? 'UTF-8');
	return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, $charset);
}

function sanitize_pass($input) {
	return trim($input ?? '');
}

function login_validate() {
	global $smarty, $lang;

	// Sanitization of the inputs
	$user = sanitize_user(filter_input(INPUT_POST, 'user', FILTER_DEFAULT) ?? '');
	$pass = sanitize_pass(filter_input(INPUT_POST, 'pass', FILTER_DEFAULT) ?? '');

	$error = array();
	$lerr = &$lang ['login'] ['error'];

	// Check whether the user has already made a login attempt in the last 30 seconds
	if (isset($_SESSION ['last_login_attempt']) && (time() - $_SESSION ['last_login_attempt'] < 30)) {
		$error ['timeout'] = $lerr ['timeout'];
	} else {
		// Set the time of the last login attempt
		$_SESSION ['last_login_attempt'] = time();
	}

	if (empty($user)) {
		$error ['user'] = $lerr ['user'];
	}

	if (empty($pass)) {
		$error ['pass'] = $lerr ['pass'];
	}

	if (empty($error) && !user_login($user, $pass)) {
		$error ['match'] = $lerr ['match'];
	}

	if (!empty($error)) {
		$smarty->assign('error', $error);
		return false;
	} else {
		return true;
	}
}

function login_head() {
	// Don't index any of these forms.
	echo '
		<meta name="robots" content="NOINDEX, NOFOLLOW">
	';
}

add_action('wp_head', 'login_head');

function login_main() {
	global $lang, $smarty;

	// Ensure session is started
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	if (empty($_SESSION ['csrf_token'])) {
		// Generate CSRF token
		$_SESSION ['csrf_token'] = bin2hex(random_bytes(32));
	}

	// New login, we (re)set the session data
	utils_nocache_headers();

	// Transfer token to Smarty
	$smarty->assign('csrf_token', $_SESSION ['csrf_token']);

	// Initialize modifier functions
	$smarty->registerPlugin('modifier', 'wp_specialchars', 'wp_specialchars');
	$smarty->registerPlugin('modifier', 'function_exists', 'function_exists');
	$smarty->registerPlugin('modifier', 'is_numeric', 'is_numeric');

	if (user_loggedin()) {
		if (isset($_GET ['do']) && ($_GET ['do'] == 'logout')) {
			user_logout();

			add_filter('wp_head', function () {
				// Logout redirects to home page
				myredirect('.');
			});

			$content = (SHARED_TPLS . 'login.tpl');
		} elseif (user_loggedin()) {
			add_filter('wp_head', function () {
				// Login redirects to Admin Area
				myredirect('admin.php');
			});

			$content = (SHARED_TPLS . 'login_success.tpl');
		} else {
			utils_redirect();
		}
	} elseif (sess_remove('logout_done')) {
			//add_filter('wp_head', function () {
				//myredirect('.');
			//});

		$content = (SHARED_TPLS . 'login_success.tpl');
	} elseif (empty($_POST)) {
		$content = (SHARED_TPLS . 'login.tpl');
	} else {
		// CSRF token verification
		if (!isset($_POST ['csrf_token']) || $_POST ['csrf_token'] !== $_SESSION ['csrf_token']) {
			$content = (SHARED_TPLS . 'login.tpl');
		} elseif (login_validate()) {
			// Validate after a POST and reset CSRF token after successful verification
			unset($_SESSION ['csrf_token']);
			$_SESSION ['csrf_token'] = bin2hex(random_bytes(32));
			$smarty->assign('csrf_token', $_SESSION ['csrf_token']);
			utils_redirect('login.php');
			exit();
		} else {
			// Assign sanitized inputs here
			$smarty->assign('user', $_POST ['user'] ?? '');
			$content = (SHARED_TPLS . 'login.tpl');
		}
	}

	// Set page title and content
	// first parameter is Title, second is content.
	// Content can be both a shared tpl or raw html content; in this last case
	// you have to set the third optional parameter to true

	$smarty->assign('subject', $lang ['login'] ['head']);
	$smarty->assign('content', $content);
}

function myredirect($target) {
	login_redirect($target);
}

function login_redirect($url, $secs = 0) {
	echo '<meta http-equiv="refresh" content="' . $secs . ';url=' . $url . '">';
}

function login_title($title, $sep) {
	global $lang;
	return $title . " " . $sep . " " . $lang ['login'] ['head'];
}

add_filter('wp_title', 'login_title', 10, 2);

function login_display() {
	global $smarty;

	login_main();

	theme_init($smarty);

	$smarty->display('default.tpl');

	unset($smarty);

	do_action('shutdown');
}

system_init();
login_display();
?>

<?php
require_once 'defaults.php';
require_once INCLUDES_DIR . 'includes.php';

$tpl = 'default.tpl';

function login_validate() {
	global $smarty, $lang;

	$user = trim(htmlspecialchars(@$_POST ['user']));
	$pass = trim(htmlspecialchars(@$_POST ['pass']));

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

	// Initialize modifier functions
	$smarty->registerPlugin('modifier', 'wp_specialchars', 'wp_specialchars');
	$smarty->registerPlugin('modifier', 'function_exists', 'function_exists');
	$smarty->registerPlugin('modifier', 'is_numeric', 'is_numeric');

	if (user_loggedin()) {

		if (isset($_GET ['do']) && ($_GET ['do'] == 'logout')) {
			user_logout();

			function myredirect() {
				// logout redirects to home page
				login_redirect('.');
			}

			add_filter('wp_head', 'myredirect');

			$content = (SHARED_TPLS . 'login.tpl');
		} elseif (user_loggedin()) {

			function myredirect() {
				// login redirects to Admin Area
				login_redirect('admin.php');
			}

			add_filter('wp_head', 'myredirect');

			$content = (SHARED_TPLS . 'login_success.tpl');
		} else {

			utils_redirect();
		}
	} elseif (sess_remove('logout_done')) {

		function myredirect() {
			// login_redirect('.');
		}

		add_filter('wp_head', 'myredirect');

		$content = (SHARED_TPLS . 'login_success.tpl');
	} elseif (empty($_POST)) {
		$content = (SHARED_TPLS . 'login.tpl');
	} else {
		// validate after a POST
		if (login_validate()) {
			utils_redirect('login.php');
		} else {
			$smarty->assign($_POST);
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

function login_redirect($url, $secs = 0) {
	echo '<meta http-equiv="refresh" content="' . "$secs;url=$url" . '" />';
}

function login_title($title, $sep) {
	global $lang;
	return $title = "$title $sep {$lang['login']['head']}";
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

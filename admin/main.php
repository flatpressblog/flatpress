<?php

// Add new protocol for template
include (ADMIN_DIR . 'panels/admin.defaultpanels.php');
include (ADMIN_DIR . 'includes/panels.prototypes.php');

utils_nocache_headers();

define('MOD_ADMIN_PANEL', 1);

// Deactivate OPcache when the theme panel is called up
if (function_exists('opcache_get_status') && ini_get('opcache.enable')) {
	if (isset($_GET ['p']) && $_GET ['p'] === 'themes') {
		ini_set('opcache.enable', 0);
	}
}

/**
 * Handle failed nonce verification.
 *
 * @param string $action The action that was attempted (optional).
 */
function wp_nonce_ays($action = '') {
	die('We apologize, an error occurred.' . ($action ? ' Action: ' . htmlspecialchars($action) : ''));
}

/*
 * function admin_is_user_loggedin() {
 * return ($u=user_loggedin()) && utils_checkreferer() ;
 * }
 */
function main() {

	// General setup
	global $panel, $action, $lang, $smarty, $fp_admin, $fp_admin_action;

	// Register all Smarty modifier functions used by the admin templates
	admin_register_smartyplugins();

	$panels = admin_getpanels();

	$panel = (isset($_GET ['p'])) ? $_GET ['p'] : $panels [0];
	define('ADMIN_PANEL', $panel);
	$smarty->assign('panel', $panel);

	// redirect to admin main page if requested panel does not exist
	if (!admin_panelexists($panel)) {
		// will lead to login if not logged in already
		utils_redirect("admin.php");
		die();
	}

	$panelprefix = 'admin.' . $panel;
	$panelpath = ADMIN_DIR . 'panels/' . $panel . '/' . $panelprefix . '.php';

	$fp_admin = null;

	if (file_exists($panelpath)) {

		include ($panelpath);
		$panelclass = 'admin_' . $panel;

		if (!class_exists($panelclass)) {
			trigger_error("No class defined for requested panel", E_USER_ERROR);
		}

		$fp_admin = new $panelclass($smarty);
	}

	/* check if user is loggedin */

	if (!user_loggedin()) {
		utils_redirect("login.php");
		die();
	}

	$action = isset($_GET ['action']) ? $_GET ['action'] : 'default';
	if (!$fp_admin) {
		return;
	}

	$fp_admin_action = $fp_admin->get_action($action);

	define('ADMIN_PANEL_ACTION', $action);
	$smarty->assign('action', $action);
	$panel_url = BLOG_BASEURL . 'admin.php?p=' . $panel;
	$action_url = $panel_url . '&action=' . $action;
	$smarty->assign('panel_url', $panel_url);
	$smarty->assign('action_url', $action_url);

	if (!empty($_POST)) {
		check_admin_referer('admin_' . $panel . '_' . $action);
	}

	$smarty->assign('success', sess_remove('success_' . $panel));
	$retval = $fp_admin_action->exec();

	if ($retval > 0) { // if has REDIRECT option
	                   // clear postdata by a redirect

		sess_add('success_' . $panel, $smarty->getTemplateVars('success'));
		$smarty->getTemplateVars('success');

		$to_action = $retval > 1 ? ('&action=' . $action) : '';
		$with_mod = isset($_GET ['mod']) ? ('&mod=' . $_GET ['mod']) : '';
		$with_arguments = '';

		if ($retval == PANEL_REDIRECT_CURRENT) {
			foreach ($fp_admin_action->args as $mandatory_argument) {
				$with_arguments .= '&' . $mandatory_argument . '=' . $_REQUEST [$mandatory_argument];
			}
		}

		$url = 'admin.php?p=' . $panel . $to_action . $with_mod . $with_arguments;
		utils_redirect($url);
	}
}

// smarty tag
function admin_filter_action($string, $action) {
	if (strpos($string, '?') === false) {
		return $string .= '?action=' . $action;
	} else {
		return $string .= wp_specialchars('&action=' . $action);
	}
}

// smarty tag
function admin_filter_command($string, $cmd, $val) {
	global $panel, $action;

	$arg = $cmd ? '&' . $cmd : $cmd;

	return wp_nonce_url($string . $arg . '=' . $val, 'admin_' . $panel . '_' . $action . '_' . $cmd . '_' . $val);
}

function admin_panelstrings($panelprefix) {
	global $lang, $smarty;

	lang_load('admin');
	lang_load($panelprefix);

	$smarty->assign('subject', $lang ['admin'] ['head']);
	$smarty->assign('menubar', admin_getpanels());

	add_filter('wp_title', 'admin_panel_title', 10, 2);
}

function admin_panel_title($title, $sep) {
	global $lang, $panel;

	$t = @$lang ['admin'] ['panels'] [$panel];
	$title = $title . ' ' . $sep . ' ' . $t;
	return $title;
}

// Called by {controlpanel} in admin.tpl (Smarty 5: BCPluginWrapper)
function showcontrolpanel(array $params, \Smarty\Template $template): string {
	$smarty = $template->getSmarty();
	// Only render the admin body, not admin.tpl (would recursively execute {controlpanel} again)
	return $smarty->fetch('file:' . ABS_PATH . ADMIN_DIR . 'main.tpl');
}

// html header
function admin_title($title, $sep) {
	global $lang;
	return $title = $title . ' ' . $sep . ' ' . $lang ['admin'] ['head'];
}

add_filter('wp_title', 'admin_title', 10, 2);

// setup admin_header
function admin_header_default_action() {
	global $panel, $action;
	do_action('admin_' . $panel . '_' . $action . '_head');
}
add_filter('admin_head', 'admin_header_default_action');

/**
 * Registers all Smarty modifier functions used by the admin templates
 */
function admin_register_smartyplugins() {
	global $smarty;
	$smarty->registerPlugin('modifier', 'action_link', 'admin_filter_action');
	$smarty->registerPlugin('modifier', 'cmd_link', 'admin_filter_command');
	// Plugin functions
	if (function_exists('fpprotect_harden_prettyurls_plugin')) {
		$smarty->registerPlugin('modifier', 'fpprotect_harden_prettyurls_plugin', 'fpprotect_harden_prettyurls_plugin');
	}
	$functionsToRegister = array(
		// FlatPress functions
		'entry_idtotime',
		'plugin_getinfo',
		'plugin_geturl',
		'wp_specialchars',
		'wp_nonce_url',
		'wptexturize',
		// PHP functions
		'addslashes',
		'array_intersect',
		'array_key_exists',
		'count',
		'date',
		'defined',
		'function_exists',
		'htmlspecialchars',
		'in_array',
		'is_numeric',
		'sprintf'
	);
	foreach ($functionsToRegister as $functionToRegister) {
		$smarty->registerPlugin('modifier', $functionToRegister, $functionToRegister);
	}
}

$fp_config = config_load();
system_init();
main();
admin_panelstrings('admin.' . ADMIN_PANEL);
theme_init($smarty);
$smarty->registerPlugin('function', 'controlpanel', 'showcontrolpanel');

$v = $lang ['admin'] [$panel] [$action];

$smarty->assign('panelstrings', $v);
$smarty->assign('plang', $v);

if (isset($_GET ['mod'])) {

	switch ($_GET ['mod']) {
		case 'inline':
			$smarty->display(ABS_PATH . ADMIN_DIR . 'admin-inline.tpl');
			break;
		case 'ajax':
			echo $smarty->getTemplateVars('success');
	}
} else {
	$smarty->display('admin.tpl');
}

?>

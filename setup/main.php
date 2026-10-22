<?php
if ($_SERVER ['SERVER_NAME'] === 'localhost') {
	@ini_set('display_errors', 'on');
	error_reporting(E_ALL);
} else {
	@ini_set('display_errors', 'off');
	error_reporting(0);
}

// Permissions are checked and applied only to selected data and Core paths.

// Sets the local language based on the browser
$browserLang = isset($browserLang) && is_string($browserLang) && $browserLang !== '' ? $browserLang : (defined('LANG_DEFAULT') ? (string)LANG_DEFAULT : 'en-us');
$language = isset($_POST ['language']) && is_string($_POST ['language']) && $_POST ['language'] !== '' ? $_POST ['language'] : $browserLang;

$lf = 'lang.' . $language . '.php';
if (!preg_match('|^lang\.[a-z]{2}-[a-z]{2}\.php$|', $lf)) {
	die ('Error with lang file');
}

include __DIR__ . '/lang/' . $lf;
$setupMainVars = get_defined_vars();
$lang = isset($setupMainVars ['lang']) && is_array($setupMainVars ['lang']) ? $setupMainVars ['lang'] : array();
include __DIR__ . '/lib/main.lib.php';

$step = null;

$id = getstep($step);

if (!isset($lang [$step]) || !is_array($lang [$step])) {
	$lang [$step] = array();
}
$l = &$lang [$step];

include __DIR__ . '/tpls/header.tpl.php';
include __DIR__ . '/tpls/' . $step . '.tpl.php';
include __DIR__ . '/tpls/footer.tpl.php';

?>

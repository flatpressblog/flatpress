<?php
/**
 * This function prints some localized strings in javascript.
 * It's called by the init hook.
 */
function plugin_commentcenter_jslang() {
	if(empty($_GET['jslang'])) {
		return;
	}
	if($_GET['jslang']!='commentcenter') {
		return;
	}

	global $lang;

	header('Content-type: text/javascript');

	echo "commentcenter_lang={\n";

	foreach($lang['admin']['entry']['commentcenter']['msgs'] as $key=>$value) {
		$key=str_replace('-', '_', $key);
		$value=str_replace("\n", "\\n", $value);
		$value=str_replace("\r", "\\r", $value);
		$value=str_replace("\t", "\\t", $value);
		$value=str_replace('"', '\\"', $value);
		echo "\t'msg{$key}' : \"{$value}\",\n";
	}

	echo '}';

	die;
}
add_action('init', 'plugin_commentcenter_jslang');
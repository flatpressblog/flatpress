<?php
/*
 * Plugin Name: AdminArea
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: AdminArea plugin. Part of the standard distribution.
 * Version: 1.1.0
 */
function plugin_adminarea_widget() {
	$lang = lang_load('plugin:adminarea');
	$baseurl = BLOG_BASEURL;

	if (user_loggedin()) {
		$user = user_get();
		if ($user && isset($user ['userid'])) {
			$userid = htmlspecialchars($user ['userid']);
			$string = '<p>' . $lang ['plugin'] ['adminarea'] ['welcome'] . ' <strong>' . $userid . '</strong>!</p>
						<ul>
							<li><a href="' . $baseurl . 'admin.php">' . $lang ['plugin'] ['adminarea']['admin_panel'] . '</a></li>
							<li><a href="' . $baseurl . 'admin.php?p=entry&amp;action=write">' . $lang ['plugin'] ['adminarea'] ['add_entry'] . '</a></li>
							<li><a href="' . $baseurl . 'login.php?do=logout">' . $lang ['plugin'] ['adminarea'] ['logout'] . '</a></li>
						</ul>';
		} else {
			$string = '<ul><li><a href="' . $baseurl . 'login.php">' . $lang ['plugin'] ['adminarea'] ['login'] . '</a></li></ul>';
		}
	} else {
		$string = '<ul><li><a href="' . $baseurl . 'login.php">' . $lang ['plugin'] ['adminarea'] ['login'] . '</a></li></ul>';
	}

	$entry ['subject'] = $lang ['plugin'] ['adminarea'] ['subject'];
	$entry ['content'] = $string;

	return $entry;
}

register_widget('adminarea', 'AdminArea', 'plugin_adminarea_widget');
?>

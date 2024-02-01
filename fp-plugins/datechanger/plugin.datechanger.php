<?php
/*
 * Plugin Name: DateChanger
 * Plugin URI: https://www.flatpress.org
 * Type: Block
 * Author: FlatPress
 * Description: Allows to change the date and time for <a href="./admin.php?p=entry&action=write" title="Write Entry">new entries</a> via a drop-down menu. <a href="./fp-plugins/datechanger/doc_datechanger.txt" title="Instructions" target="_blank">[Instructions]</a>
 * Version: 1.0.4
 * Author URI: https://www.flatpress.org
 */
if (!(basename($_SERVER ['PHP_SELF']) == 'admin.php' && // must be admin area
	@$_GET ['p'] == 'entry' && // must be right panel
	@$_GET ['action'] == 'write' && // must be right action
	!(@$_POST ['timestamp'] || @$_REQUEST ['entry']))) // must be a new entry
return;

function plugin_datechanger_toolbar() {
	$time = time();

	$h = date('H', $time);
	$m = date('i', $time);
	$s = date('s', $time);

	$Y = date('Y', $time);
	$M = date('m', $time);
	$D = date('d', $time);

	$lang = lang_load('plugin:datechanger'); // Multilingual support by Plugin
	global $lang; // Multilingual support by FlatPress

	echo '<div id="admin-date"><fieldset id="plugin_datechanger"><legend>' . $lang ['admin'] ['plugin'] ['datechanger'] ['title'] . '</legend><p>' . $lang ['admin'] ['plugin'] ['datechanger'] ['time'] . ':&nbsp;';

	// set time
	echo '<label><select name="date[]">';
	for($i = 0; $i < 24; $i++) {
		$v = sprintf('%02d', $i);
		echo '<option value="' . $v . '"' . (($v == $h) ? ' selected="selected"' : '') . '>' . $v . '</option>';
	}

	echo '</select></label>:';

	echo '<label><select name="date[]">';
	for($i = 0; $i < 60; $i++) {
		$v = sprintf('%02d', $i);
		echo '<option value="' . $v . '"' . (($v == $m) ? ' selected="selected"' : '') . '>' . $v . '</option>';
	}

	echo '</select></label>:';

	echo '<label><select name="date[]">';
	for($i = 0; $i < 60; $i++) {
		$v = sprintf('%02d', $i);
		echo '<option value="' . $v . '"' . (($v == $s) ? ' selected="selected"' : '') . '>' . $v . '</option>';
	}

	echo '</select>&nbsp;&nbsp;&nbsp;</label> ';

	// set date
	echo '' . $lang ['admin'] ['plugin'] ['datechanger'] ['date'] . ':&nbsp;<select name="date[]">';
	for($i = 1; $i <= 31; $i++) {
		$v = sprintf('%02d', $i);
		echo '<option value="' . $v . '"' . (($v == $D) ? ' selected="selected"' : '') . '>' . $v . '</option>';
	}
	echo '</select>&nbsp;';

	$mths = $lang ['date'] ['month'];

	echo '<select name="date[]">';
	for($i = 0; $i < 12; $i++) {
		$v = sprintf('%02d', $i + 1);
		echo '<option value="' . $v . '"' . (($v == $M) ? ' selected="selected"' : '') . '>' . $mths [$i] . '</option>';
	}
	echo '</select>&nbsp;';

	echo '<select name="date[]">';
	foreach (range(2000, intval($Y) + 10) as $v) {
		echo '<option value="' . $v . '"' . (($v == $Y) ? ' selected="selected"' : '') . '>' . $v . '</option>';
	}
	echo '</select>';

	echo '</p></fieldset></div><!-- end of #admin-date -->';
}

// Meh, {toolbar} no longer works with fp-1.3 dev -> #17
//add_action('editor_toolbar', 'plugin_datechanger_toolbar', 0);
add_filter('simple_datechanger_form', 'plugin_datechanger_toolbar', 0);


function plugin_datechanger_check() {
	if ((isset($_GET ['p']) && $_GET ['p'] != 'entry') || (isset($_GET ['action']) && $_GET ['action'] != 'write'))
		return;

	if (empty($_POST))
		return;

	if (!empty($_POST ['date']))
		$date = $_POST ['date'];
	else
		return;

	foreach ($date as $v) {
		if (!is_numeric($v))
			return;
		else
			$date [] = intval($v);
	}

	list ($hour, $minute, $second, $day, $month, $year) = $date;

	$time = mktime($hour, $minute, $second, $month, $day, $year);

	$_POST ['timestamp'] = $time;
}

add_action('init', 'plugin_datechanger_check');
?>

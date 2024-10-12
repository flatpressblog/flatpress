<?php
/*
 * Plugin Name: DateChanger
 * Plugin URI: https://www.flatpress.org
 * Type: Block
 * Author: FlatPress
 * Description: Allows to change the date and time for <a href="./admin.php?p=entry&action=write" title="Write Entry">new entries</a> via a drop-down menu. Part of the standard distribution. <a href="./fp-plugins/datechanger/doc_datechanger.txt" title="Instructions" target="_blank">[Instructions]</a>
 * Version: 1.0.6
 * Author URI: https://www.flatpress.org
 */
function is_valid_admin_area() {
	// Check whether we are in the admin area
	if (basename($_SERVER ['PHP_SELF']) !== 'admin.php') {
		return false;
	}

	// Check whether the required GET parameters are set and have the correct values
	if (!isset($_GET ['p']) || $_GET ['p'] !== 'entry') {
		return false;
	}

	// Check whether the correct 'write' action is executed
	if (!isset($_GET ['action']) || $_GET ['action'] !== 'write') {
		return false;
	}

	// Check whether it is a new entry (no timestamp and no existing 'entry')
	if (isset($_POST ['timestamp']) || isset($_REQUEST ['entry'])) {
		return false;
	}

	// All conditions fulfilled
	return true;
}

if (!is_valid_admin_area()) {
	return;
}


function plugin_datechanger_toolbar() {
	global $fp_config, $lang;

	// Retrieve language from the configuration
	$language = $fp_config ['locale'] ['lang'];

	// Current time (UTC + time offset)
	$time = date_time();

	$h = date('H', $time);
	$m = date('i', $time);
	$s = date('s', $time);

	$Y = date('Y', $time);
	$M = date('m', $time);
	$D = date('d', $time);

	// Load language files
	$lang = lang_load('plugin:datechanger');

	// Show time selection
	echo '<div id="admin-date"><fieldset id="plugin_datechanger"><legend>' . $lang ['admin'] ['plugin'] ['datechanger'] ['title'] . '</legend><p>' . $lang ['admin'] ['plugin'] ['datechanger'] ['time'] . ':&nbsp;';

	// Time selection fields (hours, minutes, seconds)
	echo '<label><select name="date[]">';
	for ($i = 0; $i < 24; $i++) {
		$v = sprintf('%02d', $i);
		echo '<option value="' . $v . '"' . (($v == $h) ? ' selected="selected"' : '') . '>' . $v . '</option>';
	}
	echo '</select></label>:';

	echo '<label><select name="date[]">';
	for ($i = 0; $i < 60; $i++) {
		$v = sprintf('%02d', $i);
		echo '<option value="' . $v . '"' . (($v == $m) ? ' selected="selected"' : '') . '>' . $v . '</option>';
	}
	echo '</select></label>:';

	echo '<label><select name="date[]">';
	for ($i = 0; $i < 60; $i++) {
		$v = sprintf('%02d', $i);
		echo '<option value="' . $v . '"' . (($v == $s) ? ' selected="selected"' : '') . '>' . $v . '</option>';
	}
	echo '</select>&nbsp;&nbsp;&nbsp;</label>';

	// Date selection (different depending on language)
	$daySelect = '<select name="date[]">';
	for ($i = 1; $i <= 31; $i++) {
		$v = sprintf('%02d', $i);
		$daySelect .= '<option value="' . $v . '"' . (($v == $D) ? ' selected="selected"' : '') . '>' . $v . '</option>';
	}
	$daySelect .= '</select>&nbsp;';

	$monthSelect = '<select name="date[]">';
	// Load month names from the language file
	$mths = $lang ['date'] ['month'];
	for ($i = 0; $i < 12; $i++) {
		$v = sprintf('%02d', $i + 1);
		$monthSelect .= '<option value="' . $v . '"' . (($v == $M) ? ' selected="selected"' : '') . '>' . $mths [$i] . '</option>';
	}
	$monthSelect .= '</select>&nbsp;';

	$yearSelect = '<select name="date[]">';
	foreach (range(2006, intval($Y) + 10) as $v) {
		$yearSelect .= '<option value="' . $v . '"' . (($v == $Y) ? ' selected="selected"' : '') . '>' . $v . '</option>';
	}
	$yearSelect .= '</select>';

	// Adjust the order of the fields depending on the language
	$formattedDateSelect = '';
	switch ($language) {
		case 'en-us':
			// MM/DD/YYYY
			$formattedDateSelect = $monthSelect . $daySelect . $yearSelect;
			break;
		case 'cs-cz':
		case 'ru-ru':
		case 'ja-jp':
			// YYYY/MM/DD
			$formattedDateSelect = $yearSelect . $monthSelect . $daySelect;
			break;
		default:
			// Default: DD/MM/YYYY (e.g. for de-de)
			$formattedDateSelect = $daySelect . $monthSelect . $yearSelect;
			break;
	}

	// Output of the date
	echo $lang ['admin'] ['plugin'] ['datechanger'] ['date'] . ':&nbsp;' . $formattedDateSelect;

	echo '</p></fieldset></div><!-- end of #admin-date -->';
}

add_filter('simple_datechanger_form', 'plugin_datechanger_toolbar', 0);


function plugin_datechanger_check() {
	global $fp_config;

	// Retrieve language from the configuration
	$language = $fp_config ['locale'] ['lang'];

	if (!is_valid_admin_area()) {
		return;
	}

	if (empty($_POST)) {
		return;
	}

	if (!empty($_POST ['date'])) {
		$date = $_POST ['date'];
	} else {
		return;
	}

	// Extract date and time values
	foreach ($date as $v) {
		if (!is_numeric($v)) {
			return;
		}
	}

	// Set the order of the variables depending on the language
	switch ($language) {
		case 'en-us':
			// MM/DD/YYYY
			list($month, $day, $year, $hour, $minute, $second) = array_slice($date, 0, 6);
			break;
		case 'cs-cz':
		case 'ru-ru':
		case 'ja-jp':
			// YYYY/MM/DD
			list($year, $month, $day, $hour, $minute, $second) = array_slice($date, 0, 6);
			break;
		default:
			// Default: DD/MM/YYYY (e.g. for de-de)
			list($day, $month, $year, $hour, $minute, $second) = array_slice($date, 0, 6);
			break;
	}

	// Validation of the date
	if (!checkdate($month, $day, $year)) {
		// Invalid date, return without change
		return;
	}

	// Create timestamp
	$time = mktime($hour, $minute, $second, $month, $day, $year);

	// Insert timestamp in the POST array
	$_POST ['timestamp'] = $time;
}

add_action('init', 'plugin_datechanger_check');
?>

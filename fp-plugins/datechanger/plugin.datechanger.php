<?php
/**
 * Plugin Name: DateChanger
 * Plugin URI: https://www.flatpress.org
 * Type: Block
 * Author: FlatPress
 * Description: Allows to change the date and time for <a href="./admin.php?p=entry&action=write" title="Write Entry">new entries</a> via a drop-down menu. Part of the standard distribution. <a href="./fp-plugins/datechanger/doc_datechanger.txt" title="Instructions" target="_blank">[Instructions]</a>
 * Version: 1.0.6
 * Author URI: https://www.flatpress.org
 */

/**
 * Checks if the current request is a valid admin entry write action.
 *
 * @return bool True if in admin.php and action is 'write' for 'entry', without timestamp or entry set.
 */
function is_valid_admin_request(): bool {
	if (!isset($_SERVER ['SCRIPT_NAME'], $_GET ['p'], $_GET ['action'])) {
		return false;
	}

	// Check whether we are in the admin area
	$script = basename($_SERVER ['SCRIPT_NAME']);
	if ($script !== 'admin.php') {
		return false;
	}

	$page = strtolower((string) ($_GET ['p'] ?? ''));
	$action = strtolower((string) ($_GET ['action'] ?? ''));

	// Check whether this is the correct section
	if ($page !== 'entry' || $action !== 'write') {
		return false;
	}

	// Inhalt prüfen – Anfrage darf keine Daten enthalten
	$timestamp = $_POST ['timestamp'] ?? null;
	$entry = $_REQUEST ['entry'] ?? null;

	return empty($timestamp) && empty($entry);
}

if (!is_valid_admin_request()) {
	return;
}

function plugin_datechanger_toolbar() {
	global $fp_config, $lang;

	// Use UTC + timeoffset
	$time = date_time();

	$h = date('H', $time);
	$m = date('i', $time);
	$s = date('s', $time);

	$Y = date('Y', $time);
	$M = date('m', $time);
	$D = date('d', $time);

	// Multilingual support of the plugin
	$lang = lang_load('plugin:datechanger');

	// Load month names
	$mths = $lang ['date'] ['month'];

	// Get the language setting of FlatPress
	$language = $fp_config ['locale'] ['lang'];

	// Date format by language
	$DD_MM_YYYY = ['de-de', 'fr-fr', 'es-es', 'da-dk', 'el-gr', 'it-it', 'nl-nl', 'pt-br', 'sl-si', 'tr-tr'];
	$MM_DD_YYYY = ['en-us'];
	$YYYY_MM_DD = ['cs-cz', 'ja-jp', 'ru-ru'];

	echo '<div id="admin-date"><fieldset id="plugin_datechanger">
		<legend>' . $lang ['admin'] ['plugin'] ['datechanger'] ['title'] . '</legend>' . //
		$lang ['admin'] ['plugin'] ['datechanger'] ['time'] . ':&nbsp;';

	// Hours selection
	echo '<label><select name="date_hour">';
	for ($i = 0; $i < 24; $i++) {
		$v = sprintf('%02d', $i);
		echo '<option value="' . $v . '"' . (($v == $h) ? ' selected' : '') . '>' . $v . '</option>';
	}
	echo '</select></label>:';

	// Minute selection
	echo '<label><select name="date_minute">';
	for ($i = 0; $i < 60; $i++) {
		$v = sprintf('%02d', $i);
		echo '<option value="' . $v . '"' . (($v == $m) ? ' selected' : '') . '>' . $v . '</option>';
	}
	echo '</select></label>:';

	// Seconds Selection
	echo '<label><select name="date_second">';
	for ($i = 0; $i < 60; $i++) {
		$v = sprintf('%02d', $i);
		echo '<option value="' . $v . '"' . (($v == $s) ? ' selected' : '') . '>' . $v . '</option>';
	}
	echo '</select></label>';

	echo '&nbsp;&nbsp;&nbsp;' . $lang ['admin'] ['plugin'] ['datechanger'] ['date'] . ':&nbsp;';
	// Set the order of the date selection fields depending on the language
	if (in_array($language, $DD_MM_YYYY, true)) {
		// DD-MM-YYYY (e.g. German, French, Spanish)
		echo '<label><select name="date_day">';
		for ($i = 1; $i <= 31; $i++) {
			$v = sprintf('%02d', $i);
			echo '<option value="' . $v . '"' . (($v == date('d', $time)) ? ' selected' : '') . '>' . $v . '</option>';
		}
		echo '</select></label>&nbsp;';

		echo '<label><select name="date_month">';
		for ($i = 0; $i < 12; $i++) {
			$v = sprintf('%02d', $i + 1);
			echo '<option value="' . $v . '"' . (($v == date('m', $time)) ? ' selected' : '') . '>' . $mths [$i] . '</option>';
		}
		echo '</select></label>&nbsp;';

		echo '<label><select name="date_year">';
		foreach (range(2006, intval(date('Y', $time)) + 10) as $v) {
			echo '<option value="' . $v . '"' . (($v == date('Y', $time)) ? ' selected' : '') . '>' . $v . '</option>';
		}
		echo '</select></label>';
	} elseif (in_array($language, $MM_DD_YYYY, true)) {
		// MM-DD-YYYY (e.g English)
		echo '<label><select name="date_month">';
		for ($i = 0; $i < 12; $i++) {
			$v = sprintf('%02d', $i + 1);
			echo '<option value="' . $v . '"' . (($v == date('m', $time)) ? ' selected' : '') . '>' . $mths [$i] . '</option>';
		}
		echo '</select></label>&nbsp;';

		echo '<label><select name="date_day">';
		for ($i = 1; $i <= 31; $i++) {
			$v = sprintf('%02d', $i);
			echo '<option value="' . $v . '"' . (($v == date('d', $time)) ? ' selected' : '') . '>' . $v . '</option>';
		}
		echo '</select></label>&nbsp;';

		echo '<label><select name="date_year">';
		foreach (range(2006, intval(date('Y', $time)) + 10) as $v) {
			echo '<option value="' . $v . '"' . (($v == date('Y', $time)) ? ' selected' : '') . '>' . $v . '</option>';
		}
		echo '</select></label>';
	} else {
		// YYYY-MM-DD (e.g. Czech, Japanese, Russian)
		echo '<label><select name="date_year">';
		foreach (range(2006, intval(date('Y', $time)) + 10) as $v) {
			echo '<option value="' . $v . '"' . (($v == date('Y', $time)) ? ' selected' : '') . '>' . $v . '</option>';
		}
		echo '</select></label>&nbsp;';

		echo '<label><select name="date_month">';
		for ($i = 0; $i < 12; $i++) {
			$v = sprintf('%02d', $i + 1);
			echo '<option value="' . $v . '"' . (($v == date('m', $time)) ? ' selected' : '') . '>' . $mths [$i] . '</option>';
		}
		echo '</select></label>&nbsp;';

		echo '<label><select name="date_day">';
		for ($i = 1; $i <= 31; $i++) {
			$v = sprintf('%02d', $i);
			echo '<option value="' . $v . '"' . (($v == date('d', $time)) ? ' selected' : '') . '>' . $v . '</option>';
		}
		echo '</select></label>';
	}

	echo '</p></fieldset></div><!-- end of #admin-date -->';
}

add_filter('simple_datechanger_form', 'plugin_datechanger_toolbar', 0);

function plugin_datechanger_check() {

	if (!is_valid_admin_request()) {
		return;
	}

	if (empty($_POST)) {
		return;
	}

	$year = isset($_POST ['date_year']) ? intval($_POST ['date_year']) : date('Y');
	$month = isset($_POST ['date_month']) ? intval($_POST ['date_month']) : date('m');
	$day = isset($_POST ['date_day']) ? intval($_POST ['date_day']) : date('d');
	$hour = isset($_POST ['date_hour']) ? intval($_POST ['date_hour']) : date('H');
	$minute = isset($_POST ['date_minute']) ? intval($_POST ['date_minute']) : date('i');
	$second = isset($_POST ['date_second']) ? intval($_POST ['date_second']) : date('s');

	if (!checkdate($month, $day, $year)) {
		return;
	}

	// Generate and save timestamp
	$time = mktime($hour, $minute, $second, $month, $day, $year);
	$_POST ['timestamp'] = (int) $time;
}

add_action('init', 'plugin_datechanger_check');
?>

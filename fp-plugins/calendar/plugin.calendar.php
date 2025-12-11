<?php
/*
 * Plugin Name: Calendar
 * Version: 1.2.1
 * Type: Block
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Adds a calendar widget. Part of the standard distribution.
 */

// Based on PHP calendar (version 2.3), written by Keith Devens

function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = null, $first_day = 0, $pn = array()) {

	global $fp_config;

	// Read the language settings and set the locale
	$characterset = $fp_config ['general'] ['charset'];
	$lang = $fp_config ['locale'] ['lang'];

	// First day of the month
	$first_of_month = gmmktime(0, 0, 0, $month, 1, $year);

	// First day of the Week
	if (strpos($lang, 'en-us') !== false || strpos($lang, 'ja-jp') !== false) {
		$first_day = 0; // Sunday
	} else {
		$first_day = 1; // Monday
	}

	// Generate all the day names according to the current locale
	$day_names = array();
	for ($n = 0, $t = (3 + $first_day) * 86400; $n < 7; $n++, $t += 86400) {
		$day_names [$n] = ucfirst(date_strformat('%A', $t));
	}

	// Information about the month
	@list($month, $year, $month_name, $weekday) = explode(',', date_strformat('%m,%Y,%B,%w', $first_of_month));
	$weekday = ((int) $weekday + 7 - $first_day) % 7;
	$title = htmlentities(ucfirst($month_name)) . '&nbsp;' . $year;

	// Previous and next links, if applicable
	$prev_link = isset($pn [0]) ? $pn [0] : '';
	$next_link = isset($pn [1]) ? $pn [1] : '';

	$p = $prev_link ? '<span class="calendar-prev"><a href="' . htmlspecialchars($prev_link) . '#widget_calendar">&laquo;</a>&nbsp;</span>' : '&laquo;&nbsp;';
	$n = $next_link ? '<span class="calendar-next">&nbsp;<a href="' . htmlspecialchars($next_link) . '#widget_calendar">&raquo;</a></span>' : '&nbsp;&raquo;';

	$calendar = '<table class="calendar">' . "\n" . '<caption class="calendar-month">' . $p . ($month_href ? '<a href="' . htmlspecialchars($month_href) . '">' . $title . '</a>' : $title) . $n . "</caption>\n<tr>";

	// Output weekdays
	if ($day_name_length) {
		foreach ($day_names as $d) {
			$calendar .= '<th abbr="' . htmlentities($d) . '">' . htmlentities($day_name_length < 4 ? mb_substr($d, 0, $day_name_length, $characterset) : $d) . '</th>';
		}
		$calendar .= "</tr>\n<tr>";
	}

	// Initial 'empty' days
	if ($weekday > 0) {
		$calendar .= '<td colspan="' . $weekday . '">&nbsp;</td>';
	}

	// Output days of the month
	for ($day = 1, $days_in_month = gmdate('t', $first_of_month); $day <= $days_in_month; $day++, $weekday++) {
		if ($weekday == 7) {
			$weekday = 0; // Start a new week
			$calendar .= "</tr>\n<tr>";
		}
		if (isset($days [$day]) && is_array($days [$day])) {
			@list($link, $classes, $content) = $days [$day];
			if (is_null($content)) {
				$content = $day;
			}
			$calendar .= '<td' . ($classes ? ' class="' . htmlspecialchars($classes) . '">' : '>') . ($link ? '<a class="calendar-day" href="' . htmlspecialchars($link) . '">' . $content . '</a>' : $content) . '</td>';
		} else {
			$calendar .= '<td>' . $day . '</td>';
		}
	}

	// Remaining "empty" days
	if ($weekday != 7) {
		$calendar .= '<td colspan="' . (7 - $weekday) . '">&nbsp;</td>';
	}

	return $calendar . "</tr>\n</table>\n";
}

// Calendar APCu/File Cache Helpers

/**
 * Versioned namespace suffix for calendar cache (":vN") or "".
 * Uses APCu key "fp:calendar:v". Bumped by hooks on content changes.
 */
function plugin_calendar_cache_ns() {
	static $ns = null;
	if ($ns !== null) {
		return $ns;
	}
	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
	if (!$apcu_on) {
		return $ns = '';
	}
	$v = apcu_get('fp:calendar:v');
	if (!$v) {
		@apcu_set('fp:calendar:v', 1); $v = 1;
	}
	return $ns = ':v' . (int)$v;
}

/**
 * Bumps calendar cache version and purges file fallback.
 */
function plugin_calendar_cache_bump() {
	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
	if ($apcu_on) {
		$ok = false;
		apcu_incr('fp:calendar:v', 1, $ok);
		if (!$ok) {
			@apcu_set('fp:calendar:v', 1);
		}
	}
	$dir = defined('CACHE_DIR') ? CACHE_DIR : ((defined('FP_CONTENT') ? FP_CONTENT : 'fp-content/') . 'cache/');
	if (!is_dir($dir)) {
		return;
	}
	foreach (glob($dir . 'calendar-*.html') as $f) {
		@unlink($f);
	}
}

// Hook-based invalidation
if (function_exists('add_action')) {
	// Core hooks
	add_action('publish_post', 'plugin_calendar_cache_bump', 10, 1);
	add_action('delete_post', 'plugin_calendar_cache_bump', 10, 1);
	// Edit hooks used by plugins or core
	add_action('edit_post', 'plugin_calendar_cache_bump', 10, 1);
	add_action('update_post', 'plugin_calendar_cache_bump', 10, 1);
	add_action('save_post', 'plugin_calendar_cache_bump', 10, 1);
}

function plugin_calendar_cache_key($y4, $m2, $lang, $first_day) {
	$norm = array(
		'y' => (int)$y4,
		'm' => (int)$m2,
		'lang' => (string)$lang,
		'fd' => (int)$first_day
	);
	$rev = plugin_calendar_cache_ns();
	return 'calendar:' . sha1(json_encode($norm, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)) . $rev;
}

/**
 * Replace the BLOG_BASEURL placeholder in cached HTML with the current BLOG_BASEURL.
 *
 * @param string $html
 * @return string
 */
function plugin_calendar_cache_expand_baseurl($html) {
	if (!is_string($html) || $html === '') {
		return $html;
	}
	if (defined('BLOG_BASEURL')) {
		return str_replace('%BLOG_BASEURL%', BLOG_BASEURL, $html);
	}
	return $html;
}

function plugin_calendar_cache_get($key, $ttl = 3600) {
	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
	if ($apcu_on) {
		$hit = false;
		$val = apcu_get($key, $hit);
		if ($hit && is_string($val)) {
			return plugin_calendar_cache_expand_baseurl($val);
		}
	}
	// File fallback
	$dir = defined('CACHE_DIR') ? CACHE_DIR : ((defined('FP_CONTENT') ? FP_CONTENT : 'fp-content/') . 'cache/');
	$file = $dir . 'calendar-' . sha1($key) . '.html';
	if (@file_exists($file)) {
		$mt = @filemtime($file);
		if ($mt && (time() - $mt) <= (int)$ttl) {
			$val = io_load_file($file);
			if (is_string($val)) {
				return plugin_calendar_cache_expand_baseurl($val);
			}
		}
	}
	return null;
}

function plugin_calendar_cache_set($key, $html, $ttl = 3600) {
	if (!is_string($html) || $html === '') {
		return;
	}
	$apcu_on = function_exists('is_apcu_on') ? is_apcu_on() : false;
	if ($apcu_on) {
		@apcu_set($key, $html, max(60, (int)$ttl));
	}
	$dir = defined('CACHE_DIR') ? CACHE_DIR : ((defined('FP_CONTENT') ? FP_CONTENT : 'fp-content/') . 'cache/');
	if (!@is_dir($dir)) {
		@mkdir($dir, defined('DIR_PERMISSIONS') ? DIR_PERMISSIONS : 0777, true);
	}
	$file = $dir . 'calendar-' . sha1($key) . '.html';
	@file_put_contents($file, $html);
}
// End Helpers

function plugin_calendar_widget() {
	global $fpdb, $fp_config, $fp_params;

	// Determine the current year and month
	$y = isset($fp_params ['y']) ? $fp_params ['y'] : date('Y');
	$m = isset($fp_params ['m']) ? $fp_params ['m'] : date('m');

	// Shorten $y to two digits if four digits
	if (strlen($y) === 4) {
		$y = substr($y, 2);
	}

	// Cache: build key and try APCu/file cache
	$first_day = 0;
	$lang_code = isset($fp_config ['general'] ['language']) ? $fp_config ['general'] ['language'] : 'en-US';
	$y4 = calendar_normalize_year($y);
	$cache_key = plugin_calendar_cache_key($y4, (int)$m, $lang_code, $first_day);
	$cached = plugin_calendar_cache_get($cache_key, 86400);
	if (is_string($cached)) {
		$lang = lang_load('plugin:calendar');
		$widget = array();
		$widget ['subject'] = $lang ['plugin'] ['calendar'] ['subject'];
		$widget ['content'] = $cached;
		return $widget;
	}

	// Collect entries using the index for the current month.
	$days = array();

	$m2 = str_pad((string)$m, 2, '0', STR_PAD_LEFT);

	$q = new FPDB_Query(array('fullparse' => false, 'y' => $y, 'm' => $m, 'count' => -1), null);

	while ($q->hasMore()) {
		@list($id, $entry) = $q->getEntry();
		if (empty($id)) {
			continue;
		}

		$date = date_from_id($id);
		if (empty($date) || empty($date ['d'])) {
			continue;
		}

		$d = (int)$date ['d'];
		if ($d < 1 || $d > 31) {
			continue;
		}

		$days [$d] = array(get_day_link($y, $m2, str_pad((string)$d, 2, '0', STR_PAD_LEFT)), 'linked-day', null);
	}

	// Retrieve links for the previous and next month with entries
	$prev_link = find_prev_month_with_entries($y, $m);
	$next_link = find_next_month_with_entries($y, $m);

	// Load plugin strings
	$lang = lang_load('plugin:calendar');

	// Compile widget content
	$widget = array();
	$widget ['subject'] = $lang ['plugin'] ['calendar'] ['subject'];
	$html = '<ul id="widget_calendar"><li>' . generate_calendar($y, $m, $days, 3, null, 0, array($prev_link, $next_link)) . '</li></ul>';
	plugin_calendar_cache_set($cache_key, $html, 86400);
	$widget ['content'] = $html;

	return $widget;
}

register_widget('calendar', 'Calendar', 'plugin_calendar_widget');

/**
 * Normalize a possibly two-digit year to four digits.
 * FlatPress stores years as two digits (00–99 = 2000–2099) in queries and URLs.
 * This keeps queries and links unchanged while allowing safe boundary checks.
 * @param int|string $y
 * @return int
 */
function calendar_normalize_year($y) {
	$y = intval($y);
	if ($y < 100) {
		return 2000 + $y;
	}
	return $y;
}

// Function to search for the previous month with entries
function find_prev_month_with_entries($year, $month) {
	global $fpdb;

	for ($i = 1; $i <= 12; $i++) {
		$month--;
		if ($month < 1) {
			$month = 12;
			$year--;
		}

		// Request for the month
		$q = new FPDB_Query(array(
			'fullparse' => false,
			'y' => $year,
			'm' => $month,
			'count' => 1
		), null);

		if ($q->hasMore()) {
			return get_month_link($year, str_pad($month, 2, '0', STR_PAD_LEFT));
		}

		// Cancel if the year goes back too far (default: 2006, year of birth of FlatPress)
		if (calendar_normalize_year($year) < 2006) {
			break;
		}
	}

	return null;
}

// Function to search for the next month with entries
function find_next_month_with_entries($year, $month) {
	global $fpdb;

	for ($i = 1; $i <= 12; $i++) {
		$month++;
		if ($month > 12) {
			$month = 1;
			$year++;
		}

		// Request for the month
		$q = new FPDB_Query(array(
			'fullparse' => false,
			'y' => $year,
			'm' => $month,
			'count' => 1
		), null);

		if ($q->hasMore()) {
			return get_month_link($year, str_pad($month, 2, '0', STR_PAD_LEFT));
		}

		// Cancel if the year goes too far into the future (default: current year plus 2 years)
		if (calendar_normalize_year($year) > date('Y') + 2) {
			break;
		}
	}

	return null;
}
?>

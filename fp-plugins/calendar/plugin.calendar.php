<?php
/*
 * Plugin Name: Calendar
 * Version: 1.2
 * Type: Block
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Adds a calendar widget. Part of the standard distribution.
 */

// PHP Calendar (version 2.3), written by Keith Devens
// http://keithdevens.com/software/php_calendar
// see example at http://keithdevens.com/weblog
// License: http://keithdevens.com/software/license

function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = null, $first_day = 0, $pn = array()) {

	global $fp_config;
	$characterset = $fp_config ['general'] ['charset'];

	// First day of the month
	$first_of_month = gmmktime(0, 0, 0, $month, 1, $year);

	// Generate all the day names according to the current locale
	$day_names = array();
	for ($n = 0, $t = (3 + $first_day) * 86400; $n < 7; $n++, $t += 86400) {
		$day_names [$n] = ucfirst(date_strformat('%A', $t));
	}

	// Information about the month
	@list($month, $year, $month_name, $weekday) = explode(',', date_strformat('%m,%Y,%B,%w', $first_of_month));
	$weekday = ($weekday + 7 - $first_day) % 7;
	$title = htmlentities(ucfirst($month_name)) . '&nbsp;' . $year;

	// Previous and next links, if applicable
	$prev_link = isset($pn [0]) ? $pn [0] : '';
	$next_link = isset($pn [1]) ? $pn [1] : '';

	$p = $prev_link ? '<span class="calendar-prev"><a href="' . htmlspecialchars($prev_link) . '">&laquo;</a>&nbsp;</span>' : '&laquo;&nbsp;';
	$n = $next_link ? '<span class="calendar-next">&nbsp;<a href="' . htmlspecialchars($next_link) . '">&raquo;</a></span>' : '&nbsp;&raquo;';

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
		if (isset($days [$day]) and is_array($days [$day])) {
			@list($link, $classes, $content) = $days [$day];
			if (is_null($content)) {
				$content = $day;
			}
			$calendar .= '<td' . ($classes ? ' class="' . htmlspecialchars($classes) . '">' : '>') . ($link ? '<a class="calendar-day" href="' . htmlspecialchars($link) . '">' . $content . '</a>' : $content) . '</td>';
		} else {
			$calendar .= "<td>$day</td>";
		}
	}

	// Remaining "empty" days
	if ($weekday != 7) {
		$calendar .= '<td colspan="' . (7 - $weekday) . '">&nbsp;</td>';
	}

	return $calendar . "</tr>\n</table>\n";
}

function plugin_calendar_widget() {
	global $fp_params;

	// Determine current year and month
	$y = isset($fp_params ['y']) ? $fp_params ['y'] : date('Y');
	$m = isset($fp_params ['m']) ? $fp_params ['m'] : date('m');

	global $fpdb;

	// Creating the request for the current month
	$q = new FPDB_Query(array(
		'fullparse' => true,
		'y' => $y,
		'm' => $m,
		'count' => -1
	), null);

	// Collect entries
	$days = array();
	while ($q->hasMore()) {
		@list($id, $entry) = $q->peekEntry();
		$date = date_from_id($id);
		$d = (int) $date ['d'];

		$days [$d] = array(
			get_day_link($y, $m, str_pad($d, 2, '0', STR_PAD_LEFT)),
			'linked-day'
		);

		// Increase pointer
		$q->pointer++;
	}

	// Calculate the links for the previous and next month
	$prev_month = ($m - 1) < 1 ? 12 : $m - 1;
	$prev_year = ($prev_month == 12) ? $y - 1 : $y;

	$next_month = ($m + 1) > 12 ? 1 : $m + 1;
	$next_year = ($next_month == 1) ? $y + 1 : $y;

	// Check whether there are entries in the previous month
	$prev_q = new FPDB_Query(array(
		'fullparse' => true,
		'y' => $prev_year,
		'm' => $prev_month,
		'count' => 1 // Only one entry required
	), null);
	$prev_link = $prev_q->hasMore() ? get_month_link($prev_year, $prev_month) : null;

	// Check whether entries are available in the next month
	$next_q = new FPDB_Query(array(
		'fullparse' => true,
		'y' => $next_year,
		'm' => $next_month,
		'count' => 1 // Only one entry required
	), null);
	$next_link = $next_q->hasMore() ? get_month_link($next_year, $next_month) : null;

	// load plugin strings
	$lang = lang_load('plugin:calendar');

	// Compile widget content
	$widget = array();
	$widget ['subject'] = $lang ['plugin'] ['calendar'] ['subject'];
	$widget ['content'] = '<ul id="widget_calendar"><li>' . generate_calendar($y, $m, $days, 3, null, 0, array($prev_link, $next_link)) . '</li></ul>';

	return $widget;
}

register_widget('calendar', 'Calendar', 'plugin_calendar_widget');

?>

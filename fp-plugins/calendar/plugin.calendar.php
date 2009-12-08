<?php
/*
Plugin Name: Calendar
Version: 1.1
Plugin URI: http://flatpress.sf.net
Type: Block
Description: Adds a Calendar block level element 
Author: NoWhereMan
Author URI: http://flatpress.sf.net
*/


# PHP Calendar (version 2.3), written by Keith Devens
# http://keithdevens.com/software/php_calendar
#  see example at http://keithdevens.com/weblog
# License: http://keithdevens.com/software/license

function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array()){
	$first_of_month = gmmktime(0,0,0,$month,1,$year);
	#remember that mktime will automatically correct if invalid dates are entered
	# for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
	# this provides a built in "rounding" feature to generate_calendar()

	$day_names = array(); #generate all the day names according to the current locale
	for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) #January 4, 1970 was a Sunday
		$day_names[$n] = ucfirst(date_strformat('%A',$t)); #%A means full textual day name

	list($month, $year, $month_name, $weekday) = explode(',',date_strformat('%m,%Y,%B,%w',$first_of_month));
	$weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day
	$title   = htmlentities(ucfirst($month_name)).'&nbsp;'.$year;  #note that some locales don't capitalize month and day names

	#Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03
	@list($p, $pl) = each($pn); @list($n, $nl) = each($pn); #previous and next links, if applicable
	if($p) $p = '<span class="calendar-prev">'.($pl ? '<a href="'.($pl).'">'.$p.'</a>' : $p).'</span>&nbsp;';
	if($n) $n = '&nbsp;<span class="calendar-next">'.($nl ? '<a href="'.($nl).'">'.$n.'</a>' : $n).'</span>';
	$calendar = '<table class="calendar">'."\n".
		'<caption class="calendar-month">'.$p.($month_href ? '<a href="'.($month_href).'">'.$title.'</a>' : $title).$n."</caption>\n<tr>";

	if($day_name_length){ #if the day names should be shown ($day_name_length > 0)
		#if day_name_length is >3, the full name of the day will be printed
		foreach($day_names as $d)
			$calendar .= '<th abbr="'.htmlentities($d).'">'.htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d).'</th>';
		$calendar .= "</tr>\n<tr>";
	}

	if($weekday > 0) $calendar .= '<td colspan="'.$weekday.'">&nbsp;</td>'; #initial 'empty' days
	for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
		if($weekday == 7){
			$weekday   = 0; #start a new week
			$calendar .= "</tr>\n<tr>";
		}
		if(isset($days[$day]) and is_array($days[$day])){
			@list($link, $classes, $content) = $days[$day];
			if(is_null($content))  $content  = $day;
			$calendar .= '<td'.($classes ? ' class="'.htmlspecialchars($classes).'">' : '>').
				($link ? '<a class="calendar-day" href="'.($link).'">'.$content.'</a>' : $content).'</td>';
		}
		else $calendar .= "<td>$day</td>";
	}
	if($weekday != 7) $calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>'; #remaining "empty" days

	return $calendar."</tr>\n</table>\n";
}

function plugin_calendar_widget() {

	global $fp_params;
	
	$y = isset($fp_params['y'])? $fp_params['y'] : date('y'); 
	$m = isset($fp_params['m'])? $fp_params['m'] : date('m'); 
		
	global $fpdb;
	
	$q =& new FPDB_Query(array('fullparse'=>false,'y'=>$y,'m'=>$m, 'count' => -1), null);
	
	
	$days = array();
	
	while ($q->hasmore($queryId)) {
		
		list($id, $entry) = $q->getEntry($queryId);
		$date = date_from_id($id);
		$d = (int)$date['d'];
		
		$days[$d] = array(get_day_link($y, $m, str_pad($d, 2, '0', STR_PAD_LEFT)), 'linked-day');
		
		
		$count++;
	}
	
	// load plugin strings
	// they're located under plugin.PLUGINNAME/lang/LANGID/
	$lang = lang_load('plugin:calendar');
	
	$widget = array();
	$widget['subject'] = $lang['plugin']['calendar']['subject'];
	$widget['content'] = '<ul id="widget_calendar"><li>'.	generate_calendar($y,$m, $days).'</li></ul>';
	
	return $widget;
}

register_widget('calendar', 'Calendar', 'plugin_calendar_widget');


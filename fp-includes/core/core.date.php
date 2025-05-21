<?php
	
	date_default_timezone_set('UTC');

	function date_strformat($format, $timestamp=0) {
		global $lang;

		// D l day
		
		if ( strpos($format, '%a') !== false ) {
			$i = strftime('%w', $timestamp);
			$format = str_replace('%a', $lang['date']['weekday_abbr'][$i], $format);
		}
		
		if ( strpos($format, '%A') !== false  ) {
			$i = strftime('%w', $timestamp);
			$format = str_replace('%A', $lang['date']['weekday'][$i], $format);
		}
		
		
		// F M month
		
		if ( strpos($format, '%b') !== false  ) {
			$i = intval(strftime('%m', $timestamp))-1;
			$format = str_replace('%b', $lang['date']['month_abbr'][$i], $format);
		}
		
		
		if ( strpos($format, '%B') !== false  ) {
			$i = intval(strftime('%m', $timestamp))-1;
			$format = str_replace('%B', $lang['date']['month'][$i], $format);
		}
		
			if (DIRECTORY_SEPARATOR == '\\') {
			$_win_from = array('%D',       '%h', '%n', '%r',          '%R',    '%t', '%T');
			$_win_to   = array('%m/%d/%y', '%b', "\n", '%I:%M:%S %p', '%H:%M', "\t", '%H:%M:%S');
				if (strpos($format, '%e') !== false) {
					$_win_from[] = '%e';
					$_win_to[]   = sprintf('%\' 2d', date('j', $timestamp));
				}
				if (strpos($format, '%l') !== false) {
					$_win_from[] = '%l';
					$_win_to[]   = sprintf('%\' 2d', date('h', $timestamp));
				}
				$format = str_replace($_win_from, $_win_to, $format);
			}
		
		return strftime($format, $timestamp);
	
		
	}

	function date_time($offset=null) {
		global $fp_config;
		if (is_null($offset)) {
			$offset = $fp_config['locale']['timeoffset'];
		}
		$timestamp = time();
		return $timestamp + $offset * 3600;
	}


	/*
	function date_now($offset=0) {	
		$timestamp = gmtime();
		$time_stamp = intval($timestamp) + intval($offset) * 60 * 60;
		return date($format, $time_stamp);
	
	}
	*/
	
	// I really DON'T LIKE THIS, looks like an hack, yech...
	// Takes filename and extension as a parameter, strips
	// alphabetic chars (ascii) from filename and "parses" the date;
	// In fact it's just a substr, counting on the fact filename should be
	// "prefix%y%m%d-%H%M%S.ext"
	function date_from_id($id) {
			
			
			$strdate = substr($id, -13);
			
			if (!preg_match('/[0-9]{6}-[0-9]{6}/', $strdate))
				return array();
			
			$arr[ 'y' ] = substr($strdate, 0, 2);
			$arr[ 'm' ] = substr($strdate, 2, 2);
			$arr[ 'd' ] = substr($strdate, 4, 2);
			
			$arr[ 'H' ] = substr($strdate, 7, 2);
			$arr[ 'M' ] = substr($strdate, 9, 2);
			$arr[ 'S' ] = substr($strdate, 11, 2);
			
			$arr['ymd'] = $arr['y'] . $arr['m'] . $arr['d'];
			$arr['HMS'] = $arr['H'] . $arr['M'] . $arr['S'];
			
			$arr['time'] = mktime($arr['H'], $arr['M'], $arr['S'],
					$arr['m'], $arr['d'], $arr['y']);
			
		return $arr;
		
	}
	
?>

<?php

	function date_strformat($timestamp=null, $offset=0, $format='l dS F Y h:i:s A') {
		if ( strpos( $timestamp, ',' ) !== false ) {
			// This is an hack for compatibility with the time
			// format from versions < 0.3.3. In 0.3.3 spb switched
			// to the unix timestamp for storing times.
			//
			// Before that it was in this format:
			//   date( 'F j, Y, g:i a', $time_stamp );
			//   'May 10, 2004, 3:57 pm'
			$time_stamp = str_replace( ',', '', $time_stamp );
			$time_stamp = strtotime( $time_stamp );
		}
		
		$timestamp = ($timestamp != null) ? $timestamp : time();
		$time_stamp = intval($timestamp) + intval($offset) * 60 * 60;
		return date($format, $time_stamp);
		
		
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
					$arr['y'], $arr['m'], $arr['d']);
			
		return $arr;
		
	}
	
?>

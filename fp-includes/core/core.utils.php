<?php

	// utils.php
	// library of misc utilities
	
	
	// function subkey sort
	// function prototype :
	// array utils_sksort(array $arr, string $key, int $flag=SORT_ASC)
	// sorts an array of associative arrays by given key $key
	// $flag can be SORT_ASC or SORT_DESC for ascending
	// or descending order (defaults to ascending);
	// other flags are the same of array_multisort() php function ;)
	function utils_sksort($arr, $key, $flag=SORT_ASC) {
		if ($arr) {
			foreach($arr as $val)
				$sorter[] = $val[$key];
			array_multisort($sorter, $flag, $arr);
			return $arr;
		} else return false;
	}
	
	// function prototype:
	// bool utils_pattern_match(string $string, string $pattern)
	
	// returns true if $pattern matches $string, else returns false (what else?)
	// $pattern is a string containing standard shell-style jokers: * and ?
	// regex are powerful but somtimes, too complicated :)
	// usage: 	* matches a variable number of chars
	//			e.g. : doc*.txt   matches  document.txt, docs.txt, dock.txt, etc.
	//					and also doc.txt (note: I didn't want it to do that, but I didn't change it)
	//		? matches one char, whichever is
	//			e.g. : document?.txt matches document1.txt, document2.txt, document3.txt, etc.
	//					likewise "*", it should match document.txt, too (but I hadn't tried it :) )
	
	// code from http://www.php.net/function.glob.php.htm#54519
	// by x_terminat_or_3 at yahoo dot country:fr
	// thank you, man ;)
	// as usual, slightly modified to fit my tastes :)
	
if (!function_exists('fnmatch')) {
	function fnmatch($pattern, $string) {
		
		if ($pattern == null)
			return false;
		
		//basically prepare a regular expression
		$out=null;
		$chunks=explode(';',$pattern);
		foreach($chunks as $pattern)
		{
			$escape=array('$','^','.','{','}','(',')','[',']','|');
			while(strpos($pattern,'**')!==false)
				$pattern=str_replace('**','*',$pattern);
				
			foreach($escape as $probe)
				$pattern=str_replace($probe,"\\$probe",$pattern);
			$pattern=str_replace('?*','*', str_replace('*?','*', 
										str_replace('*',".*",
												str_replace('?','.{1,1}',$pattern))));
			$out[]=$pattern;
		}
		/* // NoWhereMan note: why splitting this in two? :)
				if(count($out)==1) return(eregi("^$out[0]$",$string)); else*/
		foreach($out as $tester) {
			if (preg_match("/^$tester$/i",$string))
				return true;
		}
				
		return false;
	}
}
	
	
	// function prototype:
	// array utils_kexplode(string $string, string $delim='|')
	
	// explodes a string into an array by the given delimiter;
	// delimiter defaults to pipe ('|').
	// the string must be formatted as in:
	//	key1|value1|key2|value2 , etc.
	// the array will look like
	// $arr['key1'] = 'value1'; $arr['key2'] = 'value2'; etc.
	
	function utils_kexplode($string, $delim='|', $keyupper=true) {
		$arr = array();
		$string = trim($string);
		
		$k = strtolower(strtok($string, $delim));	
		$arr[$k] = strtok($delim);
		while (( $k = strtok($delim) ) !== false) {
			if ($keyupper && !preg_match('/[A-Z-_]/',$k)){
				/* 
				trigger_error("Failed parsing <pre>$string</pre>
				keys were supposed to be UPPERCASE but <strong>\"$k\"</strong> was found; file may be corrupted
				or in an expected format. <br /> 
				Some SimplePHPBlog files may raise this error: set DUMB_MODE_ENABLED 
				to true in your defaults.php to force parsing of the offending keys.", 
				E_USER_WARNING);
				*/
				continue;
			}
			
			$arr[strtolower($k)] = strtok($delim);
		}
		
		return $arr;
	}
	

	/*
	function utils_newkexplode($string, $delim='|') {
	
		$arr = array();
		
		$lastoffset = $offset = 0;
		$len = strlen($string);
		
		while ($lastoffset<$len) {
			$offset = strpos($string, $delim, $lastoffset);
			$key = substr($string, $lastoffset, $offset-$lastoffset);
			//echo 'parsing key: ', $key, $offset, chr(10);
			
			$lastoffset = $offset + 1;
			
			if (!ctype_upper($key)) 
				trigger_error("Failed parsing \"$string\"
				keys were supposed to be UPPERCASE", E_USER_ERROR);
			
			$offset = strpos($string, $delim, $lastoffset);

			if ($offset===false)
				$offset = $len;

			$val = substr($string, $lastoffset, $offset-$lastoffset);

			//echo 'parsing value ', $val, $offset, chr(10);
			
			$lastoffset = $offset + 1;
			
			$arr[$key] = $val;
		
		}	
		return $arr;
		
	}*/

	
	// function prototype:
	// array utils_kimplode(string $string, string $delim='|')
	
	// explodes a string into an array by the given delimiter; 
	// delimiter defaults to pipe ('|').
	// the string must be formatted as in:
	//	key1|value1|key2|value2 , etc.
	// the array will look like
	// $arr['key1'] = 'value1'; $arr['key2'] = 'value2'; etc.
	
	function utils_kimplode($arr, $delim='|') {
		
		$string = "";
		foreach ($arr as $k => $val) {
			if ($val)
				$string .= strtoupper($k) . $delim . ($val) . $delim;
		}
		return $string;
	}
	
	/**
	 * @todo send mail to admin
	 */


	function &utils_explode_recursive($array, &$string, $rdelim, $ldelim='', $outerldelim='', $outerrdelim='') {

		$string .= $outerldelim;

		while (list(,$val) = each($array)) {
			
			$string .= $rdelim;
			if (is_array($val)) {
				$string .= utils_explode_recursive($val, $string, $rdelim, $ldelim, $outerldelim, $outerrdelim);
			} else {
				$string .= $val;	
			}

			$string .= $ldelim;

		}

		$string .= $outerrdelim;

	}





	function utils_validateinput($str) {
		
		if (preg_match('/[^a-z0-9\-_]/i',$str)){
			trigger_error("String \"$str\" is not a valid input", E_USER_ERROR);
			//return false;
		} else
			return true;
	}
	
	function utils_cut_string($str,$maxc) {
		$car = strlen($str);
		if($car > $maxc) {
			return substr($str, 0, $maxc)."...";
		} else {
			return $str;
		}
	}


	function utils_status_header($status) {

		switch ($status) {
			case 301:
				header("HTTP/1.1 301 Moved Permanently");
				break;
			case 403:
				header("HTTP/1.1 403 Forbidden");
				break;
			case 404:
				header("HTTP/1.1 404 Not Found");
				break;

		}

	}

	// code from php.net ;)
	// defaults to index.php ;)
	function utils_redirect($location="", $absolute_path=false, $red_type=null) {

		if (!$absolute_path)
			$location = BLOG_BASEURL . $location;
		
		if ( function_exists('wp_redirect') ) {
			wp_redirect($location);
		} else {
			header("Location: $location");
		}
		
		exit();

	}
	
	
	/*
	 * utils_geturlstring()
	 * 
	 * @return string complete url string as displayed in the browser
	 * 
	 */
	 
	function utils_geturlstring() {
		$str = BLOG_BASEURL . $_SERVER['PHP_SELF'];
		if ($_SERVER['QUERY_STRING'])
			$str .='?'.$_SERVER['QUERY_STRING'];
		return $str; 
	}
	
	// custom array_merge:
	// pads the second array to match the length of the first
	// this can be improved, anyway for now I'd just
	// do a quick & dirty solution :)
	function utils_array_merge($arr1, $arr2) {
		
		$len=count($arr1[0]);
		
		foreach($arr2 as $k=>$v)
			$arr2[$k]=array_pad((Array) $v, $len, null);
		
		return array_merge($arr1, $arr2);
	}

		
	/*
	* Simple function to replicate PHP 5 behaviour
	*/
	function utils_microtime()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	function utils_countdashes($string, &$rest) {
		trim($string);
		$i = 0;
		while ($string{$i} == '-') {
			$i++;
		}
		if ($i)
			$rest = substr($string, $i);
		else $rest = $string;

		return $i;


	}
	
	function utils_mail($from, $subject, $message, $headers = '') {
		global $fp_config;
		if( $headers == '' ) {
			$headers = "MIME-Version: 1.0\n" .
				"From: " . $from . "\n" . 
				"Content-Type: text/plain; charset=\"" . $fp_config['general']['charset'] . "\"\n";
		}
	
		return mail($fp_config['general']['email'], $subject, $message, $headers);
	}
	
/*
 * props: http://crisp.tweakblogs.net/blog/2031
 */
	function utils_validateIPv4($IP) { 
	    return $IP == long2ip(ip2long($IP)); 
	} 
	
	function utils_validateIPv6($IP) { 
	    // fast exit for localhost 
	    if (strlen($IP) < 3) 
	        return $IP == '::'; 
	
	    // Check if part is in IPv4 format 
	    if (strpos($IP, '.')) 
	    { 
	        $lastcolon = strrpos($IP, ':'); 
	        if (!($lastcolon && validateIPv4(substr($IP, $lastcolon + 1)))) 
	            return false; 
	
	        // replace IPv4 part with dummy 
	        $IP = substr($IP, 0, $lastcolon) . ':0:0'; 
	    } 
	
	    // check uncompressed 
	    if (strpos($IP, '::') === false) 
	    { 
	        return preg_match('/^(?:[a-f0-9]{1,4}:){7}[a-f0-9]{1,4}$/i', $IP); 
	    } 
	
	    // check colon-count for compressed format 
	    if (substr_count($IP, ':') < 8) 
	    { 
	        return preg_match('/^(?::|(?:[a-f0-9]{1,4}:)+):(?:(?:[a-f0-9]{1,4}:)*[a-f0-9]{1,4})?$/i', $IP); 
	    } 
	
	    return false; 
	} 

	// get client IP
	function utils_ipget() {
		
		$ip = '';

		if ( !empty ( $_SERVER[ 'HTTP_CLIENT_IP' ] ) ) {
			$ip = $_SERVER[ 'HTTP_CLIENT_IP' ];
		}
		elseif ( !empty ( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) ) {
			$ip = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
		}
		elseif ( !empty ( $_SERVER[ 'REMOTE_ADDR' ] ) ) {
			$ip = $_SERVER[ 'REMOTE_ADDR' ];
		}
		elseif ( getenv( "HTTP_CLIENT_IP" ) ) {
			$ip = getenv( "HTTP_CLIENT_IP" );
		}
		elseif ( getenv( "HTTP_X_FORWARDED_FOR" ) ) {
			$ip = getenv( "HTTP_X_FORWARDED_FOR" );
		}
		elseif ( getenv( "REMOTE_ADDR") ) {
			$ip = getenv( "REMOTE_ADDR" );
		}

		if (utils_validateIPv4($ip) || utils_validateIPv6($ip)) {
			return $ip;
		} else {
			return '';
		}


	}

	function utils_nocache_headers() {
	        @ header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
	        @ header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	        @ header('Cache-Control: no-cache, must-revalidate, max-age=0');
	        @ header('Pragma: no-cache');
	}

// from http://nadeausoftware.com/articles/2007/06/php_tip_how_get_web_page_using_curl
// code under OSI BSD
/**
 * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
 * array containing the HTTP server response header fields and content.
 */
function utils_geturl($url) {
	/*
	if (ini_get('allow_url_fopen')) {
		return array('content' => io_load_file($url));
	}
	*/
	if (!function_exists('curl_init')) {
		trigger_error('curl extension is not installed');
		return array();
	}

    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => false,    // don't follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}


	
	function utils_checksmarty() {
	
		if (!file_exists(SMARTY_DIR . 'Smarty.class.php')) {
		$err = <<<ERR
		Fatal error: Smarty is not installed; please download it from <a href="http://smarty.php.net">http://smarty.php.net</a>; you will 
	probably need <a href="http://www.phpinsider.com/php/code/SmartyValidate/">SmartyValidate</a> as well; unpack them to <b>fp-includes/core/smarty</b>: please do not overwrite files in fp-includes/core/smarty/plugins/
ERR;
		trigger_error($err, E_USER_ERROR);
		}

	}
	
	
	function fplog($str) {
		if(!defined('DEBUG_MODE'))
			echo "\n[DEBUG] $str \n";
	}
	
?>

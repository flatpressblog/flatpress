<?php

function wp_nonce_url($actionurl, $action = -1) {
        return wp_specialchars( $actionurl . '&_wpnonce=' . wp_create_nonce($action) );
}

function wp_nonce_field($action = -1) {
        echo '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce($action) . '" />';
        wp_referer_field();
}

function wp_referer_field() {
        $ref = wp_specialchars($_SERVER['REQUEST_URI']);
        echo '<input type="hidden" name="_wp_http_referer" value="'. $ref . '" />';
        if ( wp_get_original_referer() ) {
                $original_ref = wp_specialchars(stripslashes(wp_get_original_referer()));
                echo '<input type="hidden" name="_wp_original_http_referer" value="'. $original_ref . '" />';
        }
}

function wp_original_referer_field() {
        echo '<input type="hidden" name="_wp_original_http_referer" value="' . wp_specialchars(stripslashes($_SERVER['REQUEST_URI'])) . '" />';
}

function wp_get_referer() {
        foreach ( array(@$_REQUEST['_wp_http_referer'],@$_SERVER['HTTP_REFERER']) as $ref )
                if ( !empty($ref) )
                        return $ref;
        return false;
}

function wp_get_original_referer() {
        if ( !empty($_REQUEST['_wp_original_http_referer']) )
                return $_REQUEST['_wp_original_http_referer'];
        return false;
}





function add_magic_quotes($array) {
	foreach ($array as $k => $v) {
		if (is_array($v)) {
			$array[$k] = add_magic_quotes($v);
		} else {
			$array[$k] = addslashes($v);
		}
	}
	return $array;
}

function wp_remote_fopen( $uri ) {
	if ( ini_get('allow_url_fopen') ) {
		$fp = fopen( $uri, 'r' );
		if ( !$fp )
			return false;
		$linea = '';
		while( $remote_read = fread($fp, 4096) )
			$linea .= $remote_read;
		fclose($fp);
		return $linea;		
	} else if ( function_exists('curl_init') ) {
		$handle = curl_init();
		curl_setopt ($handle, CURLOPT_URL, $uri);
		curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 1);
		curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
		$buffer = curl_exec($handle);
		curl_close($handle);
		return $buffer;
	} else {
		return false;
	}	
}

?>
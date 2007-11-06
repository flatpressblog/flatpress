<?php

	function config_read($fullpath) {
		if ($fullpath{0}!='/')
			trigger_error('config_read: syntax error. Path must begin with a /');
		$last_slash = strrpos($fullpath, '/');
		$option = substr($fullpath, $last_slash + 1);
		$path = substr($fullpath, 1, $last_slash);
		$file = str_replace('/', '.', $path) . 'conf.php';
		$f = CONFIG_DIR . $file;
		if (file_exists($f)) {
			include($f);
		}
		
		$arr = explode('/', $fullpath);
		
		/* todo finire */
		
	}
	
	// a cosmetic wrapper around an include :D
	// plus, loads the defaults if CONFIG_FILE is not found
	function config_load($conffile=CONFIG_FILE) {
		
		if ( !file_exists($conffile) && ($conffile==CONFIG_FILE) )
			$conffile = CONFIG_DEFAULT;
		
		include $conffile; 
		
		// todo CHANGE
		//$fp_config['general'] = array_change_key_case($blog_confi);
		return $fp_config;
		
	}


	// $conf_arr can have a variable number of args
	// they are the same of system_save(), as this is in fact
	// a wrapper to that ;)
	// so:
	// $conf_arr[ 'myvariable' ] = $myvariable;
	function config_save($conf_arr=null, $conffile=CONFIG_FILE) {
		if ($conf_arr==null) {
			global $fp_config;
			$conf_arr=$fp_config;
		}
	
		$arr = array('fp_config' => $conf_arr);
		return system_save($conffile, $arr);
	}

?>
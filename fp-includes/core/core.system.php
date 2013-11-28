<?php 

	/**
	 * system.php
	 * string-to-php and general system functions 
	 */
	
	
	

	/**
	 * function system_save
	 * 
	 * This function saves a list of variables provided after $file
	 * encapsulated in an array where KEY is the var name
	 * in a php file.
	 *
	 * Example usage:
	 * <code>
	 * <?php
	 * // Let's suppose you want to save an array called $my_arr
	 *  // in file $my_file
	 * $my_file = 'path/to/file'
	 * $my_arr = array ('val1', 'val2', 'val3');
	 * $save_arr = array('$my_arr' => $my_arr); //same as: $save_arr['$my_arr'] = $my_arr);
	 * system_save($my_file, $my_arr);
	 * // now the file $my_file will contain the following lines:
	 *  // global $my_arr;
	 * // $my_arr = array (
	 * //           '$my_arr' => val1', 
	 * //           '$my_arr' => 'val2', 
	 * //           '$my_arr' => 'val3'
	 * //           );
	 * ?>
	 * </code>
	 *
	 * @param string $file file path where $array contents will be saved
	 * @array $var_list list of vars to be saved
	 * @return bool
	 *
	 * @see config_save, config_load
	 *
	 */
	function system_save($file, $array ) {
		
		//if ( ( $numargs = func_num_args() ) > 1) {
		
			$string = "<?php\n\n";
		
			//$arg_list = func_get_args();
			foreach ($array as $key => $arg) {
				//$vname = utils_vname ($arg);
				//var_export($arg);
				$s = /*"  global {$key};\n*/  "\${$key} = " .
						var_export($arg, true) . ";\n";
				$string .= $s;
			}
		
			$string .= "\n?>";
			
			return io_write_file($file, $string);
		
		//} else die('Wrong number of parameters!');
			
	}

	function system_hashsalt_save($force=false) {
		global $fp_config;
		if ($force || !file_exists(HASHSALT_FILE))
			return system_save(HASHSALT_FILE, array('fp_hashsalt'=>$fp_config['general']['blogid'] . ABS_PATH . BLOG_BASEURL .mt_rand()));	
		return true;
	}


	
	define('SYSTEM_VER', '1.0.2');
	function system_ver() {
		return 'fp-' . SYSTEM_VER;
	}

	function system_ver_compare($newver, $oldver) {
		$nv_arr = explode('.', $newver);
		$ov_arr = explode('.', $oldver);
		$cn = count($nv_arr);
		$co = count($ov_arr);
		$max = min($cn, $co);

		// let's compare if one of the first version numbers differs
		// from new version, being greater
		for ($i=0; $i<$max; $i++) {
			if ( $nv_arr[ $i ] > $ov_arr[ $i ] ) { return 1; }
			if ( $nv_arr[ $i ] < $ov_arr[ $i ] ) { return 0; }
		}

		// if they equals, but still new version has more digits
		// then old-version is still outdated
		if ($cn > $co) return 1;

		
	}

	function system_generate_id($string) {
		return 'fp-'.dechex(crc32($string) ^ mt_rand());
	}
	
	function system_guessblogroot() {
		return substr($_SERVER['REQUEST_URI'],	0,strrpos($_SERVER['REQUEST_URI'],'/')+1);
	}
	
	function system_guessbaseurl() {
		return 'http://'.$_SERVER['HTTP_HOST']. BLOG_ROOT;
	}
	
	function system_getindex() {
		if (MOD_BLOG != INDEX)
			return MOD_BLOG;
		else
			return 'index.php'; 
	}
	
	function system_unregister_globals() {
		$v = @ini_get('register_globals');
		
		// on error we unregister anyway
		if ($v || is_null($v)) {
			foreach ($_REQUEST as $var => $val) {
				unset($GLOBALS[$var]);
			}
		}
		
	}
	
	function system_sanitizequery() {
		$err = false;
		foreach ($_GET as $k => $v) {
			if (preg_match('![<>]|://!', $v)) {
				$err = true;
				break;
			}
		}
		if ($err) {
			// @todo add log handler
			utils_redirect();
		}
	}

	function system_prepare_iis() {
		if (!@$_SERVER['REQUEST_URI']) {
			$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 ); 
	        if (isset($_SERVER['QUERY_STRING'])) { 
			  	$_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING']; 
			} 
		  }
	}
	

	function system_init_action_params() {
		
		global $fp_params;
		
		$fp_params = array();
		
		if ($x = @$_GET['x'])
			$fp_params = utils_kexplode($x, ':;', false);
		
		$fp_params = array_merge($_GET, $fp_params);
		
	}


	
	function system_init() {
		
		system_sanitizequery();
		system_unregister_globals();
		system_prepare_iis();
		
		$GLOBALS['fpdb'] = new FPDB;
							
		$GLOBALS['fp_widgets'] = new widget_indexer;
		
		$GLOBALS['smarty'] =& $GLOBALS['_FP_SMARTY'];
		$smarty =& $GLOBALS['smarty'];
		
		$GLOBALS['fp_config'] = config_load();
		
		cookie_setup();
		sess_setup();
		user_loggedin();
		
		ob_start();
		
		$GLOBALS['theme'] = theme_loadsettings();
		
		$GLOBALS['lang'] = lang_load();
		

		plugin_loadall();
		
		// init smarty	
		$smarty->compile_dir = CACHE_DIR;
		$smarty->cache_dir = SMARTY_DIR . 'cache/';
		$smarty->caching = 0;
	
		do_action('init');
		ob_end_clean();
		
	}

	function system_seterr($module, $val) {
		if ($module)
			$elem = 'success_'.$module;
		else
			$elem = 'success';
		sess_add($elem, $val);
	}

	function system_geterr($module='') {
		if ($module)
			$elem = 'success_'.$module;
		else
			$elem = 'success';
		return sess_remove($elem);
	}
	
	/* delayed print */
	function system_dpr($action, $content) {
		$p = print_r($content,1);
		$f = create_function('', "echo '<pre style=\'position:absolute\'>$p</pre>';");
		add_action($action, $f);
	}
	

?>

<?php

	//define('PLUG_BLOCK', 'block');

	
	class plugin_indexer extends fs_filelister {
		
		var $_varname = 'fp_plugins';
		var $_enabledlist = null;
		var $_directory = PLUGINS_DIR;
		
		function plugin_indexer() {
			$this->_enabledlist = CONFIG_DIR . 'plugins.conf.php';
			parent::fs_filelister();
		}
		
		function _checkFile($directory, $file) {
			$f = "$directory/$file";
			if (is_dir($f) && file_exists("$f/plugin.$file.php")) {
				array_push($this->_list, $file);
			}
			return 0;
		}
		
		/*
		 *
		 * @param $checkonly bool 	if false will load all the plugins, 
		 *				if true will check if the plugin exist 
		 */
		
		function getEnableds($checkonly) {
		
			$lang =& $GLOBALS['lang'];
			$errors = array();
		
			if (!file_exists($this->_enabledlist))
				return false;
			include($this->_enabledlist);
			$var = $this->_varname;
			
			$this->enabledlist = $$var;
			
			foreach ($$var as $plugin) {
				
				$e = plugin_load($plugin, $checkonly);
				if ($e)
					$errors[] = $e;
				
			}
			
			return $errors;
			
		}
		
	}
	
	function plugin_loadall($check=false){
		
		// this is done during init process
		// all the plugin are loaded
		
		$pluginlister = new plugin_indexer;
		$enab = $pluginlister->getEnableds($check);
		
		include_once (INCLUDES_DIR . 'core.wp-pluggable-funcs.php');
		
		return $enab;
		
	}

	function plugin_get($id=null){
	
		$pluginlister = new plugin_indexer;
		return $pluginlister->getList();
		
	}
	
	function plugin_loaded($id) {
		
		if (file_exists(PLUGINS_DIR . $id. '/plugin.'. $id.".php")) {
			return true;
		} 
		
		return false;
	}
	
	function plugin_load($plugin, $checkonly=true, $langload=true) {
	
				global $lang;
	
				$errno = 0;
				$errors = false;
				
				if (file_exists($f = PLUGINS_DIR . "$plugin/plugin.$plugin.php")){
					$errno = 1; // 1 means exists
				}elseif (file_exists($f = PLUGINS_DIR . "$plugin/$plugin.php")){
					$errno = 2; // 2 means exists but filename is oldstyle
				} 
				
				
				if ($errno > 0){
					ob_start();
					include_once($f);
					ob_end_clean();
				}
				
				if ($langload)
					@lang_load("plugin:{$plugin}");
				
				if ($checkonly) {
					$func = "plugin_{$plugin}_setup";
	
					if (is_callable($func)){
						$errno = $func();
					}
				
					if ($errno<=0) {
						
						if (isset($lang['plugin'][$plugin]['errors'][$errno])) {
							$errors = "[<strong>{$plugin}</strong>] {$lang['plugin'][$plugin]['errors'][$errno]}";
						} elseif($errno<0) {
							$errors = "[<strong>$plugin</strong>] " . 
							sprintf($lang['admin']['plugin']['errors']['generic'], $errno);
						} else {
							$errors = "[<strong>$plugin</strong>] " . 
								$lang['admin']['plugin']['errors']['notfound'];
						}
						
					}
				}
				
				return $errors;
				
	}

	function plugin_exists($id) {
		return file_exists($f = PLUGINS_DIR . $id . '/plugin.'. $id.".php");
	}

	function plugin_do($id, $type=null){
		$entry = null;
		if (file_exists($f = PLUGINS_DIR . 'plugin.'. $id.".php")) {
			include_once($f);
		} else return false;
	}
	
	function plugin_require($id) {
	
		return !plugin_loaded($id);
			/*
			global $_FP_SMARTY;
			$_FP_SMARTY->trigger_error("A plugin required <strong>$id</strong> to be loaded to work properly, but $id ".
			"does not appear to be loaded. Maybe the plugins have been loaded in the wrong sequence. ".
			"Check your <a href=\"admin.php?p=plugins\">plugin config</a> in the control panel");
			*/
			
	}
	
	function plugin_getdir($id) {
		return PLUGINS_DIR . $id . '/';
	}
	
	function plugin_geturl($id) {
		return BLOG_BASEURL . PLUGINS_DIR . $id . '/';
	}
	
	/*
	 *
	 *	plugin options system might 
	 *	change
	 *
	 */
	
	function plugin_getoptions($plugin, $key=null) {
		global $fp_config;
		
		if ($key && isset($fp_config['plugins'][ $plugin ][ $key ]))
			return $fp_config['plugins'][ $plugin ][ $key ];
		
		return isset($fp_config['plugins'][ $plugin ])?
			$fp_config['plugins'][ $plugin ]
			: null;
	}
	
	function plugin_addoption($plugin, $key, $val) {
		global $fp_config;
		if (!isset($fp_config['plugins']))
			$fp_config['plugins'] = array();
		if (!isset($fp_config['plugins'][$plugin]))
			$fp_config['plugins'][$plugin] = array();
		
		return $fp_config['plugins'][ $plugin ][ $key ] = $val ;
	}
	
	function plugin_saveoptions($null=null) {
		return config_save();
	}
	
	
	
	function smarty_function_plugin_getdir($params, &$smarty) {
		if (!isset($params['plugin'])) //todo complete here
			$smarty->trigger_error('You must set plugin= parameter to a valid id!'); 
		return plugin_getdir($id);
	}
	
	function plugin_getinfo($plugin) {
	    $plugin_data = io_load_file(plugin_getdir($plugin) . "plugin.$plugin.php");
        preg_match("|Plugin Name:(.*)|i", $plugin_data, $plugin_name);
        preg_match("|Plugin URI:(.*)|i", $plugin_data, $plugin_uri);
        preg_match("|Description:(.*)|i", $plugin_data, $description);
        preg_match("|Author:(.*)|i", $plugin_data, $author_name);
        preg_match("|Author URI:(.*)|i", $plugin_data, $author_uri);
        if (preg_match("|Version:(.*)|i", $plugin_data, $version))
                $version = trim($version[1]);
        else
                $version = '';

        $description = wptexturize(trim($description[1]));

        $name = $plugin_name[1];
        $name = trim($name);
        $plugin = $name;
        
       
        if ('' != $plugin_uri[1] && '' != $name) {
        		// '" title="'.__('Visit plugin homepage').'">'.
                $plugin = '<a href="' . trim($plugin_uri[1]) . $plugin.'</a>';
        }

        if ('' == $author_uri[1]) {
                $author = trim($author_name[1]);
        } else {
        		// . '" title="'.__('Visit author homepage').
                $author = '<a href="' . trim($author_uri[1]) . '">' . trim($author_name[1]) . '</a>';
        }

		
        global $smarty;
        $smarty->assign(
        	array (
        			  'name' => $name, 
        			  'title' => $plugin, 
        			  'description' => $description, 
        			  'author' => $author, 
        			  'version' => $version, 
        			  'template' => $template[1]
        			  )
        	);

	}
	
	$_FP_SMARTY->register_function('plugin_getdir','smarty_function_plugin_getdir');


?>

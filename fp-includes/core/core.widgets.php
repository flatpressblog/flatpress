<?php	

	/**
	 * Block-Managing Functions
	 */
	 
	 
	 
	class widget_indexer extends fs_filelister {
		
		var $_varname = 'fp_widgets';
		var $_enabledlist = null;
		
		function widget_indexer() {
			if (!file_exists(CONFIG_DIR. 'widgets.conf.php')) 
				trigger_error('widgets.conf.php not found. Blog may not work as expected, create a widgetlist.conf.php 
					or reinstall completely FlatPress. If you have just installed FlatPress, the package you
					downloaded may be corrupted.', E_USER_WARNING);
			$this->_enabledlist = CONFIG_DIR . 'widgets.conf.php';
			$this->getEnableds();
		}

		function getEnableds() {
			
			if (!file_exists($this->_enabledlist))
				return;
			
			include($this->_enabledlist);
			
			$this->_list = ${$this->_varname};
			
		}
		
		function hasMore($hor) {

			return is_array($this->_list[$hor]) &&  (current($this->_list[$hor]) !== false);
		}
		
	
		
		function get($hor) {
		
			global $fp_registered_widgets;
		
			do {
				$content = array();

				list(,$id) = each($this->_list[$hor]);
				
				$newid=$id;# @list($newid, $params) = explode(":", $id);
				if (@$params) $params = explode(',', $params); else $params = array();
				// $var = 'plugin_' . $newid . '_widget';
				$var = $fp_registered_widgets[ $newid ]['func'];
				if (is_callable($var)) {
					$content = call_user_func_array($var, $params); 
					if (!isset($content['id'])) {
						$content['id'] = "widget-$newid";
					}
				} /*
					else $content = array(
					'subject' => "Sidebar::Error", 
					'content' => "<ul class=\"widget-error\"><li>No $var function found for plugin $newid. 
					Plugin may not have been loaded. 
					Verify whether it is enabled.</li></ul>",
					);
					*/
				
			} while(!$content && $id);
				
			return array_change_key_case($content, CASE_LOWER);
				
			
			
			
		}

	}
	
	
	function register_widgetset($widgetset) {
		global $fp_registered_widgetsets;
		if (!$fp_registered_widgetsets) {
			$fp_registered_widgetsets = array();
		}
		
		if (!in_array($widgetset, $fp_registered_widgetsets))
			$fp_registered_widgetsets[] = $widgetset;
			
	}
	
	
	
	function get_registered_widgetsets($widgetset) {
		global $fp_registered_widgetsets;
		if (!$fp_registered_widgetsets) {
			$fp_registered_widgetsets = array();
		}
		
		return $fp_registered_widgetsets;
	}
	
	
	
	function register_widget(
						$widgetid, 				// widget id
						$widgetname,			// name to show 
						$widget_func, 			// function/method to call 
						$num_params = 0, 		// number of eventually needed parameters
												// -1 means optional,
												// 0 means no parameters
												// each N>0 means *at least* N parameters
						 
						$limit_params_to=array()// indexed array of arrays, containing
														// allowed parameters (not impl.)  
						) {
						
		global $fp_registered_widgets;
		if (!$fp_registered_widgets)
			$fp_registered_widgets = array();
		
		/* we won't mind about collisions, for now */
		
		$fp_registered_widgets[$widgetid] = array(
			'name'	=>	$widgetname,
			'func'	=>	$widget_func, 
			'nparams'=>	$num_params, 
			//'needed'=>	$params_needed, 
			'params'=>	$limit_params_to
		);
		  
	}
	
	
	
	function get_registered_widgets($widget=null) {
		global $fp_registered_widgets;
		
		if (!$fp_registered_widgets)
			$fp_registered_widgets = array();
		
	
		ksort($fp_registered_widgets);
	
		if ($widget)
			return isset($fp_registered_widgets[$widget])? 
							$fp_registered_widgets[$widget]
							:
							false;
		
		return $fp_registered_widgets;	
	
	}



	function smarty_block_widgets($params, $content, &$smarty, &$repeat) {
		global $fp_widgets;
		
		if($repeat = $fp_widgets->hasMore(($params['pos']))) {
			
			$entry = $fp_widgets->get(($params['pos']));
			$smarty->assign($entry);
		}

		return $content;
		
	}		
	
	
	$smarty->register_block('widgets','smarty_block_widgets');
	
	

?>

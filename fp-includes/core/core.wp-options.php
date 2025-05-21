<?php

	//======================//
	/* Options functions */
	
	function get_settings($setting) {
		
		/*$options=get_alloptions();		
		return isset($options[$setting])? $options[$setting] : false;*/ 
		
	}
	
	function get_option($option) {
		return get_settings($option);
	}
	
	function form_option($option) {
	//	echo htmlspecialchars( get_option($option), ENT_QUOTES );
	}
	
	function get_alloptions() {
		
		global $blog_config;
		//global $theme;
		//if (!isset($theme)) die ('Invalid call of get_alloptions(): theme not loaded');
		
		$options=$blog_config;
		
		system_dprint($options);
		
		return $options;
		
	}
	
	function update_option($option_name, $newvalue) {
		$options = get_alloptions();
		$options[$option_name]=$newvalue;
		config_save($options);
	}
	
	
	// thx Alex Stapleton, http://alex.vort-x.net/blog/
	function add_option($name, $value = '', $description = '', $autoload = 'yes') {
		//not yet fully implemented
		$options=get_alloptions();
		$options[$name]=$value;
		return config_save($options);
	}
	
	function delete_option($name) {
		$options = get_alloptions();
		unset($options[$name]);
		return config_save($options);
	}

?>

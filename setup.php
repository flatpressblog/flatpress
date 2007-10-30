<?php

	require_once('defaults.php');
	require_once(INCLUDES_DIR.'includes.php');
	
	define('SETUPTEMP_FILE', FP_CONTENT . 'settingup.lock');

	 @system_init();
	
	if (empty($_POST)) {
		session_destroy();
		cookie_clear();
	}

	
	require('./setup/main.php');	
	
?>
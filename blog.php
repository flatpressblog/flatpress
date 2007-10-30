<?php

	// Example of use
	require_once 'defaults.php';
	require_once (INCLUDES_DIR.'includes.php');

	@utils_status_header(301);	
	@utils_redirect('?'.$_SERVER['QUERY_STRING']);	

?>

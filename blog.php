<?php

	// Example of use
	require_once 'defaults.php';
	require_once (INCLUDES_DIR.'includes.php');

	/* backward compatibility */
	
	if (!@$_GET['entry']) {
		@utils_redirect('?'.$_SERVER['QUERY_STRING']);	
	} else {
		@utils_status_header(301);
		@utils_redirect(str_replace('&amp;','&', get_permalink($_GET['entry'])), true);
	}

?>

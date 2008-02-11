<?php

	// Example of use
	require_once 'defaults.php';
	require_once (INCLUDES_DIR.'includes.php');

	/* backward compatibility */
	@utils_status_header(301);
	@utils_redirect(str_replace('&amp;','&', theme_feed_link()) . '&' . $_SERVER['QUERY_STRING'], true);	

?>

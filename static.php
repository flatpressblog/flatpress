<?php

	include 'defaults.php';
	include INCLUDES_DIR . 'includes.php';
	
	/* backward compatibility */
	
	if (!@$_GET['page']) {
		@utils_redirect();
	} else {
		@utils_status_header(301);
		@utils_redirect(str_replace('&amp;','&', theme_staticlink($_GET['page'])), true);
	}
	
	
?>

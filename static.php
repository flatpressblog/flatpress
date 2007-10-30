<?php

	include 'defaults.php';
	include INCLUDES_DIR . 'includes.php';
	@utils_status_header(301);
	@utils_redirect("?page={$_GET['page']}");
?>

<?php
function check_step() {

	global $err, $lang;

	if(check_write(SETUPTEMP_FILE, 2)) {

		$r  = fs_mkdir(CACHE_DIR);

		$r = $r && fs_mkdir(COMPILE_DIR);

		$r = $r && fs_mkdir(INDEX_DIR);

		$r = $r && fs_copy(CONFIG_DEFAULT, CONFIG_FILE);

		$r = $r && fs_copy(FP_DEFAULTS . 'plugins.conf.php', CONFIG_DIR . 'plugins.conf.php');

		$r = $r && fs_copy(FP_DEFAULTS . 'widgets.conf.php', CONFIG_DIR . 'widgets.conf.php');

		// $r = $r && create_content();

		if (!$r) {
			$err [] = $lang ['err'] ['writeerror'];
		}

		// fs_mkdir() returns bool, as does fs_copy().
		return (bool)$r;

	}

	$err [] = $lang ['err'] ['writeerror'];

	return false;
}
?>

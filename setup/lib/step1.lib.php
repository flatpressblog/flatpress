<?php

function setup_prepare_files() {
	global $err, $lang;
	if (!setup_data_preflight(true)) {
		return false;
	}
	$ok = fs_mkdir(CACHE_DIR) && fs_mkdir(COMPILE_DIR) && fs_mkdir(INDEX_DIR);
	foreach ([CONFIG_DEFAULT => CONFIG_FILE, FP_DEFAULTS . 'plugins.conf.php' => CONFIG_DIR . 'plugins.conf.php', FP_DEFAULTS . 'widgets.conf.php' => CONFIG_DIR . 'widgets.conf.php'] as $source => $target) {
		// Retrying an incomplete preparation must not reset previously saved settings.
		$ok = $ok && (is_file($target) || fs_copy($source, $target));
	}
	if (!$ok) {
		$err [] = $lang ['err'] ['writeerror'];
	}
	return $ok;
}

?>

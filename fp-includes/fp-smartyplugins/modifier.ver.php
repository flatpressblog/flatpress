<?php
/**
 * Smarty modifier |ver – appends ?v=… (see utils_asset_ver()).
 * Use in template:
 *   <script src={" /editor.js"|ver}></script>
 *   <link rel="stylesheet" href={"/css/app.css"|ver:$smarty.const.SYSTEM_VER}>
 */
function smarty_modifier_ver($path, $version = null) {
	if (!function_exists('utils_asset_ver')) {
		// core.utils.php should be loaded via includes.php.
		return (string)$path;
	}
	return utils_asset_ver((string)$path, $version);
}
?>

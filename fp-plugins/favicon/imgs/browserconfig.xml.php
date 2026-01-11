<?php
declare(strict_types=1);
@error_reporting(0);

header('Content-Type: application/xml; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once dirname(__FILE__, 4) . '/defaults.php';
require_once dirname(__FILE__, 4) . '/fp-includes/core/core.system.php';

$baseUrl = rtrim ((string)BLOG_BASEURL, "/") . "/";

$pluginsDir = defined('PLUGINS_DIR') ? (string)PLUGINS_DIR : 'fp-plugins/';
$pluginsDir = trim($pluginsDir, "/\\") . '/';
$pluginRel = $pluginsDir . 'favicon/imgs/';

$siteBase = $baseUrl;
if (substr($baseUrl, -strlen($pluginRel)) === $pluginRel) {
    $siteBase = substr($baseUrl, 0, -strlen($pluginRel));
}
$siteBase = rtrim ($siteBase, "/") . "/";

$imgUrlBase = $siteBase . $pluginRel;
$imgFsBase = rtrim (ABS_PATH, "/\\") . '/' . $pluginsDir . 'favicon/imgs/';

$assetVer = function (string $filename) use ($imgFsBase): string {
	$file = $imgFsBase . $filename;
	if (is_file($file)) {
		$mtime = @filemtime($file);
		if (is_int($mtime) && $mtime > 0) {
			return '?v=' . rawurlencode((string)$mtime);
		}
	}
	if (defined('SYSTEM_VER')) {
		return '?v=' . rawurlencode((string)SYSTEM_VER);
	}
	return '';
};

$e = function (string $s): string {
	return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
};

echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
echo '<browserconfig>' . "\n";
echo "	<msapplication>\n";
echo "		<tile>\n";
echo "			<square70x70logo src=\"" . $e($imgUrlBase . 'mstile-70x70.png' . $assetVer('mstile-70x70.png')) . "\"/>\n";
echo "			<square150x150logo src=\"" . $e($imgUrlBase . 'mstile-150x150.png' . $assetVer('mstile-150x150.png')) . "\"/>\n";
echo "			<square310x310logo src=\"" . $e($imgUrlBase . 'mstile-310x310.png' . $assetVer('mstile-310x310.png')) . "\"/>\n";
echo "			<wide310x150logo src=\"" . $e($imgUrlBase . 'mstile-310x150.png' . $assetVer('mstile-310x150.png')) . "\"/>\n";
echo "			<TileColor>#b77b7b</TileColor>\n";
echo "		</tile>\n";
echo "	</msapplication>\n";
echo '</browserconfig>' . "\n";
?>

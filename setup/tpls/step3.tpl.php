<h2><?php echo $l ['head']; ?></h2>
<div class="post">

<?php
/**
 * During setup, BLOG_BASEURL may still reflect the pre-configuration value within the same request.
 * Use the freshly updated config value when available.
 */
global $fp_config;
$base = '';

if (isset($fp_config) && is_array($fp_config)) {
	$base = (string)($fp_config ['general'] ['www'] ?? '');
}
if ($base === '') {
	$base = (string)BLOG_BASEURL;
}
if ($base !== '' && substr($base, -1) !== '/') {
	$base .= '/';
}
echo wpautop(sprintf($l ['descr'], $base, $base . 'login.php'));
?>

</div>

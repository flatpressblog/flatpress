<h2><?php echo $l ['head']; ?></h2>
<div class="post">

<?php
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
echo wpautop(sprintf($l ['descr'], LOCKFILE, $base, $base . 'setup.php'));
?>

</div>
<?php header('Content-Type: text/xml; charset=utf-8');
@error_reporting(0);

require_once('../../../defaults.php');
$blogBaseUrl = BLOG_BASEURL;

// Indicates the version of the symbol. Increase it by one when you change the image ($v = '?v=3', $v = '?v=4', etc.).
// The browser will then immediately display the latest version.
$v = '?v=2';
?>
<?xml version="1.0" encoding="utf-8"?>
<browserconfig>
	<msapplication>
		<tile>
			<square70x70logo src="<?php echo $blogBaseUrl;?>mstile-70x70.png<?php echo $v;?>"/>
			<square150x150logo src="<?php echo $blogBaseUrl;?>mstile-150x150.png<?php echo $v;?>"/>
			<square310x310logo src="<?php echo $blogBaseUrl;?>mstile-310x310.png<?php echo $v;?>"/>
			<wide310x150logo src="<?php echo $blogBaseUrl;?>mstile-310x150.png<?php echo $v;?>"/>
			<TileColor>#b77b7b</TileColor>
		</tile>
	</msapplication>
</browserconfig>

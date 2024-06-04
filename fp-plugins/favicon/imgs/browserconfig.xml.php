<?php header('Content-Type: text/xml; charset=utf-8');
@error_reporting(0);

require_once('../../../defaults.php');
$blogBaseUrl = BLOG_BASEURL;
?>
<?xml version="1.0" encoding="utf-8"?>
<browserconfig>
	<msapplication>
		<tile>
			<square70x70logo src="<?php echo $blogBaseUrl;?>mstile-70x70.png"/>
			<square150x150logo src="<?php echo $blogBaseUrl;?>mstile-150x150.png"/>
			<square310x310logo src="<?php echo $blogBaseUrl;?>mstile-310x310.png"/>
			<wide310x150logo src="<?php echo $blogBaseUrl;?>mstile-310x150.png"/>
			<TileColor>#b77b7b</TileColor>
		</tile>
	</msapplication>
</browserconfig>

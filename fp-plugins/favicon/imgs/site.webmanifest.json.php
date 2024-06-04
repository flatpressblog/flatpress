<?php header('Content-Type: text/json; charset=utf-8');
@error_reporting(0);

require_once('../../../defaults.php');
$blogBaseUrl = BLOG_BASEURL;
?>
{
	"icons": [
		{
			"src": "<?php echo $blogBaseUrl;?>android-chrome-192x192.png",
			"sizes": "192x192",
			"type": "image/png"
		},
		{
			"src": "<?php echo $blogBaseUrl;?>android-chrome-256x256.png",
			"sizes": "256x256",
			"type": "image/png"
		}
	],
	"theme_color": "#b77b7b",
	"background_color": "#b77b7b"
}

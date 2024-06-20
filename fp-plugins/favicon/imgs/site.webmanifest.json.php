<?php header('Content-Type: text/json; charset=utf-8');
@error_reporting(0);

require_once('../../../defaults.php');
$blogBaseUrl = BLOG_BASEURL;

// Indicates the version of the symbol. Increase it by one when you change the image ($v = '?v=3', $v = '?v=4', etc.).
// The browser will then immediately display the latest version.
$v = '?v=2';
?>
{
	"icons": [
		{
			"src": "<?php echo $blogBaseUrl;?>android-chrome-192x192.png<?php echo $v;?>",
			"sizes": "192x192",
			"type": "image/png"
		},
		{
			"src": "<?php echo $blogBaseUrl;?>android-chrome-256x256.png<?php echo $v;?>",
			"sizes": "256x256",
			"type": "image/png"
		}
	],
	"theme_color": "#b77b7b",
	"background_color": "#b77b7b"
}

<?php
header('Content-Type: text/css; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: max-age=31536000');

require_once __DIR__ . '/../../../defaults.php';
$url = BLOG_BASEURL . PLUGINS_DIR . 'feed/res/';
?>

.feed-widget {
	margin: 0px;
	height: 16px
}

/**
 * ===========
 * Font Zocial
 * ===========
 *
 * Name: Zocial
 * Module: zocial-rss.css
 * Designer Name: Sam Collins
 * Author URI: https://github.com/smcllns/css-social-buttons
 * Description: This file defines the font
 * Last change: 08.01.2025 by FKM
 * License: The font of Zocial is under MIT-License
 */

@font-face {
	font-family: 'zocial';
	font-display: swap;
	src: local('zocial-rss'),
	url('<?php echo $url;?>zocial/zocial-rss.woff2') format('woff2');
	font-weight: normal;
	font-style: normal;
}

[class^="icon-"]:before, [class*=" icon-"]:before {
	font-family: "zocial";
	font-style: normal;
	font-weight: normal;
	speak: never;

	display: inline-block;
	text-decoration: inherit;
	width: 1em;
	margin-right: .2em;
	text-align: center;
	/* opacity: .8; */

	/* For safety - reset parent styles, that can break glyph codes*/
	font-variant: normal;
	text-transform: none;

	/* fix buttons height, for twitter bootstrap */
	line-height: 1em;

	/* Animation center compensation - margins should be symmetric */
	/* remove if not needed */
	margin-left: .2em;

	/* you can be more comfortable with increased icons size */
	/* font-size: 120%; */

	/* Font smoothing. That was taken from TWBS */
	-webkit-font-smoothing: antialiased;
	-moz-osx-font-smoothing: grayscale;

	/* Uncomment for 3D effect */
	/* text-shadow: 1px 1px 1px rgba(127, 127, 127, 0.3); */
}

.icon-rss:before { content: '\e800'; } /* '' */

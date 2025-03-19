<?php
// Turn off all error reporting
error_reporting(0);

header('Content-Type: text/css; charset=utf-8');

// PHP4.1.0 or later supported
if (phpversion() >= "4.1.0") {
	extract($_GET);
}

// load language file
require_once '../../../defaults.php';
if (file_exists(CONFIG_DIR . 'settings.conf.php')) {
	require_once CONFIG_DIR . 'settings.conf.php';
}
$langId = $fp_config ['locale'] ['lang'];
$langFile = ABS_PATH . PLUGINS_DIR . 'gdprvideoembed/lang/lang.' . $langId . '.php';

if (!file_exists($langFile)) {
	$langFile = ABS_PATH . PLUGINS_DIR . 'gdprvideoembed/lang/lang.en-us.php';
}

require_once $langFile;
?>
/*
 * Name: GDPR video embed
 * Module: ggdpr-video-embed.css
 * Plugin URI: https://www.flatpress.org
 * Author: Fraenkiman
 * Author URI: https://frank-web.dedyn.io
 */

.responsive_bbcode_video iframe { display: inline-block }

.responsive_bbcode_video iframe[data-src] {
	background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg"><text text-anchor="middle" alignment-baseline="central" y="50%" font-size="1em" fill="black"><tspan x="50%" dy="-1.5em"><?php echo $lang ['plugin'] ['gdprvideoembed'] ['noscript_1']; ?></tspan><tspan x="50%" dy="1.5em"><?php echo $lang ['plugin'] ['gdprvideoembed'] ['noscript_2']; ?></tspan><tspan x="50%" dy="1.5em"><?php echo $lang ['plugin'] ['gdprvideoembed'] ['noscript_3']; ?></tspan></text></svg>');
}

.responsive_bbcode_video,
.responsive_bbcode_video iframe[data-src] {
	position: relative;
	margin: 0 auto; /* einfügen */
	color: #333
}

.responsive_bbcode_video iframe[data-src] {
	border: 1px solid #ccc;
	border-radius: 3px
}

@media (max-width: 719px) {
	.responsive_bbcode_video {
		padding-bottom: unset !IMPORTANT;
		overflow: unset !IMPORTANT;
		height: auto !IMPORTANT;
		aspect-ratio: 1.777 / 1
	}
}

.responsive_bbcode_video strong {
	display: block;
	text-align: center;
	font-size: 1em;
	margin: 0.5em 0;
	display: block;
	white-space: nowrap;
	position: absolute;
	left: 50%;
	transform: translateX(-50%)
}

.responsive_bbcode_video div {
	position: absolute;
	width: calc(100% - 1em);
	top: 2em;
	bottom: 5em;
	overflow-y: auto;
	margin: 0.5em;
	background-color: #efefef;
	border: 1px solid #ccc;
	border-radius: 3px
}

/* === the right Scrollbar === */
/* Chrome, Edge, and Safari */
@supports selector(::-webkit-scrollbar) {
	.responsive_bbcode_video div::-webkit-scrollbar {
		width: 6px
	}

	.responsive_bbcode_video div::-webkit-scrollbar-thumb {
		background-color: #ccc;
		border-radius: 3px
	}
}

/* Firefox */
@supports (-moz-appearance:button) and (contain:paint) {
	.responsive_bbcode_video div {
		scrollbar-width: thin;
		scrollbar-color: #ccc #EEE
	}
}

.responsive_bbcode_video p {
	font-size: 0.8em;
	margin: 0 0 1em;
	text-align: left;
	padding: 0 0 0 6px
}

.responsive_bbcode_video a { color: inherit }

.responsive_bbcode_video .video-link {
	display: block;
	white-space: nowrap;
	font-size: 0.8em;
	margin: 0;
	position: absolute;
	left: 50%;
	bottom: 4.5em;
	transform: translateX(-50%)
}

.responsive_bbcode_video button {
	cursor: pointer;
	display: block;
	height: 2.25em;
	text-align: left;
	margin: 0;
	position: absolute;
	left: 50%;
	bottom: 1em;
	transform: translateX(-50%)
}

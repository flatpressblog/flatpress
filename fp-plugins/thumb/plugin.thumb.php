<?php

/*
 * Plugin Name: Thumbnails
 * Version: 1.1.0
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Creates thumbnails for scaled images. Part of the standard distribution.
 */
define('THUMB_DIR', '.thumbs');

if (!function_exists('imagegd2')) {
	define('PLUGIN_THUMB_ENABLED', false);
} else {
	define('PLUGIN_THUMB_ENABLED', true);
}

function plugin_thumb_setup() {
	return PLUGIN_THUMB_ENABLED ? 1 : -1;
}

/**
 *
 * plugin_thumb_create
 *
 * creates a thumbnail and caches the thumbnail in IMAGES_DIR/.thumb
 *
 * @param string $fpath string with filepath
 * @param array $infos infos from getimagesize($fpath)
 * @param int $new_width Width of the thumbnail
 * @param int $new_height Height of the thumbnail
 *
 * @return array [string $thumbpath, int $thumbwidth, int $thumbheight]
 *        
 */
function plugin_thumb_create($fpath, $infos, $new_width, $new_height) {
	if (!defined('PLUGIN_THUMB_ENABLED')) {
		return [];
	}

	if (!file_exists($fpath)) {
		return [];
	}

	if (!($new_width && $new_height)) {
		trigger_error("Size can't be 0 but got width=" . $new_width . " height=" . $new_height . "\n", E_USER_WARNING);
		return [];
	}

	$thumbname = basename($fpath);
	$thumbdir = dirname($fpath) . '/' . THUMB_DIR;
	$thumbpath = $thumbdir . '/' . $thumbname;

	if (file_exists($thumbpath)) {
		$oldthumbinfo = getimagesize($thumbpath);
		if ($new_width == $oldthumbinfo [0]) {
			return [$thumbpath, $new_width, $new_height];
		}
	}

	@fs_mkdir($thumbdir);

	// we support only jpeg's, png's and gif's

	switch ($infos [2]) {
		case 1:
			$image = imagecreatefromgif($fpath);
			break;
		case 2:
			$image = imagecreatefromjpeg($fpath);
			break;
		case 3:
			$image = imagecreatefrompng($fpath);
			break;
		case 18:
			if (function_exists('imagecreatefromwebp')) {
				$image = imagecreatefromwebp($fpath);
			} else {
				return [];
			}
			break;
		default:
			return [];
	}

	// $image = imagecreatefromgd2 ($fpath);

	// create empty scaled and copy(resized) the picture

	$scaled = imagecreatetruecolor($new_width, $new_height);
	/**
	 * If gif or png preserve the alpha channel
	 *
	 * Added by Piero VDFN
	 * Kudos to http://www.php.net/manual/en/function.imagecopyresampled.php#104028
	 */
	if ($infos[2] == 1 || $infos[2] == 3 || $infos[2] == 18) {
		imagecolortransparent($scaled, imagecolorallocatealpha($scaled, 0, 0, 0, 127));
		imagealphablending($scaled, false);
		imagesavealpha($scaled, true);

		if ($infos [2] == 3) {
			$output = 'png';
		} elseif ($infos [2] == 18) {
			$output = 'webp';
		} else {
			$output = 'gif';
		}
	} else {
		$output = 'jpg';
	}

	imagecopyresampled($scaled, $image, 0, 0, 0, 0, $new_width, $new_height, $infos [0], $infos [1]);

	if ($output == 'png') {
		imagepng($scaled, $thumbpath);
	} elseif ($output == 'gif') {
		imagegif($scaled, $thumbpath);
	} elseif ($output == 'webp' && function_exists('imagewebp')) {
		imagewebp($scaled, $thumbpath, 80);
	} else {
		imagejpeg($scaled, $thumbpath, 90);
	}

	@chmod($thumbpath, FILE_PERMISSIONS);
	return array(
		$thumbpath,
		$new_width,
		$new_height
	);
}

function plugin_thumb_bbcodehook($actualpath, $props, $newsize) {
	list ($width, $height) = $newsize;
	if ($thumb = plugin_thumb_create($actualpath, $props, $width, $height)) {
		$thumb = BBCODE_USE_WRAPPER ? ("getfile.php?f=" . basename($actualpath) . '&amp;thumb=true') : $thumb [0];
	}
	return $thumb;
}

add_filter('bbcode_img_scale', 'plugin_thumb_bbcodehook', 0, 3);

?>

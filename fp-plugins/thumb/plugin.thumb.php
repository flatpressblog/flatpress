<?php

/*
Plugin Name: Thumbnails
Plugin URI: http://www.nowhereland.it/
Description: Thumbnail plugin. Part of the standard distribution ;) If this is loaded scale parameter of images will create a scaled version of your img
Author: NoWhereMan real_nowhereman at user dot sf dot net
Version: 1.0
Author URI: http://www.nowhereland.it/
*/ 

define('THUMB_DIR', '.thumbs');


if (!function_exists('imagegd2'))
		{ define('PLUGIN_THUMB_ENABLED', false);  }
else { define('PLUGIN_THUMB_ENABLED', true); }


function plugin_thumb_setup() {

	return PLUGIN_THUMB_ENABLED? 1 : -1;
	
}


/**
 *
 * plugin_thumb_create
 *
 * creates a thumbnail and caches the thumbnail in IMAGES_DIR/.thumb
 *
 * @param string $fpath string with filepath
 * @param array $infos infos from getimagesize($fpath) function
 * @param int $new_width
 * @param int $new_height
 *
 * @return array array(string $thumbpath, int $thumbwidth, int $thumbheight)
 *
 */

function plugin_thumb_create($fpath, $infos, $new_width, $new_height) {

	if (!defined('PLUGIN_THUMB_ENABLED')) {
		return array();
	}
	
	if (!file_exists($fpath)) {
		return array();
	}

	if (!($new_width && $new_height)) {
		trigger_error("Size can't be 0 but got width=$new_width height=$new_height\n", E_USER_WARNING);
		return;
	}
	
	$thumbname = basename($fpath);
	$thumbdir  = dirname($fpath) . '/' . THUMB_DIR ;
	
	
	
	@fs_mkdir($thumbdir);
	
	$thumbpath = $thumbdir .'/'. $thumbname;
	
	
	
	/*
	$new_width = (int)($infos[0] * $scalefact);    
	$new_height = (int)($infos[1] * $scalefact);
	*/
	
	
	
	if (file_exists($thumbpath)) {
		$oldthumbinfo = getimagesize($thumbpath);
		if ($new_width==$oldthumbinfo[0]) {
			// already scaled
			return array($thumbpath, $new_width, $new_height);
		}
	}

	// we support only jpeg's, png's and gif's
	
	switch($infos[2]) {
		case 1: $image = imagecreatefromgif($fpath); break;
		case 2: $image = imagecreatefromjpeg ($fpath); break;
		case 3: $image = imagecreatefrompng($fpath);
	}
	
	
	//$image = imagecreatefromgd2 ($fpath);
	
	// create empty scaled and copy(resized) the picture
	
	
	
	$scaled = imagecreatetruecolor($new_width, $new_height);
	imagecopyresampled($scaled, $image, 0, 0, 0, 0, $new_width, $new_height, $infos[0], $infos[1]);
	
	imagejpeg($scaled, $thumbpath);
	@chmod($thumbpath, 0777);
	
	return array($thumbpath, $new_width, $new_height);
	
}

function plugin_thumb_bbcodehook($actualpath, $props, $newsize){
	list($width, $height) = $newsize;
	if ($thumb = plugin_thumb_create($actualpath, $props, $width, $height))
		$thumb = BBCODE_USE_WRAPPER? 
				("getfile.php?f=" . basename($actualpath) .'&amp;thumb=true') : $thumb[0];
	return $thumb;
}

add_filter('bbcode_img_scale', 'plugin_thumb_bbcodehook', 0, 3);

?>

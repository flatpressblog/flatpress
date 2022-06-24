<?php

/*
 * Functions for handling image galleries.
 */

/**
 * The name of the captions file
 *
 * @var string
 */
const GALLERY_CAPTIONS_FILENAME = '.captions.conf';

/**
 * The name of the captions file (legacy mode for galleries managed with PhotoSwipe plugin version < 1.1)
 *
 * @var string
 */
const GALLERY_CAPTIONS_LEGACYFILENAME = 'texte.conf';

/**
 * Fetches all gallery directories from the images directory and returns their names as iterative array.
 *
 * @return string[] the gallery names
 */
function gallery_fetch_galleries() {
	$galleries = array();

	// return empty array if there is no image dir
	if (!file_exists(ABS_PATH . IMAGES_DIR)) {
		return $galleries;
	}

	// read folder names from image dir
	$dir = opendir(ABS_PATH . IMAGES_DIR);
	while (false !== ($file = readdir($dir))) {
		$fullpath = ABS_PATH . IMAGES_DIR . $file;
		if (!fs_is_directorycomponent($file) && !fs_is_hidden_file($file) && is_dir($fullpath)) {
			$galleries [] = $file;
		}
	}

	// return result
	return $galleries;
}

/**
 * Reads the images from the gallery directory and returns their names as iterative array
 *
 * @param string $galleryDir
 *        	the gallery dir, e.g. 'images/NameOfTheGallery'
 * @return array
 */
function gallery_read_images($galleryDir) {
	$d = substr_replace($galleryDir, IMAGES_DIR, 0, 7);
	$fs = new fs_filelister($d);
	$l = $fs->getlist();
	foreach ($l as $i => $f) {
		// remove caption files
		if ($f === GALLERY_CAPTIONS_FILENAME || $f === GALLERY_CAPTIONS_LEGACYFILENAME) {
			array_splice($l, $i, 1);
		}
	}
	sort($l);
	return $l;
}

/**
 * Reads the captions from the given gallery directory.
 *
 * @param string $galleryDir
 *        	the gallery dir, e.g. 'images/NameOfTheGallery'
 * @return array the gallery captions as associative array { filename => caption }
 */
function gallery_read_captions($galleryDir) {
	$captions = array();

	$captionsFileContent = null;
	$galleryDirPathAbs = ABS_PATH . FP_CONTENT . $galleryDir . '/';
	// read captions.conf from gallery dir
	if (file_exists($galleryDirPathAbs . GALLERY_CAPTIONS_FILENAME)) {
		$captionsFileContent = file($galleryDirPathAbs . GALLERY_CAPTIONS_FILENAME);
	} //
	  // legacy mode: if captions.conf is not available, check for texte.conf
	elseif (file_exists($galleryDirPathAbs . GALLERY_CAPTIONS_LEGACYFILENAME)) {
		$captionsFileContent = file($galleryDirPathAbs . GALLERY_CAPTIONS_LEGACYFILENAME);
	} //
	  // no caption file available
	else {
		return array();
	}

	// read captions file line by line
	foreach ($captionsFileContent as $currentline) {
		// image file name is before of the '=' character, ...
		$image = trim(substr($currentline, 0, strpos($currentline, '=')));
		// ... the caption after.
		$caption = trim(substr($currentline, (strpos($currentline, '=') + 1)));
		// $captions [$image] = htmlentities($descript);
		$captions [$image] = $caption;
	}
	return $captions;
}

/**
 * Stores the given captions for the given gallery
 *
 * @param string $galleryName
 *        	the gallery dir, e.g. 'NameOfTheGallery'
 * @param array $captions
 *        	the gallery captions as associative array { filename => caption }
 * @return boolean <code>true</code> if captions were written successfully; <code>false</code> otherwise
 */
function gallery_write_captions($galleryName, $captions) {
	$gallerydirPath = IMAGES_DIR . $galleryName;
	if (!file_exists($gallerydirPath)) {
		return false;
	}
	$captionfilePath = IMAGES_DIR . $galleryName . DIRECTORY_SEPARATOR . GALLERY_CAPTIONS_FILENAME;
	$captionfileHandle = fopen($captionfilePath, 'w');

	$filecontent = '';
	foreach ($captions as $filename => $caption) {
		$filecontent .= $filename . ' = ' . $caption . PHP_EOL;
	}

	fwrite($captionfileHandle, $filecontent);
	fclose($captionfileHandle);

	// Updating from legacy versions: If legacy captions fill still exists, delete if
	$legacyCaptionfilePath = IMAGES_DIR . $galleryName . DIRECTORY_SEPARATOR . GALLERY_CAPTIONS_LEGACYFILENAME;
	if (file_exists($legacyCaptionfilePath)) {
		unlink($legacyCaptionfilePath);
	}
	return true;
}
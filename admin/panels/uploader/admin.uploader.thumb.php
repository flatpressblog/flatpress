<?php
/*
 *
 * useful snippet http://ontosys.com/php/cache.html, but looks like it doesn't in this case
 *
 * $if_modified_since = preg_replace('/;.*$/', '', @$_SERVER['HTTP_IF_MODIFIED_SINCE']);
 *
 * $mtime = filemtime($fpath);
 * $gmdate_mod = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
 *
 * if ($if_modified_since == $gmdate_mod) {
 *		header("HTTP/1.0 304 Not Modified");
 *		exit;
 * }
 *
 * header("Last-Modified: $gmdate_mod");
 *
 */

// we support jpeg, png, gif, and webp
function thumb_send($fpath) {

	$MAX = 100;

	// we support only jpeg's, png's and gif's

	$infos = getimagesize($fpath);
	if (!$infos) return;

	list($w, $h) = $infos;

	$isWebP = (isset($infos [2]) && $infos [2] === 18);

	if ($w <= $MAX && $h <= $MAX) {
		switch ($infos [2]) {
			case 1 : header('Content-Type: image/gif'); break;
			case 2 : header('Content-Type: image/jpeg'); break;
			case 3 : header('Content-Type: image/png'); break;
			case 18: header('Content-Type: image/webp'); break;
			default: return;
		}

		readfile($fpath);
		return;
	}

	switch($infos [2]) {
		case 1 : $image = imagecreatefromgif($fpath); break;
		case 2 : $image = imagecreatefromjpeg ($fpath); break;
		case 3 : $image = imagecreatefrompng($fpath); break;
		case 18:
			if (function_exists('imagecreatefromwebp')) {
				$image = imagecreatefromwebp($fpath);
			} else {
				return;
			}
			break;
		default:
			return;
	}

	if (!$image) return;

	if ($w > $h) {

		$ratio = $w / $h;
		$new_width = $MAX;
		$new_height = (int)($MAX/$ratio);

	} else {
		$ratio = $h / $w;
		$new_height = $MAX;
		$new_width = (int)($MAX/$ratio);
	}

	$scaled = imagecreatetruecolor($new_width, $new_height);

	if (in_array($infos [2], [3, 18])) {
		imagealphablending($scaled, false);
		imagesavealpha($scaled, true);
		$transparent = imagecolorallocatealpha($scaled, 0, 0, 0, 127);
		imagefilledrectangle($scaled, 0, 0, $new_width, $new_height, $transparent);
	}

	imagecopyresampled($scaled, $image, 0, 0, 0, 0, $new_width, $new_height, $infos [0], $infos [1]);

	if ($infos [2] === 18 && function_exists('imagewebp')) {
		header('Content-Type: image/webp');
		imagewebp($scaled);
	} else {
		header('Content-Type: image/jpeg');
		imagejpeg($scaled, null, 90);
	}

	if (!is_php85_plus()) {
		imagedestroy($scaled);
		imagedestroy($image);
	}
}

if (isset($_GET ['f'])) {

	$f = ABS_PATH . IMAGES_DIR . $_GET ['f'];
	if (strpos ($f, '..') !== false) {
		return;
	}

	if (file_exists($f)) {
		thumb_send($f);
	}
}

exit();
?>

<?php

/**
 * uploader control panel
 *
 * Type:
 * Name:
 * Date:
 * Purpose:
 * Input:
 * Change-Date: 24.11.2024, by FKM
 *
 * @author NoWhereMan <real_nowhereman at users dot sf dot com>
 *
 */
class admin_uploader extends AdminPanel {
	var $panelname = 'uploader';

	var $actions = array(
		'default' => true
	);
}

class admin_uploader_default extends AdminPanelAction {
	var $events = array(
		'upload'
	);

	function main() {
		if ($f = sess_remove('admin_uploader_files')) {
			$this->smarty->assign('uploaded_files', $f);
		}
	}

	function sanitize_filename($filename) {
		// Define allowed characters: letters, numbers, hyphens, underscores, dots, and language-specific characters
		$allowed_chars = '/[^a-zA-Z0-9._\-\p{L}\p{M}]/u';

		// Remove all disallowed characters
		$filename = preg_replace($allowed_chars, '', $filename);

		// Ensure no trailing dots, underscores, or hyphens remain
		$filename = rtrim($filename, "._-");

		return $filename;
	}

	/**
	 * This function protects against possible attacks by only using the base name of the file name.
	 */
	function prevent_directory_traversal($filename) {
		// Prevents directory traversal through cleanup
		return basename($filename);
	}

	function onupload() {
		global $fp_config;

		// FPPROTECT: Load configuration
		$pluginConfig = plugin_getoptions('fpprotect');
		$allowImageMetadata = isset($pluginConfig ['allowImageMetadata']) ? (bool)$pluginConfig ['allowImageMetadata'] : false;

		$removeMetadata = !$allowImageMetadata;

		$success = false;

		/**
		 * first check if user is logged in
		 * to prevent remote admin.uploader.php script execution
		 *
		 * By testing the admin/main.php made the redirect job
		 * By direct URL call PHP throw a visible error -> AdminPanel class not found!
		 */
		if (!user_loggedin()) {
			utils_redirect('login.php');
			die();
		}

		if (!file_exists(IMAGES_DIR)) {
			fs_mkdir(IMAGES_DIR);
		}

		if (!file_exists(ATTACHS_DIR)) {
			fs_mkdir(ATTACHS_DIR);
		}

		/**
		 * Blacklist entries from OWASP and
		 * https://stackoverflow.com/questions/4166762/php-image-upload-security-check-list
		 */
		$blacklist_extensions = array(
			'htaccess',
			'pht',
			'phtm',
			'phtml',
			'ph2',
			'ph3',
			'ph4',
			'ph5',
			'ph6',
			'ph7',
			'ph8',
			'php',
			'php2',
			'php3',
			'php4',
			'php5',
			'php6',
			'php7',
			'php8',
			'phps',
			'cgi',
			'exe',
			'pl',
			'asp',
			'aspx',
			'shtml',
			'shtm',
			'fcgi',
			'fpl',
			'jsp',
			'htm',
			'html',
			'wml',
			'svg',
			'xml',
			'md',
			'pages',
			'xsig'
		);

		$imgs = array(
			'.jpg',
			'.gif',
			'.png',
			'.jpeg',
			'.webp'
		);

		// intentionally
		// I've not put BMPs

		$uploaded_files = array();
		$this->smarty->assign('uploaded_files', $uploaded_files);

		foreach ($_FILES ['upload'] ['error'] as $key => $error) {
			if ($error != UPLOAD_ERR_OK) {
				continue;
			}

			$tmp_name = $_FILES ['upload'] ['tmp_name'] [$key];
			$name = $_FILES ['upload'] ['name'] [$key];

			// Forbids the upload of hidden files (e.g. .test.png)
			if (strpos($name, '.') === 0) {
				$this->smarty->assign('success', -1);
				sess_add('admin_uploader_files', $uploaded_files);
				return -1;
			}

			/**
			 * second check extension list
			 * https://stackoverflow.com/questions/4166762/php-image-upload-security-check-list
			 */

			// Prevents directory traversal
			$name = $this->prevent_directory_traversal($name);
			$uploadfilename = strtolower($name);
			$deeptest = explode('.', $uploadfilename);
			$extcount = count($deeptest);

			$isForbidden = false;

			// Validation of file extensions
			if ($extcount == 1) {
				/**
				 * none extension like .jpg or something else
				 *
				 * possible filename = simple-file-without-extension - linux like ok
				 */
				$isForbidden = false;
			} elseif ($extcount == 2) {
				/**
				 * Only one possible extension
				 *
				 * possible filename = 1.jpg
				 * possible filename = admin.uploader.php
				 * possible filename = .htaccess
				 * and so on...
				 */
				$check_ext1 = trim($deeptest [1], "\x00..\x1F");
				$isForbidden = in_array($check_ext1, $blacklist_extensions);
			} elseif ($extcount > 2) {
				/**
				 * Chekc only the last two possible extensions
				 *
				 * Hint: OWASP - Unrestricted File Upload
				 *
				 * In Apache, a php file might be executed using the
				 * double extension technique such as "file.php.jpg"
				 * when ".jpg" is allowed.
				 *
				 * possible filename = 1.PhP.jpg
				 * possible filename = admin.uploader.php.JPg
				 * and so on...
				 */
				$check_ext1 = trim($deeptest [$extcount - 1], "\x00..\x1F");
				$check_ext2 = trim($deeptest [$extcount - 2], "\x00..\x1F");
				$isForbidden = in_array($check_ext1, $blacklist_extensions) || in_array($check_ext2, $blacklist_extensions);
			}

			if ($isForbidden) {
				$this->smarty->assign('success', $success ? 1 : -1);
				sess_add('admin_uploader_files', $uploaded_files);
				return -1;
			}

			// MIME type check
			if (!function_exists('finfo_open')) {
				return -1;
			}

			/**
			 * third check extension
			 * if someone upload a .php file as .gif, .jpg or .txt
			 * if someone upload a .html file as .gif, .jpg or .txt
			 */
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $tmp_name);
			finfo_close($finfo);

			/**
			 * If one blacklisted extension found then
			 * return with -1 = An error occurred while trying to upload.
			 */
			if (in_array($mime, array('text/x-php', 'text/html'))) {
				$this->smarty->assign('success', $success ? 1 : -1);
				sess_add('admin_uploader_files', $uploaded_files);
				return -1;
			}

			$ext = strtolower(strrchr($name, '.'));

			if (in_array($ext, $imgs)) {
				$dir = IMAGES_DIR;
			} else {
				$dir = ATTACHS_DIR;
			}

			// Sanitizing the file name
			$name = $this->sanitize_filename(substr($name, 0, -strlen($ext))) . $ext;

			$target = $dir . '/' . $name;
			@umask(022);
			if (move_uploaded_file($tmp_name, $target)) {
				// Remove metadata from images if false or not set in FPPROTECT
				if ($removeMetadata && in_array($ext, $imgs)) {
					$this->remove_image_metadata($target, $ext);
				}
				@chmod($target, FILE_PERMISSIONS);
				$uploaded_files [] = $name;
				$success = true;
			} else {
				$success = false;
			}

			// One failure will make $success == false :)
			$success &= $success;
		}

		if ($uploaded_files) {
			$this->smarty->assign('success', $success ? 1 : -1);
			sess_add('admin_uploader_files', $uploaded_files);
		}

		return 1;
	}

	function remove_image_metadata($filepath, $ext) {
		switch ($ext) {
			case '.jpg':
			case '.jpeg':
				$image = imagecreatefromjpeg($filepath);
				$saveFunc = 'imagejpeg';
				break;
			case '.png':
				$image = imagecreatefrompng($filepath);
				$saveFunc = 'imagepng';
				break;
			case '.gif':
				$image = imagecreatefromgif($filepath);
				$saveFunc = 'imagegif';
				break;
			case '.webp':
				if (function_exists('imagecreatefromwebp')) {
					$image = imagecreatefromwebp($filepath);
					$saveFunc = 'imagewebp';
				} else {
					return false;
				}
				break;
 			default:
				return false;
		}

		if ($image) {
			$saveFunc($image, $filepath);
			imagedestroy($image);
			return true;
		}
		return false;
	}
}
?>

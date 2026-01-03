<?php

/**
 * uploader control panel
 *
 * Type:
 * Name:
 * Date:
 * Purpose:
 * Input:
 * Change-Date: 03.01.2026, by FKM
 *
 * @author NoWhereMan <real_nowhereman at users dot sf dot com>
 *
 */
function admin_uploader_head() {
	global $fp_config;
	$blogbase = BLOG_BASEURL;
	$random_hex = RANDOM_HEX;
	$css = utils_asset_ver($blogbase . 'admin/panels/uploader/uploader.css', SYSTEM_VER);
	$js = utils_asset_ver($blogbase . 'admin/panels/uploader/uploader.js', SYSTEM_VER);

	echo '
		<!-- BOF Admin Multi file uploader CSS -->
		<link rel="stylesheet" type="text/css" href="' . $css . '">
		<script nonce="' . $random_hex . '" src="' . $js . '" defer></script>
		<!-- BOF Admin Multi file uploader CSS -->
	';
}

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
		add_action('admin_head', 'admin_uploader_head');

		if ($f = sess_remove('admin_uploader_files')) {
			$this->smarty->assign('uploaded_files', $f);
		}
		if ($e = sess_remove('admin_uploader_errors')) {
			$this->smarty->assign('upload_errors', $e);
		}

		if ($w = sess_remove('admin_uploader_warnings')) {
			$this->smarty->assign('upload_warnings', $w);
		}

		// Determine PHP upload limits for client-side hints
		$upload_limits = array();

		$max_files = (int) @ini_get('max_file_uploads');
		if ($max_files > 0) {
			$upload_limits ['max_files'] = $max_files;
		}

		$post_max = $this->parse_ini_bytes(@ini_get('post_max_size'));
		$upload_max = $this->parse_ini_bytes(@ini_get('upload_max_filesize'));

		if ($post_max > 0 || $upload_max > 0) {
			$max_bytes = 0;

			if ($post_max > 0 && $upload_max > 0) {
				$max_bytes = (int) min($post_max, $upload_max * ($max_files > 0 ? $max_files : 1));
			} elseif ($post_max > 0) {
				$max_bytes = (int) $post_max;
			} else {
				$max_bytes = (int) $upload_max;
			}

			// Keep a small safety margin to avoid hitting the hard limit exactly
			if ($max_bytes > 0) {
				$upload_limits ['max_bytes'] = (int) floor($max_bytes * 0.9);
				$upload_limits ['max_bytes_readable'] = $this->format_bytes($upload_limits ['max_bytes']);
			}
		}

		if (!empty($upload_limits)) {
			$this->smarty->assign('upload_limits', $upload_limits);
		}

	}

	function parse_ini_bytes($val) {
		if (!is_string($val) || $val === '') {
			return 0;
		}
		$val = trim($val);
		$last = strtolower(substr($val, -1));
		$num = (float) $val;

		switch ($last) {
			case 'g':
				$num *= 1024;
				// no break
			case 'm':
				$num *= 1024;
				// no break
			case 'k':
				$num *= 1024;
		}

		return (int) $num;
	}

	function format_bytes($bytes) {
		$bytes = (int) $bytes;
		if ($bytes <= 0) {
			return '0 B';
		}

		$units = array('B', 'KiB', 'MiB', 'GiB', 'TiB');
		$idx = 0;

		while ($bytes >= 1024 && $idx < count($units) - 1) {
			$bytes /= 1024;
			$idx++;
		}

		if ($idx === 0) {
			return sprintf('%d %s', $bytes, $units [$idx]);
		}

		return sprintf('%.1f %s', $bytes, $units [$idx]);
	}

	function sanitize_filename($filename) {
		// Keep names portable across filesystems/hosts: remove accents and strip unsafe characters.
		$filename = (string)$filename;
		$filename = trim($filename);
		// Remove any path components (some browsers send "C:\\fakepath\\...")
		$filename = basename(str_replace('\\', '/', $filename));
		// Decode entities (e.g. &auml;)
		$filename = html_entity_decode($filename, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		// Normalize whitespace to underscore
		$filename = preg_replace('/\s+/u', '_', $filename);
		// Remove accents if possible
		if (function_exists('remove_accents')) {
			// fp-includes/core/core.wp-formatting.php
			$filename = remove_accents($filename);
		} else {
			$filename = strtr($filename, array(
				'ä' => 'a', 'Ä' => 'A',
				'ö' => 'o', 'Ö' => 'O',
				'ü' => 'u', 'Ü' => 'U',
				'ß' => 'ss'
			));
		}
		// Keep only ASCII safe characters
		$filename = preg_replace('/[^A-Za-z0-9._-]/', '', $filename);
		// Collapse duplicate underscores
		$filename = preg_replace('/_{2,}/', '_', $filename);
		// Avoid leading/trailing separators and dots
		$filename = trim($filename, " ._-");
		if ($filename === '') {
			$filename = 'file';
		}

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
		global $fp_config, $lang;

		// FPPROTECT: Load configuration
		$pluginConfig = plugin_getoptions('fpprotect');
		if (!is_array($pluginConfig)) {
			$pluginConfig = array();
		}
		// FPPROTECT: Allow the original metadata in the images
		$allowImageMetadata = !empty($pluginConfig ['allowImageMetadata']);

		$removeMetadata = !$allowImageMetadata;

		// FPPROTECT: Allow uploading SVG files
		$allowSvgUpload = !empty($pluginConfig ['allowSvgUpload']);

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
			'asp',
			'aspx',
			'bash',
			'bat',
			'cgi',
			'cmd',
			'com',
			'fcgi',
			'fpl',
			'htaccess',
			'htm',
			'html',
			'jsp',
			'md',
			'pages',
			'pht',
			'phar',
			'phtm',
			'phtml',
			'ph2',
			'ph3',
			'ph4',
			'ph5',
			'ph6',
			'ph7',
			'ph8',
			'ph9',
			'php',
			'php2',
			'php3',
			'php4',
			'php5',
			'php6',
			'php7',
			'php8',
			'php9',
			'phps',
			'pl',
			'py',
			'shtm',
			'shtml',
			'sh',
			'so',
			'svg',
			'svgz',
			'wml',
			'xml',
			'xsig'
		);

		if ($allowSvgUpload) {
			// Allow explicit SVG uploads when enabled in FlatPress Protect
			$blacklist_extensions = array_values(array_diff($blacklist_extensions, array('svg', 'svgz')));
		}

		$imgs = array(
			'.jpg',
			'.gif',
			'.png',
			'.jpeg',
			'.webp',
			'.svg',
			'.svgz'
		);

		// intentionally
		// I've not put BMPs

		$uploaded_files = array();
		$upload_errors = array();
		$upload_warnings = array();
		$this->smarty->assign('uploaded_files', $uploaded_files);

		// Server-side detection when PHP has rejected the upload.
		$filesFieldPresent = (isset($_FILES ['upload']) && is_array($_FILES ['upload']) && isset($_FILES ['upload'] ['error']) && is_array($_FILES ['upload'] ['error']));

		$hasAnyFile = false;
		if ($filesFieldPresent) {
			foreach ($_FILES ['upload'] ['error'] as $err) {
				if ($err !== UPLOAD_ERR_NO_FILE) {
					$hasAnyFile = true;
					break;
				}
			}
		}

		if (!$filesFieldPresent || !$hasAnyFile) {
			$contentLength = isset($_SERVER ['CONTENT_LENGTH']) ? (int) $_SERVER ['CONTENT_LENGTH'] : 0;
			$postMaxBytes = 0;
			$postMax = @ini_get('post_max_size');

			if (is_string($postMax) && $postMax !== '') {
				$val = trim($postMax);
				$last = strtolower(substr($val, -1));
				$num = (float) $val;

				switch ($last) {
					case 'g':
						$num *= 1024;
						// no break
					case 'm':
						$num *= 1024;
						// no break
					case 'k':
						$num *= 1024;
				}

				$postMaxBytes = (int) $num;
			}

			// Get panel messages (msgs) for status codes
			$panelMsgs = array();
			if (isset($lang ['admin'] ['uploader'] ['default'] ['msgs']) && is_array($lang ['admin'] ['uploader'] ['default'] ['msgs'])) {
				$panelMsgs = $lang ['admin'] ['uploader'] ['default'] ['msgs'];
			}

			$successCode = -1;
			$msg = null;

			if ($contentLength > 0 && $postMaxBytes > 0 && $contentLength > $postMaxBytes) {
				// Upload was rejected by PHP due to post_max_size
				$successCode = -2;
				if (isset($panelMsgs [$successCode])) {
					$msg = sprintf($panelMsgs [$successCode], $postMax);
				}
			} elseif ($contentLength > 0 && !$filesFieldPresent) {
				// Upload data is present, but $_FILES[‘upload’] is missing completely
				$successCode = -3;
				if (isset($panelMsgs [$successCode])) {
					$msg = $panelMsgs [$successCode];
				}
			} else {
				// No files selected/received
				$successCode = -4;
				if (isset($panelMsgs [$successCode])) {
					$msg = $panelMsgs [$successCode];
				}
			}

			if ($msg !== null) {
				$upload_errors [] = $msg;
			}

			$this->smarty->assign('upload_errors', $upload_errors);
			$this->smarty->assign('success', $successCode);
			sess_add('admin_uploader_errors', $upload_errors);
			return 1;
		}

		foreach ($_FILES ['upload'] ['error'] as $key => $error) {
			if ($error != UPLOAD_ERR_OK) {
				continue;
			}

			$tmp_name = $_FILES ['upload'] ['tmp_name'] [$key];
			$name = $_FILES ['upload'] ['name'] [$key];

			// Forbids the upload of hidden files (e.g. .test.png)
			if (strpos($name, '.') === 0) {
				$upload_errors [] = (string)$name . ' (hidden file)';
			continue;
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
				$upload_errors [] = (string)$name . ' (forbidden extension)';
				continue;
			}

			if (!function_exists('finfo_open') && !function_exists('mime_content_type')) {
				$upload_errors [] = (string)$name . ' (cannot detect MIME type)';
				continue;
			}

			/**
			 * third check extension
			 * if someone upload a .php file as .gif, .jpg or .txt
			 * if someone upload a .html file as .gif, .jpg or .txt
			 */
			if (function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime = $finfo ? @finfo_file($finfo, $tmp_name) : false;
				if ($finfo) {
					if (!is_php85_plus()) {
						@finfo_close($finfo);
					}
				}
			} else {
				$mime = @mime_content_type($tmp_name);
			}
			if ($mime === false || $mime === null) {
				$upload_errors [] = (string)$name . ' (MIME detection failed)';
				continue;
			}

			/**
			 * Prohibited MIME types
			 */
			if (in_array($mime, array('text/x-php', 'text/html'), true)) {
				$upload_errors [] = (string)$name . ' (' . $mime . ')';
				continue;
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
					$metaRemoved = $this->remove_image_metadata($target, $ext);
					if (!$metaRemoved) {
						$upload_warnings[] = (string)$name;
					}
				}
				@chmod($target, FILE_PERMISSIONS);
				$uploaded_files [] = $name;
				$success = true;
			} else {
				$success = false;
				$upload_errors [] = (string)$name . ' (move failed)';
			}
		}

		// Finalize: report successes and per-file errors
		$anySuccess = !empty($uploaded_files);
		$hasErrors = !empty($upload_errors);
		$hasWarnings = !empty($upload_warnings);

		// Transfer to template
		$this->smarty->assign('uploaded_files', $uploaded_files);
		if ($hasErrors) {
			$this->smarty->assign('upload_errors', $upload_errors);
		}
		if ($hasWarnings) {
			$this->smarty->assign('upload_warnings', $upload_warnings);
		}
		$this->smarty->assign('success', $anySuccess ? 1 : -1);

		// Persistence via redirect/reload
		sess_add('admin_uploader_files', $uploaded_files);
		if ($hasErrors) {
			sess_add('admin_uploader_errors', $upload_errors);
		}
		if ($hasWarnings) {
			sess_add('admin_uploader_warnings', $upload_warnings);
		}

		return 1;
	}

	function get_memory_limit_bytes() {
		$limit = @ini_get('memory_limit');
		if (!is_string($limit) || $limit === '' || $limit === '-1') {
			// Unlimited or unknown
			return 0;
		}
		return (int) $this->parse_ini_bytes($limit);
	}

	/**
	 * Try to predict whether loading+re-encoding an image via GD will fit into memory.
	 * This prevents fatal "Allowed memory size exhausted" errors when stripping metadata.
	 */
	function can_process_image_in_memory($filepath) {
		$limitBytes = $this->get_memory_limit_bytes();
		if ($limitBytes === null) {
			// unlimited or cannot determine
			return true;
		}

		$usage = function_exists('memory_get_usage') ? (int) @memory_get_usage(true) : 0;

		// Safety margin: leave some headroom for PHP, Smarty, and temporary buffers
		$available = (float) ($limitBytes - $usage - (8 * 1024 * 1024));
		if ($available <= 0) {
			return false;
		}

		$info = @getimagesize($filepath);
		if ($info === false || !isset($info [0], $info [1])) {
			return false;
		}

		$w = (int) $info [0];
		$h = (int) $info [1];

		// GD typically expands to 4 bytes per pixel (truecolor) plus overhead/copies.
		$pixels = (float) $w * (float) $h;
		$bytesPerPixel = 4.0;

		// Factor 2.5 = original + working copy + overhead, plus extra buffer.
		$needed = ($pixels * $bytesPerPixel * 2.5) + (5 * 1024 * 1024);

		return ($needed > 0 && $needed < $available);
	}

	function remove_image_metadata($filepath, $ext) {
		// Prefer Imagick if available (often more reliable for metadata stripping).
		if (class_exists('Imagick')) {
			try {
				$img = new Imagick();
				$img->readImage($filepath);
				$img->stripImage();
				$img->writeImage($filepath);
				$img->clear();
				$img->destroy();
				return true;
			} catch (Throwable $e) {
				// Fallback to GD below
			}
		}

		// Guard against fatal OOM errors in GD.
		if (!$this->can_process_image_in_memory($filepath)) {
			return false;
		}

		switch ($ext) {
			case '.jpg':
			case '.jpeg':
				if (!function_exists('imagecreatefromjpeg')) {
					return false;
				}
				$image = @imagecreatefromjpeg($filepath);
				$saveFunc = 'imagejpeg';
				break;
			case '.png':
				if (!function_exists('imagecreatefrompng')) {
					return false;
				}
				$image = @imagecreatefrompng($filepath);
				$saveFunc = 'imagepng';
				break;
			case '.gif':
				if (!function_exists('imagecreatefromgif')) {
					return false;
				}
				$image = @imagecreatefromgif($filepath);
				$saveFunc = 'imagegif';
				break;
			case '.webp':
				if (function_exists('imagecreatefromwebp')) {
					$image = @imagecreatefromwebp($filepath);
					$saveFunc = 'imagewebp';
				} else {
					return false;
				}
				break;
			default:
				return false;
		}

		if ($image) {
			// Preserve transparency for formats that support it.
			if ($ext === '.png' && function_exists('imagesavealpha')) {
				@imagesavealpha($image, true);
			}
			if ($ext === '.webp' && function_exists('imagesavealpha')) {
				@imagesavealpha($image, true);
			}

			$ok = @$saveFunc($image, $filepath);

			if (!is_php85_plus()) {
				@imagedestroy($image);
			}

			return (bool) $ok;
		}

		return false;
	}
}
?>

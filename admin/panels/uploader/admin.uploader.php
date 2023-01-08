<?php

/**
 * uploader control panel
 *
 * Type:
 * Name:
 * Date:
 * Purpose:
 * Input:
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

	function onupload() {
		$success = false;

		/*
		 * first check if user is logged in
		 * to prevent remote admin.uploader.php script execution
		 *
		 * By testing the admin/main.php made the redirect job
		 * By direct URL call PHP throw a visible error -> AdminPanel class not found!
		 *
		 * 2019-11-23 - laborix
		 */
		if (!user_loggedin()) {
			utils_redirect("login.php");
			die();
		}

		if (!file_exists(IMAGES_DIR))
			fs_mkdir(IMAGES_DIR);

		if (!file_exists(ATTACHS_DIR))
			fs_mkdir(ATTACHS_DIR);
		/*
		 * Blacklist entries from OWASP and
		 * https://stackoverflow.com/questions/4166762/php-image-upload-security-check-list
		 *
		 * 2019-11-23 - laborix
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
			'pages'
		);

		$imgs = array(
			'.jpg',
			'.gif',
			'.png',
			'.jpeg'
		);

		// intentionally
		// I've not put BMPs

		$uploaded_files = array();
		$this->smarty->assign('uploaded_files', $uploaded_files);

		foreach ($_FILES ["upload"] ["error"] as $key => $error) {

			// Upload went wrong -> jump to the next file
			if ($error != UPLOAD_ERR_OK) {
				continue;
			}

			$tmp_name = $_FILES ["upload"] ["tmp_name"] [$key];
			$name = $_FILES ["upload"] ["name"] [$key];

			$dir = ATTACHS_DIR;

			/*
			 * second check extension list
			 * https://stackoverflow.com/questions/4166762/php-image-upload-security-check-list
			 *
			 * 2019-11-24 - laborix
			 */

			$uploadfilename = strtolower($name);

			$isForbidden = false;
			$deeptest = array();
			$extcount = 0;
			$deeptest = explode('.', $uploadfilename);
			$extcount = count($deeptest);

			if ($extcount == 1) {
				/*
				 * none extension like .jpg or something else
				 *
				 * possible filename = simple-file-without-extension - linux like ok
				 */
				$isForbidden = false;
			} elseif ($extcount == 2) {
				/*
				 * Only one possible extension
				 *
				 * possible filename = 1.jpg
				 * possible filename = admin.uploader.php
				 * possible filename = .htaccess
				 * and so on...
				 */
				$check_ext1 = "";
				$check_ext1 = trim($deeptest [1], "\x00..\x1F");
				if (in_array($check_ext1, $blacklist_extensions)) {
					$isForbidden = true;
				} else {
					$isForbidden = false;
				}
			} elseif ($extcount > 2) {
				/*
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
				$check_ext1 = "";
				$check_ext2 = "";
				$check_ext1 = trim($deeptest [$extcount - 1], "\x00..\x1F");
				if (in_array($check_ext1, $blacklist_extensions)) {
					$isForbidden = true;
				} else {
					$isForbidden = false;
				}
				/* Test only if first extension check are not in the blacklist */
				if (!$isForbidden) {
					$check_ext2 = trim($deeptest [$extcount - 2], "\x00..\x1F");
					if (in_array($check_ext2, $blacklist_extensions)) {
						$isForbidden = true;
					} else {
						$isForbidden = false;
					}
				}
			}
			/*
			 * If one blacklisted extension found then
			 * return with -1 = An error occurred while trying to upload.
			 */
			if ($isForbidden) {
				$this->smarty->assign('success', $success ? 1 : -1);
				sess_add('admin_uploader_files', $uploaded_files);
				return -1;
			}

			/*
			 * third check extension
			 * if someone upload a .php file as .gif, .jpg or .txt
			 * if someone upload a .html file as .gif, .jpg or .txt
			 *
			 * 2019-11-24 - laborix
			 */

			if (version_compare(PHP_VERSION, '5.3.0') < 0)
				return -1;
			if (!function_exists('finfo_open'))
				return -1;

			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $tmp_name);
			finfo_close($finfo);

			if (($mime == "text/x-php") || ($mime == "text/html")) {
				$this->smarty->assign('success', $success ? 1 : -1);
				sess_add('admin_uploader_files', $uploaded_files);
				return -1;
			}

			$ext = strtolower(strrchr($name, '.'));

			if (in_array($ext, $imgs)) {
				$dir = IMAGES_DIR;
			}

			$name = sanitize_title(substr($name, 0, -strlen($ext))) . $ext;

			$target = "$dir/$name";
			@umask(022);
			$success = move_uploaded_file($tmp_name, $target);
			@chmod($target, 0766);

			$uploaded_files [] = $name;

			// one failure will make $success == false :)
			$success &= $success;
		}

		if ($uploaded_files) {
			$this->smarty->assign('success', $success ? 1 : -1);
			sess_add('admin_uploader_files', $uploaded_files);
		}

		return 1;
	}

}

?>

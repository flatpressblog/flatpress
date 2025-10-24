<?php
/**
 * Admin panel action for managing media files and galleries.
 *
 * @package FlatPress
 */
class admin_uploader_mediamanager extends AdminPanelAction {

	var $finfo;

	var $conf;

	var $langres = 'plugin:mediamanager';

	 /**
	 * Comparison function to sort files by type and name.
	 *
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	function cmpfiles($a, $b) {
		$typeOrder = ['gallery' => 0, 'attachs' => 1, 'images' => 2];

		$typeA = isset($a ['type']) ? $a ['type'] : 'images';
		$typeB = isset($b ['type']) ? $b ['type'] : 'images';

		$orderA = $typeOrder [$typeA] ?? 3;
		$orderB = $typeOrder [$typeB] ?? 3;

		if ($orderA !== $orderB) {
			return $orderA - $orderB;
		}

		return strnatcmp($a ['name'], $b ['name']);
	}

	/**
	 * Format a byte value into a human-readable string.
	 *
	 * @param int $bytes
	 * @param int $precision
	 * @return string
	 */
	function formatBytes($bytes, $precision = 2) {
		$units = array(
			'B',
			'KB',
			'MB',
			'GB',
			'TB'
		);

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		$bytes /= pow(1024, $pow);

		return round($bytes, $precision) . ' ' . $units [$pow];
	}

	 /**
	 * Get formatted file information including size and modified time.
	 *
	 * @param string $filepath Absolute filesystem path.
	 * @return array{
	 *   name:string,
	 *   relpath:string,
	 *   size:string,
	 *   mtime:string,
	 *   usecount:int|null,
	 *   gallery:string|null,
	 *   use_via_gallery:bool
	 * }|null
	 */
	function getFileInfo($filepath) {
		global $fp_config;

		// Prevents the capture of .dlctr (Igor Kromins DownloadCounter) files
			if (pathinfo($filepath, PATHINFO_EXTENSION) === 'dlctr') {
				return null;
		}

		$filepath_stat = @stat($filepath);
		$file_size = 0;
		$file_mtime = 0;
		if (is_array($filepath_stat)) {
			if (isset($filepath_stat['size'])) {
				$file_size = (int)$filepath_stat ['size'];
			}
			if (isset($filepath_stat ['mtime'])) {
				$file_mtime = (int)$filepath_stat ['mtime'];
			}
		}
		if ($file_size === 0 && is_file($filepath)) {
			$fs = @filesize($filepath);
			if ($fs !== false) {
				$file_size = (int)$fs;
			}
		}
		if ($file_mtime === 0 && is_file($filepath)) {
			$fm = @filemtime($filepath);
			if ($fm !== false) {
				$file_mtime = (int)$fm;
			}
		}

		$rel = (strpos($filepath, ABS_PATH . IMAGES_DIR) === 0) ? str_replace("\\", "/", ltrim(substr($filepath, strlen(ABS_PATH . IMAGES_DIR)), "/")) : basename($filepath);
		$info = array(
			"name" => basename($filepath),
			"relpath" => $rel,
			"size" => $this->formatBytes($file_size),
			"mtime" => date_strformat($fp_config ['locale'] ['dateformatshort'], $file_mtime)
		);

		// Read via relative path, fallback to old key (base name) for backward compatibility
		if (isset($this->conf ['usecount'] [$info ['relpath']])) {
			$info ['usecount'] = $this->conf ['usecount'] [$info ['relpath']];
		} elseif (isset($this->conf ['usecount'] [basename($filepath)])) {
			$info ['usecount'] = $this->conf ['usecount'] [basename($filepath)];
		} else {
			$info ['usecount'] = null;
		}

		// Gallery name and usage flag
		$info ['gallery'] = (strpos($rel, '/')!== false) ? substr($rel, 0 ,strpos($rel, '/')) : null;
		if (isset($this->conf ['useflags'] [$info ['relpath']])) {
			$flags = $this->conf ['useflags'] [$info ['relpath']];
			$info ['use_via_gallery'] = is_array($flags) && !empty($flags ['gallery']);
		} else {
			$info ['use_via_gallery'] = false;
		}

		return $info;
	}

	/**
	 * Assigns template resource for the admin panel.
	 *
	 * @return void
	 */
	function setup() {
		$this->smarty->assign('admin_resource', "plugin:mediamanager/admin.plugin.mediamanager.files");
	}

	/**
	 * Recursively delete a folder and its contents.
	 *
	 * @param string $folder
	 * @param string $mmbaseurl
	 * @return bool
	 */
	function deleteFolder($folder, $mmbaseurl) {
		if (!file_exists($folder)) {
			return false;
		}
		if ($dir = opendir($folder)) {
			while (false !== ($file = readdir($dir))) {
				if (!fs_is_directorycomponent($file)) {
					if (is_dir($folder . "/" . $file)) {
						$this->deleteFolder($folder . "/" . $file, $mmbaseurl);
					} else {
						if (!unlink($folder . "/" . $file)) {
							closedir($dir);
							return false;
						}
					}
				}
			}
			closedir($dir);
		}
		return rmdir($folder);
	}

	/**
	 * Handles item-specific actions like file deletion.
	 *
	 * @param string $folder
	 * @param string $mmbaseurl
	 * @return bool
	 */
	function doItemActions($folder, $mmbaseurl) {

		/* delete file */
		if (isset($_GET ['deletefile'])) {
			// at first: check if nonce was given correctly
			check_admin_referer('mediamanager_deletefile');

			// now get the file to be deleted
			list ($type, $name) = explode("-", $_GET ['deletefile'], 2);
			// prevent path traversal: remove ".." and "/" resp. "\"
			$name = preg_replace('(\.\.|\/|\\\\)', '', $name);
			switch ($type) {
				case 'attachs':
					$type = ABS_PATH . ATTACHS_DIR;
					break;
				case 'images':
					$type = ABS_PATH . IMAGES_DIR . $folder;
					break;
				case 'gallery':
					if (!$this->deleteFolder(ABS_PATH . IMAGES_DIR . $name, $mmbaseurl)) {
						@utils_redirect($mmbaseurl . '&status=-1');
					}
					@utils_redirect($mmbaseurl . '&status=1');
					return true;
					// break;
				default:
					{
						@utils_redirect($mmbaseurl . '&status=-1');
						return true;
					}
			}
			if (!file_exists($type . $name)) {
				@utils_redirect($mmbaseurl . '&status=-1');
				return true;
			}
			if (!unlink($type . $name)) {
				@utils_redirect($mmbaseurl . '&status=-1');
				return true;
			}
			@utils_redirect($mmbaseurl . '&status=1');
			return true;
		}
		if (isset($_GET ['status'])) {
			$this->smarty->assign('success', $_GET ['status']);
		}
		return false;
	}

	/**
	 * Main method to display media manager data.
	 *
	 * @return void
	 */
	function main() {
		$mmbaseurl = "admin.php?p=uploader&action=mediamanager";
		$folder = "";
		$gallery = "";
		if (isset($_GET ['gallery']) && !fs_is_directorycomponent($_GET ['gallery'])) {
			$mmbaseurl .= "&gallery=" . $_GET ['gallery'];
			$gallery = str_replace("/", "", $_GET ['gallery']);
			$folder = $gallery . "/";
		}

		$weburl = plugin_geturl('mediamanager');
		$this->conf = plugin_getoptions('mediamanager');
		if ($this->doItemActions($folder, $mmbaseurl)) {
			return;
		}

		$files = array();
		$galleries = array();

		$files_needupdate = array();
		$galleries_needupdate = array();

		// galleries (always from IMAGES_DIR)
		if (file_exists(ABS_PATH . IMAGES_DIR)) {
			if ($dir = opendir(ABS_PATH . IMAGES_DIR)) {
				while (false !== ($file = readdir($dir))) {
					$fullpath = ABS_PATH . IMAGES_DIR . $file;
					if (!fs_is_directorycomponent($file) && !fs_is_hidden_file($file) && is_dir($fullpath)) {
						$info = $this->getFileInfo($fullpath);
						if ($info) {
							$info ['type'] = "gallery";
							$galleries [$fullpath] = $info;
							if (is_null($info ['usecount'])) {
								$galleries_needupdate [] = $fullpath;
							}
						}
					}
				}
				closedir($dir);
			}
		}

		// attachs (NO attachs in galleries)
		if ($folder == "" && file_exists(ABS_PATH . ATTACHS_DIR)) {
			if ($dir = opendir(ABS_PATH . ATTACHS_DIR)) {
				while (false !== ($file = readdir($dir))) {
					if (!fs_is_directorycomponent($file) && !fs_is_hidden_file($file)) {
						$fullpath = ABS_PATH . ATTACHS_DIR . $file;
						$info = $this->getFileInfo($fullpath);
						if ($info) {
							$info ['type'] = "attachs";
							$info ['url'] = BLOG_ROOT . ATTACHS_DIR . $file;
							$files [$fullpath] = $info;
							if (is_null($info ['usecount'])) {
								$files_needupdate [] = $fullpath;
							}
						}
					}
				}
				closedir($dir);
			}
		}

		// images
		if (file_exists(ABS_PATH . IMAGES_DIR . $folder)) {
			if ($dir = opendir(ABS_PATH . IMAGES_DIR . $folder)) {
				while (false !== ($file = readdir($dir))) {
					$fullpath = ABS_PATH . IMAGES_DIR . $folder . $file;
					if (!fs_is_directorycomponent($file) && !fs_is_hidden_file($file) && !is_dir($fullpath)) {
						$info = $this->getFileInfo($fullpath);
						if ($info) {
							$info ['type'] = "images";
							$info ['url']  = BLOG_ROOT . IMAGES_DIR . $folder . $file;
							$files [$fullpath] = $info;
							// Always maintain, not just in the root folder
							if (is_null($info ['usecount'])) {
								$files_needupdate [] = $fullpath;
							}
						}
					}
				}
				closedir($dir);
			}
		}

		mediamanager_updateUseCountArr($files, $files_needupdate);
		mediamanager_updateUseCountArr($galleries, $galleries_needupdate);

		usort($files, [$this, "cmpfiles"]);
		usort($galleries, [$this, "cmpfiles"]);

		$totalfilescount = (string) count($files);

		// paginator
		$pages = ceil((count($files) + count($galleries)) / ITEMSPERPAGE);
		if ($pages == 0) {
			$pages = 1;
		}
		if (isset($_GET ['page'])) {
			$page = (int) $_GET ['page'];
		} else {
			$page = 1;
		}
		if ($page < 1) {
			$page = 1;
		}
		if ($page > $pages) {
			$page = $pages;
		}
		$pagelist = array();
		for($k = 1; $k <= $pages; $k++) {
			$pagelist [] = $k;
		}
		$paginator = array(
			"total" => $pages,
			"current" => $page,
			"limit" => ITEMSPERPAGE,
			"pages" => $pagelist
		);

		$startfrom = ($page - 1) * ITEMSPERPAGE;
		$galleriesout = count(array_slice($galleries, 0, $startfrom));
		$dropdowngalleries = $galleries;
		$galleries = array_slice($galleries, $startfrom, ITEMSPERPAGE);

		$files = array_slice($files, $startfrom - $galleriesout, ITEMSPERPAGE - count($galleries));

		$this->smarty->assign('paginator', $paginator);
		$this->smarty->assign('files', $files);
		$this->smarty->assign('galleries', $galleries);
		$this->smarty->assign('dwgalleries', $dropdowngalleries);
		$this->smarty->assign('mmurl', $weburl);
		$this->smarty->assign('mmbaseurl', $mmbaseurl);
		$this->smarty->assign('currentgallery', $gallery);
		$this->smarty->assign('totalfilescount', $totalfilescount);
	}

	/**
	 * Handles form submissions for media manager actions (e.g. create gallery).
	 *
	 * @param array|null $data
	 * @return int
	 */
	function onsubmit($data = NULL) {
		if (isset($_POST ['mm-newgallery'])) {
			$newgallery = strip_tags($_POST ['mm-newgallery-name']);
			if ($newgallery == "") {
				$this->smarty->assign('success', -3);
				return 2;
			}
			$newgallery = str_replace("/", "", $newgallery);
			$newgallery = str_replace(".", "", $newgallery);
			// create images folder if not existant
			if (!file_exists(ABS_PATH . IMAGES_DIR)) {
				mkdir(ABS_PATH . IMAGES_DIR);
			}
			// now create gallery folder
			if (mkdir(ABS_PATH . IMAGES_DIR . $newgallery)) {
				$this->smarty->assign('success', 3);
			} else {
				$this->smarty->assign('success', -2);
			}
			return 2;
		}

		$folder = "";
		if (isset($_GET ['gallery'])) {
			$mmbaseurl .= "&gallery=" . $_GET ['gallery'];
			$folder = str_replace("/", "", $_GET ['gallery']) . "/";
		}

		list ($action, $arg) = explode("-", $_POST ['action'], 2);
		if (!isset($_POST ['file'])) {
			return 2;
		}
		foreach ($_POST ['file'] as $file => $v) {
			list ($type, $name) = explode("-", $file, 2);
			if ($action == 'atg' && $type == 'images') {
				copy(ABS_PATH . IMAGES_DIR . $folder . $name, ABS_PATH . IMAGES_DIR . $arg . '/' . $name);
				$this->smarty->assign('success', 2);
			}
		}
		return 2;
	}

}

admin_addpanelaction('uploader', 'mediamanager', true);
?>

<?php

/**
 * Filesystem lib
 * provides basic filesystem handling functions.
 *
 * @author NoWhereMan <nowhereman@phreaker.net>
 */
class fs_filelister {

	var $_list = array();

	var $_directory = null;

	// constructor
	function __construct($directory = null) {
		if ($directory) {
			$this->_directory = $directory;
		}
		$this->_listFiles($this->_directory);
	}

	function _checkFile($directory, $file) {
		if (!is_dir($directory . "/" . $file)) {
			array_push($this->_list, $file);
		}
		return 0;
	}

	function _exitingDir($directory, $file) {
	}

	function _listFiles($directory) {

		// Try to open the directory
		if (!file_exists($directory)) {
			return array();
		}

		if ($dir = opendir($directory)) {
			// Add the files
			while ($file = readdir($dir)) {
				if (!fs_is_directorycomponent($file)) {
					$action = $this->_checkFile($directory, $file);

					// $action == 0: ok, go on
					// $action == 1: recurse
					// $action == 2: exit function

					switch ($action) {
						case (1):
							{
								$this->_listFiles($directory . "/" . $file);
								$this->_exitingDir($directory, $file);
								break;
							}
						case (2):
							{
								return false;
							}
					}
				}
			}

			// Finish off the function
			closedir($dir);
			return true;
		} else {
			return false;
		}
	}

	function getList() {
		// $this->_listFiles($this->_directory);
		return $this->_list;
	}

	function count() {
		if (!isset($this->count)) {
			$this->count = count($this->_list);
		}
		return $this->count;
	}

}

class fs_pathlister extends fs_filelister {

	function _checkFile($directory, $file) {
		$f = $directory . "/" . $file;
		if (!is_dir($f)) {
			array_push($this->_list, $f);
		} else {
			return 1;
		}
	}

}

// dir list
function fs_list_dirs($dir) {
	$dh = opendir($dir);
	while (false !== ($filename = readdir($dh))) {
		if (($filename [0] != '.')) {
			// $id = lang_id($filename);
			$files [] = $filename;
		}
	}
	sort($files);
	return $files;
}

/**
 * function fs_mkdir
 *
 * <p>Function from : {@link http://www.php.net/function.mkdir.php}</p>
 *
 * <p>Recursively creates dirs.</p>
 * <p>Returns true on success, else false</p>
 *
 * @param string $path
 *        	Directory or directories to create
 * @param int $mode
 *        	octal mode value; same as UNIX chmod; defaults to 0777 (rwrwrw);
 * @return bool
 *
 * @todo cleanup & check bool return value
 *      
 */
function fs_mkdir($dir, $mode = DIR_PERMISSIONS) {
	if (is_dir($dir) || (@mkdir($dir, $mode))) {
		@chmod($dir, $mode);
		return TRUE;
	}
	if (!fs_mkdir(dirname($dir), $mode)) {
		return FALSE;
	}
	return (@mkdir($dir, $mode) && @chmod($dir, $mode));
}

/**
 * function fs_delete
 *
 * Deletes a file and recursively deletes dirs, if they're empty
 */
function fs_delete($path) {
	if (file_exists($path)) {

		$fsuccess = unlink($path);
		$dsuccess = true;

		while ($dsuccess) {

			$path = dirname($path);
			$dsuccess = @rmdir($path);
		}

		// unlink can return both 0 and false -__-'
		return ($fsuccess);
	}

	// in our particular implementation
	// you can always delete a non existent file;
	// anyway, we'll return a value != false
	// so that we can anyway track it back

	return 2;
}

/**
 * Class fs_chmodder
 * 
 * An extension of fs_filelister for managing and applying file and directory permissions recursively. 
 * It ensures specific permissions are set for different file types and directories, with special handling for core 
 * and restricted locations. Symlink attacks are prevented by checking and skipping symbolic links.
 *
 * Attributes:
 * - public $_chmod_dir: Default permissions for directories.
 * - public $_chmod_file: Default permissions for files.
 * - public $_core_file: Permissions for critical (core) files.
 * - public $_core_dir: Permissions for critical (core) directories.
 * - public $_restricted_file: Permissions for restricted files.
 * - public $_restricted_dir: Permissions for restricted directories.
 *
 * Methods:
 * - __construct(): Initializes permissions and calls the parent class constructor.
 * - _checkFile(): Verifies and sets appropriate permissions for files and directories, avoiding symlink attacks.
 *
 * Function fs_chmod_recursive:
 * Recursively applies permissions to a specified directory and its contents.
 * 
 * Function restore_chmods:
 * Restores permissions for critical system directories and files.
 * Processes specific paths like FP_CONTENT, BASE_DIR, ADMIN_DIR, FP_INCLUDES, FP_INTERFACE, and PLUGINS_DIR.
 * Combines results to return a list of problematic files or directories that failed to update.
 *
 * Note:
 * This implementation avoids symbolic link processing and ensures consistent permission settings based on the
 * specified classification of files and directories.
 */

// Class fs_chmodder: Extension of fs_filelister for managing file and directory permissions
class fs_chmodder extends fs_filelister {

	// Attributes for permission values
	public $_chmod_dir;
	public $_chmod_file;
	public $_core_file;
	public $_core_dir;
	public $_restricted_file;
	public $_restricted_dir;

	// Initializes attributes and calls the parent constructor
	function __construct(
		$directory,
		$ch_file = FILE_PERMISSIONS,
		$ch_dir = DIR_PERMISSIONS,
		$core_file = CORE_FILE_PERMISSIONS,
		$core_dir = CORE_DIR_PERMISSIONS,
		$restricted_file = RESTRICTED_FILE_PERMISSIONS,
		$restricted_dir = RESTRICTED_DIR_PERMISSIONS
	) {
		$this->_directory = $directory;
		$this->_chmod_file = $ch_file;
		$this->_chmod_dir = $ch_dir;
		$this->_core_file = $core_file;
		$this->_core_dir = $core_dir;
		$this->_restricted_file = $restricted_file;
		$this->_restricted_dir = $restricted_dir;
		parent::__construct();
	}

	// Verifies and sets permissions for a file or directory, avoiding symlink attacks
	function _checkFile($directory, $file) {
		$retval = 0;
		$path = $directory . "/" . $file;

		// Prevent symlink attacks by verifying the path
		if (is_link($path)) {
			array_push($this->_list, $path);
			return $retval;
		}

		// Path classification
		$is_fp_content = strpos(realpath($path), realpath(FP_CONTENT)) === 0;
		$is_admin_dir = strpos(realpath($path), realpath(ADMIN_DIR)) === 0;
		$is_includes_dir = strpos(realpath($path), realpath(FP_INCLUDES)) === 0;
		$is_interface_dir = strpos(realpath($path), realpath(FP_INTERFACE)) === 0;
		$is_plugin_dir = strpos(realpath($path), realpath(PLUGINS_DIR)) === 0;
		$is_config_dir = strpos(realpath($path), realpath(CONFIG_DIR)) === 0;
		$is_users_dir = strpos(realpath($path), realpath(USERS_DIR)) === 0;
		$is_defaults_file = realpath($path) === realpath(BASE_DIR . '/defaults.php');

		if (is_dir($path)) {
			$retval = 1;
			if ($is_admin_dir || $is_includes_dir || $is_interface_dir || $is_plugin_dir || $is_config_dir || $is_users_dir) {
				// Core permissions for system-critical directories
				$chmod_value = CORE_DIR_PERMISSIONS;
			} else {
				// Default or restricted permissions
				$chmod_value = $is_fp_content ? $this->_chmod_dir : $this->_restricted_dir;
			}
		} else {
			// Otherwise, it is a file
			if ($is_defaults_file) {
				// Specific permissions for defaults.php
				$chmod_value = CORE_FILE_PERMISSIONS;
			} elseif ($is_admin_dir || $is_includes_dir || $is_interface_dir || $is_plugin_dir || $is_config_dir || $is_users_dir) {
				// Core permissions for system-critical files
				$chmod_value = CORE_FILE_PERMISSIONS;
			} else {
				// Default or restricted permissions
				$chmod_value = $is_fp_content ? $this->_chmod_file : $this->_restricted_file;
			}
		}

		// Attempt to apply permissions and log errors
		if (!@chmod($path, $chmod_value)) {
			// If chmod fails, add the path to the list of failed items
			//error_log("Failed to chmod $path to " . decoct($chmod_value));
			array_push($this->_list, $path);
		}

		// Return value (0 for file, 1 for directory)
		return $retval;
	}
}

// Recursively applies permissions to a directory
function fs_chmod_recursive($fpath = BASE_DIR) {
	$obj = new fs_chmodder(
		$fpath,
		FILE_PERMISSIONS,
		DIR_PERMISSIONS,
		CORE_FILE_PERMISSIONS,
		CORE_DIR_PERMISSIONS,
		RESTRICTED_FILE_PERMISSIONS,
		RESTRICTED_DIR_PERMISSIONS
	);
	// Return list of files/directories with permission issues
	return $obj->getList();
}

// Restores permissions for critical directories and files
function restore_chmods() {
	$files_content = fs_chmod_recursive(FP_CONTENT);
	$files_base = fs_chmod_recursive(BASE_DIR);
	$files_admin = fs_chmod_recursive(ADMIN_DIR);
	$files_includes = fs_chmod_recursive(FP_INCLUDES);
	$files_interface = fs_chmod_recursive(FP_INTERFACE);
	$files_plugins = fs_chmod_recursive(PLUGINS_DIR);
	$files_config = fs_chmod_recursive(CONFIG_DIR);
	$files_users = fs_chmod_recursive(USERS_DIR);

	// Combine results from all directories
	$files = array_merge(
		$files_content,
		$files_base,
		$files_admin,
		$files_includes,
		$files_interface,
		$files_plugins,
		$files_config,
		$files_users
	);
	//error_log("DEBUG: Files updated: " . print_r($files, true));

	// Return list of problematic files/directories for feedback
	return $files;
}

/**
 * recursive deletion
 * deletes all files and directories recursively in the given $path
 *
 * @param $fpath dir
 *        	path
 * @return bool
 */

/*
 * class fs_deleter extends fs_filelister {
 *
 * function fs_deleter($directory) {
 * $this->_directory = $directory;
 * parent::__construct();
 * }
 *
 * function _checkFile($directory, $file) {
 *
 * $path = "$directory/$file";
 *
 * /*
 * open dir handle prevents directory deletion of php5 (and probably win)
 * thanks to cimangi <cimangi (at) yahoo (dot) it> for noticing and
 * giving a possible solution:
 *
 * filenames are cached and then deleted
 * //
 *
 * if ( is_dir($path) ) {
 * return 1;
 * } elseif ( file_exists($path) ) {
 * array_push($this->_list, $path);
 * return 0;
 * } else {
 * return 2;
 * }
 *
 * }
 *
 * }
 *
 */

/*
 * open dir handle prevents directory deletion of php5 (and probably win)
 * thanks to cimangi <cimangi (at) yahoo (dot) it> for noticing and
 * giving a possible solution;
 *
 * paths are now cached and then deleted
 */
function fs_delete_recursive($path) {
	if (file_exists($path)) {

		$obj = new fs_pathlister($path);
		$list = ($obj->getList());

		unset($obj);

		$elem = null;
		while ($elem = array_pop($list)) {
			$elem;
			fs_delete($elem);
		}
	}

	return true;
}

function fs_copy($source, $dest) {
	if ($contents = io_load_file($source)) {
		return io_write_file($dest, $contents);
	}
	return false;
}

/**
 * Checks if the file with the given name is a directory component ('.' or '..').
 *
 * @param string $filename
 *        	the file name
 * @return boolean <code>true</code> if the file is a directory component; otherwise <code>false</code>
 */
function fs_is_directorycomponent($filename) {
	return $filename === '.' || $filename === '..';
}

/**
 * Checks if the file with the given name is a hidden file (i.e., starts with a '.').
 *
 * @param string $filename
 *        	the file name
 * @return boolean <code>true</code> if the file is a hidden file; otherwise <code>false</code>
 */
function fs_is_hidden_file($filename) {
	return strlen($filename) > 0 && substr($filename, 0, 1) === '.';
}
?>

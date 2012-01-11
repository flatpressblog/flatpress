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
		
		
		//constructor
		function fs_filelister($directory = null) {
			if ($directory) $this->_directory = $directory; 
			$this->_listFiles($this->_directory);
		}
		
		function _checkFile($directory, $file) {
			if (!is_dir("$directory/$file"))
				array_push($this->_list, $file);
			return 0;
		}
		
		function _exitingDir($directory, $file) {
			
		}
		
		function _listFiles($directory) {
		
			// Try to open the directory
			if (!file_exists($directory)) return array();
			
			if($dir = opendir($directory)) {
				// Add the files
				while($file = readdir($dir)) {
				
					if ($file != '.' && $file != '..') {
						 
						$action = $this->_checkFile($directory,$file);
						
						// $action == 0:	ok, go on
						// $action == 1:	recurse
						// $action == 2:	exit function
						
						switch ($action) {
							case (1): {
								$this->_listFiles("$directory/$file");
								$this->_exitingDir($directory, $file);
								break;
							}
							case (2): {
								return false;
							}
						}
					}
					
				}
			
			// Finish off the function
			closedir($dir);
			return true;
			}
			else return false;
			
		}
		
		function getList() {
			//$this->_listFiles($this->_directory);
			return $this->_list;
		}

		function count() {
			if (!isset ($this->count))
				$this->count = count($this->_list);
			return $this->count;
		}

	}
	
	class fs_pathlister extends fs_filelister {
		function _checkFile($directory, $file) {
			$f = "$directory/$file";
			if (!is_dir($f))
				array_push($this->_list, $f);
			else 
				return 1;
		}
	
	}
	
	// dir list
	function fs_list_dirs($dir) {
		$dh  = opendir($dir);
		while (false !== ($filename = readdir($dh))) {
		if ( ($filename[0] != '.') ) {
		//	$id = lang_id($filename);
			$files[] = $filename;
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
	 * @param string $path Directory or directories to create
	 * @param int $mode octal mode value; same as UNIX chmod; defaults to 0777 (rwrwrw);
	 * @return bool
	 *
	 * @todo cleanup & check bool return value
	 *
	 */
        function fs_mkdir($dir, $mode=DIR_PERMISSIONS) {
                if (is_dir($dir) || (@mkdir($dir,$mode))) {@chmod($dir, $mode); return TRUE;}
                if (!fs_mkdir(dirname($dir),$mode)) return FALSE;
                return (@mkdir($dir,$mode) && @chmod($dir, $mode));               
        }

	
	
	/**
	 * function fs_delete
	 *
	 * Deletes a file and recursively deletes dirs, if they're empty
	 * 
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
	 * function fs_recursive_chmod
	 * 
	 * Perform a recursive reset of file permission in the given $path
	 * and its subdirectories to 0777
	 *
	 * @param $fpath dir path
	 * @return bool
	 *
	 */
	
	 class fs_chmodder extends fs_filelister {
		 
		 var $_chmod_dir;
		 var $_chmod_file;

		function fs_chmodder($directory, $ch_file=FILE_PERMISSIONS, $ch_dir=DIR_PERMISSIONS) {
			$this->_directory = $directory;
			$this->_chmod_file = $ch_file;
			$this->_chmod_dir = $ch_dir;
			parent::fs_filelister();
		}
		
		function _checkFile($directory, $file) {
			$retval = 0;
			$path = "$directory/$file";
			if (is_dir($path))
				$retval = 1;
			if ( !@chmod($path, ($retval? $this->_chmod_dir : $this->_chmod_file ) ) )
				array_push($this->_list, $path);
			
			return $retval;
		}
	 }
	 
	function fs_chmod_recursive($fpath=FP_CONTENT) {
		$obj = new fs_chmodder($fpath);
		return $obj->getList();
	}
	
	
	
	/**
	* recursive deletion
	* deletes all files and directories recursively in the given $path
	* @param $fpath dir path
	* @return bool
	*/
	
	/*class fs_deleter extends fs_filelister {
		
		function fs_deleter($directory) {
			$this->_directory = $directory;
			parent::fs_filelister();
		}
		
		function _checkFile($directory, $file) {
			
			$path = "$directory/$file";	
			
			/*
			 *	open dir handle prevents directory deletion of php5 (and probably win)
			 *	thanks to cimangi <cimangi (at) yahoo (dot) it> for noticing and 
			 *	giving a possible solution:
			 *
			 *	filenames are cached and then deleted
			 //
			
			if ( is_dir($path) ) {
				return 1;
			} elseif ( file_exists($path) ) {
				array_push($this->_list, $path);
				return 0;
			} else {
				return 2;
			}
			
		}
		
	}
	
	*/
	
	/*
	 *	open dir handle prevents directory deletion of php5 (and probably win)
	 *	thanks to cimangi <cimangi (at) yahoo (dot) it> for noticing and 
	 *	giving a possible solution;
	 *
	 *	paths are now cached and then deleted
	 */

	function fs_delete_recursive($path) {
		if (file_exists($path)) {
		
			$obj = new fs_pathlister($path);
			$list = ($obj->getList());
			
			unset($obj);
			
			$elem = null;
			while($elem = array_pop($list)) {
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



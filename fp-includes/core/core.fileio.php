<?php	

	// fileio.php
	// low-level io-handling functions
	
	function io_write_file($filename, $data) {
		@umask(0);
		$dir = dirname($filename);
		if (fs_mkdir($dir)) {
			$f = fopen($filename, "w");
			if ($f) {
			
				if (!flock($f, LOCK_EX))
					return -1;
			
				$length = strlen($data);
				$done = fwrite($f, $data);
				
				flock($f, LOCK_UN);
				
				fclose($f);
				
				@chmod($filename, FILE_PERMISSIONS);
				
				//returns true on success

				return($length == $done);
			}
			
		}
		
		return false;
			
	}
	
	function io_load_file($filename) {
			
		if (file_exists($filename)) {
			if (function_exists('file_get_contents'))
				return file_get_contents($filename);

			$f = fopen($filename, "r");
			if ($f) {
				if (!flock($f, LOCK_SH))
					return -1;
				$contents = fread($f, filesize($filename));
				flock($f, LOCK_UN);
				fclose($f);
			
				//returns contents as string on success
				return($contents);
			}
		} 
		//trigger_error("io_load_file: $filename does not exists", E_USER_ERROR);
		return false;
	}	
	


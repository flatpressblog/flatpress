<?php

	define('FP_SORTING', SORT_DESC);
	
	class cache_filelister extends fs_filelister {
		
		var $_cachefile = null;
		
		// sub-classes will fill the above variables on constructing
		function cache_filelister() {
		
			if (!$this->_cachefile)
				trigger_error('CACHE: no cache file specified');
		
			$varname = $this->_varname;
				
			if (file_exists($this->_cachefile)) {
				//include($this->_cachefile);
				$var = io_load_file($this->_cachefile);
				$this->_list = unserialize($var);
			} else {
				parent::fs_filelister();
				
				$this->save();
			}
			
			return $this->_list;

		}
		
		function checksorting() {
		
			list($k1) = each($this->_list);
			list($k2) = each($this->_list);
			
			// decreasing order
			
			if ((FP_SORTING==SORT_DESC) & (strcmp($k1, $k2) < 0)) {
				$this->save;
			}
			
			
			
			
		}
		
		function save() {
		
			
			// TODO: re-think this :)
			// reverse sorting on save is an acceptable overhead, 
			// still this is quite an hack
			
			krsort($this->_list);
			$succ = io_write_file($this->_cachefile, serialize($this->_list));
			
			if (!$succ){
						trigger_error("Error while saving data in {$this->_cachefile}",
						E_USER_WARNING);
				return false;
			
			
			} else return $this->_list;
						
		}
		
		function getList() {
			return $this->_list;
		}
		
		function get($id) {
			return isset($this->_list[$id])? $this->_list[$id] : false;
		}
		
		function add($id, $val) {
			$this->_list[$id]=$val;
			
			return $this->save();
		}
		
		function delete($entryid) {
			$cache =& $this->_list;
			unset($cache[$entryid]); // if id found, it is deleted
			
			return $this->save();
		}
		
		function purge() {
			return fs_delete($this->_cachefile);
		}
		
	}
	
?>
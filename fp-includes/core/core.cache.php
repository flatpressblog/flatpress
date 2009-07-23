<?php

	define('FP_SORTING', SORT_DESC);
	
	class cache_filelister extends fs_filelister {
		
		var $_cachefile = null;
		var $_offset = 0;
		var $_nodesize = 30;
		var $_keysize = 12;
		
		// sub-classes will fill the above variables on constructing
		function cache_filelister() {
		
			if (!$this->_cachefile)
				trigger_error('CACHE: no cache file specified', E_USER_ERROR);
		
			$varname = $this->_varname;
			
			if (!file_exists($this->_cachefile.'.dat')) {
				trigger_error  ("Can't find index '{$this->_cachefile}'", E_USER_ERROR);
			}

			$this->_tree = new caching_SBPT(
				fopen($this->_cachefile.'.dat', 'rb'),
				fopen(INDEX_DIR.'index.strings.dat', 'rb'),
				256,
				$this->_offset,
				$this->_chunksize,
				$this->_keysize
			);

			$this->_tree->open();
			
		
			#return $this->_list;

		}

		function walker() {
			return $this->_tree->walker();
		}

		function length() {
			return $this->_tree->length();
		}

		function save() {

			trigger_error('Cannot save() a cache', E_USER_ERROR);

			/*
			
			krsort($this->_list);
			$succ = io_write_file($this->_cachefile, serialize($this->_list));
			
			if (!$succ){
						trigger_error("Error while saving data in {$this->_cachefile}",
						E_USER_WARNING);
				return false;
			
			
			} else return $this->_list;
			 */
						
		}
		
		function getList() {
			trigger_error('Cannot getlist from cache', E_USER_WARNING);
			#return $this->_list;
		}
		
		function get($id) {
			return $this->_tree->getitem($id);
			#return isset($this->_list[$id])? $this->_list[$id] : false;
		}
		
		function add($id, $val) {
			trigger_error('Cannot add to a cache', E_USER_ERROR) ;
			$this->_list[$id]=$val;
			
			return $this->save();
		}
		
		function delete($entryid) {
			trigger_error('Cannot delete from a cache', E_USER_ERROR) ;
			$cache =& $this->_list;
			unset($cache[$entryid]); // if id found, it is deleted
			
			return $this->save();
		}
		
		function purge() {
			trigger_error('cannot purge', E_USER_ERROR);
			return fs_delete($this->_cachefile);
		}
		
	}
	
?>

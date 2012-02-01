<?php

	class comment_indexer extends fs_filelister {
		
		function comment_indexer($id) {
			$f = bdb_idtofile($id,BDB_COMMENT); //todo change
			$this->_directory = $f; 
			parent::fs_filelister();
			//substr(bdb_idtofile($id), -strlen(EXT)); 
		}
		
		function _checkFile($directory, $file) {
			$f = "$directory/$file";
				if (fnmatch('comment*'.EXT, $file)) {
					array_push($this->_list, basename($file,EXT));
					return 0;
				}
		}
		
		// overrides parent method to return sorted results
		function getList() {
			sort($this->_list);
			return parent::getList();
		}
		
	}
	

	/**
	 * function bdb_get_comments
	 *
	 * <p>On success returns an array containing the comment <b>IDs</b>, associated to
	 * the entry ID in $id</p>
	 * <p>On failure returns false</p>
	 *
	 * @param string $id string formatted like "prefixYYMMDD-HHMMSS.EXT"
	 * @return mixed
	 * 
	 * @see bdb_idtofile()
	 */
	function comment_getlist($id) {
		$obj = new comment_indexer($id); //todo change syntax
		return $obj->getList();
		
	}
	
	function comment_parse($entryid, $id) {
		
		$f = comment_exists($entryid, $id);
		
		
		if (!$f) return false;
		
		
		$fc = io_load_file($f);
		$arr = utils_kexplode($fc);
		
		//$arr['EMAIL'] = apply_filters('comment_email', $arr['EMAIL']);
		// hackish: dash to underscore for ip-address :( todo: clean this up here or somewhere else
		//$arr['ip_address'] = $arr['ip-address'];
		return array_change_key_case($arr, CASE_LOWER);
		
	}
	
	function comment_exists($entryid, $id) {
		if (!preg_match('|^comment[0-9]{6}-[0-9]{6}$|', $id))
			return false;
		$f = entry_exists($entryid);
		if (!$f) return false;
		
		$f2 = substr($f, 0, -strlen(EXT)) . '/comments/' . $id.EXT;
		if (!file_exists($f2)) return false;
		
		return $f2;
		
	}


	function comment_clean(&$arr) {
		$arr['name'] = apply_filters('pre_comment_author_name', stripslashes($arr['name']));
		if (isset($arr['email']))
			$arr['email'] = apply_filters('pre_comment_author_email', $arr['email']);
		if (isset($arr['url']))
			$arr['url'] = apply_filters('pre_comment_author_url', $arr['url']);
		$arr['content'] = apply_filters('pre_comment_content', stripslashes($arr['content']));
		return $arr;
	}


	/**
	 * function bdb_save_comment
	 *
	 * <p>Saves the content of the $comment array, associating it to the entry-ID $id.</p>
	 * <p>$comment must be formatted as the one returned by {@link bdb_parse_entry()}.</p>
	 * <p>Returns true on success, or false on failure</p>
	 *
	 * @param string $id string formatted like "prefixYYMMDD-HHMMSS"
	 * @param array $comment array formatted as the one returned by {@link bdb_parse_entry()}
	 * @return bool
	 * 
	 * @see bdb_parse_entry()
	 */
	function comment_save($id, $comment) {
		
		comment_clean($comment);
		
		$comment = array_change_key_case($comment, CASE_UPPER);
		
		$comment_dir = bdb_idtofile($id,BDB_COMMENT);
		
		if (!isset($comment['DATE']))
				$comment['DATE'] = date_time();
		$id = bdb_idfromtime(BDB_COMMENT, $comment['DATE']);
		$f = $comment_dir . $id . EXT;
		$str = utils_kimplode($comment);
		if (io_write_file($f, $str))
			return $id;
		
		
		return false;
		
		
	}
	


	
	/**
	 * function comment_delete
	 *
	 * <p>Deletes the $id comment</p>
	 * <p>Returns true on success, or false on failure</p>
	 *
	 * @param string $id string formatted like "entryYYMMDD-HHMMSS"
	 * @param string $comment_id string representig comment id as in "commentYYMMDD-HHMMSS"  
	 * @return bool
	 * 
	 * @see entry_delete()
	 */
	function comment_delete($id, $comment_id) {
		do_action('comment_delete', $id, $comment_id);
		$comment_dir = bdb_idtofile($id,BDB_COMMENT);
		$f = $comment_dir . $comment_id .EXT;
		return fs_delete($f);
	}
	
		
	function dummy_comment($val) {
		return $val;
	}
	
	add_filter('comment_validate', 'dummy_comment');
	

	
?>

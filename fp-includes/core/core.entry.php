<?php

	class entry_cached_index extends caching_SBPT { #cache_filelister {

		var $position = 0;
		var $nodesize = 30;
		var $keylen = 12;


		/**
		 * opens the index belonging to a given category
		 * @params int $id_cat	
		 */
		function entry_cached_index($id_cat=0) {
			$F = INDEX_DIR.'index-'.$id_cat.'.dat';
		
			if (!file_exists($F)) {
				trigger_error  ("Can't find index '{$F}'", E_USER_ERROR);
				
			}

			parent::caching_SBPT(
				fopen($F, 'rb'),
				fopen(INDEX_DIR.'index.strings.dat', 'rb'),
				256,
				$this->position,
				$this->nodesize,
				$this->keylen
			);

			$this->open();
		}

	}

	class entry_index {
		
		var $indices = array();
		var $_offset = 0;
		var $_chunksize = 30;
		var $_keysize = 12;

		var $_lock_file = null;


		function entry_index() {
			$this->_lock_file = CACHE_DIR.'bpt.lock';

			$this->catlist = entry_categories_list();

			// only main index s a SBPlus (string BPlus): 
			// the other (other categories) are managed 
			// as if they were simple BPlus trees, so
			// values in key,value pairs won't
			// be strings but integers
			//
			// the integer will be the seek position 
			// in the SBPlus' string file
			//
			// they'll be loaded back with the string file
			// as SBPlus trees: the string-key, string-value pair
			// will be returned
			
			if ($oldfile = file_exists($f=INDEX_DIR.'index-0.dat')) 
				$mode = 'r+b';
			else 
				$mode = 'w+b';

			$this->indices[0] = new SBPlusTree(
				fopen($f, $mode),
				fopen(INDEX_DIR.'index.strings.dat', $mode),
				256,
				$this->_offset,
				$this->_chunksize,
				$this->_keysize
			);

			if ($oldfile)
				$this->indices[0]->open();
			else 
				$this->indices[0]->startup();


		}

		function _lock_acquire($exclusive=true, $cat=0) {
			if (file_exists($this->_lock_file)) {
				trigger_error("Could not acquire write lock on INDEX. ".
				"Didn't I told you FlatPress is not designed for concurrency, already? ;) ".
				"Don't worry: your entry has been saved as draft!", E_USER_WARNING);
				return false;
			}

			// simulates atomic write by writing to a file, then moving in place
			$tmp = $this->_lock_file.".tmp";
			if (io_write_file($tmp, 'dummy')) {
				if (rename($tmp, $this->_lock_file)) {
					return true;
				}
			}

			return false;

		}

		function _lock_release($cat=0) {
			if (file_exists($this->_lock_file)) {
				return @unlink($this->_lock_file);
			} else {
				trigger_error("Lock file did not exist: ignoring (index was already unlocked.)", E_USER_NOTICE);
				return 2;
			}
	
		}

		function &get_index($cat=0) {
			if (!is_numeric($cat)) 
				trigger_error("CAT must be an integer ($cat was given)", E_USER_ERROR);
			if (!isset($this->indices[$cat])) {
				$f = INDEX_DIR.'index-'.$cat.'.dat';
				if ($oldfile = file_exists($f)) 
					$mode = 'r+b';
				else	$mode = 'w+b';

				$this->indices[$cat] = new BPlusTree(
					fopen($f, $mode),
					$this->_offset,
					$this->_chunksize,
					$this->_keysize
				);
				if ($oldfile)
					$this->indices[$cat]->open();
				else $this->indices[$cat]->startup();
			}
			return $this->indices[$cat];
		}

		function add($id, $entry, $del = array(), $update_title = true) {
			$key = entry_idtokey($id);
			$val = $entry['subject'];

			if (!$this->_lock_acquire()) return false; // we're DOOMED!

			$main =& $this->get_index();
			$seek = null;

			// title must not be updated, let's get the offset value from has_key
			if (!$update_title) 
				$seek = $main->has_key($key, $val);

			// if seek is null, then there is no such key, and we must set it
			// in the main index
			if (!is_numeric($seek))
				$seek = $main->setitem($key, $val);

			// key has been set, let's set the other indices (if any), and link them
			// to the title string using $seek
			
			if (isset($entry['categories']) && is_array($entry['categories'])) {
				
				$categories = array();
				
				foreach ($entry['categories'] as $cat) {
					
					// skip non-numeric special categories (such as 'draft')
					if (!is_numeric($cat)) continue;
					
					$categories[] = $cat;
					
					// traverse the full cat tree (in linearized form)
					// to update categories which eventually aren't
					// explicitly listed
					while ($parent = $this->catlist[ $cat ]) {
						$categories[] = $parent;
						$cat = $parent;
					}
				}
				
				// delete any duplicate
				$categories = array_unique($categories);
				
				foreach ($categories as $cat) {
					$this_index =& $this->get_index($cat);
					$this_index->setitem($key, $seek);
				}
			}

			// if the set of indices changed, some might have to be deleted 
			if ($del) {
				foreach($del as $cat) {
					// echo 'DEL '. $cat,"\n";
					if (!is_numeric($cat)) continue;
					$this_index =& $this->get_index($cat);
					$this_index->delitem($key);
				}
			}

			return $this->_lock_release();

		}

		function delete($id, $entry) {
			$key = entry_idtokey($id);

			if (!$this->_lock_acquire()) return false; // we're DOOMED!
			
			$main =& $this->get_index();
			$main->delitem($key);

			
			if (isset($entry['categories']) && is_array($entry['categories'])) {
				foreach ($entry['categories'] as $cat) {
					if (!is_numeric($cat)) continue;
					$this_index =& $this->get_index($cat);
					if ($this_index->has_key($key))
						$this_index->delitem($key);
				}
			}

			return $this->_lock_release();

		}

	}

	class entry_archives extends fs_filelister {
		
		var $_directory = CONTENT_DIR;
		var $_y = null;
		var $_m = null;
		var $_d = null;
		
		var $_count = 0;
		
		var $_filter = 'entry*';
		
		function entry_archives($y, $m = null, $d = null) {
			
			$this->_y = $y;
			$this->_m = $m;
			$this->_d = $d;
			
			$this->_directory .= "$y/";
			
			if ($m){
			
				$this->_directory .= "$m/";
		
				if ($d) {
					$this->_filter = "entry$y$m$d*";
				}
			
			}	
			
			return parent::fs_filelister();
		}
		
		function _checkFile($directory, $file) {
			$f = "$directory/$file";
				if ( is_dir($f) && ctype_digit($file)) {
					return 1;
				}
				
				if (fnmatch($this->_filter.EXT, $file)) {
					$id=basename($file,EXT);
					$this->_count++;
					array_push($this->_list, $id);
					return 0;
				}
		}
		
		function getList() {
			rsort($this->_list);
			return parent::getList();
		}
		
		function getCount() {
			return $this->_count;
		}
		
	}
	
	/* //work in progress
	class entry {

		var $_indexer;
		var $id;

		function entry($id, $content) {
			//$this->_indexer =& $indexer;
		}

		function get($field) {
			$field = strtolower($field);
			if (!isset($this->$field)) {
				// if it is not set
				// tries to fetch from the database
				$arr = entry_parse($id);
				while(list($field, $val) = each($arr))
					$this->$field = $val;
				
				// if still is not set raises an error
				if (!isset($this->$field))
					trigger_error("$field is not set", E_USER_NOTICE);
					return;
					
				
			}
			
			return $this->$field;
			
		}

		function set($field, $val) {
			$field = strtolower($field);
			$this->$field = $val;
		}

	}	
	*/
	
	/**
	* function entry_init
	* fills the global array containing the entry object
	*/
	function &entry_init() {
		
		#global $fpdb;
		#$fpdb->init();
		
		static $entry_index = null;

		if (is_null($entry_index)) 
			$entry_index= new entry_index;

		return $entry_index;
		
	}

	function &entry_cached_index($id_cat) {

		$F = INDEX_DIR.'index-'.$id_cat.'.dat';
		
		if (!file_exists($F)) {
			$o = false;	
		} else {
			$o = new entry_cached_index($id_cat);
		}

		return $o;

	}

	
	/*
	function entry_query($params=array()){
		
		global $fpdb;
		$queryid = $fpdb->query($params);
		$fpdb->doquery($queryid);
		
		
	}
	
	function entry_hasmore() {
		global $fpdb;
		return $fpdb->hasmore();
		
	}
	
	function entry_get() {
		$fpdb->get();
	}
	 */

	function entry_keytoid($key) {
		$date = substr($key,0,6);
		$time = substr($key,6);
		return "entry{$date}-{$time}";
	}

	function entry_idtokey($id) {
		return substr($id, 5, 6) . substr($id, 12);
	}

	function entry_timetokey($time) {
		return date('ymdHis', $time);
	}

	function entry_keytotime($key) {
		$arr[ 'y' ] = substr($key, 0, 2);
		$arr[ 'm' ] = substr($key, 2, 2);
		$arr[ 'd' ] = substr($key, 4, 2);
			
		$arr[ 'H' ] = substr($key, 6, 2);
		$arr[ 'M' ] = substr($key, 8, 2);
		$arr[ 'S' ] = substr($key, 10, 2);

		return 	mktime($arr['H'], $arr['M'], $arr['S'],
				$arr['m'], $arr['d'], $arr['y']);
	}

	function entry_idtotime($id) {
		$date = date_from_id($id);
		return $date['time'];
	}

	function entry_list() {

		trigger_error('function deprecated', E_USER_ERROR);
		
		$obj =& entry_init();
		
		$entry_arr = $obj->getList();
		
		
		if ($entry_arr) {
			krsort($entry_arr);
			return $entry_arr;
		}
	}
	
	function entry_exists($id) {
		$f = entry_dir($id).EXT;
		return file_exists($f)? $f : false;
	}
	
	function entry_dir($id, $month_only = false) {
		if (!preg_match('|^entry[0-9]{6}-[0-9]{6}$|', $id))
			return false;
		$date = date_from_id($id);
		if ($month_only)
			$f = CONTENT_DIR . "{$date['y']}/{$date['m']}/"; 
		else
			$f = CONTENT_DIR . "{$date['y']}/{$date['m']}/$id"; 
		return $f;
	
	
	}
	
	function entry_parse($id, $raw=false) {
		
		$f = entry_exists($id);
		if (!$f)
			return array();
			
		$fc = io_load_file($f);
		
		if (!$fc)
			return array();
			
		$arr = utils_kexplode($fc);
		
		// propagates the error if entry does not exist
		
		
		if (isset($arr['categories']) && // fix to bad old behaviour:
					(trim($arr['categories']) != '')) { 
		 
				
				$cats = (array)explode(',',$arr['categories']);
				$arr['categories'] = (array) $cats;
				
		
				
		} else $arr['categories'] = array();
		
		// if (!is_array($arr['categories'])) die();
		
		if (!isset($arr['AUTHOR'])) {
			global $fp_config;
			$arr['AUTHOR'] = $fp_config['general']['author'];
		}

		if ($raw) return $arr;
		return $arr;
		
	}
	
	
	/**
	 * function entry_get_comments
	 * 
	 * @param string id entry id
	 * @param array entry entry content array by ref; 'commentcount' field is added to the array
	 * 
	 * @return object comment_indexer as reference
	 *
	 */
	 
	function &entry_get_comments($id, &$count) {
		$obj = new comment_indexer($id);

		$count = count($obj->getList());
		
		return $obj;
		
	}


	function entry_categories_encode($cat_file) {
	
	
		//if ($string = io_load_file(CONTENT_DIR . 'categories.txt')) {
			$lines = explode("\n", trim($cat_file));
			$idstack = $result = $indentstack=array();
		
			while (!empty($lines)) {
		
				$v = array_pop($lines);

				$vt = trim($v);

				if ($vt) {
		
					$text='';
					$indent = utils_countdashes($vt, $text);
					
					$val = explode(':', $text);
				
					$id     = trim($val[1]);
					$label  = trim($val[0]);

					// IDs must be strictly positive
					
					if ($label && $id <= 0) return -1;


			
					if (empty($indentstack)) {
						array_push($indentstack,$indent);
						array_push($idstack, $id);
						$indent_old = $indent;
					} else {
						$indent_old = end($indentstack);
					}
			
					if ($indent < $indent_old) {
						array_push($indentstack, $indent);
						array_push($idstack, $id);
					} elseif ($indent > $indent_old) {
						$idstack = array($id);
						$indentstack = array($indent);
					} else {
						array_pop($idstack);
						$idstack = array($id);
					}
					
					
			
					$result['rels'][$id]  = $idstack;
					$result['defs'][$id] = $label;
				}
		
			}
			
			ksort($result['rels']);
			ksort($result['defs']);

			//print_r($result);
			
			return io_write_file(CONTENT_DIR . 'categories_encoded.dat', serialize($result));
			
		//}
	
		return false;
	
	
	}

	/*
	
	function entry_categories_print(&$lines, &$indentstack, &$result, $params) {

	
}

*/
	function entry_categories_list() {
		if (!$string = io_load_file(CONTENT_DIR . 'categories.txt'))
			return false;

			$lines = explode("\n", trim($string));
			$idstack = array(0);
			$indentstack=array();


			// $categories = array(0=>null);
			$lastindent = 0;
			$lastid = 0;
			$parent = 0;

			$NEST = 0;

			foreach ($lines as $v) {

				$vt = trim($v);

				if (!$vt) continue;

				$text='';
				$indent = utils_countdashes($vt, $text);
					
				$val = explode(':', $text);
				
				$id     = trim($val[1]);
				$label  = trim($val[0]);

				// echo "PARSE: $id:$label\n";
				if ($indent > $lastindent) {
					// echo "INDENT ($indent, $id, $lastid)\n";
					$parent = $lastid;
					array_push($indentstack, $lastindent);
					array_push($idstack, $lastid);
					$lastindent = $indent;
					$NEST++;
				} elseif ($indent < $lastindent) {
					// echo "DEDENT ($indent)\n";
					do {
						$dedent = array_pop($indentstack);
						array_pop($idstack);
						$NEST--;
					} while ($dedent > $indent);
					if ($dedent < $indent) return false; //trigger_error("failed parsing ($dedent<$indent)", E_USER_ERROR);
					$parent = end($idstack);
					$lastindent = $indent;
					$lastid = $id;
				}

					$lastid = $id;
					// echo "NEST: $NEST\n";
				

				$categories[ $id ] = $parent;
			
			}

			return $categories;

	}

	function entry_categories_get($what=null) {
		
		global $fpdb;
		
		$categories = array();
		
		if (!empty($fpdb->_categories)) {
			$categories = $fpdb->_categories; 
		} else { 
			
			$f = CONTENT_DIR . 'categories_encoded.dat';
			if (file_exists($f)) {
				if ($c = io_load_file($f))
					$categories = unserialize($c);
			}
			
		}
		
		if ($categories) {
	
			if ($what=='defs' || $what=='rels')
				return $categories[$what];
			else 
				return $categories;
		}
		return array();
	}
	
	/**
	  
	  flags are actually special categories
	  which are usually hidden.
	  
	  they can be set when editing your entries
	  to let flatpress perform special actions
	  
	  draft: Draft entry (hidden, awaiting publication)
	  static: Static entry (allows saving an alias, so you can reach it with 
	      ?page=myentry)
	  commslock: Comments locked (comments disallowed for this entry)
	  
	  
	*/
	
	function entry_flags_get() {
		 
		return array(
			'draft',
			//'static',
			'commslock'
			);
		
		
	}

	// @TODO : check against schema ?
	function entry_prepare(&$entry) {		// prepare for serialization
		global $post;
		
		// fill in missing value
		if (!isset($entry['date'])) {
			$entry['date']=date_time();
		}

		// import into global scope
		$post = $entry;

		// apply *_pre filters
		$entry['content'] = apply_filters('content_save_pre', $entry['content']);
		$entry['subject'] = apply_filters('title_save_pre', $entry['subject']);


		// prepare for serialization
		if (isset($entry['categories'])) {
		
			if (!is_array($entry['categories'])) {
				trigger_error("Expected 'categories' to be an array, found " 
					. gettype($entry['categories']), E_USER_WARNING);
				$entry['categories'] = array();
			}

		} else { $entry['categories'] = array(); }

	

		return $entry;
	}

	/**
	 *
	 * @param array entry 	contents
	 * @param string|null 	entry id, null if can be deducted from the date field of $entry; 
	 * 						defaults to null
	 *
	 * @param bool 			updates entry index; defaults to true	
	 *
	 *
	 * @return integer 		-1 failure while storing preliminar draft, abort. Index not touched.
	 * 						-2 index updated succesfully, but draft doesn't exist anymore 
	 * 						   (should never happen!) OR
	 * 						   failure while trying to move draft to entry path, draft does not exist anymore
	 * 						   index not touched
	 * 						-3 error while moving draft still exists, index written succesfully but rolled back
	 * 						-4 failure while saving to index, aborted (draft still exists)
	 *
	 *
	 */
		
	function entry_save($entry, $id=null, $update_index = true) {

		// PHASE 1 : prepare entry
		if (!$id) {
			if (!@$entry['date']) $entry['date'] = date_time();
			$id = bdb_idfromtime(BDB_ENTRY, $entry['date']);
		}


		// PHASE 2 : Store
	
		// secure data as DRAFT
		// (entry is also implicitly entry_prepare()'d here)
		$ret = draft_save($entry, $id);
		do_action('publish_post', $id, $entry);

		if ($ret === false) {
			return -1; // FAILURE: ABORT
		}


		// PHASE 3 : Update index
		$delete_cats = array();
		$all_cats = @$entry['categories'];
		$update_title = true;
		if ($old_entry = entry_parse($id)) {
			if ($all_cats) {
				$delete_cats = array_diff($old_entry['categories'], $all_cats);
			}
			$all_cats = $all_cats? array_merge($all_cats, $old_entry['categories']) : $old_entry['categories'];
			$update_title = $entry['subject'] != $old_entry['subject'];
		} 

		/*
		echo 'old';
		print_r($old_entry['categories']);
		echo 'new';
		print_r($entry['categories']);
		echo 'del';
		print_r($delete_cats);
		echo 'all';
		print_r($all_cats);
		*/

		$INDEX =& entry_init();
		$ok = ($update_index) ? $INDEX->add($id, $entry, $delete_cats, $update_title) : true;

		// PHASE 4 : index updated; let's move back the entry
		if ($ok) {
	
			$entryd = entry_dir($id, true);
			$entryf = $entryd.$id.EXT;
			$draftf = draft_exists($id);
			if ($draftf === false) { // this should never happen!
				if ($update_index) {
						$INDEX->delete($id, $all_cats);
				}
				return -2;
			}
		
			fs_delete($entryf);
			fs_mkdir($entryd);
			$ret = rename($draftf, $entryf);

			if (!$ret) {
				if (draft_exists($id)) {
					// rollback changes in the index
					// (keep the draft file)
					if ($update_index) {
						$INDEX->delete($id, $all_cats);
					}
					return -3;
				} else {
					return -2;
				}
			} else {
				// SUCCESS : delete draft, move comments along
				draft_to_entry($id);
				return $id;
			}
			
		} 
		return -4;
		
	}

	
	function entry_delete($id) {
	
		if ( ! $f = entry_exists($id) )
			return; 
		
		
		/*
		$d = bdb_idtofile($id,BDB_COMMENT);
		fs_delete_recursive("$d");
		
		// thanks to cimangi for noticing this
		$f = dirname($d) . '/view_counter' .EXT;
		fs_delete($f);
		
		
		$f = bdb_idtofile($id);
		*/
		
		$d = entry_dir($id);
		fs_delete_recursive($d);
		
		$obj =& entry_init();
		$obj->delete($id, entry_parse($id));
		
		do_action('delete_post', $id);
		
		return fs_delete($f);
	}
	
	function entry_purge_cache() {
		$obj =& entry_init();
		$obj->purge();
	}
	//add_action('init', 

?>

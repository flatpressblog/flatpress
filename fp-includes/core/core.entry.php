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


		function entry_index() {

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

		function &get_index($cat=0) {
			if (!is_numeric($cat)) 
				trigger_error("CAT must be an integer ($cat was given)", E_USER_ERROR);
			if (!isset($this->indices[$cat])) {
				$f = INDEX_DIR.'index-'.$cat.'.dat';
				if ($oldfile = file_exists($f)) 
					$mode = 'r+b';
				else	$mode = 'w+b';

				$this->indices[$cat] =& new BPlusTree(
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

		function add($id, $entry, $del = array()) {
			$key = entry_idtokey($id);
			$val = $entry['SUBJECT'];

			$main =& $this->get_index();
			$seek = $main->setitem($key, $val);

			if (isset($entry['CATEGORIES']) && is_array($entry['CATEGORIES'])) {
				foreach ($entry['CATEGORIES'] as $cat) {
					if (!is_numeric($cat)) continue;
					$this_index =& $this->get_index($cat);
					$this_index->setitem($key, $seek);
				}
			}

			if ($del) {
				foreach($del as $cat) {
					$this_index =& $this->get_index($cat);
					$this_index->delitem($key);
				}
			}

			return true;

		}

		function delete($id) {
			$key = entry_idtokey($id);

			$main =& $this->get_index();
			$main->delitem($key);

			$entry = entry_parse($id);
			if (isset($entry['categories']) && is_array($entry['categories'])) {
				foreach ($entry['categories'] as $cat) {
					if (!is_numeric($cat)) continue;
					$this_index =& $this->get_index($cat);
					$this_index->delitem($key);
				}
			}

		}

	}

	class _entry_indexer extends cache_filelister {
		
		var $_varname = 'cache';
		var $_cachefile = null;
		var $_directory = CONTENT_DIR;
		
		function entry_indexer() {
			$this->_cachefile = CACHE_DIR . CACHE_FILE;
			
			return parent::cache_filelister();
		}
		
		function _checkFile($directory, $file) {
			$f = "$directory/$file";
				if ( is_dir($f) && ctype_digit($file)) {
					return 1;
				}
				
				if (fnmatch('entry*'.EXT, $file)) {
					$id=basename($file,EXT);
					$arr=entry_parse($id);
		
					$this->addEntry($id, $arr);
					
					return 0;
				}
		}
		
		
		function addEntry($id, $arr) {
		
		
			if ($arr) {
						// do_action('publish_post', $id, $arr);
						$this->_list[$id]=array(
							'subject' => $arr['subject'],
							'categories' => 
								(
								isset($arr['categories'])? 
									$arr['categories'] 
									: 
									array()
								)
							);
					
					}
					
			}
		
		
		function save() {
			do_action('cache_save');
			return parent::save();
		}
		
		function add($id, $val) {
			
			$this->_list[$id]=array('subject' => $val['SUBJECT'],
						'categories' => 
							(isset($val['CATEGORIES'])? 
							$val['CATEGORIES'] : array()));
						
			return $this->save();
		}
		
		function get($id) {
			if (isset($this->_list[$id]))
				return $this->_list[$id];
			else {
				//trigger_error("entry_lister: No such element \"$id\" in
					//list", E_USER_WARNING);
				return false;
			}
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
			$entry_index=& new entry_index;

		return $entry_index;
		
	}

	function &entry_cached_index($id_cat) {

		$F = INDEX_DIR.'index-'.$id_cat.'.dat';
		
		if (!file_exists($F)) {
			$o = false;	
		} else {
			$o =& new entry_cached_index($id_cat);
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
	
	function entry_dir($id) {
		if (!preg_match('|^entry[0-9]{6}-[0-9]{6}$|', $id))
			return false;
		$date = date_from_id($id);
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
		
		
		if (isset($arr['CATEGORIES']) && // fix to bad old behaviour:
					(trim($arr['CATEGORIES']) != '')) { 
		 
				
				$cats = (array)explode(',',$arr['CATEGORIES']);
				$arr['CATEGORIES'] = (array) $cats;
				
		
				
		} else $arr['CATEGORIES'] = array();
		
		// if (!is_array($arr['CATEGORIES'])) die();
		
		if (!isset($arr['AUTHOR'])) {
			global $fp_config;
			$arr['AUTHOR'] = $fp_config['general']['author'];
		}

		if ($raw) return $arr;
		return array_change_key_case($arr, CASE_LOWER);
		
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
		$obj =& new comment_indexer($id);

		$count = count($obj->getList());
		
		return $obj;
		
	}


	function entry_categories_encode() {
	
		if ($string = io_load_file(CONTENT_DIR . 'categories.txt')) {
			$lines = explode("\n", trim($string));
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
			
		}
	
		return false;
	
	
	}

	/*
	
	function entry_categories_print(&$lines, &$indentstack, &$result, $params) {

	
}

*/

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
	
	function entry_save($entry_cont, $id=null, $update_index = true) {

		global $post;
		
		$obj =& entry_init();
		
		if (!isset($entry_cont['date'])) {
			$entry_cont['date']=date_time();
		}
		
		$post = $entry_cont;

		$entry = array_change_key_case($entry_cont, CASE_UPPER);
		
		if (!$id) {
			$id = bdb_idfromtime(BDB_ENTRY, $entry['DATE']);
		}
		do_action('publish_post', $id, $entry_cont);
		
		$f = bdb_idtofile($id);
		
		$entry['CONTENT'] = apply_filters('content_save_pre', $entry['CONTENT']);
		$entry['SUBJECT'] = apply_filters('title_save_pre', $entry['SUBJECT']);

		$del = array();
		if ($arr = entry_parse($id)) {
			if (isset($entry['CATEGORIES']) && is_array($entry['CATEGORIES']))
				$del = array_diff($arr['categories'], $entry['CATEGORIES']);
		}
		
		$ok = ($update_index) ? $obj->add($id, $entry, $del) : true;
		
		if ($ok) {
		
				
			if (isset($entry['CATEGORIES'])) {
			
				if (is_array($entry['CATEGORIES']))
					$entry['CATEGORIES'] = implode(',',$entry['CATEGORIES']);
				else
					trigger_error("Failed saving entry. Expected 'categories' to be
							an array, found " . gettype($entry['CATEGORIES']), E_USER_ERROR);	
			}
			
			
			$str = utils_kimplode($entry);
				
			if (!io_write_file($f, $str)) {
				if ($update_index)
					 $obj->delete($id, $entry);
				return false;
			} else return $id;
			
		} 
		return false;
		
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
		$obj->delete($id);
		
		do_action('delete_post', $id);
		
		return fs_delete($f);
	}
	
	function entry_purge_cache() {
		$obj =& entry_init();
		$obj->purge();
	}
	//add_action('init', 

?>

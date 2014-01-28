<?php

	
	define('DRAFT_DIR', CONTENT_DIR . 'drafts/');
	
	class draft_indexer extends fs_filelister {
		
		var $_varname = 'cache';
		var $_cachefile = null;
		var $_directory = DRAFT_DIR;
		
		function draft_indexer() {
			$this->_cachefile = CACHE_DIR . 'draft_index.php';	
			return parent::fs_filelister();
		}
		
		
		function _checkFile($directory, $file) {
		
			$f = "$directory/$file";
				if ( is_dir($f) && ctype_digit($file)) {
					return 1;
				}
				
				if (fnmatch('entry*'.EXT, $file)) {
					$id=basename($file,EXT);
					$arr=draft_parse($id);
		
					//$this->add($id, $arr['subject']);
					$this->_list[$id] = $arr['subject'];
					
					return 0;
				}
		
		}
		
	}
	
	function &draft_init() {
		global $draftdb;
		if (!isset($draftdb))
			$draftdb = new draft_indexer;
		return $draftdb;
	}
	
		
	function draft_getlist() {
		
		static $list = array();
		
		if (!$list) {
			$obj =& draft_init();
			$list = $obj->getList();
			krsort($list);
		}
		
		return $list;
		
	}	
	
	function draft_parse($id) {
	
		if ($fname=draft_exists($id)) {
		
			$entry = io_load_file($fname);
			
			$entry = utils_kexplode($entry);
			if (!isset($entry['categories']))
				$entry['categories'] = array();
			else 
				$entry['categories'] = explode(',', $entry['categories']);
				
			return $entry;
		}
		return array();
	}

	
	function draft_save(&$entry, $id=null, $update_index = false, $update_date=false) {
	
		if (!$id) {
			$id = bdb_idfromtime('entry', $entry['date']);
		}
	
		$ed = entry_dir($id);
		$dd = draft_dir($id);
		
		if (file_exists($ed.EXT)) {
		
			// move collateral files
			@rename($ed, $dd);
			
			if ($update_index) {
				// delete normal entry
				fs_delete($ed.EXT);
			
				// remove from normal flow
				$o =& entry_init();
				$o->delete($id, null);
			}
	
		}

		$new_entry = entry_prepare($entry);
		if ($new_entry['categories'])
				$new_entry['categories']=implode(',', $entry['categories']);
		else unset($new_entry['categories']);

		$string = utils_kimplode($new_entry);
		
	
		if (!io_write_file($dd.EXT, $string)) {
				return false;
		} else return $id;
		
		return false;
		
	}
	
	function draft_dir($id) {
		if (!preg_match('|^entry[0-9]{6}-[0-9]{6}$|', $id))
			return false;
		//$date = date_from_id($id);
		//$f = CONTENT_DIR . "{$date['y']}/{$date['m']}/$id";
		return DRAFT_DIR . $id; 
		//return $f;
	
	
	}
	
	function draft_exists($id) {
	
		$dir = draft_dir($id);
		if (!$dir)
			return false;
	
		$f = $dir .EXT;
		if (file_exists($f))
			return $f;
		
		return false;
	}

	function draft_delete($id) {
		$dir = draft_dir($id);
		
		$f=$dir.EXT;
		if (!file_exists($f))
			return false;
		
		//$draftdb =& draft_init();
		//$draftdb->delete($id);
		fs_delete_recursive($dir);
		
		return fs_delete($f);
	}
	
	/*
	function draft_from_entry($entryid) {
		$dir = entry_dir($entryid);
		//$dir2 = str_replace('entry', 'draft', $dir);
		$dir2 = draft_dir($entryid);
		@rename($dir, $dir2);
		@rename($dir.EXT, $dir2.EXT);
	}
	*/
	
	function draft_to_entry($draftid) {
	
		$dir = draft_dir($draftid);
		$dir2 = entry_dir($draftid);
		
		@rename($dir, $dir2);
		draft_delete($draftid);
	}
	
	
	function smarty_block_draftlist($params, $content, &$smarty, &$repeat) {
		global $fpdb;
		
		if ($list = draft_getlist()) {
			$smarty->assign('draft_list', $list);
			return $content;
		}
		
	}
	
	
	function smarty_block_draft($params, $content, &$smarty, &$repeat) {
		
		static $list = array();
		
		$smarty->assign(array(	'subject'=>'',
					'content'=>'',
					'date'=>'',
					'author'=>'',
					'version'=>'',
					'id'=>''
					)
				);
		$arr =& $smarty->get_template_vars('draft_list');
		
		$id = $subject = null;
		if ($arr)
			list($id, $subject)=each($arr);
		
		if ($id){
			$smarty->assign('subject', $subject);
			$smarty->assign('id', $id);
		}
		
		$repeat = (bool) $id;

		return $content;	
	}
	
	
	
	$smarty->register_block('draft_block', 'smarty_block_draftlist');
	$smarty->register_block('draft', 'smarty_block_draft');


?>

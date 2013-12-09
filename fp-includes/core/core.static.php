<?php

	
	define('STATIC_DIR', CONTENT_DIR . 'static/');
	
	
	class static_indexer extends fs_filelister {
		
		var $_directory = STATIC_DIR;
		
		function _checkfile($directory, $file) {
			array_push($this->_list, basename($file,EXT));
			return 0;
		}
		
	}
	
	function static_getlist() {
		
		$obj = new static_indexer;
		$list = $obj->getList();
		return $list;
		
	}	
	
	function static_parse($id) {
		if (!static_isvalid($id)) return false;
		
		if ($fname=static_exists($id)) {
			$entry = io_load_file($fname);
			return (utils_kexplode($entry));
		}
		return array();
	}
	
	function static_isvalid($id) {
		return preg_match('![^./\\\\]+!', $id);
	}
		
	
	function static_save($entry, $id, $oldid=null) {
		if (!static_isvalid($id)) return false;
		
		$fname = STATIC_DIR . $id . EXT;
		
		$entry['content'] = apply_filters('content_save_pre', $entry['content']);
		$entry['subject'] = apply_filters('title_save_pre', $entry['subject']);
	
		$str = utils_kimplode($entry);

		if (io_write_file($fname, $str)) {
			if ( $oldid && $id!=$oldid && $fname = static_exists($oldid)) {
				$succ = static_delete($oldid) ;
				return ($succ !== false && $succ !== 2);
			}
			return true;
		}
		return false;
	}
	
	function static_exists($id) {
		if (!static_isvalid($id)) return false;
		
		$fname = STATIC_DIR . $id . EXT;
		
		if (file_exists($fname))
			return $fname;
		
		return false;
	}

	function static_delete($id) {
		if (!static_isvalid($id)) return false;
		
		return fs_delete(STATIC_DIR . $id . EXT);
	}
	
	
	
	function smarty_block_statics($params, $content, &$smarty, &$repeat) {
		global $fpdb;
		
		/*
		$show = false;
		
		if (isset($params['alwaysshow']) && $params['alwaysshow']) {
			return $content;
		}
		*/
		return $content;
		
	}
			
	
	function smarty_block_static($params, $content, &$smarty, &$repeat) {
		global $fpdb;
		static $pointer = 0;
		
		// clean old variables
		
		$smarty->assign(array(	'subject'=>'',
					'content'=>'',
					'date'=>'',
					'author'=>'',
					'version'=>'',
					'id'=>''
					)
				);
		
		if ($arr=$smarty->get_template_vars('static_page')){
			$smarty->assign('id', $smarty->get_template_vars('static_id'));
			if (THEME_LEGACY_MODE)
				theme_entry_filters($arr);
			$smarty->assign($arr);
			return $content;
		}
				
		if (isset($params['content']) && is_array($params['content']) && $params['content']) {
			//foreach ($params['entry'] as $k => $val)
			$smarty->assign($params['content']);
			return $content;
		}
		
		if (isset($params['alwaysshow']) && $params['alwaysshow']) {
			return $content;
		}
		
		$list = $smarty->get_template_vars('statics');
		
		
		if(isset($list[$pointer])) {
			//foreach ($entry as $k => $val)
			$smarty->assign(static_parse($list[$pointer]));
			$smarty->assign('id', $list[$pointer]);
			
			$pointer++;
			
			$repeat = true;
			
		} else {
			$repeat = false;
		}
		

		return $content;
		
	}
	
	
	$_FP_SMARTY->register_block('statics', 'smarty_block_statics');
	$_FP_SMARTY->register_block('static_block', 'smarty_block_statics');
	$_FP_SMARTY->register_block('static', 'smarty_block_static');

?>

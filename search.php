<?php

if (!defined('MOD_INDEX')) {
	// Example of use
	require_once 'defaults.php';
	require_once (INCLUDES_DIR.'includes.php');
	
	require(SMARTY_DIR . 'SmartyValidate.class.php');



		system_init();
		search_main();
		search_display();
}

	function search_title($title, $sep) {
		global $lang;
		return "$title $sep {$lang['search']['head']}";
	}
	

	function search_display() {
		global $smarty;
		theme_init($smarty);
		
		$smarty->display('default.tpl');
			
		unset($smarty);
			
		do_action('shutdown');
			

	}


	function search_main() {
		
		global $lang, $smarty;


		add_action('wp_title', 'search_title', 0, 2);
		
		if(empty($_GET)) {
			// display form
			$title = $lang['search']['head'];
			$content = "shared:search.tpl"; 
		} else {    
			// validate
			if(isset($_GET['q']) && $kw = trim($_GET['q'])) {
				$title = $lang['search']['head'];
				$content = "shared:search_results.tpl"; 

				$kw = strtolower($kw);	
				search_do($kw);
				
			} else {
				$smarty->assign('error', $lang['search']['error']['keywords']);
				$title = $lang['search']['headres'];
				$content = "shared:search.tpl"; 
				
			}
		}
		
		$smarty->assign(array('subject'=>$title, 'content'=>$content));
		return 'default.tpl';
	}
	
	
	function search_do($keywords) {
		
		global $smarty, $srchresults;
		// get parameters
			
			$srchkeywords = $keywords;
			
			$params = array();
			$params['start']=0;
			$params['count']=-1;
			
			(!empty($_GET['Date_Day'])) && ($_GET['Date_Day']!='--')? $params['d'] = $_GET['Date_Day']: null;
			isset($_GET['Date_Month']) && ($_GET['Date_Month']!='--')? $params['m'] = $_GET['Date_Month'] : null;
			!empty($_GET['Date_Year']) && ($_GET['Date_Year']!='--')? $params['y'] = substr($_GET['Date_Year'], 2) : null;
			
			isset($_GET['cats'])? $params = $_GET['cats']: null;
			
			
			$params['fullparse'] = false;
				
			if(!empty($_GET['stype']) && $_GET['stype']=='full') {
				$params['fullparse'] = true;
			}
			
			$srchparams = $params;
			
			
			$list = array();
				
			$q = new FPDB_Query($params, null);
			
			
			while ($q->hasMore()) {
				
				list($id, $e) = $q->getEntry();

				$match = false;
						
				if ($keywords == '*') {
					$match = true;
				} else {
					$match = strpos(
									strtolower($e['subject']),
									$keywords
							) !== false;
					
					if (!$match && $params['fullparse']) {
						
						$match = strpos(
									strtolower($e['content']),
									$keywords
								) !== false;
						
					}
							
				}
				
				if ($match)
					$list[$id] = $e;
			}
			
			
			$smarty->register_block('search_result_block', 'smarty_search_results_block');
			$smarty->register_block('search_result', 'smarty_search_result');
			
			if (!$list)
				$smarty->assign('noresults', true);
			
			
			$srchresults = $list;
		
	
	}
	
	function smarty_search_results_block($params, $content, &$smarty, &$repeat) {
	
		global $srchresults;
		
		if ($srchresults) {
			return $content;
		}
		
		
	}
	
	function smarty_search_result($params, $content, &$smarty, &$repeat) {
		
		global $srchresults, $post;
		
		$repeat = false;
		
		if (list($id, $e) = each($srchresults)) {
			$smarty->assign('id', $id);
			$post = $e;
			$smarty->assign($e);
			$repeat = true;
			return $content;
		}
	
		return	$content;
	}

	/*
	class rawsearchbot extends fs_filelister {
		
		var $_varname = 'cache';
		var $_cachefile = null;
		var $_directory = CONTENT_DIR;
		var $_constrained_list = array();
		
		function rawsearchbot($keywords, $fullparse, $searchjusthese=array()) {
			
			$this->keywords = strtolower($keywords);
			$this->fullparse = $fullparse;
			$o = new entry_indexer;
			$this->entry_arr = $o->getList();
			
			
			if ($searchjusthese) {
				$this->_constrained_list = $searchjusthese;
			}
			return parent::fs_filelister();
			
		}
		
		function _checkFile($directory, $file) {
			
			$f = "$directory/$file";
			
			if ( is_dir($f) && ctype_digit($file)) {
					return 1;
			}
			
			if (fnmatch('entry*'.EXT, $file)) {
				$id=basename($file,EXT);
				$arr = $this->entry_arr[$id];
				
				if (in_array('draft',$arr['categories'])) return 0;
				
				if ($this->fullparse) {
					$str = strtolower(io_load_file(bdb_idtofile($id, BDB_ENTRY)));
					
					if (strpos($str, $this->keywords)!==false) {
						$this->_list[$id] = $arr['subject'];
					}
					
				} else {
					if (strpos(strtolower($arr['subject']), $this->keywords)!==false) {
						$this->_list[$id] = $arr['subject'];
					}
				}
				return 0;
			}
		}
		
	}
	*/
	
?>

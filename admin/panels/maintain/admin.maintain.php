<?php

/**
 * add entry panel
 *
 * Type:     
 * Name:     
 * Date:     
 * Purpose:  
 * Input:
 *         
 * @author NoWhereMan <real_nowhereman at users dot sf dot com>
 *
 */
 	
	/* utility class */
		class tpl_deleter extends fs_filelister {
		
		function tpl_deleter() {
			
			//$this->smarty = $GLOBALS['_FP_SMARTY'];
			
			$this->_directory = CACHE_DIR;
			parent::fs_filelister();
		}
	
		function _checkFile($directory, $file) {
				
				if ($file != CACHE_FILE) {
					array_push($this->_list, $file);
					fs_delete("$directory/$file");
				}
				//trigger_error($file, E_USER_NOTICE);
			return 0;
		}
	
	}
	/*********************/
	
	
	
 	class admin_maintain extends AdminPanel {
		var $panelname = 'maintain';
		var $actions = array('default'=>false, 'updates'=>false);
	}
 	
	
	class admin_maintain_updates extends AdminPanelAction {
		
		var $web = 'http://www.flatpress.org/fp/VERSION';
		var $fpweb = 'http://flatpress.nowhereland.it/downloads.php';
		var $sfweb = 'http://sourceforge.net/project/showfiles.php?group_id=157089';
	
		function main() {
			$success = -1;
			$ver = array(
					'stable'=>'unknown',
					'unstable'=>'unknown',
				);
		
			$f = @fopen($this->web, 'r');
			
			if ($f) {
				$file='';
				while(!feof($f)) {
					$file .= fgets($f);
				}
				if ($file){
					$ver = utils_kexplode($file);
					
					if (strcmp($ver['STABLE'], SYSTEM_VER)>0)
						$success = 1;
					else
						$success = 2;
					
					
					$ver = array_change_key_case($ver, CASE_LOWER);
					
				}
			}
			
			
			$this->smarty->assign('updates', $ver);
			$this->smarty->assign('fpweb', $this->fpweb);
			$this->smarty->assign('sfweb', $this->sfweb);		
			$this->smarty->assign('success', $success);
			
		}
		
	}
	
	class admin_maintain_default extends AdminPanelAction {
	
		var $commands = array('do');
		
		function dodo($do) {
		
			switch ($do) {
			case 'purgecache': {
					$obj =& entry_init();
					$obj->purge();
					if (!file_exists(CACHE_DIR))
						fs_mkdir(CACHE_DIR);
						
					$this->smarty->assign('success', 1);
					return PANEL_REDIRECT_CURRENT;
				}
			case 'restorechmods': {
					$this->smarty->assign('files',fs_chmod_recursive());
					$this->smarty->assign('success', 1);
					return PANEL_NOREDIRECT;
				}
			case 'purgetplcache': {
				$tpldel = new tpl_deleter;
				unset($tpldel);
				$this->smarty->cache_dir = CACHE_DIR.'cache/';
				$this->smarty->caching = 0;
				$this->smarty->clear_all_cache();
				$this->smarty->clear_compiled_tpl();
				$this->smarty->compile_check = true;
				$this->smarty->force_compile = true;
				$this->smarty->assign('success', 1);
			
				if (!file_exists(CACHE_DIR))
					fs_mkdir(CACHE_DIR);
			
				return PANEL_NOREDIRECT;
				}
			case 'phpinfo': {
				ob_start();                                                                                                       
				phpinfo();                                                                                                       
				$info = ob_get_contents();                                                                                       
				ob_end_clean();                                                                                                   

				$this->smarty->assign('phpinfo', preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $info));
				}
				
				return PANEL_NOREDIRECT;
			}
			
		
		
		
		}
		
	 	function main() {
		
		}
		
	}

?>

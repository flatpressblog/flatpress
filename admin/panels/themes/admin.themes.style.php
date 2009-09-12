<?php

	class admin_themes_obj_style_idx extends fs_filelister {

		function admin_themes_obj_style_idx() {
			$this->_directory = THEMES_DIR . THE_THEME;
			parent::fs_filelister();
		}
		
		function _checkFile($d, $f) {
			$p = "$d/$f";
			if (is_dir($p) && file_exists($p.'/style.conf.php'))
				$this->_list[] = $f;
		}
		
	}


	class admin_themes_style extends AdminPanelAction {
		
		var $defprev = '';
		var $commands = array('select');
	
		function style_list() {
			global $fp_config;
			
			$o = new admin_themes_obj_style_idx;
			$list = $o->getList();
			sort($list);

			$info = array();
			$based = THEMES_DIR . THE_THEME;
			
			foreach ($list as $sty) {
				
				// don't show current theme
				//if ($fp_config['general']['theme'] == $thm)
				//	continue;
			
				$style = array();
				$d = "$based/$sty";
				
				
				$f = $d . '/style.conf.php';
				
				$style = admin_theme_data($f, $sty, $this->defprev);
				
				$info[] = $style;
			}
			
			return $info;
		}
		
		function setup() {
			global $fp_config;
			$this->defprev = BLOG_BASEURL . ADMIN_DIR . 'panels/'. ADMIN_PANEL .'/preview-default.png';
			
			if (isset($fp_config['general']['style']))
				$this->smarty->assign('current_style', 
					admin_theme_data(THEMES_DIR . THE_THEME . '/' 
					.$fp_config['general']['style'] .'/style.conf.php', 
					THE_THEME, $this->defprev));

			$this->smarty->assign('available_styles', $this->style_list());
		}
		
		function doselect($id) {
			global $fp_config;
			
			if ($id) {
				$id = sanitize_title($id);
				if (theme_style_exists($id)) {
					$fp_config['general']['style'] = $id;
				
					$return = config_save() ? 1 : -1;
				} else {
					$return = -2;
				}
				
				$this->smarty->assign('success', $return);
				
				return 2;	
			}
		}
		
		
		function onerror() {
			$this->main();
			return 0;	
		}
		
		function cleartplcache() {
				// if theme was switched, clear tpl cache
			
				$tpl = new tpl_deleter();
				
				$tpl->getList();
				
				
		}

	}
	
?>

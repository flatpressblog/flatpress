<?php

	class admin_themes extends AdminPanel {
		var $panelname = 'themes';
		var $actions = array('default' => true);
		
		function admin_themes(&$smarty) {
			global $theme;
			
			if ($theme['version'] > 0.703)
				$this->actions['style'] = true;
			
			parent::AdminPanel($smarty);
				
		}
		
	}
	
	function admin_theme_data( $theme_file, $theme_id, $defprev ) {
		
		
		$theme_data = io_load_file($theme_file);
		$theme_data = str_replace ( '\r', '\n', $theme_data ); 
		preg_match( '/(Theme|Style) Name:(.*)/i', $theme_data, $theme_name );
		preg_match( '/(Theme|Style) URI:(.*)/i', $theme_data, $theme_uri );
		preg_match( '|Description:(.*)|i', $theme_data, $description );
		preg_match( '|Author:(.*)|i', $theme_data, $author_name );
		preg_match( '|Author URI:(.*)|i', $theme_data, $author_uri );
		preg_match( '|Template:(.*)|i', $theme_data, $template );
		if ( preg_match( '|Version:(.*)|i', $theme_data, $version ) )
			$version = trim( $version[1] );
		else
			$version ='';
		if ( preg_match('|Status:(.*)|i', $theme_data, $status) )
			$status = trim($status[1]);
		else
			$status = 'publish';

		$description = @wptexturize( trim( $description[1] ) );

		$name = @$theme_name[1]? $theme_name[2] : $theme_id;
		$name = trim( $name );
		$theme = $name;
		$theme_uri = trim( @$theme_uri[2] );

		if ( '' == @$author_uri[1] ) {
			$author = trim( @$author_name[1] );
		} else {
			$author = '<a href="' . trim( $author_uri[1] ) . '">' . 
				trim( $author_name[1] ) . '</a>';
		}
		
		if (file_exists($f = dirname($theme_file). '/preview.png'))
					$prev = $f;
				else
					$prev = $defprev;
					
		//$theme['name'] = isset($theme['name'])? $theme['name'] : ($thm);		

		return 
			array( 	'name' => $name, 
					'id'   => $theme_id,
					'title' => $theme, 
					'www' => $theme_uri, 
					'description' => $description, 
					'author' => $author, 
					'version' => $version, 
					'template' => $template, 
					'status' => $status,
					'preview' => $prev
			);
		}


 	
	class admin_themes_default extends AdminPanelAction {
		
		var $defprev = '';	
		var $commands = array('select');
	
		function theme_list() {
			global $fp_config;
			$list = theme_list();
			$info = array();
			foreach ($list as $thm) {
				
				// don't show current theme
				if ($fp_config['general']['theme'] == $thm)
					continue;
			
				$theme = array();
				$d = THEMES_DIR . $thm;
				
				
				$f = $d . '/theme.conf.php';
				
				if (!file_exists($f))
					continue;
				
				$theme = admin_theme_data($d . '/theme.conf.php', $thm, $this->defprev);
				
				$info[] = $theme;
			}
			
			return $info;
		}
		
		function setup() {
			$this->defprev = BLOG_BASEURL . ADMIN_DIR . 'panels/'. ADMIN_PANEL .'/preview-default.png';
				
			$current_theme = admin_theme_data(THEMES_DIR . THE_THEME . '/theme.conf.php', THE_THEME, $this->defprev);
			$this->smarty->assign('current_theme', $current_theme);
			
			$this->smarty->assign('available_themes', $this->theme_list());
		}
		
		
		function doselect($id) {
			global $fp_config;
			//$id = isset($_GET['select'])? $_GET['select'] : null;
			if ($id) {
				$id = sanitize_title($id);
				if (theme_exists($id)) {
					$fp_config['general']['theme'] = $id;
					
					unset($fp_config['general']['style']);	
				
					//$t = theme_loadsettings();
					//$fp_config['general']['style'] = $t['default_style'];
						
					$return = config_save() ? 1 : -1;
				} else {
					$return = -2;
					
				}
				
				$this->smarty->assign('success', $return);
				
				return 1;
				
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

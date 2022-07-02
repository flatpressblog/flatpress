<?php

	class admin_config extends AdminPanel {
		var $panelname = 'config';
	}
 	
	class admin_config_default extends AdminPanelActionValidated {
		
		var $validators = array(
			// not needed anymore !
			// array('blog_root', 'blog_root', 'notEmpty', false, false, 'trim'),
			array('www', 'www', 'notEmpty', false, false, 'trim'),
			// ...
			array('title', 'title', 'notEmpty', false, false, 'trim'),
			//array('subtitle', 'subtitle', 'notEmpty', false, false, 'trim'),
			//array('blogfooter', 'blogfooter', 'notEmpty', false, false, 'trim'),
			array('email', 'email', 'isEmail', false, false, 'trim'),
			array('maxentries', 'maxentries', 'isInt', false, false, 'trim'),
			
			array('timeoffset', 'timeoffset', 'isNumber', false, false, 'trim'),
			array('timeformat', 'timeformat', 'notEmpty', false, false, 'trim'),
			array('dateformat', 'dateformat', 'notEmpty', false, false, 'trim'),
			array('dateformatshort', 'dateformatshort', 'notEmpty', false, false, 'trim'),
			
			array('lang', 'lang', 'notEmpty', false, false, 'trim'),
			array('charset', 'charset', 'notEmpty', false, false, 'trim'),
			
			
		);
		
		 var $events = array('save');
		
	
		function setup() {
			$this->smarty->assign('themes', theme_list());
			$this->smarty->assign('lang_list', lang_list());
			
			$static_list = array();
			
			foreach(static_getlist() as $id) {
				$static_list[$id] = static_parse($id);
			}
			
			$this->smarty->assign('static_list', $static_list);
			
		}
		
		
		function onsave() {
		
			global $fp_config;
			$l = explode(',',$_POST['lang']);
			$fp_config['general'] = array(
			//'BLOG_ROOT'	=> $_POST['blog_root'],
				'www'	=> $_POST['www'],
				'title' => html_entity_decode(stripslashes($_POST['title'])),
				'subtitle' => html_entity_decode(stripslashes($_POST['subtitle'])),
				'footer' => html_entity_decode(stripslashes($_POST['blogfooter'])),
				'author' => $_POST['author'],
				'email' => $_POST['email'],
				'startpage' => ($_POST['startpage'] == ':NULL:')? null : $_POST['startpage'],
				'maxentries' => $_POST['maxentries'],
				// 'voting' => $_POST['voting'],
				'notify' => isset($_POST['notify']),
				/* preserve the following */
				'theme'	=> $fp_config['general']['theme'],
				'style'	=> @$fp_config['general']['style'],
				'blogid' => $fp_config['general']['blogid'],
				'charset'=> 'utf-8',
	
			);
			
			$fp_config['locale'] = array(
				'timeoffset' => $_POST['timeoffset'],
				'timeformat' => $_POST['timeformat'],
				'dateformat' => $_POST['dateformat'],
				'dateformatshort' => $_POST['dateformatshort'],
				'charset'	 => $_POST['charset'],
				'lang'		 => $_POST['lang']
			);
			

			// 'LANG'	=> $l[0],
			// 'CHARSET'=> $l[1],
	
			
				

			
			$success = config_save()? 1: -1;
			
			$this->smarty->assign('success', $success);
			
			return 1;
			
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

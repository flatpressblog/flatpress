<?php

/**
 * edit entry panel
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
 	
	
 

 	class admin_entry_write extends AdminPanelActionValidated {
		
		var $validators = array(
				array('subject', 'subject', 'notEmpty', false, false, 'trim,stripslashes'),
				array('content', 'content', 'notEmpty', false, false, 'stripslashes'),
			); 
		
		var $events = array('save', 'preview', 'savecontinue');
		
		function _makePreview($arr, $id=null) {
			
			if (!$id) {
				$arr['subject'] = apply_filters('title_save_pre', $arr['subject']);
				$arr['content'] = apply_filters('content_save_pre', $arr['content']);
			}
			
			
			$this->smarty->assign('post', $arr);
			
			if (THEME_LEGACY_MODE) {
				theme_entry_filters($arr, $id);
			}
			
			$arr = array_change_key_case($arr, CASE_LOWER);
			
			$this->smarty->assign('entry', $arr);
			$this->smarty->assign('preview', true);
			
		}
 	
		function makePageTitle($title, $sep) {
			global $lang;
			return "$title $sep {$lang['admin']['entry']['write']['head']}";
		}
	
		function _getCatsFlags() {
				
			//$this->smarty->assign('saved_categories', entry_categories_format());
			$this->smarty->assign('saved_flags', entry_flags_get());
			
		}
		
		function setup() {
		
				$this->id = @$_REQUEST['entry'];		
				$this->smarty->assign('id', $this->id);
				
				
		}
		
		function main() {
			
			global $lang;
			
			$id = $this->id;
			
			if (isset($_REQUEST['entry'])) {
				
				$arr = draft_parse($id);
				
				if (!$arr)
					$arr = entry_parse($id);
				else 
					$this->smarty->assign('draft', true);
				
				// if entry does not exists
				if ($arr) {
					$this->_makePreview($arr, $id);		
				} 
		
			}
			
			$this->_getCatsFlags();
			add_filter('wp_title', array(&$this, 'makePageTitle'), 10, 2);
				
		}
		
		function _getposteddata() {

			$arr['version'] = system_ver();
			$arr['subject'] = ($_POST['subject']);
			$arr['content'] = ($_POST['content']);
			$author = user_get();
			$arr['author'] = $author['userid'];
			$arr['date'] = !empty($_POST['timestamp'])?$_POST['timestamp']:date_time();
			
			$cats = !empty($_POST['cats'])?$_POST['cats']:array();
			$flags = !empty($_POST['flags'])?$_POST['flags']:array();
			
			$catids = array_merge(array_keys($flags), array_keys($cats));
			
			if ($catids)
				$arr['categories'] = $catids;
			
			return $arr;
		
		}
		
		function onsave($do_preview = false) {
			
			$id = $this->id;
			$data = $this->_getposteddata();			
			
			if (isset($data['categories']) && in_array('draft', $data['categories'])) {
				
				$success=draft_save($data, $id);
			} else {
				
				/* anyway issued */

				draft_to_entry($id);
				$success=entry_save($data, $id);
				
			}
			
			if ($success) sess_remove('entry');
			
			$this->smarty->assign('success',$success? 1:-1);
			
			if ($do_preview)
				$this->_makePreview($data);
			
			return 1;
		}
		
		
		function onpreview() {
			global $lang;
			
			$this->_makePreview($this->_getposteddata());		
			
			
			$this->_getCatsFlags();

			add_filter('wp_title', array(&$this, 'makePageTitle'), 10, 2);
			
			return 0;
			
			
		}
		
		function onsavecontinue() {
			global $lang;	
			$this->onsave(true);
			
			$this->_getCatsFlags();

			add_filter('wp_title', array(&$this, 'makePageTitle'), 10, 2);
			
		}
		
		
		function onerror() {
			$this->main();
			return 0;	
		}

	}

?>

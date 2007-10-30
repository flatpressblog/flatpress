<?php

/**
 * shared entry form
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
 	
 	
 	function shared_entry_form_setup(&$smarty) {
		$smarty->assign('form', ABS_PATH.ADMIN_DIR."panels/entry/shared.entry.form.tpl");
		admin_entry_cats_flags($smarty);
	
 	}
 	
 	function shared_entry_form_main() {
	
		SmartyValidate::register_validator('subject', 'subject', 'notEmpty', false, false, 'trim');
		SmartyValidate::register_validator('content', 'content', 'notEmpty', false, false);
	}
	
	
	function shared_entry_form_onsubmit() {
		
		$arr['version'] = system_ver();
		$arr['subject'] = stripslashes($_POST['subject']);
		$arr['content'] = stripslashes($_POST['content']);
		$author = user_get();
		$arr['author'] = $author['NAME'];
		$arr['date'] = !empty($_POST['timestamp'])?$_POST['timestamp']:time();
		
		$cats = !empty($_POST['cats'])?$_POST['cats']:array();
		$flags = !empty($_POST['flags'])?$_POST['flags']:array();
		
		$arr['categories'] = array_merge(array_keys($flags), array_keys($cats));
		
		//sess_add('entry', $arr);

		
		return $arr;
	
	}

?>

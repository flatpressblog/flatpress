<?php
	require_once 'defaults.php';
	require_once (INCLUDES_DIR.'includes.php');
	require SMARTY_DIR . 'SmartyValidate.class.php';
	
			
	function contact_form_validate() {

		
		$arr['version'] = system_ver();
		$arr['name'] = $_POST['name'];
		
		if (!empty($_POST['email']))
			($arr['email'] = $_POST['email']);
		if (!empty($_POST['url']))
			($arr['url'] = $_POST['url']);
		$arr['content'] = $_POST['content'];
		
		$arr['ip-address'] = utils_ipget();
		
		if (apply_filters('comment_validate', true, $arr))
			return $arr;
		else return false;
				
		
	}

	
	function contact_form() {
		
		global $smarty, $lang, $fp_config;

		if(empty($_POST)) {
			
			$smarty->assign('success', system_geterr('contact'));
			$smarty->assign_by_ref('panelstrings', $lang['contact']);
			
		
		// new form, we (re)set the session data
			SmartyValidate::connect($smarty, true);
		// register our validators
			SmartyValidate::register_validator('name', 'name', 'notEmpty', false, false, 'trim');
			SmartyValidate::register_validator('email', 'email', 'isEmail', true, false, 'trim');
			SmartyValidate::register_validator('www', 'url', 'isURL', true, false, 'trim');
			SmartyValidate::register_validator('content', 'content', 'notEmpty', false, false);
		} else {   
		       utils_nocache_headers();	
			// validate after a POST
			SmartyValidate::connect($smarty);

			if (!empty($_POST['url']) && strpos($_POST['url'], 'http://')===false) $_POST['url'] = 'http://'.$_POST['url'];
			
			
			// custom hook here!!
			// we'll use comment actions, anyway
			if(SmartyValidate::is_valid($_POST) && $arr=contact_form_validate()) {
				
				$msg = "Name: \n{$arr['name']} \n\n";
				
				if (isset($arr['email']))
					$msg .= "Email: {$arr['email']}\n\n";
				if (isset($arr['url']))
					$msg .= "WWW: {$arr['url']}\n\n";
				$msg .= "Content:\n{$arr['content']}\n";
				
					$success = @utils_mail(
						(
						isset($arr['email'])? 
							$arr['email'] 
							: 
							$fp_config['general']['email']
						), 
						"Contact sent through {$fp_config['general']['title']} ", $msg );

				system_seterr('contact', $success? 1 : -1);			
				utils_redirect(basename(__FILE__));	
			} else {
				$smarty->assign('values', $_POST);
			}
		}
	}

	
	function contact_main() {
		global $smarty;
		
		$lang = lang_load('contact');
		
		$smarty->assign('subject', $lang['contact']['head']);
		$smarty->assign('content', 'shared:contact.tpl');
		contact_form();
		
	}

	function contact_display() {
		global $smarty;
		
		contact_main();
		
		theme_init($smarty);
		
		$smarty->display('default.tpl');
			
		unset($smarty);
			
		do_action('shutdown');
			
	}

	
	system_init();
	contact_display();
	
?>

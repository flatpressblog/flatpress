<?php

	// Example of use
	require_once 'defaults.php';
	require_once (INCLUDES_DIR.'includes.php');
	
	require(SMARTY_DIR . 'SmartyValidate.class.php');
	
	
	$tpl = 'default.tpl';
		
	function main() {
		global $lang, $smarty;
		
		if (user_loggedin()) {
			
			if(isset($_GET['do']) && ($_GET['do']=='logout')) {
				user_logout();
				
				function myredirect() {
					login_redirect('index.php');
				}
					
				add_filter('wp_head', 'myredirect');
				
				$content = (SHARED_TPLS . 'login_success.tpl');
				
			} elseif (user_loggedin()) {
			
				function myredirect() {
					login_redirect('index.php');
				}
					
				add_filter('wp_head', 'myredirect');
				
				$content = (SHARED_TPLS . 'login_success.tpl');
		
			} else {

				utils_redirect();
				
			}
			
		} elseif (sess_remove('logout_done')) {
				
				function myredirect() {
					login_redirect('index.php');
				}
					
				add_filter('wp_head', 'myredirect');
				
				$content = (SHARED_TPLS . 'login_success.tpl');

		
		
		} elseif(empty($_POST)) {
		
		// new form, we (re)set the session data
			SmartyValidate::connect($smarty, true);
		// register our validators
			SmartyValidate::register_validator('userid', 'user', 'notEmpty', false, false, 'trim');
			SmartyValidate::register_validator('pwd', 'pass', 'notEmpty', false, false, 'trim');
			SmartyValidate::register_validator('password', 'user:pass', 'isValidPassword', false, false);
			
		// display form
			$content = (SHARED_TPLS . 'login.tpl'); 
		} else {    
			// validate after a POST
			SmartyValidate::connect($smarty);
			if(SmartyValidate::is_valid($_POST)) {
				SmartyValidate::disconnect();
				
				// sess_add('login_do', true);
				// utils_redirect();
				utils_redirect('login.php');
								
			} else {
				$smarty->assign($_POST);
				$content = (SHARED_TPLS . 'login.tpl');
			}
		}
		
		// Set page title and content
		// first parameter is Title, second is content.
		// Content can be both a shared tpl or raw html content; in this last case
		// you have to set the third optional parameter to true
		
		$smarty->assign('subject', $lang['login']['head']);
		$smarty->assign('content', $content);

	}


	
	
	function login_redirect($url, $secs=5){
		echo '<meta http-equiv="refresh" content="'."$secs;url=$url".'" />';
	}
	
	function login_title($title, $sep) {
		global $lang;
		return $title = "$title $sep {$lang['login']['head']}";
	}
	
	add_filter('wp_title', 'login_title', 10, 2);
	
			

	system_init();
	main();
	theme_init($smarty);
	$smarty->display('default.tpl');
	
	
?>
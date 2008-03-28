<?php

	// Example of use
	require_once 'defaults.php';
	require_once (INCLUDES_DIR.'includes.php');
	
	require(SMARTY_DIR . 'SmartyValidate.class.php');
	
	
	$tpl = 'default.tpl';

	function login_validate() {
		global $smarty, $lang;

		$user = trim(@$_POST['user']);
		$pass = trim(@$_POST['pass']);

		$error = array();
		$lerr =& $lang['login']['error'];

		if (!$user) {
			$error['user'] = $lerr['user']; 
		}

		if (!$pass) {
                 	$error['pass'] = $lerr['pass'];
		}

		if (!$error && !user_login($user, $pass)) {
			$error['match'] = $lerr['match']; 
		}

		if ($error) {
			$smarty->assign('error', $error);
			return  0;
		}

		return 1;

	}
		
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
		       $content = (SHARED_TPLS . 'login.tpl'); 
		} else {    
			// validate after a POST
			if(login_validate()) {
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

<?php
	

	// aggiungere nuovo protocollo per template


	include(ADMIN_DIR.'panels/admin.defaultpanels.php');
	include(ADMIN_DIR.'includes/panels.prototypes.php');
	require(SMARTY_DIR . 'SmartyValidate.class.php');
 
	utils_nocache_headers();
	
	define('MOD_ADMIN_PANEL',1);
	
	function wp_nonce_ays() {
		die('We apologize, an error occurred.');
	}
	
	/*
	function admin_is_user_loggedin() {
		return ($u=user_loggedin()) && utils_checkreferer() ;
	}
	*/
	
	
	function main() {
			
		// general setup
		
		global $panel, $action, $lang, $smarty, $fp_admin, $fp_admin_action;
		
		$panels = admin_getpanels();
		
		$panel = (isset($_GET['p']))? $_GET['p'] :  $panels[0];
		define('ADMIN_PANEL', $panel);
		$smarty->assign('panel', $panel);

		if (!admin_panelexists($panel)) 
			trigger_error('Requested panel does not exists!', E_USER_ERROR);
			
		
		$panelprefix = "admin.$panel";
		$panelpath = ADMIN_DIR."panels/$panel/$panelprefix.php";
		
		
		$fp_admin = null;
		
		if (file_exists($panelpath)) {
		
			include($panelpath);
			$panelclass = "admin_$panel";
			
			if (!class_exists($panelclass))
				trigger_error("No class defined for requested panel", E_USER_ERROR);
				
			$fp_admin = new $panelclass($smarty);
		
		}

		/* check if user is loggedin */
		
		if (!user_loggedin()) {
			utils_redirect("login.php");
			die();
		}
	
		
		$action = isset($_GET['action'])? $_GET['action'] : 'default';
		if (!$fp_admin)
			return;
		
		$fp_admin_action = $fp_admin->get_action($action);
		
		
		define('ADMIN_PANEL_ACTION', $action);
		$smarty->assign('action', $action);
		$panel_url = BLOG_BASEURL . "admin.php?p={$panel}";
		$action_url = $panel_url . "&action={$action}";
		$smarty->assign('panel_url', $panel_url);
		$smarty->assign('action_url', $action_url);
				
	
		if (!empty($_POST))
			check_admin_referer("admin_{$panel}_{$action}");
				
			
			
		
 		$smarty->assign('success', sess_remove("success_{$panel}"));
 		$retval = $fp_admin_action->exec();
				
		if ($retval > 0) { // if has REDIRECT option
			// clear postdata by a redirect
			
			sess_add("success_{$panel}", $smarty->get_template_vars('success'));
			$smarty->get_template_vars('success');
			
			$to_action = $retval > 1 ? ('&action=' . $action) : '';
			$with_mod = isset($_GET['mod'])? ('&mod=' . $_GET['mod']) : ''; 
			$with_arguments = '';

			if ($retval == PANEL_REDIRECT_CURRENT) {
				foreach ($fp_admin_action->args as $mandatory_argument) {
					$with_arguments .= '&' . $mandatory_argument .
								'=' . $_REQUEST[$mandatory_argument];
				}
			}

			$url = "admin.php?p={$panel}{$to_action}{$with_mod}{$with_arguments}";
			utils_redirect($url);
			
		}
		
		$smarty->register_modifier('action_link', 'admin_filter_action');
		$smarty->register_modifier('cmd_link', 'admin_filter_command');

	}
	
	// smarty tag
	function admin_filter_action($string, $action) {
		if (strpos($string, '?')===false)
			return $string .= "?action={$action}";
		else
			return $string .= wp_specialchars("&action={$action}");
	}
	
	// smarty tag
	function admin_filter_command($string, $cmd, $val) {
	
		global $panel, $action;
		
		$arg = $cmd? "&{$cmd}" : $cmd;
		
		return wp_nonce_url("{$string}{$arg}={$val}", "admin_{$panel}_{$action}_{$cmd}_{$val}");
		
	}
	
	
	function admin_panelstrings($panelprefix) {
	
		global $lang, $smarty;
		
		lang_load('admin');
		lang_load($panelprefix);
	
		$smarty->assign('subject', $lang['admin']['head']);
		$smarty->assign('menubar', admin_getpanels());
			
		add_filter('wp_title', 'admin_panel_title', 10, 2);
	}
	
	function admin_panel_title($title, $sep) {
	
		global $lang, $panel;
		
		$t = @$lang['admin']['panels'][$panel];
		$title = "$title $sep $t";
		return $title;
	}
	
	

	function showcontrolpanel($params, &$smarty) {
		$smarty->display(ABS_PATH. ADMIN_DIR . 'main.tpl');
	}
	
	// html header
	
	function admin_title($title, $sep) {
		global $lang;
		return $title = "$title $sep {$lang['admin']['head']}"; 
	}
	
	add_filter('wp_title', 'admin_title', 10, 2);


	// setup admin_header
	function admin_header_default_action() {
		global $panel, $action;
		do_action("admin_{$panel}_{$action}_head");
	}
	add_filter('admin_head', 'admin_header_default_action');
	
	
	$fp_config = config_load();
	system_init();
	main();
	admin_panelstrings('admin.'.ADMIN_PANEL);
	theme_init($smarty);
	$smarty->register_function('controlpanel', 'showcontrolpanel');
	
	$v = $lang['admin'][$panel][$action];
	
	
	$smarty->assign_by_ref('panelstrings',	$v);
	$smarty->assign_by_ref('plang',			$v);

	
	if (isset($_GET['mod'])) {
	
		switch ($_GET['mod']) {
			case 'inline' :
				$smarty->display(ABS_PATH . ADMIN_DIR . 'admin-inline.tpl');
				break;
			case 'ajax' :
				echo $smarty->get_template_vars('success');
		}
		
	} else {
		$smarty->display('admin.tpl');
	}

	
?>

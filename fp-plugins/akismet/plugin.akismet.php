<?php
/*
Plugin Name: Akismet
Version: 0.1
Plugin URI: http://flatpress.sf.net
Description: Integration with Akismet powerful Antispam system!
Author: NoWhereMan
Author URI: http://flatpress.sf.net
*/


define('AKISMET_TIMEOUT', 10);

require plugin_getdir('akismet') . '/inc/Akismet.class.php';

function plugin_akismet_setup() {
	global $fp_config;

	if (!plugin_getoptions('akismet','apikey')) {
		return -1;
	}

	return 1;		
}

if (plugin_getoptions('akismet','apikey')) {
	add_filter('comment_validate','plugin_akismet_validate', 10, 2);
}

function plugin_akismet_validate($bool, $contents) {
	
	if (!$bool) return false;
	
	global $fp_config;
	
	$akismet = new Akismet($fp_config['general']['www'], plugin_getoptions('akismet','apikey'));
	$akismet->setAuthor($contents['name']);
	$akismet->setAuthorEmail(isset($contents['email'])? $contents['email'] : '');
	$akismet->setAuthorURL(isset($contents['url'])? $contents['url'] : '');
	$akismet->setContent($contents['content']);
	
	if ($v= $akismet->isSpam()){
		global $smarty;
		$smarty->assign('error', array('ERROR: Comment is invalid'));
		return false;
	} 
	return true;
}

if (class_exists('AdminPanelAction')){

	class admin_plugin_akismet extends AdminPanelAction { 
		
		var $langres = 'plugin:akismet';
		
		function setup() {
			$this->smarty->assign('admin_resource', "plugin:akismet/admin.plugin.akismet");
		}
		
		function main() {
			$akismetconf = plugin_getoptions('akismet');
			$this->smarty->assign('akismetconf', $akismetconf);
		}
		
		function onsubmit() {
			global $fp_config;
			
			if ($_POST['wp-apikey']){
				
				plugin_addoption('akismet', 'apikey', $_POST['wp-apikey']);
				plugin_saveoptions('akismet');
				
				$this->smarty->assign('success', 1);
			} else {
			 	$this->smarty->assign('success', -1);
			}
			
			return 2;
		}
		
	}

	admin_addpanelaction('plugin', 'akismet', true);

}

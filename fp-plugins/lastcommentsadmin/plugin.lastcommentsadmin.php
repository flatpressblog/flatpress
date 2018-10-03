<?php
/*
Plugin Name: Last Comments Admin
Version: 0.1
Plugin URI: http://kirgroup.com/blog/
Description: Manage last comments cache. Require LastComment plugin.
Author: Fabrixxm
Author URI: http://kirgroup.com/blog/
*/



if (class_exists('AdminPanelAction')){

	class admin_plugin_lastcommentsadmin extends AdminPanelAction { 
		
		var $langres = 'plugin:lastcommentsadmin';
		
		function setup() {
			$this->smarty->assign('admin_resource', "plugin:lastcommentsadmin/admin.plugin.lastcommentsadmin");
		}
		
		function main() {
			if  (!function_exists('plugin_lastcomments_cache')){
				$this->smarty->assign('success', -2);
			}
		}
		
		function onsubmit($data = NULL) {
			global $fp_config;
			
			if (isset($_POST['lastcommentadmin_clear'])){
				fs_delete(LASTCOMMENTS_CACHE_FILE);
				$this->smarty->assign('success', 1);
			}
			
			if (isset($_POST['lastcommentadmin_rebuild'])){
				fs_delete(LASTCOMMENTS_CACHE_FILE);
				$coms = Array();
				
				$q = new FPDB_Query(array('fullparse'=>false,'start'=>0,'count'=>-1), null);
				while ($q->hasmore()) {
					list($id,$e) = $q->getEntry();
					$obj = new comment_indexer($id);
					foreach($obj->getList() as $value){
						$coms[$value]=$id;
					}
					ksort($coms);
					$coms = array_slice($coms, -LASTCOMMENTS_MAX );
				}
				foreach($coms as $cid=>$eid){
					$c = comment_parse($eid, $cid);
					plugin_lastcomments_cache($eid, array($cid, $c));
				}
				$this->smarty->assign('success', 2);
			}
			
			return 2;
		}
		
	}

	admin_addpanelaction('plugin', 'lastcommentsadmin', true);

}
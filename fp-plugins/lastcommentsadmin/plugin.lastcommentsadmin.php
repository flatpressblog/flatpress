<?php
/*
 * Plugin Name: LastComments Admin
 * Version: 1.0
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Manage the cache of the LastComments plugin. Requires LastComment plugin enabled. Part of the standard distribution.
 */
if (class_exists('AdminPanelAction')) {

	class admin_plugin_lastcommentsadmin extends AdminPanelAction {

		var $langres = 'plugin:lastcommentsadmin';

		function setup() {
			$this->smarty->assign('admin_resource', "plugin:lastcommentsadmin/admin.plugin.lastcommentsadmin");
		}

		function main() {
			if (!function_exists('plugin_lastcomments_cache')) {
				$this->smarty->assign('success', -2);
			}
		}

		function onsubmit($data = NULL) {
			global $fp_config;

			if (isset($_POST ['lastcommentadmin_clear'])) {
				fs_delete(LASTCOMMENTS_CACHE_FILE);
				$this->smarty->assign('success', 1);
			}

			if (isset($_POST ['lastcommentadmin_rebuild'])) {
				fs_delete(LASTCOMMENTS_CACHE_FILE);
				$coms = Array();

				$q = new FPDB_Query(array(
					'fullparse' => false,
					'start' => 0,
					'count' => -1
				), null);
				while ($q->hasmore()) {
					list ($id, $e) = $q->getEntry();
					$obj = new comment_indexer($id);
					foreach ($obj->getList() as $value) {
						$coms [$value] = $id;
					}
					ksort($coms);
					$coms = array_slice($coms, -LASTCOMMENTS_MAX);
				}
				foreach ($coms as $cid => $eid) {
					$c = comment_parse($eid, $cid);
					plugin_lastcomments_cache($eid, array(
						$cid,
						$c
					));
				}
				$this->smarty->assign('success', 2);
			}

			return 2;
		}

	}

	admin_addpanelaction('plugin', 'lastcommentsadmin', true);
}
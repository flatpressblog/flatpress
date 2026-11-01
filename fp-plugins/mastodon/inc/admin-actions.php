<?php
/** Admin action classes; loaded only inside the original AdminPanelAction guard. */

	class admin_plugin_mastodon extends AdminPanelAction {

		var $langres = 'plugin:mastodon';

		function setup() {
			$this->smarty->assign('admin_resource', 'plugin:mastodon/admin.plugin.mastodon');
			plugin_mastodon_admin_assign($this->smarty);
		}

		function main() {
			return 0;
		}

		function onsubmit($data = null) {
			$options = plugin_mastodon_get_options();
			$stateMaintenanceResult = array();

			if (isset($_POST ['mastodon_save'])) {
				$options = plugin_mastodon_admin_apply_save_post($options, $_POST);
				plugin_mastodon_save_options($options);
				$this->smarty->assign('success', 1);
			} elseif (isset($_POST ['mastodon_register_app'])) {
				$options ['instance_url'] = plugin_mastodon_normalize_instance_url(isset($_POST ['instance_url']) ? $_POST ['instance_url'] : $options ['instance_url']);
				plugin_mastodon_save_options($options);
				$response = plugin_mastodon_register_app($options);
				$this->smarty->assign('success', $response ['ok'] ? 2 : -2);
			} elseif (isset($_POST ['mastodon_exchange_code'])) {
				$code = trim(isset($_POST ['authorization_code']) ? (string) $_POST ['authorization_code'] : '');
				$response = plugin_mastodon_exchange_code_for_token($options, $code);
				$this->smarty->assign('success', $response ['ok'] ? 3 : -3);
			} elseif (isset($_POST ['mastodon_refresh_instance_info'])) {
				$options ['instance_url'] = plugin_mastodon_normalize_instance_url(isset($_POST ['instance_url']) ? $_POST ['instance_url'] : $options ['instance_url']);
				if ($options ['instance_url'] === '') {
					$options = plugin_mastodon_clear_saved_instance_info($options);
					plugin_mastodon_save_options($options);
					$this->smarty->assign('success', -6);
				} else {
					if ($options ['instance_url'] !== plugin_mastodon_normalize_instance_url(plugin_mastodon_get_options()['instance_url'])) {
						plugin_mastodon_save_options($options);
					}
					$response = plugin_mastodon_refresh_instance_information($options);
					$this->smarty->assign('success', !empty($response ['ok']) ? 6 : -6);
				}
			} elseif (isset($_POST ['mastodon_diagnose_state'])) {
				$diagnostics = plugin_mastodon_state_diagnose_comment_shards(null);
				$stateMaintenanceResult = plugin_mastodon_admin_state_maintenance_result($diagnostics);
				$this->smarty->assign('success', !empty($diagnostics ['ok']) ? 7 : -7);
			} elseif (isset($_POST ['mastodon_repair_state'])) {
				$diagnostics = plugin_mastodon_state_repair_comment_shards(null, true);
				$stateMaintenanceResult = plugin_mastodon_admin_state_maintenance_result($diagnostics);
				$repairOk = empty($diagnostics ['errors']) && (!empty($diagnostics ['repaired']) || empty($diagnostics ['warnings']));
				$this->smarty->assign('success', $repairOk ? 8 : -8);
			} elseif (isset($_POST ['mastodon_run_now'])) {
				$result = plugin_mastodon_run_sync(true, false);
				$this->smarty->assign('success', $result ['ok'] ? 4 : -4);
			} elseif (isset($_POST ['mastodon_run_full_now'])) {
				$result = plugin_mastodon_run_sync(true, true);
				$this->smarty->assign('success', $result ['ok'] ? 4 : -4);
			} elseif (isset($_POST ['mastodon_run_full_deletion'])) {
				$result = plugin_mastodon_run_deletion_sync(true);
				$this->smarty->assign('success', $result ['ok'] ? 4 : -4);
			} elseif (isset($_POST ['mastodon_clear_token'])) {
				$options ['access_token'] = '';
				$options ['authorization_code'] = '';
				plugin_mastodon_save_options($options);
				$this->smarty->assign('success', 5);
			}

			plugin_mastodon_admin_assign($this->smarty);
			if ($stateMaintenanceResult !== array()) {
				$this->smarty->assign('mastodon_state_maintenance_result', $stateMaintenanceResult);
			}
			return 0;
		}
	}


	class admin_plugin_mastodon_maintenance extends AdminPanelAction {

		var $langres = 'plugin:mastodon';

		function setup() {
			$this->smarty->assign('admin_resource', 'plugin:mastodon/admin.plugin.mastodon.maintenance');
			plugin_mastodon_admin_assign($this->smarty);
		}

		function main() {
			return 0;
		}

		function onsubmit($data = null) {
			$stateMaintenanceResult = array();

			if (isset($_POST ['mastodon_diagnose_state'])) {
				$diagnostics = plugin_mastodon_state_diagnose_comment_shards(null);
				$stateMaintenanceResult = plugin_mastodon_admin_state_maintenance_result($diagnostics);
				$this->smarty->assign('success', !empty($diagnostics ['ok']) ? 7 : -7);
			} elseif (isset($_POST ['mastodon_repair_state'])) {
				$diagnostics = plugin_mastodon_state_repair_comment_shards(null, true);
				$stateMaintenanceResult = plugin_mastodon_admin_state_maintenance_result($diagnostics);
				$repairOk = empty($diagnostics ['errors']) && (!empty($diagnostics ['repaired']) || empty($diagnostics ['warnings']));
				$this->smarty->assign('success', $repairOk ? 8 : -8);
			}

			plugin_mastodon_admin_assign($this->smarty);
			if ($stateMaintenanceResult !== array()) {
				$this->smarty->assign('mastodon_state_maintenance_result', $stateMaintenanceResult);
			}
			return 0;
		}
	}
?>

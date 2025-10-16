<?php
/**
 * plugin control panel
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

class admin_plugin extends AdminPanel {
	var $panelname = 'plugin';
	var $actions = array(
		'default' => true
	);
}

class admin_plugin_default extends AdminPanelAction {
	var $commands = array(
		'enable',
		'disable'
	);

	var $errors = array();
	var $pluginid;
	var $fp_plugins;

	function setup() {
		// Input validation coming from $_POST or $_GET to avoid XSS
		$this->pluginid = isset($_GET ['plugin']) ? sanitize_text_field($_GET ['plugin']) : null;

		$pi = new plugin_indexer();
		$plist = $pi->getList();
		sort($plist);
		$this->smarty->assign('pluginlist', $plist);
		$this->errors = @$pi->getEnableds(true);
		$this->fp_plugins = $pi->enabledlist;

		// Initial enabled plugins list
		$this->smarty->assign('enabledlist', $this->fp_plugins);
	}

	function dodisable($id) {
		// at first: check if nonce was given correctly
		check_admin_referer('admin_plugin_default_disable_' . $id);

		$success = -1;
		$fp_plugins = $this->fp_plugins;

		if (plugin_exists($id)) {
			$success = 1;
			if (($key = array_search($id, $fp_plugins, true)) !== false) {
				unset($fp_plugins [$key]);
				$fp_plugins = array_values($fp_plugins);
				sort($fp_plugins);
				do_action('deactivate_' . $id);
				$success = system_save(CONFIG_DIR . 'plugins.conf.php', compact('fp_plugins'));
				if ($success && function_exists('opcache_invalidate')) {
					@opcache_invalidate(CONFIG_DIR . 'plugins.conf.php', true);
				}
			}
		}

		if ($success) {
			// Update the list of enabled plugins
			$this->fp_plugins = $fp_plugins;

			// Assign updated enabled list
			$this->smarty->assign('enabledlist', $this->fp_plugins);
			$this->smarty->assign('success', $success);
		}

		// Call main() to render updated list without reload
		return $this->main();
	}

	function doenable($id) {
		// at first: check if nonce was given correctly
		check_admin_referer('admin_plugin_default_enable_' . $id);

		$success = -1;
		$fp_plugins = $this->fp_plugins;

		if (plugin_exists($id)) {
			$success = 1;
			if (!in_array($id, $fp_plugins, true)) {
				$fp_plugins [] = $id;
				$fp_plugins = array_values(array_unique($fp_plugins));
				sort($fp_plugins);
				plugin_load($id, false, false);
				do_action('activate_' . $id);
				$success = system_save(CONFIG_DIR . 'plugins.conf.php', compact('fp_plugins'));
				if ($success && function_exists('opcache_invalidate')) {
					@opcache_invalidate(CONFIG_DIR . 'plugins.conf.php', true);
				}
			}
		}

		if ($success) {
			// Update the list of enabled plugins
			$this->fp_plugins = $fp_plugins;

			// Assign updated enabled list
			$this->smarty->assign('enabledlist', $this->fp_plugins);
			$this->smarty->assign('success', $success);
		}

		// Call main() to render updated list without reload
		return $this->main();
	}

	function main() {
		if (!empty($this->errors)) {
			$this->smarty->assign('warnings', $this->errors);
		}
		// Ensure enabledlist is assigned on every call
		$this->smarty->assign('enabledlist', $this->fp_plugins);

		lang_load('admin.plugin');
		return 0;
	}
}
?>

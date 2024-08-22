<?php

if (class_exists('AdminPanelAction')){
	/**
	 * Provides an admin panel entry for QuickSpam setup.
	 */
	class admin_plugin_bbcode extends AdminPanelAction {
		var $langres = 'plugin:bbcode';
		
		/**
		 * Initializes this panel.
		 */
		function setup() {
			$this->smarty->assign('admin_resource', "plugin:bbcode/admin.plugin.bbcode");
		}
		
		/**
		 * Setups the default panel.
		 */
		function main() {
			$bbconf = plugin_getoptions('bbcode');

			// pass sane values to form... is this really needed?
			$this->smarty->assign(
				'bbchecked',
				array(
					isset($bbconf['escape-html']) && $bbconf['escape-html']
						? 1
						: 0,
					isset($bbconf['escape-html']) && $bbconf['comments']
						? 1
						: 0,
					isset($bbconf['escape-html']) && $bbconf['editor']
						? 1
						: 0
				)
			);
			$bbconf['number'] = isset($bbconf['url-maxlen']) && is_numeric($bbconf['url-maxlen'])
				? $bbconf['url-maxlen']
				: 40;
			$this->smarty->assign('bbconf', $bbconf);
		}
		
		/**
		 * Will be executed when the BBCode configuration is send.
		 *
		 * @return int
		 */
		function onsubmit($data = null) {
			if (isset($_POST['bb-conf'])){
				$maxlen = isset($_POST['bb-maxlen']) && is_numeric($_POST['bb-maxlen'])
					? (int)$_POST['bb-maxlen']
					: 40;
				plugin_addoption('bbcode', 'escape-html', isset($_POST['bb-allow-html']));
				plugin_addoption('bbcode', 'comments',    isset($_POST['bb-comments']));
				plugin_addoption('bbcode', 'editor',      isset($_POST['bb-toolbar']));
				plugin_addoption('bbcode', 'url-maxlen',  $maxlen);
				plugin_saveoptions('bbcode');
				$this->smarty->assign('success', 1);
			} else {
			 	$this->smarty->assign('success', -1);
			}
			return 2;
		}
	}
	admin_addpanelaction('plugin', 'bbcode', true);
}

?>

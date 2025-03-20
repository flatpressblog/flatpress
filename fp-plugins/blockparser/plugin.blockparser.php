<?php
/*
 * Plugin Name: BlockParser
 * Type: Block
 * Version: 1.0.1
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Allows you to use simple non-plugin custom blocks. Part of the standard distribution.
 */

// define('BLOCKS_DIR', CONTENT_DIR . 'blocks/');

// as a default blocks_dir == static_dir
// so you can edit blocks using the static editor!!
define('BLOCKS_DIR', STATIC_DIR);

function plugin_blockparser_parse($blockid) {
	if ($f_contents = io_load_file(BLOCKS_DIR . $blockid . EXT)) {
		$contents = utils_kexplode($f_contents);
		return array_change_key_case($contents, CASE_LOWER);
	}
	// Return false if file could not be loaded
	return false;
}

// register_widget('blockparser', 'BlockParser', 'plugin_blockparser_widget', 1);
function plugin_blockparser_widget($blockid) {
	if ($contents = plugin_blockparser_parse($blockid)) {
		$contents ['subject'] = apply_filters('the_title', $contents ['subject']);
		$contents ['content'] = apply_filters('the_content', $contents ['content']);
		$contents ['id'] = 'widget-bp-' . $blockid;
		return $contents;
	}

	return array(
		'subject' => 'BlockParser::Error',
		'content' => '<ul><li>Error parsing block ' . $blockid . '; file may not exist</li></ul>'
	);
}

/**
 * Initializes the BlockParser plugin.
 * Registers widgets based on activated pages in the plugin panel.
 */
function plugin_blockparser_init() {
	$pgs = plugin_getoptions('blockparser', 'pages');
	if (is_array($pgs)) {
		foreach ($pgs as $page) {
			register_widget('blockparser:' . $page, // widget id
			'BlockParser: ' . $page, // widget name
			function () use ($page) {
				return plugin_blockparser_widget($page);
			} // Widget content as lambda function
			);
		}
	}
}

add_action('init', 'plugin_blockparser_init');

if (class_exists('AdminPanelAction')) {

	class admin_widgets_blockparser extends AdminPanelAction {

		var $langres = 'plugin:blockparser';

		var $commands = array(
			'enable',
			'disable'
		);

		var $bp_enabled;

		/**
		 * Activates a page and updates the list.
		 *
		 * @param string $id The ID of the page to be activated.
		 * @return void The updated list is displayed directly.
		 */
		 function doenable($id) {
			$success = -1;
			$enabled = &$this->bp_enabled;

			if (static_exists($id)) {
				if (!$enabled) {
					$enabled = array();
				}

				if (!in_array($id, $enabled)) {
					// Activate and save page
					$enabled [] = $id;
					sort($enabled);
					plugin_addoption('blockparser', 'pages', $enabled);
					plugin_saveoptions();
					$success = 1;
				}
			}

			if ($success === 1) {
				// Update list of activated pages
				$this->bp_enabled = $enabled;
				$this->smarty->assign('enabledlist', $this->bp_enabled);
				$this->smarty->assign('success', $success);
			}

			// Call main() to show updated list
			$this->main();
		}

		/**
		 * Deactivates a page and updates the list.
		 *
		 * @param string $id The ID of the page to be deactivated.
		 * @return void The updated list is displayed directly.
		 */
		function dodisable($id) {
			$success = -2;
			$enabled = &$this->bp_enabled;

			if ($enabled && is_numeric($v = array_search($id, $enabled))) {
				unset($enabled [$v]);
				@sort($enabled);
				@plugin_addoption('blockparser', 'pages', $enabled);
				plugin_saveoptions();
				$success = 2;
			}

			if ($success === 2) {
				// Update list of activated pages
				$this->bp_enabled = $enabled;
				$this->smarty->assign('enabledlist', $this->bp_enabled);
				$this->smarty->assign('success', $success);
			}

			// Call main() to show updated list
			$this->main();
		}

		function setup() {
			$this->smarty->assign('admin_resource', 'plugin:blockparser/admin.plugin.blockparser');
			$this->smarty->assign('enabledlist', $this->bp_enabled = plugin_getoptions('blockparser', 'pages'));
		}

		function main() {
			global $fp_config;
			// $this->smarty->assignByRef('enabledpages', plugin_getoptions('blockparser'));
			$this->smarty->assign('statics', $assign = static_getlist());
		}

	}

	admin_addpanelaction('widgets', 'blockparser', true);
}
?>

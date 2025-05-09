<?php

/*
 * Plugin Name: QuickSpamFilter
 * Version: 3.5.1
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Block comments by "bad" words. Part of the standard distribution.
 */

/**
 * This plugin denies comments containing "bad words" (e.g., "href" or "[url").
 *
 * @global object $smarty Smarty template engine instance.
 * @param bool $bool Whether the comment validation should proceed.
 * @param array|string $contents The comment being checked (should be an array).
 * @return bool Returns false if the comment contains spam words, true otherwise.
 */
function plugin_qspam_validate($bool, $contents) {
	if (!$bool) {
		return false;
	}

	$qscfg = plugin_getoptions('qspam');

	// Rudimentary ban of links
	$BAN_WORDS = isset($qscfg ['wordlist']) ? (array) $qscfg ['wordlist'] : ['href', '[url'];

	$qscfg ['number'] = isset($qscfg ['number']) ? (int) $qscfg ['number'] : 1;

	if (!is_array($contents) || !isset($contents ['content'])) {
		return false;
	}

	$txt = strtolower(trim($contents ['content']));
	$count = 0;

	foreach ($BAN_WORDS as $word) {
		$count += substr_count($txt, strtolower($word));
	}

	if ($count >= $qscfg ['number']) {
		global $smarty;
		$lang = lang_load('plugin:qspam');
		$smarty->assign('error', [$lang ['plugin'] ['qspam'] ['error']]);
		return false;
	}

	return true;
}
add_filter('comment_validate', 'plugin_qspam_validate', 5, 2);

if (class_exists('AdminPanelAction')) {

	/**
	 * Provides an admin panel entry for QuickSpam setup.
	 */
	class admin_plugin_qspam extends AdminPanelAction {

		var $langres = 'plugin:qspam';

		/**
		 * Initializes this panel.
		 */
		function setup() {
			$this->smarty->assign('admin_resource', "plugin:qspam/admin.plugin.qspam");
		}

		/**
		 * Setups the default panel.
		 */
		function main() {
			$qscfg = plugin_getoptions('qspam');
			$qscfg ['wordlist'] = isset($qscfg ['wordlist']) && is_array($qscfg ['wordlist']) ? implode("\n", $qscfg ['wordlist']) : '';
			$qscfg ['number'] = isset($qscfg ['number']) ? $qscfg ['number'] : 1;
			$this->smarty->assign('qscfg', $qscfg);
		}

		/**
		 * Will be executed when the QSF configuration is send.
		 *
		 * @return int
		 */
		function onsubmit($data = null) {
			if ($_POST ['qs-wordlist']) {
				$wordlist = isset($_POST ['qs-wordlist']) ? stripslashes($_POST ['qs-wordlist']) : '';
				$wordlist = str_replace("\r", "\n", $wordlist);
				// DMKE: Works neither recursive correct nor in a loop... *grrr*
				// $wordlist = str_replace("\n\n", "\n", $wordlist);
				$wordlist = explode("\n", $wordlist);
				$wordlist = array_filter($wordlist, array(
					$this,
					'_array_filter'
				));
				$number = isset($_POST ['qs-number']) && is_numeric($_POST ['qs-number']) ? (int) $_POST ['qs-number'] : 1;
				plugin_addoption('qspam', 'wordlist', $wordlist);
				plugin_addoption('qspam', 'number', $number);
				plugin_saveoptions('qspam');
				$this->smarty->assign('success', 1);
			} else {
				$this->smarty->assign('success', -1);
			}
			return 2;
		}

		/**
		 * Array filter callback function.
		 * Culls empty array values.
		 * Life is hell ._.
		 *
		 * @param string $str
		 * @return boolean
		 */
		function _array_filter($str) {
			return strlen(trim($str)) > 0;
		}

	}
	admin_addpanelaction('plugin', 'qspam', true);
}

?>

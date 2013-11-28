<?php
/*
Plugin Name: QuickSpamFilter
Plugin URI: http://flatpress.nowherland.it/
Description: Quick ban words (edit the plugin to add more to the list) 
Author: NoWhereMan
Version: 3.5.1
Author URI: http://www.nowhereland.it
*/

/**
 * This plugin denies comments when they're containing "bad words",
 * e.g. "href" (which indexes links)., etc.
 *
 * @global $smarty
 * @param boolean $bool
 * @param string $contents The comment
 * @return unknown
 */
function plugin_qspam_validate($bool, $contents) {
	if (!$bool) {
		return false;
	}
	$qscfg = plugin_getoptions('qspam');
	// We're looking for these words:
	$BAN_WORDS = '';
	if (isset($qscfg['wordlist'])) {
		$BAN_WORDS = $qscfg['wordlist'];
	} else {
		// rudimentary ban of links
		$BAN_WORDS = array('href', '[url');
	}
	$qscfg['number'] = isset($qscfg['number'])
		? $qscfg['number']
		: 1;
	$txt = strtolower(trim($contents['content']));
	$count = 0;
	while ($w = array_pop($BAN_WORDS)) {
		$count += substr_count($txt, strtolower($w));
	}
	if ($count >= $qscfg['number']) {
		global $smarty;
		$lang = lang_load('plugin:qspam');
		$smarty->assign('error', array($lang['plugin']['qspam']['error']));
		return false;
	}
	return true;
}
add_action('comment_validate', 'plugin_qspam_validate', 5, 2);

if (class_exists('AdminPanelAction')){
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
			$qscfg['wordlist'] = isset($qscfg['wordlist']) && is_array($qscfg['wordlist'])
				? implode("\n", $qscfg['wordlist'])
				: '';
			$qscfg['number'] = isset($qscfg['number'])
				? $qscfg['number']
				: 1;
			$this->smarty->assign('qscfg', $qscfg);
		}
		
		/**
		 * Will be executed when the QSF configuration is send.
		 *
		 * @return int
		 */
		function onsubmit($data = null) {
			if ($_POST['qs-wordlist']){
				$wordlist = isset($_POST['qs-wordlist'])
					? stripslashes($_POST['qs-wordlist'])
					: '';
				$wordlist = str_replace("\r", "\n", $wordlist);
				// DMKE: Works neither recursive correct nor in a loop... *grrr*
				#$wordlist = str_replace("\n\n", "\n", $wordlist);
				$wordlist = explode("\n", $wordlist);
				$wordlist = array_filter($wordlist, array($this, '_array_filter'));
				$number = isset($_POST['qs-number']) && is_numeric($_POST['qs-number'])
					? (int)$_POST['qs-number']
					: 1;
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
		 * Array filter callback function. Culls empty array values.
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

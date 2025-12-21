<?php
/**
 * Plugin Name: QuickSpamFilter
 * Version: 3.5.2
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Block comments by "bad" words. Part of the standard distribution.
 */

/**
 * Returns the default list of banned words.
 * Note: These are persisted into the plugin configuration when no custom list is configured.
 *
 * @return array
 */
function plugin_qspam_default_wordlist() {
	return array('href', '[url', 'http', 'https');
}

/**
 * Returns plugin options with sane defaults. If the wordlist is missing/empty,
 * the defaults are written to the FlatPress configuration so the admin UI shows them.
 *
 * @param bool $persistDefaults
 * @return array
 */
function plugin_qspam_getoptions_sane($persistDefaults = true) {
	$qscfg = plugin_getoptions('qspam');
	$qscfg = is_array($qscfg) ? $qscfg : array();

	$needsSave = false;

	// Wordlist
	$wordlist = array();
	if (isset($qscfg ['wordlist'])) {
		$wordlist = (array) $qscfg ['wordlist'];
		$wordlist = array_values(array_filter($wordlist, function ($str) {
			return is_string($str) && strlen(trim($str)) > 0;
		}));
	}
	if (empty($wordlist)) {
		$wordlist = plugin_qspam_default_wordlist();
		$needsSave = true;
	}

	// Threshold
	$number = 1;
	if (isset($qscfg ['number']) && is_numeric($qscfg ['number'])) {
		$number = (int) $qscfg ['number'];
	}
	if ($number < 1) {
		$number = 1;
		$needsSave = true;
	} elseif (!isset($qscfg ['number'])) {
		// Persist default threshold as well (harmless, and keeps config explicit).
		$needsSave = true;
	}

	$qscfg ['wordlist'] = $wordlist;
	$qscfg ['number'] = $number;

	if ($persistDefaults && $needsSave) {
		plugin_addoption('qspam', 'wordlist', $wordlist);
		plugin_addoption('qspam', 'number', $number);
		// On some hosts the config can be read-only; never break comment submission/admin UI.
		@plugin_saveoptions();
	}

	return $qscfg;
}

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

	$qscfg = plugin_qspam_getoptions_sane(true);

	$BAN_WORDS = isset($qscfg ['wordlist']) ? (array) $qscfg ['wordlist'] : plugin_qspam_default_wordlist();
	$BAN_WORDS = array_values(array_unique(array_filter($BAN_WORDS, function ($str) {
		return is_string($str) && strlen(trim($str)) > 0;
	})));

	// Count longer words first to avoid double-counting overlapping patterns (e.g. http within https).
	usort($BAN_WORDS, function ($a, $b) {
		$la = strlen((string) $a);
		$lb = strlen((string) $b);
		if ($la === $lb) {
			return 0;
		}
		return ($la < $lb) ? 1 : -1;
	});

	$threshold = isset($qscfg ['number']) ? (int) $qscfg ['number'] : 1;
	if ($threshold < 1) {
		$threshold = 1;
	}

	if (!is_array($contents) || !isset($contents ['content'])) {
		return false;
	}

	$txt = strtolower(trim((string) $contents ['content']));
	$count = 0;

	foreach ($BAN_WORDS as $word) {
		$w = strtolower((string) $word);
		if ($w === '') {
			continue;
		}
		$c = substr_count($txt, $w);
		if ($c > 0) {
			$count += $c;
			$txt = str_replace($w, str_repeat(' ', strlen($w)), $txt);
		}
	}

	if ($count >= $threshold) {
		global $smarty;
		$lang = lang_load('plugin:qspam');
		$smarty->assign('error', array($lang ['plugin'] ['qspam'] ['error']));
		return false;
	}

	return true;
}
add_filter('comment_validate', 'plugin_qspam_validate', 5, 2);

if (class_exists('AdminPanelAction')) {

	/**
	 * Provides an admin panel entry for QuickSpam setup.
	 */
	class admin_entry_qspam extends AdminPanelAction {

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
			$qscfg = plugin_qspam_getoptions_sane(true);
			$qscfg ['wordlist'] = implode("\n", (array) $qscfg ['wordlist']);
			$qscfg ['number'] = isset($qscfg ['number']) ? $qscfg ['number'] : 1;
			$this->smarty->assign('qscfg', $qscfg);
		}

		/**
		 * Will be executed when the QSF configuration is send.
		 *
		 * @return int
		 */
		function onsubmit($data = null) {
			if (isset($_POST ['qs-wordlist'])) {
				$wordlistRaw = isset($_POST ['qs-wordlist']) ? (string) $_POST ['qs-wordlist'] : '';
				$wordlistRaw = stripslashes($wordlistRaw);
				$wordlistRaw = str_replace("\r", "\n", $wordlistRaw);

				if (strlen(trim($wordlistRaw)) === 0) {
					$wordlist = plugin_qspam_default_wordlist();
				} else {
					$wordlist = explode("\n", $wordlistRaw);
					$wordlist = array_map('trim', $wordlist);
					$wordlist = array_filter($wordlist, array(
						$this,
						'_array_filter'
					));
					$wordlist = array_values(array_unique($wordlist));
					if (empty($wordlist)) {
						$wordlist = plugin_qspam_default_wordlist();
					}
				}
				$number = isset($_POST ['qs-number']) && is_numeric($_POST ['qs-number']) ? (int) $_POST ['qs-number'] : 1;
				if ($number < 1) {
					$number = 1;
				}
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
	admin_addpanelaction('entry', 'qspam', true);
}

?>

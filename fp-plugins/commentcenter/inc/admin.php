<?php
/**
 * This is the administration panel for the plugin Flatpress Comment Center.
 */
if (!class_exists('plugin_commentcenter')) {
	die('Don\'t try to hack us.');
}

class admin_entry_commentcenter extends AdminPanelAction {

	// The language files
	var $langres = 'plugin:commentcenter';

	// The commands
	var $commands = array(
		'configure',
		'polup',
		'poldown',
		'poledit',
		'poldelete',
		'approve_list',
		'publishcomm',
		'pubnoham',
		'deletecomm',
		'manage',
		'deletecomm2',
		'commspam'
	);

	// The submits
	var $events = array(
		'configure',
		'edit_policy',
		'delok',
		'cancel',
		'multidel',
		'mpubcomm',
		'mdelcomm',
		'commdelok',
		'ccancel',
		'entry_search',
		'mdelcomm_2',
		'commdelok_2',
		'ccancel_2'
	);

	/**
	 * This function is used as a callback when the panel is loaded.
	 * It indicates the Smarty template and it saves by reference the
	 * main class of the plugin Comment Center.
	 */
	function setup() {
		global $lang;
		$this->plugin = &$GLOBALS ['plugin_commentcenter'];
		$smarty = &$this->smarty;

		// The default TPL
		$smarty->assign('admin_resource', 'plugin:commentcenter/policies');
		$smarty->assign('plugin_url', plugin_geturl('commentcenter'));

		$smarty->register_modifier('idToSubject', array(
			&$this,
			'_idToTitle'
		));
		add_filter('wp_title', array(
			&$this,
			'_title'
		), 15, 2);
		add_action('wp_head', array(
			&$this,
			'_head'
		), 10);
	}

	/**
	 * This function is the callback for the hook system.
	 * It sets the title.
	 *
	 * @param string $val:
	 *        	The current title
	 * @param string $sep:
	 *        	The separator
	 * @return string: The title
	 */
	function _title($val, $sep) {
		return "{$val} {$sep} Comment Center";
	}

	/**
	 * This function is the callback for the hook system.
	 * It adds the javascript of the plugin.
	 */
	function _head() {
		if (!function_exists('plugin_jquery_head')) {
			return;
		}
		$src1 = plugin_geturl('commentcenter') . 'res/ajax.js';
		$src2 = BLOG_BASEURL . 'admin.php?jslang=commentcenter';
		echo '<script src="' . $src1 . "\"></script>\n";
		echo '<script src="' . $src2 . "\"></script>\n";
	}

	/**
	 * This function return the entry title from the id.
	 * It's made to be called from Smarty.
	 *
	 * @param string $id:
	 *        	The entry id
	 * @return string: The output
	 */
	function _idToTitle($id) {
		$o = new FPDB_Query(array(
			'start' => 0,
			'count' => 1,
			'fullparse' => false,
			'id' => $id
		), null);
		if (!$o->hasMore()) {
			return false;
		}
		$arr = $o->getEntry();
		return wp_specialchars($arr [1] ['subject']);
	}

	/**
	 * This function is an advanced redirect option.
	 *
	 * @param string $cmd:
	 *        	The command
	 * @param mixed $cmdval:
	 *        	The value for the command
	 * @param boolean $nosuccess:
	 *        	Don't save the success?
	 */
	function _redirect($cmd, $cmdval = 1, $nosuccess = false) {
		global $panel;
		$smarty = &$this->smarty;
		sess_add("success_{$panel}", $smarty->get_template_vars('success'));

		$action_url = $smarty->get_template_vars('action_url');
		$url = admin_filter_command($action_url, $cmd, $cmdval);
		$url = html_entity_decode($url);
		$url = substr($url, strlen(BLOG_BASEURL));
		utils_redirect($url);
		die();
	}

	/**
	 * This is the main function of the panel.
	 */
	function main() {
		$smarty = &$this->smarty;
		$plugin = &$this->plugin;
		$plugin->loadPolicies();
		$smarty->assign('policies', $plugin->policies);
	}

	/**
	 * This is the callback for the configure command.
	 */
	function doconfigure() {
		global $lang;
		$plugin = &$this->plugin;
		$conf = $plugin->getConf();

		$smarty = &$this->smarty;
		$smarty->assign('admin_resource', 'plugin:commentcenter/configure');
		$smarty->assign('pl_conf', $conf);

		$conf = $plugin->getConf();
		if (isset($conf) && array_key_exists('akismet_check', $conf) && $conf ['akismet_check']) {
			$akismet = $plugin->akismetLoad();
			if (is_numeric($akismet)) {
				$error = $lang ['admin'] ['entry'] ['commentcenter'] ['akismet_errors'] [$akismet];
				$smarty->assign('warnings', $error);
			}
		}
	}

	/**
	 * This callback is used when the configuration is saved.
	 */
	function onconfigure() {
		if (!empty($_POST ['akismet_url'])) {
			$pos = strpos($_POST ['akismet_url'], '//');
			if ($pos === FALSE || $pos > 8) {
				$_POST ['akismet_url'] = 'http://' . $_POST ['akismet_url'];
			}
		}

		$save = array(
			'log_all' => isset($_POST ['log_all']),
			'email_alert' => isset($_POST ['email_alert']),
			'akismet_check' => isset($_POST ['akismet_check']),
			'akismet_key' => $_POST ['akismet_key'],
			'akismet_url' => $_POST ['akismet_url']
		);
		// It doesn't make very sense: I could just use array_merge but...
		foreach ($save as $key => $value) {
			plugin_addoption('commentcenter', $key, $value);
		}

		$success = plugin_saveoptions() ? 1 : -1;
		$this->smarty->assign('success', $success);
		$this->_redirect('configure');
	}

	/**
	 * The edit policy/new policy action callback.
	 *
	 * @param integer $id:
	 *        	The policy id. -1 means a new one
	 * @return integer: The redirect option
	 */
	function dopoledit($id) {
		global $lang;
		$plugin = &$this->plugin;
		$smarty = &$this->smarty;
		$plang = &$lang ['admin'] ['entry'] ['commentcenter'];

		$plugin->loadPolicies();
		$smarty->assign('policy', array());
		$smarty->assign('admin_resource', 'plugin:commentcenter/editpol');
		$smarty->assign('pol_id', $id);

		if ($id != -1 && isset($plugin->policies [$id])) {
			$smarty->assign('policy', $plugin->policies [$id]);
		} elseif ($id != -1) {
			// Inexistent policy
			$smarty->assign('errors', $plang ['errors'] ['pol_nonex']);
		} else {
			$smarty->assign('polnew', true);
		}
		return 0;
	}

	/**
	 * The edit policy/new policy save callback.
	 *
	 * @return integer: The redirect option
	 */
	function onedit_policy() {
		$plugin = &$this->plugin;
		$success = 2;
		$policy = array();
		@$id = $_POST ['policy_id'];

		$plugin->loadPolicies();
		if ($id != -1 && !isset($plugin->policies [$id])) {
			$success = -2;
		}

		while (true && $success == 2) {
			if (empty($_POST ['apply_to'])) {
				$success = -2;
				break;
			}
			if (!isset($_POST ['behavoir'])) {
				$success = -2;
				break;
			}

			$behavoir = $_POST ['behavoir'];
			if ($behavoir != 1 && $behavoir != 0 && $behavoir != -1) {
				$success = -2;
				break;
			}
			$policy ['do'] = $behavoir;

			switch ($_POST ['apply_to']) {
				case 'all_entries':
					$policy ['is_all'] = true;
					break 2;
				case 'some_entries':
					if (empty($_POST ['entries'])) {
						$success = -2;
					} else {
						$entries = array();
						foreach ($_POST ['entries'] as $entry) {
							if (entry_exists($entry)) {
								$entries [] = $entry;
							}
						}
						if (count($entries) == 0) {
							$success = -2;
						} else {
							$entries = array_unique($entries);
							$policy ['entry'] = $entries;
						}
					}
					break 2;
				case 'properties':
					$policy ['is_all'] = true;
					if (isset($_POST ['cats'])) {
						$policy ['categories'] = array_keys($_POST ['cats']);
						$policy ['is_all'] = false;
					}
					if (is_numeric($_POST ['older'])) {
						// Save in seconds
						$policy ['older'] = $_POST ['older'] * 86400;
						$policy ['is_all'] = false;
					}
					if (isset($policy ['is_all']) && @!$policy ['is_all']) {
						unset($policy ['is_all']);
					}
					break 2;
				default:
					$success = -2;
					break 2;
			}
			break;
		}

		if ($success == 2) {
			if ($id == -1) {
				$plugin->policies [] = $policy;
			} else {
				$plugin->policies [$id] = $policy;
			}
			$success = $plugin->savePolicies() ? 2 : -2;
		}

		$this->smarty->assign('success', $success);
		return 2;
	}

	/**
	 * This function is the callback for the poldelete action.
	 *
	 * @param integer $id:
	 *        	The id of policy you wish to delete
	 * @return integer: Redirect option
	 */
	function dopoldelete($id) {
		$plugin = &$this->plugin;
		$smarty = &$this->smarty;

		$smarty->assign('admin_resource', 'plugin:commentcenter/deletepol');
		$plugin->loadPolicies();

		if (isset($plugin->policies [$id])) {
			$smarty->assign('policies', array(
				$id => $plugin->policies [$id]
			));
		}
		$smarty->assign('single', true);
		return 0;
	}

	/**
	 * This function is like dopoldelete but it's for multiple policies.
	 *
	 * @return integer: Redirect option
	 */
	function onmultidel() {
		$plugin = &$this->plugin;
		$smarty = &$this->smarty;

		if (@!count($_POST ['select'])) {
			$smarty->assign('success', -4);
			return 2;
		}

		$smarty->assign('admin_resource', 'plugin:commentcenter/deletepol');
		$plugin->loadPolicies();
		$policies = array();

		foreach ($_POST ['select'] as $polid => $checkvalue) {
			if (isset($plugin->policies [$polid])) {
				$policies [$polid] = $plugin->policies [$polid];
			}
		}

		if (count($policies) > 0) {
			$smarty->assign('policies', $policies);
		} else {
			$smarty->assign('success', -4);
			return 2;
		}

		if (count($policies) == 1) {
			$smarty->assign('single', true);
		}

		return 0;
	}

	/**
	 * This is the delete ok command.
	 *
	 * @return integer: The redirect option
	 */
	function ondelok() {
		if (empty($_POST ['del_policy'])) {
			$s = -4;
		} else {
			$plugin = $this->plugin;
			$plugin->loadPolicies();
			foreach ($_POST ['del_policy'] as $polid) {
				if (isset($plugin->policies [$polid])) {
					unset($plugin->policies [$polid]);
				}
			}
			$s = $plugin->savePolicies() ? 4 : -4;
		}
		$this->smarty->assign('success', $s);
		return 2;
	}

	/**
	 * This is the cancel callback.
	 * It just makes the redirect.
	 *
	 * @return integer: The redirect option
	 */
	function oncancel() {
		return 2;
	}

	/**
	 * This function is the callback for the polup action.
	 *
	 * @param integer $id:
	 *        	The id of policy you wish to delete
	 * @return integer: Redirect option
	 */
	function dopolup($id) {
		$s = -3;

		if ($id > 0) {
			$plugin = &$this->plugin;
			$plugin->loadPolicies();
			$plugin->policyMove($id, $id - 1);
			$s = $plugin->savePolicies() ? 3 : -3;
		}

		$this->smarty->assign('success', $s);
		return 2;
	}

	/**
	 * This function is the callback for the poldown action.
	 *
	 * @param integer $id:
	 *        	The id of policy you wish to delete
	 * @return integer: Redirect option
	 */
	function dopoldown($id) {
		$s = -3;

		$plugin = &$this->plugin;
		$plugin->loadPolicies();

		if ($id < count($plugin->policies) - 1) {
			$plugin->policyMove($id, $id + 1);
			$s = $plugin->savePolicies() ? 3 : -3;
		}

		$this->smarty->assign('success', $s);
		return 2;
	}

	/**
	 * This function is the callback for the approve_list command
	 *
	 * @return integer: The redirect option
	 */
	function doapprove_list() {
		$plugin = &$this->plugin;
		$smarty = &$this->smarty;

		$conf = $plugin->getConf();
		$smarty->assign('use_akismet', @$conf ['akismet_check']);
		$smarty->assign('other', @$conf ['log_all']);
		$smarty->assign('admin_resource', 'plugin:commentcenter/approvelist');

		$lister = new commentcenter_list($plugin->pl_dir);
		$smarty->assign('entries', $lister->toDetails());

		return 0;
	}

	/**
	 * This function is the callback for the publishcomm command
	 *
	 * @param string $id:
	 *        	The comment id
	 * @param boolean $noredirect:
	 *        	If true, don't redirect
	 * @param boolean $noham:
	 *        	If it was blocked from Akismet, don't submit as ham
	 * @return integer: The redirect option
	 */
	function dopublishcomm($id, $noredirect = false, $noham = false) {
		$plugin = &$this->plugin;
		$smarty = &$this->smarty;
		$f = $plugin->pl_dir . $id . '.txt';

		if (!file_exists($f)) {
			$smarty->assign('success', -5);
			$succ = -5;
		} else {

			include $f;

			$entry = substr($id, 1, 18);
			$ham = false;

			if (isset($comment ['log_reason'])) {
				$ham = $comment ['log_reason'] == 'akismet' && !$noham;
				unset($comment ['log_reason']);
			}
			if (isset($comment ['id'])) {
				unset($comment ['id']);
			}

			if ($ham) {
				$clean = $plugin->akismetClean($comment, $entry);
				$akismet = &$plugin->akismetLoad();
				if (is_object($akismet)) {
					$akismet->setComment($clean);
					$akismet->submitHam();
				}
			}

			$id = comment_save($entry, $comment);
			do_action('comment_post', $entry, array(
				$id,
				$comment
			));
			$succ = $id ? 5 : -5;
			$smarty->assign('success', $succ);

			if ($succ == 5) {
				@unlink($f);
			}
		}

		if ($noredirect) {
			return $succ == 5;
		}

		$this->_redirect('approve_list');
		return 0;
	}

	/**
	 * This is the callback for the action pubnoham.
	 *
	 * @param string $id:
	 *        	The comment id
	 * @return integer: The redirect option.
	 */
	function dopubnoham($id) {
		return $this->dopublishcomm($id, false, true);
	}

	/**
	 * This is the callback to publish multiple comments.
	 *
	 * @return integer: The redirect option
	 */
	function onmpubcomm() {
		if (!isset($_POST ['select'])) {
			$this->dopublishcomm('fake');
		}

		$target = count($_POST ['select']);
		$noham = !isset($_POST ['submitham']);
		$i = 0;

		foreach ((array) $_POST ['select'] as $comm => $check) {
			$i++;
			$this->dopublishcomm($comm, $i != $target, $noham);
		}

		// If it's correct, we should exited the script before
		$this->smarty->assign('success', -5);
		$this->_redirect('approve_list');
		return 0;
	}

	/**
	 * This function is the callback for the deletecomm action.
	 *
	 * @param integer $id:
	 *        	The id of the comment you wish to delete
	 * @return integer: Redirect option
	 */
	function dodeletecomm($id) {
		$plugin = &$this->plugin;
		$smarty = &$this->smarty;

		$smarty->assign('admin_resource', 'plugin:commentcenter/deletecomm');

		$entry = substr($id, 1, 18);
		$commid = substr($id, 21, 20);

		$f = $plugin->pl_dir . $id . '.txt';
		if (file_exists($f)) {
			include $f;
			$delete [$entry] ['del'] [$commid] = $comment;
			$smarty->assign('entries', $delete);
		} else {
			$smarty->assign('success', -6);
			$this->_redirect('approve_list');
		}

		$smarty->assign('single', true);
		return 0;
	}

	/**
	 * This function is like dopoldelete but it's for multiple policies.
	 *
	 * @return integer: Redirect option
	 */
	function onmdelcomm() {
		$plugin = &$this->plugin;
		$smarty = &$this->smarty;

		if (@!count($_POST ['select'])) {
			$smarty->assign('success', -6);
			$this->_redirect('approve_list');
			return 0;
		}

		$smarty->assign('admin_resource', 'plugin:commentcenter/deletecomm');
		$remove = array();

		foreach ($_POST ['select'] as $commbig => $check) {
			$entry = substr($commbig, 1, 18);
			$commid = substr($commbig, 21, 20);
			$f = $plugin->pl_dir . $commbig . '.txt';
			if (file_exists($f)) {
				include $f;
				$remove [$entry] ['del'] [$commid] = $comment;
			}
		}

		if (count($remove) > 0) {
			$smarty->assign('entries', $remove);
		} else {
			$smarty->assign('success', -6);
			$this->_redirect('approve_list');
			return 0;
		}

		if (count($remove) == 1) {
			$smarty->assign('single', true);
		}

		return 0;
	}

	/**
	 * This is the delete ok command.
	 *
	 * @return integer: The redirect option
	 */
	function oncommdelok() {
		if (empty($_POST ['select'])) {
			$s = -6;
		} else {
			foreach ($_POST ['select'] as $commid => $check) {
				$f = $this->plugin->pl_dir . $commid . '.txt';
				@unlink($f);
			}
			$s = 6;
		}
		$this->smarty->assign('success', $s);
		$this->_redirect('approve_list');
		return 2;
	}

	/**
	 * This is the cancel callback.
	 * It just makes the redirect.
	 *
	 * @return integer: The redirect option
	 */
	function onccancel() {
		$this->_redirect('approve_list');
		return 2;
	}

	/**
	 * This function is the callback for the action "manage".
	 *
	 * @param string $entry:
	 *        	The entry id
	 * @return integer: The redirect option
	 */
	function domanage($entry) {
		global $lang, $fpdb;
		$smarty = &$this->smarty;

		$smarty->assign('admin_resource', 'plugin:commentcenter/manage');
		$smarty->assign('is_managing', true);

		$conf = $this->plugin->getConf();
		if (@$conf ['akismet_check']) {
			$smarty->assign('use_akismet', true);
		}

		if ($entry != 'search' && !entry_exists($entry)) {
			$smarty->assign('error', $lang ['admin'] ['entry'] ['commentcenter'] ['errors'] ['entry_nf']);
		} elseif ($entry != 'search') {
			$smarty->assign('entry_id', $entry);
			$smarty->assign('fetch', 'list');

			$fpdb->query("id:{$entry},fullparse:true,comments:true");
			$q = &$fpdb->getQuery();
			$q->getEntry();
			$list = array();
			while ($q->comments->hasMore()) {
				list ($id, $comment) = $q->comments->getComment();
				$list [$entry] ['list'] [$id] = $comment;
			}
			$smarty->assign('entries', $list);
		}
		return 0;
	}

	/**
	 * Since it's impossible to use a GET form in Flatpress, to search entries
	 * we have to use a POST form, and here's its callback.
	 *
	 * @return integer: The redirect option
	 */
	function onentry_search() {
		if (!isset($_POST ['entry'])) {
			$_POST ['entry'] = '';
		}
		// In the function we call 'search' isn't an error, but here yes, so delete it
		if ($_POST ['entry'] == 'search') {
			$_POST ['entry'] = '';
		}
		return @$this->domanage($_POST ['entry']);
	}

	/**
	 * This function is the callback for the deletecomm2 action.
	 *
	 * @param integer $id:
	 *        	The id of the comment you wish to delete
	 * @return integer: Redirect option
	 */
	function dodeletecomm2($id) {
		$smarty = &$this->smarty;

		$smarty->assign('admin_resource', 'plugin:commentcenter/deletecomm');
		$smarty->assign('is_managing', true);

		$entry = substr($id, 1, 18);
		$commid = substr($id, 21, 20);
		$smarty->assign('entry', $entry);

		if (comment_exists($entry, $commid)) {
			$delete [$entry] ['del'] [$commid] = comment_parse($entry, $commid);
			$smarty->assign('entries', $delete);
		} else {
			$smarty->assign('success', -6);
			$this->_redirect('manage', $entry);
		}

		$smarty->assign('single', true);
		return 0;
	}

	/**
	 * This function is like dopoldelete but it's for multiple policies.
	 *
	 * @return integer: Redirect option
	 */
	function onmdelcomm_2() {
		$smarty = &$this->smarty;

		if (@!count($_POST ['select'])) {
			$smarty->assign('success', -6);
			$this->_redirect('manage', @$_POST ['entry_hid']);
			return 0;
		}

		$smarty->assign('admin_resource', 'plugin:commentcenter/deletecomm');
		$smarty->assign('is_managing', true);
		$smarty->assign('entry', @$_POST ['entry_hid']);
		$remove = array();

		foreach ($_POST ['select'] as $commbig => $check) {
			$entry = substr($commbig, 1, 18);
			$commid = substr($commbig, 21, 20);
			$comment = comment_parse($entry, $commid);
			if ($comment !== false) {
				$remove [$entry] ['del'] [$commid] = $comment;
			}
		}

		if (count($remove) > 0) {
			$smarty->assign('entries', $remove);
		} else {
			$smarty->assign('success', -6);
			$this->_redirect('manage', @$_POST ['entry_hid']);
			return 0;
		}

		if (count($remove) == 1) {
			$smarty->assign('single', true);
		}

		return 0;
	}

	/**
	 * This is the delete ok command.
	 *
	 * @return integer: The redirect option
	 */
	function oncommdelok_2() {
		if (empty($_POST ['select'])) {
			$s = -6;
		} else {
			$i = 0;
			foreach ($_POST ['select'] as $commid => $check) {
				$entry = substr($commid, 1, 18);
				$commid = substr($commid, 21, 20);
				$i += comment_delete($entry, $commid) ? 1 : 0;
			}
			$s = $i > 0 ? 6 : -6;
		}
		$this->smarty->assign('success', $s);
		$this->_redirect('manage', @$_POST ['entry']);
		return 2;
	}

	/**
	 * This is the cancel callback.
	 * It just makes the redirect.
	 *
	 * @return integer: The redirect option
	 */
	function onccancel_2() {
		$this->_redirect('manage', @$_POST ['entry']);
		return 2;
	}

	/**
	 * This function is the callback for the action commspam.
	 *
	 * @param string $id:
	 *        	The comment id
	 * @return integer: The redirect option
	 */
	function docommspam($id) {
		$smarty = &$this->smarty;
		$plugin = &$this->plugin;
		$entry = substr($id, 1, 18);
		$commid = substr($id, 21, 20);

		if (!comment_exists($entry, $commid)) {
			$smarty->assign('success', -7);
		} else {
			$comment = comment_parse($entry, $commid);
			$clean = $plugin->akismetClean($comment, $entry);

			$akismet = &$plugin->akismetLoad();
			if (is_object($akismet)) {
				$akismet->setComment($clean);
				$akismet->submitSpam();
				$smarty->assign('success', $akismet->errorsExist() ? -7 : 7);
			} else {
				$smarty->assign('success', -7);
			}
		}

		$this->_redirect('manage', $entry);
		return 0;
	}

}
admin_addpanelaction('entry', 'commentcenter', true);
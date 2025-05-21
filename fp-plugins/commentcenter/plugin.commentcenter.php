<?php

/*
 * Plugin Name: Comment Center
 * Version: 1.1.2
 * Plugin URI: https://www.flatpress.org
 * Author: FlatPress
 * Author URI: https://www.flatpress.org
 * Description: Manage your blog's comments: Set policies, publish or reject comments. Part of the standard distribution.
 */

/**
 * This class interacts with Flaptress comment system.
 */
class plugin_commentcenter {

	// The plugin configuration
	var $conf = array();

	// The policies
	var $policies = array();

	// The plugin_dir
	var $pl_dir = 'fp-content/plugin_commentcenter/';

	/**
	 * This is the constructor.
	 */
	function __construct() {
		add_action('entry_block', array(
			&$this,
			'lock'
		));
		add_filter('comment_validate', array(
			&$this,
			'validate'
		), 5, 2);
		$this->pl_dir = FP_CONTENT . 'plugin_commentcenter/';
		if (!file_exists($this->pl_dir)) {
			fs_mkdir($this->pl_dir);
		}
	}

	/**
	 * This function loads the configuration of the plugin.
	 *
	 * @param boolean $foce:
	 *        	Force to load it?
	 * @return array: The configuration
	 */
	function getConf($force = false) {
		if (!empty($this->conf) && $force) {
			return $this->conf;
		}
		$this->conf = plugin_getoptions('commentcenter');
		return $this->conf;
	}

	/**
	 * This function check if comment must be locked.
	 */
	function lock() {
		global $fp_params, $post, $smarty;
		$this->loadPolicies();
		$cats = array_key_exists('categories', $post) && is_array($post ['categories']) ? $post ['categories'] : array();
		$behavoir = array_key_exists('entry', $fp_params) ? $this->behavoirFromPolicies($fp_params ['entry'], $cats) : 1;
		if ($behavoir == -1 && !user_loggedin()) {
			$smarty->assign('entry_commslock', true);
		}
	}

	/**
	 * This function validates a comment.
	 *
	 * @param boolean $status:
	 *        	The current status of the comment validation
	 * @param array $comment:
	 *        	The comment data
	 * @return boolean: Is the comment valid?
	 */
	function validate($status, $comment) {
		global $smarty, $fp_params, $lang;
		// If the comment has been already stopped or this is the contact page, stop here our check
		if (!$status || function_exists('contact_display')) {
			return $status;
		}

		// If the comment has been made from an administrator, don't check it
		if (@$comment ['loggedin']) {
			return true;
		}

		$entry = $fp_params ['entry'];
		if (!isset($comment ['date'])) {
			$comment ['date'] = date_time();
		}
		$comment ['id'] = bdb_idfromtime(BDB_COMMENT, $comment ['date']);
		$conf = $this->getConf();

		// This variables defines how the system has to behave.
		$behavoir = 1;
		$this->loadPolicies();
		// To get categories of the entry, we use the same method of PrettyURLs...
		global $post;
		$behavoir = $this->behavoirFromPolicies($entry, $post ['categories']);

		// If comments are locked we don't send to Akismet
		if (@$conf ['akismet_check'] && $behavoir != -1) {
			$akismet = $this->akismetCheck($comment, $entry);
			if (!$akismet) {
				$smarty->append('error', $lang ['plugin'] ['commentcenter'] ['akismet_error']);
				$this->logComment($comment, $entry, 'akismet');
				return false;
			}
		}

		if ($behavoir == 0) {
			$this->logComment($comment, $entry, 'confirm');
			$smarty->append('warnings', $lang ['plugin'] ['commentcenter'] ['approvation']);
			if (@$conf ['email_alert']) {
				$this->commentMail($comment, $post ['subject']);
			}
		} elseif ($behavoir == -1 && @$conf ['log_all']) {
			$this->logComment($comment, $entry, 'denided');
			$smarty->append('error', $lang ['plugin'] ['commentcenter'] ['lock']);
		}

		if ($behavoir != 1) {
			// Delete the comment content
			$_POST ['content'] = '';
		}

		// Also if the comment need to be approved, we return false.
		return $behavoir == 1;
	}

	/**
	 * This function create an akismet instance.
	 * An Akismet object is returned by reference. But if an error
	 * happens, the function return a negative integer:
	 * -1 if we can't find the key
	 * -2 if we can't contact Akismet servers
	 * -3 if the response failed
	 * -4 if the key isn't valid
	 *
	 * @param string $key:
	 *        	A key for the service
	 * @return object: The akismet object
	 */
	function &akismetLoad($key = '') {
		$conf = $this->getConf();

		if (!empty($key)) {
		} elseif (empty($conf ['akismet_key'])) {
			$e = -1;
			return $e;
		} else {
			$key = $conf ['akismet_key'];
		}
		$url = empty($conf ['akismet_url']) ? BLOG_BASEURL : $conf ['akismet_url'];

		include_once dirname(__FILE__) . '/inc/akismet.class.php';
		$akismet = new Akismet($url, $key);

		if ($akismet->errorsExist()) {
			$e = 0;
			switch (true) {
				case $akismet->isError(AKISMET_SERVER_NOT_FOUND):
					$e = -2;
					break;
				case $akismet->isError(AKISMET_RESPONSE_FAILED):
					$e = -3;
					break;
				case $akismet->isError(AKISMET_INVALID_KEY):
					$e = -4;
					break;
			}
			return $e;
		} else {
			return $akismet;
		}
	}

	/**
	 * This function clean a comment to send it to Akismet.
	 *
	 * @param array $comment:
	 *        	The comment data
	 * @param string $entry:
	 *        	The entry id
	 * @return array: $comment cleaned
	 */
	function akismetClean($comment, $entry) {
		global $post;
		$conf = $this->getConf();

		$oldpost = $post;
		$o = new FPDB_Query("id:{$entry},fullparse:false", null);
		$arr = $o->getEntry();
		$post = $arr [1];
		$link = get_permalink($entry);
		if (!empty($conf ['akismet_url'])) {
			$link = $conf ['akismet_url'] . substr($link, strlen(BLOG_BASEURL));
		}

		$post = $oldpost;

		$clean = array(
			'author' => $comment ['name'],
			'email' => @$comment ['email'],
			'website' => @$comment ['url'],
			'body' => $comment ['content'],
			'user_ip' => @$comment ['ip-address'],
			'permalink' => $link
		);

		return $clean;
	}

	/**
	 * This function manages the Akismet Check
	 *
	 * @param array $comment:
	 *        	The comment data
	 * @param string $entry:
	 *        	The entry id
	 * @return boolean: Is the comment allowed?
	 */
	function akismetCheck($comment, $entry) {
		$akismet = &$this->akismetLoad();
		if (!is_object($akismet)) {
			// Failed to load it. We return true, but the comment isn't checked
			// TODO: Add an error logger? Or make different, configurable behaves?
			return true;
		}

		$clean = $this->akismetClean($comment, $entry);
		$akismet->setComment($clean);
		if ($akismet->isSpam()) {
			// Akismet sign the comment as spam. Let's stop it.
			return false;
		} else {
			return true;
		}
	}

	/**
	 * This function loads the comment policies.
	 *
	 * @param boolean $force:
	 *        	Force to load them?
	 * @return array: The policies
	 */
	function &loadPolicies($force = false) {
		if (!$force && !empty($this->policies)) {
			return $this->policies;
		}

		if (!file_exists($f = $this->pl_dir . 'policies.txt')) {
			$this->policies = array();
			return $this->policies;
		}

		include $f;
		$this->policies = $policies;
		return $this->policies;
	}

	/**
	 * This function saves the policies.
	 *
	 * @return boolean
	 */
	function savePolicies() {
		$this->policies = array_values($this->policies);
		return system_save($this->pl_dir . 'policies.txt', array(
			'policies' => $this->policies
		));
	}

	/**
	 * This function adds a policy in a certain position.
	 *
	 * @param mixed $policy:
	 *        	The policy
	 * @param integer $position:
	 *        	The position
	 */
	function addPolicyAt($policy, $position) {
		if ($position < 0) {
			$position = count($this->policies) + $position + 1;
		}
		$before = array_slice($this->policies, 0, $position);
		$after = array_slice($this->policies, $position);
		$this->policies = array_merge($before, array(
			$policy
		), $after);
	}

	/**
	 * This function moves a policy from a postition to another one.
	 *
	 * @param integer $old:
	 *        	The old position
	 * @param integer $new:
	 *        	The new position
	 */
	function policyMove($old, $new) {
		if (!isset($this->policies [$old])) {
			return false;
		}
		$new = $new > $old ? $new + 1 : $new;
		$del = $new > $old ? $old : $old + 1;
		$this->addPolicyAt($this->policies [$old], $new);
		if (isset($this->policies [$del])) {
			unset($this->policies [$del]);
		}
		$this->policies = array_values($this->policies);
		return true;
	}

	/**
	 * Get behavoir from policies.
	 * 1: The user can comment
	 * 0: The comment need to be approved
	 * -1: The user can't comment
	 *
	 * @param string $entry:
	 *        	The entry id
	 * @param array $cats:
	 *        	The categories
	 * @return integer: The behavoir
	 */
	function behavoirFromPolicies($entry, $cats = array()) {
		$date = date_from_id($entry);
		// check if $date is in expected format
		if (!array_key_exists('time', $date)) {
			return -1;
		}
		$time = $date ['time'];
		$return = 1;
		$pols = &$this->policies;

		if (count($pols)) {
			foreach ($pols as $policy) {
				if (@$policy ['is_all']) {
					$return = $policy ['do'];
				} elseif (!empty($policy ['entry']) && is_array($policy ['entry'])) {
					if (in_array($entry, $policy ['entry'])) {
						$return = $policy ['do'];
					}
				} elseif (!empty($policy ['entry'])) {
					if ($entry == $policy ['entry']) {
						$return = $policy ['do'];
					}
				} else {
					$consider = true;
					if (!empty($policy ['categories'])) {
						$consider = count(array_intersect($policy ['categories'], $cats)) > 0;
					}
					if (!empty($policy ['older'])) {
						$consider = (time() - $time) > $policy ['older'];
					} else {
						if (!empty($policy ['time_less'])) {
							$consider = $time > $policy ['time_less'];
						}
						if (!empty($policy ['time_more'])) {
							$consider = $time < $policy ['time_more'];
						}
					}
					$return = $consider ? $policy ['do'] : $return;
				}
			}
		}

		return $return;
	}

	/**
	 * This function saves a comment into the plugin directory.
	 * Maybe it's considered SPAM by Akismet or the comment requires
	 * the Administrator's approvation.
	 *
	 * @param array $comment:
	 *        	The comment data
	 * @param string $entry:
	 *        	The entry id
	 * @param string $why:
	 *        	The reason of the log
	 * @return boolean: Can it saves the log?
	 */
	function logComment($comment, $entry, $why = '') {
		$f = $this->pl_dir . "e{$entry}_c{$comment['id']}.txt";
		if (!empty($why)) {
			$comment ['log_reason'] = $why;
		}
		return system_save($f, array(
			'comment' => $comment
		));
	}

	/**
	 * This function send an email to the administrator with the comment data.
	 * It's based on the code of comment.php
	 *
	 * @param array $comment:
	 *        	The comment data
	 * @param string $entry_title:
	 *        	The title of the entry
	 * @return boolean
	 */
	function commentMail($comment, $entry_title) {
		global $lang, $fp_config;

		$subject = $lang ['plugin'] ['commentcenter'] ['mail_subj'];
		$subject = sprintf($subject, $fp_config ['general'] ['title']);

		$comm_mail = empty($comment ['email']) ? '' : "<{$comment['email']}>";
		$from_mail = $fp_config ['general'] ['email'];

		$text = $lang ['plugin'] ['commentcenter'] ['mail_text'];
		$text = str_replace(array(
			'%toname%',
			'%fromname%',
			'%frommail%',
			'%entrytitle%',
			'%content%',
			'%blogtitle%'
		), array(
			$fp_config ['general'] ['author'],
			$comment ['name'],
			$comm_mail,
			$entry_title,
			$comment ['content'],
			$fp_config ['general'] ['title']
		), $text);

		return @utils_mail($from_mail, $subject, $text);
	}

}

$GLOBALS ['plugin_commentcenter'] = new plugin_commentcenter();

/**
 * This class makes the list of comments that needs to be approved.
 */
class commentcenter_list extends fs_filelister {

	/**
	 * This is the constructor of the class.
	 *
	 * @params string $dir: The directory to list
	 */
	function __construct($dir) {
		parent::__construct($dir);
	}

	function _checkFile($directory, $file) {
		if (fnmatch('eentry*.txt', $file)) {
			$entry = substr($file, 1, 18);
			$comment = substr($file, 21, 20);
			$this->_list [$entry] [] = $comment;
		}
		return 0;
	}

	function toDetails($entry = null) {
		$list = array();
		if (!is_null($entry) && @is_array($this->_list [$entry])) {
			foreach ($this->_list [$entry] as $commentid) {
				include $this->_directory . "/e{$entry}_c{$commentid}.txt";
				if (empty($comment ['log_reason'])) {
					$comment ['log_reason'] = 'nd';
				}
				$list [$comment ['log_reason']] [$commentid] = $comment;
			}
		} else {
			foreach ($this->_list as $key => $comments) {
				$list [$key] = $this->toDetails($key);
			}
		}
		return $list;
	}

}

// If we're in administration area, we load admin panel
if (defined('MOD_ADMIN_PANEL')) {
	include dirname(__FILE__) . '/inc/admin.php';
	include dirname(__FILE__) . '/inc/jslang.php';
	include dirname(__FILE__) . '/inc/editor.php';
}
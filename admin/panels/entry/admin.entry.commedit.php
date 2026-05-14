<?php
/**
 * Comment edit panel
 *
 * Type:
 * Name:
 * Date: 12.05.2026
 * Purpose: Provides the option to edit comments
 * Input:
 *
 * @author FlatPress
 *
 */
class admin_entry_commedit extends AdminPanelActionValidated {

	var $validators = array(
		array(
			'name',
			'name',
			'notEmpty',
			false,
			false,
			'trim,stripslashes'
		),
		array(
			'email',
			'email',
			'isEmail',
			true,
			false,
			'trim,stripslashes'
		),
		array(
			'url',
			'url',
			'isURL',
			true,
			false,
			'trim,stripslashes'
		),
		array(
			'content',
			'content',
			'notEmpty',
			false,
			false,
			'stripslashes'
		)
	); 

	var $events = array(
		'save'
	);

	var $args = array(
		'entry',
		'comment'
	);

	var $nosuchcomment = false;

	function commedit_validate() {
		global $lang;

		$lerr = isset($lang ['admin'] ['entry'] ['commedit'] ['error']) && is_array($lang ['admin'] ['entry'] ['commedit'] ['error']) ? $lang ['admin'] ['entry'] ['commedit'] ['error'] : array();
		$errors = array();
		$name = isset($_POST ['name']) ? trim(stripslashes((string)$_POST ['name'])) : '';
		$email = isset($_POST ['email']) ? trim(stripslashes((string)$_POST ['email'])) : '';
		$url = isset($_POST ['url']) ? trim(stripslashes((string)$_POST ['url'])) : '';
		$content = isset($_POST ['content']) ? stripslashes((string)$_POST ['content']) : '';

		// check name
		if (!$name) {
			$errors ['name'] = isset($lerr ['name']) ? $lerr ['name'] : 'name';
		}

		// check email
		if ($email) {
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$errors ['email'] = isset($lerr ['email']) ? $lerr ['email'] : 'email';
			}
		}

		// check url
		if ($url) {
			if (!filter_var($url, FILTER_VALIDATE_URL)) {
				$errors ['url'] = isset($lerr ['url']) ? $lerr ['url'] : 'url';
			}
		}

		// check content
		if (!$content) {
			$errors ['content'] = isset($lerr ['content']) ? $lerr ['content'] : 'content';
		}

		// assign error messages to template
		if ($errors) {
			$this->smarty->assign('error', $errors);
			return false;
		}
		return true;
	}

	function setup() {
		$this->nosuchcomment = !comment_exists($_REQUEST ['entry'], $_REQUEST ['comment']);
		$this->smarty->assign('entryid', $_REQUEST ['entry']);
		$this->smarty->assign('id', $_REQUEST ['comment']);
	}

	function main() {
		global $lang;
		if ($this->nosuchcomment) {
			return PANEL_REDIRECT_DEFAULT;
		}

		$e = entry_parse($_REQUEST ['entry']);
		if ($e) {
			$this->smarty->assign('entrysubject', $e ['subject']);
		} else {
			return PANEL_REDIRECT_DEFAULT;
		}

		$comment = comment_parse($_REQUEST ['entry'], $_REQUEST ['comment']);
		if ($comment) {
			$this->smarty->assign('values', $comment);
			$ip = isset($comment ['ip-address']) ? $comment ['ip-address'] : '';
			$this->smarty->append('values', array('ip_address' => $ip), true);
		} else {
			return PANEL_REDIRECT_DEFAULT;
		}
	}

	function onsave($content) {

		$success = false;

		if ($this->nosuchcomment) {
			return PANEL_REDIRECT_DEFAULT;
		}

		$comment = comment_parse($_REQUEST ['entry'], $_REQUEST ['comment']);

		if (is_array($comment) && (array_key_exists('loggedin', $comment) || user_loggedin())) {
			if (array_key_exists('loggedin', $comment)) {
				$content ['loggedin'] = $comment ['loggedin'];
			}
			if (array_key_exists('ip-address', $comment)) {
				$content ['ip-address'] = $comment ['ip-address'];
			}
			if (array_key_exists('date', $comment)) {
				$content ['date'] = $comment ['date'];
			}
			$success = comment_save($_REQUEST ['entry'], $content);
			$this->smarty->assign('success', $success ? 1 : -1);
		}

		if ($success === false || $success < 0) {
			$this->main();
			return PANEL_NOREDIRECT;
		}

		return PANEL_REDIRECT_CURRENT;
	}

	function onerror() {
		$this->main();
	return 0;
	}
}
?>

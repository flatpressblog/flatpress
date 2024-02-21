<?php
/**
 * Comment edit panel
 *
 * Type:
 * Name:
 * Date: 21.02.2024
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
		$lerr = & $lang ['admin'] ['entry'] ['commedit'] ['error'];
		$errors = array();

		// check name
		if (!$name) {
			$errors ['name'] = $lerr ['name'];
		}

		// check email
		if ($email) {
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$errors ['email'] = $lerr ['email'];
			}
		}

		// check url
		if ($url) {
			if (!filter_var($url, FILTER_VALIDATE_URL)) {
				$errors ['url'] = $lerr ['url'];
			}
		}

		// check content
		if (!$content) {
			$errors ['content'] = $lerr ['content'];
		}

		// assign error messages to template
		if ($errors) {
			$smarty->assign('error', $errors);
			return false;
		}
	}

	function setup() {
		$this->nosuchcomment = !comment_exists($_REQUEST ['entry'], $_REQUEST ['comment']);
		$this->smarty->assign('entryid', $_REQUEST ['entry']);
		$this->smarty->assign('id', $_REQUEST ['comment']);
	}

	function main() {
		global $lang;
		if ($this->nosuchcomment) return PANEL_REDIRECT_DEFAULT;

		$e = entry_parse($_REQUEST ['entry']);
		if ($e) {
			$this->smarty->assign('entrysubject', $e ['subject']);
		} else return PANEL_REDIRECT_DEFAULT;

		$comment = comment_parse($_REQUEST ['entry'], $_REQUEST ['comment']);
		if ($comment) {
			$this->smarty->assign('values', $comment);
			$this->smarty->append('values', array('ip_address' => $comment ['ip-address']), true);
		} else return PANEL_REDIRECT_DEFAULT;
	}

	function onsave($content) {
		if ($this->nosuchcomment) return PANEL_REDIRECT_DEFAULT;

		$comment = comment_parse($_REQUEST ['entry'], $_REQUEST ['comment']);
		if (isset($comment ['loggedin']))
			$content ['loggedin'] = $comment ['loggedin'];
			$content ['ip-address'] = $comment ['ip-address'];
			$content ['date'] = $comment ['date'];
			$success = comment_save($_REQUEST ['entry'], $content);
			$this->smarty->assign('success', $success ? 1 : -1);

		if ($success < 0) {
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

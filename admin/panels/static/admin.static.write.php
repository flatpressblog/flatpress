<?php

/**
 * edit static site panel
 *
 * Type:
 * Name:
 * Date:
 * Purpose:
 * Input:
 * Change-Date: 23.11.2024, by FKM
 *
 * @author NoWhereMan <real_nowhereman at users dot sf dot com>
 *
 */
class admin_static_write extends AdminPanelActionValidated {

	var $validators = array(
		array(
			'subject',
			'subject',
			'notEmpty',
			false,
			false,
			'trim'
		),
		array(
			'content',
			'content',
			'notEmpty',
			false,
			false,
			'stripslashes'
		),
		array(
			'id',
			'id',
			'isValidEntryId',
			false,
			false,
			'stripslashes'
		)
	);

	var $events = array(
		'save',
		'preview'
	);

	function _makePreview($arr, $id = null) {
		if (!$id) {
			$arr ['subject'] = apply_filters('title_save_pre', $arr ['subject']);
			$arr ['content'] = apply_filters('content_save_pre', $arr ['content']);
		}

		$this->smarty->assign('post', $arr);

		if (THEME_LEGACY_MODE) {
			theme_entry_filters($arr, $id);
		}

		$arr = array_change_key_case($arr, CASE_LOWER);

		$this->smarty->assign('entry', $arr);
		$this->smarty->assign('preview', true);

		$this->smarty->assign('id', $id);
	}

	function sanitizePageTitle($title) {

		$title = strip_tags($title);
		$title = htmlspecialchars_decode($title, ENT_QUOTES);

		$dangerous = [
			'<',
			'>',
		];

		$title = str_replace($dangerous, '', $title);

		$title = preg_replace('/\bon\w+\s*=\s*["\'][^"\']*["\']/i', '', $title);

		$allowed = '/[^\p{L}\p{N}\p{P}\p{Zs}\p{M}]/u';

		$title = preg_replace($allowed, '', $title);

		$title = trim($title);

		return $title;
	}

	function sanitizePageId($id) {

		$id = preg_replace('/\bon\w+\s*=\s*["\'][^"\']*["\']/i', '', $id);

		$allowedPattern = '/[^\p{L}\p{N}_-]/u';

		$id = preg_replace($allowedPattern, '', $id);

		$id = trim($id);

		return $id;
	}

	function makePageTitle($title, $sep) {
		global $lang;
		return $title . ' ' . $sep . ' ' . $lang ['admin'] ['static'] ['write'] ['head'];
	}

	function main() {
		global $lang;

		$this->smarty->assign('static_id', 'static' . date_time());

		if (isset($_GET ['page'])) {
			$id = $_GET ['page'];
			$arr = static_parse($id);
			// if entry does not exists,
			// we print the list
			if ($arr) {
				$this->_makePreview($arr, $id);
			} else {
				$id = '';
				$arr = array();
				$_GET ['page'] = '';
				utils_redirect('admin.php?p=static');
			}
		}

		add_filter('wp_title', array(
			&$this,
			'makePageTitle'
		), 10, 2);
	}

	function _getposteddata() {
		global $fp_config;
		$arr ['version'] = system_ver();

		$arr ['subject'] = isset($_POST ['subject']) ? $this->sanitizePageTitle($_POST ['subject']) : ($this->id ?: 'Untitled Site');

		$arr ['id'] = isset($_POST ['id']) ? $this->sanitizePageId($_POST ['id']) : 'Empty';

		$arr ['content'] = isset($_POST ['content']) ? $_POST ['content'] : 'No Content';

		$author = user_get();
		$arr ['author'] = !empty($fp_config ['general'] ['author']) ? $fp_config ['general'] ['author'] : $author ['userid'];

		$arr ['date'] = !empty($_POST ['timestamp']) ? $_POST ['timestamp'] : date_time();

		$cats = !empty($_POST ['cats']) ? $_POST ['cats'] : array();
		$flags = !empty($_POST ['flags']) ? $_POST ['flags'] : array();

		// If required, process the categories and flags here.
		// $arr['categories'] = array_merge(array_keys($flags), array_keys($cats));

		return $arr;
	}

	function onsave() {
		$oldid = isset($_GET ['page']) ? $_GET ['page'] : null;
		$id = $_POST ['id'];

		$success = static_save($this->_getposteddata(), $id, $oldid);

		$this->smarty->assign('success', $success ? 1 : -1);

		return $success;
	}

	function onpreview() {
		global $lang;

		$this->_makePreview($this->_getposteddata());

		return 0;
	}

	function onerror() {
		$this->main();
		return 0;
	}

}
?>

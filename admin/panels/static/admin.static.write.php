<?php
/**
 * edit static site panel
 *
 * Type:
 * Name:
 * Date:
 * Purpose:
 * Input:
 * Change-Date: 04.02.2026, by FKM
 *
 * @author NoWhereMan <real_nowhereman at users dot sf dot com>
 *
 */
class admin_static_write extends AdminPanelActionValidated {

	protected $id;

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

		// Content for editing: keep the storage format (with escaped separators)
		// but show the real separator character in the textarea (e.g. "|" instead of "&#124;").
		$post = $arr;
		if (function_exists('fmt_unescape_separator')) {
			if (isset($post['subject'])) {
				$post ['subject'] = fmt_unescape_separator($post ['subject']);
			}
			if (isset($post['content'])) {
				$post ['content'] = fmt_unescape_separator($post ['content']);
			}
		}

		$this->smarty->assign('post', $post);

		if (THEME_LEGACY_MODE) {
			theme_entry_filters($arr, $id);
		}

		$arr = array_change_key_case($arr, CASE_LOWER);

		$this->smarty->assign('entry', $arr);
		$this->smarty->assign('preview', true);

		$this->smarty->assign('id', $id);
	}

	function sanitizePageTitle($title) {
		global $fp_config;

		// Decode named + numeric entities early so entity-encoded "<" / ">" cannot slip through as markup later.
		$charset = !empty($fp_config ['locale'] ['charset']) ? $fp_config ['locale'] ['charset'] : 'UTF-8';
		$title = html_entity_decode((string) $title, ENT_QUOTES | ENT_HTML5, $charset);

		// Remove any HTML tags and normalize spaces.
		$title = strip_all_tags($title);
		// NBSP → space (UTF-8)
		$title = str_replace("\xC2\xA0", ' ', $title);

		// Never allow real tag delimiters in titles.
		$title = str_replace(['<', '>'], '', $title);

		// Drop control/format characters (incl. newlines, zero-width, etc.).
		$tmp = preg_replace('/\p{C}+/u', '', $title);
		if ($tmp !== null) {
			$title = $tmp;
		} else {
			// Fallback for invalid UTF-8 sequences (should be rare).
			$title = preg_replace('/[\x00-\x1F\x7F]+/', '', $title);
		}

		// Allow letters, numbers, punctuation, symbols (e.g. =, ~, £, $, €), spaces and combining marks.
		$allowed = '/[^\p{L}\p{N}\p{P}\p{S}\p{Zs}\p{M}]/u';
		$tmp = preg_replace($allowed, '', $title);
		if ($tmp !== null) {
			$title = $tmp;
		}

		// Collapse whitespace.
		$title = preg_replace('/\s+/u', ' ', $title);

		return trim($title);
	}

	function sanitizePageId($id) {

		$id = preg_replace([
			'/\bon\w+\s*=\s*["\'][^"\']*["\']/i',
			'/[<>]/', 
			'/[^\p{L}\p{N}_-]/u'
		], '', $id);

		return trim(str_replace(' ', '', $id));
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
				$this->id = $id;
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

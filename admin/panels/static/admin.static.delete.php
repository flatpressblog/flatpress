<?php

/**
 * static delete panel
 *
 * Type:
 * Name:
 * Date:
 * Purpose:
 * Input:
 *
 * @author NoWhereMan <real_nowhereman at users dot sf dot com>
 *
 */
class admin_static_delete extends AdminPanelAction {

	var $events = array(
		'delete',
		'cancel'
	);

	var $page;

	function setup() {
		// Clean up input
		$this->page = isset($_REQUEST ['page']) ? sanitize_text_field($_REQUEST ['page']) : null;
		$this->smarty->assign('pageid', $this->page);
	}

	function main() {
		if ($this->page) {
			$arr = static_parse($this->page);

			if (THEME_LEGACY_MODE) {
				theme_entry_filters($arr, null);
			}

			$this->smarty->assign('entry', $arr);
		} else {
			return 1;
		}
	}

	function ondelete() {
		// at first: check if nonce was given correctly
		check_admin_referer('admin_static_delete');

		// Clean up input
		$id = sanitize_text_field($this->page);

		// Validate static pages directly here
		if (empty($id) || !preg_match('/^[a-zA-Z0-9-_]+$/', $id)) {
			// Error status
			$this->smarty->assign('success', -1);
			return 1;
		}

		$success = static_delete($id);
		$this->smarty->assign('success', $success ? 2 : -2);
		return 1;
	}

	function oncancel() {
		return 1;
	}
}
?>

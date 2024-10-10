<?php

/**
 * entry delete panel
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
class admin_entry_delete extends AdminPanelAction {

	var $events = array(
		'delete',
		'cancel'
	);

	function main() {
		global $fpdb;

		if (isset($_REQUEST['entry'])) {
			// Clean up input
			$id = sanitize_text_field($_REQUEST ['entry']);
			if ($a = entry_parse($id)) {
				;
			} else {
				$a = draft_parse($id);
			}

			if ($a) {
				if (THEME_LEGACY_MODE) {
					theme_entry_filters($a, $id);
				}

				$this->smarty->assign('entry', $a);
				$this->smarty->assign('id', $id);
				return 0;
			}
		}

		return 1;
	}

	function ondelete() {
		// at first: check if nonce was given correctly
		check_admin_referer('admin_entry_delete');

		// Clean up input
		$id = sanitize_text_field($_REQUEST ['entry']);

		// Validate entries directly here
		if (empty($id) || !preg_match('/^[a-zA-Z0-9-_]+$/', $id)) {
			// Error status
			$this->smarty->assign('success', -1);
			return 1;
		}

		$ok = draft_delete($id) || entry_delete($id);
		$success = $ok ? 2 : -2;
		$this->smarty->assign('success', $success);
		return 1;
	}

	function oncancel() {
		return 1;
	}
}
?>

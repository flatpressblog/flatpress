<?php

/**
 * edit entry panel
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

// require (ADMIN_DIR . 'panels/entry/shared.entry.form.php');

// ---------------------------------------------------------------------
// utils
// ---------------------------------------------------------------------
function smarty_function_flag_classes($params, &$smarty) {
	$flags = entry_flags_get();
	($active_flags = array_intersect($smarty->getTemplateVars('categories'), $flags));
	return implode(' ', $active_flags);
}

class admin_entry_list extends AdminPanelActionValidated {

	var $actionname = 'list';

	function setup() {
		$this->smarty->registerPlugin('function', 'flag_classes', 'smarty_function_flag_classes');
	}

	function main() {
		// Returns an int value of 0... What for?
		// parent::main();

		// $smarty = $this->smarty;

		$this->smarty->assign('formtarget', strip_tags($_SERVER ['PHP_SELF']));
		$this->smarty->assign('categories_all', entry_categories_get('defs'));
		$this->smarty->assign('saved_flags', entry_flags_get());

		// parameters for the list
		// start offset and count (now defaults to 8...)
		$defcount = 8;
		$allowed_counts = array(8, 25, 50, 100);

		global $fpdb, $fp_config;

		if (!empty($_REQUEST ['entry'])) {
			utils_redirect('admin.php?p=entry&action=write&entry=' . $_REQUEST ['entry']);
		}

		// Handle per-page count selection with FlatPress config persistence
		$perpage_count = $defcount;
		if (isset($fp_config ['entrylist'] ['entries_perpage']) && in_array((int)$fp_config ['entrylist'] ['entries_perpage'], $allowed_counts, true)) {
			$perpage_count = (int)$fp_config ['entrylist'] ['entries_perpage'];
		}
		if (isset($_REQUEST ['count']) && in_array((int)$_REQUEST ['count'], $allowed_counts, true)) {
			$perpage_count = (int)$_REQUEST ['count'];

			// Persist selection only when it changes (avoid unnecessary disk writes)
			if (!isset($fp_config ['entrylist']) || !is_array($fp_config ['entrylist'])) {
				$fp_config ['entrylist'] = array();
			}
			if (!isset($fp_config ['entrylist'] ['entries_perpage']) || (int)$fp_config ['entrylist'] ['entries_perpage'] !== $perpage_count) {
				$fp_config ['entrylist'] ['entries_perpage'] = $perpage_count;
				// Persist to config; ignore failure (e.g. read-only FS) and keep current value for this request
				if (function_exists('config_save')) {
					@config_save();
				}
			}
		}

		// Pass per-page selector data to template
		$this->smarty->assign('perpage_options', $allowed_counts);
		$this->smarty->assign('perpage_current', $perpage_count);
		// Build base query string for per-page links (preserve filters, reset to page 1)
		$query_params = array('p' => 'entry');
		if (isset($_REQUEST ['category']) && $_REQUEST ['category'] != 'all') {
			$query_params ['category'] = $_REQUEST ['category'];
		}
		if (isset($_REQUEST ['m'])) {
			$query_params ['m'] = $_REQUEST ['m'];
		}
		if (isset($_REQUEST ['y'])) {
			$query_params ['y'] = $_REQUEST ['y'];
		}
		$this->smarty->assign('perpage_base_query', http_build_query($query_params, '', '&amp;'));

		isset($_REQUEST ['m']) ? $params ['m'] = $_REQUEST ['m'] : null;
		isset($_REQUEST ['y']) ? $params ['y'] = $_REQUEST ['y'] : null;
		// $params['start'] = isset($_REQUEST['start'])? $_REQUEST['start'] : 0;
		$params ['count'] = $perpage_count;
		$params ['page'] = isset($_REQUEST ['paged']) ? $_REQUEST ['paged'] : 1;
		isset($_REQUEST ['category']) ? $params ['category'] = $_REQUEST ['category'] : $params ['category'] = 'all';
		$params ['fullparse'] = false;
		$params ['comments'] = true;
		$fpdb->query($params);

		return 0;
	}

	function onsubmit($data = null) {
		parent::onsubmit($data);
		return $this->main();
	}

	function onfilter() {
		return $this->main();
	}

	function onerror() {
		return $this->main();
	}

}

?>

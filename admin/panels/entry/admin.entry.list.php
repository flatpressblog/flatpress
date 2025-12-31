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

		// parameters for the list
		// start offset and count (now defaults to 8...)

		$this->smarty->assign('formtarget', strip_tags($_SERVER ['PHP_SELF']));
		$this->smarty->assign('categories_all', entry_categories_get('defs'));
		$this->smarty->assign('saved_flags', entry_flags_get());

		$defcount = 8; // <-- no magic numbers! todo: add config option?
		$allowed_counts = array(8, 25, 50, 100);
		$cookie_name = 'fp_admin_entries_perpage';

		global $fpdb;

		if (!empty($_REQUEST ['entry'])) {
			utils_redirect('admin.php?p=entry&action=write&entry=' . $_REQUEST ['entry']);
		}

		// Handle per-page count selection with cookie persistence
		$perpage_count = $defcount;
		if (isset($_REQUEST['count']) && in_array((int)$_REQUEST['count'], $allowed_counts, true)) {
			$perpage_count = (int)$_REQUEST['count'];
			setcookie($cookie_name, $perpage_count, time() + 31536000, '/');
		} elseif (isset($_COOKIE[$cookie_name]) && in_array((int)$_COOKIE[$cookie_name], $allowed_counts, true)) {
			$perpage_count = (int)$_COOKIE[$cookie_name];
		}

		isset($_REQUEST ['m']) ? $params ['m'] = $_REQUEST ['m'] : null;
		isset($_REQUEST ['y']) ? $params ['y'] = $_REQUEST ['y'] : null;
		// $params['start'] = isset($_REQUEST['start'])? $_REQUEST['start'] : 0;
		$params ['count'] = $perpage_count;
		$params ['page'] = isset($_REQUEST ['paged']) ? $_REQUEST ['paged'] : 1;
		isset($_REQUEST ['category']) ? $params ['category'] = $_REQUEST ['category'] : $params ['category'] = 'all';
		$params ['fullparse'] = false;
		$params ['comments'] = true;

		// Pass per-page selector data to template
		$this->smarty->assign('perpage_options', $allowed_counts);
		$this->smarty->assign('perpage_current', $perpage_count);
		
		// Build base query string for per-page links (preserve filters, reset to page 1)
		$query_params = array('p' => 'entry');
		if (isset($_REQUEST['category']) && $_REQUEST['category'] != 'all') {
			$query_params['category'] = $_REQUEST['category'];
		}
		if (isset($_REQUEST['m'])) {
			$query_params['m'] = $_REQUEST['m'];
		}
		if (isset($_REQUEST['y'])) {
			$query_params['y'] = $_REQUEST['y'];
		}
		$this->smarty->assign('perpage_base_query', http_build_query($query_params));

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

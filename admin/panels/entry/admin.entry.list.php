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
 	
 	
 	//require (ADMIN_DIR . 'panels/entry/shared.entry.form.php');
	
	
	// ---------------------------------------------------------------------
	// utils
	// ---------------------------------------------------------------------
	
	
		function smarty_function_flag_classes($params, &$smarty) {
			$flags = entry_flags_get();
			($active_flags = array_intersect(
				$smarty->get_template_vars('categories'), $flags));
			return implode(' ', $active_flags);
		}
			
	
	class admin_entry_list extends AdminPanelActionValidated {
		
		
		var $actionname = 'list'; 
	
			
		function setup() {
			$this->smarty->register_function('flag_classes', 'smarty_function_flag_classes');
			
		}
		
		function main() {
			parent::main();
			//$smarty = $this->smarty;
			
			// parameters for the list
			// start offset and count (now defaults to 8...)
			
			$this->smarty->assign('categories_all', entry_categories_get('defs'));
			$this->smarty->assign('saved_flags', entry_flags_get());
			
			$defcount = 8; // <-- no magic numbers! todo: add config option?
			
			global $fpdb;
			
			if (!empty($_REQUEST['entry']))
				utils_redirect('admin.php?p=entry&action=write&entry='.$_REQUEST['entry']);
			
			isset($_REQUEST['m'])? $params['m'] = $_REQUEST['m'] : null;
			isset($_REQUEST['y'])? $params['y'] = $_REQUEST['y'] : null;
			// $params['start'] = isset($_REQUEST['start'])? $_REQUEST['start'] : 0;
			$params['count'] = isset($_REQUEST['count'])? $_REQUEST['count'] : $defcount;
			$params['page'] = isset($_REQUEST['paged'])? $_REQUEST['paged'] : 1; 
			isset($_REQUEST['category'])? $params['category'] = $_REQUEST['category'] : $params['category'] = 'all';
			$params['fullparse']=false;
			$params['comments']=true;
			$fpdb->query($params);
			
			return 0;
		}
		
		
		function onsubmit($data=null) {
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

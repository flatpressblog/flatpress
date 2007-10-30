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
 	
 	
 	class admin_entry_delete extends AdminPanelAction {
	
		var $events = array('delete', 'cancel');
		
		function main() {
			global $fpdb;
			
			if (isset($_REQUEST['entry'])){
				$id = $_REQUEST['entry'];
				if ($a = entry_parse($id));
				else 
					$a = draft_parse($id);
					
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
			$id=$_REQUEST['entry'];
			$ok=draft_delete($id) || entry_delete($id);
			
			$success = $ok? 2 : -2;
			$this->smarty->assign('success',$success);
			return 1;
		}
		
		function oncancel() {
			return 1;	
		}

	}		
?>

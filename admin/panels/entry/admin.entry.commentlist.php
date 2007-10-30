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

 
 	class admin_entry_commentlist extends AdminPanelAction {
 		
 		var $commands = array('delete');
 		
 		function dodelete($commentid) {
			$this->smarty->assign('success',
				comment_delete($_GET['entry'], $commentid)? 6 : -6 
			);
			return PANEL_REDIRECT_CURRENT;
 		}
		
		function main() {
			global $fpdb;
			if (isset($_GET['entry'])) {
				
				$fpdb->query("id:{$_GET['entry']},fullparse:true");
				
				return 0;
				
				
			}
			
			
			return 1;
			
		}
	}			

?>

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
		var $args = array('entry');
 		
 		function dodelete($commentid) {
			$this->smarty->assign('success',
				comment_delete($_GET['entry'], $commentid)? 1 : -1
			);
			return PANEL_REDIRECT_CURRENT;
 		}
		
		function main() {
			global $fpdb;
				
			$fpdb->query("id:{$_GET['entry']},fullparse:true");
				
			return 0;
				
		}
	}			

?>

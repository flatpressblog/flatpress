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
 	
 	
 	
	// ---------------------------------------------------------------------
	// utils
	// ---------------------------------------------------------------------
	
	
	
	class admin_static_list extends AdminPanelActionValidated {
		
		
		var $actionname = 'list'; 
		
		function main() {
			parent::main();
			$this->smarty->assign('statics', $assign = static_getlist());
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

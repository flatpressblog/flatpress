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
 	
 	
 	class admin_static_delete extends AdminPanelAction {
	
		var $events = array('delete', 'cancel');
		
		function setup() {
			$this->page = @($_REQUEST['page']);
			$this->smarty->assign('pageid', $this->page);
		}
		
		function main() {
			
			if ($this->page) {
				$arr = static_parse($this->page);
					
				if (THEME_LEGACY_MODE)
					theme_entry_filters($arr, null);
	
				$this->smarty->assign('entry', $arr);
			} else return 1;
			
		}
		
		
		function ondelete() {
			$id=$this->page;
			$success=static_delete($id);
			$this->smarty->assign('success',$success? 2 : -2);
			return 1;
		}
		
		function oncancel() {
			return 1;	
		}

	}		
?>

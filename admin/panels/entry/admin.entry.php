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
 	
	
	class admin_entry extends AdminPanel {
		
		var $panelname = "entry";
		var $actions = array(
					'list'			=> true,
					'write'			=> true,	
					'commentlist'	=> false,
					'commedit'		=> false,
					'delete'		=> false,
					'cats'			=> true,
					'stats'			=> false
					);
		var $defaultaction = 'list';
			
	}
 	
	
	
	

?>

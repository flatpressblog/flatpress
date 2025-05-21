<?php

/**
 * plugin control panel
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
 
 
 	class admin_widgets extends AdminPanel {
		
		var $panelname = "widgets";
		var $actions = array(
			'default' => true
			//, 'raw' => true
		);
			
	}
	
	function admin_widgets_checkall() {
		global $fp_widgets, $lang;
		$list = $fp_widgets->getList();
		
		if (!($list)) return array();
		$errs = array();
		
		foreach ($list as $pos => $group) {
			if (is_array($group)) {
				foreach ($group as $id) {
					list($newid) = explode(":", $id);
					$var = 'plugin_' . $newid . '_widget';
					if (!function_exists($var)) {
						$errs = sprintf($lang['admin']['widgets']['errors']['generic'], $newid);
					}			
				}
			}
		}
		
		return $errs;
			
	}
	 	
?>

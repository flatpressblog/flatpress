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
 
 	class admin_widgets_raw extends AdminPanelAction {
	 
		var $validators =  array(array('content', 'content', 'notEmpty', false, false));
		var $events = array('save');
		
		function main() {
		
		
			lang_load('admin.widgets');
			$this->smarty->assign('warnings', admin_widgets_checkall());
		
			$conf = io_load_file(CONFIG_DIR . 'widgets.conf.php');
			
			$this->smarty->assign('pluginconf', $conf);
			
			return 0;
			
			
		}
		
		function onsave() {
		
			$str=stripslashes(@$_POST['content']);

			if (!$str) {
				$this->smarty->assign('success', -1 );
				return PANEL_REDIRECT_CURRENT;
			}

			
			$tmp = $str;
			
			$tmp = str_replace('<?php', '', $tmp);
			$tmp = str_replace('<?', '', $tmp);
			$tmp = str_replace('?>', '', $tmp);
			
			if (@eval($tmp) !== false)
				$success = io_write_file(CONFIG_DIR . 'widgets.conf.php', $str);
			else 
				$success = false;
				
			$this->smarty->assign('success', ( $success )? 1 : -1 );
			
			return PANEL_REDIRECT_CURRENT;
			
		}
	
	}
 	
 ?>

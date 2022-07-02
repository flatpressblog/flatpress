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
 
 
 	class admin_entry_cats extends AdminPanelAction {
		
		var $events = array('save');
		
		
		function main() {
			
			if (isset($_GET['do']) && $_GET['do'] == 'clear') {
				$ret1 = fs_delete(CONTENT_DIR . 'categories_encoded.dat') &&
				$ret2 = fs_delete(CONTENT_DIR . 'categories.txt');
				
				$ret  = ($ret1 && $ret2) ? 2 : -2;
				
				$this->smarty->assign('success', $ret);
			}
			
			if (file_exists(CONTENT_DIR . 'categories.txt')) {
				$cats = io_load_file(CONTENT_DIR . 'categories.txt');
				$this->smarty->assign('catdefs', $cats);
			}
			
			do_action('update_categories', true);
			
			return 0;
	
			
		}
		
		
		function onsave() {
			
			$str = stripslashes( trim( @$_POST['content'] ) ) ;
			
			if ($str) {
				//$success = io_write_file(CONTENT_DIR . 'categories.txt', $str);
				$success = entry_categories_encode($str);

				$ret = 1 ;
				if ($success <= 0) {
					if ($success == -1) $ret = -3;
					elseif ($success == 0) $ret = -1;
				} else { 
					$success = io_write_file(CONTENT_DIR . 'categories.txt', $str) ? -1 : 1;
				}

				$this->smarty->assign('success', $ret);

			} else {
				$this->smarty->assign('success', -1 );
			}
			
			return PANEL_REDIRECT_CURRENT;
			
		}
		
	}
 	
 ?>

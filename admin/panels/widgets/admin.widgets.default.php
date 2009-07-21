<?php
	function admin_widgets_head() {
		echo '<script type="text/javascript" src="'.BLOG_BASEURL.ADMIN_DIR.'panels/widgets/admin.widgets.js"></script>';
	}
	add_action('wp_footer', 'admin_widgets_head');
	
 
 	class admin_widgets_default extends AdminPanelAction {
	 
		//var $validators =  array(array('content', 'content', 'notEmpty', false, false));
		var $events = array('save');
		
		
		function get_widget_lists($wlist, $wpos, &$widget_list, $registered_w, $add_empties) {
		
			if (!isset($wlist[$wpos]))
				return;
		
			$widget_list[$wpos] = array();
		
			foreach($wlist[$wpos] as $idx => $wdg) {
					
					$widget_list[$wpos][$idx] = array();
					
					$newid = $wdg; # @list($newid, $params) = explode(":", $wdg);
					
					$widget_list[$wpos][$idx]['id'] = $newid;
					
					
					if (isset($registered_w[$newid])){
						$thiswdg = $registered_w[$newid];
						
						$widget_list[$wpos][$idx]['name'] = $thiswdg['name'];
						
						if ($thiswdg['nparams'] > 0) {
							$widget_list[$wpos][$idx]['params'] = $params;
						}
						
						/*
						 *	here should go the check for
						 *	limited parameters: parameters limited to a 
						 *	particular set would mean using a <select> control
						 * 	in the template
						 *
						 */
					
					} else {

						global $lang;
					
						$widget_list[$wpos][$idx]['name'] = $newid;
						$widget_list[$wpos][$idx]['class'] = 'errors';
							
						$errs = sprintf($lang['admin']['widgets']['errors']['generic'], $newid);
						$this->smarty->append('warnings', $errs); 
						
					}
				}
				
			
			if (!$widget_list[$wpos] && !$add_empties)	
				unset($widget_list[$wpos]);
			
		
		}
		
		function main() {
		
		
			lang_load('admin.widgets');
			# $this->smarty->assign('warnings', admin_widgets_checkall());
			global $fp_widgets;
			
			
			$registered_w = get_registered_widgets();
			$registered_ws = get_registered_widgetsets(null);
			$this->smarty->assign('fp_registered_widgets', $registered_w);
			
			
			$wlist = $fp_widgets->getList();
			$widget_list = array();
			
			foreach($registered_ws as $wpos) {
				
				$widget_list[$wpos] = array();
				
				$this->get_widget_lists($wlist, $wpos, $widget_list, $registered_w, true);
				
				unset($wlist[$wpos]);
				
			}
			
			$oldwidget_list = array();
			foreach($wlist as $wpos => $c){
				$this->get_widget_lists($wlist, $wpos, $oldwidget_list, $registered_w, false);
			}
			
			$this->smarty->assign('widgetlist', $widget_list);
			$this->smarty->assign('oldwidgetlist', $oldwidget_list);
			
		
			$conf = io_load_file(CONFIG_DIR . 'widgets.conf.php');
			
			$this->smarty->assign('pluginconf', $conf);
			
			return 0;
			
			
		}
		

		function onsave() {

			$fp_widgets = isset($_POST['widgets'])? $_POST['widgets'] : array(); 	
			$success = system_save(CONFIG_DIR . 'widgets.conf.php', compact('fp_widgets'));

			$this->smarty->assign('success', ( $success )? 1 : -1 );
			
			return PANEL_REDIRECT_CURRENT;
			
		}
	
	}
?>

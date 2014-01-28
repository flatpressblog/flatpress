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

/*
	function admin_plugin_adminheader() {
		$f = ADMIN_DIR . '/panels/plugin/admin.plugin.js'; 
		echo <<<SCP
		<script src="$f" type="text/javascript"></script>
SCP;
	
	}
 	add_action('wp_head', 'admin_plugin_adminheader');
*/
 
 	class admin_plugin extends AdminPanel {
		var $panelname = 'plugin';
		var $actions = array('default'=>true);		
	}
 	
	
	class admin_plugin_default extends AdminPanelAction {
		
		
		var $commands = array('enable', 'disable'); 
		var $errors = array();
		
		function setup() {
		
			$this->pluginid = isset($_GET['plugin'])? $_GET['plugin'] : null;
			
			$pi = new plugin_indexer;
			$plist = $pi->getList();
			sort($plist);
			$this->smarty->assign('pluginlist', $plist);
			$this->errors = @$pi->getEnableds(true);
			$this->fp_plugins = $pi->enabledlist;
				
			
		
		}
		
		function dodisable($id) {
			
			$fp_plugins = $this->fp_plugins;
		
		
			$success = -1;
		
			if (plugin_exists($id)) {
				
				$success = 1;
			
				if (false !== $i = array_search($id, $fp_plugins)) {
					unset($fp_plugins[$i]);
					sort($fp_plugins); /* compact indices */
					do_action('deactivate_'.$id);
					$success = system_save(CONFIG_DIR . 'plugins.conf.php', compact('fp_plugins'));
				} else {
					$success = -1; 
				}
			
			}
			
			if ($success)
				$this->smarty->assign('success', $success);
			
			return PANEL_REDIRECT_CURRENT;
		
		}
		
		function doenable ($id) {
			$success = -1; 
			$fp_plugins = $this->fp_plugins;
		
			
			if (plugin_exists($id)) {
			
				$success = 1;
					
				if (!in_array($id, $fp_plugins)) {
					$fp_plugins[] = $id;
					sort($fp_plugins);
					plugin_load($id, false, false);
					do_action('activate_'.$id);
					$success = system_save(CONFIG_DIR . 'plugins.conf.php', compact('fp_plugins'));
				} else {
					$success = -1;
				}
			}
			
			if ($success)
				$this->smarty->assign('success', $success);
			
			return PANEL_REDIRECT_CURRENT;
			
		}
		

		
		
		function main() {
			
		
			//$conf = io_load_file(CONFIG_DIR . 'plugins.conf.php');
			
			
			$this->smarty->assign('warnings', $this->errors);
			$this->smarty->assign('enabledlist', $this->fp_plugins);
		
			
			lang_load('admin.plugin');
			
			return 0;
	
			
		}
		
		
		function onsave() {
			
			$fp_plugins = array_keys($_POST['plugin_enabled']);
			$success = system_save(CONFIG_DIR . 'plugins.conf.php', compact('fp_plugins'));
			
			$retval = ( $success )? 1 : -1 ;
			
			$this->smarty->assign('success', $retval);
			//$this->smarty->assign('pluginconf', $str);
			
			return $retval;
			
		}
		
	}
 	
 ?>

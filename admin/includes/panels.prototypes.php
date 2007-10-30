<?php

	define('PANEL_NOREDIRECT',			0);
	define('PANEL_REDIRECT_DEFAULT', 	1);
	define('PANEL_REDIRECT_CURRENT', 	2);
	

	/*
	 *
	 * GLOBALS: var $action string contains name of current panel action
	 *
	 */
	
	class AdminPanel {
		
		var $panelname = null;
		var $smarty = null;
		
		var $actions = array('default'=>false);
		var $defaultaction = 'default';
		
		var $actionpanel = null;
		
		
		function AdminPanel(&$smarty) {
			$this->smarty =& $smarty;
			if (!$this->panelname)
				trigger_error("Variable \$panelname is not defined!", E_USER_ERROR);
				
				
			
			/* get plugin panels */
			$plugactions = admin_getpanelactions($this->panelname);
			
			/* add plugged actions to system-defined */
			$this->actions = array_merge($this->actions, $plugactions);
			
			/* if # actions > 1 we won't show it in the submenu bar	*/
			/* this is just for aesthetics ;)						*/
			if ((count($this->actions) > 1) && in_array(true, $this->actions))
				$this->smarty->assign('submenu', $this->actions);
			
			
		}
		
		
		
		function &get_action(&$action) {
		
			if (!$action)
				$action = $this->defaultaction;
			
			$obj = null;
			
			if (!isset($this->actions[$action])) {
				// trigger_error("$action: 
				// No such an action was defined", E_USER_ERROR);
				$action = $this->defaultaction;
			}
			
			$this->smarty->assign('actionname', $action);
			
			$class = get_class($this) . "_$action";
			
			if (!class_exists($class)) {
				
				$f = str_replace('_','.',$class);
				
				$fname = ADMIN_DIR . "panels/{$this->panelname}/$f.php";
				
			
				if (file_exists($fname)) {
					include($fname);
					
					if (!class_exists($class)) {
						trigger_error("No classes for action $action.", E_USER_ERROR);
					}
					
					$obj =& new $class($this->smarty);
					return $obj;
					
				} else trigger_error("No script found for action $action", E_USER_ERROR);
				
			} else {
				$obj =& new $class($this->smarty);
			}
				
			return $obj;
		}
		
	}
		
	class AdminPanelAction {
		
		var $actionname = null;
		var $smarty = null;
		var $events = array();
		var $commands = array();
		
		var $langres = ''; 
		
		function AdminPanelAction(&$smarty) { 
			$this->smarty =& $smarty;
			$the_action_panel = get_class($this);
			$this->smarty->assign('admin_panel_id', $the_action_panel);
			if (!$this->langres) 
				$this->langres = 'admin.' . ADMIN_PANEL ;
		}
		
		function exec() {
			
			$this->setup();
			$result = 0; // if !=0, defaultaction for this panel is called
			
			if (empty($_POST)) {
				if ($this->commands) {
					foreach($this->commands as $cmd) {
						if (isset($_GET[ $cmd ])) {
							return $this->docommand($cmd, $_GET[ $cmd ]); 
						}
					}
				}
				
				$result = $this->main();
				lang_load($this->langres);
				
			} else {
				$result = $this->onsubmit();
			}
			
			return $result;
			
			
		}
		
		function setup() {
			
		}
		
		function main() {
			return 0;
		}
		
		/**
	 	 * Method onsubmit <br />
		 *
		 * @return int values: 
		 *	1. if you want main() method to be called;
		 *	2. if you want main() method of the defaultaction
		 *	0. if you don't want any further action to be called
		 *
		 */
		
		function onsubmit() {
			
			$returnvalue = 1;
			$valid_evts = array_intersect(array_keys($_POST), $this->events);
			
			if ($the_event=array_pop($valid_evts)) {
					$event = "on{$the_event}";
					if (method_exists($this, $event))
						$returnvalue = call_user_func(array(&$this, $event));
			}
			
			return $returnvalue;
			
		}
		
		function docommand($the_cmd, $the_val) {
			
			global $panel, $action;
			
			check_admin_referer("admin_{$panel}_{$action}_{$the_cmd}_{$the_val}");
			$cmd = "do{$the_cmd}";
			
			if (method_exists($this, $cmd))
				return call_user_func(array(&$this, $cmd), $the_val);
			
			return 1;
		}
		
		
	}
	
	class AdminPanelActionValidated extends AdminPanelAction {
		
		var $validators = array();
		
		function exec() {
				
			if (empty($_POST))
				SmartyValidate::disconnect($this->smarty);
			
			$form_id = get_class($this);
			
			SmartyValidate::connect($this->smarty);
			if (!SmartyValidate::is_registered_form($form_id))
				SmartyValidate::register_form($form_id);
				
			$this->setup();
			
			$retval = 0;
			
			if (empty($_POST)) {
				if ($validators =& $this->validators) {
					foreach ($validators as $validator) {
						$validator[6]=$form_id;
						call_user_func_array(array('SmartyValidate', 'register_validator'), $validator);
					}
				}
				
				if ($this->commands) {
					foreach($this->commands as $cmd) {
						if (isset($_GET[ $cmd ])) {
							return $this->docommand($cmd); 
						}
					}
				}
				
				lang_load($this->langres);
				$retval = $this->main();
			} else {
				$retval = $this->onsubmit();
			}
			
			return $retval;
			
		}
		
		
		function onsubmit() {
			$result = 0;
			
			if(SmartyValidate::is_valid($_POST, get_class($this))) {
				$result = parent::onsubmit();
			} else $result = $this->onerror();
			
			return $result;
			
		}
		
		function onerror() {
			return true;
		}
		
		
	}
	


?>
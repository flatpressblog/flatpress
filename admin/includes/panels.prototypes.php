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
					
					$obj = new $class($this->smarty);
					return $obj;
					
				} else trigger_error("No script found for action $action", E_USER_ERROR);
				
			} else {
				$obj = new $class($this->smarty);
			}
				
			return $obj;
		}
		
	}
		
	class AdminPanelAction {
		
		var $actionname = null;
		var $smarty = null;
		var $events = array();
		var $commands = array();
		var $args = array();
		
		var $langres = ''; 
		
		function AdminPanelAction(&$smarty) { 
			$this->smarty =& $smarty;
			$the_action_panel = get_class($this);
			$this->smarty->assign('admin_panel_id', $the_action_panel);
			if (!$this->langres) 
				$this->langres = 'admin.' . ADMIN_PANEL ;
		}
		
		function exec() {
			
			global $panel,$action;
			
			foreach($this->args as $mandatory_argument) {
				if (!isset($_REQUEST[$mandatory_argument])) {
					return PANEL_REDIRECT_DEFAULT;
				}
			}
			
			$this->setup();
			do_action("admin_{$panel}_{$action}_setup");
			
			$result = 0; // if !=0, defaultaction for this panel is called
			
			if (empty($_POST)) {
				if ($this->commands) {
					foreach($this->commands as $cmd) {
						if (isset($_GET[ $cmd ])) {
							$result = $this->docommand($cmd, $_GET[ $cmd ]);
							return apply_filters("admin_{$panel}_{$action}_{$cmd}_{$_GET[ $cmd ]}", $result);
							//return $result;
						}
					}
				}
				
				$result = $this->main();
				do_action("admin_{$panel}_{$action}_main");
				lang_load($this->langres);
				
			} else {
				$data = apply_filters("admin_{$panel}_{$action}_onsubmit", null);
				$result = $this->onsubmit($data);	
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
		
		function onsubmit($data = null) {
			
			global $panel,$action;
			
			$returnvalue = 1;
			$valid_evts = array_intersect(array_keys($_POST), $this->events);
			
			if ($the_event=array_pop($valid_evts)) {
					$event = "on{$the_event}";
					if (method_exists($this, $event)) {
						$data = apply_filters("admin_{$panel}_{$action}_{$event}", $data);
						$returnvalue = call_user_func(array(&$this, $event), $data);
					}
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
		
		function dummy() {
		
		}
		
		// just dummy, remove once transition is complete
		function exec() {
			$this->smarty->register_function('validate_init', array(&$this, 'dummy'));
			$this->smarty->register_function('validate', array(&$this, 'dummy'));
			return parent::exec();
		}
		
		function onsubmit($data=null) {
		
			global $lang, $panel, $action;
				
			$result = 0;
			
			$dummyarr = array();
			$errors = array();
			$content = array();
			$lang_loaded = false;
			$l = null;
			
			foreach ($this->validators as $valid_arr) {
			
				
				# array('subject', 'subject', 'notEmpty', false, false, 'func1,func2');
				
				list($vid, $field, $validatorname, $empty, $halt, $funcs) = $valid_arr;
				
				$includepath = SMARTY_DIR . 'plugins/';
				
				$string = @$_POST[$field];
				
				// execute functions on string
				if ($string) {
					$func_arr = explode(',', $funcs);
					foreach($func_arr as $f) {
						$string = @$f($string);
					}
				}
				
				include_once (
					$includepath . 
					'validate_criteria.' . 
					$validatorname . 
					'.php'
				);
				
				# smarty_validate_criteria_notEmpty
				
				$valid_f = 'smarty_validate_criteria_'  . $validatorname;
				
				if ( ! $valid_f($string, $empty, $dummyarr, $dummyarr ) ) {
				
					if (!$lang_loaded) {
						$lang = lang_load('admin.'.ADMIN_PANEL);
						$l = $lang['admin'][ADMIN_PANEL][ADMIN_PANEL_ACTION];
					}
					
					$errors[$field] = isset($l['error'][$field])? $l['error'][$field] : htmlspecialchars($field);
					if ($halt)
						break;
				} else {
					$content[$field] = $string;
				}
				
				
			}
			
			if(!$errors) {
				$result = parent::onsubmit($content);
			} else {
				$this->smarty->assign('error', $errors);
				
				$result = apply_filters("admin_{$panel}_{$action}_onerror", $this->onerror());
			}
			
			return $result;
			
		}
		
		function onerror() {
			return PANEL_NOREDIRECT;
		}
		
		
	}
	


?>

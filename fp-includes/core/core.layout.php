<?php

	class LayoutDefault {
		var $content;
		var $fpdb;
		var $fp_widgets;
		var $smarty;
		var $config;
		var $lang;
		
		var $tpl = 'index.tpl';
		
		var $message_queue = array();

		function LayoutDefault($content=array()) {
			
			$this->pagecontent = $content;
			
			$this->fpdb =& new FPDB;
			$GLOBALS['fpdb'] =& $this->fpdb;
					        
			$this->fp_widgets =& new widget_indexer;	
			$GLOBALS['fp_widgets'] =& $this->fp_widgets;
			
			$this->smarty =& $GLOBALS['_FP_SMARTY'];  
			
			$GLOBALS['fp_config'] =& $this->config;
			$this->config = $GLOBALS['fp_config']['general'];
			
			
			$this->theme = theme_loadsettings();
			$GLOBALS['theme'] =& $this->theme;
			
			$this->lang = lang_load();	
			$GLOBALS['lang'] =& $this->lang;
			
		//	user_loggedin() or sess_setup();
			
			plugin_loadall();
			
			// init smarty
			
			$this->smarty->compile_dir = CACHE_DIR;
			$this->smarty->cache_dir = SMARTY_DIR . 'cache/';
			$this->smarty->caching = 0;
	
	
			
			do_action('init');

		}
		
		
		function display() {
			$this->main();
			theme_init($this->smarty, $this);
			$this->smarty->display($this->tpl);
			
			unset($this->smarty);
			
			do_action('shutdown');
			
		}
		/*
		
		function post_message($module, $ring, $message) {
			$this->message_queue[$module][$ring][]=$message;
			
		}
		
		function flush_messages($module, $ring=-1) {
		
			$msg_arr=array();
			if ($ring<0)
				$ring_arr =
					array_keys($this->message_queue[$module]);
			else
				$ring_arr = array($ring);
			
			foreach($ring_arr as $this_ring) {
				$localq=& $this->message_queue[$module][$this_ring];
				foreach ($localq as $msg) {
					$msg_arr[]=$msg;
				}			
			}
			
			$this->smarty->append('err', $msg_arr);
			return $msg_arr;
		}
		*/
		
	}
		
	class Abstract_LayoutIndex extends LayoutDefault {
		
		var $tpl = 'index.tpl';
		
	}
	
	class Abstract_LayoutComment extends LayoutDefault {
		
		var $tpl = 'comments.tpl';
		
	}
	
	class Abstract_LayoutDialog extends LayoutDefault {
		
		var $tpl = 'default.tpl';
		
		function page($subject, $content, $rawcontent=false) {
			$this->pagecontent = array(
					'subject'=>$subject,
					'content'=>$content
					);
					
			if ($rawcontent) $this->smarty->assign('rawcontent', true);
			
		}
		
		function pagecontent($params, $content, &$smarty, &$repeat) {
			if ($this->pagecontent) {
				$this->smarty->assign($this->pagecontent);
				return $content;
			} else return;
			
		}
		
		function display() {
			$this->smarty->register_block('page', array(&$this, 'pagecontent'));
			parent::display();
		}
	
		
	}


?>

<?php

	// Example of use
	require_once 'defaults.php';
	require_once (INCLUDES_DIR.'includes.php');

	// this is only for index
	if (!file_exists(LOCKFILE)) {
		utils_redirect('setup.php');
	}


	class LayoutRSSFeed extends Abstract_LayoutIndex {
		
		function LayoutRSSFeed() {
			/* 
			
				RSS feeds use new theme constracts,
				which are now deprecated and that will
				be unsupported from Crescendo+1 on
				
				so let me use this hackish trick
				to force legacy mode off :P
				
				 
			*/
			define('THEME_LEGACY_MODE', false);
			parent::Abstract_LayoutIndex();
			$this->tpl = SHARED_TPLS . 'rss.tpl';
		}
		
		function main() {
			header('Content-type: application/xml;');
			$this->fpdb->query('fullparse:true');
		}	

	}

	$FLATPRESS =& new LayoutRSSFeed;
	$FLATPRESS->display();


?>

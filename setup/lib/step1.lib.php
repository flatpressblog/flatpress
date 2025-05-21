<?php
	
	function check_step() {

		global $err;

		if(check_write(SETUPTEMP_FILE, 2)) {
		
			$r =	fs_mkdir(CACHE_DIR);

			$r &=	fs_mkdir(INDEX_DIR);
				
			$r &=	fs_copy(CONFIG_DEFAULT, CONFIG_FILE);
				
			$r &=	fs_copy(FP_DEFAULTS. 'plugins.conf.php', 
						CONFIG_DIR . 'plugins.conf.php');
						
			$r &=	fs_copy(FP_DEFAULTS. 'widgets.conf.php', 
						CONFIG_DIR . 'widgets.conf.php');
		
			//$r &=	create_content();
			
			return true;
		
		}

		$err[] = 'Write error';
		
		return false;
	}
	
?>

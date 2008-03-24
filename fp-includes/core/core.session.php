<?php

	

	function sess_setup() {
		if (SESSION_PATH != '')
			session_save_path(SESSION_PATH);
		
		session_name(SESS_COOKIE);
		
		session_start();
		
	}
	
	
	function sess_add($key, $val) {
		$_SESSION[$key] = $val;
	}
	
	
	function sess_remove($key) {
		if (isset($_SESSION[$key])) {
			$oldval=$_SESSION[$key];
			unset($_SESSION[$key]);
			return $oldval;
		}
	}
	
	function sess_get($key) {
		 if (isset($_SESSION[$key]))
			 return $_SESSION[$key];
		 else return false;
	}
		
	function sess_close() {
		unset($_SESSION);
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
			session_set_cookie_params(-42000);
		}
		session_destroy();
	}

?>

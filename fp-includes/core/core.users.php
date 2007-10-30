<?php

	class user_lister extends fs_filelister {
		
		var $_varname = 'cache';
		var $_cachefile = null;
		var $_directory = USERS_DIR;
		
		function bdb_entrylister() {
			$this->_cachefile = CACHE_DIR . 'userlist.php';
			parent::cache_filelister();
		}
		
		function _checkFile($directory, $file) {
			if (fnmatch('*.php', $file)) {
				array_push($this->_list, basename($file,EXT));
				return 0;
			}
		}
		
	}

	
	function user_list(){
		$obj =& new user_lister;
		if ($users = $obj->getList()) {
			return $entry_arr;
		} else	return false;
		
		
		
	}

	function user_pwd($userid, $pwd){
		return md5($userid.$pwd);
	}


	function user_login($userid, $pwd, $params=null){
	
		global $loggedin;
		
		$loggedin = false;
		
		$user = user_get($userid);
		// $retval = 0;
		
		/*
		print_r($user);
		print_r(user_pwd($userid,$pwd));
		*/
		
		if (user_pwd($userid,$pwd) == $user['password']){
		
			$loggedin = true;
			
			// session_regenerate_id();
			
			$expire = time() + 31536000;
	
			setcookie(USER_COOKIE, $userid, $expire, COOKIEPATH, COOKIE_DOMAIN);
			setcookie(PASS_COOKIE, $user['password'], $expire, COOKIEPATH, COOKIE_DOMAIN);
		
			/*
			
			$retval = 1;
			
			sess_close();
		
			sess_setup(60*60*24*7);
			
			$retval = 1;
			
			
			sess_add('userid', $userid);
			// sess_add('userhash', $user['PWD']);
			sess_add('loggedin', true);
			sess_add('ip', $_SERVER['REMOTE_ADDR']);
			sess_add('host', $_SERVER['SERVER_ADDR']);
			sess_add('path', ABS_PATH);
			
			$user = user_get($userid);
			$user['LOGINTIME']=time();
			system_save(USERS_DIR . $user['NAME'] . ".php", compact('user'));
			*/
			
		} 
		
		return $loggedin;
	}

	function user_logout(){
		global $loggedin;
		
		if ( user_loggedin() ) {
			
			setcookie(USER_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);
			setcookie(PASS_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN);
	
			
			/*
			
			$user = user_get(sess_get('userid'));
			unset($user['LOGINTIME']);
			system_save(USERS_DIR . $user['NAME'] . ".php", compact('user'));
			sess_close();
		
			*/
			
		}
		
		$loggedin = false;
		
	}

	function user_loggedin(){
	
		global $loggedin, $fp_user;
	
		if ($loggedin)
			return $fp_user;
	
		if ( empty($_COOKIE[USER_COOKIE]) || empty($_COOKIE[PASS_COOKIE]) ) {
			$fp_user = null;
			return $loggedin = false;
		}
		
		// print_r($_COOKIE);
		
		$fp_user = user_get($_COOKIE[USER_COOKIE]);
		
		if ($fp_user) {
				$loggedin = ($_COOKIE[PASS_COOKIE] == $fp_user['password']);
		}
		
		return $fp_user; 
		
		/*
		//return true;
		if (!$loggedin && sess_get('loggedin')) {
			$user = user_get(sess_get('userid'));
			// removed: sess_get('ip') == $_SERVER['REMOTE_ADDR'] &&
			// quite stupid, as people usually will disconnect sooner or later :D
				if (sess_get('host') == $_SERVER['SERVER_ADDR'] &&
						sess_get('path') == ABS_PATH ) {
							@sess_setup(60*60*24*7);
							
							// may bug sometimes: 
							// session_regenerate_id();
							$loggedin = true;
							
				}
		} 
		*/
							
		
		return $loggedin;
	}



	function user_get($userid=null){
		if ($userid == null && ($user = user_loggedin())) {
			return $user;
		} 
		if (file_exists($f = USERS_DIR . $userid.".php")) {
			include($f);
			
			return $user;
		}
	}
	

	
	function user_add($user){
		
		$user['password']=user_pwd($user['userid'], $user['password']);
		
		return system_save(USERS_DIR . $user['userid'] . ".php", compact('user'));
		
	}


?>

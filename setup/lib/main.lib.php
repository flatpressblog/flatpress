<?php

$err = array();

function print_done_fail($label, $bool) {
	echo
	"<li>", 
	$label.' <strong style="color :' . 
	(($bool)? 'green;">DONE' : 'red;">FAILED') .
	'</strong><br />',
	"</li>\n";
}

function config_exist() {
	return file_exists(CONFIG_DIR);
}

function cache_exist() {
	return file_exists(CACHE_FILE);
}

function check_write($num = 2) {
	$ok = @io_write_file(SETUPTEMP_FILE, $num);
	return $ok;
}

function remove_checkfile() {
	$ok = @fs_delete(SETUPTEMP_FILE);
	return $ok;
}

function setupid() {
	
	global $setupid;
	if (isset($_POST['setupid'])) {
		$setupid = $_POST['setupid'];
	} else {
		$setupid = system_generate_id(BLOG_BASEURL . $_SERVER['HTTP_HOST']);
	}
	
	return $setupid;
	
}

function getstep(&$id) {

	global $err;

	$STEPS	= array('locked', 'step1', 'step2', 'step3');
	$MAXST	= count($STEPS)-1;
	
	$i = 0;
	
	$setupid = null;
	
	if (!file_exists(LOCKFILE)) {
	
		$setupid = setupid();
	
		if(!$setupid)
			die('Setup is running');

	
		if (!file_exists(SETUPTEMP_FILE)) {
			if (empty($_POST))
				$i = 0;
			else 
				$i = 1;
		} else {
			$x = explode(',', io_load_file(SETUPTEMP_FILE));
			if ($x[0]!=$setupid)
				die('Setup is running: if you are the owner, you can delete ' .
				 SETUPTEMP_FILE .' to restart');
			$i = intval($x[1]);	
		}
		
		@include("./setup/lib/{$STEPS[$i]}.lib.php");
		if (!function_exists('check_step')) :
			function check_step() {
				return true;
			}
		endif;
		
		if (check_step()) {
			++$i;
			if ($i>=$MAXST) {
				fs_delete(SETUPTEMP_FILE);
				io_write_file(LOCKFILE, "locked");
			} else {
				if ($i > 0 && !@io_write_file(SETUPTEMP_FILE, "$setupid,$i")) {
					$err[]	= 'Write error';
				} 
			}
		}
		
	}
	
	$id = $STEPS[ $i ];
	
	return $i;
}


function validate() {
	if (!ctype_alnum($_POST['fpuser']))
		$err[] = "{$_POST['fpuser']} is not a valid username. 
		Username must be alphanumeric and should not contain spaces.";
	
	if (strlen(trim(($_POST['fppwd']))) < 6)
		$err[] = "Password must contain at least 6 non-space characters";
	
	if (($_POST['fppwd'])!=($_POST['fppwd2']))
		$err[] = "Passwords did not match"; 
	
	if (!(preg_match('!@.*@|\.\.|\,|\;!', $_POST['email']) ||
			preg_match('!^.+\@(\[?)[a-zA-Z0-9\.\-]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$!', $_POST['email'])))
		$err[] = "{$_POST['email']} is not a valid email address";
	
	$www = $_POST['www'];
	if (!(preg_match('!^http(s)?://[\w-]+\.[\w-]+(\S+)?$!i', $www)
    		|| preg_match('!^http(s)?://localhost!', $www)))
			$err[] = "$www is not a valid URL";
	if ($www && $www[strlen($www)-1]!='/')
		$www .= '/';
	
	global $fp_config;
		
	$fp_config['general']['author'] = $user['userid'] = $_POST['fpuser'];
	$user['password'] = $_POST['fppwd'];
	
	$fp_config['general']['www'] = $user['www'] = $www;
	$fp_config['general']['email'] = $user['email'] = $_POST['email'];
	
	
	
	if (isset($err)) {
		$GLOBALS['err']=$err;
		return false;
	}
	
	
	$fp_config['general']['blogid'] = system_generate_id(	
								BLOG_ROOT.
								$user['www'].
								$user['email'].
								$user['userid'] 
							);
													
	config_save();

	system_hashsalt_save();
	
	user_add($user);
	
	return true;
}

function print_err() {
	global $err;
	if (isset($err)) {
		echo "<p><big>Error!</big> 
		The following errors have been encountered processing the form:</p><ul>";
		foreach ($err as $val) {
			echo "<li>$val</li>";
		}
		echo "</ul>";
	}
}

?>

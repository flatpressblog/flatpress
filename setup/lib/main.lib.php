<?php
$err = array();

function print_done_fail($label, $bool) {
	echo "<li>", $label . ' <strong style="color: ' . (($bool) ? 'green;">DONE' : 'red;">FAILED') . '</strong><br>', "</li>\n";
}

function config_exist() {
	return file_exists(CONFIG_DIR);
}

function cache_exist() {
	return file_exists(CACHE_FILE);
}

function check_write($file = SETUPTEMP_FILE, $data = 2) {
	$ok = @io_write_file($file, $data);
	return $ok;
}

function remove_checkfile() {
	$ok = @fs_delete(SETUPTEMP_FILE);
	return $ok;
}

function setupid() {
	global $setupid;
	if (isset($_POST ['setupid'])) {
		$setupid = $_POST ['setupid'];
	} else {
		$setupid = system_generate_id(BLOG_BASEURL . $_SERVER ['HTTP_HOST']);
	}

	return $setupid;
}

function getstep(&$id) {
	global $err, $lang;

	$STEPS = array(
		'locked',
		'step1',
		'step2',
		'step3'
	);
	$MAXST = count($STEPS) - 1;

	$i = 0;

	$setupid = null;

	if (!file_exists(LOCKFILE)) {

		$setupid = setupid();

		if (!$setupid) {
			die($lang ['err'] ['setuprun1']);
		}

		if (!file_exists(SETUPTEMP_FILE)) {
			if (empty($_POST)) {
				$i = 0;
			} else {
				$i = 1;
			}
		} else {
			$x = explode(',', io_load_file(SETUPTEMP_FILE));
			if ($x [0] != $setupid) {
				die($lang ['err'] ['setuprun2'] . SETUPTEMP_FILE . $lang ['err'] ['setuprun3']);
			}
			$i = intval($x [1]);
		}

		$libfile = __DIR__ . '/' . $STEPS [$i] . '.lib.php';
		if (is_file($libfile)) {
			include $libfile;
		}

		if (!function_exists('check_step')) :
			/** @phpstan-ignore-next-line */
			function check_step() {
				return true;
			}
		endif;

		if (check_step()) {
			++$i;
			if ($i >= $MAXST) {
				fs_delete(SETUPTEMP_FILE);
				io_write_file(LOCKFILE, "locked");
			} else {
				if ($i > 0 && !@io_write_file(SETUPTEMP_FILE, $setupid . "," . $i)) {
					$err [] = $lang ['err'] ['writeerror'];
				}
			}
		}
	}

	$id = $STEPS [$i];

	return $i;
}

function validate() {
	global $lang;
	$fpuser = strip_tags($_POST ['fpuser']);
	$fppwd = $_POST ['fppwd'];
	$fppwd2 = $_POST ['fppwd2'];
	$email = strip_tags($_POST ['email']);
	$www = strip_tags($_POST ['www']);
	if (!(preg_match('/^[\w]+$/u', $fpuser))) {
		$err [] = $fpuser . $lang ['err'] ['fpuser2'];
	}
	if (strlen(trim(($fppwd))) < 6) {
		$err [] = $lang ['err'] ['fppwd'];
	}
	if (($fppwd) != ($fppwd2)) {
		$err [] = $lang ['err'] ['fppwd2'];
	}
	if (!(preg_match('!@.*@|\.\.|\,|\;!', $email) || preg_match('!^.+\@(\[?)[a-zA-Z0-9\.\-]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$!', $email))) {
		$err [] = $email . $lang ['err'] ['email'];
	}
	if (!(preg_match('!^http(s)?://[\w-]+\.[\w-]+(\S+)?$!i', $www) || preg_match('!^http(s)?://localhost!', $www))) {
		$err [] = $www . $lang ['err'] ['www'];
	}
	if ($www && $www [strlen($www) - 1] != '/') {
		$www .= '/';
	}

	global $fp_config;

	$fp_config ['general'] ['author'] = $user ['userid'] = $fpuser;
	$user ['password'] = $fppwd;

	$fp_config ['general'] ['www'] = $user ['www'] = $www;
	$fp_config ['general'] ['email'] = $user ['email'] = $email;

	// Set UTC offset according to time zone set in php.ini
	$timezoneFromIni = new DateTimeZone('UTC'); // UTC as fallback value
	try {
		$timezoneFromIni = new DateTimeZone(ini_get('date.timezone'));
	} catch (Exception $e) {
		// ignore "Unknown or bad timezone" exceptions - just move on with UTC
	}
	// calculate the offset from local time zon to UTC...
	$now = new DateTime('now', $timezoneFromIni);
	$timeOffset = $timezoneFromIni->getOffset($now) / 3600;
	// ... and set it to the FlatPress config
	$fp_config ['locale'] ['timeoffset'] = $timeOffset;

	if (isset($err)) {
		$GLOBALS ['err'] = $err;
		return false;
	}

	$fp_config ['general'] ['blogid'] = system_generate_id(BLOG_ROOT . $user ['www'] . $user ['email'] . $user ['userid']);

	config_save();

	system_hashsalt_save();

	user_add($user);

	return true;
}

function print_err() {
	global $err;
	global $lang;
	if (isset($err)) {
		echo $lang ['err'] ['www'];
		foreach ($err as $val) {
			echo "<li>" . $val . "</li>";
		}
		echo "</ul>";
	}
}

?>

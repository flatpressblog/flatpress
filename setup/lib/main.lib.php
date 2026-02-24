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
	$ok = @io_write_file($file, (string)$data);
	return $ok;
}

function remove_checkfile() {
	$ok = @fs_delete(SETUPTEMP_FILE);
	return $ok;
}

function setupid() {
	global $setupid;
	if (isset($_POST ['setupid'])) {
		$setupid = (string)$_POST ['setupid'];
	} else {
		$host = '';
		if (isset($_SERVER ['HTTP_HOST']) && is_string($_SERVER ['HTTP_HOST'])) {
			$host = $_SERVER ['HTTP_HOST'];
		} elseif (isset($_SERVER ['SERVER_NAME']) && is_string($_SERVER ['SERVER_NAME'])) {
			$host = $_SERVER ['SERVER_NAME'];
		}
		$setupid = system_generate_id(BLOG_BASEURL . $host);
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
			$tmp = (string)io_load_file(SETUPTEMP_FILE);
			$x = explode(',', $tmp, 2);
			$tmp_setupid = isset($x [0]) ? (string)$x [0] : '';
			if ($tmp_setupid !== $setupid) {
				die($lang ['err'] ['setuprun2'] . SETUPTEMP_FILE . $lang ['err'] ['setuprun3']);
			}
			$i = isset($x [1]) ? (int)$x [1] : 0;
		}

		// Hardening: clamp out-of-range step indexes (e.g. if SETUPTEMP_FILE is corrupted)
		if ($i < 0) {
			$i = 0;
		} elseif ($i > $MAXST) {
			$i = $MAXST;
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
				// Ensure valid index for the final screen (step3)
				$i = $MAXST;
				fs_delete(SETUPTEMP_FILE);
				io_write_file(LOCKFILE, "locked");
			} else {
				if ($i > 0 && !@io_write_file(SETUPTEMP_FILE, $setupid . "," . $i)) {
					$err [] = $lang ['err'] ['writeerror'];
				}
			}
		}
	}

	if (!isset($STEPS [$i])) {
		$i = 0;
	}
	$id = $STEPS [$i];

	return $i;
}

function validate() {
	global $lang, $err;

	/**
	 * Validate only when the step2 form was actually submitted.
	 *  On reloads or malformed requests, avoid PHP warnings about missing POST keys.
	 */
	$required = array('fpuser', 'fppwd', 'fppwd2', 'email', 'www');
	foreach ($required as $k) {
		if (!array_key_exists($k, $_POST)) {
			$err = array();
			return false;
		}
	}

	$err = array();

	$fpuser = strip_tags((string)$_POST ['fpuser']);
	$fppwd = (string)$_POST ['fppwd'];
	$fppwd2 = (string)$_POST ['fppwd2'];
	$email = strip_tags((string)$_POST ['email']);
	$www = strip_tags((string)$_POST ['www']);
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
	if ($www !== '' && substr($www, -1) !== '/') {
		$www .= '/';
	}

	global $fp_config;

	$user = array();
	$fp_config ['general'] ['author'] = $user ['userid'] = $fpuser;
	$user ['password'] = $fppwd;

	$fp_config ['general'] ['www'] = $user ['www'] = $www;
	$fp_config ['general'] ['email'] = $user ['email'] = $email;

	// Set UTC offset according to time zone set in php.ini
	$timezoneFromIni = new DateTimeZone('UTC'); // UTC as fallback value
	try {
		$iniTz = (string)ini_get('date.timezone');
		if ($iniTz !== '') {
			$timezoneFromIni = new DateTimeZone($iniTz);
		}
	} catch (Throwable $e) {
		// ignore "Unknown or bad timezone" exceptions - just move on with UTC
	}
	try {
		// calculate the offset from local time zone to UTC...
		$now = new DateTime('now', $timezoneFromIni);
		$timeOffset = $timezoneFromIni->getOffset($now) / 3600;
		// ... and set it to the FlatPress config
		$fp_config ['locale'] ['timeoffset'] = $timeOffset;
	} catch (Throwable $e) {
		// As a last-resort fallback, keep FlatPress at UTC.
		$fp_config ['locale'] ['timeoffset'] = 0;
	}

	if (!empty($err)) {
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
	if (!empty($err)) {
		echo "<ul>";
		foreach ($err as $val) {
			echo "<li>" . $val . "</li>";
		}
		echo "</ul>";
	}
}

?>

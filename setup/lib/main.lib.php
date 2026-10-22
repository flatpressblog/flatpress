<?php
$err = array();
require_once __DIR__ . '/permissions.lib.php';

/**
 * Persist the owner token before any installation mutation; fail closed on session storage errors. 
 */
function setup_session_persist($token) {
	if (!@session_write_close()) {
		return false;
	}
	$_SESSION = [];
	if (!@session_start()) {
		return false;
	}
	return setup_session_matches($token);
}

/**
 * Read the session handler's freshly loaded data, independently of previous in-memory state.
 */
function setup_session_matches($token) {
	return isset($_SESSION ['fp_setup_token']) && is_string($_SESSION ['fp_setup_token']) && hash_equals($token, $_SESSION ['fp_setup_token']);
}

/**
 * Journal only public identifiers and hash fingerprints, never plaintext passwords.
 */
function setup_checkpoint($changes) {
	global $fp_setup_state;
	if (!is_array($fp_setup_state)) {
		return false;
	}
	$next = array_merge($fp_setup_state, $changes);
	if (!io_write_file(SETUPTEMP_FILE, json_encode($next), ['preserve_existing' => true, 'fsync' => true])) {
		return false;
	}
	$fp_setup_state = $next;
	return true;
}

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
	$setupid = '';
	if (session_status() !== PHP_SESSION_ACTIVE || session_name() !== SESS_COOKIE || !empty($GLOBALS ['fp_setup_cookie_error'])) {
		return $setupid;
	}
	if (!isset($_SESSION ['fp_setup_token']) || !is_string($_SESSION ['fp_setup_token']) || !preg_match('/^[a-f0-9]{64}$/D', $_SESSION ['fp_setup_token'])) {
		$_SESSION ['fp_setup_token'] = bin2hex(random_bytes(32));
	}
	$setupid = $_SESSION ['fp_setup_token'];
	return $setupid;
}

function getstep(&$id) {
	global $err, $lang, $fp_setup_state;
	$id = 'locked';
	if (is_file(LOCKFILE) && !is_link(LOCKFILE)) {
		return 0;
	}
	$id = 'step1';
	if (file_exists(LOCKFILE) || is_link(LOCKFILE)) {
		setup_filesystem()->error('permission_type', LOCKFILE); return 1;
	}
	$token = setupid();
	if ($token === '') {
		$err [] = $lang ['err'] ['session']; return 1;
	}
	$isPost = ($_SERVER ['REQUEST_METHOD'] ?? 'GET') === 'POST';
	if ($isPost && (!isset($_POST ['setupid']) || !is_string($_POST ['setupid']) || !hash_equals($token, $_POST ['setupid']))) {
		$err [] = $lang ['err'] ['request'];
		return 1;
	}
	if (!setup_session_persist($token)) {
		$err [] = $lang ['err'] ['session'];
		return 1;
	}
	$fs = setup_filesystem();
	if (!$fs->safe(SETUPTEMP_FILE)) {
		return 1;
	}
	// Read-only ownership check before changing any permissions or creating the mutex.
	$state = ['token_hash' => hash('sha256', $token), 'step' => 1];
	if (file_exists(SETUPTEMP_FILE)) {
		$state = setup_read_state($token);
		if ($state === false) {
			return 1;
		}
	} elseif (setup_has_accounts()) {
		$err [] = $lang ['err'] ['state']; return 1;
	}
	if (!$isPost) {
		$id = 'step' . $state ['step']; return $state ['step'];
	}
	// One installation writer at a time, including the final lock transition.
	if (!$fs->ensure(FP_CONTENT, DIR_PERMISSIONS) || !$fs->safe(FP_CONTENT . '.setup-install.lock')) {
		return 1;
	}
	$lockPath = FP_CONTENT . '.setup-install.lock';
	$lock = @fopen($lockPath, 'c+b');
	if ($lock === false) {
		$err [] = $lang ['err'] ['writeerror'];
		return 1;
	}
	try {
		if (!flock($lock, LOCK_EX | LOCK_NB)) {
			$err [] = $lang ['err'] ['state'];
			return 1;
		}
		if (!$fs->apply($lockPath, 0600, true)) {
			return 1;
		}
		clearstatcache();
		if (file_exists(LOCKFILE)) {
			$id = 'locked';
			return 0;
		}
		$state = ['token_hash' => hash('sha256', $token), 'step' => 1];
		if (file_exists(SETUPTEMP_FILE)) {
			$state = setup_read_state($token);
			if ($state === false) {
				return 1;
			}
		} elseif (setup_has_accounts()) {
			$err [] = $lang ['err'] ['state'];
			return 1;
		}
		$fp_setup_state = $state;
		$i = $state ['step'];
		$id = 'step' . $i;
		if (!setup_checkpoint([])) {
			$err [] = $lang ['err'] ['writeerror']; return $i;
		}
		if (!setup_data_preflight(true)) {
			return $i;
		}
		if ($i === 1) {
			require_once __DIR__ . '/step1.lib.php';
			if (!setup_prepare_files()) {
				return 1;
			}
			if (!setup_checkpoint(['step' => 2])) {
				$err [] = $lang ['err'] ['writeerror'];
				return 1;
			}
			$id = 'step2'; return 2;
		}
		require_once __DIR__ . '/step2.lib.php';
		if (!setup_create_content()) {
			return 2;
		}
		if (!setup_core_permissions() || !setup_data_preflight(true)) {
			return 2;
		}
		// Keep the resumable state until the completion marker is durable.
		if (!io_write_file(LOCKFILE, 'locked', ['preserve_existing' => true, 'fsync' => true])) {
			$err [] = $lang ['err'] ['writeerror'];
			return 2;
		}
		@unlink(SETUPTEMP_FILE);
		unset($_SESSION ['fp_setup_token'], $_SESSION ['fp_setup_user'], $_SESSION ['fp_setup_entry'], $_SESSION ['fp_setup_blogid']);
		$id = 'step3';
		return 3;
	} finally {
		flock($lock, LOCK_UN);
		fclose($lock);
	}
}

function setup_read_state($token) {
	global $err, $lang;
	$raw = is_file(SETUPTEMP_FILE) ? @file_get_contents(SETUPTEMP_FILE, false, null, 0, 16385) : false;
	$state = is_string($raw) && strlen($raw) <= 16384 ? json_decode($raw, true) : null;
	if (!is_array($state) || !isset($state ['token_hash'], $state ['step']) || !is_string($state ['token_hash']) || !in_array($state ['step'], [1, 2], true) || !hash_equals(hash('sha256', $token), $state ['token_hash'])) {
		$err [] = $lang ['err'] ['state'];
		return false;
	}
	if ((isset($state['blogid']) && !is_string($state ['blogid'])) || (isset($state ['entry_date']) && !is_int($state ['entry_date'])) || (isset($state ['account']) && (!is_array($state ['account']) || !isset($state ['account'] ['userid_hash'], $state ['account'] ['fingerprint']) || !is_string($state ['account'] ['userid_hash']) || !is_string($state ['account'] ['fingerprint']) || !preg_match('/^[a-f0-9]{64}$/D', $state ['account'] ['userid_hash']) || !preg_match('/^[a-f0-9]{64}$/D', $state ['account'] ['fingerprint'])))) {
		$err [] = $lang ['err'] ['state'];
		return false;
	}
	return $state;
}

function setup_has_accounts() {
	foreach ((array)@glob(USERS_DIR . '*.php') as $file) {
		if (is_string($file) && is_valid_userid(basename($file, '.php'))) {
			return true;
		}
	}
	return false;
}

function validate() {
	global $lang, $err, $fp_setup_state;

	/**
	 * Validate only when the step2 form was actually submitted.
	 *  On reloads or malformed requests, avoid PHP warnings about missing POST keys.
	 */
	$required = array('fpuser', 'fppwd', 'fppwd2', 'email', 'www');
	foreach ($required as $k) {
		if (!isset($_POST [$k]) || !is_string($_POST [$k])) {
			$err = array($lang ['err'] ['request']);
			return false;
		}
	}

	$err = array();

	$fpuser = $_POST ['fpuser'];
	$fppwd = $_POST ['fppwd'];
	$fppwd2 = $_POST ['fppwd2'];
	$email = $_POST ['email'];
	$www = $_POST ['www'];
	if (!is_valid_userid($fpuser)) {
		$err [] = $fpuser . $lang ['err'] ['fpuser2'];
	}
	if (strlen($fppwd) < 6 || preg_match('/\s|\x00/', $fppwd)) {
		$err [] = $lang ['err'] ['fppwd'];
	}
	if (strlen($fppwd) > 72) {
		$err [] = $lang ['err'] ['password_max'];
	}
	if ($fppwd !== $fppwd2) {
		$err [] = $lang ['err'] ['fppwd2'];
	}
	if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
		$err [] = $email . $lang ['err'] ['email'];
	}
	$parts = @parse_url($www);
	if (!is_array($parts) || !isset($parts ['scheme'], $parts ['host']) || !in_array(strtolower($parts ['scheme']), ['http', 'https'], true) || filter_var($www, FILTER_VALIDATE_URL) === false || preg_match('/[\s\x00-\x1f\x7f<>"\'`\\\\]/', $www) || isset($parts ['user']) || isset($parts ['pass']) || isset($parts ['query']) || isset($parts ['fragment'])) {
		$err [] = $www . $lang ['err'] ['www'];
	}
	if ($www !== '' && substr($www, -1) !== '/') {
		$www .= '/';
	}

	global $fp_config;

	$user = array();
	$fp_config ['general'] ['author'] = $user ['userid'] = $fpuser;
	// Setup receives plaintext even when it happens to look like a password hash.
	$user ['password'] = $fppwd;

	$fp_config ['general'] ['www'] = $user ['www'] = $www;
	$fp_config ['general'] ['trust_forwarded_scheme'] = function_exists('fp_should_trust_forwarded_scheme_for_url') ? fp_should_trust_forwarded_scheme_for_url($www) : false;
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

	if (!setup_filesystem()->safe(USERS_DIR . $fpuser . '.php')) {
		return false;
	}
	try {
		$existing = user_get($fpuser);
	} catch (Throwable $exception) {
		$err [] = $lang ['err'] ['account'];
		return false;
	}
	if ($existing !== null && (!isset($existing ['password']) || !is_string($existing ['password']))) {
		$err [] = $lang ['err'] ['account'];
		return false;
	}
	$account = $fp_setup_state['account'] ?? null;
	if (($account !== null && (!is_array($account) || ($account ['userid_hash'] ?? null) !== hash('sha256', $fpuser))) || ($existing !== null && (!is_array($account) || !isset($account ['fingerprint']) || !is_string($account ['fingerprint']) || !hash_equals(hash('sha256', $existing ['password']), $account ['fingerprint']) || !password_verify($fppwd, $existing ['password'])))) {
		$err [] = $lang ['err'] ['account'];
		return false;
	}
	try {
		$user ['password'] = $existing !== null ? $existing ['password'] : user_pwd($fppwd);
	} catch (Throwable $exception) {
		$err [] = $lang ['err'] ['account'];
		return false;
	}
	if (!is_string($user ['password']) || $user ['password'] === '') {
		$err [] = $lang ['err'] ['account'];
		return false;
	}
	$blogid = $fp_setup_state ['blogid'] ?? system_generate_id(BLOG_ROOT . $user ['www'] . $user ['userid']);
	if (!is_string($blogid) || !setup_checkpoint(['blogid' => $blogid, 'account' => ['userid_hash' => hash('sha256', $fpuser), 'fingerprint' => hash('sha256', $user ['password'])]])) {
		$err [] = $lang ['err'] ['writeerror'];
		return false;
	}
	$fp_config ['general'] ['blogid'] = $blogid;
	if (!config_save() || (file_exists(HASHSALT_FILE) && !is_file(HASHSALT_FILE)) || !system_hashsalt_save()) {
		$err [] = $lang ['err'] ['writeerror'];
		return false;
	}
	if (!user_add($user)) {
		$err [] = $lang ['err'] ['account'];
		return false;
	}
	$_SESSION ['fp_setup_user'] = $fpuser;

	return true;
}

function print_err() {
	global $err;
	global $lang;
	if (!empty($err)) {
		echo "<ul>";
		foreach ($err as $val) {
			echo "<li>" . htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</li>";
		}
		echo "</ul>";
	}
}

?>

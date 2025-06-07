<?php
if ($_SERVER ['SERVER_NAME'] === 'localhost') {
	@ini_set('display_errors', 'on');
	error_reporting(E_ALL);
} else {
	@ini_set('display_errors', 'off');
	error_reporting(0);
}

function owner_has_write_permissions($path) {
	if (!file_exists($path)) {
		return false;
	}
	$is_writable = is_writable($path);
	$perms = fileperms($path);
	if ($perms === false) {
		return false;
	}
	// -3, 1 owner, -1 others, -2 group/ others
	$octal_perms = substr(sprintf('%o', $perms), -3, 1);
	return $is_writable && (
		// 2: Write permission for the group/others.
		// 6: Read and write permission for the group/ others.
		// 7: Full access (read, write, execute) for the group/others.
		strpos($octal_perms, '2') !== false || strpos($octal_perms, '6') !== false || strpos($octal_perms, '7') !== false
	);
}

// Changing file/directory permissions recursively
function chmod_r($path, $filemode, $dirmode) {

	if (!file_exists($path)) {
		trigger_error('[FlatPress Setup] Path does not exist: ' . $path, E_USER_WARNING);
		return false;
	}

	if (!owner_has_write_permissions($path)) {
		trigger_error('[FlatPress Setup] The owner does not have write permission for the path: ' . $path, E_USER_WARNING);
		return false;
	}

	if (is_dir($path)) {
		if (!@chmod($path, $dirmode)) {
			$dirmode_str = decoct($dirmode);
			trigger_error('[FlatPress Setup] Failed to apply permissions ' . $dirmode_str . ' on directory: ' . $path, E_USER_WARNING);
			return false;
		}

		$dir = new DirectoryIterator($path);
		foreach ($dir as $fileinfo) {
			if ($fileinfo->isDot() || $fileinfo->getFilename() === '.git') {
				// Skip '.', '..', and '.git'
				continue;
			}
			chmod_r($fileinfo->getPathname(), $filemode, $dirmode);
		}
	} elseif (is_file($path)) {
		if (!@chmod($path, $filemode)) {
			$filemode_str = decoct($filemode);
			trigger_error('[FlatPress Setup] Failed to apply permissions ' . $filemode_str . ' on file: ' . $path, E_USER_WARNING);
			return false;
		}
	} elseif (is_link($path)) {
		trigger_error('[FlatPress Setup] Skipping symlink: ' . $path, E_USER_WARNING);
		return false;
	} else {
		trigger_error('[FlatPress Setup] Unknown file type: ' . $path, E_USER_WARNING);
		return false;
	}

	//var_dump($path); var_dump(decoct($filemode)); var_dump(decoct($dirmode)); 
	return true;
}

// is defined in the defaults.php file
chmod_r(BASE_DIR, FILE_PERMISSIONS, DIR_PERMISSIONS);

// Sets the local language based on the browser
$language = @$_POST ['language'] ? $_POST ['language'] : $browserLang;

$lf = 'lang.' . $language . '.php';
if (!preg_match('|^lang\.[a-z]{2}-[a-z]{2}\.php$|', $lf)) {
	die ('Error with lang file');
}

include __DIR__ . '/../setup/lang/' . $lf;
include __DIR__ . '/../setup/lib/main.lib.php';

$step = null;

$id = getstep($step);

$l = &$lang [$step];

include __DIR__ . '/../setup/tpls/header.tpl.php';
include __DIR__ . '/../setup/tpls/' . $step . '.tpl.php';
include __DIR__ . '/../setup/tpls/footer.tpl.php';

?>

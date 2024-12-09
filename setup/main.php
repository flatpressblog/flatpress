<?php
//@error_reporting($_SERVER ['SERVER_NAME'] == "localhost" ? E_ALL : 0);

// Changing file/directory permissions recursively
function chmod_r($path, $filemode, $dirmode) {

	if (!file_exists($path)) {
		trigger_error('Path does not exist: ' . $path, E_USER_WARNING);
		return false;
	}

	if (is_dir($path)) {
		if (!@chmod($path, $dirmode)) {
			$dirmode_str = decoct($dirmode);
			trigger_error('Failed to apply permissions ' . $dirmode_str . ' on directory: ' . $path, E_USER_WARNING);
			return false;
		}

		$dir = new DirectoryIterator($path);
		foreach ($dir as $fileinfo) {
			if ($fileinfo->isDot()) {
				// Skip '.' and '..'
				continue;
			}
			chmod_r($fileinfo->getPathname(), $filemode, $dirmode);
		}
	} elseif (is_file($path)) {
		if (!@chmod($path, $filemode)) {
			$filemode_str = decoct($filemode);
			trigger_error('Failed to apply permissions ' . $filemode_str . ' on file: ' . $path, E_USER_WARNING);
			return false;
		}
	} elseif (is_link($path)) {
		trigger_error('Skipping symlink: ' . $path, E_USER_WARNING);
		return false;
	} else {
		trigger_error('Unknown file type: ' . $path, E_USER_WARNING);
		return false;
	}

	//var_dump($path); var_dump(decoct($filemode)); var_dump(decoct($dirmode)); 
	return true;
}

// is defined in the defaults.php file
chmod_r(BASE_DIR, FILE_PERMISSIONS, DIR_PERMISSIONS);

// Sets the local language based on the browser
$language = @$_POST ['language'] ? $_POST ['language'] : $browserLang;

$lf = "lang." . $language . ".php";
if (!preg_match('|^lang\.[a-z]{2}-[a-z]{2}\.php$|', $lf)) {
	die ('Error with lang file');
}

include('./setup/lang/' . $lf);
include('./setup/lib/main.lib.php');

$step = null;

$id = getstep($step);

$l =& $lang [$step];

include ("./setup/tpls/header.tpl.php");
include ("./setup/tpls/" . $step . ".tpl.php");
include ("./setup/tpls/footer.tpl.php");

?>

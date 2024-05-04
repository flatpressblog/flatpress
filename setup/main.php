<?php
//@error_reporting($_SERVER ['SERVER_NAME'] == "localhost" ? E_ALL : 0);

// Changing file/directory permissions recursively
function chmod_r($path, $filemode, $dirmode) {
	if (!file_exists($path)) {
		trigger_error('Failed file not exists ' . $path, E_USER_WARNING);
		return false;
	}
	if (is_dir($path)) {
		if (!chmod($path, $dirmode)) {
			$dirmode_str = decoct($dirmode);
			trigger_error('Failed applying filemode ' . $dirmode_str . ' on directory ' . $path, E_USER_WARNING);
			trigger_error('  `-> the directory ' . $path . ' will be skipped from recursive chmod', E_USER_WARNING);
			return false;
		}
		$dh = opendir($path);
		while (($file = readdir($dh)) !== false) {
			if($file != '.' && $file != '..') {
				// skip self and parent pointing directories
				$fullpath = $path . '/' . $file;
				chmod_r($fullpath, $filemode, $dirmode);
			}
		}
		closedir($dh);
	} elseif (is_file($path)) {
		if (!chmod($path, $filemode)) {
			$filemode_str = decoct($filemode);
			trigger_error('Failed applying filemode ' . $filemode_str . ' on file ' . $path, E_USER_WARNING);
			return false;
		}
	} elseif (is_link($path)) {
		trigger_error('link ' . $path . ' is skipped', E_USER_WARNING);
		return false;
	}
	//var_dump($path); var_dump(decoct($filemode)); var_dump(decoct($dirmode)); 
}

// is defined in the defaults.php file
chmod_r(BASE_DIR, FILE_PERMISSIONS, DIR_PERMISSIONS);

// Sets the local language based on the browser
$language = @$_POST ['language'] ? $_POST ['language'] : $browserLang;

$lf = "lang.$language.php";
if (!preg_match('|^lang\.[a-z]{2}-[a-z]{2}\.php$|', $lf))
	die('Error with lang file');

include('./setup/lang/' . $lf);
include('./setup/lib/main.lib.php');

$step = null;

$id = getstep($step);

$l =& $lang [$step];

include("./setup/tpls/header.tpl.php");
include("./setup/tpls/{$step}.tpl.php");
include("./setup/tpls/footer.tpl.php");

?>

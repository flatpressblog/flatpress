<?php
//@error_reporting($_SERVER ['SERVER_NAME'] == "localhost" ? E_ALL : 0);

// Changing file/directory permissions recursively
$start_dir = BASE_DIR; // Starting directory
$perms ['file'] = FILE_PERMISSIONS; // chmod value for files
$perms ['folder'] = DIR_PERMISSIONS; // chmod value for folders

function chmod_r($dir) {
	global $perms;

	$dp = @opendir($dir);
	while($file = readdir($dp)) {
		if (($file == ".") || ($file == ".."))
		continue;

		$fullPath = $dir . '/' . $file;

		if(is_dir($fullPath)) {
			// echo('DIR:' . $fullPath . "\n");
			@chmod($fullPath, $perms ['folder']);
			chmod_r($fullPath, $perms ['folder'], $perms ['file']);
		} else {
			// echo('FILE:' . $fullPath . "\n");
			@chmod($fullPath, $perms ['file']);
		}
	}
	closedir($dp);
}

chmod_r($start_dir, $perms ['folder'], $perms ['file']);
  
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

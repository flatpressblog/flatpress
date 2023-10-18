<?php
error_reporting($_SERVER ["SERVER_NAME"] == "localhost" ? E_ALL : 0);

// Changing file/directory permissions recursively
$start_dir = FP_CONTENT; // Starting directory
$perms ['file'] = FILE_PERMISSIONS; // chmod value for files
$perms ['folder'] = DIR_PERMISSIONS; // chmod value for folders

function chmod_file_folder($dir) {
	global $perms;

	$dh = @opendir($dir);

	if ($dh) {

		while (false !== ($file = readdir($dh))) {

			if ($file != "." && $file != "..") {

				$fullpath = $dir . '/' . $file;
				if (!is_dir($fullpath)) {

					chmod($fullpath, $perms ['file']);
				} else {
					chmod($fullpath, $perms ['folder']);
					chmod_file_folder($fullpath);
				}
			}
		}
		closedir($dh);
	}
}

$language = @$_POST ['language'] ?$_POST ['language'] : $browserLang;

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

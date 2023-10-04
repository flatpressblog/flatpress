<?php
error_reporting($_SERVER ["SERVER_NAME"] == "localhost" ? E_ALL : 0);
chmod("./fp-content/", 0775);

$language = @$_POST ['language']? $_POST ['language'] : $browserLang;

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

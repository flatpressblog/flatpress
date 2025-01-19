<?php
$lang ['admin'] ['config'] ['default'] = array(
	'head' => 'Configuration',
	'descr' => 'Customize and configure your FlatPress installation.',
	'submit' => 'Save Changes',

	'sysfset' => 'General System Informations',
	'syswarning' => '<big>Warning!</big> These informations are critical and need to be correct, or FlatPress will (probably) refuse to work properly.',
	'blog_root' => '<strong>Absolute path to FlatPress</strong>. Note: ' . //
		'generally you won\'t have to edit this, anyway be careful, because we can\'t check whether is correct or not.',
	'www' => '<strong>Blog root</strong>. URL to your blog, complete of subdirectories.<br>' . //
		'e.g.: http://www.mydomain.com/flatpress/ (trailing slash needed)',

	// ------
	'gensetts' => 'General settings',
	'adminname' => 'Administrator Name',
	'adminpassword' => 'New password',
	'adminpasswordconfirm' => 'Repeat password',
	'blogtitle' => 'Blog title',
	'blogsubtitle' => 'Blog subtitle',
	'blogfooter' => 'Blog footer',
	'blogauthor' => 'Blog author',
	'startpage' => 'The home page of this web site is',
	'stdstartpage' => 'my blog (default)',
	'blogurl' => 'Blog URL',
	'blogemail' => 'Blog email',
	'notifications' => 'Notifications',
	'mailnotify' => 'Enable email notification for comments',
	'blogmaxentries' => 'Number of posts per page',
	'langchoice' => 'Language',

	'intsetts' => 'International settings',
	'utctime' => '<abbr title="Universal Coordinated Time">UTC</abbr> time is',
	'timeoffset' => 'Time should differ by',
	'hours' => 'hours',
	'timeformat' => 'Default format for time',
	'dateformat' => 'Default format for date',
	'dateformatshort' => 'Default format for date (short)',
	'output' => 'Output',
	'charset' => 'Character set',
	'charsettip' => 'The character set you write your blog in (UTF-8 is ' . //
		'<a class="hint" href="https://wiki.flatpress.org/doc:techfaq#character_encoding" target="_blank" title="Which character encoding standards are supported by FlatPress?">recommended</a>).'
);

$lang ['admin'] ['config'] ['default'] ['msgs'] = array(
	1 => 'Configuration has been saved successfully.',
	2 => 'The administrator has been changed. You will now be logged out.',
	-1 => 'An error occurred while trying to save the configuration.'
);

$lang ['admin'] ['config'] ['default'] ['error'] = array(
	'www' => 'Blog root must be a valid URL',
	'title' => 'You must specify a title',
	'email' => 'Email must have a valid format',
	'maxentries' => 'You didn\'t enter a valid number of entries',
	'timeoffset' => 'You didn\'t enter a valid time offset! You can use floating point (e.g. 2h30" => 2.5)',
	'timeformat' => 'You must insert a format string for time',
	'dateformat' => 'You must insert a format string for date',
	'dateformatshort' => 'You must insert a format string for date (short)',
	'charset' => 'You must insert a charset id',
	'lang' => 'The language you chose is not available',
	'admin' => 'The name of the administrator may only contain letters, numbers and 1 underscore.',
	'password' => 'The password must contain at least 6 characters and must not contain any spaces.',
	'confirm_password' => 'The passwords do not match.'
);
?>

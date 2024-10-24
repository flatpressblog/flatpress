<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'I can\'t find or create an <code>.htaccess</code> file in your root ' . //
		'directory. PrettyURLs might not work properly, see the config panel.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLs Config';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'PrettyURLs Configuration',
	'description1' => 'Here you can turn the standard FlatPress URLs into beautiful, SEO-friendly URLs.',
	'fpprotect_is_on' => 'The PrettyURLs plugin requires an .htaccess file. ' . //
		'To create or modify this file, activate the option in the <a href="admin.php?p=config&action=fpprotect" title="go to FlatPress Protect Plugin">FlatPress Protect Plugin</a>. ',
	'fpprotect_is_off' => 'The FlatPress Protect plugin protects the .htaccess file from unintentional changes. ' . //
		'You can activate the plugin <a href="admin.php?p=plugin&action=default" title="Go to the plugin administration">here</a>!',
	'nginx' => 'PrettyURLs with NGINX',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => 'This editor allows you to edit the <code>.htaccess</code>required for the PrettyURLs plugin directly.<br>' . //
		'<strong>Note:</strong> Only web servers that are NCSA compatible, such as Apache, are familiar with the concept of .htaccess files. ' . //
		'Your server software is: <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>',
	'cantsave' => 'You can\'t edit this file, because it\'s not <strong>writable</strong>. You can give writing permission or copy and paste to a file and then upload manually.',
	'mode' => 'Mode',
	'auto' => 'Automatic',
	'autodescr' => 'try to guess the best choice for me',
	'pathinfo' => 'Path Info',
	'pathinfodescr' => 'e.g. /index.php/2024/01/01/hello-world/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => 'e.g. /?u=/2024/01/01/hello-world/',
	'pretty' => 'Pretty',
	'prettydescr' => 'e.g. /2024/01/01/hello-world/',

	'saveopt' => 'Save settings',

	'location' => '<strong>Storage location:</strong> ' . ABS_PATH . '',
	'submit' => 'Save .htaccess'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess saved successfully',
	-1 => '.htaccess could not be saved (do you have writing permissions on <code>' . BLOG_ROOT . '</code>)?',

	2 => 'Options saved successfully',
	-2 => 'An error occurred attempting to save settings',
);
?>

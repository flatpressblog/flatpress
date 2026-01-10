<?php
$lang ['admin'] ['maintain'] ['submenu'] ['support'] = 'Prikaži podatke o podpori';

$lang ['admin'] ['maintain'] ['support'] = array(
	'title' => 'Podporni podatki',
	'intro' => 'Za poročila o napakah in pomoč obiščite <a href="https://forum.flatpress.org" target="_blank">FlatPress forum</a>, ' . //
		'prijavite napako na <a href="https://github.com/flatpressblog/flatpress/issues" target="_blank">GitHub</a> ' . //
		'ali <a href="mailto:hello@flatpress.org">pošljite e-pošto</a>.<br>Polepite te težave (copy &#38; paste) v angleščini ' . //
		'z naslednjimi informacijami: Opis napake, koraki za reprodukcijo.',

	// output "Setup"
	'h2_general' => 'General',
	'h3_setup' => 'Setup',

	'version' => '<p class="output"><strong>FlatPress version:</strong> ',
	'basedir' => '<p class="output"><strong>Base directory:</strong> ',
	'blogbaseurl' => '<p class="output"><strong>Blog base URL:</strong> ',

	'pos_theme' => '<p class="output"><strong>Theme:</strong> ',
	'neg_theme' => '<p class="output"><strong>Theme:</strong> not set (default is leggero)</p>',

	'pos_style' => '<p class="output"><strong>Style:</strong> ',
	'neg_style' => '<p class="output"><strong>Style:</strong> default style</p>',

	'pos_plugins' => '<p class="output"><strong>Activated plugins:</strong> ',
	'neg_plugins' => '<p class="output"><strong>Activated plugins:</strong> Could not be determined.</p>',

	// output "International"
	'h3_international' => 'International',

	'pos_LANG_DEFAULT' => '<p class="output"><strong>Language (automatic):</strong> ',
	'neg_LANG_DEFAULT' => '<p class="output"><strong>Language (automatic): &#8505;</strong> not recognized</p>',

	'pos_lang' => '<p class="output"><strong>Language (set):</strong> ',
	'neg_lang' => '<p class="output"><strong>Language (set):</strong> not set</p>',

	'pos_charset' => '<p class="output"><strong>Character set:</strong> ',
	'neg_charset' => '<p class="output"><strong>Character set:</strong> not set (default is utf-8)</p>',

	'global_date_time' => '<p class="output"><strong>UTC date, time:</strong> ',
	'neg_global_date_time' => 'Could not be determined.</p>',

	'local_date_time' => '<p class="output"><strong>LCL date, time:</strong> ',
	'neg_local_date_time' => 'Could not be determined.</p>',

	'time_offset' => '<p class="output"><strong>Time offset:</strong> ',

	// output "Core files"
	'h2_permissions' => 'File and directory permissions',
	'h3_core_files' => 'Core',

	'desc_setupfile' => '<p>As soon as the setup has been successfully executed, the setup.php file should be deleted before productive operation.</p>',
	'error_setupfile' => '<p class="error"><strong>&#33;</strong> The setup file is located in the main directory!</p>',
	'success_setupfile' => '<p class="success"><strong>&#10003;</strong> The setup file was not found in the main directory.</p>',

	'desc_defaultsfile' => '<p>The defaults.php file should be write-protected for others in productive operation.</p>',
	'attention_defaultsfile' => '<p class="attention"><strong>&#8505;</strong> The defaults.php file can be changed!</p>',
	'success_defaultsfile' => '<p class="success"><strong>&#10003;</strong> The defaults.php file cannot be changed.</p>',

	'desc_configdir' => '<p>The config directory should be write-protected for others in productive operation.</p>',
	'error_configdir' => '<p class="error"><strong>&#33;</strong> The configuration directory can be written to by others!</p>',
	'success_configdir' => '<p class="success"><strong>&#10003;</strong> The configuration directory cannot be written to by others.</p>',

	'desc_admindir' => '<p>The admin directory should be write-protected for others in productive operation.</p>',
	'attention_admindir' => '<p class="attention"><strong>&#8505;</strong> The admin directory can be written to by others!</p>',
	'success_admindir' => '<p class="success"><strong>&#10003;</strong> The admin directory cannot be written to by others.</p>',

	'desc_includesdir' => '<p>The fp-includes directory should be write-protected for others in productive operation.</p>',
	'attention_includesdir' => '<p class="attention"><strong>&#8505;</strong> The fp-includes directory can be written to by others!</p>',
	'success_includesdir' => '<p class="success"><strong>&#10003;</strong> The fp-includes directory cannot be written to by others.</p>',

	// output "Configuration file for the webserver"
	'h3_configwebserver' => 'Configuration file for the webserver',

	'note_configwebserver' => 'The main directory must be writable in order to be able to create or modify an .htaccess file with the PrettyURLs plugin.<br>' . //
		'<strong>Note:</strong> Only web servers that are NCSA compatible, such as Apache, are familiar with the concept of .htaccess files.',
	'serversoftware' => 'The server software is <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>.',

	'success_maindir' => '<p class="success"><strong>&#10003;</strong> The FlatPress main directory is writable.</p>',
	'attention_maindir' => '<p class="attention"><strong>&#8505;</strong> The FlatPress main directory is not writable!</p>',

	'success_htaccessw' => '<p class="success"><strong>&#10003;</strong> The .htaccess file can be written.</p>',
	'attention_htaccessw' => '<p class="attention"><strong>&#8505;</strong> The .htaccess file cannot be written!</p>',

	'attention_htaccessn' => '<p class="attention"><strong>&#8505;</strong> A .htaccess file already exists in the main directory!</p>',
	'success_htaccessn' => '<p class="success"><strong>&#10003;</strong> No .htaccess file was found in the main directory.</p>',

	// output "Themes and plugins"
	'h3_themesplugins' => 'Themes and plugins',

	'desc_interfacedir' => 'The fp-interface directory should be write-protected for others in productive operation.',
	'attention_interfacedir' => '<p class="attention"><strong>&#8505;</strong> The fp-interface directory can be written to by others!</p>',
	'success_interfacedir' => '<p class="success"><strong>&#10003;</strong> The fp-interface directory cannot be written to by others.</p>',

	'desc_themesdir' => 'The themes directory should be write-protected for others in productive operation.',
	'attention_themesdir' => '<p class="attention"><strong>&#8505;</strong> The themes directory can be written to by others!</p>',
	'success_themesdir' => '<p class="success"><strong>&#10003;</strong> The themes directory cannot be written to by others.</p>',

	'desc_plugindir' => 'The fp-plugins directory should be write-protected for others in productive operation.',
	'attention_plugindir' => '<p class="attention"><strong>&#8505;</strong> The fp-plugins directory can be written to by others!</p>',
	'success_plugindir' => '<p class="success"><strong>&#10003;</strong> The fp-plugins directory cannot be written to by others.</p>',

	// output "Content directory"
	'h3_contentdir' => 'Content',

	'desc_contentdir' => 'The fp-content directory must be writable for FlatPress to work.',
	'success_contentdir' => '<p class="success"><strong>&#10003;</strong> The fp-content directory is writable.</p>',
	'error_contentdir' => '<p class="error"><strong>&#33;</strong> The fp-content directory is not writable!</p>',

	'desc_imagesdir' => 'This images directory must have write permissions so that you can upload images.',
	'success_imagesdir' => '<p class="success"><strong>&#10003;</strong> The images directory is writable.</p>',
	'error_imagesdir' => '<p class="error"><strong>&#33;</strong> The images directory is not writable!</p>',
	'attention_imagesdir' => '<p class="attention"><strong>&#8505;</strong> The images directory does not exist.</p>',

	'desc_thumbsdir' => 'This thumbs directory must have write permissions so that scalable images can be created.',
	'success_thumbsdir' => '<p class="success"><strong>&#10003;</strong> The images/.thumbs directory is writable.</p>',
	'error_thumbsdir' => '<p class="error"><strong>&#33;</strong> The images/.thumbs directory is not writable!</p>',
	'attention_thumbsdir' => '<p class="attention"><strong>&#8505;</strong> The .thumbs directory does not exist, ' . //
		'but is created automatically as soon as a thumbnail has been created with the Thumbnails plugin.</p>',

	'desc_attachsdir' => 'This upload directory must have write permissions so that you can upload something.',
	'success_attachsdir' => '<p class="success"><strong>&#10003;</strong> The upload directory is writable.</p>',
	'error_attachsdir' => '<p class="error"><strong>&#33;</strong> The upload directory is not writable!</p>',
	'attention_attachsdir' => '<p class="attention"><strong>&#8505;</strong> The upload directory does not exist, ' . //
		'but is created automatically with the first upload.</p>',

	'desc_cachedir' => 'This cache directory must have write permission for the cache to function correctly.',
	'success_cachedir' => '<p class="success"><strong>&#10003;</strong> The cache directory is writable.</p>',
	'error1_cachedir' => '<p class="error"><strong>&#33;</strong> The cache directory is not writable!</p>',
	'error2_cachedir' => '<p class="error"><strong>&#33;</strong> The directory cache does not exist!</p>',

	// output "PHP"
	'h2_php' => 'PHP',

	'php_ver' => '<strong>Version: </strong>',

	'php_timezone' => '<strong>Timezone: </strong>',
	'php_timezone_neg' => 'Not available. UTC is used.',

	'h3_extensions' => 'Extensions',

	'desc_php_intl' => 'The PHP-Intl extension must be activated.',
	'error_php_intl' => '<p class="error"><strong>&#33;</strong> The intl extension is not activated!</p>',
	'success_php_intl' => '<p class="success"><strong>&#10003;</strong> The intl extension is activated.</p>',

	'desc_php_gdlib' => 'The GDlib extension must be activated to create image thumbnails.',
	'error_php_gdlib' => '<p class="error"><strong>&#33;</strong> The GD extension is not activated!</p>',
	'success_php_gdlib' => '<p class="success"><strong>&#10003;</strong> The GD extension is activated.</p>',

	'desc_php_mbstring' => 'For optimal performance in productive operation, the PHP multibyte extension must be enabled for Smarty.',
	'attention_php_mbstring' => '<p class="attention"><strong>&#8505;</strong> The Multibyte extension is not activated!</p>',
	'success_php_mbstring' => '<p class="success"><strong>&#10003;</strong> The Multibyte extension is activated.</p>',

	// output "Other"
	'h2_other' => 'Other',

	'desc_browser' => 'The browser used is of interest if there are display errors.',
	'no_browser' => 'Not recognized',
	'detect_browser' => '<p class="output"><strong>Browser: </strong>',

	'desc_cookie' => 'If visitors to the FlatPress blog are to be informed about cookies, this is the cookie.<br>' . //
		'<strong>Hint:</strong> The name of the cookie changes each time FlatPress is reinstalled.',
	'session_cookie' => '<p class="output"><strong>Session cookie: </strong>',
	'no_session_cookie' => 'Could not be determined.',

	'h3_completed' => 'Output completed!',

	'symbols' => '<p class="output"><strong>Symbols:</strong></p>',
	'symbol_success' => '<p class="success"><strong>&#10003;</strong> No action necessary</p>',
	'symbol_attention' => '<p class="attention"><strong>&#8505;</strong> Does not restrict functionality, but requires attention</p>',
	'symbol_error' => '<p class="error"><strong>&#33;</strong> Action urgently needed</p>',

	'close_btn' => 'Close'
);
?>

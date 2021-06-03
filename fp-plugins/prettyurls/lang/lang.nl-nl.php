<?php
	$lang['plugin']['prettyurls']['errors'] = array (
		-2	=> 'Ik vind geen of kan geen <code>.htaccess</code> bestand maken in jouw root '.
				'directorie. PrettyURLs might not work properly, ie het configuratie menu.'
	);
	
	$lang['admin']['plugin']['submenu']['prettyurls'] = 'PrettyURLs Configuratie';
	$lang['admin']['plugin']['prettyurls'] = array(
		'head'		=> 'PrettyURLs Configuratie',
		'htaccess'	=> '.htaccess',
		'description'=>'Met deze onbewerkte editor kunt u uw '.
						'<code>.htaccess</code>.',
		'cantsave'	=> 'U kunt dit bestand niet bewerken omdat het niet <strong>schrijfbaar</strong> is. U kunt schrijfmachtigingen geven of een kopie maken en plakken in een bestand en vervolgens handmatig uploaden.',
		'mode'		=> 'Modus',
		'auto'		=> 'Automatisch',
			'autodescr'	=> 'probeer de beste keuze voor mij te raden',
		'pathinfo'	=> 'Path Info',
			'pathinfodescr' => 'e.g. /index.php/2011/01/01/hello-world/',
		'httpget'	=> 'HTTP Get',
			'httpgetdescr'=> 'e.g. /?u=/2011/01/01/hello-world/',
		'pretty'	=> 'Pretty',
			'prettydescr'=> 'e.g. /2011/01/01/hello-world/',

		'saveopt' 	=> 'Instellingen opslaan',

		'submit'	=> 'Bewaar .htaccess'
	);
	$lang['admin']['plugin']['prettyurls']['msgs'] = array(
		1		=> '.htaccess opgeslagen',
		-1		=> '.htaccess kon niet worden opgeslagen (heb je schrijfrechten op <code>'. BLOG_ROOT .'</code>)?',

		2		=> 'Opties opgeslagen',
		-2		=> 'Er is een fout opgetreden bij het opslaan van instellingen',
	);
	
?>

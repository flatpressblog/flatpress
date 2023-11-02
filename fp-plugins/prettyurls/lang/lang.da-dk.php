<?php
	$lang['plugin']['prettyurls']['errors'] = array (
		-2	=> 'Der kan ikke oprettes en <code>.htaccess</code> eller en <code>.htaccess</code> i blogroden. '.
				'PrettyURLs-plugin\'et fungerer så muligvis ikke korrekt.'
	);
	
	$lang['admin']['plugin']['submenu']['prettyurls'] = 'PrettyURLs';
	$lang['admin']['plugin']['prettyurls'] = array(
		'head'		=> 'PrettyURLs Konfiguration',
		'htaccess'	=> '.htaccess',
		'description'=>'Denne editor giver dig mulighed for direkte at redigere de '.
						'<code>.htaccess</code> der er nødvendige for PrettyUrls plugin.',
		'cantsave'	=> 'Denne fil kan ikke redigeres, fordi den er skrivebeskyttet. Ændr adgangsrettighederne, eller kopier disse linjer, indsæt dem i en lokal fil, og upload den derefter.',
		'mode'		=> 'Tilstand',
		'auto'		=> 'Automatisk',
			'autodescr'	=> 'Find automatisk den bedste løsning',
		'pathinfo'	=> 'Sti-info',
			'pathinfodescr' => 'e.g. /index.php/2011/01/01/hello-world/',
		'httpget'	=> 'HTTP Get',
			'httpgetdescr'=> 'e.g. /?u=/2011/01/01/hello-world/',
		'pretty'	=> 'Pretty',
			'prettydescr'=> 'e.g. /2011/01/01/hello-world/',

		'saveopt' 	=> 'Gem indstillinger',

		'submit'	=> '.htaccess gem'
	);
	$lang['admin']['plugin']['prettyurls']['msgs'] = array(
		1		=> '.htaccess-filen blev gemt med succes',
		-1		=> '.htaccess-filen kunne ikke gemmes (ingen skriverettigheder i <code>'. BLOG_ROOT .'</code>)?',

		2		=> 'Indstillingerne blev gemt med succes',
		-2		=> 'Der opstod en fejl under lagring',
	);
	
?>

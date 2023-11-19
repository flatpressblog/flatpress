<?php
	$lang['plugin']['prettyurls']['errors'] = array (
		-2	=> 'Ne morem najti ali ustvariti datoteke <code>.htaccess</code> v vašem korenskem '.
				'direktoriju. PrettyURLs morda ne bo pravilno deloval, glejte konfiguracijsko ploščo.'
	);
	
	$lang['admin']['plugin']['submenu']['prettyurls'] = 'Nastavitve PrettyURLs';
	$lang['admin']['plugin']['prettyurls'] = array(
		'head'		=> 'Nastavitve PrettyURLs',
		'htaccess'	=> '.htaccess',
		'description'=>'Ta urejevalnik vam omogoča urejanje vaše '.
						'<code>.htaccess</code> datoteke.',
		'cantsave'	=> 'Datoteke ne morete urejati, ker ni <strong>zapisljiva</strong>. Lahko podelite dovoljenja za pisanje ali kopirate in prilepite v datoteko ter jo nato naložite ročno.',
		'mode'		=> 'Način',
		'auto'		=> 'Avtomatski',
			'autodescr'	=> 'poskusi uganiti najboljšo izbiro zame',
		'pathinfo'	=> 'Path Info',
			'pathinfodescr' => 'npr. /index.php/2011/01/01/pozdravljen-svet/',
		'httpget'	=> 'HTTP Get',
			'httpgetdescr'=> 'npr. /?u=/2011/01/01/pozdravljen-svet/',
		'pretty'	=> 'Pretty',
			'prettydescr'=> 'npr. /2011/01/01/pozdravljen-svet/',

		'saveopt' 	=> 'Shrani nastavitve',

		'submit'	=> 'Shrani .htaccess'
	);
	$lang['admin']['plugin']['prettyurls']['msgs'] = array(
		1		=> '.htaccess uspešno shranjen',
		-1		=> '.htaccess ni bilo mogoče shraniti (ali imate dovoljenja za pisanje v <code>'. BLOG_ROOT .'</code>)?',

		2		=> 'Možnosti uspešno shranjene',
		-2		=> 'Prišlo je do napake pri poskusu shranjevanja nastavitev',
	);
	
?>

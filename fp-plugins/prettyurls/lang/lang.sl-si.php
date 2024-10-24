<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'Ne morem najti ali ustvariti datoteke <code>.htaccess</code> v vašem korenskem ' . //
		'direktoriju. PrettyURLs morda ne bo pravilno deloval, glejte konfiguracijsko ploščo.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'Nastavitve PrettyURLs';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'Nastavitve PrettyURLs',
	'description1' => 'Tu lahko standardne URL-je iz FlatPressa spremenite v čudovite, SEO prijazne URL-je.',
	'fpprotect_is_on' => 'Vtičnik PrettyURLs zahteva datoteko .htaccess. ' . //
		'Če želite ustvariti ali spremeniti to datoteko, aktivirajte to možnost v vtičniku <a href="admin.php?p=config&action=fpprotect" title="pojdite na FlatPress Protect Plugin">FlatPress Protect</a>. ',
	'fpprotect_is_off' => 'Vtičnik FlatPress Protect ščiti datoteko .htaccess pred nenamernimi spremembami. ' . //
		'Vtičnik lahko aktivirate <a href="admin.php?p=plugin&action=default" title="Pojdite v administracijo vtičnika">tukaj</a>!',
	'nginx' => 'PrettyURLs med NGINX',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => 'Ta urejevalnik vam omogoča neposredno urejanje <code>.htaccess</code>, potrebnega za vtičnik PrettyURLs.<br>' . //
		'<strong>Opomba:</strong> Koncept datotek .htaccess poznajo samo spletni strežniki, ki so združljivi z NCSA, kot je Apache. ' . //
		'Programska oprema vašega strežnika je: <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>',
	'cantsave' => 'Datoteke ne morete urejati, ker ni <strong>zapisljiva</strong>. Lahko podelite dovoljenja za pisanje ali kopirate in prilepite v datoteko ter jo nato naložite ročno.',
	'mode' => 'Način',
	'auto' => 'Avtomatski',
	'autodescr' => 'poskusi uganiti najboljšo izbiro zame',
	'pathinfo' => 'Path Info',
	'pathinfodescr' => 'npr. /index.php/2024/01/01/pozdravljen-svet/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => 'npr. /?u=/2024/01/01/pozdravljen-svet/',
	'pretty' => 'Pretty',
	'prettydescr' => 'npr. /2024/01/01/pozdravljen-svet/',

	'saveopt' => 'Shrani nastavitve',

	'location' => '<strong>Lokacija shranjevanja:</strong> ' . ABS_PATH . '',
	'submit' => 'Shrani .htaccess'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess uspešno shranjen',
	-1 => '.htaccess ni bilo mogoče shraniti (ali imate dovoljenja za pisanje v <code>' . BLOG_ROOT . '</code>)?',

	2 => 'Možnosti uspešno shranjene',
	-2 => 'Prišlo je do napake pri poskusu shranjevanja nastavitev'
);
?>

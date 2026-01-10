<?php
$lang ['admin'] ['maintain'] ['submenu'] ['support'] = 'Zobrazit údaje o podpoře';

$lang ['admin'] ['maintain'] ['support'] = array(
	'title' => 'Podpůrné údaje',
	'intro' => 'Pro hlášení chyb a pomoc navštivte <a href="https://forum.flatpress.org" target="_blank">FlatPress fórum</a>, ' . //
		'nahlaste chybu na <a href="https://github.com/flatpressblog/flatpress/issues" target="_blank">GitHub</a> nebo ' . //
		'<a href="mailto:hello@flatpress.org">pošlete e-mail</a>.<br>Vložte tyto problémy (copy &#38; paste) v angličtině ' . //
		's následujícími informacemi: Popis chyby, kroky k reprodukci.',

	// output "Setup"
	'h2_general' => 'Obecné',
	'h3_setup' => 'Nastavení',
	
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
	'h3_international' => 'Lokalizace',

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
	'h2_permissions' => 'Oprávnění k souborům a adresářům',
	'h3_core_files' => 'Jádro',

	'desc_setupfile' => '<p>Jakmile je instalace úspěšně provedena, měl by být soubor setup.php přejmenován na setup.ph_ nebo smazán.</p>',
	'error_setupfile' => '<p class="error"><strong>&#33;</strong> Instalační soubor se nachází v hlavním adresáři!</p>',
	'success_setupfile' => '<p class="success"><strong>&#10003;</strong> Instalační soubor nebyl nalezen v hlavním adresáři.</p>',

	'desc_defaultsfile' => '<p>Soubor defaults.php by měl být chráněn proti zápisu pro ostatní.</p>',
	'attention_defaultsfile' => '<p class="attention"><strong>&#8505;</strong> Soubor defaults.php lze změnit!</p>',
	'success_defaultsfile' => '<p class="success"><strong>&#10003;</strong> Soubor defaults.php nelze změnit.</p>',

	'desc_configdir' => '<p>Adresář config by měl být chráněn proti zápisu pro ostatní.</p>',
	'error_configdir' => '<p class="error"><strong>&#33;</strong> Do adresáře configuration mohou ostatní uživatelé zapisovat!</p>',
	'success_configdir' => '<p class="success"><strong>&#10003;</strong> Do adresáře configuration nemohou ostatní uživatelé zapisovat.</p>',

	'desc_admindir' => '<p>Adresář admin by měl být chráněn proti zápisu pro ostatní.</p>',
	'attention_admindir' => '<p class="attention"><strong>&#8505;</strong> Do adresáře admin mohou ostatní uživatelé zapisovat!</p>',
	'success_admindir' => '<p class="success"><strong>&#10003;</strong> Do adresáře admin nemohou ostatní uživatelé zapisovat.</p>',

	'desc_includesdir' => '<p>Adresář fp-includes by měl být chráněn proti zápisu pro ostatní.</p>',
	'attention_includesdir' => '<p class="attention"><strong>&#8505;</strong> Do adresáře fp-includes mohou ostatní uživatelé zapisovat!</p>',
	'success_includesdir' => '<p class="success"><strong>&#10003;</strong> Do adresáře fp-includes nemohou ostatní uživatelé zapisovat.</p>',

	// output "Configuration file for the webserver"
	'h3_configwebserver' => 'Konfigurační soubor pro webový server',

	'note_configwebserver' => 'Hlavní adresář musí být zapisovatelný, aby bylo možné vytvořit nebo upravit soubor .htaccess pomocí pluginu PrettyURLs.<br>' . //
		'<strong>Poznámka:</strong> Pouze webové servery kompatibilní s NCSA, jako například Apache, načítají volby ze souboru .htaccess.',
	'serversoftware' => 'Serverový software je <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>.',

	'success_maindir' => '<p class="success"><strong>&#10003;</strong> Hlavní adresář FlatPress je zapisovatelný.</p>',
	'attention_maindir' => '<p class="attention"><strong>&#8505;</strong> Hlavní adresář FlatPress není zapisovatelný!</p>',

	'success_htaccessw' => '<p class="success"><strong>&#10003;</strong> Do souboru .htaccess lze zapisovat.</p>',
	'attention_htaccessw' => '<p class="attention"><strong>&#8505;</strong> Do souboru .htaccess nelze zapisovat!</p>',

	'attention_htaccessn' => '<p class="attention"><strong>&#8505;</strong> Soubor .htaccess již v hlavním adresáři existuje.</p>',
	'success_htaccessn' => '<p class="success"><strong>&#10003;</strong> Soubor .htaccess v hlavním adresáři neexistuje.</p>',

	// output "Themes and plugins"
	'h3_themesplugins' => 'Šablony a pluginy',

	'desc_interfacedir' => 'Adresář fp-interface by měl být chráněn proti zápisu.',
	'attention_interfacedir' => '<p class="attention"><strong>&#8505;</strong> Do adresáře fp-interface mohou ostatní zapisovat!</p>',
	'success_interfacedir' => '<p class="success"><strong>&#10003;</strong> Do adresáře fp-interface nemohou ostatní zapisovat.</p>',

	'desc_themesdir' => 'Adresář themes by měl být chráněn proti zápisu.',
	'attention_themesdir' => '<p class="attention"><strong>&#8505;</strong> Do adresáře themes mohou ostatní zapisovat!</p>',
	'success_themesdir' => '<p class="success"><strong>&#10003;</strong> Do adresáře themes nemohou ostatní zapisovat.</p>',

	'desc_plugindir' => 'Adresář fp-plugins by měl být chráněn proti zápisu.',
	'attention_plugindir' => '<p class="attention"><strong>&#8505;</strong> Do adresáře fp-plugins mohou ostatní zapisovat!</p>',
	'success_plugindir' => '<p class="success"><strong>&#10003;</strong> Do adresáře fp-plugins nemohou ostatní zapisovat.</p>',

	// output "Content directory"
	'h3_contentdir' => 'Obsah',

	'desc_contentdir' => 'Adresář fp-content musí být zapisovatelný, aby FlatPress fungoval.',
	'success_contentdir' => '<p class="success"><strong>&#10003;</strong> Adresář fp-content je zapisovatelný.</p>',
	'error_contentdir' => '<p class="error"><strong>&#33;</strong> Adresář fp-content není zapisovatelný!</p>',

	'desc_imagesdir' => 'Adresář pro nahrávání obrázků musí mít oprávnění k zápisu.',
	'success_imagesdir' => '<p class="success"><strong>&#10003;</strong> Adresář "images" je zapisovatelný.</p>',
	'error_imagesdir' => '<p class="error"><strong>&#33;</strong> Adresář "images" není zapisovatelný!</p>',
	'attention_imagesdir' => '<p class="attention"><strong>&#8505;</strong> Adresář "images" neexistuje!</p>',

	'desc_thumbsdir' => 'Adresář miniatur musí mít oprávnění k zápisu, aby bylo možné vytvářet náhledy obrázků.',
	'success_thumbsdir' => '<p class="success"><strong>&#10003;</strong> Adresář ".thumbs" je zapisovatelný.</p>',
	'error_thumbsdir' => '<p class="error"><strong>&#33;</strong> Adresář ".thumbs" není zapisovatelný!</p>',
	'attention_thumbsdir' => '<p class="attention"><strong>&#8505;</strong> Adresář ".thumbs" neexistuje, ' . //
		'ale vytvoří se automaticky, jakmile je pomocí pluginu Miniatury vytvořena miniatura.</p>',

	'desc_attachsdir' => 'Adresář pro nahrávání musí mít oprávnění k zápisu, abyste mohli nahrávat soubory.',
	'success_attachsdir' => '<p class="success"><strong>&#10003;</strong> Adresář "attachs" je zapisovatelný.</p>',
	'error_attachsdir' => '<p class="error"><strong>&#33;</strong> Adresář "attachs" není zapisovatelný!</p>',
	'attention_attachsdir' => '<p class="attention"><strong>&#8505;</strong> Adresář "attachs" neexistuje, ' . //
		'ale vytvoří se automaticky při prvním nahrávání souboru.</p>',

	'desc_cachedir' => 'Adresář mezipaměti musí mít oprávnění k zápisu, aby mezipaměť fungovala správně.',
	'success_cachedir' => '<p class="success"><strong>&#10003;</strong> Adresář "cache" je zapisovatelný.</p>',
	'error1_cachedir' => '<p class="error"><strong>&#33;</strong> Adresář "cache" není zapisovatelný!</p>',
	'error2_cachedir' => '<p class="error"><strong>&#33;</strong> Adresář "cache" neexistuje!</p>',

	// output "PHP"
	'h2_php' => 'PHP',

	'php_ver' => '<strong>Verze: </strong>',

	'php_timezone' => '<strong>Časové pásmo: </strong>',
	'php_timezone_neg' => 'Není k dispozici. Používá se UTC.',

	'h3_extensions' => 'Rozšíření',

	'desc_php_intl' => 'Rozšíření PHP-Intl musí být aktivováno.',
	'error_php_intl' => '<p class="error"><strong>&#33;</strong> Rozšíření Intl není aktivováno!</p>',
	'success_php_intl' => '<p class="success"><strong>&#10003;</strong> Rozšíření Intl je aktivováno.</p>',

	'desc_php_gdlib' => 'Pro vytváření miniatur obrázků musí být aktivováno rozšíření GDlib.',
	'error_php_gdlib' => '<p class="error"><strong>&#33;</strong> Rozšíření GDlib není aktivováno!</p>',
	'success_php_gdlib' => '<p class="success"><strong>&#10003;</strong> Rozšíření GDlib je aktivováno.</p>',

	'desc_php_mbstring' => 'Pro optimální výkon musí být pro Smarty povoleno rozšíření PHP Multibyte.',
	'attention_php_mbstring' => '<p class="attention"><strong>&#8505;</strong> Rozšíření Multibyte není aktivováno!</p>',
	'success_php_mbstring' => '<p class="success"><strong>&#10003;</strong> Rozšíření Multibyte je aktivováno.</p>',

	// output "Other"
	'h2_other' => 'Ostatní',

	'desc_browser' => 'Na typu prohlížeče může záležet, pokud se vyskytnou chyby v zobrazení.',
	'no_browser' => 'Prohlížeč nedetekován!',
	'detect_browser' => '<p class="output"><strong>Prohlížeč: </strong>',

	'desc_cookie' => 'Pokud jsou návštěvníci blogu FlatPress informováni o souboru cookie, jedná se o tento soubor:<br>' . //
		'<strong>Nápověda:</strong> Název souboru cookie se změní při každé přeinstalaci FlatPressu.',
	'session_cookie' => '<p class="output"><strong>Cookie: </strong>',
	'no_session_cookie' => 'Nepodařilo se určit.',

	'h3_completed' => 'Výstup dokončen!',

	'symbols' => '<p class="output"><strong>Symboly:</strong></p>',
	'symbol_success' => '<p class="success"><strong>&#10003;</strong> Není nutná žádná akce</p>',
	'symbol_attention' => '<p class="attention"><strong>&#8505;</strong> Neomezuje funkčnost, ale vyžaduje pozornost</p>',
	'symbol_error' => '<p class="error"><strong>&#33;</strong> Je naléhavě nutná akce</p>',

	'close_btn' => 'Zavřít'
);
?>

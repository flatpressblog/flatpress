<?php

	$lang['admin']['config']['default'] = 
	array(
		'head'		=> 'Nastavení',
		'descr'		=> 'Uprav a nastav si svoji instalaci Flatpressu.',
		'submit'		=> 'Ulož změny',
		
		'sysfset'		=> 'Informace o systému',
		'syswarning'	=> '<big>Varování!</big> Tyto informace jsou důležité a musí být správné,
	jinak nemusí Flatpress fungovat správně.',
		'blog_root'		=> '<strong>Absolutní cesta k flatpressu</strong>. Poznámka: 
	většinou to nebudeš potřebovaa měnit, každopádně buď opatrný, protože neumíme ověřit správnost',
		'www'		=>'<strong>Blog root</strong>. URL tvého blogu, kompletní cesta i s podadresáři. <br />
	např.: http://www.mydomain.com/flatpress/ (koncové lomítko je potřebné)',
		
		// ------
		
		'gensetts'		=> 'Hlavní nastavení',
		'blogtitle'		=> 'Nadpis blogu',
		'blogsubtitle'		=> 'Podnadpis blogu',
		'blogfooter'		=> 'Patička blogu',
		'blogauthor'		=> 'Autor blogu',
		'startpage'			=> 'Domácí stránka pro tento blog je:',
		'stdstartpage'		=> 'můj blog (přednastavené)',
		'blogurl'			=> 'Blog URL',
		'blogemail'			=> 'Blog email',
		'notifications'		=> 'Oznámení',
		'mailnotify'		=> 'Zasílat upozornění o komentářích na email',
		'blogmaxentries'	=> 'Počet příspěvků na stránku',
		'langchoice'		=> 'Jazyk',

		'intsetts'		=> 'Mezinárodní nastavení',
		'utctime'		=> '<acronym title="Universal Coordinated Time">UTC</acronym> čas je',
		'timeoffset'		=> 'Čas bude oddělený: ',
		'hours'			=> 'hodiny',
		'timeformat'		=> 'Přednastavený formát času',
		'dateformat'		=> 'Přednastavený formát datumu',
		'dateformatshort'	=> 'Formát datumu (krátký)',
		'output'		=> 'Výstup',
		'charset'		=> 'Znaková sada',
		'charsettip'	=> 'Doporučená znaková sada na blogu je UTF-8'
		);
		
	$lang['admin']['config']['default']['msgs'] = 
	array(
		1		=> 'Konfigurace úspěšně uložena..',
		-1		=> 'Při ukládání sa vyskytla chyba..',
		
		);
			
	$lang['admin']['config']['default']['error'] = 
	array(
		'www' 		=>	'Root blogu musí být platná adresa',
		'title'		=>	'Musíš zadat nadpis',
		'email'		=>	'Email musí mít správný formát',
		'maxentries'=>	'Nezadal jsi správny počet položek',
		'timeoffset'=>	'Nezadal jsi správný čas! '.
						'Můžeš používat celé čísla (např. 2h30" => 2.5)',
		'timeformat'=>	'Musíš zadat řetězec s formátem času ',
		'dateformat'=>	'Musíš zadat řetězec s formátem datumu',
		'dateformatshort'=>	'Musíš zadat řetězec s formátem datumu (krátká verze)',
		'charset'	=>	'Musíš zadat ID znakové sady',
		'lang'		=>	'Vybraný jazyk není dostupný'
		);		
			
		
?>

<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array(
	-2 => 'Nelze najít nebo vytvořit soubor <code>.htaccess</code> ve Vašem kořenovém ' . 'adresáři. PrettyURLs nemusí fungovat správně, viz Nastavení PretyURLs.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLs Config';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'Nastavení PrettyURLs',
	'htaccess' => '.htaccess',
	'description' => 'Tímto editorem můžete editovat Váš soubor <code>.htaccess</code>.',
	'cantsave' => 'Nemůžete editovat tento soubor, protože není <strong>zapisovatelný</strong>. Můžete nastavit povolení k zapisování nebo kopírovat a vložit do souboru a poté nahrát.',
	'mode' => 'Režim',
	'auto' => 'Automatický',
	'autodescr' => 'se snaží vybrat nejlepší volbu',
	'pathinfo' => 'Path Info',
	'pathinfodescr' => 'např. /index.php/2011/01/01/hello-world/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => 'např. /?u=/2011/01/01/hello-world/',
	'pretty' => 'Pretty',
	'prettydescr' => 'např. /2011/01/01/hello-world/',

	'saveopt' => 'Uložit nastavení',

	'submit' => 'Uložit .htaccess'
);
$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess úspěšně uložen',
	-1 => '.htaccess nemohl být uložen (nemáte oprávnění pro zapisování <code>' . BLOG_ROOT . '</code>)?',

	2 => 'Možnosti úspěšně uloženy',
	-2 => 'Došlo k chybě při pokusu o uložení nastavení'
);

?>

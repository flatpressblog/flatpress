<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'Ezin dut <code>.htaccess</code> fitxategirik aurkitu edo sortu zure erro ' . //
		' direktorioan. Baliteke PrettyURLs-ek behar bezala ez funtzionatzea, ikusi konfigurazio panela.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLs konfigurazioa';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'PrettyURLs konfigurazioa',
	'description1' => 'Hemen FlatPressen URL estandarrak SEOrako egokiak diren URL ederretan bihur ditzakezu.',
	'fpprotect_is_on' => 'PrettyURLs pluginak .htaccess fitxategi bat behar du. ' . //
	  'Fitxategi hau sortu edo aldatzeko, aldatu <a href="admin.php?p=config&action=fpprotect" title="go to FlatPress Protect Plugin">FlatPress Protect plugin</a>aren ezarpenak. ',
	'fpprotect_is_off' => 'FlatPress Protect pluginak .htaccess fitxategia nahi gabeko aldaketetatik babesten du. ' . //
		'Plugina <a href="admin.php?p=plugin&action=default" title="Joan pluginaren ezarpenetara">hemen</a> aktibatu dezakezu!',
	'nginx' => 'PrettyURLs NGINXekin',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => 'Editore honek PrettyURLs pluginerako beharrezkoa den <code>.htaccess</code> fitxategia zuzenean editatzeko aukera ematen dizu.<br>' . //
		'<strong>Oharra:</strong> NCSArekin bateragarriak diren web zerbitzariek bakarrik ezagutzen dute .htaccess fitxategien kontzeptua, hala nola Apache-k. ' . //
		'Zure zerbitzari softwarea honako hau da: <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>',
	'cantsave' => 'Ezin duzu fitxategi hau editatu, ez baita <strong>idatzigarria</strong>. Idazteko baimena eman diezaiokezu FlatPressi edo kopiatu' . //
    ' eta fitxategi berri batera itsatsi eta gero eskuz igo.',
	'mode' => 'Modua',
	'auto' => 'Automatikoa',
	'autodescr' => 'Saiatu niretzat aukerarik onena dena asmatzen',
	'pathinfo' => 'Bidearen informazioa',
	'pathinfodescr' => 'adib. /index.php/2024/01/01/kaixo-mundua/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => 'adib. /?u=/2024/01/01/kaixo-mundua/',
	'pretty' => 'Pretty',
	'prettydescr' => 'adib. /2024/01/01/kaixo-mundua/',

	'saveopt' => 'Gorde ezarpenak',

	'location' => '<strong>Biltegiratzearen kokapena:</strong> ' . ABS_PATH . '',
	'submit' => 'Gorde .htaccess fitxategia'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess fitzategia ondo gorde da.',
	-1 => 'Errore bat gertatu da .htaccess fitxategia gordetzen saiatzean. Idazketa baimenak al dituzu <code>' . BLOG_ROOT . '</code>) fitxategian?',

	2 => 'Ezarpenak ondo gorde dira.',
	-2 => 'Errore bat gertatu da ezarpenak gordetzen saiatzean.',
);
?>

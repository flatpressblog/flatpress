<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'Es ist keine <code>.htaccess</code> vorhanden oder es kann keine <code>.htaccess</code> im Blog Root angelegt werden. ' . //
		'Das PrettyURLs Plugin wird dann unter Umständen nicht korrekt arbeiten.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLs';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'PrettyURLs Konfiguration',
	'description1' => 'Hier kannst du die Standard-URL\'s von FlatPress in schöne, SEO-freundliche URL\'s verwandeln.',
	'fpprotect_is_on' => 'Das Plugin PrettyURLs benötigt eine .htaccess-Datei. ' . //
		'Um diese Datei zu erstellen oder zu verändern, aktiviere dazu die Option im <a href="admin.php?p=config&action=fpprotect" title="gehe zum FlatPress Protect Plugin">FlatPress Protect Plugin</a>. ',
	'fpprotect_is_off' => 'Das FlatPress Protect Plugin schützt die .htaccess Datei vor unbeabsichtigten Änderungen. ' . //
		'<a href="admin.php?p=plugin&action=default" title="gehe zur Plugin Verwaltung">Hier</a> kannst du das Plugin aktivieren!',
	'nginx' => 'PrettyURLs mit NGINX',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => 'Dieser Editor ermöglicht die für das PrettyURLs Plugin benötigte <code>.htaccess</code> direkt zu bearbeiten.<br>' . //
		'<strong>Hinweis:</strong> Nur Webserver, die NCSA kompatibel sind, wie beispielsweise Apache, kennen das Konzept der .htaccess Dateien. ' . //
		'Deine Serversoftware ist: <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>',
	'cantsave' => 'Diese Datei kann nicht bearbeitet werden, weil sie schreibgeschützt ist. Ändere die Zugriffsrechte oder kopiere diese Zeilen, füge sie in eine lokale Datei ein und lade diese dann hoch.',
	'mode' => 'Modus',
	'auto' => 'Automatisch',
	'autodescr' => 'Ermittle die beste Möglichkeit automatisch',
	'pathinfo' => 'Path Info',
	'pathinfodescr' => 'Beispiel: /index.php/2024/01/01/hello-world/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => 'Beispiel: /?u=/2024/01/01/hello-world/',
	'pretty' => 'Pretty',
	'prettydescr' => 'Beispiel: /2024/01/01/hello-world/',

	'saveopt' => 'Einstellungen speichern',

	'location' => '<strong>Speicherort:</strong> ' . ABS_PATH . '',
	'submit' => '.htaccess speichern'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => 'Die Datei .htaccess wurde erfolgreich gespeichert',
	-1 => 'Die Datei .htaccess konnte nicht gespeichert werden (Keine Schreibrechte im <code>' . BLOG_ROOT . '</code>)?',

	2 => 'Einstellungen wurden erfolgreich gespeichert',
	-2 => 'Ein Fehler ist beim Speichern aufgetreten'
);
?>

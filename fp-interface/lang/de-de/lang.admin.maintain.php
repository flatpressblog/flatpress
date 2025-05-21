<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Verwaltung';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Flatpress-Wartung',
	'descr' => 'Dieses Menü bietet verschiedene Möglichkeiten für das Flatpress-Blog, um einige Dinge zu korrigieren oder einfach nur nach Updates zu suchen.',
	'opt0' => '&laquo; Zurück zur Wartung',
	'opt1' => 'Den Flatpress-Index neu erstellen',
	'opt2' => 'Den Theme- und Template-Cache leeren',
	'opt3' => 'Wiederherstellen der Dateizugriffsrechte',
	'opt4' => 'Zeige PHP-Informationen des Webservers',
	'opt5' => 'Prüfe auf neue Versionen',

	'chmod_info' => "Die Dateizugriffsrechte <strong>konnten nicht</strong>
					auf die Default Werte von 0777 zurückgesetzt werden. 
          Normalerweise kann man diesen Hinweis ignorieren."
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Aktion ausgeführt.'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Updates',
	'list' => '<ul>
		<li>Du hast die FlatPress-Version <big>%s</big></li>
		<li>Die letzte stabile Flatpress-Version ist <big><a href="%s">%s</a></big></li>
		<li>Letzte Entwicklungsversion von Flatpress ist <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Hinweis:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'Es sind Updates verfügbar!',
	2 => 'Du benutzt bereits die aktuelle Version',
	-1 => 'Fehler: Es konnten keine Update-Informationen abgerufen werden'
);

?>
<?php

	$lang['admin']['plugin']['submenu'] = array (
		'default'		=> 'Plugin Verwaltung'
	);
	
	/* main plugin panel */

	$lang['admin']['plugin']['default'] = array(
	
		'head'		=> 'Plugin Verwaltung',
		'enable'	=> 'Aktivieren',
		'disable'	=> 'Deaktivieren',
		'descr'		=> 'Diese Verwaltung ermöglicht es <a class="hint" href="http://wiki.flatpress.org/doc:plugins" title="Was ist ein Plugin?">Plugins</a> zu aktivieren oder deaktivieren, die die Funktionalität von Flatpress sehr flexibel gestalten.</p>'.
						'<p>Um ein neues Plugin in Flatpress zu integrieren, muss dieses Plugin in das Verzeichnis <code>fp-plugins/</code> '.
						'geladen werden. Ist ein Name und eine Beschreibung im neuen Plugin an entsprechender Stelle vorhanden, dann werden diese Texte hier mit angezeigt.',
		'name'		=> 'Name',
		'description'=>'Beschreibung',
		'author'	=> 'Autor',
		'version'	=> 'Version',
		'action'	=> 'Aktion',
	);
	
	$lang['admin']['plugin']['default']['msgs'] = array(
		1	=> 'Konfiguration gespeichert',
		-1	=> 'Ein Fehler ist beim Speichern aufgetreten. Eventuell stimmt die Syntax des Plugins nicht.',
	);
	
	/* system errors */
	
	$lang['admin']['plugin']['errors'] = array(
		'head'		=> 'Folgendes Fehler sind beim Laden des Plugins aufgetreten:',
		'notfound'	=> 'Das Plugin wurde nicht gefunden. Übersprungen.',
		'generic'	=> 'Fehler Nummer %d',
	);
	
?>

<?php

	$lang['admin']['widgets']['submenu']['default'] = 'Verwaltung Widgets';
	$lang['admin']['widgets']['submenu']['raw'] 	= 'Verwaltung Widgets (raw)';

	/* default action */
	
	$lang['admin']['widgets']['default'] = array(
		'head'		=> 'Widget-Verwaltung',
		
		'descr'		=> 'Flatpress hat verschiedene Widgets mit an Bord, wie zum Beispiel die Login- oder die Suchfunktion. Ein <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a Widget?">'.
						'Widget</a> ist ein dynamisches Steuerelement eines <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a plugin?">'.
						'Plugins</a>, das im Widget Bereich '.
						'(<em>Widgetsets</em>) des Blogs frei positioniert werden kann'.
						'</p>'.
						'<p><strong>Schiebe</strong> mit der Maus das Widget Element aus der Auswahl <strong>Verfügbare Widgets</strong> '.
						'in die gewünschte Position der Widget Leiste.</p>',
						
		'availwdgs'	=> 'Verfügbare Widgets',
		'trashcan'	=> 'Zum Löschen hierher schieben',
		
		'themewdgs' => 'Widgetsets für dieses Theme',
		'themewdgsdescr' => 'In diesem Theme stehen folgende Widgets zur Verfügung',
		'oldwdgs'	=> 'Andere Widget Sets',
		'oldwdgsdescr' =>'Das folgende Widget Set scheint zu keinem der oben gelisteten '.
						'Widget Sets zu gehören. Eventuell ist es Teil eines anderen Themes.',
		
		'submit'	=> 'Änderungen speichern',

	);
	
	$lang['admin']['widgets']['default']['stdsets'] = array(
		'top'		=> 'Kopfleiste',
		'bottom'	=> 'Fussleiste',
		'left'		=> 'Linke Leiste',
		'right'		=> 'Rechte Leiste',
	);
	
	$lang['admin']['widgets']['default']['msgs'] = array(
		1	=> 'Konfiguration gespeichert',
		-1	=> 'Ein Fehler ist beim Speichern der Konfiguration aufgetreten, bitte nochmals versuchen',
	);


	
	/* "raw" panel */	
	
	$lang['admin']['widgets']['raw'] = array(
		'head'		=> 'Verwaltung Widgets (<em>raw editor</em>)',
		'descr'		=> 'Ein <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a Widget?">'.
						'Widget</a> ist ein dynamisches Steuerelement eines <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a plugin?">'.
						'Plugins</a>, das im Widget Bereich '.
						'(<em>Widgetsets</em>) des Blogs frei positioniert werden kann. '.
						'Nur Widgets/Plugins eintragen die auch in der <a href="admin.php?p=plugin">Plugin Verwaltung</a> aktiviert worden sind. '.						
						'</p>'.

  /* added by laborix, only available in the german language pack for svn */

'<p>Flatpress (raw) Namenskonventionen:</p>
<pre>
Beschreibung               Widget/Plugin
-----------------------------------------------  
Administrationsbereich   = adminarea
Kalender                 = calendar
Suche                    = searchbox
Archiv                   = archives
Kategorien               = categories
Letzte Beiträge          = lastentries
Neueste Kommentare       = lastcomments

Blockparser Beispiel     = blockparser:menu
</pre>'.

  /* end of "added by laborix" */

						'<p>Mit diesem Editor kann man manuell den Widget Bereich bearbeiten. '.
						'Das ist für alle diejenigen gedacht, die kein Javascript aktiv haben oder einfach alles per Hand editieren.',
						
		'fset1'		=> 'Editor',
		'fset2'		=> 'Änderungen durchführen',
		'submit'	=> 'Änderungen speichern',

	);
	
	
	$lang['admin']['widgets']['raw']['msgs'] = array(
		1	=> 'Konfiguration gespeichert',
		-1	=> 'Ein Fehler ist beim Speichern der Konfiguration aufgetreten. Bitte nochmalig auf Korrektheit überprüfen.',
	);

		

	/* system errors */
		
	$lang['admin']['widgets']['errors'] = array(
		'generic'	=> 'Das Widget <strong>%s</strong> ist nicht in der Liste eingetragen und wird übersprungen. '.
 				'Bitte in der <a href="admin.php?p=plugin">Plugin Verwaltung</a> nachsehen, ob dieses Plugin aktiviert ist. '

	);
	
?>

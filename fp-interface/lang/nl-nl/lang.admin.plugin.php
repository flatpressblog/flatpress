<?php

	$lang['admin']['plugin']['submenu'] = array (
		'default'		=> 'Beheer Plugins'
	);
	
	/* main plugin panel */

	$lang['admin']['plugin']['default'] = array(
	
		'head'		=> 'Beheer Plugins',
		'enable'	=> 'Inschakelen',
		'disable'	=> 'Uitschakelen',
		'descr'		=> 'Een <a class="hint" '.
						'href="https://wiki.flatpress.org/res:plugins" title="Wat is een plugin?">'.
						'Plugin</a> is een component die de mogelijkheden van FlatPress kan uitbreiden.</p>'.
						'<p>U kunt plug-ins installeren door ze te uploaden in jouw <code>fp-plugins/</code> '.
						'directorie.</p>'.
						'<p>Met dit paneel kunt u plug-ins in- en uitschakelen',
		'name'		=> 'Naam',
		'description'=>'Beschrijving',
		'author'	=> 'Auteur',
		'version'	=> 'Versie',
		'action'	=> 'Actie',
	);
	
	$lang['admin']['plugin']['default']['msgs'] = array(
		1	=> 'Configuratie opgeslagen',
		-1	=> 'Er is een fout opgetreden tijdens het opslaan. Dit kan om verschillende redenen gebeuren: misschien bevat uw bestand syntaxisfouten.',
	);
	
	/* system errors */
	
	$lang['admin']['plugin']['errors'] = array(
		'head'		=> 'De volgende fouten zijn opgetreden tijdens het laden van plug-ins:',
		'notfound'	=> 'Plug-in is niet gevonden. Overgeslagen.',
		'generic'	=> 'Foutnummer %d',
	);
	
?>

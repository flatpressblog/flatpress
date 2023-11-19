<?php

	$lang['admin']['plugin']['submenu'] = array (
		'default'		=> 'Plugin-administration'
	);
	
	/* main plugin panel */

	$lang['admin']['plugin']['default'] = array(
	
		'head'		=> 'Plugin-administration',
		'enable'	=> 'Aktivér',
		'disable'	=> 'Deaktiver',
		'descr'		=> 'Denne administration gør det muligt at aktivere eller deaktivere <a class="hint" href="http://wiki.flatpress.org/doc:plugins" title="Hvad er et plugin?">plugins</a>, som gør funktionaliteten i FlatPress meget fleksibel.</p>'.
						'<p>For at integrere et nyt plugin i FlatPress, skal dette plugin indlæses i <code>fp-plugins/</code> -biblioteket. '.
						'Hvis der er et navn og en beskrivelse i det nye plugin på det relevante sted, vises disse tekster også her.',
		'name'		=> 'Navn',
		'description'=>'Beskrivelse',
		'author'	=> 'Forfatter',
		'version'	=> 'Version',
		'action'	=> 'Handling',
	);
	
	$lang['admin']['plugin']['default']['msgs'] = array(
		1	=> 'Konfiguration gemt',
		-1	=> 'Der opstod en fejl under lagring. Syntaksen for plug-in\'et er muligvis ikke korrekt.',
	);
	
	/* system errors */
	
	$lang['admin']['plugin']['errors'] = array(
		'head'		=> 'Følgende fejl opstod, da plug-in\'et blev indlæst:',
		'notfound'	=> 'Pluginet blev ikke fundet. Skippet.',
		'generic'	=> 'Fejlnummer %d',
	);
	
?>

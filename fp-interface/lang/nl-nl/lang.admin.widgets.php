<?php

	$lang['admin']['widgets']['submenu']['default'] = 'Beheer Widgets';
	$lang['admin']['widgets']['submenu']['raw'] 	= 'Beheer Widgets (raw)';

	/* default action */
	
	$lang['admin']['widgets']['default'] = array(
		'head'		=> 'Beheer Widgets',
		
		'descr'		=> 	'Een <a class="hint" '.
						'href="https://wiki.flatpress.org/doc:plugins:standard" title="Wat is een Widget?">'.
						'Widget</a> is een dynamisch onderdeel dat gegevens kan weergeven en kan communiceren met de gebruiker.
						Hoewel <strong>Themas</strong> bedoeld zijn om te veranderen hoe je blog eruit ziet, Widgets zal looks en functionaliteiten
						<strong>uitbreiden</strong>.</p>

						<p>Widgets kan worden gesleept naar speciale gebieden van uw thema genaamd de 
						<strong>WidgetSets</strong>. Het nummer en de naam van de WidgetSets kunnen verschillen met het 
						thema dat u kiest.</p>

						<p>FlatPress komt met verschillende widgets: zo zijn er widgets om je met inloggen te helpen, om 
						deen zoek box te tonen, etc.</p>
						
						<p>Elke Widget is gedefineert door een <a class="hint" '.
						'href="https://wiki.flatpress.org/res:plugins" title="Wat is een Widget?">plugin</a>.',
						
		'availwdgs'	=> 'Beschikbare Widgets',
		'trashcan'	=> 'Sleep het hier om te verwijderen',
		
		'themewdgs' => 'Widgetsets voor dit thema',
		'themewdgsdescr' => 'Het thema wat je nu hebt geslekteerd heeft de volgende widgetsets',
		'oldwdgs'	=> '\\\andere widgetsets',
		'oldwdgsdescr' =>'De volgende widgetsets lijkt niet behoren tot elke andere van de '.
						'widgetsets als boven getoond. Dit kan herinneringen zijn van andere thema.',
		
		'submit'	=> 'Bewaar veranderingen',

	);
	
	$lang['admin']['widgets']['default']['stdsets'] = array(
		'top'		=> 'Bovenste balk',
		'bottom'	=> 'Onderste balk',
		'left'		=> 'Linker balk',
		'right'		=> 'Rechter balk',
	);
	
	$lang['admin']['widgets']['default']['msgs'] = array(
		1	=> 'Configuratie bewaard',
		-1	=> 'Een probleem is ontstaan bij het bewaren, probeer het nogmaals',
	);


	
	/* "raw" panel */	
	
	$lang['admin']['widgets']['raw'] = array(
		'head'		=> 'Beheer Widgets (<em>raw editor</em>)',
		'descr'		=> 'A <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="Wat is een Widget?">'.
						'Widget</a> is aeen visueel element van een <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="Wat is een plugin?">'.
						'Plugin</a> die u in een aantal speciale gebieden kunt plaatsen (the <em>widgetsets</em>) op jouw blog paginas. </p>'.
						'<p>Dit is de <strong>raw</strong> editor; voor sommige gevorderde gebruikers of mensen die '.
						'niet aan JavaScript de voorkeur geven.',
						
		'fset1'		=> 'Editor',
		'fset2'		=> 'Bewaar veranderingen',
		'submit'	=> 'Toepassen',

	);
	
	
	$lang['admin']['widgets']['raw']['msgs'] = array(
		1	=> 'Configuratie bewaard',
		-1	=> 'Er is een fout opgetreden tijdens het opslaan. Dit kan om verschillende redenen gebeuren: misschien bevat uw bestand syntaxisfouten.',
	);

		

	/* system errors */
		
	$lang['admin']['widgets']['errors'] = array(
		'generic'	=> 'De widget genaamd <strong>%s</strong> is niet geregistreerd, en al worden overgeslagen. '.
 				'Als de plugin is uitgeschakeld in de <a href="admin.php?p=plugin">plugin paneel</a>?'

	);
	
?>

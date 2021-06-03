<?php
	
	$lang['admin']['panel']['maintain'] = 'Onderhoud';

	$lang['admin']['maintain']['default'] = array(
		'head'		=> 'Onderhoud',
		'descr'		=> 'Kom hier als je denkt dat er iets is verknoeid, '.
					'en misschien vind je hier een oplossing.
					Dit werkt echter mogelijk niet.',
		'opt0'		=> '&laquo; Terug naar hoofdmenu',
		'opt1'		=> 'Herbouw index',
		'opt2'		=> 'Thema- en sjablonencache opschonen',
		'opt3'		=> 'Bestandsmachtigingen herstellen',
		'opt4'		=> 'Toon info over PHP',
		'opt5'		=> 'Controleren op updates',

		'chmod_info'	=> "De volgende bestandsmachtigingen <strong>kan niet</strong>
				worden teruggezet naar 0777; waarschijnlijk is de bestandseigenaar niet hetzelfde als de
				webserver's. Meestal kunt u deze kennisgeving negeren.",
		
	);
	
	$lang['admin']['maintain']['default']['msgs'] = array(
		1		=> 'Operation completed'
	);
	
	$lang['admin']['maintain']['updates'] = array(
		'head'	=> 'Updates',
		'list'	=> '<ul>
		<li>Je hebt FlatPress versie <big>%s</big></li>
		<li>Laatste stabiele versie voor FlatPress is <big><a href="%s">%s</a></big></li>
		<li>Laatste onstabiele versie voor FlatPress is <big><a href="%s">%s</a></big></li>
		</ul>',
		'notice'=>'Kennisgeving:'
		
	);
	
	
	
	$lang['admin']['maintain']['updates']['msgs'] = array(
		1		=> 'Er zijn updates beschikbaar!',
		2		=> 'U bent al up-to-date',
		-1		=> 'Kan updates niet ophalen'
	);

?>

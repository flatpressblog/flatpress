<?php

	$lang['admin']['widgets']['submenu']['default'] = 'Management-widgets';
	$lang['admin']['widgets']['submenu']['raw'] 	= 'Management Widgets (rå)';

	/* default action */
	
	$lang['admin']['widgets']['default'] = array(
		'head'		=> 'Widget-styring',
		
		'descr'		=> 'FlatPress har forskellige widgets ombord, f.eks. login- eller søgefunktionen. En <a class="hint" '.
						'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="What is a Widget?">'.
						'Widget</a> er et dynamisk kontrolelement i en <a class="hint" '.
						'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="What is a plugin?">'.
						'plugin</a>, der placeres i widgetområdet'.
						'(<em>Widgetsets</em>) widget-området på bloggen.'.
						'</p>'.
						'<p><strong>Flyt</strong> widget-elementet fra <strong>Available Widget</strong> '.
						'-valget til den ønskede position i widget-linjen.',
						
		'availwdgs'	=> 'Tilgængelige widgets',
		'trashcan'	=> 'Flyt her for at slette',
		
		'themewdgs' => 'Widget-sæt til dette tema',
		'themewdgsdescr' => 'Følgende widgets er tilgængelige i dette tema',
		'oldwdgs'	=> 'Andre widget-sæt',
		'oldwdgsdescr' =>'Følgende widget-sæt ser ikke ud til at passe til nogen af de ovennævnte '.
						'widget-sæt, der er anført ovenfor. Det kan være en del af et andet tema.',
		
		'submit'	=> 'Gem ændringer',

	);
	
	$lang['admin']['widgets']['default']['stdsets'] = array(
		'top'		=> 'Overskrift',
		'bottom'	=> 'Fodliste',
		'left'		=> 'Venstre bjælke',
		'right'		=> 'Højre bjælke',
	);
	
	$lang['admin']['widgets']['default']['msgs'] = array(
		1	=> 'Konfiguration gemt',
		-1	=> 'Der opstod en fejl under lagring af konfigurationen, prøv venligst igen',
	);


	
	/* "raw" panel */	
	
	$lang['admin']['widgets']['raw'] = array(
		'head'		=> 'Administrations-widgets (<em>raw editor</em>)',
		'descr'		=> 'En <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a Widget?">'.
						'Widget</a> er et dynamisk kontrolelement i et <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a plugin?">'.
						'plugin</a>, der frit kan placeres i bloggens '.
						'(<em>Widgetsets</em>) widget-område. '.
						'Tilføj kun widgets/plugins, der er blevet aktiveret i <a href="admin.php?p=plugin">Plugin Administration</a>. '.						
						'</p>'.

  /* added by laborix, only available in the german language pack for svn */

'<p>FlatPress (raw) navngivningskonventioner:</p>
<pre>
Beskrivelse               Widget/Plugin
-----------------------------------------------  
Administrationsområde    = adminarea
Kalender                 = calendar
Søgning                  = searchbox
Arkiv                    = archives
Kategorier               = categories
Seneste indlæg           = lastentries
Seneste kommentarer      = lastcomments

Eksempel på blokparser   = blockparser:menu
</pre>'.

  /* end of "added by laborix" */

						'<p>Med denne editor kan du manuelt redigere widgetområdet. '.
						'Dette er beregnet til alle dem, der ikke har Javascript aktivt eller blot redigerer alt i hånden.',
						
		'fset1'		=> 'Redaktør',
		'fset2'		=> 'Udfør ændringer',
		'submit'	=> 'Gem ændringer',

	);
	
	
	$lang['admin']['widgets']['raw']['msgs'] = array(
		1	=> 'Konfiguration gemt',
		-1	=> 'Der opstod en fejl, da konfigurationen blev gemt. Tjek venligst igen for korrekthed.',
	);

		

	/* system errors */
		
	$lang['admin']['widgets']['errors'] = array(
		'generic'	=> '<strong>%s</strong>-widgetten er ikke med på listen og bliver sprunget over. '.
 				'Tjek venligst i <a href="admin.php?p=plugin">Plugin-administrationen</a>, om dette plugin er aktiveret. '

	);
	
?>

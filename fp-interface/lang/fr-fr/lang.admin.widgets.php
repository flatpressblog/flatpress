<?php

	$lang['admin']['widgets']['submenu']['default'] = 'G&eacute;rer les Widgets';
	$lang['admin']['widgets']['submenu']['raw'] 	= 'G&eacute;rer les Widgets (raw)';

	/* default action */
	
	$lang['admin']['widgets']['default'] = array(
		'head'		=> 'G&eacute;rer les Widgets',
		
		'descr'		=> 	'Un <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:widgets" title="C\'est quoi un Widget?">'.
						'Widget</a> est un composant dynamique qui peut afficher des donn&eacute;es et interagir avec l\'utilisateur.
						Bien que les<strong> Th&egrave;mes</strong> ont pour but de modifier l\'apparence de votre blog, les widgets 
						<strong>&eacute;tendent</strong> les possibilit&eacute;s et les fonctions.</p>

						<p>Les Widgets peuvent &ecirc;tre d&eacute;plac&eacute;s vers des zones sp&eacute;ciales de votre th&egrave;me appel&eacute;es 
						<strong>WidgetSets</strong>. Le nombre et le nom des WidgetSets peuvent varier avec le
th&egrave;me que vous choisissez.</p>

						<p>FlatPress est fourni avec plusieurs widgets: il y a des widgets pour aider &agrave; la connexion, pour
afficher une bo&icirc;te de recherche, etc</p>
						
						<p>Chaque widget est d&eacute;fini par un <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="Aide Widget?">plugin</a>.',
						
		'availwdgs'	=> 'Widgets disponibles',
		'trashcan'	=> 'D&eacute;placer ici pour supprimer',
		
		'themewdgs' => 'Widgetsets pour ce th&egrave;me',
		'themewdgsdescr' => 'Le th&egrave;me que vous avez s&eacute;lectionn&eacute; dispose des widgetsets suivants',
		'oldwdgs'	=> 'Autres widgetsets',
		'oldwdgsdescr' =>'Les widgetsets suivantes semblent ne pas appartenir &agrave; l\'un des '.
						'widgetsets list&eacute;s ici. Ceux-ci proviennent peut-&ecirc;tre d\'un autre th&egrave;me.',
		
		'submit'	=> 'Enregistrer modifications',

	);
	
	$lang['admin']['widgets']['default']['stdsets'] = array(
		'top'		=> 'Zone du haut',
		'bottom'	=> 'Zone du bas',
		'left'		=> 'Zone de gauche',
		'right'		=> 'Zone de droite',
	);
	
	$lang['admin']['widgets']['default']['msgs'] = array(
		1	=> 'Modifications enregistr&eacute;es',
		-1	=> 'Echec de la sauvegarde, veuillez essayer &agrave; nouveau',
	);


	
	/* "raw" panel */	
	
	$lang['admin']['widgets']['raw'] = array(
		'head'		=> 'G&eacute;rer Widgets (<em>&eacute;diteur RAW</em>)',
		'descr'		=> 'Un <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="C\'est quoi un Widget?">'.
						'Widget</a> est un &eacute;l&eacute;ment visuel de <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="C\'est quoi un plugin?">'.
						'Plugin</a> que vous pouvez mettre dans certaines zones particuli&egrave;res (le <em>widgetsets</em>) des pages de votre blog. </p>'.
						'<p>Voici l\'<strong>&eacute;diteur</strong> avanc&eacute; ; r&eacute;serv&eacute; aux personnes experiment&eacute;es '.
						'qui pref&egrave;rent ne pas utiliser Javascript.',
						
		'fset1'		=> 'Editeur',
		'fset2'		=> 'Appliquer modifications',
		'submit'	=> 'Appliquer',

	);
	
	
	$lang['admin']['widgets']['raw']['msgs'] = array(
		1	=> 'Modifications enregistr&eacute;es',
		-1	=> 'Une erreur est survenue pendant l\'enregistrement. V&eacute;rifiez les param&egrave;tres.',
	);

		

	/* system errors */
		
	$lang['admin']['widgets']['errors'] = array(
		'generic'	=> 'Le widget appel&eacute; <strong>%s</strong> n\'est pas enregistr&eacute;, et sera ignor&eacute;. '.
 				'est le plugin activ&eacute; dans le <a href="admin.php?p=plugin">panneau des plugins</a>?'

	);
	
?>

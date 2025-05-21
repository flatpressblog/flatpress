<?php

	$lang['admin']['plugin']['submenu'] = array (
		'default'		=> 'G&eacute;rer Plugins'
	);
	
	/* main plugin panel */

	$lang['admin']['plugin']['default'] = array(
	
		'head'		=> 'G&eacute;rer les Plugins',
		'enable'	=> 'Activer',
		'disable'	=> 'D&eacute;sactiver',
		'descr'		=> 'Un <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="C\'est quoi un plugin?">'.
						'Plugin</a> est un composant qui &eacute;tend les possibilit&eacute;s de FlatPress.</p>'.
						'<p>Vous pouvez installer les plugins en les transf&eacute;rant dans le dossier <code>fp-plugins/</code> '.
						'de votre ftp.</p>'.
						'<p>Ce menu vous permet d\'activer ou de d&eacute;sactiver les plugins',
		'name'		=> 'Nom',
		'description'=>'Description',
		'author'	=> 'Auteur',
		'version'	=> 'Version',
		'action'	=> 'Action',
	);
	
	$lang['admin']['plugin']['default']['msgs'] = array(
		1	=> 'Configuration enregistr&eacute;e',
		-1	=> 'Une erreur est survenue pendant l\'enregistrement. Il peut y avoir plusieurs raisons: erreurs de synthaxes.',
	);
	
	/* system errors */
	
	$lang['admin']['plugin']['errors'] = array(
		'head'		=> 'Des erreurs sont survenues pendant le chargement des plugins:',
		'notfound'	=> 'Plugin non trouv&eacute;.',
		'generic'	=> 'Erreur num&eacute;ro %d',
	);
	
?>

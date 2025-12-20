<?php
$lang ['admin'] ['plugin'] ['submenu'] = array (
	'default' => 'Gérer les Plugins'
);

/* main plugin panel */
$lang ['admin'] ['plugin'] ['default'] = array(
	'head' => 'Gérer les Plugins',
	'enable' => 'Activer',
	'disable' => 'Désactiver',
	'descr' => 'Un <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="Qu\'est-ce qu\'un plugin ?">Plugin</a> est un composant qui étend les fonctionnalités de FlatPress.</p>' . //
		'<p>Vous pouvez installer des plugins en les transférant dans le répertoire <code>fp-plugins/</code>.</p><p>Ce panneau vous permet d\'activer et de désactiver les plugins.',
	'name' => 'Nom',
	'description' => 'Description',
	'author' => 'Auteur',
	'version' => 'Version',
	'action' => 'Action'
);

$lang ['admin'] ['plugin'] ['default'] ['msgs'] = array(
	1 => 'Configuration enregistrée',
	-1 => 'Une erreur s\'est produite lors de l\'enregistrement. Cela peut arriver pour plusieurs raisons : le fichier peut contenir des erreurs de syntaxe.'
);

/* system errors */
$lang ['admin'] ['plugin'] ['errors'] = array(
	'head' => 'Les erreurs suivantes se sont produites lors du chargement des plugins :',
	'notfound' => 'Plugin introuvable. Ignoré.',
	'generic' => 'Erreur numéro %d'
);
?>

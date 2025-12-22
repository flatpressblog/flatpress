<?php
$lang ['admin'] ['plugin'] ['submenu'] = array (
	'default' => 'Gérer Plugins'
);

/* main plugin panel */
$lang ['admin'] ['plugin'] ['default'] = array(
	'head' => 'Gérer les Plugins',
	'enable' => 'Activer',
	'disable' => 'Désactiver',
	'descr' => 'Un <a class="hint" href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="Qu’est-ce qu’un plugin?">plugin</a> est un composant qui étend les fonctionnalités de FlatPress.</p>' . //
			'<p>Vous pouvez installer des plugins en les transférant dans le dossier <code>fp-plugins/</code> de votre serveur FTP.</p><p>Ce menu vous permet d’activer ou de désactiver les plugins.',
	'name' => 'Nom',
	'description' => 'Description',
	'author' => 'Auteur',
	'version' => 'Version',
	'action' => 'Action'
);

$lang ['admin'] ['plugin'] ['default'] ['msgs'] = array(
	1 => 'Configuration enregistrée avec succès.',
	2 => 'L’administrateur a été modifié. Vous allez maintenant être déconnecté.',
	-1 => 'Une erreur est survenue lors de l’enregistrement. Cela peut être dû à des erreurs de syntaxe.'
);

/* system errors */
$lang ['admin'] ['plugin'] ['errors'] = array(
	'head' => 'Des erreurs sont survenues lors du chargement des plugins :',
	'notfound' => 'Plugin introuvable.',
	'generic' => 'Erreur numéro %d'
);
?>

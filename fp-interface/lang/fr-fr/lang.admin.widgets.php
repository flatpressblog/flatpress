<?php
$lang ['admin'] ['widgets'] ['submenu'] ['default'] = 'Gérer les Widgets';
$lang ['admin'] ['widgets'] ['submenu'] ['raw'] = 'Gérer les Widgets (éditeur brut)';

/* action par défaut */
$lang ['admin'] ['widgets'] ['default'] = array(
	'head' => 'Gérer les Widgets',

	'descr' => 'Un <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="C’est quoi un Widget ?">' . //
		'Widget</a> est un composant dynamique qui peut afficher des données et interagir avec l’utilisateur. ' . //
		'Alors que les <strong>Thèmes</strong> modifient l’apparence de votre blog, les widgets ' . //
		'étendent les fonctionnalités et les possibilités.' . //

		'<p>Les Widgets peuvent être placés dans des zones spéciales de votre thème appelées ' . //
		'<strong>WidgetSets</strong>. Le nombre et les noms des WidgetSets dépendent du thème sélectionné.</p>' . //

		'<p>FlatPress fournit plusieurs widgets intégrés : par exemple, des widgets pour la connexion, une boîte de recherche, etc.</p>' . //

		'<p>Chaque widget est défini par un <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="Aide Widgets ?">plugin</a>.',

	'availwdgs' => 'Widgets disponibles',
	'trashcan' => 'Déplacez ici pour supprimer',

	'themewdgs' => 'WidgetSets pour ce thème',
	'themewdgsdescr' => 'Le thème sélectionné propose les WidgetSets suivants',
	'oldwdgs' => 'Autres WidgetSets',
	'oldwdgsdescr' => 'Ces WidgetSets ne semblent pas appartenir au thème actuel. Ils proviennent peut-être d’un autre thème.',

	'submit' => 'Enregistrer les modifications',
	'drop_here' => 'Placez ici'
);

$lang ['admin'] ['widgets'] ['default'] ['stdsets'] = array(
	'top' => 'Zone du haut',
	'bottom' => 'Zone du bas',
	'left' => 'Zone de gauche',
	'right' => 'Zone de droite'
);

$lang ['admin'] ['widgets'] ['default'] ['msgs'] = array(
	1 => 'Modifications enregistrées avec succès',
	-1 => 'Une erreur est survenue lors de l’enregistrement des modifications'
);

/* panneau brut */
$lang ['admin'] ['widgets'] ['raw'] = array(
	'head' => 'Gérer les Widgets (<em>éditeur brut</em>)',
	'descr' => 'Un <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="C’est quoi un Widget ?">' . //
		'Widget</a> est un élément visuel défini par un <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="C’est quoi un Plugin ?">' . //
		'plugin</a>, que l’on peut ajouter dans certaines zones spécifiques de votre blog (les <em>WidgetSets</em>).</p>' . //
		'<p>Cet éditeur avancé est destiné aux utilisateurs expérimentés préférant ne pas utiliser JavaScript.',

	'fset1' => 'Éditeur',
	'fset2' => 'Appliquer les modifications',
	'submit' => 'Appliquer'
);

$lang ['admin'] ['widgets'] ['raw'] ['msgs'] = array(
	1 => 'Modifications enregistrées avec succès',
	-1 => 'Une erreur est survenue pendant l’enregistrement. Veuillez vérifier les paramètres.'
);

/* erreurs système */
$lang ['admin'] ['widgets'] ['errors'] = array(
	'generic' => 'Le widget nommé <strong>%s</strong> n’est pas enregistré et sera ignoré. ' . //
		'Assurez-vous que le plugin est activé dans le <a href="admin.php?p=plugin">panneau des plugins</a>.'
);
?>
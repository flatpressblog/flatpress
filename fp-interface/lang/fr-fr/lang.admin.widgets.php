<?php
$lang ['admin'] ['widgets'] ['submenu'] ['default'] = 'Gérer les widgets';
$lang ['admin'] ['widgets'] ['submenu'] ['raw'] = 'Gérer les widgets (raw)';

/* default action */
$lang ['admin'] ['widgets'] ['default'] = array(
	'head' => 'Gérer les widgets',

	'descr' => 'Un <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="C\'est quoi un widget ?">' . //
		'Widget</a> est un composant dynamique qui peut afficher des données et interagir avec l\'utilisateur. ' . //
		'Bien que les <strong>thèmes</strong> aient pour but de modifier l\'apparence de votre blog, les widgets ' . //
		'<strong>étendent</strong> les possibilités et les fonctions.</p>' . //

		'<p>Les widgets peuvent être déplacés vers des zones spéciales de votre thème appelées ' . //
		'<strong>widgetsets</strong>. Le nombre et le nom des widgetsets peuvent varier avec le thème que vous choisissez.</p>' . //

		'<p>FlatPress est fourni avec plusieurs widgets : il y a des widgets pour aider à la connexion, pour afficher une boîte de recherche, etc.</p>' . //

		'<p>Chaque widget est défini par un <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="Aide widget ?">plugin</a>.',

	'availwdgs' => 'Widgets disponibles',
	'trashcan' => 'Déplacer ici pour supprimer',

	'themewdgs' => 'Widgetsets pour ce thème',
	'themewdgsdescr' => 'Le thème que vous avez sélectionné dispose des widgetsets suivants',
	'oldwdgs' => 'Autres widgetsets',
	'oldwdgsdescr' => 'Les widgetsets suivants semblent ne pas appartenir à l\'un des ' . //
		'widgetsets listés ici. Ceux-ci proviennent peut-être d\'un autre thème.',

	'submit' => 'Enregistrer les modifications',
	'drop_here' => 'Placer ici'
);

$lang ['admin'] ['widgets'] ['default'] ['stdsets'] = array(
	'top' => 'Zone du haut',
	'bottom' => 'Zone du bas',
	'left' => 'Zone de gauche',
	'right' => 'Zone de droite'
);

$lang ['admin'] ['widgets'] ['default'] ['msgs'] = array(
	1 => 'Modifications enregistrées',
	-1 => 'Échec de la sauvegarde, veuillez essayer à nouveau'
);

/* "raw" panel */
$lang ['admin'] ['widgets'] ['raw'] = array(
	'head' => 'Gérer les widgets (<em>éditeur RAW</em>)',
	'descr' => 'Un <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="C\'est quoi un widget ?">' . //
		'Widget</a> est un élément visuel de <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="C\'est quoi un plugin ?">' . //
		'Plugin</a> que vous pouvez mettre dans certaines zones particulières (les <em>widgetsets</em>) des pages de votre blog.</p>' . //
		'<p>Voici l\'<strong>éditeur</strong> avancé réservé aux personnes expérimentées ' . //
		'qui préfèrent ne pas utiliser JavaScript.',

	'fset1' => 'Éditeur',
	'fset2' => 'Appliquer les modifications',
	'submit' => 'Appliquer'
);

$lang ['admin'] ['widgets'] ['raw'] ['msgs'] = array(
	1 => 'Modifications enregistrées',
	-1 => 'Une erreur est survenue pendant l\'enregistrement. Vérifiez les paramètres.'
);

/* system errors */
$lang ['admin'] ['widgets'] ['errors'] = array(
	'generic' => 'Le widget appelé <strong>%s</strong> n\'est pas enregistré, et sera ignoré. ' . //
		'Le plugin est-il activé dans le <a href="admin.php?p=plugin">panneau des plugins</a> ?'
);
?>

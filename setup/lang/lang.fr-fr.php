<?php
/*
 * LangId: Français
 */
$lang ['setup'] = array(
	'setup' => 'Configuration'
);

$lang ['locked'] = array(
	'head' => 'La configuration est verrouillée',
	'descr' => 'On dirait que la configuration est déjà en place: Le fichier de blocage <code>%s</code> existe déjà.

		Si tu veux redémarrer l\'installation, supprime d\'abord ce fichier.

		<strong>Attention!</strong> Le fichier <code>setup.php</code> et le répertoire <code>setup/</code> ne doivent pas rester sur le serveur. Merci de les supprimer une fois la configuration terminée!

		<ul>
		<li><a href="%s">Ok, amène-moi à mon blog</a></li>
		<li><a href="%s">J\'ai supprimé le fichier. Redémarrer le setup.</a></li>
		</ul>'
);

$lang ['err'] = array(
	'setuprun1' => 'La configuration est en cours.',

	'setuprun2' => 'La configuration est déjà en cours: si tu es l\'administrateur, tu peux supprimer ',
	'setuprun3' => ' pour redémarrer.',
	'writeerror' => 'Erreur d\'écriture',

	'fpuser1' => ' n\'est pas un utilisateur valide. ' . //
		'Le nom d\'utilisateur doit être alphanumérique et ne doit pas contenir d\'espaces.',
	'fpuser2' => ' n\'est pas un utilisateur valide. ' . //
		'Le nom d\'utilisateur ne peut contenir que des lettres, des chiffres et 1 trait de soulignement.',
	'fppwd' => 'Le mot de passe doit contenir au moins 6 caractères et ne doit pas comporter d\'espaces.',
	'fppwd2' => 'Les mots de passe ne correspondent pas.',
	'email' => ' n\'est pas une adresse e-mail valide.',
	'www' => ' n\'est pas une URL valide',
	'error' => '<p><big>Erreur!</big> ' . //
		'Les erreurs suivantes sont survenues lors du traitement du formulaire :</p><ul>'
);

$lang ['step1'] = array(
	'head' => 'Bienvenue chez FlatPress!',
	'descr' => 'Merci d\'avoir choisi <strong>FlatPress</strong>.

		Avant de pouvoir commencer ton tout nouveau blog, tu dois encore indiquer quelques petites choses.

		Mais ne t\'inquiète pas, ça ne durera pas longtemps !',
	'descrl1' => 'Choisis ta langue.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Pas dans la liste ?</a>',
	'descrlang' => 'Si tu ne trouves pas ta langue dans la liste, regarde s\'il existe <a href="https://wiki.flatpress.org/res:language" target="_blank" rel="external">un pack de langue correspondant</a>:

		<pre>%s</pre>

		Pour installer un pack de langue, il suffit de télécharger son contenu dans ton répertoire <code>flatpress/</code>. Puis <a href="./setup.php">redémarre la configuration</a>.',
	'descrw' => '<strong>La seule</strong> chose dont tu as besoin pour faire fonctionner FlatPress est un répertoire <em>inscriptible</em>.

		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => 'Créer un utilisateur',
	'descr' => 'Presque terminé ! Il ne reste plus que les détails suivants:',
	'fpuser' => 'Nom d\'utilisateur',
	'fppwd' => 'Mot de passe',
	'fppwd2' => 'Mot de passe (répétition)',
	'www' => 'Page d\'accueil',
	'email' => 'Courrier électronique'
);

$lang ['step3'] = array(
	'head' => 'Prêt',
	'descr' => '<strong>C\'est tout.</strong>

		Pas croyable?

		Non, en fait, <strong>c\'est maintenant que ça commence vraiment</strong>! Mais le blogging est maintenant <em>ton</em> devoir ;)

		<ul>
		<li>Vers la <a href="%s">page principale de ton blog</a></li>
		<li>Bon blog! <a href="%s">Connectez-vous maintenant</a></li>
		<li>Tu souhaites faire part de tes compliments ou de tes critiques? Visite-nous sur <a href="https://www.flatpress.org/" target="_blank" rel="external">FlatPress.org</a>!</li>
		</ul>

		Merci d\'avoir choisi FlatPress!'
);

$lang ['buttonbar'] = array(
	'next' => 'Continuer >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Menu';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]Page d\'accueil[/url]
[*][url=?paged=1]Blog[/url]
[*][url=static.php?page=about]Sur[/url]
[*][url=contact.php]Contact[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'Bienvenue chez FlatPress!';
$lang ['samplecontent'] ['entry'] ['content'] = 'Voici un exemple de contribution. Il te montre quelques fonctions de [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url].

L\'élément "more" te permet de passer du résumé de l\'article à l\'article complet.

[more]


[h4]Mise en forme du texte[/h4]

Dans FlatPress, tu formates ton contenu avec [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]BBcode[/url] (Bulletin-Board-Code). Avec le BBCode, c\'est très facile. Vous voulez des exemples? [b] fait [b]du texte en gras[/b], [i] [i]du texte en italique[/i].

[quote]L\'élément [b]quote[/b] permet de marquer les citations.[/quote]

[code]L\'élément \'code\' crée une section avec une largeur de caractère fixe.
Il peut également
   représenter des indentations.[/code]

Les éléments \'img\' (images) et \'url\' (liens) ont des options spéciales. Pour en savoir plus, consulte le [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]FlatPress-Wiki[/url].


[h4]Entrées (articles de blog) et pages statiques[/h4]

Ceci est une entrée, alors que [url=static.php?page=about]Sur[/url] est une [b]page statique[/b]. Contrairement à une entrée, une page statique ne peut pas être commentée et n\'apparaît pas non plus dans les listes d\'entrées du blog.

Les pages statiques sont utiles pour les informations générales, par exemple une page d\'accueil fixe ou les mentions légales. On pourrait même renoncer complètement aux fonctions de blog et créer avec FlatPress un site web avec uniquement des pages statiques.

Dans la [url=admin.php]zone d\'administration[/url] tu peux créer des entrées et des pages statiques - et définir si la page d\'accueil de ton blog FlatPress doit être une page statique ou l\'aperçu du blog.


[h4]Plugins[/h4]

Tu peux adapter FlatPress de manière complète à tes besoins en l\'enrichissant de [url=https://wiki.flatpress.org/doc:plugins:standard target=_blank rel=external]Plugins[/url]. Le BBCode, par exemple, est un plugin.

Voici un autre exemple de contenu qui te montre encore plus de fonctions FlatPress :)

Deux pages statiques sont déjà préparées pour toi:
[list]
[*][url=static.php?page=about]Sur[/url]
[*][url=static.php?page=menu]Menu[/url] (Le contenu de cette page statique apparaît également dans la barre latérale de ton blog - c\'est la magie du [b]widget Blockparser[/b]. Le [url=https://wiki.flatpress.org/ target=_blank rel=external]FlatPress-Wiki[/url] a des informations à ce sujet, et bien plus encore!)
[/list]

Avec le plugin [b]PhotoSwipe-Plugin[/b] tu peux désormais placer encore plus facilement tes images, au choix en tant qu\'image individuelle orientée  float="left"-  ou  float="right"  ,entourée par le texte.
Tu peux même présenter des galeries entières à tes visiteurs grâce à l\'élément \'gallery\'. Tu découvriras ici [url=https://wiki.flatpress.org/res:plugins:photoswipe target=_blank rel=external]à quel point c\'est simple[/url].


[h4]Widgets[/h4]

Aucun des éléments de la barre latérale de ton blog n\'est fixe, tu peux les déplacer, les supprimer et en ajouter de nouveaux dans la zone d\'administration.

Ces éléments sont appelés [b]Widgets[/b]. Bien entendu, le wiki FlatPress dispose également de [url=https://wiki.flatpress.org/doc:tips:widgets target=_blank rel=external]nombreuses informations utiles sur ce sujet[/url].


[h4]Thèmes[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
Avec le thème FlatPress Leggero, tu disposes de 3 modèles de style - du classique au moderne. Ces modèles sont un excellent point de départ pour créer quelque chose de personnel.


[h4]Encore plus[/h4]

Tu souhaites en savoir plus sur FlatPress?

[list]
[*]Le [url=https://www.flatpress.org/?x target=_blank rel=external]blog du projet[/url] te permet de savoir ce qui se passe actuellement dans le projet FlatPress.
[*]Visite le [url=https://forum.flatpress.org/ target=_blank rel=external]forum de support[/url] pour obtenir de l\'aide et entrer en contact avec d\'autres utilisateurs de FlatPress.
[*]Télécharge de superbes [b]thèmes[/b] créés par la communauté à partir du [url=https://wiki.flatpress.org/res:themes target=_blank rel=external]Wiki[/url].
[*]On y trouve également de superbes [url=https://wiki.flatpress.org/res:plugins target=_blank rel=external]plugins[/url].
[*]Obtenez [url=https://wiki.flatpress.org/res:language target=_blank rel=external]translation pack[/url] pour votre langue.
[*]Tu peux aussi suivre FlatPress sur [url=https://twitter.com/FlatPress target=_blank rel=external]X (Twitter)[/url] et [url=https://fosstodon.org/@flatpress target=_blank rel=external]Mastodon[/url].
[/list]


[h4]Comment puis-je soutenir FlatPress?[/h4]

[list]
[*]Soutenez le projet avec un [url=https://www.flatpress.org/home/static.php?page=donate target=_blank rel=external]petit don[/url].
[*][url=https://www.flatpress.org/contact/ target=_blank rel=external]Signale[/url] les erreurs survenues ou envoie-nous des propositions d\'amélioration.
[*]Les programmeurs sont invités à nous rejoindre sur [url=https://github.com/flatpressblog/flatpress target=_blank rel=external]GitHub[/url].
[*]Traduis FlatPress et sa documentation dans [url=https://wiki.flatpress.org/res:language target=_blank rel=external]ta langue[/url].
[*]Rejoignez la communauté FlatPress sur le [url=https://forum.flatpress.org/ target=_blank rel=external]forum de support[/url].
[*]Dis au monde entier à quel point FlatPress est génial! :)
[/list]


[h4]Alors, et maintenant ?[/h4]

[url=login.php]Connecte-toi[/url] pour commencer à bloguer dans [url=admin.php]Rapport d\'administration[/url].

Amusez-vous bien! :)

[i]L\'équipe de [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url][/i]

';

$lang ['samplecontent'] ['about'] ['subject'] = 'Sur';
$lang ['samplecontent'] ['about'] ['content'] = 'Écris ici quelque chose sur toi et sur ce blog. ([url=admin.php?p=static&action=write&page=about]Travaille-moi![/url])';
?>

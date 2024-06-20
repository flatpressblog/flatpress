<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => 'Le fichier <code>robots.txt</code> contrôle les robots d\'un moteur de recherche et le comportement des robots sur ton blog FlatPress. ' . //
		'Ici, tu peux créer et modifier un fichier <code>rotots.txt</code> pour l\'optimisation des moteurs de recherche.',
	'location' => '<strong>Emplacement de stockage :</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'Enregistrer robots.txt',

	// SEO Metatags part
	'legend_desc' => 'Description et mots-clés Descripción y palabras clave',
	'description' => 'Ces détails permettent de les retrouver plus facilement avec les moteurs de recherche et de les afficher sur les médias sociaux. <a class="hint" href="https://fr.wikipedia.org/wiki/Élément_meta" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => 'Insérez la description :',
	'sample_desc' => 'Articles, guides et plugins relatifs à FlatPress',
	'input_keywords' => 'Insérez les mots-clés :',
	'sample_keywords' => 'flatpress, flatpress articles, flatpress guides, flatpress plugins',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=fr#noindex" target="_blank" title="En savoir plus sur noindex">Désactiver l\'indexation</a>:',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=fr#nofollow" target="_blank" title="En savoir plus sur nofollow">Désactiver suivant</a>:',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=fr#noarchive" target="_blank" title="En savoir plus sur noarchive">Désactiver l\'archivage</a>:',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=fr#nosnippet" target="_blank" title="En savoir plus sur nosnippet">Désactiver les snippets</a>:'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'Accueil',
	'blog_home' => 'Accueil du blog',
	'blog_page' => 'Blog',
	'archive' => 'Archives',
	'category' => 'Catégorie',
	'tag' => 'Tag',
	'contact' => 'Nous contacter',
	'comments' => 'Commentaires',
	'pagenum' => 'Page #'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => 'Le fichier <code>robots.txt</code> a été enregistré avec succès',
	-1 => 'Le fichier <code>robots.txt</code> n\'a pas pu être enregistré (pas de droits d\'écriture dans le <code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code>) ?',

	2 => 'Les paramètres ont été enregistrés avec succès',
	-2 => 'Une erreur est survenue lors de l\'enregistrement'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'Il n\'y a pas de <code>robots.txt</code> ou il n\'est pas possible de créer un <code>robots.txt</code> le répertoire principal du document HTTP.'
);
?>

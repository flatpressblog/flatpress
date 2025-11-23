<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => 'Het <code>robots.txt</code>-bestand regelt de crawlers van een zoekmachine en het gedrag van de crawlers op je FlatPress-blog. ' . //
		'Hier kunt u een <code>rotots.txt</code>-bestand maken en bewerken voor zoekmachineoptimalisatie.',
	'location' => '<strong>Opslaglocatie:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'robots.txt opslaan',

	// SEO Metatags part
	'legend_desc' => 'Beschrijving en trefwoorden',
	'description' => 'Deze gegevens maken het gemakkelijker om ze te vinden met zoekmachines en om ze op sociale media te plaatsen. <a class="hint" href="https://nl.wikipedia.org/wiki/Metatag" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => 'Voeg de beschrijving in:',
	'sample_desc' => 'FlatPress-gerelateerde artikelen, gidsen en plugins',
	'input_keywords' => 'Voeg de sleutelwoorden in:',
	'sample_keywords' => 'flatpress, flatpress artikelen, flatpress gidsen, flatpress plugins',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noindex" target="_blank" title="Lees meer over noindex">Indexering weigeren</a>:',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nofollow" target="_blank" title="Lees meer over nofollow">Verbied volgen</a>:',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noarchive" target="_blank" title="Lees meer over noarchive">Archivering niet toestaan</a>:',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nosnippet" target="_blank" title="Lees meer over nosnippet">Snippets niet toestaan</a>:'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'Home',
	'blog_home' => 'Blog Home',
	'blog_page' => 'Blog',
	'archive' => 'Archief',
	'category' => 'Categorie',
	'tag' => 'Tag',
	'contact' => 'Contacteer ons',
	'comments' => 'Commentaar',
	'pagenum' => 'Pagina #',
	'introduction' => 'Inleiding'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => 'Het <code>robots.txt</code>-bestand is succesvol opgeslagen',
	-1 => 'Het <code>robots.txt</code>-bestand kon niet worden opgeslagen (Geen schrijfrechten in <code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code>)?',

	2 => 'Instellingen zijn succesvol opgeslagen',
	-2 => 'Er is een fout opgetreden tijdens het opslaan van'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'Er is geen <code>robots.txt</code> beschikbaar of er kan geen <code>robots.txt</code> worden aangemaakt in de hoofdmap van het HTTP-document.'
);
?>

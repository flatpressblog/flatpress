<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => 'Filen <code>robots.txt</code> styrer søgemaskinernes crawlere og crawlernes adfærd på din FlatPress-blog. ' . //
		'Her kan du oprette og redigere en <code>rotots.txt</code>-fil til søgemaskineoptimering.',
	'location' => '<strong>Opbevaringssted:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'robots.txt speichern',

	// SEO Metatags part
	'legend_desc' => 'Beskrivelse og nøgleord',
	'description' => 'Disse detaljer gør det lettere at finde dem med søgemaskiner og at poste dem på sociale medier. <a class="hint" href="https://de.wikipedia.org/wiki/Meta-Element" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => 'Indtast beskrivelsen:',
	'sample_desc' => 'FlatPress-relaterede artikler, vejledninger og plugins',
	'input_keywords' => 'Indsæt nøgleordene:',
	'sample_keywords' => 'flatpress, flatpress-artikler, flatpress-guider, flatpress-plugins',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noindex" target="_blank" title="Læs mere om noindex">Forbyd indeksering</a>:',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nofollow" target="_blank" title="Læs mere om nofollow">Forbyd at følge links</a>:',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noarchive" target="_blank" title="Læs mere om noarchive">Forbyd arkivering</a>:',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nosnippet" target="_blank" title="Læs mere om nosnippet">Forbyd udskæringer</a>:'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'Hjemmeside',
	'blog_home' => 'Bloggens hjemmeside',
	'blog_page' => 'Blog',
	'archive' => 'Arkiv',
	'category' => 'Kategori',
	'tag' => 'Dag',
	'contact' => 'Kontakt',
	'comments' => 'Kommentarer',
	'pagenum' => 'Side #',
	'introduction' => 'Introduktion'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => 'Filen <code>robots.txt</code> blev gemt med succes',
	-1 => 'Filen <code>robots.txt</code> kunne ikke gemmes (ingen skriveautorisation i <code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code>)?',

	2 => 'Indstillingerne er blevet gemt med succes',
	-2 => 'Der opstod en fejl under lagring'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'Der findes ingen <code>robots.txt</code>, eller der kan ikke oprettes nogen <code>robots.txt</code> i HTTP-dokumentets rodmappe.'
);
?>

<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => 'Datoteka <code>robots.txt</code> nadzira brskalnike iskalnikov in njihovo obnašanje na vašem blogu FlatPress. ' . //
		'Tu lahko ustvarite in urejate datoteko <code>rotots.txt</code> za optimizacijo za iskalnike.',
	'location' => '<strong>Lokacija shranjevanja:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'Shranite robots.txt',

	// SEO Metatags part
	'legend_desc' => 'Opis in ključne besede',
	'description' => 'Ti podatki olajšajo iskanje spletnega mesta s spletnimi iskalniki in omogočajo deljenje na družbenih omrežjih. <a class="hint" href="https://en.wikipedia.org/wiki/Meta_element" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => 'Vnesite opis:',
	'sample_desc' => 'Povezane novice, vodniki in vtičniki FlatPress',
	'input_keywords' => 'Vnesite ključne besede:',
	'sample_keywords' => 'flatpress, flatpress novice, flatpress vodniki, flatpress vtičniki',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noindex" target="_blank" title="Preberite več o noindex">Zakleni indeksiranje</a>:',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nofollow" target="_blank" title="Preberite več o nofollow">Zakleni sledenje</a>:',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noarchive" target="_blank" title="Preberite več o noarchive">Zakleni arhiviranje</a>:',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nosnippet" target="_blank" title="Preberite več o nosnippet">Prepovedano izrezovanje</a>:'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'Domov',
	'blog_home' => 'Glavna stran bloga',
	'blog_page' => 'Blog',
	'archive' => 'Arhiv',
	'category' => 'Kategorija',
	'tag' => 'Oznaka',
	'contact' => 'Kontaktirajte nas',
	'comments' => 'Komentarji',
	'pagenum' => 'Stran #'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => 'Datoteka <code>robots.txt</code> je bila uspešno shranjena',
	-1 => 'Datoteke <code>robots.txt</code> ni bilo mogoče shraniti (v <code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code> ni dovoljenja za pisanje)?',

	2 => 'Nastavitve so bile uspešno shranjene',
	-2 => 'Pri shranjevanju je prišlo do napake'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'V korenskem imeniku dokumenta HTTP ni na voljo <code>robots.txt</code> ali pa ga ni mogoče ustvariti.'
);
?>

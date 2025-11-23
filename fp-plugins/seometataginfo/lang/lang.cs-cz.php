<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => 'Soubor <code>robots.txt</code> řídí procházení vyhledávačů a jejich chování na vašem blogu FlatPress. ' . //
		'Zde můžete vytvořit a upravit soubor <code>rotots.txt</code> pro optimalizaci pro vyhledávače.',
	'location' => '<strong>Místo uložení:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'Save robots.txt',

	// SEO Metatags part
	'legend_desc' => 'Popis a klíčová slova',
	'description' => 'Tyto údaje usnadňují jejich nalezení pomocí vyhledávačů a zveřejnění na sociálních sítích. <a class="hint" href="https://en.wikipedia.org/wiki/Meta_element" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => 'Vložte popis:',
	'sample_desc' => 'FlatPress související články, průvodci a pluginy',
	'input_keywords' => 'Vložte klíčová slova:',
	'sample_keywords' => 'flatpress, flatpress články, flatpress průvodci, flatpress pluginy',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noindex" target="_blank" title="Přečtěte si více o noindex">Zakázat indexování</a>:',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nofollow" target="_blank" title="Přečtěte si více o nofollow">Zakázat sledování</a>:',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noarchive" target="_blank" title="Přečtěte si více o noarchive">Zakázat archivaci</a>:',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nosnippet" target="_blank" title="Přečtěte si více o nosnippet">Zakázat úryvky</a>:'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'Home',
	'blog_home' => 'Blog Domů',
	'blog_page' => 'Blog',
	'archive' => 'Archiv',
	'category' => 'Kategorie',
	'tag' => 'Tag',
	'contact' => 'Kontaktujte nás',
	'comments' => 'Komentáře',
	'pagenum' => 'Stránka #',
	'introduction' => 'Úvod'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => 'Soubor <code>robots.txt</code> byl úspěšně uložen',
	-1 => 'Soubor <code>robots.txt</code> se nepodařilo uložit (chybí oprávnění k zápisu do <code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code>)?',

	2 => 'Nastavení byla úspěšně uložena',
	-2 => 'Při ukládání došlo k chybě'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'V kořenovém adresáři dokumentu HTTP není k dispozici soubor <code>robots.txt</code> nebo jej nelze vytvořit.'
);
?>

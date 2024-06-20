<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => 'Il file <code>robots.txt</code> controlla i crawler di un motore di ricerca e il comportamento dei crawler sul vostro blog FlatPress. ' . //
		'Qui è possibile creare e modificare un file <code>rotots.txt</code> per l\'ottimizzazione dei motori di ricerca.',
	'location' => '<strong>Posizione di stoccaggio:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'Salvare robots.txt',

	// SEO Metatags part
	'legend_desc' => 'Descrizione e parole chiave',
	'description' => 'Questi dettagli rendono più facile trovarli con i motori di ricerca e pubblicarli sui social media. <a class="hint" href="https://it.wikipedia.org/wiki/Meta_tag" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => 'Inserire la descrizione:',
	'sample_desc' => 'Articoli, guide e plugin correlati a FlatPress',
	'input_keywords' => 'Inserire le parole chiave:',
	'sample_keywords' => 'flatpress, articoli flatpress, guide flatpress, plugin flatpress',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=it#noindex" target="_blank" title="Per saperne di più sul noindex">Disconoscimento dell\'indicizzazione</a>:',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=it#nofollow" target="_blank" title="Per saperne di più sul nofollow">Disconoscimento di quanto segue</a>:',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=it#noarchive" target="_blank" title="Per saperne di più sul noarchive">Disconoscimento dell\'archiviazione</a>:',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=it#nosnippet" target="_blank" title="Per saperne di più sul nosnippet">Disconoscimento degli snippet</a>:'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'Pagina principale',
	'blog_home' => 'Pagina principale del blog',
	'blog_page' => 'Blog',
	'archive' => 'Archivio',
	'category' => 'Categoria',
	'tag' => 'Etichetta',
	'contact' => 'Contatto',
	'comments' => 'Commenti',
	'pagenum' => 'Pagina #'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => 'Il file <code>robots.txt</code> è stato salvato con successo',
	-1 => 'Non è stato possibile salvare il file <code>robots.txt</code> (nessuna autorizzazione di scrittura in <code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code>)?',

	2 => 'Le impostazioni sono state salvate correttamente',
	-2 => 'Si è verificato un errore durante il salvataggio'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'Non è disponibile alcun file <code>robots.txt</code> o non è possibile creare un file <code>robots.txt</code> nella directory principale del documento HTTP.'
);
?>

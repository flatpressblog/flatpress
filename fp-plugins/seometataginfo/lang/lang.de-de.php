<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => 'Die Datei <code>robots.txt</code> steuert Crawler einer Suchmaschine und das Verhalten der Crawler auf deinem FlatPress-Blog. ' . //
		'Hier kannst du zur Suchmaschinenoptimierung eine <code>rotots.txt</code> Datei erstellen und bearbeiten.',
	'location' => '<strong>Speicherort:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'robots.txt speichern',

	// SEO Metatags part
	'legend_desc' => 'Beschreibung und Schlüsselwörter',
	'description' => 'Diese Angaben erleichtern das Auffinden mit Suchmaschinen und das Einfügen in sozialen Medien. <a class="hint" href="https://de.wikipedia.org/wiki/Meta-Element" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => 'Gebe die Beschreibung ein:',
	'sample_desc' => 'FlatPress-bezogene Artikel, Anleitungen und Plugins',
	'input_keywords' => 'Füge die Schlüsselwörter ein:',
	'sample_keywords' => 'flatpress, flatpress artikel, flatpress anleitungen, flatpress plugins',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=de#noindex" target="_blank" title="Lese mehr über noindex">Indizieren verbieten</a>:',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=de#nofollow" target="_blank" title="Lese mehr über nofollow">Link-Following verbieten</a>:',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=de#noarchive" target="_blank" title="Lese mehr über noarchive">Archivierung verbieten</a>:',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=de#nosnippet" target="_blank" title="Lese mehr über nosnippet">Ausschnitte verbieten</a>:'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'Startseite',
	'blog_home' => 'Startseite des Blogs',
	'blog_page' => 'Blog',
	'archive' => 'Archiv',
	'category' => 'Kategorie',
	'tag' => 'Tag',
	'contact' => 'Kontakt',
	'comments' => 'Kommentare',
	'pagenum' => 'Seite #'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => 'Die Datei <code>robots.txt</code> wurde erfolgreich gespeichert',
	-1 => 'Die Datei <code>robots.txt</code> konnte nicht gespeichert werden (Keine Schreibrechte im <code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code>)?',

	2 => 'Einstellungen wurden erfolgreich gespeichert',
	-2 => 'Ein Fehler ist beim Speichern aufgetreten'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'Es ist keine <code>robots.txt</code> vorhanden oder es kann keine <code>robots.txt</code> im HTTP-Dokument-Hauptverzeichnis angelegt werden.'
);
?>

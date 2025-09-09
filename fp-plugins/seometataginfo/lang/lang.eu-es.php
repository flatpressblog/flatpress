<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => '<code>robots.txt</code> fitxategiak bilaketa-motor baten arakatzaileak eta arakatzaileen portaera kontrolatzen ditu zure FlatPress blogean. ' . //
		'Hemen <code>rotots.txt</code> fitxategi bat sortu eta editatu dezakezu bilaketa-motorren optimizaziorako.',
	'location' => '<strong>Biltegiratzearen kokapena:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'Gorde robots.txt fitxategia',

	// SEO Metatags part
	'legend_desc' => 'Deskribapena eta gako-hitzak',
	'description' => 'Xehetasun hauek errazten dute bilatzaileetan aurkitzea eta sare sozialetan argitaratzea. <a class="hint" href="https://en.wikipedia.org/wiki/Meta_element" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => 'Sartu deskribapena:',
	'sample_desc' => 'FlatPressi buruzko artikuluak, gidak eta pluginak',
	'input_keywords' => 'Sartu gako-hitzak:',
	'sample_keywords' => 'flatpress, flatpress artikuluak, flatpress gidak, flatpress pluginak',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noindex" target="_blank" title="Read more about noindex">Debekatu indexatzea</a>:',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nofollow" target="_blank" title="Read more about nofollow">Debekatu jarraitzea</a>:',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noarchive" target="_blank" title="Read more about noarchive">Debekatu artxibatzea</a>:',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nosnippet" target="_blank" title="Read more about nosnippet">Debekatu snippetak</a>:'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'Hasiera',
	'blog_home' => 'Blogaren hasiera orria',
	'blog_page' => 'Bloga',
	'archive' => 'Artxiboa',
	'category' => 'Kategoria',
	'tag' => 'Etiketa',
	'contact' => 'Jarri gurekin harremanetan',
	'comments' => 'Iruzkinak',
	'pagenum' => 'Orri #'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => '<code>robots.txt</code> fitxategia ondo gorde da.',
	-1 => 'Errore bat gertatu da <code>robots.txt</code> fitxategia gordetzen saiatzean. Idazketa baimenak al dituzu <code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code> fitxategian?',

	2 => 'Ezarpenak ondo gorde dira.',
	-2 => 'Errore bat gertatu da ezarpenak gordetzen saiatzean.'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'Ez dago <code>robots.txt</code> fitxategirik eskuragarri edo ezin da <code>robots.txt</code> fitxategirik sortu HTTP dokumentuaren erro direktorioan.'
);
?>

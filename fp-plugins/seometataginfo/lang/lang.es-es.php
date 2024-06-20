<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => 'El archivo <code>robots.txt</code> controla los rastreadores de un motor de búsqueda y el comportamiento de los rastreadores en su blog FlatPress. ' . //
		'Aquí puede crear y editar un archivo <code>rotots.txt</code> para la optimización de motores de búsqueda.',
	'location' => '<strong>Lugar de almacenamiento:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'Guardar robots.txt',

	// SEO Metatags part
	'legend_desc' => 'Descripción y palabras clave',
	'description' => 'Estos datos facilitan su búsqueda en los motores de búsqueda y su publicación en las redes sociales. <a class="hint" href="https://es.wikipedia.org/wiki/Etiqueta_meta" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => 'Introduzca la descripción:',
	'sample_desc' => 'FlatPress artículos relacionados, guías y plugins',
	'input_keywords' => 'Introduzca las palabras clave:',
	'sample_keywords' => 'flatpress, flatpress artículos, flatpress guías, flatpress plugins',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=es#noindex" target="_blank" title="Más información sobre noindex">No permitir la indexación</a>:',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=es#nofollow" target="_blank" title="Más información sobre nofollow">No permitir lo siguiente</a>:',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=es#noarchive" target="_blank" title="Más información sobre noarchive">No permitir el archivo</a>:',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=es#nosnippet" target="_blank" title="Más información sobre nosnippet">No permitir fragmentos</a>:'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'Inicio',
	'blog_home' => 'Blog Inicio',
	'blog_page' => 'Blog',
	'archive' => 'Archivo',
	'category' => 'Categoría',
	'tag' => 'Tag',
	'contact' => 'Contacte con nosotros',
	'comments' => 'Comentarios',
	'pagenum' => 'Página #'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => 'El archivo <code>robots.txt</code> se ha guardado correctamente',
	-1 => 'No se ha podido guardar el archivo <code>robots.txt</code> (No hay autorización de escritura en <code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code>)?',

	2 => 'Los ajustes se han guardado correctamente',
	-2 => 'Se ha producido un error al guardar'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'No hay <code>robots.txt</code> disponibles o no se pueden crear <code>robots.txt</code> en el directorio raíz del documento HTTP.'
);
?>

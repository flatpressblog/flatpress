<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => 'O arquivo <code>robots.txt</code> controla os rastreadores de um mecanismo de pesquisa e o comportamento dos rastreadores em seu blog do FlatPress. ' . //
		'Aqui você pode criar e editar um arquivo <code>rotots.txt</code> para otimização de mecanismos de pesquisa.',
	'location' => '<strong>Local de armazenamento:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'Salvar o arquivo robots.txt',

	// SEO Metatags part
	'legend_desc' => 'Descrição e palavras-chave',
	'description' => 'Esses detalhes facilitam a localização nos mecanismos de pesquisa e a publicação nas mídias sociais. <a class="hint" href="https://en.wikipedia.org/wiki/Meta_element" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => 'Insira a descrição:',
	'sample_desc' => 'Artigos, guias e plug-ins relacionados ao FlatPress',
	'input_keywords' => 'Insira as palavras-chave:',
	'sample_keywords' => 'flatpress, artigos sobre flatpress, guias flatpress, flatpress plugins',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=pt-br#noindex" target="_blank" title="Leia mais sobre o noindex">Não permitir a indexação</a>:',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=pt-br#nofollow" target="_blank" title="Leia mais sobre o nofollow">Não permitir o seguinte</a>:',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=pt-br#noarchive" target="_blank" title="Leia mais sobre o noarchive">Não permitir o arquivamento</a>:',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=pt-br#nosnippet" target="_blank" title="Leia mais sobre o nosnippet">Não permitir snippets</a>:'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'Início',
	'blog_home' => 'Página inicial do blog',
	'blog_page' => 'Blog',
	'archive' => 'Arquivo',
	'category' => 'Categoria',
	'tag' => 'Tag',
	'contact' => 'Entre em contato conosco',
	'comments' => 'Comentários',
	'pagenum' => 'Página #',
	'introduction' => 'Introdução'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => 'O arquivo <code>robots.txt</code> foi salvo com êxito',
	-1 => 'Não foi possível salvar o arquivo <code>robots.txt</code> (não há autorização de gravação em <code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code>)?',

	2 => 'As configurações foram salvas com êxito',
	-2 => 'Ocorreu um erro ao salvar'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'Nenhum <code>robots.txt</code> está disponível ou nenhum <code>robots.txt</code> pode ser criado no diretório raiz do documento HTTP.'
);
?>

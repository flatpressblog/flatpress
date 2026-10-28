<?php
$lang ['admin'] ['plugin'] ['submenu'] ['bbcode'] = 'BBCode';
$lang ['admin'] ['plugin'] ['bbcode'] = array(
	'head' => 'Configuração do BBCode',
	'desc1' => 'Esse plug-in permite o uso de marcação <a href="https://wiki.flatpress.org/' . //
		'doc:techfaq#bbcode" class="hint" target="_blank">BBCode</a>.',

	'options' => 'Opções',

	'editing' => 'Editar',
	'allow_html' => 'HTML embutido',
	'allow_html_long' => 'Ativar o uso de HTML junto com o BBCode',
	'toolbar' => 'Barra de ferramentas',
	'toolbar_long' => 'Ativar a barra de ferramentas do editor',

	'other' => 'Outras opções',
	'comments' => 'Comentários',
	'comments_long' => 'Permitir BBCode nos comentários',
	'url_new_window' => 'Abrir links em uma nova aba ou janela',
	'url_new_window_long' => 'Por padrão, a opção está desativada e os links abrem na mesma janela. Um destino definido explicitamente no BBCode tem prioridade.',
	'urlmaxlen' => 'comprimento máximo do URL',
	'urlmaxlen_long_pre' => 'Encurte URLs maiores que ',
	'urlmaxlen_long_post' => ' caracteres',

	'attachsdir' => 'Downloads de arquivos',
	'attachsdir_long' => 'Não exiba o diretório de upload (fp-content/attachs/) no URL.',

	'submit' => 'Salvar configuração',
	'msgs' => array(
		1 => 'Configuração do BBCode salva com sucesso',
		-1 => 'Configuração do BBCode não salva'
	),

	'editor' => array(
		'formatting' => 'Formatação',
		'textarea' => 'Área de texto: ',
		'expand' => 'Expandir',
		'expandtitle' => 'Expandir altura da área de texto',
		'reduce' => 'Reduzir',
		'reducetitle' => 'Reduzir altura da área de texto',
		'urltitle' => 'URL/ Link (Ctrl+K / ⌘K)',
		'mailtitle' => 'Endereço de e-mail',
		'boldtitle' => 'Negrito (Ctrl+B / ⌘B)',
		'italictitle' => 'Itálico (Ctrl+I / ⌘I)',
		'headlinetitle' => 'Título',
		'fonttitle' => 'Fonte',
		'underlinetitle' => 'Sublinhado (Ctrl+U / ⌘U)',
		'crossouttitle' => 'Riscado (Ctrl+Shift+X / ⌘⇧X)',
		'unorderedlisttitle' => 'Lista não classificada (Ctrl+Shift+8 / ⌘⇧8)',
		'orderedlisttitle' => 'Lista ordenada (Ctrl+Shift+7 / ⌘⇧7)',
		'quotetitle' => 'Citação',
		'codetitle' => 'Código',
		'htmltitle' => 'Inserir como código HTML',
		'help' => 'Ajuda do BBCode',
		'file' => 'Arquivo: ',
		'image' => 'Imagem: ',
		'gallery' => 'Galeria: ',
		'selection' => '-- Seleção --'
	)
);

$lang ['plugin'] ['bbcode'] = array (
	'go_to' => 'Ir para',

	// Filewrapper get.php
	'error_403' => 'Erro 403',
	'not_send' => 'O arquivo solicitado não pode ser enviado.',
	'error_404' => 'Erro 404',
	'not_found' => 'O arquivo solicitado não pôde ser encontrado.',
	'file' => 'Arquivo',
	'report_error_1' => '',
	'report_error_2' => 'Relatar um erro',
	'blog_search_1' => '',
	'blog_search_2' => 'pesquisar no blog',
	'start_page_1' => 'ou voltar para a',
	'start_page_2' => 'página inicial'
);
?>
<?php

$lang['admin']['plugin']['submenu']['bbcode'] = 'BBCode';
$lang['admin']['plugin']['bbcode'] = array(
	'head' => 'BBCode Configuração',
	'desc1' => 'Este plugin permite o uso de <a href="http://www.phpbb.com/'.
		'phpBB/faq.php?mode=bbcode">BBCode</a> markup e fornece '.
		'integração automática com o lightbox (quando também ativado).',
	
	'options' => 'Opções',

	'editing'	=> 'Editing',
	'allow_html'=> 'HTML embutido',
	'allow_html_long' => 'Ativar o uso de HTML junto com o BBCode',
	'toolbar' => 'Barra de ferramentas',
	'toolbar_long' => 'Ativar a barra de ferramentas do editor.',

	'other'	=>	'Outras opções',
	'comments' => 'Comentários',
	'comments_long' => 'Permitir BBCode nos comentários',
	'urlmaxlen' => 'comprimento máximo do URL',
	'urlmaxlen_long_pre' => 'Encurte URLs maiores que ',
	'urlmaxlen_long_post'=>' caracteres.',
	'submit' => 'Salvar configuração',
	'msgs' => array(
		1 => 'Configuração do BBCode salva com sucesso',
		-1 => 'Configuração do BBCode não salva'
	),

	'editor' => array(
		'formatting'     => 'Formatação',
		'textarea'       => 'Área de texto: ',
		'expand'         => 'Expandir',
		'expandtitle'    => 'Expandir altura da área de texto',
		'reduce'         => 'Reduzir',
		'reducetitle'    => 'Reduzir altura da área de texto',
		// note: accesskeys are not internationalized...
		// btw. why not :-D
		'bold'           => 'N',
		'boldtitle'      => 'Negrito',
		'italic'         => 'I',
		'italictitle'    => 'Itálico',
		'underline'      => 'S',
		'underlinetitle' => 'Sublinhado',
		'quote'          => 'Citação',
		'quotetitle'     => 'Citação',
		'code'           => 'Código',
		'codetitle'      => 'Código',
		'help'           => 'Ajuda do BBCode',
		// currently not used
		'status'         => 'Barra de status',
		'statusbar'      => 'Moda normal. Pressionar &lt;Esc&gt; para mudar o modo de edição'
	)
);

?>

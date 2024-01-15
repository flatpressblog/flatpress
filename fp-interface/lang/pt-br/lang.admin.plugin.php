<?php
$lang ['admin'] ['plugin'] ['submenu'] = array (
	'default' => 'Administre seus Plugins'
);

/* main plugin panel */
$lang ['admin'] ['plugin'] ['default'] = array(
	'head' => 'Administre seus Plugins',
	'enable' => 'Ativar',
	'disable' => 'Desativar',
	'descr' => 'Um <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="O que é um plugin?">Plugin</a> é um componente que pode expandir os recursos do FlatPress.</p>' . //
		'<p>Você pode instalar plugins fazendo o upload deles na sua pasta de <code>fp-plugins/</code>.</p><p>Neste painel você pode ativar e desativar os plugins.',
	'name' => 'Nome',
	'description' => 'Descrição',
	'author' => 'Autor',
	'version' => 'Versão',
	'action' => 'Ação'
);

$lang ['admin'] ['plugin'] ['default'] ['msgs'] = array(
	1 => 'Configuração salva',
	-1 => 'Ocorreu um erro ao tentar salvar. Isso pode acontecer por vários motivos: talvez seu arquivo contenha erros de sintaxe.'
);

/* system errors */
$lang ['admin'] ['plugin'] ['errors'] = array(
	'head' => 'Os seguintes erros foram encontrados ao carregar plugins:',
	'notfound' => 'O plugin não foi encontrado. Ignorado.',
	'generic' => 'Número do erro: %d'
);
?>

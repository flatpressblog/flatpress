<?php
//Terminado 15 de fevereiro de 2020.

	$lang['admin']['plugin']['submenu'] = array (
		'default'		=> 'Administre Plugins'
	);
	
	/* main plugin panel */

	$lang['admin']['plugin']['default'] = array(
	
		'head'		=> 'Administre Plugins',
		'enable'	=> 'Ative',
		'disable'	=> 'Desative',
		'descr'		=> 'Um <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="O que é um plugin?">'.
						'Plugin</a> é um componente que pode expandir os recursos do FlatPress.</p>'.
						'<p>Você pode instalar plug-ins fazendo o upload deles na sua pasta de <code>fp-plugins/</code>.</p>'.
						'<p>Neste painel pode ativar e desativar os plugins.',
		'name'		=> 'Nome',
		'description'	=> 'Descrição',
		'author'	=> 'Autor',
		'version'	=> 'Versão',
		'action'	=> 'Ação',
	);
	
	$lang['admin']['plugin']['default']['msgs'] = array(
		1	=> 'Configuração salva',
		-1	=> 'Ocorreu um erro ao tentar salvar. Isso pode acontecer por vários motivos: talvez seu arquivo contenha erros de sintaxe.',
	);
	
	/* system errors */
	
	$lang['admin']['plugin']['errors'] = array(
		'head'		=> 'Os seguintes erros foram encontrados ao carregar plug-ins:',
		'notfound'	=> 'O plugin não foi encontrado. Ignorado.',
		'generic'	=> 'Número do erro: %d',
	);
	
?>

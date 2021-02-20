<?php
	$lang['plugin']['prettyurls']['errors'] = array (
		-2	=> 'Não consigo encontrar ou criar um arquivo <code>.htaccess</code>  na sua raiz. Pode ser que PrettyURLs não funciona corretamente.  Consulte o painel de configuração.'
	);
	
	$lang['admin']['plugin']['submenu']['prettyurls'] = 'Config de PrettyURLs';
	$lang['admin']['plugin']['prettyurls'] = array(
		'head'		=> 'PrettyURLs Configuração',
		'htaccess'	=> '.htaccess',
		'description'=>'Este editor bruto permite editar o seu '.
						'<code>.htaccess</code>.',
		'cantsave'	=> 'Você não pode editar este arquivo, porque ele não é <strong>gravável</strong>. Você pode conceder permissão de gravação ou copiar e colar em um arquivo e fazer o upload.',
		'mode'		=> 'Modo',
		'auto'		=> 'Automático',
			'autodescr'	=> 'Tente adivinhar a melhor opção para mim.',
		'pathinfo'	=> 'Informações do caminho',
			'pathinfodescr' => 'e.g. /index.php/2011/01/01/hello-world/',
		'httpget'	=> 'HTTP Get',
			'httpgetdescr'=> 'e.g. /?u=/2011/01/01/hello-world/',
		'pretty'	=> 'Pretty',
			'prettydescr'=> 'e.g. /2011/01/01/hello-world/',

		'saveopt' 	=> 'Salvar configurações',

		'submit'	=> 'Salvar .htaccess'
	);
	$lang['admin']['plugin']['prettyurls']['msgs'] = array(
		1		=> '.htaccess salvo com sucesso',
		-1		=> '.htaccess não pôde ser salvo (você tem permissões de gravação em <code>'. BLOG_ROOT .'</code>)?',

		2		=> 'Opções salvas com sucesso',
		-2		=> 'Ocorreu um erro ao tentar salvar as configurações',
	);
	
?>

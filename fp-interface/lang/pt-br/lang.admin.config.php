<?php
//Terminado 15 de fevereiro de 2020.

	$lang['admin']['config']['default'] = 
	array(
		'head'		=> 'Opções',
		'descr'		=> 'Personalize e configure sua instalação do FlatPress.',
		'submit'		=> 'Salve alterações',
		
		'sysfset'		=> 'Informações Gerais do Sistema',
		'syswarning'	=> '<big>Aviso!</big> Essas informações são críticas e precisam estar corretas ou o FlatPress (provavelmente) não funcionará corretamente.',
		'blog_root'		=> '<strong>Caminho absoluto para o Flatpress</strong>. Nota: Geralmente, você não precisará editar isso. De qualquer forma, tenha cuidado, porque não podemos verificar se está correto ou não.',
		'www'		=>'<strong>Raiz do blog</strong>. URL para o seu blog, completo com subdiretórios. <br/> por exemplo: http://www.mydomain.com/flatpress/ (barra final necessária),',
		
		// ------
		
		'gensetts'		=> 'Configurações em geral',
		'blogtitle'		=> 'Título do blog',
		'blogsubtitle'		=> 'Subtítulo do blog',
		'blogfooter'		=> 'Rodapé do blog',
		'blogauthor'		=> 'Autor do blog',
		'startpage'			=> 'A página principal deste site é',
		'stdstartpage'		=> 'meu blog (padrão)',
		'blogurl'			=> 'URL do blog',
		'blogemail'			=> 'Email do blog',
		'notifications'		=> 'Notificações',
		'mailnotify'		=> 'Ative notificações por email para comentários.',
		'blogmaxentries'	=> 'Número de posts por página',
		'langchoice'		=> 'Idioma',

		'intsetts'		=> 'Configurações internacionais',
		'utctime'		=> '<acronym title="Universal Coordinated Time">UTC</acronym> hora está',
		'timeoffset'		=> 'Diferença de hora de UTC hora',
		'hours'			=> 'horas',
		'timeformat'		=> 'Formato padrão para a hora',
		'dateformat'		=> 'Formato padrão para a data',
		'dateformatshort'	=> 'Formato padrão para a data (curta)',
		'output'		=> 'Resultado',
		'charset'		=> 'Repertório de caracteres',
		'charsettip'	=> 'O repertório de caracteres em que você escreve seu blog. (UTF-8 é '.
						'<a href="http://wiki.flatpress.org/doc:charsets">recomendado.</a>)'
		);
		
	$lang['admin']['config']['default']['msgs'] = 
	array(
		1		=> 'A configuração foi salva com sucesso.',
		-1		=> 'Ocorreu um erro ao tentar salvar a configuração.',
		
		);
			
	$lang['admin']['config']['default']['error'] = 
	array(
		'www' 		=>	'A raiz do blog deve ser um URL válido.',
		'title'		=>	'Você deve especificar um título.',
		'email'		=>	'Você não inseriu um email válido.',
		'maxentries'	=>	'Você não inseriu um número válido de entradas.',
		'timeoffset'	=>	'Você não inseriu um deslocamento de hora válido. '.
						'Você pode usar ponto flutuante (e.g. 2h30" => 2.5).',
		'timeformat'	=>	'Você deve inserir uma string de formato para a hora.',
		'dateformat'	=>	'Você deve inserir uma string de formato para a data.',
		'dateformatshort'=>	'Você deve inserir uma string de formato para a data (curta).',
		'charset'	=>	'Você deve inserir um repertório de caracteres.',
		'lang'		=>	'O idioma que você escolheu não está disponível.'
		);		
			
		
?>

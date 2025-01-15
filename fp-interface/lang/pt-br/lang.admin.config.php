<?php
$lang ['admin'] ['config'] ['default'] = array(
	'head' => 'Opções',
	'descr' => 'Personalize e configure sua instalação do FlatPress.',
	'submit' => 'Salve alterações',

	'sysfset' => 'Informações Gerais do Sistema',
	'syswarning' => '<big>Aviso!</big> Essas informações são críticas e precisam estar corretas ou o FlatPress (provavelmente) não funcionará corretamente.',
	'blog_root' => '<strong>Caminho absoluto para o FlatPress</strong>. Nota: ' . //
		'Geralmente, você não precisará editar isso. De qualquer forma, tenha cuidado, porque não podemos verificar se está correto ou não.',
	'www' => '<strong>Raiz do blog</strong>. URL para o seu blog, completo com subdiretórios. <br>' . //
		'por exemplo: http://www.mydomain.com/flatpress/ (barra final necessária),',

	// ------
	'gensetts' => 'Configurações em geral',
	'adminname' => 'Nome do administrador',
	'adminpassword' => 'Nova senha',
	'adminpasswordconfirm' => 'Repetir senha',
	'blogtitle' => 'Título do blog',
	'blogsubtitle' => 'Subtítulo do blog',
	'blogfooter' => 'Rodapé do blog',
	'blogauthor' => 'Autor do blog',
	'startpage' => 'A página principal deste site é',
	'stdstartpage' => 'meu blog (padrão)',
	'blogurl' => 'URL do blog',
	'blogemail' => 'Email do blog',
	'notifications' => 'Notificações',
	'mailnotify' => 'Ative notificações por email para comentários.',
	'blogmaxentries' => 'Número de posts por página',
	'langchoice' => 'Idioma',

	'intsetts' => 'Configurações internacionais',
	'utctime' => '<abbr title="Universal Coordinated Time">UTC</abbr> hora é:',
	'timeoffset' => 'Diferença em horas com relação ao UTC:',
	'hours' => 'horas',
	'timeformat' => 'Formato padrão para a hora',
	'dateformat' => 'Formato padrão para a data',
	'dateformatshort' => 'Formato padrão para a data (curta)',
	'output' => 'Resultado',
	'charset' => 'Conjunto de caracteres',
	'charsettip' => 'O conjunto de caracteres em que você escreve seu blog (UTF-8 é ' . //
		'<a class="hint" href="https://wiki.flatpress.org/doc:techfaq#character_encoding" target="_blank" title="Quais padrões de codificação de caracteres são compatíveis com o FlatPress?">recomendado</a>)'
);

$lang ['admin'] ['config'] ['default'] ['msgs'] = array(
	1 => 'A configuração foi salva com sucesso.',
	2 => 'O administrador foi alterado. Agora você será desconectado.',
	-1 => 'Ocorreu um erro ao tentar salvar a configuração.'
);

$lang ['admin'] ['config'] ['default'] ['error'] = array(
	'www' => 'A raiz do blog deve ser uma URL válida.',
	'title' => 'Você deve especificar um título.',
	'email' => 'Você não inseriu um email válido.',
	'maxentries' => 'Você não inseriu um número válido de entradas.',
	'timeoffset' => 'Você não inseriu um range de hora válido. Você pode usar ponto flutuante (e.g. 2h30" => 2.5).',
	'timeformat' => 'Você deve inserir uma string de formato para a hora.',
	'dateformat' => 'Você deve inserir uma string de formato para a data.',
	'dateformatshort' => 'Você deve inserir uma string de formato para a data (curta).',
	'charset' => 'Você deve inserir um conjunto de caracteres.',
	'lang' => 'O idioma que você escolheu não está disponível.',
	'admin' => 'O nome do administrador só pode conter letras, números e um sublinhado.',
	'password' => 'A senha deve conter pelo menos 6 caracteres e não deve conter espaços.',
	'confirm_password' => 'As senhas não são iguais.'
);
?>
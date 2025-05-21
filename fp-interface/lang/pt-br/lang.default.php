<?php
//Terminado 15 de fevereiro de 2020.

	$lang = array();

	$lang['main'] = array(
		
		'nextpage'	=> 'Próxima página &raquo;',
		'prevpage'	=> '&laquo; Página anterior',
		'entry'      	=> 'Post',
		'static'     	=> 'Página estática',
		'comment'    	=> 'Comentário',
		'preview'    	=> 'edite/visualize',
		
		'filed_under'	=> 'Arquivado em ',	
		
		'add_entry'  	=> 'Adicione post',
		'add_comment'  	=> 'Adicione comentário',
		'add_static'  	=> 'Adicione página estática',
		
		'btn_edit'     	=> 'Edite',
		'btn_delete'   	=> 'Exclua',
		
		'nocomments'	=> 'Adicione comentário',
		'comment'	=> '1 comentário',
		'comments'	=> 'comentários',
		
	);
	
	$lang['search'] = array(
		
		'head'		=> 'Pesquisa',
		'fset1'		=> 'Inserir critérios de pesquisa',
		'keywords'	=> 'Frase',
		'onlytitles'	=> 'Apenas títulos',
		'fulltext'	=> 'Texto completo',
		
		'fset2'		=> 'Data',
		'datedescr'	=> 'Você pode vincular sua pesquisa a uma data específica. Você pode selecionar um ano, um ano e um mês ou uma data completa. '.
					'Deixe em branco para pesquisar em todo o banco de dados.',
		
		'fset3' 	=> 'Pesquisar nas categorias.',
		'catdescr'	=> 'Não selecione nenhuma categoria para pesquisar todas',
		
		'fset4'		=> 'Iniciar pesquisa.',
		'submit'	=> 'Pesquise',
		
		'headres'	=> 'Resultados da pesquisa',
		'descrres'	=> 'A pesquisa de <strong>%s</strong> retornou os seguintes resultados:',
		'descrnores'	=> 'A pesquisa de <strong>%s</strong> não retornou resultados.',
		
		'moreopts'	=> 'Mais opções',
		
		
		'searchag'	=> 'Pesquise de novo',
		
	);
	
	$lang['search']['error'] = array(
	
		'keywords'	=> 'Você deve especificar pelo menos uma palavra-chave.'
	
	);
	
	
	
	
	
	$lang['entry'] = array();
	$lang['entry']['flags'] = array();
	
	$lang['entry']['flags']['long'] = array(
		'draft' => '<strong>Rascunho</strong>: oculta, aguardando publicação.',
		//'static' => '<strong>Static entry</strong>: normally hidden, to reach the entry put ?page=title-of-the-entry in url (experimental)',
		'commslock' => '<strong>Comentários bloqueados</strong>: comentários não permitidos para este item.'
	);
	
	$lang['entry']['flags']['short'] = array(
		'draft' => 'Rascunho',
		//'static' => 'Static',
		'commslock' => 'Comentários bloqueados'
	);

	$lang['404error'] = array(
		'subject'	=> 'Não encontrado',
		'content'	=> '<p>Desculpe, não conseguimos encontrar a página solicitada.</p>'
	);
		
	// Login
	$lang['login'] = array(
		
		'head'		=> 'Login',
		'fieldset1'	=> 'Insira seu nome de usuário e senha.',
		'user'		=> 'Nome de usuário:',
		'pass'		=> 'Senha:',
		'fieldset2'	=> 'Faça login',
		'submit'	=> 'Login',
		'forgot'	=> 'Senha perdida'
	);
		
	$lang['login']['success'] = array(
		'success'	=> 'Você está logado.',
		'logout'	=> 'Você está desconectado.',
		'redirect'	=> 'Você será redirecionado em 5 segundos.',
		'opt1'		=> 'Volte para Início',
		'opt2'		=> 'Ir para o Painel de Controle.',
		'opt3'		=> 'Adicione novo item.'
	);
	
	$lang['login']['error'] = array(
		'user'		=> 'Você deve inserir um nome de usuário.',
		'pass'		=> 'Você deve inserir uma senha.',
		'match'		=> 'Você deve inserir um nome de usuário incorreta.'
	);
	
	
	$lang['comments'] = array(
		'head'		=> 'Adicione comentário.',
		'descr'		=> 'Preencha o formulário abaixo para adicionar seus próprios comentários.',
		'fieldset1'	=> 'Dados do usário',
		'name'		=> 'Nome (*)',
		'email'		=> 'Email:',
		'www'		=> 'Website:',
		'cookie'	=> 'Lembre de mim',
		'fieldset2'	=> 'Adicione seu comentário',
		'comment'	=> 'Comentário (*):',
		'fieldset3'	=> 'Envie',
		'submit'	=> 'Comente',
		'reset'		=> 'Reinicialize',
		'success'	=> 'Seu comentário foi adicionado com sucesso.',
		'nocomments'	=> 'Esta entrada ainda não foi comentada.',
		'commslock'	=> 'Os comentários foram desativados para esta entrada.',
	);
	
	$lang['comments']['error'] = array(
		'name'		=> 'Você deve inserir um nome.',
		'email'		=> 'Você deve inserir um email válido.',
		'www'		=> 'Você deve inserir um URL válido.',
		'comment'	=> 'Você deve inserir um comentário.',
	);
	
	$lang['date']['month'] = array(
		
		'Janeiro',
		'Fevereiro',
		'Março',
		'Abril',
		'Maio',
		'Junho',
		'Julho',
		'Agosto',
		'Setembro',
		'Outubro',
		'Novembro',
		'Dezembro'
		
	);

	$lang['date']['month_abbr'] = array(
		
		'Jan',
		'Fev',
		'Mar',
		'Abr',
		'Mai',
		'Jun',
		'Jul',
		'Ago',
		'Sep',
		'Out',
		'Nov',
		'Dez'
		
	);

	$lang['date']['weekday'] = array(
		
		'Domingo',
		'Segunda-feira',
		'Terça-feira',
		'Quarta-feira',
		'Quinta-feira',
		'Sexta-feira',
		'Sábado',
		
	);

	$lang['date']['weekday_abbr'] = array(
		
		'Dom',
		'Seg',
		'Ter',
		'Qua',
		'Qui',
		'Sex',
		'Sab',
		
	);



?>

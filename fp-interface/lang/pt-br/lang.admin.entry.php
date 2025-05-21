<?php
//Terminado 15 de fevereiro de 2020.

	$lang['admin']['entry']['submenu'] = 
	array (
		'list'		=> 'Administre posts',
		'write'		=> 'Crie post',
		'cats'		=> 'Administre categorias'
	);


	/* default action */
	
	$lang['admin']['entry']['list'] = 
	array(
		'head'		=> 'Administre posts',
		'descr'		=> 'Selecione um post para editar ou <a href="admin.php?p=entry&amp;action=write">adicione um novo.</a>'.
					'<br /><a href="admin.php?p=entry&amp;action=cats">Edite os categorias.</a>',
		'filter'	=> 'Filtro: ',
		'nofilter'	=> 'Mostre todo',
		'filterbtn'	=> 'Aplique filtro',
		'sel'		=> 'Selecione', // checkbox
		'date'		=> 'Data',
		'title'		=> 'Título',
		'author'	=> 'Autor',
		'comms'		=> '#Comentários', // comments
		'action'	=> 'Ação',
		'act_del'	=> 'Exclua',
		'act_view'	=> 'Visualize',
		'act_edit'	=> 'Edite'
	);
	
	/* write action */
	$lang['admin']['entry']['write'] = 
	array(
		'head'		=> 'Crie post.',
		'descr'		=> 'Edite o formulário para criar o post',
		'uploader'	=> 'Uploader',
		'fieldset1'	=> 'Edite',
		'subject'	=> 'Assunto (*):',
		'content'	=> 'Conteúdo (*):',
		'fieldset2'	=> 'Crie',
		'submit'	=> 'Publique',
		'preview'	=> 'Visualize',
		'savecontinue'	=> 'Salve e continue',
		'categories'	=> 'Categorias',
		'nocategories'	=> 'Nenhuma categoria definida.<a href="admin.php?p=entry&amp;action=cats">Crie o seu próprio '. 
					'categories</a> do painel de posts principal. '.
					'<a href="#save">Salve</a> seu post antes.',
		'saveopts'	=> 'Opções de salvar',
		'success'	=> 'Seu post foi publicada com sucesso.',
		'otheropts'	=> 'Outras opções',
		'commmsg'	=> 'Administre comentários para este post.',
		'delmsg'	=> 'Exclua este post.',
		//'back'		=> 'Back discarding changes',
	);
	

	$lang['admin']['entry']['list']['msgs'] = array(
		1	=> 'O post foi salva com sucesso.',
		-1	=> 'Ocorreu um erro ao tentar salvar o post.',
		2	=> 'O post foi excluída com sucesso.',
		-2	=>	 'Ocorreu um erro ao tentar excluir o post.',
	);

	
	$lang['admin']['entry']['write']['error'] = array(
		'subject'	=> 'Você deve inserir um assunto.',
		'content'	=> 'Você deve inserir conteúdo.',
	);
	
	$lang['admin']['entry']['write']['msgs'] = array(
		1	=> 'O post foi salva com sucesso.',
		-1	=> 'Ocorreu um erro: seu post não pôde ser salva com sucesso.',
		-2	=> 'Ocorreu um erro: seu post não foi salva; índice pode ter se tornado corrompido.',
		-3	=> 'Ocorreu um erro: seu post foi salva como rascunho.',
		-4	=> 'Ocorreu um erro: seu post foi salva como rascunho; índice pode ter se tornado corrompido.',
		'draft'=> 'Você está editando um <strong>rascunho</strong> de um post.'
	);

	
	/* comments */
	
	$lang['admin']['entry']['commentlist'] = 
	array(
		'head'		=> "Comentários para o post", 
		'descr'		=> 'Selecione um comentário para excluir.',
		'sel'		=> 'Sel',
		'content'	=> 'Conteúdo',
		'date'		=> 'Data',
		'author'	=> 'Autor',
		'email'		=> 'Email',
		'ip'		=> 'IP',
		'actions'	=> 'Ações',
		'act_edit'	=> 'Edite',
		'act_del'	=> 'Exclua',
		'act_del_confirm' => 'Com certeza quer excluir este comentário?',
		'nocomments'	=> 'Este post ainda não foi comentado.',
		
	
	);

	$lang['admin']['entry']['commentlist']['msgs'] =
	array(
		1	=> 'O comentário foi excluído com sucesso.',
		-1	=> 'Ocorreu um erro ao tentar excluir o comentário.',
		
	);

	$lang['admin']['entry']['commedit'] = 
	array(
		'head'		=> "Edite o comentário para o post.", 
		'content'	=> 'Conteúdo',
		'date'		=> 'Data',
		'author'	=> 'Autor',
		'www'		=> 'Website',
		'email'		=> 'Email',
		'ip'		=> 'IP',
		'loggedin'	=> 'Usário cadestrado',
		'submit'	=> 'Salve'
		
	
	);

	$lang['admin']['entry']['commedit']['msgs'] =
	array(
		1	=> 'O comentário foi editado.',
		-1	=> 'Ocorreu um erro ao tentar editar o comentário.',
	);
	
	/* delete action */
	
	$lang['admin']['entry']['delete'] = 
	array(
		'head'		=> 'Exclua o post.', 
		'descr'		=> 'Você está prestes a excluir o seguinte post:',
		'preview'	=> 'Visualize',
		'confirm'	=> 'Com certeza quer continuar?',
		'fset'		=> 'Exclua',
		'ok'		=> 'Sim, Exclua este post.',
		'cancel'	=> 'Não, leve-me de volta ao painel.',
		'err'		=> 'O post especificado não existe.',
	
	);
	
	/* category mgmt */
	
	$lang['admin']['entry']['cats'] =
	array(
		'head'		=> 'Edite categorias',
		'descr'		=> '<p>Use o formulário abaixo para adicionar e editar suas categorias. </p><p>Cada item de categoria deve estar neste formato "nome da categoria: <em>número de id</em>". Recue itens com traços para criar hierarquias.</p>
		
	<p>Examplo:</p>
	<pre>
Geral: 1
Notícias: 2
--Anúncios: 3
--Eventos: 4
---- Misc: 5
Tecnologia: 6
	</pre>',
		'clear'		=> 'Exclua todos os dados de categorias.',
	
		'fset1'		=> 'Editor',
		'fset2'		=> 'Aplique alterações',
		'submit'	=> 'Salve'
	);
	
	$lang['admin']['entry']['cats']['msgs'] = array(
		
		1	=> 'Categorias salvas',
		-1	=> 'Ocorreu um erro ao tentar salvar categorias.',
		2	=> 'Categorias foram limpadas.',
		-2	=> 'Ocorreu um erro ao tentar limpar categorias.',
		-3 	=> 'Os IDs da categoria devem ser apenas positivos. (0 não é permitido.)'

	);
	
	
		
?>

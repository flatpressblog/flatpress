<?php
//Terminado 15 de fevereiro de 2020.

	$lang['admin']['static']['submenu'] = array(
		'list'		=> 'Administre páginas estáveis',
		'write'		=> 'Crie uma página estável'
	);

	
	/* main panel */
		
	$lang['admin']['static']['list'] = array(
	
		'head'		=> 'Páginas estáveis',
		'descr'		=> 'Por favor, selecione uma página para editar ou <a href="admin.php?p=static&amp;action=write">crie nova</a>.',
	
		'sel'		=> 'Sel', // checkbox
		'date'		=> 'Data',
		'name'		=> 'Página',
		'title'		=> 'Título',
		'author'	=> 'Autor',
		
		'action'	=> 'Ação',
		'act_view'	=> 'Visualize',
		'act_del'	=> 'Exclua',
		'act_edit'	=> 'Edite'		
	);
	
	$lang['admin']['static']['list']['msgs'] = array(
		1	=> 'A página foi salva com sucesso.',
		-1	=> 'Ocorreu um erro ao tentar salvar a página.',
		2	=> 'Página foi excluída com sucesso.',
		-2	=> 'Ocorreu um erro ao tentar excluir a página.',
	);

	/* write panel */

	$lang['admin']['static']['write'] = 
	array(
		'head'		=> 'Publique página estável',
		'descr'		=> 'Edite o formulário para publicar a página.',
		'fieldset1'	=> 'Edite',
		'subject'	=> 'Assunto (*):',
		'content'	=> 'Conteúdo (*):',
		'fieldset2'	=> 'Publique',
		'pagename'	=> 'Nome da página (*):',
		'submit'	=> 'Publique',
		'preview'	=> 'Visualize',

		'delfset'	=> 'Exclua',
		'deletemsg'	=> 'Exclua esta página',
		'del'		=> 'Exclua',
		'success'	=> 'Sua página foi publicada com sucesso.',
		'otheropts'	=> 'Outras opções',
	);
	
	$lang['admin']['static']['write']['error'] = array(
		'subject'	=> 'Você deve incluir um assunto.',
		'content'	=> 'Você deve incluir conteúdo.',
		'id'		=> 'Você deve incluir um id válido.'
	);
	
	
	/* delete action */	
	$lang['admin']['static']['delete'] = array(
		'head'		=> "Exclua página", 
		'descr'		=> 'Você está prestes a excluir a seguinte página:',
		'preview'	=> 'Visualize',
		'confirm'	=> 'Com certeza quer continuar?',
		'fset'		=> 'Exclua',
		'ok'		=> 'Sim, exclua esta página.',
		'cancel'	=> 'Não, leve-me de volta ao painel.',
		'err'		=> 'A página especificada não existe.',
	
	);
	
	
		
?>

<?php
$lang ['admin'] ['static'] ['submenu'] = array(
	'list' => 'Administre páginas estáticas',
	'write' => 'Crie uma página estática'
);

/* main panel */
$lang ['admin'] ['static'] ['list'] = array(
	'head' => 'Páginas estáticas',
	'descr' => 'Por favor, selecione uma página para editar ou <a href="admin.php?p=static&amp;action=write">crie nova</a>',

	'sel' => 'Sel', // checkbox
	'date' => 'Data',
	'name' => 'Página',
	'title' => 'Título',
	'author' => 'Autor',

	'action' => 'Ação',
	'act_view' => 'Visualize',
	'act_del' => 'Exclua',
	'act_edit' => 'Edite',

	'natural' => 'Classifique os títulos em ordem decrescente em vez de por data de criação.',
	'submit' => 'Reordenar nomes de páginas'
);

$lang ['admin'] ['static'] ['list'] ['msgs'] = array(
	1 => 'A página foi salva com sucesso.',
	-1 => 'Ocorreu um erro ao tentar salvar a página.',
	2 => 'Página foi excluída com sucesso.',
	-2 => 'Ocorreu um erro ao tentar excluir a página.'
);

/* write panel */
$lang ['admin'] ['static'] ['write'] = array(
	'head' => 'Publique página estática',
	'descr' => 'Edite o formulário para publicar a página.',
	'fieldset1' => 'Edite',
	'subject' => 'Assunto (*):',
	'content' => 'Conteúdo (*):',
	'fieldset2' => 'Publique',
	'pagename' => 'Nome da página (*):',
	'submit' => 'Publicar',
	'preview' => 'Visualizar',

	'delfset' => 'Exclua',
	'deletemsg' => 'Exclua esta página',
	'del' => 'Exclua',
	'success' => 'Sua página foi publicada com sucesso.',
	'otheropts' => 'Outras opções'
);

$lang ['admin'] ['static'] ['write'] ['error'] = array(
	'subject' => 'Você deve incluir um assunto.',
	'content' => 'Você deve incluir conteúdo.',
	'id' => 'Você deve incluir um id válido.'
);

/* delete action */	
$lang ['admin'] ['static'] ['delete'] = array(
	'head' => 'Exclua a página', 
	'descr' => 'Você está prestes a excluir a seguinte página:',
	'preview' => 'Visualizar',
	'confirm' => 'Com certeza quer continuar?',
	'fset' => 'Excluir',
	'ok' => 'Sim, exclua esta página.',
	'cancel' => 'Não, leve-me de volta ao painel.',
	'err' => 'A página especificada não existe.'
);
?>

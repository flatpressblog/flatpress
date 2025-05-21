<?php

$lang['plugin']['qspam'] = array(
	'error' => 'ERRO: O comentário continha palavras proibidas.'
);

$lang['admin']['plugin']['submenu']['qspam'] = 'QuickSpamFilter';
$lang['admin']['plugin']['qspam'] = array(
	'head' => 'QuickSpam Configuração',
	'desc1' => 'Não permita comentários contendo estas palavras (escreva um por linha):',
	'desc2' => '<strong>Aviso:</strong> Um comentário não será permitido, mesmo quando uma palavra fizer parte de outra. 
	
	(e.g. "arte" também corresponde "p<em>arte</em>" too)',
	'options' => 'Outras opções',
	'desc3' => 'Contagem de palavras não permitidas',
	'desc3pre' => 'Bloquear comentários contendo mais de ',
	'desc3post' => ' palavras não permitidas.',
	'submit' => 'Salvar configuração',
	'msgs' => array(
		1 => 'Palavras não permitidas salvas com sucesso',
		-1 => 'Palavras não permitidas não salvas'
	)
);

?>

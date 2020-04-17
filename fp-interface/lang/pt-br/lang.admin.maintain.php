<?php
//Terminado 15 de fevereiro de 2020.
	
	$lang['admin']['panel']['maintain'] = 'Manutenção';

	$lang['admin']['maintain']['default'] = array(
		'head'		=> 'Manutenção',
		'descr'		=> 'Venha aqui quando achar que algo quebrou e talvez aqui encontre uma solução. No entanto, não há garantias!',
		'opt0'		=> '&laquo; Voltar ao menu principal',
		'opt1'		=> 'Reconstrua índice',
		'opt2'		=> 'Limpe cache de tema e modelos',
		'opt3'		=> 'Restaure permissões de arquivo',
		'opt4'		=> 'Mostre informações sobre PHP',
		'opt5'		=> 'Procure atualizações',

		'chmod_info'	=> "As seguintes permissões de arquivo <strong>não puderam</strong> ser redefinido para 0777; provavelmente o proprietário do arquivo não é o mesmo do servidor da web. Geralmente você pode ignorar este aviso.",
		
	);
	
	$lang['admin']['maintain']['default']['msgs'] = array(
		1		=> 'Operação concluída'
	);
	
	$lang['admin']['maintain']['updates'] = array(
		'head'	=> 'Atualizações',
		'list'	=> '<ul>
		<li>Sua versão de FlatPress é <big>%s</big></li>
		<li>A última versão estável do FlatPress é <big><a href="%s">%s</a></big></li>
		<li>A última versão instável do FlatPress é <big><a href="%s">%s</a></big></li>
		</ul>',
		'notice'=>'Aviso:'
		
	);
	
	
	
	$lang['admin']['maintain']['updates']['msgs'] = array(
		1		=> 'Existem atualizações disponíveis!',
		2		=> 'Você já está atualizado.',
		-1		=> 'Não foi possível recuperar as atualizações.'
	);

?>

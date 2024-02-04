<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Manutenção';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Manutenção',
	'descr' => 'Venha aqui quando achar que algo quebrou e talvez encontre uma solução. No entanto, não há garantias!',
	'opt0' => '&laquo; Voltar ao menu principal',
	'opt1' => 'Reconstrua o índice',
	'opt2' => 'Limpe o cache de tema e modelos',
	'opt3' => 'Restaure as permissões de arquivos',
	'opt4' => 'Mostre as informações sobre o PHP',
	'opt5' => 'Procure atualizações',

	'chmod_info' => 'Se não for possível redefinir as permissões do arquivo para ' . decoct(FILE_PERMISSIONS) . ', o proprietário do arquivo provavelmente não é o mesmo que o proprietário do servidor Web.<br>' . //
		'Geralmente você pode ignorar este aviso.'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Operação concluída',
	-1 => 'Falha na operação'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Atualizações',
	'list' => '<ul>
		<li>Sua versão de FlatPress é <big>%s</big></li>
		<li>A última versão estável do FlatPress é <big><a href="%s">%s</a></big></li>
		<li>A última versão instável do FlatPress é <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Aviso:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'Existem atualizações disponíveis!',
	2 => 'O FlatPress já está atualizado.',
	-1 => 'Não foi possível recuperar as atualizações.'
);
?>

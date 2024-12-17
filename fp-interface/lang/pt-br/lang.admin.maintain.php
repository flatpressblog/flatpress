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

	'chmod_info' => 'Se as permissões <strong>não puderem</strong> ser redefinidas, o proprietário do arquivo/ diretório provavelmente não é o mesmo que o proprietário do servidor Web.<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>Autorizações</th>
					<th>' . FP_CONTENT . '</th>
					<th>Núcleo</th>
					<th>Todos os outros</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Arquivos</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>Diretórios</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => 'Todas as autorizações foram atualizadas com sucesso.',
	'opt3_error' => 'Erro ao definir as autorizações:'
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

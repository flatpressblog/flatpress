<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Manutenção';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Manutenção',
	'descr' => 'Venha aqui quando achar que algo quebrou e talvez encontre uma solução. No entanto, não há garantias!',
	'opt0' => '&laquo; Voltar ao menu principal',
	'opt1' => 'Reconstrua o índice',
	'opt2' => 'Limpe o cache de tema e modelos',
	'opt3' => 'Restaurar as autorizações para operação produtiva',
	'opt4' => 'Mostre as informações sobre o PHP',
	'opt5' => 'Procure atualizações',
	'opt6' => 'Status do cache APCu',

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

$lang ['admin'] ['maintain'] ['apcu'] = array(
	'head' => 'Cache APCu',
	'descr' => 'Visão geral do uso da memória compartilhada APCu e da eficiência do cache.',
	'status_heading' => 'Status heurístico',
	'status_good' => 'O cache parece ter o tamanho adequado para a carga de trabalho atual.',
	'status_bad' => 'Alta taxa de erros ou memória livre muito baixa: o cache APCu pode estar muito pequeno ou muito fragmentado.',
	'hit_rate' => 'Taxa de acertos',
	'free_mem' => 'Memória livre',
	'total_mem' => 'Memória compartilhada total',
	'used_mem' => 'Memória usada',
	'avail_mem' => 'Memória disponível',
	'memory_type' => 'Tipo de memória',
	'memory_type_unknown' => 'n/a',
	'num_slots' => 'Número de slots',
	'num_hits' => 'Número de acertos',
	'num_misses' => 'Número de erros',
	'cache_type' => 'Tipo de cache',
	'cache_user_only' => 'Cache de dados do usuário',
	'legend_good' => 'Verde: a configuração parece saudável (alta taxa de acertos, memória livre razoável).',
	'legend_bad' => 'Vermelho: cache sob pressão (muitas falhas ou quase nenhuma memória livre).',
	'no_apcu' => 'O APCu não parece estar habilitado neste servidor.',
	'back' => '&laquo; Voltar à manutenção',
	'clear_fp_button'=> 'Limpar entradas APCu do FlatPress',
	'clear_fp_confirm' => 'Deseja mesmo excluir todas as entradas APCu? Isso limpará os caches APCu do FlatPress.',
	'clear_fp_result'=> 'Excluídas %d entradas APCu.',
	'msgs' => array(
		1  => 'As entradas APCu do FlatPress foram limpas.',
		2  => 'Nenhuma entrada APCu foi encontrada.',
		-1 => 'O APCu não está disponível ou não pôde ser acessado; nada foi excluído.'
	)
);
?>

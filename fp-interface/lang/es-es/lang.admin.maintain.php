<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Mantención';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Mantención',
	'descr' => 'Consulta esta sección cuando creas que algo se ha estropeado y quizás encuentres aquí una solución. Sin embargo, esto podría no funcionar.',
	'opt0' => '&laquo; Regresar al menú principal',
	'opt1' => 'Reconstruir índice',
	'opt2' => 'Vaciar el caché de plantillas y temas',
	'opt3' => 'Restablecer las autorizaciones para el funcionamiento productivo',
	'opt4' => 'Mostrar información sobre PHP',
	'opt5' => 'Buscar actualizaciones',
	'opt6' => 'Estado de la caché APCu',

	'chmod_info' => 'Si los permisos <strong>no se pueden restablecer</strong>, es probable que el propietario del archivo/ directorio no sea el mismo que el propietario del servidor web.<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>Autorizaciones</th>
					<th>' . FP_CONTENT . '</th>
					<th>Núcleo</th>
					<th>Todos los demás</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Archivos</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>Directorios</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => 'Todas las autorizaciones se han actualizado correctamente.',
	'opt3_error' => 'Error al establecer las autorizaciones:'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Operación completada',
	-1 => 'Operación fallida'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Actualizaciones',
	'list' => '<ul>
		<li>Usted tiene la versión FlatPress <big>%s</big></li>
		<li>La última versión estable de FlatPress es <big><a href="%s">%s</a></big></li>
		<li>La última versión inestable de FlatPress es <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Aviso:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => '¡Hay actualizaciones disponibles!',
	2 => 'Su sistema ya está actualizado',
	-1 => 'No se encontraron actualizaciones'
);

$lang ['admin'] ['maintain'] ['apcu'] = array(
	'head' => 'Caché APCu',
	'descr' => 'Descripción general del uso de la memoria compartida APCu y la eficiencia de la caché.',
	'status_heading' => 'Estado heurístico',
	'status_good' => 'La caché parece tener un tamaño adecuado para la carga de trabajo actual.',
	'status_bad' => 'Alta tasa de fallos o muy poca memoria libre: la caché APCu podría ser demasiado pequeña o estar muy fragmentada.',
	'hit_rate' => 'Tasa de aciertos',
	'free_mem' => 'Memoria libre',
	'total_mem' => 'Memoria compartida total',
	'used_mem' => 'Memoria utilizada',
	'avail_mem' => 'Memoria disponible',
	'memory_type' => 'Tipo de memoria',
	'memory_type_unknown' => 'n/a',
	'num_slots' => 'Número de ranuras',
	'num_hits' => 'Número de aciertos',
	'num_misses' => 'Número de fallos',
	'cache_type' => 'Tipo de caché',
	'cache_user_only' => 'Caché de datos de usuario',
	'legend_good' => 'Verde: la configuración parece correcta (alta tasa de aciertos, memoria libre razonable).',
	'legend_bad' => 'Rojo: caché bajo presión (muchos fallos o casi sin memoria libre).',
	'no_apcu' => 'APCu no parece estar habilitado en este servidor.',
	'back' => '&laquo; Volver al mantenimiento',
	'clear_fp_button'=> 'Borrar entradas APCu de FlatPress',
	'clear_fp_confirm' => '¿De verdad quieres eliminar todas las entradas APCu? Esto borrará las cachés APCu de FlatPress.',
	'clear_fp_result'=> 'Se han eliminado %d entradas APCu.',
	'msgs' => array(
		1  => 'Las entradas APCu de FlatPress se han borrado.',
		2  => 'No se han encontrado entradas APCu.',
		-1 => 'APCu no está disponible o no se ha podido acceder a él; no se ha eliminado nada.'
	)
);
?>

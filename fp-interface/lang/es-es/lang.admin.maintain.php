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
?>

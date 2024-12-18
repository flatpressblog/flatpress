<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Maintainance';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Техническое обслуживание',
	'descr' => 'Заходите сюда, когда вам кажется, что что-то не так, и, возможно, здесь вы найдете решение. Однако это может не сработать.',
	'opt0' => '&laquo; Вернуться в главное меню',
	'opt1' => 'Переиндексация',
	'opt2' => 'Очистить кэш тем и шаблонов',
	'opt3' => 'Восстановление разрешений на производственную деятельность',
	'opt4' => 'Показать информацию о PHP',
	'opt5' => 'Проверить наличие обновлений',

	'chmod_info' => 'Если разрешения <strong>не удалось</strong> сбросить, владелец файла/ директории, вероятно, не тот же, что и владелец веб-сервера.<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>Полномочия</th>
					<th>' . FP_CONTENT . '</th>
					<th>ядро</th>
					<th>Все остальные</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>файлы</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>каталоги</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => 'Все разрешения были успешно обновлены.',
	'opt3_error' => 'Ошибка при установке полномочий:'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Операция выполнена',
	-1 => 'Операция не удалась'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Обновления',
	'list' => '<ul>
		<li>Ваша текущая версия FlatPress: <big>%s</big></li>
		<li>Последняя стабильная версия FlatPress: <big><a href="%s">%s</a></big></li>
		<li>Последняя нестабильная версия FlatPress: <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Примечание:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'Доступны обновления!',
	2 => 'Вы уже обновились',
	-1 => 'Невозможно получить обновления'
);
?>

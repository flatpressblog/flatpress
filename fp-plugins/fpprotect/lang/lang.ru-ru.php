<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Настройки FlatPress Protect',
	'desc1' => 'Здесь вы можете изменить параметры безопасности для вашего блога FlatPress.',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => 'Разрешить небезопасные Java-скрипты (Не рекомендуется)',

	'allowUnsafeInlineDsc' => '<p>Разрешает загрузку небезопасного встроенного кода JavaScript.</p>' . //
		'<p><br>Примечание для разработчиков плагинов: пожалуйста, добавьте nonce к вашему Java-скрипту.</p>' . //
		'Пример для PHP:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Пример для шаблона Smarty:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>Это гарантирует, что браузер посетителя будет выполнять только те Java-скрипты, которые исходят от вашего блога FlatPress.</p>',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Разрешить создание и редактирование файла .htaccess.',
	'allowPrettyURLEditDsc' => 'Позволяет получить доступ к полю редактирования .htaccess плагина PrettyURLs для создания или изменения файла .htaccess.',

	'submit' => 'Сохранить настройки',
		'msgs' => array(
		1 => 'Настройки успешно сохранены.',
		-1 => 'Ошибка при сохранении настроек.'
	)
);
?>

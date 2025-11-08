<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Настройки FlatPress Protect',
	'desc1' => 'Здесь вы можете изменить параметры безопасности для вашего блога FlatPress. ' . //
		'Лучшая защита для ваших посетителей и вашего блога FlatPress - это когда все опции деактивированы.',

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

	// Part for metadate in images after upload
	'allow_image_metadate' => 'Сохранение метаданных и исходного качества загруженных изображений.',
	'allowImageMetadataDsc' => 'После загрузки изображений с помощью загрузчика метаданные сохраняются. К ним относятся, например, информация о камере и геокоординаты.',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => 'Разрешите FlatPress использовать неанонимизированный IP-адрес посетителя.',
	'allowVisitorIpDsc' => 'Затем FlatPress будет сохранять неанонимизированный IP-адрес, в частности, в комментариях. ' . //
		'Если вы используете антиспам-службу Akismet, Akismet также получит неанонимизированный IP-адрес.',

	// Part for Idle timeout for admin session
	'session_timeout_label' => 'Таймаут бездействия для сеанса администратора (минуты)',
	'session_timeout_desc' => 'Количество минут бездействия до истечения сеанса администратора. Пустое поле или значение 0 означает стандартное значение 60 минут.',

	'submit' => 'Сохранить настройки',
		'msgs' => array(
		1 => 'Настройки успешно сохранены.',
		-1 => 'Ошибка при сохранении настроек.'
	),

	// Warning message
	'warning_allowUnsafeInline' => 'Предупреждение: Content-Security-Policy -> Эта политика содержит "unsafe-inline", что опасно в script-src-policy.',
	'warning_allowVisitorIp' => 'Предупреждение: использование неанонимизированных IP-адресов посетителей -> Не забудьте сообщить <a href="static.php?page=privacy-policy" title="edit static page">посетителям вашего FlatPress-блога</a> об этом!'
);
?>

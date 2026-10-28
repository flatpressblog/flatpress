<?php
$lang ['admin'] ['plugin'] ['submenu'] ['bbcode'] = 'BBCode';
$lang ['admin'] ['plugin'] ['bbcode'] = array(
	'head' => 'Конфигурация BBCode',
	'desc1' => 'Этот плагин позволяет использовать разметку <a href="https://wiki.flatpress.org/doc:techfaq#bbcode" class="hint" target="_blank">BBCode</a>.',

	'options' => 'Опции',

	'editing' => 'Редактирование',
	'allow_html' => 'Встраиваемый HTML',
	'allow_html_long' => 'Разрешить использование HTML вместе с BBCode',
	'toolbar' => 'Панель инструментов',
	'toolbar_long' => 'Включить панель инструментов редактора.',

	'other' => 'Другие опции',
	'comments' => 'Комментарии',
	'comments_long' => 'Разрешить BBCode в комментариях',
	'url_new_window' => 'Открывать ссылки в новой вкладке или окне',
	'url_new_window_long' => 'По умолчанию эта настройка отключена и ссылки открываются в том же окне. Цель, явно заданная в BBCode, имеет приоритет.',
	'urlmaxlen' => 'Максимальная длина URL-адреса',
	'urlmaxlen_long_pre' => 'Сокращение URL-адресов, длина которых превышает ',
	'urlmaxlen_long_post' => ' символов.',

	'attachsdir' => 'Загрузка файлов',
	'attachsdir_long' => 'Не отображайте каталог загрузки (fp-content/attachs/) в URL.',

	'submit' => 'Сохранить конфигурацию',
	'msgs' => array(
		1 => 'Конфигурация BBCode успешно сохранена.',
		-1 => 'Конфигурация BBCode не сохранена.'
	),

	'editor' => array(
		'formatting' => 'Форматирование',
		'textarea' => 'Текстовая область: ',
		'expand' => 'Увеличить',
		'expandtitle' => 'Увеличить высоту текстовой области',
		'reduce' => 'Уменьшить',
		'reducetitle' => 'Уменьшить высоту текстовой области',
		'urltitle' => 'URL/Ссылка (Ctrl+K / ⌘K)',
		'mailtitle' => 'Адрес электронной почты',
		'boldtitle' => 'Жирный (Ctrl+B / ⌘B)',
		'italictitle' => 'Курсив (Ctrl+I / ⌘I)',
		'headlinetitle' => 'Направление',
		'fonttitle' => 'шрифт',
		'underlinetitle' => 'Подчеркнутый (Ctrl+U / ⌘U)',
		'crossouttitle' => 'Зачеркнуто (Ctrl+Shift+X / ⌘⇧X)',
		'unorderedlisttitle' => 'Неотсортированный список (Ctrl+Shift+8 / ⌘⇧8)',
		'orderedlisttitle' => 'Сортированный список (Ctrl+Shift+7 / ⌘⇧7)',
		'quotetitle' => 'Цитата',
		'codetitle' => 'Код',
		'htmltitle' => 'Вставка в виде HTML-кода',
		'help' => 'Подсказки BBCode',
		'file' => 'Файл: ',
		'image' => 'Изображение: ',
		'gallery' => 'Галерея: ',
		'selection' => '-- Выбрать --'
	)
);

$lang ['plugin'] ['bbcode'] = array (
	'go_to' => 'Перейти к',

	// Filewrapper get.php
	'error_403' => 'Ошибка 403',
	'not_send' => 'Запрошенный файл не может быть отправлен.',
	'error_404' => 'Ошибка 404',
	'not_found' => 'Запрашиваемый файл не может быть найден.',
	'file' => 'Файл',
	'report_error_1' => '',
	'report_error_2' => 'Сообщить об ошибке',
	'blog_search_1' => '',
	'blog_search_2' => 'выполнить поиск в блоге',
	'start_page_1' => 'или вернуться на',
	'start_page_2' => 'стартовую страницу'
);
?>

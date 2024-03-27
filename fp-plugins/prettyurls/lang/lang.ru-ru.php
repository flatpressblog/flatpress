<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'Я не могу найти или создать файл <code>.htaccess</code> в вашем корневом каталоге ' . //
		'PrettyURLs может работать некорректно, см. панель конфигурации.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'Конфигурация PrettyURLs';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'Конфигурация PrettyURLs',
	'description1' => 'Здесь вы можете превратить стандартные URL из FlatPress в красивые, SEO-дружественные URL.',
	'nginx' => 'PrettyURLs с NGINX',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => 'Этот редактор позволяет напрямую редактировать <code>.htaccess</code>, необходимый для работы плагина PrettyURLs.<br>' . //
		'<strong>Примечание:</strong> Только веб-серверы, совместимые с NCSA, такие как Apache, поддерживают концепцию файлов .htaccess. ' . //
		'Ваше серверное программное обеспечение: <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>',
	'cantsave' => 'Вы не можете редактировать этот файл, поскольку он  <strong>недоступен для записи</strong>. Вы можете дать разрешение на запись или скопировать и вставить в файл, а затем загрузить вручную.',
	'mode' => 'Режим',
	'auto' => 'Автоматический',
	'autodescr'	=> 'выбрать оптимальный вариант',
	'pathinfo' => 'Информация о пути',
	'pathinfodescr' => 'Например, /index.php/2024/01/01/hello-world/',
	'httpget' => 'HTTP-запрос методом GET',
	'httpgetdescr' => 'Например, /?u=/2024/01/01/hello-world/',
	'pretty' => 'Красивый URL',
	'prettydescr' => 'Например, /2024/01/01/hello-world/',

	'saveopt' => 'Сохранить настройки',

	'location' => '<strong>Место хранения:</strong> ' . ABS_PATH . '',
	'submit' => 'Сохранить .htaccess'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess успешно сохранен',
	-1 => '.htaccess не удалось сохранить (есть ли у вас права на запись  <code>' . BLOG_ROOT . '</code>)?',

	2 => 'Параметры успешно сохранены',
	-2 => 'При попытке сохранить настройки произошла ошибка'
);
?>

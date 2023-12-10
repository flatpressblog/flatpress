<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'Я не могу найти или создать файл <code>.htaccess</code> в вашем корневом каталоге ' .
		'PrettyURLs может работать некорректно, см. панель конфигурации.'
);
	
$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'Конфигурация PrettyURLs';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'Конфигурация PrettyURLs',
	'htaccess' => '.htaccess',
	'description' => 'Этот редактор позволяет редактировать ваш <code>.htaccess</code>.',
	'cantsave' => 'Вы не можете редактировать этот файл, поскольку он  <strong>недоступен для записи</strong>. Вы можете дать разрешение на запись или скопировать и вставить в файл, а затем загрузить вручную.',
	'mode' => 'Режим',
	'auto' => 'Автоматический',
	'autodescr'	=> 'выбрать оптимальный вариант',
	'pathinfo' => 'Информация о пути',
	'pathinfodescr' => 'Например, /index.php/2011/01/01/hello-world/',
	'httpget' => 'HTTP-запрос методом GET',
	'httpgetdescr' => 'Например, /?u=/2011/01/01/hello-world/',
	'pretty' => 'Красивый URL',
	'prettydescr' => 'Например, /2011/01/01/hello-world/',

	'saveopt' => 'Сохранить настройки',

	'submit' => 'Сохранить .htaccess'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess успешно сохранен',
	-1 => '.htaccess не удалось сохранить (есть ли у вас права на запись  <code>' . BLOG_ROOT . '</code>)?',

	2 => 'Параметры успешно сохранены',
	-2 => 'При попытке сохранить настройки произошла ошибка'
);
?>

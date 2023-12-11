<?php
$lang ['plugin'] ['akismet'] ['errors'] = array (
	-1 => 'Не установлен API-ключ. Откройте плагин для установки API-ключа. Зарегистрируйтесь на сайте <a href="https://akismet.com/signup/" target="_blank">akismet.com</a> чтобы получить его.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['akismet'] = 'Конфигурация плагина Akismet';

$lang ['admin'] ['plugin'] ['akismet'] = array(
	'head' => 'Конфигурация плагина Akismet',
	'description' => 'Для многих <a href="https://akismet.com/">Akismet</a> значительно уменьшит ' . //
		'или даже полностью устранит спам в комментариях и трекбэках, который вы получаете на своем сайте. ' . //
		'Если у вас еще нет учетной записи akismet.com, вы можете получить ее на сайте ' . //
		'<a href="https://akismet.com/signup/" target="_blank">akismet.com/signup</a>.',
	'apikey' => 'akismet.com API Key',
	'whatis' => '(<a href="https://akismet.com/support/getting-started/api-key/" target="_blank">Что это?</a>)',
	'submit' => 'Сохранить API-ключ'
);

$lang ['admin'] ['plugin'] ['akismet'] ['msgs'] = array(
	1 => 'API-ключ сохранен',
	-1 => 'API-ключ не действителен'
);
?>
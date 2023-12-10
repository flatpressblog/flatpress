<?php
$lang ['admin'] ['plugin'] ['submenu'] = array (
	'default' => 'Управление плагинами'
);

/* main plugin panel */
$lang ['admin'] ['plugin'] ['default'] = array(
	'head' => 'Управление плагинами',
	'enable' => 'Включить',
	'disable' => 'Отключить',
	'descr' => '<a class="hint" ' .
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="Что такое плагин?">Плагин</a> это компонент, позволяющий расширить возможности FlatPress.</p>' .
		'<p>Вы можете установить плагины, загрузив их в директорию <code>fp-plugins/</code><p>Эта панель позволяет включать и отключать плагины',
	'name' => 'Название',
	'description' => 'Описание',
	'author' => 'Автор',
	'version' => 'Версия',
	'action' => 'Действие'
);

$lang ['admin'] ['plugin'] ['default'] ['msgs'] = array(
	1 => 'Конфигурация сохранена',
	-1 => 'При попытке сохранения произошла ошибка. Это может произойти по нескольким причинам: возможно, ваш файл содержит синтаксические ошибки.'
);

/* system errors */
$lang ['admin'] ['plugin'] ['errors'] = array(
	'head' => 'При загрузке плагинов были обнаружены следующие ошибки:',
	'notfound' => 'Плагин не найден. Пропущено.',
	'generic' => 'Номер ошибки %d'
);
?>

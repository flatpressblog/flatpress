<?php
$lang ['admin'] ['widgets'] ['submenu'] ['default'] = 'Управление виджетами';
$lang ['admin'] ['widgets'] ['submenu'] ['raw'] = 'Управление виджетами (PHP)';

/* default action */
$lang ['admin'] ['widgets'] ['default'] = array(
	'head' => 'Управление виджетами',

	'descr' => '<p><a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="Что такое виджет?">' . //
		'Виджет</a> — это динамический компонент, который может отображать данные и взаимодействовать с пользователем.' . //
		'В то время как <strong>темы</strong> предназначены для изменения внешнего вида вашего блога, виджеты <strong>распространяются</strong> на внешний вид и функциональные возможности.</p>' . //

		'<p>Виджеты можно перетаскивать в специальные области темы, называемые <strong>Наборы виджетов</strong>. Количество и названия наборов виджетов может отличаться в зависимости от выбранной темы.</p>' . //

		'<p>FlatPress поставляется с несколькими виджетами: есть виджеты для входа в систему, для отображения строки поиска и т.д.</p>' . //

		'<p>Каждый виджет определяется <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title=Что такое плагин?">плагином</a>.',

	'availwdgs' => 'Доступные виджеты',
	'trashcan' => 'Перенесите сюда, чтобы удалить',

	'themewdgs' => 'Наборы виджетов для этой темы',
	'themewdgsdescr' => 'Выбранная в данный момент тема позволяет иметь следующие наборы виджетов',
	'oldwdgs' => 'Другие наборы виджетов',
	'oldwdgsdescr' => 'Следующие виджеты, по-видимому, не принадлежат ни к одному ' . //
		'из перечисленных выше наборов виджетов. Возможно, это остатки от другой темы.',

	'submit' => 'Сохранить изменения',
	'drop_here' => 'Место здесь'
);

$lang ['admin'] ['widgets'] ['default'] ['stdsets'] = array(
	'top' => 'Верхняя панель',
	'bottom' => 'Нижняя панель',
	'left' => 'Левая панель',
	'right' => 'Правая панель'
);

$lang ['admin'] ['widgets'] ['default'] ['msgs'] = array(
	1 => 'Конфигурация сохранена',
	-1 => 'При попытке сохранения произошла ошибка, попробуйте еще раз'
);

/* "raw" panel */
$lang ['admin'] ['widgets'] ['raw'] = array(
	'head' => 'Управление виджетами (<em>PHP</em>)',
	'descr' => '<p><a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="Что такое виджет?">' . //
		'Виджет</a> — это визуальный элемент <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="What is a plugin?">' . //
		'плагина</a>, который можно поместить в специальные области (<em>наборы виджетов</em>) на страницах вашего блога.</p>' . //
		'<p>Это <strong>PHP</strong>-редактор; некоторые опытные пользователи или люди, у которых ' . //
		'отключен JavaScript, могут предпочесть его.',

	'fset1' => 'Редактор',
	'fset2' => 'Применить изменения',
	'submit' => 'Применить'
);

$lang ['admin'] ['widgets'] ['raw'] ['msgs'] = array(
	1 => 'Конфигурация сохранена',
	-1 => 'При попытке сохранения произошла ошибка. Это может произойти по нескольким причинам: возможно, ваш файл содержит синтаксические ошибки.'
);

/* system errors */
$lang ['admin'] ['widgets'] ['errors'] = array(
	'generic' => 'Виджет с именем <strong>%s</strong> не зарегистрирован и будет пропущен. ' . //
 		'Включен ли плагин в <a href="admin.php?p=plugin">панели плагинов</a>?'
);
?>

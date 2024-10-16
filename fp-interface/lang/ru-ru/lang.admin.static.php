<?php
$lang ['admin'] ['static'] ['submenu'] = array(
	'list' => 'Управление статическими страницами',
	'write' => 'Создать статическую страницу'
);

/* main panel */
$lang ['admin'] ['static'] ['list'] = array(
	'head' => 'Статические страницы',
	'descr' => 'Пожалуйста, выберите страницу для редактирования или <a href="admin.php?p=static&amp;action=write">создайте новую</a>.',

	'sel' => 'Sel', // checkbox
	'date' => 'Дата',
	'name' => 'Страница',
	'title' => 'Заголовок',
	'author' => 'Автор',

	'action' => 'Действия',
	'act_view' => 'Просмотр',
	'act_del' => 'Удалить',
	'act_edit' => 'Редактировать',

	'natural' => 'Сортировка заголовков по убыванию, а не по дате создания.',
	'submit' => 'Упорядочить названия страниц'
);

$lang ['admin'] ['static'] ['list'] ['msgs'] = array(
	1 => 'Page has been saved successfully',
	-1 => 'An error occurred while trying to save the page',
	2 => 'Page has been deleted successfully',
	-2 => 'An error occurred while trying to delete the page'
);

/* write panel */
$lang ['admin'] ['static'] ['write'] = array(
	'head' => 'Публикация статической страницы',
	'descr' => 'Редактирование формы для публикации страницы',
	'fieldset1' => 'Редактировать',
	'subject' => 'Тема (*):',
	'content' => 'Содержание (*):',
	'fieldset2' => 'Подтвердить',
	'pagename' => 'Наименование страницы (*):',
	'submit' => 'Опубликовать',
	'preview' => 'Предварительный просмотр',

	'delfset' => 'Удалить',
	'deletemsg' => 'Удалить эту страницу',
	'del' => 'Удалить',
	'success' => 'Ваша страница была успешно опубликована',
	'otheropts' => 'Другие опции'
);

$lang ['admin'] ['static'] ['write'] ['error'] = array(
	'subject' => 'Вы не можете оставить тему пустой',
	'content' => 'Вы не можете опубликовать пустую запись',
	'id' => 'Вы должны указать действительный идентификатор'
);

/* delete action */	
$lang ['admin'] ['static'] ['delete'] = array(
	'head' => 'Удаление страницы', 
	'descr' => 'Вы собираетесь удалить следующую страницу:',
	'preview' => 'Предварительный просмотр',
	'confirm' => 'Вы уверены, что хотите продолжить?',
	'fset' => 'Удалить',
	'ok' => 'Да, удалить эту страницу',
	'cancel' => 'Нет, вернуться в панель управления',
	'err' => 'Указанная страница не существует'
);
?>

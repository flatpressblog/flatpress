<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => 'Файл <code>robots.txt</code> контролирует краулеров поисковых систем и их поведение на вашем блоге FlatPress. ' . //
		'Здесь вы можете создать и отредактировать файл <code>rotots.txt</code> для оптимизации поисковых систем.',
	'location' => '<strong>Место хранения:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'Сохраните robots.txt',

	// SEO Metatags part
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(
	'legend_desc' => 'Описание и ключевые слова',
	'description' => 'Благодаря этим деталям их легче найти в поисковых системах и разместить в социальных сетях. <a class="hint" href="https://ru.wikipedia.org/wiki/%D0%9C%D0%B5%D1%82%D0%B0%D1%82%D0%B5%D0%B3%D0%B8" title="Метатеги" target="_blank">Метатеги (Википедия)</a>',
	'input_desc' => 'Добавьте описание:',
	'sample_desc' => 'FlatPress. Похожие статьи, руководства и плагины',
	'input_keywords' => 'Добавьте ключевые слова:',
	'sample_keywords' => 'flatpress, flatpress статьи, flatpress руководства, flatpress плагины',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=ru#noindex" target="_blank" title="Подробнее о noindex">Запретить индексацию</a>:',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=ru#nofollow" target="_blank" title="Подробнее о nofollow">Запретить сканирование</a>:',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=ru#noarchive" target="_blank" title="Подробнее о noarchive">Запретить архивирование</a>:',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=ru#nosnippet" target="_blank" title="Подробнее о nosnippet">Запретить сниппеты</a>:'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'Домашняя',
	'blog_home' => 'Домашняя страница',
	'blog_page' => 'Блог',
	'archive' => 'Архив',
	'category' => 'Категория',
	'tag' => 'Тег',
	'contact' => 'Свяжитесь с нами',
	'comments' => 'Комментарии',
	'pagenum' => 'Страница №'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => 'Файл <code>robots.txt</code> был успешно сохранен',
	-1 => 'Файл <code>robots.txt</code> не удалось сохранить (Нет прав на запись в <code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code>)?',

	2 => 'Настройки успешно сохранены',
	-2 => 'Произошла ошибка при сохранении'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'В корневом каталоге HTTP-документа отсутствует <code>robots.txt</code> или <code>robots.txt</code> не может быть создан.'
);
?>

<?php
/*
 * LangId: Russian
 */
$lang ['setup'] = array(
	'setup' => 'Установка'
);

$lang ['locked'] = array(
	'head' => 'Установка была заблокирована',
	'descr' => 'Похоже, что вы уже запустили настройку, потому что мы нашли файл блокировки <code>%s</code>.

		Если вам нужно перезапустить установку, сначала удалите этот файл..

		<strong >Помните!</strong> Хранение <code>setup.php</code> и каталога <code>setup/</code> на Вашем сервере небезопасно, on your server, мы предлагаем Вам его удалить!

		<ul>
		<li><a href="%s">Хорошо, вернуться в мой блог.</a></li>
		<li><a href="%s">Я удалил файл, перезапустить установку.</a></li>
		</ul>'
);

$lang ['err'] = array(
	'setuprun1' => 'Выполняется установка.',
	
	'setuprun2' => 'Установка уже началась: Если вы являетесь администратором, вы можете удалить ',
	'setuprun3' => ' чтобы перезапустить.',
	'writeerror' => 'Ошибки в написании',

	'fpuser1' => ' не является действующим пользователем. ' . //
		'Имя пользователя должно быть буквенно-цифровым и не должно содержать пробелов.',
	'fpuser2' => ' не является действующим пользователем. ' . //
		'Имя пользователя может содержать только буквы, цифры и 1 знак подчеркивания.',
	'fppwd' => 'Пароль должен содержать не менее 6 символов и не должен содержать пробелов.',
	'fppwd2' => 'Пароли не совпадают.',
	'email' => ' не является действительным адресом электронной почты.',
	'www' => ' не является действительным URL-адресом.',
	'error' => '<p><big>Ошибка!</big> ' . //
		'При обработке формы возникли следующие ошибки:</p><ul>'
);

$lang ['step1'] = array(
	'head' => 'Добро пожаловать во FlatPress!',
	'descr' => 'Благодарим Вас за выбор <strong>FlatPress</strong>.

		Прежде чем Вы начнете развлекаться с Вашим новым блогом, мы должны задать вам несколько вопросов.

		Не волнуйтесь, это не займет у Вас много времени!',
	'descrl1' => 'Выберите Ваш язык.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Нет в списке?</a>',
	'descrlang' => 'Если вы не видите своего языка в этом списке, посмотрите, есть ли <a href="https://wiki.flatpress.org/res:language" target="_blank" rel="external">языковой пакет</a> для этой версии:

		<pre>%s</pre>

		Чтобы установить языковой пакет, загрузите содержимое пакета в Ваш <code>flatpress/</code>, и перезапишите все, а затем <a href="./setup.php">перезапустите эту установку</a>.',
	'descrw' => '<strong>Единственное</strong>, что вам нужно для работы FlatPress, — это каталог, <em>доступный для записи</em>.

		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => 'Создание пользователя',
	'descr' => 'Вы уже почти закончили, заполните следующие данные:',
	'fpuser' => 'Имя пользователя (логин)',
	'fppwd' => 'Пароль',
	'fppwd2' => 'Повторно введите пароль',
	'www' => 'Домашняя страница',
	'email' => 'Электронная почта'
);

$lang ['step3'] = array(
	'head' => 'Готово!',
	'descr' => '<strong>Вот и все!</strong>.

		Невероятно?

		И вы правы: <strong>история только начинается</strong>, но <strong>писать ее предстоит Вам</strong>!

		<ul>
		<li>Посмотрите <a href="%s">как выглядит главная страница</a></li>
		<li>Развлекайтесь! <a href="%s">Войдите в систему сейчас!</a></li>
		<li>Не хотите ли Вы написать нам пару строчек? <a href="https://www.flatpress.org/" target="_blank" rel="external">Переходите на FlatPress.org!</a></li>
		</ul>

		И спасибо, что выбрали FlatPress!'
);

$lang ['buttonbar'] = array(
	'next' => 'Далее >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Меню';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]Домашняя[/url]
[*][url=?paged=1]Блог[/url]
[*][url=static.php?page=about]Обо мне[/url]
[*][url=contact.php]Контакты[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'FlatPress';
$lang ['samplecontent'] ['entry'] ['content'] = 'Добро пожаловать во FlatPress! Это примерная запись, опубликованная для того, чтобы показать вам некоторые возможности [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url].

Тег more позволяет создать "переход" между отрывком и полным текстом статьи.

[more]


[h4]Стилизация[/h4]

По умолчанию стилизация и форматирование содержимого выполняется с помощью [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]BBcode-разметки[/url] (bulletin board code). BBCode — это простой способ стилизовать ваши сообщения. Допускается использование наиболее распространенных кодов. Например, [b] для [b]жирного[/b] (html: strong), [i] для [i]курсива[/i] (html: em), etc.

[quote]Также есть [b]quote[/b] для отображения ваших любимых цитат.[/quote]

[code]А \'code\' отображает фрагменты моноширинным шрифтом.
Он также поддерживает
   отступы.[/code]

Теги img и url также имеют специальные опции. Подробнее об этом можно узнать в разделе на [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]FlatPress-Wiki[/url].


[h4]Записи (посты) и статические страницы[/h4]

Это запись, а [url=static.php?page=about]Обо мне[/url] — это [b]статическая страница[/b]. Статическая страница — это запись (пост), которую нельзя комментировать, и которая не появляется вместе с обычными записями блога.

Статические страницы полезны для создания страниц с общей информацией. Вы также можете сделать одну из этих страниц [b]начальной страницей[/b] для посетителей. Это означает, что с помощью FlatPress вы можете запустить полноценный сайт, не являющийся блогом. Возможность сделать статическую страницу Главной (стартовой) находится в [b]панеле опций[/b] в [url=admin.php]зоне администрирования[/url].


[h4]Плагины[/h4]

FlatPress очень хорошо настраивается и поддерживает [url=https://wiki.flatpress.org/doc:plugins:standard target=_blank rel=external]плагины[/url] для расширения своих возможностей. BBCode является самостоятельным плагином.

Мы создали еще несколько примеров контента, чтобы показать вам некоторые из скрытых функций и жемчужин FP :)
Вы можете найти две [b]статические страницы[/b], готовые к наполнению Вашим контентом:
[list]
[*][url=static.php?page=about]Обо мне[/url]
[*][url=static.php?page=menu]Меню[/url] (обратите внимание, что ссылки на этой странице будут появляться и в боковой панели — такова магия [b]виджета blockparser[/b]. Об этом и о многом другом читайте в разделе [url=https://wiki.flatpress.org/doc:faq target=_blank rel=external]FAQ[/url]!)
[/list]

С помощью плагина [b]PhotoSwipe[/b] вы можете размещать свои изображения еще проще, либо как float="left", либо как float="right", выровненные по одному изображению, заключенному в текст.
Вы даже можете использовать элемент \'gallery\', чтобы представить посетителям целые галереи. Как это работает, [url=https://wiki.flatpress.org/res:plugins:photoswipe target=_blank rel=external]вы можете узнать здесь[/url].


[h4]Виджеты[/h4]

В боковой панели нет ни одного фиксированного элемента. Все элементы, которые вы можете найти в полосах, окружающих этот текст, полностью позиционируются, и большинство из них также настраиваются. Некоторые темы даже предоставляют интерфейс панели в области администрирования.  

Эти элементы называются [b]виджетами[/b]. Подробнее о виджетах и [url=https://wiki.flatpress.org/doc:tips:widgets target=_blank rel=external]некоторых советах[/url] по получению красивых эффектов вы можете узнать на [url=https://wiki.flatpress.org/ target=_blank rel=external]wiki[/url].


[h4]Темы[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
С темой FlatPress-Leggero в вашем распоряжении 3 шаблона стиля — от классического до современного. Эти шаблоны — прекрасное начало для создания чего-то своего.


[h4]Узнать больше[/h4]

Хотите узнать больше?

[list]
[*]Следите за [url=https://www.flatpress.org/?x target=_blank rel=external]официальным блогом[/url], чтобы знать, что происходит в мире FlatPress.
[*]Посетите [url=https://forum.flatpress.org/ target=_blank rel=external]форум[/url], чтобы получить поддержку и пообщаться.
[*]Используйте [b]отличные темы[/b], [url=https://wiki.flatpress.org/res:themes target=_blank rel=external] созданные другими пользователями[/url]!
[*]Посмотрите [url=https://wiki.flatpress.org/res:plugins target=_blank rel=external]плагины[/url].
[*]Используйте [url=https://wiki.flatpress.org/res:language target=_blank rel=external]языковой пакет[/url] для Вашего языка.
[*]Вы также можете следить за FlatPress на [url=https://fosstodon.org/@flatpress target=_blank rel=external]Mastodon[/url].
[/list]


[h4]Как я могу помочь?[/h4]

[list]
[*]Поддержите проект [url=https://www.flatpress.org/home/static.php?page=donate target=_blank rel=external]небольшим пожертвованием[/url]
[*][url=https://www.flatpress.org/contact/ target=_blank rel=external]Свяжитесь с нами[/url], чтобы сообщить об ошибках или предложить улучшения.
[*]Участвуйте в разработке FlatPress на [url=https://github.com/flatpressblog/flatpress target=_blank rel=external]GitHub[/url].
[*]Переведите FlatPress или документацию на [url=https://wiki.flatpress.org/res:language target=_blank rel=external]Ваш язык[/url].
[*]Делитесь своими знаниями и общайтесь с другими пользователями FlatPress на [url=https://forum.flatpress.org/ target=_blank rel=external]форуме[/url].
[*]Распространяйте информацию! :)
[/list]


[h4]И что теперь?[/h4]

Теперь вы можете [url=login.php]Войти в систему[/url], чтобы попасть в [url=admin.php]зону администрирования[/url] и начать публиковать!

Развлекайтесь! :)

[i]Команда [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url][/i]

';

$lang ['samplecontent'] ['about'] ['subject'] = 'Обо мне';
$lang ['samplecontent'] ['about'] ['content'] = 'Напишите что-нибудь о себе здесь. ([url=admin.php?p=static&action=write&page=about]Редактировать[/url])';

$lang ['samplecontent'] ['privacy-policy'] ['subject'] = 'Политика конфиденциальности';
$lang ['samplecontent'] ['privacy-policy'] ['content'] = 'В некоторых странах, например, если вы используете службу Akismet Antispam, необходимо предоставить посетителям политику конфиденциальности. Политика конфиденциальности также может потребоваться, если посетитель может использовать контактную форму или функцию комментариев.

[b]Совет:[/b] В интернете есть множество шаблонов и генераторов.

Вы можете вставить их сюда. ([url=admin.php?p=static&action=write&page=privacy-policy]Отредактируйте меня![/url])

Если вы активируете плагин CookieBanner, ваши посетители смогут перейти непосредственно на эту страницу в контактной форме и в функции комментариев.
';
?>

<?php
/*
 * LangId: Simplified Chinese
 */
$lang ['setup'] = array(
	'setup' => '设置'
);

$lang ['locked'] = array(
	'head' => '设置已被锁定',
	'descr' => '锁定文件<code>%s</code>如果需要重新启动安装程序，请先删除此文件。

		<strong >警告!</strong> <code>setup.php</code>文件和<code>setup/</code>目录留在服务器上是危险的。建议在设置后删除！

		<ul>
		<li><a href="%s">返回到我的blog</a></li>
		<li><a href="%s">已删除锁定文件，请重新设置</a></li>
		</ul>'
);

$lang ['err'] = array(
	'setuprun1' => '正在安装',

	'setuprun2' => '正在安装：管理员可以删除 ',
	'setuprun3' => ' 重新启动。',
	'writeerror' => '写入错误',

	'fpuser1' => ' 不是有效用户。 ' . //
		'用户名必须是字母数字，不得包含任何空格。',
	'fpuser2' => ' 不是有效用。 ' . //
		'用户名可以包含字母、数字和下划线（仅限一个字符）。',
	'fppwd' => '密码必须除掉空格以外的6个字母以上。',
	'fppwd2' => '密码不匹配。',
	'email' => ' 不是有效的电子邮件地址。',
	'www' => ' 不是有效的URL。',
	'error' => '<p><big>错误！</big> ' . //
		'处理表单时发生以下错误：</p><ul>'
);

$lang ['step1'] = array(
	'head' => '欢迎来到FlatPress！',
	'descr' => '<strong>FlatPress</strong>谢谢您选择!
	
	在你开始享受你的新博客之前，我们必须问你几个问题。
	
	别担心，不会花太长时间的！',

	'descrl1' => '选择您的语言。',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">不在名单上？</a>',

	'descrlang' => '如果您在此列表中没有看到您的语言，您可能想看看是否有此版本的<a href="https://wiki.flatpress.org/res:language" target="_blank" rel="external">语言包</a>:

		<pre>%s</pre>

		要安装语言包，请在您的<code>flatpress/</code>中上传包的内容，并覆盖所有内容，然后<a href="./setup.php">重新安装</a>.',

	'descrw' => 'FlatPress安装所需的唯一要求是一个可写目录。。

		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => '管理员',
	'descr' => '马上安装完成，请填写以下详细信息:',
	'fpuser' => '用户名',
	'fppwd' => '密码(英数6个字母以上)',
	'fppwd2' => '再次输入密码',
	'www' => '主页',
	'email' => '电子邮件'
);

$lang ['step3'] = array(
	'head' => '完成',
	'descr' => '<strong>安装完成</strong>. 

		难以置信? 

		你说得对：blog里面的故事是否精彩就看你的了:

		<ul>
		<li><a href="%s">首页看起来怎么样？我们来看看吧！</li>
		<li>祝你玩得开心<a href="%s">立即登录!</li>
		<li>你想给我们写信吗？<a href="https://www.flatpress.org/" target="_blank" rel="external">FlatPress.org</a>到我的主页来吧！</li>
		</ul>

		最后，感谢您选择FlatPress！'
);

$lang ['buttonbar'] = array(
	'next' => '下一步 >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = '菜单';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]首页[/url]
[*][url=?paged=1]博客[/url]
[*][url=static.php?page=about]简介[/url]
[*][url=contact.php]联系我们[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = '欢迎来到FlatPress！';
$lang ['samplecontent'] ['entry'] ['content'] = '这是一篇示例文章，用来展示 [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url] 的一些功能。

［more］标签可以在摘要和完整文章之间创建一个“跳转”。

[more]


[h4]样式与格式[/h4]

设置内容样式和格式的默认方式是 [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]BBcode[/url]（bulletin board code，公告板代码）。BBCode 是一种为文章设置样式的简单方法。大多数常见代码都可以使用。例如，［b］用于 [b]粗体[/b]（HTML：strong），［i］用于 [i]斜体[/i]（HTML：em）等。

[quote]也可以使用 [b]引用[/b] 块来显示您喜欢的引文。[/quote]

[code]而 ［code］ 会以等宽字体显示代码片段。
它还支持
   带缩进的内容。[/code]

［img］和［url］标签也有特殊选项。更多信息可在 [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]FlatPress-Wiki[/url] 中找到。


[h4]文章（日志）和静态页面[/h4]

这是一篇文章，而 [url=static.php?page=about]简介[/url] 是一个 [b]静态页面[/b]。静态页面也是一篇文章，但不能评论，也不会与博客的普通文章一起显示。

静态页面适合创建一般信息页面。您也可以将其中一个静态页面设为访问者看到的 [b]首页[/b]。这意味着使用 FlatPress 也可以运行一个完整的非博客网站。将静态页面设为起始页的选项位于 [url=admin.php]管理区域[/url] 的 [b]选项面板[/b] 中。


[h4]插件[/h4]

FlatPress 非常易于定制，并支持使用 [url=https://wiki.flatpress.org/doc:plugins:standard target=_blank rel=external]插件[/url] 扩展功能。BBCode 本身也是一个插件。

我们还创建了一些示例内容，用来展示 FlatPress 中一些不太显眼但很实用的功能 :)
您可以找到两个 [b]静态页面[/b]，随时可填充自己的内容：
[list]
[*][url=static.php?page=about]关于我[/url]
[*][url=static.php?page=menu]菜单[/url]（请注意，此页面中的链接也会出现在侧边栏中——这就是 [b]blockparser 小部件[/b] 的魔力。更多信息请参阅 [url=https://wiki.flatpress.org/doc:faq target=_blank rel=external]FAQ[/url]！）
[/list]

使用 [b]PhotoSwipe 插件[/b]，现在可以更轻松地插入图片，既可以作为 float="left" 或 float="right" 对齐的单张图片，并被文字环绕。
您还可以使用 ［gallery］元素向访问者展示完整图库。它有多么容易使用，[url=https://wiki.flatpress.org/doc:plugins:photoswipe target=_blank rel=external]可以在这里了解[/url]。


[h4]小部件[/h4]

侧边栏中没有一个固定不变的元素。您在本文周围栏位中看到的所有元素都可以完全自由定位，而且其中大多数也可以自定义。有些主题甚至在管理区域中提供设置面板。

这些元素称为 [b]小部件[/b]。若要了解更多有关小部件的信息以及获得漂亮效果的 [url=https://wiki.flatpress.org/doc:tips:widgets target=_blank rel=external]一些技巧[/url]，请查看 [url=https://wiki.flatpress.org/ target=_blank rel=external]wiki[/url]。


[h4]主题[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
使用 FlatPress-Leggero 主题时，您可以使用从经典到现代的 4 种样式模板。这些模板是创建您自己设计的绝佳起点。


[h4]了解更多[/h4]

想了解更多吗？

[list]
[*]关注 [url=https://www.flatpress.org/?x target=_blank rel=external]官方博客[/url]，了解 FlatPress 世界中发生的事情。
[*]访问 [url=https://forum.flatpress.org/ target=_blank rel=external]论坛[/url] 获取支持或闲聊。
[*]从 [url=https://wiki.flatpress.org/res:themes target=_blank rel=external]其他用户的投稿[/url] 中获取 [b]优秀主题[/b]！
[*]查看 [url=https://wiki.flatpress.org/res:plugins target=_blank rel=external]插件[/url]。
[*]获取适用于您语言的 [url=https://wiki.flatpress.org/res:language target=_blank rel=external]翻译包[/url]。
[*]您也可以在 [url=https://fosstodon.org/@flatpress target=_blank rel=external]Mastodon[/url] 上关注 FlatPress。
[/list]


[h4]我可以怎样帮忙？[/h4]

[list]
[*]通过 [url=https://www.flatpress.org/home/static.php?page=donate target=_blank rel=external]小额捐款[/url] 支持项目。
[*]通过 [url=https://www.flatpress.org/contact/ target=_blank rel=external]联系我们[/url] 报告错误或提出改进建议。
[*]在 [url=https://github.com/flatpressblog/flatpress target=_blank rel=external]GitHub[/url] 上参与 FlatPress 的开发。
[*]将 FlatPress 或文档翻译成 [url=https://wiki.flatpress.org/res:language target=_blank rel=external]您的语言[/url]。
[*]在 [url=https://forum.flatpress.org/ target=_blank rel=external]论坛[/url] 分享知识，并与其他 FlatPress 用户联系。
[*]请帮忙传播！ :)
[/list]


[h4]接下来做什么？[/h4]

现在您可以 [url=login.php]登录[/url] 进入 [url=admin.php]管理区域[/url] 并开始发布文章！

祝您使用愉快！ :)

[i][url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url] 团队[/i]

';

$lang ['samplecontent'] ['about'] ['subject'] = '简介';
$lang ['samplecontent'] ['about'] ['content'] = '在这里写点自我介绍吧！ ([url=admin.php?p=static&action=write&page=about]马上编辑！[/url])';

$lang ['samplecontent'] ['privacy-policy'] ['subject'] = '隐私政策';
$lang ['samplecontent'] ['privacy-policy'] ['content'] = '在某些国家，例如使用Akismet Antispam服务时，需要向访问者提供隐私政策。另外，访问者在使用接触表格和评论功能时，也可能需要隐私政策。

[b]提示:[/b]互联网上有很多模板和生成器。

你可以在这里插入它们。

([url=admin.php?p=static&action=write&page=privacy-policy]编辑[/url])

CookieBanner启用插件后，访问者可以通过联系人表单或评论功能直接访问此页面。
';
?>

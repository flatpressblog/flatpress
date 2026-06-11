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
$lang ['samplecontent'] ['entry'] ['content'] = '这是 [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url] 一篇介绍几个特征的样本文章。

［more］更多标签允许您在摘录和完整文章之间创建“跳转”。

[more]


[h4]首页样式[/h4]

设置内容样式和格式的默认方式是[url=https://wiki.flatpress.org/doc:plugins:bbcodetarget=_blank rel=external]BBcode[/url]（公告板代码）。BBCode是一种设计帖子风格的简单方法。大多数常见代码都是允许的。比如[b]代表[b]bold[/b]（html:sstrong），[i]代表[i]斜体[/i]（html/em），等等。

[quote]还有[b]引用[/b]块来显示您最喜欢的引用。。[/quote]

[code]“code”以等宽的方式显示你的片段。它还支持缩进内容。[/code]

“img”和“url”标签还有特殊选项。有关详细信息，请参见[url=https://wiki.flatpress.org/doc:plugins:bbcode请看FlatPress official website。


[h4]条目（帖子）和静态页面[/h4]

您现在阅读的样本是博客文章，“自我介绍”是固定页面。不能对固定页面进行评论。另外，不会与博客文章同时显示。

固定页面适合宣传和告知等。也可以指定为网站的首页。甚至可以运营不是博客网站的网站。要将固定页面指定为“站点顶部（优先显示）页面”，请打开管理员页面的设置。


[h4]插件[/h4]

FlatPress具有非常高的可定制性[url=https://wiki.flatpress.org/doc:plugins:standard可以通过target插件扩展功能。顺便说一下，BBCode本身也是插件的功能。

我们制作了一个样例页面来介绍FlatPress隐藏的珠玉功能：）
有两个固定页面等着你编辑：

[list]
[*][url=static.php?page=about]简介[/url]
[*][url=static.php?page=menu]菜单[/url]（请注意，此页面中的链接也将出现在您的侧边栏上——这就是[b]blockparser小部件[/b]的神奇之处。请参阅[url=https://wiki.flatpress.org/doc:faqtarget=_blank rel=external]常见问题解答[/url]以及更多内容！）
[/list]

[b] 块解析器小部件[/b]。请参阅[url=https://wiki.flatpress.org/doc:faqtarget=_blank rel=external]常见问题解答[/url]以及更多内容！）。
也可以使用“gallery”标签以图片库为单位进行放置。那个方法多么简单[url=https://wiki.flatpress.org/doc:plugins:photoswipe在这里可以学习。


[h4]小部件[/h4]

侧边栏上没有一个固定部件。此文章栏周边的侧栏上的所有部件都可以自由配置，其中大部分都可以自定义。有些功能在管理员页面上提供了设置面板。

这些元素被称为[b]小部件[/b]。有关小部件和[url的更多信息=https://wiki.flatpress.org/doc:tips:widgetstarget=_blank rel=external]一些获得良好效果的技巧[/url]，请查看[url=https://wiki.flatpress.org/target=_blank rel=external]wiki[/url]。


[h4]主题[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
FlatPress-Leggero主题提供了从经典到现代的四种自由样式。这些模板是创建自己的东西的极好的起点。


[h4]了解更多[/h4]

你想知道更多吗？

[list]
[*]点击[url=https://www.flatpress.org/?xtarget=_blank rel=external]官方博客[/url]了解FlatPress世界发生了什么。
[*]访问[url=https://forum.flatpress.org/target=_blank rel=external]论坛[/url]用于支持和闲聊。
[*]从[url获取[b]精彩主题[/b]=https://wiki.flatpress.org/res:themestarget=_blank rel=external]其他用户的提交[/url]！
[*]查看[url=https://wiki.flatpress.org/res:pluginstarget=_blank rel=external]插件[/url]。
[*]获取[url=https://wiki.flatpress.org/res:languagetarget=_blank rel=external]翻译包[/url]用于您的语言。
[*]您也可以在[url上关注FlatPress=https://fosstodon.org/@flatpress target=_blank rel=external]乳齿象[/url]。
[/list]


[h4]有什么我能帮忙的吗？[/h4]

[list]
[*]使用[url支持该项目=https://www.flatpress.org/home/static.php?page=donatetarget=_blank rel=external]小额捐款[/url]。
[*][url=https://www.flatpress.org/contact/target=_blank rel=external]联系我们[/url]报告错误或提出改进建议。
[*]在[url上为FlatPress的发展做出贡献=https://github.com/flatpressblog/flatpresstarget=_blank rel=external]GitHub[/url]。
[*]将FlatPress或文档翻译成[url=https://wiki.flatpress.org/res:languagetarget=_blank rel=external]您的语言[/url]。
[*]在[url上分享您的知识并与其他FlatPress用户联系=https://forum.flatpress.org/target=_blank rel=external论坛[/url]。
[*]传播这个词！ :)
[/list]


[h4]]现在怎么办？[/h4]

现在，您可以[url=login.php]登录[/url]进入[url=admin.php]管理区域[/url][并开始发布！
玩得开心 :)

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

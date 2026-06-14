<?php
$lang ['admin'] ['widgets'] ['submenu'] ['default'] = '管理插件';
$lang ['admin'] ['widgets'] ['submenu'] ['raw'] = '管理插件（直接编辑）';

/* default action */
$lang ['admin'] ['widgets'] ['default'] = array(
	'head' => '管理插件',

	'descr' => '<a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="What is a Widget?">' . //
		'栏</a>是可以动态显示信息和与阅览者互动的部件。 ' . //
		'主题会改变网站的外观，而小部件会扩展外观和功能。</p>' . //

		'<p>可以将小部件拖动到主题“小部件集”中的特殊区域（如侧边栏）。可以使用的小部件集的数量和扩展取决于您选择的主题。</p>' . //

		'<p>FlatPress”预先包含几个小部件：用于登录的“AdminArea”小部件、用于搜索窗口的“SearchBox”小部件等。</p>' . //

		'<p>每个小部件具有<a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="What is a plugin?">插件定义。',

	'availwdgs' => '可用部件',
	'trashcan' => '如果要从部件集中删除，请将其放在此处',

	'themewdgs' => '当前主题部件集',
	'themewdgsdescr' => '当前选择的主题包含以下部件集。',
	'oldwdgs' => '其他部件',
	'oldwdgsdescr' => '以下小部件集似乎不属于任何 ' . //
		'上面列出的widgetset。它们可能是另一个主题的残留物.',

	'submit' => '保存更改',
	'drop_here' => '放在这里'
);

$lang ['admin'] ['widgets'] ['default'] ['stdsets'] = array(
	'top' => '顶部',
	'bottom' => '底部',
	'left' => '左边栏',
	'right' => '右边栏'
);

$lang ['admin'] ['widgets'] ['default'] ['msgs'] = array(
	1 => '更改已保存',
	-1 => '无法保存。请重试。'
);

/* "raw" panel */
$lang ['admin'] ['widgets'] ['raw'] = array(
	'head' => '管理部件 (<em>原始编辑器</em>)',
	'descr' => 'A <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="什么是Widget?">' . //
		'Widget</a> 是一个视觉元素 <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="什么是plugin?">' . //
		'Plugin</a> 您可以在博客页面上添加一些特殊区域（<em>小部件集</em>）.</p>' . //
		'<p>这是<strong>原始</strong>编辑器；一些高级用户或无法使用JavaScript的人可能更喜欢它.',

	'fset1' => '编辑',
	'fset2' => '应用更改',
	'submit' => '应用'
);

$lang ['admin'] ['widgets'] ['raw'] ['msgs'] = array(
	1 => '配置已保存',
	-1 => '尝试保存时出错。这可能有几个原因：也许你的文件包含语法错误。'
);

/* system errors */
$lang ['admin'] ['widgets'] ['errors'] = array(
	'generic' => '在动态输入提示中单击 <strong>%s</strong> 未找到名为的部件，将跳过. ' . //
 		'那<a href="admin.php?p=plugin">插件</a>在的面板上有效吗?'
);
?>

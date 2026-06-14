<?php
$lang ['admin'] ['widgets'] ['submenu'] ['default'] = '管理小部件';
$lang ['admin'] ['widgets'] ['submenu'] ['raw'] = '管理小部件（直接编辑）';

/* default action */
$lang ['admin'] ['widgets'] ['default'] = array(
	'head' => '管理小部件',

	'descr' => '<a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="什么是小部件？">' . //
		'小部件</a>是可以动态显示信息并与访问者互动的组件。' . //
		'<strong>主题</strong>用于改变博客的外观，而小部件则用于<strong>扩展</strong>外观和功能。</p>' . //

		'<p>您可以把小部件拖放到主题中的特殊区域，这些区域称为<strong>小部件集</strong>。可用小部件集的数量和名称取决于当前主题。</p>' . //

		'<p>FlatPress 预先包含多个小部件，例如用于登录的 AdminArea 小部件、用于搜索框的 SearchBox 小部件等。</p>' . //

		'<p>每个小部件都由一个 <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="什么是插件？">插件</a>定义。',

	'availwdgs' => '可用小部件',
	'trashcan' => '拖到这里即可删除',

	'themewdgs' => '当前主题的小部件集',
	'themewdgsdescr' => '当前选择的主题包含以下小部件集。',
	'oldwdgs' => '其他小部件集',
	'oldwdgsdescr' => '以下小部件集似乎不属于上面列出的任何小部件集。' . //
		'它们可能是其他主题留下的。',

	'submit' => '保存更改',
	'drop_here' => '放在这里'
);

$lang ['admin'] ['widgets'] ['default'] ['stdsets'] = array(
	'top' => '顶部栏',
	'bottom' => '底部栏',
	'left' => '左侧栏',
	'right' => '右侧栏'
);

$lang ['admin'] ['widgets'] ['default'] ['msgs'] = array(
	1 => '配置已保存',
	-1 => '保存时出错，请重试。'
);

/* "raw" panel */
$lang ['admin'] ['widgets'] ['raw'] = array(
	'head' => '管理小部件（<em>原始编辑器</em>）',
	'descr' => '<a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="什么是小部件？">' . //
		'小部件</a>是由<a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="什么是插件？">' . //
		'插件</a>提供的可视化元素，可放置在博客页面的特殊区域（<em>小部件集</em>）中。</p>' . //
		'<p>这是<strong>原始</strong>编辑器；高级用户或无法使用 JavaScript 的用户可能更喜欢它。',

	'fset1' => '编辑器',
	'fset2' => '应用更改',
	'submit' => '应用'
);

$lang ['admin'] ['widgets'] ['raw'] ['msgs'] = array(
	1 => '配置已保存',
	-1 => '保存时出错。这可能有多种原因，例如文件中存在语法错误。'
);

/* system errors */
$lang ['admin'] ['widgets'] ['errors'] = array(
	'generic' => '未找到名为 <strong>%s</strong> 的已注册小部件，将跳过该项。' . //
 		'对应插件是否已在<a href="admin.php?p=plugin">插件面板</a>中启用？'
);
?>

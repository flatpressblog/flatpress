<?php
$lang ['admin'] ['widgets'] ['submenu'] ['blockparser'] = 'BlockParser 小部件';

$lang ['admin'] ['widgets'] ['blockparser'] = array(
	'head' => 'BlockParser插件',
	'description' => 'BlockParser允许您从静态页面创建插件。</p>' . //
		'<p>从列表中选择一个或多个静态页面，以使相应的插件可用。</p>' . //
		'<p><a href="?p=static&amp;action=write">新建固定页</a>这样的话，就排在这个列表上。',

	'id' => '固定页',
	'title' => '标题',
	'action' => '请选择工作',
	'enable' => '打开',
	'disable' => '关闭',
	'edit' => '编辑'
);

$lang ['admin'] ['widgets'] ['blockparser'] ['msgs'] = array(
	1 => '初始化成功。<a href="?p=widgets">管理插件</a>页面可用!',
	-1 => '初始化失败。',
	2 => '已取消部件化：确保<a href="?p=widgets">管理插件</a>在页面上，请从栏中删除！',
	-2 => '无法取消部件化。'
);
?>

<?php
$lang ['admin'] ['widgets'] ['submenu'] ['blockparser'] = 'BlockParser 小部件';

$lang ['admin'] ['widgets'] ['blockparser'] = array(
	'head' => 'BlockParser 插件',
	'description' => 'BlockParser 允许您从静态页面创建小部件。</p>' . //
		'<p>从列表中选择一个或多个静态页面，使其作为相应的小部件可用。</p>' . //
		'<p><a href="?p=static&amp;action=write">新建静态页面</a>后，它会显示在此列表中。',

	'id' => '静态页面',
	'title' => '标题',
	'action' => '请选择操作',
	'enable' => '启用',
	'disable' => '禁用',
	'edit' => '编辑'
);

$lang ['admin'] ['widgets'] ['blockparser'] ['msgs'] = array(
	1 => '初始化成功。现在可在<a href="?p=widgets">管理小部件</a>页面使用！',
	-1 => '初始化失败。',
	2 => '已取消小部件化：请确认已在<a href="?p=widgets">管理小部件</a>页面中将其从栏位移除！',
	-2 => '无法取消小部件化。'
);
?>

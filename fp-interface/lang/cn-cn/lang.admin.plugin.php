<?php
$lang ['admin'] ['plugin'] ['submenu'] = array (
	'default' => '插件管理'
);

/* main plugin panel */
$lang ['admin'] ['plugin'] ['default'] = array(
	'head' => '插件管理',
	'enable' => '启用',
	'disable' => '关闭',
	'descr' => '<a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="What is a plugin?">插件</a>是将功能添加到FlatPress或更改的部件。</p>' . //
		'<p>请把要安装新插件，上传到目录<code>fp-plugins/</code>。</p><p>在此面板中，您可以在启用/禁用插件之间切换。',
	'name' => '插件名称',
	'description' => '介绍',
	'author' => '作者',
	'version' => '版本',
	'action' => '切换设置'
);

$lang ['admin'] ['plugin'] ['default'] ['msgs'] = array(
	1 => '设置已更改。',
	-1 => '无法更改设置。可能的原因：插件有语法错误。'
);

/* system errors */
$lang ['admin'] ['plugin'] ['errors'] = array(
	'head' => '加载插件时遇到以下错误:',
	'notfound' => '找不到插件。已跳过。',
	'generic' => '错误编号%d'
);
?>

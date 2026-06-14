<?php
$lang ['admin'] ['plugin'] ['submenu'] ['bbcode'] = 'BBCode';
$lang ['admin'] ['plugin'] ['bbcode'] = array(
	'head' => 'BBCode设置',
	'desc1' => '这个插件 <a href="https://wiki.flatpress.org/' . //
		'doc:techfaq#bbcode" class="hint" target="_blank">BBCode</a> 中描述的相应参数的值
。',

	'options' => '选项',

	'editing' => '编辑',
	'allow_html' => '内嵌HTML',
	'allow_html_long' => 'BBCode和HTML通常并用
',
	'toolbar' => '工具栏',
	'toolbar_long' => '启用编辑工具栏',

	'other' => '其他选项',
	'comments' => '评论',
	'comments_long' => '允许在评论栏中使用BBCode',
	'urlmaxlen' => 'URL的最大字符数',
	'urlmaxlen_long_pre' => '几个字以上时转换为缩短URL显示：',
	'urlmaxlen_long_post' => ' 文字',

	'attachsdir' => '下载文件
',
	'attachsdir_long' => 'URL中不显示上载目录（fp-content/attachs/）。',

	'submit' => '保存设置更改',
	'msgs' => array(
		1 => 'BBCode已保存设置更改。',
		-1 => 'BBCode设置更改未保存。'
	),

	'editor' => array(
		'formatting' => '格式',
		'textarea' => '文本区域: ',
		'expand' => '扩展',
		'expandtitle' => '展开文本区域高度。',
		'reduce' => '缩小',
		'reducetitle' => '减少文本区域的高度。',
		'urltitle' => 'URL/链接',
		'mailtitle' => '电子邮件地址',
		'boldtitle' => '粗体',
		'italictitle' => '斜体',
		'headlinetitle' => '标题',
		'fonttitle' => '字体',
		'underlinetitle' => '下划线',
		'crossouttitle' => '删除线',
		'unorderedlisttitle' => '无序列表',
		'orderedlisttitle' => '排序列表',
		'quotetitle' => '区域指定为引文',
		'codetitle' => '区域指定为程序代码',
		'htmltitle' => 'HTML作为代码插入',
		'help' => 'BBCode帮助',
		'file' => '文件: ',
		'image' => '图像: ',
		'gallery' => '画廊: ',
		'selection' => '--选择并插入--'
	)
);

$lang ['plugin'] ['bbcode'] = array (
	'go_to' => '目标: ',

	// Filewrapper get.php
	'error_403' => '错误403',
	'not_send' => '无法发送请求的文件。',
	'error_404' => '错误 404',
	'not_found' => '找不到请求的文件。',
	'file' => '文件',
	'report_error_1' => '',
	'report_error_2' => '报告错误',
	'blog_search_1' => '搜索',
	'blog_search_2' => '在博客中',
	'start_page_1' => '返回',
	'start_page_2' => '返回首页'
);
?>

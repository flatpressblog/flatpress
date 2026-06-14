<?php
$lang ['admin'] ['config'] ['default'] = array(
	'head' => '设置',
	'descr' => '自定义并配置 FlatPress 安装',
	'submit' => '保存更改',

	'sysfset' => '常规设置',
	'syswarning' => '<big>警告！</big>请谨慎并准确地填写这些设置，否则 FlatPress 可能无法正常工作。',
	'blog_root' => '<strong>FlatPress 的绝对路径</strong>。注意：' . //
		'通常不需要编辑此项。FlatPress 无法自动检查该路径是否正确，请谨慎修改。',
	'www' => '<strong>博客 URL</strong>。如果博客位于子目录中，URL 必须以斜线结尾。<br>' . //
		'示例：http://www.mydomain.com/flatpress/（末尾需要斜线）',

	// ------
	'gensetts' => '系统设置',
	'adminname' => '管理用户名',
	'adminpassword' => '新密码',
	'adminpasswordconfirm' => '再次输入密码',
	'blogtitle' => '博客标题',
	'blogsubtitle' => '博客副标题',
	'blogfooter' => '博客页脚',
	'blogauthor' => '用户名',
	'startpage' => '博客首页',
	'stdstartpage' => '默认设置',
	'blogurl' => '博客网址',
	'blogemail' => '管理员电子邮件',
	'notifications' => '通知设置',
	'mailnotify' => '有新评论时发送电子邮件通知。',
	'blogmaxentries' => '在博客的一页上显示的文章数',
	'langchoice' => '语言选择',

	'intsetts' => '本地设置',
	'utctime' => '<abbr title="世界时间">UTC</abbr>时间：',
	'timeoffset' => '发布时间使用的时区偏移（中国大陆推荐值：8）',
	'hours' => '时间',
	'timeformat' => '时间显示的默认格式（在中国的推荐值：%H:%M:%S）',
	'dateformat' => '默认日期显示格式（中国大陆推荐值：%Y年%m月%d日，%A）',
	'dateformatshort' => '缩短日期显示的默认格式（在中国的推荐值：%Y-%m-%d）',
	'output' => '当前设置中的显示示例',
	'charset' => '要使用的字符编码',
	'charsettip' => '推荐使用 utf-8 字符编码。' . //
		'<a class="hint" href="https://wiki.flatpress.org/doc:techfaq#character_encoding" target="_blank" title="FlatPress 支持哪些字符编码？">了解更多</a>。'
);

$lang ['admin'] ['config'] ['default'] ['msgs'] = array(
	1 => '已保存更改的设置。',
	2 => '管理员已更改。已注销。',
	-1 => '无法保存设置。'
);

$lang ['admin'] ['config'] ['default'] ['error'] = array(
	'www' => '网站URL似乎无效。',
	'title' => '请填写博客名称。',
	'email' => '请正确填写邮件地址。',
	'maxentries' => '文章数请正确输入半角数字。',
	'timeoffset' => '请用半角数字输入有效的时差！还可以使用小数。（例2小时30分钟=>2.5）',
	'timeformat' => '请用时间显示用的表记指定。',
	'dateformat' => '请以显示日期的形式指定。',
	'dateformatshort' => '请使用缩短日期的格式。',
	'charset' => '请输入有效的字符编码名称。',
	'lang' => '所选语言不可用。',
	'admin' => '管理员名称只能包含字母、数字和下划线。',
	'password' => '密码必须至少包含 6 个非空格字符。',
	'confirm_password' => '密码不匹配。'
);
?>

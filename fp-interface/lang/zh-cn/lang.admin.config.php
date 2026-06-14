<?php
$lang ['admin'] ['config'] ['default'] = array(
	'head' => '设置',
	'descr' => '自定义和配置FlatPress安装',
	'submit' => '保存更改',

	'sysfset' => '常规设置',
	'syswarning' => '<big>警告!</big>这个设置需要谨慎和准确。否则FlatPress（可能）将出现故障。',
	'blog_root' => '<strong>FlatPress的绝对路径</strong> Note: ' . //
		'一般来说，这个不需要编辑吧。FlatPress无法检查是否正确，请仔细编辑。',
	'www' => '<strong>博客URL</strong>. 以子目录结尾的博客URL <br>' . //
		'例: http://www.mydomain.com/flatpress/ (末尾需要加斜线)',

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
	'blogemail' => '管理员E-mail',
	'notifications' => '通知设置',
	'mailnotify' => '一有评论就用邮件通知。',
	'blogmaxentries' => '在博客的一页上显示的文章数',
	'langchoice' => '语言选择',

	'intsetts' => '本地设置',
	'utctime' => '<abbr title="世界时间">UTC</abbr>时间：',
	'timeoffset' => '投稿时加算的时间（在中国的推荐值：8）',
	'hours' => '时间',
	'timeformat' => '时间显示的默认格式（在中国的推荐值：%H:%M:%S）',
	'dateformat' => '显示日期的默认格式（在中国的建议值：%Y, %B %e, %A',
	'dateformatshort' => '缩短日期显示的默认格式（在中国的推荐值：%Y-%m-%d）',
	'output' => '当前设置中的显示示例',
	'charset' => '要使用的字符代码',
	'charsettip' => '使用的文字代码是将utf-8 ' . //
		'<a class="hint" href="https://wiki.flatpress.org/doc:techfaq#character_encoding" target="_blank" title="FlatPress支持哪个字符编码？">推荐</a>。)'
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
	'charset' => 'You must insert a charset id.(请正确填写文字代码名)',
	'lang' => 'The language you chose is not available.(禁用选择语言)',
	'admin' => '管理员名称可以包含字母、数字和下划线（仅限一个字符）。',
	'password' => '密码必须6个字母以上，空格除外。',
	'confirm_password' => '密码不匹配。'
);
?>

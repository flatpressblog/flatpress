<?php
$lang = array();

$lang ['main'] = array(
	'nextpage' => '下一页 &raquo;',
	'prevpage' => '&laquo; 上一页',
	'entry' => '博客文章',
	'entries' => '博客文章',
	'static' => '菜单栏',
	'preview' => '编辑/预览',

	'filed_under' => '归档 ',

	'add_entry' => '新建博客文章',
	'add_comment' => '添加注释',
	'add_static' => '新建菜单',

	'btn_edit' => '编辑',
	'btn_delete' => '删除',

	'nocomments' => '添加注释',
	'comment' => '1有评论',
	'comments' => '有评论',

	'rss' => 'RSS订阅',
	'atom' => 'Atom订阅'
);

$lang ['search'] = array(
	'head' => '搜索',
	'fset1' => '指定搜索关键字',
	'keywords' => '关键字',
	'onlytitles' => '仅搜索标题',
	'fulltext' => '搜索全文',

	'fset2' => '指定日期',
	'datedescr' => '可以指定日期缩小。可以指定年、年月日、年月日。 ' . //
		'如果不指定日期，请留空。',

	'fset3' => '按类别搜索',
	'catdescr' => '从所有类别中搜索时，请不要选择任何一个。',

	'fset4' => '开始搜索',
	'submit' => '查找',

	'headres' => '搜索结果',
	'descrres' => '<strong>%s</strong> 搜索结果:',
	'descrnores' => '<strong>%s</strong> 中找到最佳实践。',

	'moreopts' => '附加选项',

	'searchag' => '重新搜索'
);

$lang ['search'] ['error'] = array(

	'keywords' => '请填写搜索关键字'
);

$lang ['staticauthor'] = array(
	// "Published by" in static pages
	'published_by' => '发行方',
	'on' => 'on'
);

$lang ['entryauthor'] = array(
	// "Posted by" in entry pages
	'posted_by' => '发帖人',
	'at' => 'at'
);

$lang ['entry'] = array();
$lang ['entry'] ['flags'] = array();

$lang ['entry'] ['flags'] ['long'] = array(
	'draft' => '<strong>草稿</strong>: 不公开',
	//'static' => '<strong>Static entry</strong>: normally hidden, to reach the entry put ?page=title-of-the-entry in url (experimental)',
	'commslock' => '<strong>注释保护</strong>: 无法填写评论'
);

$lang ['entry'] ['flags'] ['short'] = array(
	'draft' => '草稿',
	//'static' => 'Static',
	'commslock' => '注释保护'
);

$lang ['entry'] ['categories'] = array(
	'unfiled' => '未指定类别'
);

$lang ['404error'] = array(
	'subject' => '找不到页面',
	'content' => '<p>无法找到请求的页面。</p>'
);

// Login
$lang ['login'] = array(
	'head' => '登录',
	'fieldset1' => '请输入用户名和密码',
	'user' => '用户名:',
	'pass' => '密码:',
	'fieldset2' => '登录执行',
	'submit' => '登录',
	'forgot' => '忘记密码'
);

$lang ['login'] ['success'] = array(
	'success' => '已登录。',
	'logout' => '已注销。',
	'redirect' => '5秒钟后重登录。',
	'opt1' => '返回到站点首页',
	'opt2' => '转到管理中心',
	'opt3' => '新建博客文章'
);

$lang ['login'] ['error'] = array(
	'user' => '请填写用户名。',
	'pass' => '请填写密码。',
	'match' => '密码不正确。',
	'timeout' => '请等待30秒钟后重试。'
);

$lang ['comments'] = array(
	'head' => '注释',
	'descr' => '请在下面的表格中填写评论。',
	'fieldset1' => '填写个人资料',
	'name' => '您的名字 (*)',
	'email' => '电子邮件地址:',
	'www' => 'URL:',
	'cookie' => '存储在浏览器中',
	'fieldset2' => '填写评论',
	'comment' => '评论 (*):',
	'fieldset3' => '送信',
	'submit' => '发送',
	'reset' => '重置',
	'success' => '评论已发布。',
	'nocomments' => '还没有评论。',
	'commslock' => '不能填写评论。'
);

$lang ['comments'] ['error'] = array(
	'name' => '请填写您的姓名。',
	'email' => '请确认邮件地址是否正确。',
	'www' => '请确认URL是否正确。',
	'comment' => '请填写评论。'
);

$lang ['postviews'] = array(
	// PostView-Plugin
	'views' => '次浏览'
);

$lang ['date'] ['month'] = array(
	'1月',
	'2月',
	'3月',
	'4月',
	'5月',
	'6月',
	'7月',
	'8月',
	'9月',
	'10月',
	'11月',
	'12月'
);

$lang ['date'] ['month_abbr'] = array(
	'1',
	'2',
	'3',
	'4',
	'5',
	'6',
	'7',
	'8',
	'9',
	'10',
	'11',
	'12'
);

$lang ['date'] ['weekday'] = array(
	'星期一',
	'星期二',
	'星期三',
	'星期四',
	'星期五',
	'星期六',
	'星期日'
);

$lang ['date'] ['weekday_abbr'] = array(
	'周一',
	'周二',
	'周三',
	'周四',
	'周五',
	'周六',
	'周日'
);
?>

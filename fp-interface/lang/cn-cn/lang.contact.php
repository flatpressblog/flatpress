<?php
$baseurl = BLOG_BASEURL;

$lang ['contact'] = array(
	'head' => '发送消息',
	'descr' => '请填写以下表格后发送。想要回复的人，请一定要填写邮件地址栏。',
	'fieldset1' => '简档栏',
	'name' => '您的名字 (*):',
	'email' => '电子邮件地址:',
	'www' => 'URL:',
	'cookie' => '将简介存储在浏览器中',
	'fieldset2' => '信息栏',
	'comment' => '消息正文 (*):',
	'fieldset3' => '送信',
	'submit' => '发送',
	'reset' => '取消',
	'loggedin' => '您正在登录 😉 ⇒ <a href="' . $baseurl . 'login.php?do=logout">注销</a>或 <a href="' . $baseurl . 'admin.php">转到管理区域</a>。'
);

$lang ['contact'] ['notification'] = array(
	'name' => '名称:',
	'email' => '电子邮件地址:',
	'www' => 'URL:',
	'content' => '消息:',
	'subject' => '发送消息: '
);

$lang ['contact'] ['error'] = array(
	'name' => '请填写您的姓名。',
	'email' => '请正确填写邮件地址。',
	'www' => 'URL请正确填写。',
	'content' => '请填写信息。'
);

$lang ['contact'] ['msgs'] = array(
	1 => '已发送消息。',
	-1 => '无法发送消息。'
);
?>

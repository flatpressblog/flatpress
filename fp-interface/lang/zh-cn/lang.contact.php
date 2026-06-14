<?php
$baseurl = BLOG_BASEURL;

$lang ['contact'] = array(
	'head' => '发送消息',
	'descr' => '请填写并发送以下表单。如需回复，请务必填写电子邮件地址。',
	'fieldset1' => '个人信息',
	'name' => '您的名字 (*):',
	'email' => '电子邮件地址:',
	'www' => 'URL:',
	'cookie' => '在浏览器中保存个人信息',
	'fieldset2' => '消息',
	'comment' => '消息正文 (*):',
	'fieldset3' => '发送',
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
	'email' => '请正确填写电子邮件地址。',
	'www' => '请正确填写 URL。',
	'content' => '请填写消息正文。'
);

$lang ['contact'] ['msgs'] = array(
	1 => '消息已发送。',
	-1 => '无法发送消息。'
);
?>

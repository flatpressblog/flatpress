<?php
$baseurl = BLOG_BASEURL;

$lang ['contact'] = array(
	'head' => 'メッセージの送信',
	'descr' => '次のフォームにご記入のうえ、送信してください。返信を希望される方は、必ずメールアドレス欄にもご記入ください。',
	'fieldset1' => 'プロフィール欄',
	'name' => 'お名前 (*):',
	'email' => 'メールアドレス:',
	'www' => 'URL:',
	'cookie' => 'プロフィールをブラウザに記憶させる',
	'fieldset2' => 'メッセージ欄',
	'comment' => 'メッセージ本文 (*):',
	'fieldset3' => '送信',
	'submit' => '送信する',
	'reset' => 'キャンセル',
	'loggedin' => 'あなたはログインしています 😉. <a href="' . $baseurl . 'login.php?do=logout">ログアウト</a>または <a href="' . $baseurl . 'admin.php">管理エリアに移動します</a>。'
);

$lang ['contact'] ['notification'] = array(
	'name' => '名称:',
	'email' => '電子メール:',
	'www' => 'ウェブ:',
	'content' => 'メッセージ:',
	'subject' => 'で送信した連絡先 '
);

$lang ['contact'] ['error'] = array(
	'name' => 'お名前をご記入ください。',
	'email' => 'メールアドレスを正しくご記入ください。',
	'www' => 'URLを正しくご記入ください。',
	'content' => 'メッセージをご記入ください。'
);

$lang ['contact'] ['msgs'] = array(
	1 => 'メッセージを送信しました。',
	-1 => 'メッセージを送信できませんでした。'
);
?>

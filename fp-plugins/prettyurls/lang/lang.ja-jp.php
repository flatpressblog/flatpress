<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'ルートディレクトリに<code>.htaccess</code>ファイルが見つけられないか、作成することができません。' . //
		'PrettyURLsは適切に動作できないかもしれません。PrettyURLsの設定を見てください。'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLsの設定';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'PrettyURLsの設定',
	'description1' => 'FlatPressの標準的なURLを、SEOに配慮した美しいURLに変換することができます。',
	'htaccess' => '.htaccess',
	'description2' => 'このエディタでは、PrettyUrlsプラグインに必要な<code>.htaccess</code>を直接編集することができます。<br>' . //
		'<strong>注:</strong> .htaccessファイルの概念を認識するのは、ApacheのようなNCSA互換のウェブサーバーだけです。 ' . //
		'サーバーソフトウェアは. <strong>' . $_SERVER["SERVER_SOFTWARE"] . '</strong>',
	'cantsave' => 'このファイルを編集できません、なぜなら <strong>書き込み許可</strong>されてないからです。' .
		'書き込み許可を与えたり、ファイルにコピー＆ペーストしてアップロードすることもできる。',
	'mode' => 'モード',
	'auto' => '自動',
	'autodescr' => '最良の選択を推定します。',
	'pathinfo' => 'Path Info',
	'pathinfodescr' => '例. /index.php/2011/01/01/hello-world/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => '例. /?u=/2011/01/01/hello-world/',
	'pretty' => 'Pretty',
	'prettydescr'=> '例. /2011/01/01/hello-world/',

	'saveopt' => '設定の変更を保存する',

	'submit' => '.htaccess を保存する'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess を保存しました。',
	-1 => '.htaccess を保存できませんでした。(<code>' . BLOG_ROOT . '</code>への書き込みパーミッションが設定されていますか)?',

	2 => 'オプションの保存に成功',
	-2 => '設定を保存しようとしてエラーが発生しました'
);
?>

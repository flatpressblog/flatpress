<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'ルートディレクトリに<code>.htaccess</code>ファイルが見つけられないか、作成することができません。' . //
		'PrettyURLsは適切に動作できないかもしれません。PrettyURLsの設定を確認してください。'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLsの設定';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'PrettyURLsの設定',
	'description1' => 'FlatPressの標準的なURLを、SEOに配慮した美しいURLに変換することができます。',
	'fpprotect_is_on' => 'PrettyURLsプラグインには.htaccessファイルが必要です。 ' . //
		'このファイルを作成または編集するには、<a href="admin.php?p=config&action=fpprotect" title="FlatPressプロテクト設定へ">FlatPressプロテクト設定</a>の「.htaccessファイルの作成と編集を許可します。」オプションを有効にしてください。 ',
	'fpprotect_is_off' => 'FlatPress Protectプラグインは、.htaccessファイルを意図しない変更から保護します。 ' . //
		'プラグインの有効化は<a href="admin.php?p=plugin&action=default" title="プラグインの管理へ">こちら</a>！',
	'nginx' => 'NGINXによるPrettyURLs',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => 'このエディタでは、PrettyUrlsプラグインに必要な<code>.htaccess</code>を直接編集することができます。<br>' . //
		'<strong>注:</strong> .htaccessファイルの概念を認識するのは、ApacheのようなNCSA互換のウェブサーバーだけです。 ' . //
		'このウェブサーバーは <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong> です.',
	'cantsave' => 'このファイルを編集できません、なぜなら <strong>書き込み許可</strong>されてないからです。' .
		'書き込み許可を与えたり、ファイルにコピー＆ペーストしてアップロードすることもできます。',
	'mode' => 'モード',
	'auto' => '自動',
	'autodescr' => '最良の選択を推定します。',
	'pathinfo' => 'Path Info',
	'pathinfodescr' => '例. /index.php/2024/01/01/hello-world/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => '例. /?u=/2024/01/01/hello-world/',
	'pretty' => 'Pretty',
	'prettydescr'=> '例. /2024/01/01/hello-world/',

	'saveopt' => '設定の変更を保存する',

	'location' => '<strong>保管場所:</strong> ' . ABS_PATH . '',
	'submit' => '.htaccess を保存する'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess を保存しました。',
	-1 => '.htaccess を保存できませんでした。(<code>' . BLOG_ROOT . '</code>への書き込みパーミッションが設定されていますか?)',

	2 => '設定の保存に成功しました',
	-2 => '設定を保存しようとしましたがエラーが発生しました'
);
?>

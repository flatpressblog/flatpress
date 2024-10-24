<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'FlatPressプロテクト設定',
	'desc1' => 'ここでは、FlatPressブログのセキュリティ関連のオプションを変更することができます。',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => '安全でないJavaスクリプトを許可する（推奨しない）',

	'allowUnsafeInlineDsc' => '<p>安全でないインラインJavaScriptコードの読み込みを許可します。</p>' . //
		'<p><br>プラグイン開発者への注意：Javaスクリプトにnonceを追加してください。</p>' . //
		'PHPの例:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Smartyテンプレートの例:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>これにより、訪問者のブラウザはFlatPressブログから発信されたJavaスクリプトのみを実行するようになります。</p>',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => '.htaccess ファイルの作成と編集を許可します。',
	'allowPrettyURLEditDsc' => 'PrettyURLsプラグインの.htaccess編集フィールドにアクセスし、.htaccessファイルの作成・修正を許可します。',

	'submit' => '設定の保存',
		'msgs' => array(
		1 => '設定は正常に保存されました。',
		-1 => '設定の保存エラー'
	)
);
?>

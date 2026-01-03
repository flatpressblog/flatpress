<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'FlatPressプロテクト設定',
	'desc1' => 'ここでは、FlatPressブログのセキュリティ関連のオプションを変更することができます。 ' . //
		'訪問者とFlatPressブログのための最善の保護は、すべてのオプションを無効にすることです。',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => '安全でないJavaスクリプトを許可する(推奨しません)',

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

	// Part for external iFrame embedding
	'allow_external_iframe' => 'iFrame 経由での外部コンテンツの埋め込みを許可します（非推奨）。',
	'allowExternalIframeDsc' => '<code>&lt;iframe&gt;</code> タグ経由での外部コンテンツの埋め込みを許可します（例：動画、マップ、ウィジェット）。 ' . //
		'埋め込まれたサードパーティコンテンツは訪問者を追跡する可能性があり、安全ではない可能性があります。本当に必要な場合のみ有効にしてください。',

	// Part for SVG uploads via admin uploader
	'allow_svg_upload' => 'アップローダー経由での SVG ファイルのアップロードを許可します（信頼できるユーザーのみ）。',
	'allowSvgUploadDsc' => '管理者アップローダー経由での SVG ファイルのアップロードを許可します。SVG にはアクティブコンテンツ（例：スクリプト）が含まれている場合があります。アップローダーを信頼し、信頼できない SVG を埋め込まない場合にのみ有効にしてください。',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => '.htaccess ファイルの作成と編集を許可します。',
	'allowPrettyURLEditDsc' => 'PrettyURLsプラグインの.htaccess編集フィールドにアクセスし、.htaccessファイルの作成・修正を許可します。',

	// Part for metadate in images after upload
	'allow_image_metadate' => 'アップロードされた画像のメタデータと元の画質を保持します。',
	'allowImageMetadataDsc' => 'アップローダーで画像がアップロードされた後、メタデータは保持されます。これにはカメラ情報や地理座標などが含まれたままです。',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => 'FlatPressが訪問者の匿名化されていないIPアドレスを使用することを許可します。',
	'allowVisitorIpDsc' => 'FlatPressは、匿名化されていないIPアドレスをコメントなどに保存します。 ' . //
		'Akismetスパム対策サービスをご利用の場合、Akismetも匿名化されていないIPアドレスを受信します。',

	// Part for Idle timeout for admin session
	'session_timeout_label' => '管理者セッションのアイドルタイムアウト（分）',
	'session_timeout_desc' => '管理者セッションがタイムアウトするまでの非アクティブ時間（分）。空欄または 0 の場合は、デフォルトの 60 分となります。',

	'submit' => '設定の保存',
		'msgs' => array(
		1 => '設定は正常に保存されました。',
		-1 => '設定保存時にエラーがありました。'
	),

	// Warning message
	'warning_allowUnsafeInline' => '警告： Content-Security-Policy -> このポリシーには 「unsafe-inline 」が含まれています。',
	'warning_allowExternalIframe' => '警告： Content-Security-Policy -> 外部 iFrame 埋め込みが有効になっています。埋め込まれたサードパーティコンテンツは訪問者を追跡する可能性があり、安全ではない可能性があります。',
	'warning_allowSvgUpload' => '警告： SVG ファイルにアクティブコンテンツが含まれている可能性があります。信頼できる SVG のみをアップロードし、確認なしで埋め込まないでください。',
	'warning_allowVisitorIp' => '警告: 匿名化されていない訪問者のIPアドレスの使用 -> <a href="static.php?page=privacy-policy" title="静的ページの編集">FlatPressブログへの訪問者</a>にこのことを知らせることを忘れないでください！'
);
?>

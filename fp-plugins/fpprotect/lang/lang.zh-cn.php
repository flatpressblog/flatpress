<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'FlatPress Protect 设置',
	'desc1' => '在这里，您可以更改 FlatPress 博客的安全相关选项。' . //
		'对访问者和 FlatPress 博客而言，最佳保护是禁用所有这些选项。',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => '允许不安全的 JavaScript（不推荐）',

	'allowUnsafeInlineDsc' => '<p>允许加载不安全的内联 JavaScript 代码。</p>' . //
		'<p><br>插件开发人员注意：请为 JavaScript 添加 nonce。</p>' . //
		'PHP 示例：
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Smarty 模板示例：
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>这样可以确保访问者的浏览器只执行来自您的 FlatPress 博客的 JavaScript。</p>',

	// Part for external iFrame embedding
	'allow_external_iframe' => '允许通过 iFrame 嵌入外部内容（不推荐）。',
	'allowExternalIframeDsc' => '允许通过 <code>&lt;iframe&gt;</code> 标签嵌入外部内容（例如视频、地图和小部件）。' . //
		'嵌入的第三方内容可能会跟踪访问者，也可能不安全。仅在确实需要时才启用。',

	// Part for SVG uploads via admin uploader
	'allow_svg_upload' => '允许通过上传器上传 SVG 文件（仅限可信用户）。',
	'allowSvgUploadDsc' => '允许通过管理员上传器上传 SVG 文件。SVG 可能包含活动内容（例如脚本）；只有在信任上传者且不会嵌入未经信任的 SVG 时才启用。',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => '允许创建和编辑 .htaccess 文件。',
	'allowPrettyURLEditDsc' => '允许访问 PrettyURLs 插件的 .htaccess 编辑字段，以创建或修改 .htaccess 文件。',

	// Part for metadate in images after upload
	'allow_image_metadate' => '保留上传图片的元数据和原始图片质量。',
	'allowImageMetadataDsc' => '通过上传器上传图片后，会保留图片元数据，例如相机信息和地理坐标。',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => '允许 FlatPress 使用访问者的非匿名化 IP 地址。',
	'allowVisitorIpDsc' => 'FlatPress 会将未匿名化的 IP 地址保存到评论等数据中。' . //
		'如果使用 Akismet 反垃圾评论服务，Akismet 也会接收未匿名化的 IP 地址。',

	// Part for Idle timeout for admin session
	'session_timeout_label' => '管理员会话空闲超时（分钟）',
	'session_timeout_desc' => '管理员会话过期前允许的非活动时间（分钟）。留空或设为 0 表示默认 60 分钟。',

	'submit' => '保存设置',
		'msgs' => array(
		1 => '设置已成功保存。',
		-1 => '保存配置时出错。'
	),

	// Warning message
	'warning_allowUnsafeInline' => '警告：Content-Security-Policy -> 此策略包含 unsafe-inline，这在 script-src 策略中很危险。',
	'warning_allowExternalIframe' => '警告：Content-Security-Policy -> 已启用外部 iFrame 嵌入。嵌入的第三方内容可能会跟踪访问者，也可能不安全。',
	'warning_allowSvgUpload' => '警告：SVG 文件可能包含活动内容。只上传可信 SVG，并且未经检查不要嵌入！',
	'warning_allowVisitorIp' => '警告：使用访问者的非匿名化 IP 地址 -> 请不要忘记在<a href="static.php?page=privacy-policy" title="编辑静态页面">隐私政策页面</a>中告知 FlatPress 博客的访问者！'
);
?>

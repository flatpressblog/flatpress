<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'FlatPress保护设置',
	'desc1' => '在这里，您可以更改FlatPress博客的安全相关选项。 ' . //
		'对访问者和FlatPress博客的最佳保护是禁用所有选项。',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => '允许不安全的Java脚本（不推荐）',

	'allowUnsafeInlineDsc' => '<p>不安全的内联JavaScript允许加载代码。</p>' . //
		'<p><br>插件开发人员注意：请在Java脚本中添加nonce。</p>' . //
		'PHP示例:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Smarty模板示例:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>这将使访问者的浏览器只运行FlatPress博客发出的Java脚本。</p>',

	// Part for external iFrame embedding
	'allow_external_iframe' => 'iFrame 允许嵌入外部内容（不推荐）。',
	'allowExternalIframeDsc' => '<code>&lt;iframe&gt;</code> 允许通过标记填充外部内容（例如，视频、地图和小部件）。 ' . //
		'嵌入的第三方内容可能会跟踪访问者，也可能不安全。仅在需要时才启用。',

	// Part for SVG uploads via admin uploader
	'allow_svg_upload' => '允许通过上传器上传SVG文件（仅限可信用户）。',
	'allowSvgUploadDsc' => '允许通过管理员上传器上传SVG文件。SVG 可能包含活动内容（例如，脚本）。信任上传器，不信任 SVG 中描述的场景，使用以下步骤创建明细表，以便在概念设计中分析体量的体积。',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => '.htaccess 允许创建和编辑文件。',
	'allowPrettyURLEditDsc' => 'PrettyURLs访问插件的.htaccess编辑字段、.htaccess允许创建和修改文件。',

	// Part for metadate in images after upload
	'allow_image_metadate' => '保留上传图像的元数据和原始图像质量。',
	'allowImageMetadataDsc' => '上传器上传图像后，元数据将保留。它仍然包含相机信息和地理坐标等。',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => 'FlatPress允许访问者使用非匿名IP地址。',
	'allowVisitorIpDsc' => 'FlatPress将未匿名的IP地址保存到注释中。 ' . //
		'Akismet使用反垃圾邮件服务时，Akismet也会接收未匿名化的IP地址。',

	// Part for Idle timeout for admin session
	'session_timeout_label' => '管理员会话空闲超时（分钟）',
	'session_timeout_desc' => '管理员会话超时之前的非活动时间（分钟）。如果为空或0，则默认值为60分钟。',

	'submit' => '保存设置',
		'msgs' => array(
		1 => '设置已成功保存。',
		-1 => '保存配置时出错。'
	),

	// Warning message
	'warning_allowUnsafeInline' => '警告： Content-Security-Policy -> 此策略包含unsafe-inline。',
	'warning_allowExternalIframe' => '警告： Content-Security-Policy -> 外部 iFrame 已启用嵌入。嵌入的第三方内容可能会跟踪访问者，也可能不安全。',
	'warning_allowSvgUpload' => '警告： SVG 文件可能包含活动内容。可靠 SVG 只上传，未经确认请勿嵌入。',
	'warning_allowVisitorIp' => '警告: 使用非匿名访问者的IP地址-> <a href="static.php?page=privacy-policy" title="编辑静态页面">FlatPress请不要忘记告诉访问博客的人这件事！'
);
?>

<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => '在根目录中找不到或无法创建 <code>.htaccess</code> 文件。' . //
		'PrettyURLs 可能无法正常工作，请检查配置面板。'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLs 设置';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'PrettyURLs 设置',
	'description1' => '在这里可以把 FlatPress 的标准 URL 转换为美观且有利于 SEO 的 URL。',
	'fpprotect_is_on' => 'PrettyURLs 插件需要 .htaccess 文件。' . //
		'请在<a href="admin.php?p=config&action=fpprotect" title="FlatPress Protect 设置">FlatPress Protect 设置</a>中允许创建和编辑此文件。',
	'fpprotect_is_off' => 'FlatPress Protect 插件会保护 .htaccess 文件，避免意外更改。' . //
		'您可以<a href="admin.php?p=plugin&action=default" title="管理插件">在这里启用该插件</a>！',
	'nginx' => 'NGINX 的 PrettyURLs',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => '此编辑器允许您直接编辑 PrettyURLs 插件所需的 <code>.htaccess</code> 文件。<br>' . //
		'<strong>注意：</strong>只有 Apache 等兼容 NCSA 的 Web 服务器才支持 .htaccess 文件。' . //
		'当前 Web 服务器是 <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>。',
	'cantsave' => '无法编辑此文件，因为它不是 <strong>可写</strong> 的。' .
		'您可以授予写入权限，或者复制内容并手动上传到文件中。',
	'mode' => '模式',
	'auto' => '自动',
	'autodescr' => '自动估算最佳选项。',
	'pathinfo' => '路径信息',
	'pathinfodescr' => '例如：/index.php/2024/01/01/hello-world/',
	'httpget' => 'HTTP GET',
	'httpgetdescr' => '例如：/?u=/2024/01/01/hello-world/',
	'pretty' => '美化 URL',
	'prettydescr'=> '例如：/2024/01/01/hello-world/',

	'saveopt' => '保存设置更改',

	'location' => '<strong>存放位置：</strong>' . ABS_PATH . '',
	'submit' => '保存 .htaccess'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess 已保存。',
	-1 => '.htaccess 无法保存（请确认 <code>' . BLOG_ROOT . '</code> 是否具有写入权限）。',

	2 => '设置已成功保存',
	-2 => '尝试保存设置时出错'
);
?>

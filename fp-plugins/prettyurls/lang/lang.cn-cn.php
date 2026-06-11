<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => '在根目录中找不到或无法创建文件。' . //
		'PrettyURLs可能无法正常工作。检查预设URLs设置。'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLs设置';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'PrettyURLs设置',
	'description1' => 'FlatPress的标准URL，可以转换成SEO的精美URL。',
	'fpprotect_is_on' => 'PrettyURLs插件需要.htaccess文件。 ' . //
		'请在<a href="admin.php?p=config&action=fpprotect" title="FlatPress 保护设置">FlatPress 保护设置</a>中允许创建和编辑此文件。 ',
	'fpprotect_is_off' => 'FlatPress Protect插件保护.htaccess文件不受意外更改的影响。 ' . //
		'启用插件<a href="admin.php?p=plugin&action=default" title="管理插件">点击这里</a>！',
	'nginx' => 'NGINX的PrettyURLs',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => '此编辑器允许您直接编辑PrettyURLs插件所需的<code>.htaccess</code>。<br>' . //
		'<strong>注:</strong> .htaccess只有像Apache这样的NCSA兼容的网络服务器才能认识到文件的概念。 ' . //
		'当前 Web 服务器是 <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>。',
	'cantsave' => '无法编辑此文件，原因是 <strong>允许写入</strong>因为没有被做。' .
		'您可以授予写权限，也可以将其复制并粘贴到文件中并上传。',
	'mode' => '模式',
	'auto' => '自动',
	'autodescr' => '估计最佳选择。',
	'pathinfo' => '路径信息',
	'pathinfodescr' => '例. /index.php/2024/01/01/hello-world/',
	'httpget' => 'HTTP GET',
	'httpgetdescr' => '例. /?u=/2024/01/01/hello-world/',
	'pretty' => '美化 URL',
	'prettydescr'=> '例. /2024/01/01/hello-world/',

	'saveopt' => '保存设置更改',

	'location' => '<strong>存放地点:</strong> ' . ABS_PATH . '',
	'submit' => '.htaccess 保存'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess 已保存。',
	-1 => '.htaccess 无法保存。(<code>' . BLOG_ROOT . '</code>是否已设置对的写入权限',

	2 => '已成功保存设置',
	-2 => '尝试保存设置时出错'
);
?>

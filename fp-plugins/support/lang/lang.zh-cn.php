<?php
$lang ['admin'] ['maintain'] ['submenu'] ['support'] = '查看支持数据';

$lang ['admin'] ['maintain'] ['support'] = array(
	'title' => '支持数据',
	'intro' => '如果需要提交错误报告或寻求帮助，请访问 <a href="https://forum.flatpress.org" target="_blank">FlatPress 论坛</a>，' . //
		'在 <a href="https://github.com/flatpressblog/flatpress/issues" target="_blank">GitHub</a> 报告问题，' . //
		'或<a href="mailto:hello@flatpress.org">发送电子邮件</a>。<br>' . //
		'请描述错误和复现步骤，并复制&amp;粘贴以下信息：',

	// output "Setup"
	'h2_general' => '常规',
	'h3_setup' => '安装',

	'version' => '<p class="output"><strong>FlatPress 版本：</strong> ',
	'basedir' => '<p class="output"><strong>基础目录：</strong> ',
	'blogbaseurl' => '<p class="output"><strong>博客基础 URL：</strong> ',

	'pos_theme' => '<p class="output"><strong>主题：</strong> ',
	'neg_theme' => '<p class="output"><strong>主题：</strong>未设置（默认是 leggero）</p>',

	'pos_style' => '<p class="output"><strong>样式：</strong> ',
	'neg_style' => '<p class="output"><strong>样式：</strong>默认样式</p>',

	'pos_plugins' => '<p class="output"><strong>已启用插件：</strong> ',
	'neg_plugins' => '<p class="output"><strong>已启用插件：</strong>无法确定。</p>',

	// output "International"
	'h3_international' => '国际化',

	'pos_LANG_DEFAULT' => '<p class="output"><strong>语言（自动）：</strong> ',
	'neg_LANG_DEFAULT' => '<p class="output"><strong>语言（自动）：&#8505;</strong>无法识别</p>',

	'pos_lang' => '<p class="output"><strong>语言（已设置）：</strong> ',
	'neg_lang' => '<p class="output"><strong>语言（已设置）：</strong>未设置</p>',

	'pos_charset' => '<p class="output"><strong>字符集：</strong> ',
	'neg_charset' => '<p class="output"><strong>字符集：</strong>未设置（默认是 utf-8）</p>',

	'global_date_time' => '<p class="output"><strong>UTC 日期、时间：</strong> ',
	'neg_global_date_time' => '无法确定。</p>',

	'local_date_time' => '<p class="output"><strong>本地日期、时间：</strong> ',
	'neg_local_date_time' => '无法确定。</p>',

	'time_offset' => '<p class="output"><strong>时间偏移：</strong> ',

	// output "Core files"
	'h2_permissions' => '文件和目录权限',
	'h3_core_files' => '核心文件',

	'desc_setupfile' => '<p>首次安装成功完成后，应在生产环境运行前删除 setup.php 文件。</p>',
	'error_setupfile' => '<p class="error"><strong>&#33;</strong> setup 文件仍位于主目录中！</p>',
	'success_setupfile' => '<p class="success"><strong>&#10003;</strong> 在主目录中未找到 setup 文件。</p>',

	'desc_defaultsfile' => '<p>生产环境中，defaults.php 文件应防止其他用户写入。</p>',
	'attention_defaultsfile' => '<p class="attention"><strong>&#8505;</strong> defaults.php 文件可以被修改！</p>',
	'success_defaultsfile' => '<p class="success"><strong>&#10003;</strong> defaults.php 文件不能被修改。</p>',

	'desc_configdir' => '<p>生产环境中，config 目录应防止其他用户写入。</p>',
	'error_configdir' => '<p class="error"><strong>&#33;</strong> 配置目录可被其他用户写入！</p>',
	'success_configdir' => '<p class="success"><strong>&#10003;</strong> 配置目录不能被其他用户写入。</p>',

	'desc_admindir' => '<p>生产环境中，admin 目录应防止其他用户写入。</p>',
	'attention_admindir' => '<p class="attention"><strong>&#8505;</strong> admin 目录可被其他用户写入！</p>',
	'success_admindir' => '<p class="success"><strong>&#10003;</strong> admin 目录不能被其他用户写入。</p>',

	'desc_includesdir' => '<p>生产环境中，fp-includes 目录应防止其他用户写入。</p>',
	'attention_includesdir' => '<p class="attention"><strong>&#8505;</strong> fp-includes 目录可被其他用户写入！</p>',
	'success_includesdir' => '<p class="success"><strong>&#10003;</strong> fp-includes 目录不能被其他用户写入。</p>',

	// output "Configuration file for the webserver"
	'h3_configwebserver' => 'Web 服务器配置文件',

	'note_configwebserver' => '主目录必须可写，PrettyURLs 插件才能创建或修改 .htaccess 文件。<br>' . //
		'<strong>注意：</strong>只有 Apache 等兼容 NCSA 的 Web 服务器才支持 .htaccess 文件。',
	'serversoftware' => '服务器软件是 <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>。',

	'success_maindir' => '<p class="success"><strong>&#10003;</strong> FlatPress 主目录可写。</p>',
	'attention_maindir' => '<p class="attention"><strong>&#8505;</strong> FlatPress 主目录不可写！</p>',

	'success_htaccessw' => '<p class="success"><strong>&#10003;</strong> .htaccess 文件可写。</p>',
	'attention_htaccessw' => '<p class="attention"><strong>&#8505;</strong> .htaccess 文件不可写！</p>',

	'attention_htaccessn' => '<p class="attention"><strong>&#8505;</strong> 主目录中已存在 .htaccess 文件！</p>',
	'success_htaccessn' => '<p class="success"><strong>&#10003;</strong> 主目录中未找到 .htaccess 文件。</p>',

	// output "Themes and plugins"
	'h3_themesplugins' => '主题和插件',

	'desc_interfacedir' => '生产环境中，fp-interface 目录应防止其他用户写入。',
	'attention_interfacedir' => '<p class="attention"><strong>&#8505;</strong> fp-interface 目录可被其他用户写入！</p>',
	'success_interfacedir' => '<p class="success"><strong>&#10003;</strong> fp-interface 目录不能被其他用户写入。</p>',

	'desc_themesdir' => '生产环境中，themes 目录应防止其他用户写入。',
	'attention_themesdir' => '<p class="attention"><strong>&#8505;</strong> themes 目录可被其他用户写入！</p>',
	'success_themesdir' => '<p class="success"><strong>&#10003;</strong> themes 目录不能被其他用户写入。</p>',

	'desc_plugindir' => '生产环境中，fp-plugins 目录应防止其他用户写入。',
	'attention_plugindir' => '<p class="attention"><strong>&#8505;</strong> fp-plugins 目录可被其他用户写入！</p>',
	'success_plugindir' => '<p class="success"><strong>&#10003;</strong> fp-plugins 目录不能被其他用户写入。</p>',

	// output "Content directory"
	'h3_contentdir' => '内容目录',

	'desc_contentdir' => 'FlatPress 需要 fp-content 目录可写才能正常工作。',
	'success_contentdir' => '<p class="success"><strong>&#10003;</strong> fp-content 目录可写。</p>',
	'error_contentdir' => '<p class="error"><strong>&#33;</strong> fp-content 目录不可写！</p>',

	'desc_imagesdir' => 'images 目录必须具有写入权限，才能上传图片。',
	'success_imagesdir' => '<p class="success"><strong>&#10003;</strong> images 目录可写。</p>',
	'error_imagesdir' => '<p class="error"><strong>&#33;</strong> images 目录不可写！</p>',
	'attention_imagesdir' => '<p class="attention"><strong>&#8505;</strong> images 目录不存在。</p>',

	'desc_thumbsdir' => 'thumbs 目录必须具有写入权限，才能创建缩略图。',
	'success_thumbsdir' => '<p class="success"><strong>&#10003;</strong> images/.thumbs 目录可写。</p>',
	'error_thumbsdir' => '<p class="error"><strong>&#33;</strong> images/.thumbs 目录不可写！</p>',
	'attention_thumbsdir' => '<p class="attention"><strong>&#8505;</strong> .thumbs 目录不存在，' . //
		'但在使用 Thumbnails 插件创建第一个缩略图时会自动创建。</p>',

	'desc_attachsdir' => 'upload 目录必须具有写入权限，才能上传文件。',
	'success_attachsdir' => '<p class="success"><strong>&#10003;</strong> upload 目录可写。</p>',
	'error_attachsdir' => '<p class="error"><strong>&#33;</strong> upload 目录不可写！</p>',
	'attention_attachsdir' => '<p class="attention"><strong>&#8505;</strong> upload 目录不存在，' . //
		'但会在第一次上传时自动创建。</p>',

	'desc_cachedir' => 'cache 目录必须具有写入权限，缓存才能正常工作。',
	'success_cachedir' => '<p class="success"><strong>&#10003;</strong> cache 目录可写。</p>',
	'error1_cachedir' => '<p class="error"><strong>&#33;</strong> cache 目录不可写！</p>',
	'error2_cachedir' => '<p class="error"><strong>&#33;</strong> cache 目录不存在！</p>',

	// output "PHP"
	'h2_php' => 'PHP',

	'php_ver' => '<strong>版本：</strong>',

	'php_timezone' => '<strong>时区：</strong>',
	'php_timezone_neg' => '不可用。将使用 UTC。',

	'h3_extensions' => '扩展',

	'desc_php_intl' => '必须启用 PHP-Intl 扩展。',
	'error_php_intl' => '<p class="error"><strong>&#33;</strong> intl 扩展未启用！</p>',
	'success_php_intl' => '<p class="success"><strong>&#10003;</strong> intl 扩展已启用。</p>',

	'desc_php_gdlib' => '必须启用 GDlib 扩展才能创建图片缩略图。',
	'error_php_gdlib' => '<p class="error"><strong>&#33;</strong> GD 扩展未启用！</p>',
	'success_php_gdlib' => '<p class="success"><strong>&#10003;</strong> GD 扩展已启用。</p>',

	'desc_php_mbstring' => '为了在生产环境中获得最佳性能，应为 Smarty 启用 PHP multibyte 扩展。',
	'attention_php_mbstring' => '<p class="attention"><strong>&#8505;</strong> Multibyte 扩展未启用！</p>',
	'success_php_mbstring' => '<p class="success"><strong>&#10003;</strong> Multibyte 扩展已启用。</p>',

	// output "Other"
	'h2_other' => '其他',

	'desc_browser' => '如果存在显示错误，所使用的浏览器信息会很有帮助。',
	'no_browser' => '未识别',
	'detect_browser' => '<p class="output"><strong>浏览器：</strong>',

	'desc_cookie' => '如果需要告知 FlatPress 博客访问者有关 Cookie 的信息，这里显示的是相应的 Cookie。<br>' . //
		'<strong>提示：</strong>每次重新安装 FlatPress 后，Cookie 名称都会改变。',
	'session_cookie' => '<p class="output"><strong>会话 Cookie：</strong>',
	'no_session_cookie' => '无法确定。',

	'h3_completed' => '输出完成！',

	'symbols' => '<p class="output"><strong>符号：</strong></p>',
	'symbol_success' => '<p class="success"><strong>&#10003;</strong>无需操作</p>',
	'symbol_attention' => '<p class="attention"><strong>&#8505;</strong>不限制功能，但需要注意</p>',
	'symbol_error' => '<p class="error"><strong>&#33;</strong>需要立即处理</p>',

	'close_btn' => '关闭'
);
?>

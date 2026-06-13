<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => '<code>robots.txt</code> 文件用于控制搜索引擎抓取工具在您的 FlatPress 博客上的抓取行为。 ' . //
		'您可以在这里创建和编辑用于搜索引擎优化的 <code>robots.txt</code> 文件。',
	'cantsave' => '无法编辑此文件，因为它不是 <strong>可写</strong> 的。您可以授予写入权限，或者将内容复制并粘贴到文件中，然后手动上传。',
	'location' => '<strong>存放位置:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => '保存 robots.txt',

	// SEO Metatags part
	'legend_desc' => '描述和关键字',
	'description' => '这些信息有助于搜索引擎查找内容，也便于在社交媒体上分享。 <a class="hint" href="https://zh.wikipedia.org/wiki/Meta%E5%85%83%E7%B4%A0" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => '输入描述：',
	'sample_desc' => 'FlatPress 相关文章、指南和插件',
	'input_keywords' => '输入关键字：',
	'sample_keywords' => 'flatpress, flatpress 文章, flatpress 指南, flatpress 插件',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=zh-cn#noindex" target="_blank" title="了解更多关于 noindex 的信息">禁止索引</a>：',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=zh-cn#nofollow" target="_blank" title="了解更多关于 nofollow 的信息">禁止跟踪链接</a>：',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=zh-cn#noarchive" target="_blank" title="了解更多关于 noarchive 的信息">禁止存档</a>：',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=zh-cn#nosnippet" target="_blank" title="了解更多关于 nosnippet 的信息">禁止显示摘要</a>：'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => '首页',
	'blog_home' => '博客首页',
	'blog_page' => '博客',
	'archive' => '归档',
	'category' => '分类',
	'tag' => '标签',
	'contact' => '联系我们',
	'comments' => '评论',
	'pagenum' => '第 # 页',
	'introduction' => '简介'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => '<code>robots.txt</code> 文件已成功保存。',
	-1 => '无法保存 <code>robots.txt</code> 文件（<code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code> 中没有写入权限）。',

	2 => '设置已成功保存。',
	-2 => '保存时出错。'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => '没有可用的 <code>robots.txt</code>，或者无法在 HTTP 文档根目录中创建 <code>robots.txt</code>。'
);
?>

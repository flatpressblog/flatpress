<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => '<code>robots.txt</code>文件控制搜索引擎的爬行器和爬行器在您的FlatPress博客上的行为。 ' . //
		'在这里，可以制作和编辑用于搜索引擎优化的<code>robots.txt</code>文件。',
	'cantsave' => '无法编辑此文件，因为它不是<strong>可写</strong>的。您可以授予写入权限，或者复制并粘贴到文件中，然后手动上传。',
	'location' => '<strong>存放位置:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'robots.txt 保存',

	// SEO Metatags part
	'legend_desc' => '说明和关键字',
	'description' => '这些详细信息使搜索引擎更容易找到，也更容易发布到社交媒体上。 <a class="hint" href="https://en.wikipedia.org/wiki/Meta_element" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => '嵌入文章的说明 :',
	'sample_desc' => '填写示例）FlatPress相关的文章、指南、插件',
	'input_keywords' => '嵌入文章的关键字 :',
	'sample_keywords' => '填写示例）flatpress，flatpress的文章，flatpress的向导，flatpress的插件',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=ja#noindex" target="_blank" title="更多关于noindex的阅读">禁止索引</a>',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=ja#nofollow" target="_blank" title="更多关于nofollow的阅读">拒绝跟踪</a>',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=ja#noarchive" target="_blank" title="更多关于noarchive的阅读">禁止存档</a>',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=ja#nosnippet" target="_blank" title="更多关于nosnippet的阅读">禁止片段</a>'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => '主页',
	'blog_home' => '博客主页',
	'blog_page' => '博客',
	'archive' => '档案',
	'category' => '类别',
	'tag' => '标签',
	'contact' => '联系我们',
	'comments' => '评论',
	'pagenum' => '页面#',
	'introduction' => '介绍'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => '<code>robots.txt</code>文件已成功保存。',
	-1 => '<code>robots.txt</code>无法保存文件（<code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code>)没有写入权限）',

	2 => '已成功保存设置。',
	-2 => '保存时出错。'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => '在服务器端<code>robots.txt</code>不可用，或者在HTTP文档的根目录（子库存）中<code>robots.txt</code>无法创建。'
);
?>

<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => 'The <code>robots.txt</code> file controls the crawlers of a search engine and the behavior of the crawlers on your FlatPress blog. ' . //
		'Here you can create and edit a <code>rotots.txt</code> file for search engine optimization.',
	'location' => '<strong>Storage location:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'Save robots.txt',

	// SEO Metatags part
	'legend_desc' => 'Description and keywords',
	'description' => 'These details make it easier to find them with search engines and to post them on social media. <a class="hint" href="https://en.wikipedia.org/wiki/Meta_element" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => 'Insert the description:',
	'sample_desc' => 'FlatPress related articles, guides and plugins',
	'input_keywords' => 'Insert the keywords:',
	'sample_keywords' => 'flatpress, flatpress articles, flatpress guides, flatpress plugins',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noindex" target="_blank" title="Read more about noindex">Disallow Indexing</a>:',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nofollow" target="_blank" title="Read more about nofollow">Disallow Following</a>:',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noarchive" target="_blank" title="Read more about noarchive">Disallow Archiving</a>:',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nosnippet" target="_blank" title="Read more about nosnippet">Disallow snippets</a>:'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'Home',
	'blog_home' => 'Blog Home',
	'blog_page' => 'Blog',
	'archive' => 'Archive',
	'category' => 'Category',
	'tag' => 'Tag',
	'contact' => 'Contact Us',
	'comments' => 'Comments',
	'pagenum' => 'Page #',
	'introduction' => 'Introduction'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => 'The <code>robots.txt</code> file was saved successfully',
	-1 => 'The <code>robots.txt</code> file could not be saved (No write permissions in <code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code>)?',

	2 => 'Settings have been saved successfully',
	-2 => 'An error occurred while saving'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'No <code>robots.txt</code> is available or no <code>robots.txt</code> can be created in the HTTP document root directory.'
);
?>

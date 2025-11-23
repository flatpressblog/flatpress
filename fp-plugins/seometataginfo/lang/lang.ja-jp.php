<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt part 1
	'head' => 'SEO robots.txt',
	'description1' => '<code>robots.txt</code>ファイルは、検索エンジンのクローラーと、あなたのFlatPressブログ上でのクローラーの動作を制御します。 ' . //
		'ここでは、検索エンジン最適化のための<code>robots.txt</code>ファイルを作成・編集することができます。',
	'location' => '<strong>保管場所:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'robots.txtを保存する',

	// SEO Metatags part
	'legend_desc' => '説明とキーワード',
	'description' => 'これらの詳細な情報により、検索エンジンで見つかりやすく、またソーシャルメディアに投稿されやすくなります。 <a class="hint" href="https://en.wikipedia.org/wiki/Meta_element" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => '記事に埋め込む説明 :',
	'sample_desc' => '記入例) FlatPress関連の記事、ガイド、プラグイン',
	'input_keywords' => '記事に埋め込むキーワード :',
	'sample_keywords' => '記入例) flatpress, flatpressの記事, flatpressのガイド, flatpressのプラグイン',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=ja#noindex" target="_blank" title="noindex についてもっと読む">インデックスを禁止</a>',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=ja#nofollow" target="_blank" title="nofollow についてもっと読む">フォローを拒否</a>',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=ja#noarchive" target="_blank" title="noarchive についてもっと読む">アーカイブを禁止</a>',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=ja#nosnippet" target="_blank" title="nosnippet についてもっと読む">スニペットを禁止</a>'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'ホーム',
	'blog_home' => 'ブログホーム',
	'blog_page' => 'ブログ',
	'archive' => 'アーカイブ',
	'category' => 'カテゴリ',
	'tag' => 'タグ',
	'contact' => 'お問い合わせ',
	'comments' => 'コメント',
	'pagenum' => 'ページ #',
	'introduction' => 'はじめに'
);

// SEO robots.txt part 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => '<code>robots.txt</code>ファイルは正常に保存されました。',
	-1 => '<code>robots.txt</code>ファイルを保存できませんでした（<code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code>)に書き込み権限がありません）',

	2 => '設定が正常に保存されました。',
	-2 => '保存中にエラーが発生しました。'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'サーバ側で<code>robots.txt</code> が利用できないか、 HTTPドキュメントのルート・ディレクトリ(保管場所)に<code>robots.txt</code>を作成できませんでした。'
);
?>

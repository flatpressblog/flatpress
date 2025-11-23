<?php
$lang ['admin'] ['plugin'] ['submenu'] ['seometataginfo'] = 'SEO';
$lang ['admin'] ['plugin'] ['seometataginfo'] = array(

	// SEO robots.txt kısmı 1
	'head' => 'SEO robots.txt',
	'description1' => '<code>robots.txt</code> dosyası, arama motorlarının tarayıcılarını ve FlatPress blogunuzdaki tarayıcıların davranışlarını kontrol eder. Burada, arama motoru optimizasyonu için bir <code>robots.txt</code> dosyası oluşturabilir ve düzenleyebilirsiniz.',
	'location' => '<strong>Depolama yeri:</strong> ' . $_SERVER ['DOCUMENT_ROOT'] . '/',
	'submit' => 'robots.txt dosyasını kaydet',

	// SEO Metataglar kısmı
	'legend_desc' => 'Açıklama ve anahtar kelimeler',
	'description' => 'Bu bilgiler, arama motorlarında bulunmayı ve sosyal medyada paylaşımlarını kolaylaştırır. <a class="hint" href="https://en.wikipedia.org/wiki/Meta_element" title="Wikipedia" target="_blank">Wikipedia</a>',
	'input_desc' => 'Açıklamayı girin:',
	'sample_desc' => 'FlatPress ile ilgili makaleler, kılavuzlar ve eklentiler',
	'input_keywords' => 'Anahtar kelimeleri girin:',
	'sample_keywords' => 'flatpress, flatpress makaleleri, flatpress kılavuzları, flatpress eklentileri',
	'input_noindex' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noindex" target="_blank" title="Noindex hakkında daha fazla bilgi">Indexleme engelle</a>:',
	'input_nofollow' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nofollow" target="_blank" title="Nofollow hakkında daha fazla bilgi">Takip etmeyi engelle</a>:',
	'input_noarchive' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#noarchive" target="_blank" title="Noarchive hakkında daha fazla bilgi">Arşivlemeyi engelle</a>:',
	'input_nosnippet' => '<a class="hint" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag?hl=en#nosnippet" target="_blank" title="Nosnippet hakkında daha fazla bilgi">Snippet’leri engelle</a>:'
);

$lang ['plugin'] ['seometataginfo'] = array(
	'sep' => ' - ',
	'home' => 'Ana Sayfa',
	'blog_home' => 'Blog Ana Sayfası',
	'blog_page' => 'Blog',
	'archive' => 'Arşiv',
	'category' => 'Kategori',
	'tag' => 'Etiket',
	'contact' => 'İletişim',
	'comments' => 'Yorumlar',
	'pagenum' => 'Sayfa #',
	'introduction' => 'Giriş'
);

// SEO robots.txt kısmı 2
$lang ['admin'] ['plugin'] ['seometataginfo'] ['msgs'] = array(
	1 => 'The <code>robots.txt</code> dosyası başarıyla kaydedildi',
	-1 => 'The <code>robots.txt</code> dosyası kaydedilemedi (Yazma izinleriniz yok mu <code>' . $_SERVER ['DOCUMENT_ROOT'] . '</code>)?',

	2 => 'Ayarlar başarıyla kaydedildi',
	-2 => 'Ayarlar kaydedilirken bir hata oluştu'
);

$lang ['plugin'] ['seometataginfo'] ['errors'] = array (
	-2 => 'Hiçbir <code>robots.txt</code> mevcut değil veya <code>robots.txt</code> HTTP belge kök dizininde oluşturulamıyor.'
);
?>

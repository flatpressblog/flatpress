<?php
$lang ['plugin'] ['prettyurls'] ['errors'] = array (
	-2 => 'Kök dizininizde <code>.htaccess</code> dosyasını bulamıyorum ya da oluşturamıyorum. PrettyURLs düzgün çalışmayabilir, lütfen yapılandırma panelini kontrol edin.'
);

$lang ['admin'] ['plugin'] ['submenu'] ['prettyurls'] = 'PrettyURLs Yapılandırması';
$lang ['admin'] ['plugin'] ['prettyurls'] = array(
	'head' => 'PrettyURLs Yapılandırması',
	'description1' => 'Buradan standart FlatPress URL\'lerini güzel, SEO dostu URL\'lere dönüştürebilirsiniz.',
	'fpprotect_is_on' => 'PrettyURLs eklentisi bir .htaccess dosyası gerektirir. ' . //
		'Bu dosyayı oluşturmak ya da değiştirmek için <a href="admin.php?p=config&action=fpprotect" title="FlatPress Protect Eklentisine git">FlatPress Protect Eklentisi</a>nde bu seçeneği etkinleştirin.',
	'fpprotect_is_off' => 'FlatPress Protect eklentisi, .htaccess dosyasını istemsiz değişikliklerden korur. ' . //
		'Eklentiyi <a href="admin.php?p=plugin&action=default" title="Eklenti yönetimine git">buradan</a> etkinleştirebilirsiniz!',
	'nginx' => 'NGINX ile PrettyURLs',
	'wiki_nginx' => 'https://wiki.flatpress.org/res:plugins:prettyurls#nginx',
	'htaccess' => '.htaccess',
	'description2' => 'Bu editör, PrettyURLs eklentisi için gerekli olan <code>.htaccess</code> dosyasını doğrudan düzenlemenize olanak tanır.<br>' . //
		'<strong>Not:</strong> Yalnızca NCSA uyumlu web sunucuları, Apache gibi, .htaccess dosyası kavramını tanır. ' . //
		'Sunucu yazılımınız: <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>',
	'cantsave' => 'Bu dosyayı düzenleyemezsiniz çünkü <strong>yazılabilir</strong> değil. Yazma izni verebilirsiniz ya da dosyayı kopyalayıp yapıştırarak manuel olarak yükleyebilirsiniz.',
	'mode' => 'Mod',
	'auto' => 'Otomatik',
	'autodescr' => 'Benim için en iyi seçeneği tahmin etmeye çalış',
	'pathinfo' => 'Path Info',
	'pathinfodescr' => 'Örneğin: /index.php/2024/01/01/hello-world/',
	'httpget' => 'HTTP Get',
	'httpgetdescr' => 'Örneğin: /?u=/2024/01/01/hello-world/',
	'pretty' => 'Pretty',
	'prettydescr' => 'Örneğin: /2024/01/01/hello-world/',

	'saveopt' => 'Ayarları kaydet',

	'location' => '<strong>Depolama konumu:</strong> ' . ABS_PATH . '',
	'submit' => '.htaccess dosyasını kaydet'
);

$lang ['admin'] ['plugin'] ['prettyurls'] ['msgs'] = array(
	1 => '.htaccess başarıyla kaydedildi',
	-1 => '.htaccess kaydedilemedi (yazma izinleriniz var mı <code>' . BLOG_ROOT . '</code>?)',

	2 => 'Ayarlar başarıyla kaydedildi',
	-2 => 'Ayarları kaydederken bir hata oluştu',
);
?>

<?php
$lang['admin']['maintain']['submenu']['support'] = 'Destek verilerini göster';

$lang['admin']['maintain']['support'] = array(
	'title' => 'Destek verisi',
	'intro' => 'Hata bildirimleri ve yardım için <a href="https://forum.flatpress.org" target="_blank">FlatPress forumunu</a> ziyaret edin, ' . //
		'hata bildirimini <a href="https://github.com/flatpressblog/flatpress/issues" target="_blank">GitHub</a> üzerinden yapın ' . //
		'yada <a href="mailto:hello@flatpress.org">bize e-posta gönderin</a>.<br>Şu çıktıları (kopyala & yapıştır) İngilizce olarak ekleyin ' . //
		'hata açıklaması, yeniden çalışma adımları ile birlikte.',

	// "Kurulum" çıktısı
	'h2_general' => 'Genel',
	'h3_setup' => 'Kurulum',

	'version' => '<p class="output"><strong>FlatPress sürümü:</strong> ',
	'basedir' => '<p class="output"><strong>Temel dizin:</strong> ',
	'blogbaseurl' => '<p class="output"><strong>Blog temel URL\'si:</strong> ',

	'pos_theme' => '<p class="output"><strong>Temalar:</strong> ',
	'neg_theme' => '<p class="output"><strong>Temalar:</strong> ayarlanmadı (varsayılan tema leggero)</p>',

	'pos_style' => '<p class="output"><strong>Biçim:</strong> ',
	'neg_style' => '<p class="output"><strong>Biçim:</strong> varsayılan biçim</p>',

	'pos_plugins' => '<p class="output"><strong>Aktif eklentiler:</strong> ',
	'neg_plugins' => '<p class="output"><strong>Aktif eklentiler:</strong> Belirlenemedi.</p>',

	// "Uluslararası" çıktısı
	'h3_international' => 'Uluslararası',

	'pos_LANG_DEFAULT' => '<p class="output"><strong>Dil (otomatik):</strong> ',
	'neg_LANG_DEFAULT' => '<p class="output"><strong>Dil (otomatik): &#8505;</strong> tanınmadı</p>',

	'pos_lang' => '<p class="output"><strong>Dil (ayarlanmış):</strong> ',
	'neg_lang' => '<p class="output"><strong>Dil (ayarlanmış):</strong> ayarlanmadı</p>',

	'pos_charset' => '<p class="output"><strong>Karakter seti:</strong> ',
	'neg_charset' => '<p class="output"><strong>Karakter seti:</strong> ayarlanmamış (varsayılan utf-8)</p>',

	'global_date_time' => '<p class="output"><strong>UTC tarih, saat:</strong> ',
	'neg_global_date_time' => 'Belirlenemedi.</p>',

	'local_date_time' => '<p class="output"><strong>LCL tarih, saat:</strong> ',
	'neg_local_date_time' => 'Belirlenemedi.</p>',

	'time_offset' => '<p class="output"><strong>Zaman farkı:</strong> ',

	// "Dosya ve dizin izinleri" çıktısı
	'h2_permissions' => 'Dosya ve dizin izinleri',
	'h3_core_files' => 'Temel Dosyalar',

	'desc_setupfile' => '<p>Kurulum başarıyla tamamlandığında, setup.php dosyası, çalışma ortamından önce silinmelidir.</p>',
	'error_setupfile' => '<p class="error"><strong>&#33;</strong> Kurulum dosyası ana dizinde bulunuyor!</p>',
	'success_setupfile' => '<p class="success"><strong>&#10003;</strong> Kurulum dosyası ana dizinde bulunamadı.</p>',

	'desc_defaultsfile' => '<p>defaults.php dosyası, çalışma ortamında başkaları tarafından yazılamaz olmalıdır.</p>',
	'attention_defaultsfile' => '<p class="attention"><strong>&#8505;</strong> defaults.php dosyası değiştirilebilir!</p>',
	'success_defaultsfile' => '<p class="success"><strong>&#10003;</strong> defaults.php dosyası değiştirilemez.</p>',

	'desc_configdir' => '<p>config dizini, çalışma ortamında başkaları tarafından yazılamaz olmalıdır.</p>',
	'error_configdir' => '<p class="error"><strong>&#33;</strong> Yapılandırma dizini başkaları tarafından yazılabilir!</p>',
	'success_configdir' => '<p class="success"><strong>&#10003;</strong> Yapılandırma dizini başkaları tarafından yazılamaz.</p>',

	'desc_admindir' => '<p>admin dizini, çalışma ortamında başkaları tarafından yazılamaz olmalıdır.</p>',
	'attention_admindir' => '<p class="attention"><strong>&#8505;</strong> admin dizini başkaları tarafından yazılabilir!</p>',
	'success_admindir' => '<p class="success"><strong>&#10003;</strong> admin dizini başkaları tarafından yazılamaz.</p>',

	'desc_includesdir' => '<p>fp-includes dizini, çalışma ortamında başkaları tarafından yazılamaz olmalıdır.</p>',
	'attention_includesdir' => '<p class="attention"><strong>&#8505;</strong> fp-includes dizini başkaları tarafından yazılabilir!</p>',
	'success_includesdir' => '<p class="success"><strong>&#10003;</strong> fp-includes dizini başkaları tarafından yazılamaz.</p>',

	// "Web sunucusu için yapılandırma dosyası" çıktısı
	'h3_configwebserver' => 'Web sunucusu için yapılandırma dosyası',

	'note_configwebserver' => 'Ana dizinin yazılabilir olması, PrettyURLs eklentisi ile .htaccess dosyasının oluşturulması veya değiştirilmesi için gereklidir.<br>' . //
		'<strong>Not:</strong> Yalnızca NCSA uyumlu web sunucuları, örneğin Apache, .htaccess dosyalarını tanır.',
	'serversoftware' => 'Sunucu yazılımı <strong>' . $_SERVER['SERVER_SOFTWARE'] . '</strong>.',
	
	'success_maindir' => '<p class="success"><strong>&#10003;</strong> FlatPress ana dizini yazılabilir.</p>',
	'attention_maindir' => '<p class="attention"><strong>&#8505;</strong> FlatPress ana dizini yazılamaz!</p>',

	'success_htaccessw' => '<p class="success"><strong>&#10003;</strong> .htaccess dosyası yazılabilir.</p>',
	'attention_htaccessw' => '<p class="attention"><strong>&#8505;</strong> .htaccess dosyası yazılamaz!</p>',

	'attention_htaccessn' => '<p class="attention"><strong>&#8505;</strong> Ana dizinde zaten bir .htaccess dosyası mevcut!</p>',
	'success_htaccessn' => '<p class="success"><strong>&#10003;</strong> Ana dizinde herhangi bir .htaccess dosyası bulunmadı.</p>',

	// "Temalar ve Eklentiler" çıktısı
	'h3_themesplugins' => 'Temalar ve Eklentiler',

	'desc_interfacedir' => 'fp-interface dizini, çalışma ortamında başkaları tarafından yazılamaz olmalıdır.',
	'attention_interfacedir' => '<p class="attention"><strong>&#8505;</strong> fp-interface dizini başkaları tarafından yazılabilir!</p>',
	'success_interfacedir' => '<p class="success"><strong>&#10003;</strong> fp-interface dizini başkaları tarafından yazılamaz.</p>',

	'desc_themesdir' => 'themes dizini, çalışma ortamında başkaları tarafından yazılamaz olmalıdır.',
	'attention_themesdir' => '<p class="attention"><strong>&#8505;</strong> themes dizini başkaları tarafından yazılabilir!</p>',
	'success_themesdir' => '<p class="success"><strong>&#10003;</strong> themes dizini başkaları tarafından yazılamaz.</p>',

	'desc_plugindir' => 'fp-plugins dizini, çalışma ortamında başkaları tarafından yazılamaz olmalıdır.',
	'attention_plugindir' => '<p class="attention"><strong>&#8505;</strong> fp-plugins dizini başkaları tarafından yazılabilir!</p>',
	'success_plugindir' => '<p class="success"><strong>&#10003;</strong> fp-plugins dizini başkaları tarafından yazılamaz.</p>',

	// "İçerik dizini" çıktısı
	'h3_contentdir' => 'İçerik',

	'desc_contentdir' => 'fp-content dizini, FlatPress\'in çalışabilmesi için yazılabilir olmalıdır.',
	'success_contentdir' => '<p class="success"><strong>&#10003;</strong> fp-content dizini yazılabilir.</p>',
	'error_contentdir' => '<p class="error"><strong>&#33;</strong> fp-content dizini yazılamaz!</p>',

	'desc_imagesdir' => 'Bu images dizini, resimleri yükleyebilmek için yazma izinlerine sahip olmalıdır.',
	'success_imagesdir' => '<p class="success"><strong>&#10003;</strong> images dizini yazılabilir.</p>',
	'error_imagesdir' => '<p class="error"><strong>&#33;</strong> images dizini yazılamaz!</p>',
	'attention_imagesdir' => '<p class="attention"><strong>&#8505;</strong> images dizini mevcut değil.</p>',

	'desc_thumbsdir' => 'Bu thumbs dizini, ölçeklenebilir resimler oluşturulabilmesi için yazma izinlerine sahip olmalıdır.',
	'success_thumbsdir' => '<p class="success"><strong>&#10003;</strong> images/.thumbs dizini yazılabilir.</p>',
	'error_thumbsdir' => '<p class="error"><strong>&#33;</strong> images/.thumbs dizini yazılamaz!</p>',
	'attention_thumbsdir' => '<p class="attention"><strong>&#8505;</strong> .thumbs dizini mevcut değil, ancak Thumbnails eklentisiyle bir küçük resim oluşturulduğunda otomatik olarak oluşturulur.</p>',

	'desc_attachsdir' => 'Bu upload dizini, herhangi bir şey yükleyebilmek için yazma izinlerine sahip olmalıdır.',
	'success_attachsdir' => '<p class="success"><strong>&#10003;</strong> upload dizini yazılabilir.</p>',
	'error_attachsdir' => '<p class="error"><strong>&#33;</strong> upload dizini yazılamaz!</p>',
	'attention_attachsdir' => '<p class="attention"><strong>&#8505;</strong> upload dizini mevcut değil, ancak ilk yükleme ile otomatik olarak oluşturulur.</p>',

	'desc_cachedir' => 'Bu cache dizini, önbelleğin düzgün çalışabilmesi için yazma iznine sahip olmalıdır.',
	'success_cachedir' => '<p class="success"><strong>&#10003;</strong> cache dizini yazılabilir.</p>',
	'error1_cachedir' => '<p class="error"><strong>&#33;</strong> cache dizini yazılamaz!</p>',
	'error2_cachedir' => '<p class="error"><strong>&#33;</strong> cache dizini mevcut değil!</p>',

	// "PHP" çıktısı
	'h2_php' => 'PHP',

	'php_ver' => '<strong>Sürüm: </strong>',

	'php_timezone' => '<strong>Zaman dilimi: </strong>',
	'php_timezone_neg' => 'Mevcut değil. UTC kullanılıyor.',

	'h3_extensions' => 'Eklentiler',

	'desc_php_intl' => 'PHP-Intl eklentisi etkinleştirilmelidir.',
	'error_php_intl' => '<p class="error"><strong>&#33;</strong> Intl eklentisi etkinleştirilmemiştir!</p>',
	'success_php_intl' => '<p class="success"><strong>&#10003;</strong> Intl uzantısı etkinleştirilmiştir.</p>',

	'desc_php_gdlib' => 'Görüntü küçük resimleri oluşturmak için GDlib uzantısı etkinleştirilmelidir.',
	'error_php_gdlib' => '<p class="error"><strong>&#33;</strong> GD uzantısı etkinleştirilmemiştir!</p>',
	'success_php_gdlib' => '<p class="success"><strong>&#10003;</strong> GD uzantısı etkinleştirilmiştir.</p>',

	'desc_php_mbstring' => 'Üretken çalışmada optimum performans için, Smarty için PHP multibyte uzantısı etkinleştirilmelidir.',
	'attention_php_mbstring' => '<p class="attention"><strong>&#8505;</strong> Multibyte uzantısı etkinleştirilmemiştir!</p>',
	'success_php_mbstring' => '<p class="success"><strong>&#10003;</strong> Multibyte uzantısı etkinleştirilmiştir.</p>',

	// "Diğer" çıktısı
	'h2_other' => 'Diğer',

	'desc_browser' => 'Tarayıcı, görüntüleme hataları olması durumunda önemlidir.',
	'no_browser' => 'Tanınmadı',
	'detect_browser' => '<p class="output"><strong>Tarayıcı: </strong>',

	'desc_cookie' => 'Eğer FlatPress blogu ziyaretçilerine çerezler hakkında bilgi verilecekse, bu çerez ile ilgilidir.<br>' . //
		'<strong>İpucu:</strong> Çerezin adı, FlatPress yeniden yüklendiğinde değişir.',
	'session_cookie' => '<p class="output"><strong>Oturum çerezi: </strong>',
	'no_session_cookie' => 'Belirlenemedi.',

	'h3_completed' => 'Çıktı tamamlandı!',

	'symbols' => '<p class="output"><strong>Semboller:</strong></p>',
	'symbol_success' => '<p class="success"><strong>&#10003;</strong> Herhangi bir işlem gerekli değil</p>',
	'symbol_attention' => '<p class="attention"><strong>&#8505;</strong> İşlevselliği sınırlamaz, ancak dikkat gerektirir</p>',
	'symbol_error' => '<p class="error"><strong>&#33;</strong> Hızlıca müdahale gereklidir</p>',

	'close_btn' => 'Kapat'
);
?>

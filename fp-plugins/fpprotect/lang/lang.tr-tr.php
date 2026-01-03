<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'FlatPress Protect ayarları',
	'desc1' => 'Burada, FlatPress blogunuzun güvenlik ile ilgili ayarlarını değiştirebilirsiniz. ' . //
		'Ziyaretçileriniz ve FlatPress blogunuz için en iyi koruma, tüm seçeneklerin devre dışı bırakıldığı durumdur.',

	// Güvensiz iç scriptler için bölüm
	'allow_unsafe_inline' => 'Güvensiz JavaScript\'lere izin ver (Önerilmez)',

	'allowUnsafeInlineDsc' => '<p>Güvensiz satıriçi JavaScript kodlarının yüklenmesine izin verir.</p>' . //
		'<p><br>Eklenti geliştiricilerine not: Lütfen JavaScript\'inize bir nonce ekleyin.</p>' . //
		'PHP için bir örnek:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Smarty şablonu için bir örnek:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>Bu, ziyaretçinin tarayıcısının yalnızca FlatPress blogunuzdan gelen JavaScript\'leri çalıştırmasını sağlar.</p>',

	// Part for external iFrame embedding
	'allow_external_iframe' => 'iFrame aracılığıyla harici içerik yerleştirmeye izin verin (Önerilmez).',
	'allowExternalIframeDsc' => '<code>&lt;iframe&gt;</code> etiketi aracılığıyla harici içerik yerleştirmeye izin verir (örneğin videolar, haritalar, widget\'lar). ' . //
		'Yerleştirilen üçüncü taraf içerik ziyaretçileri izleyebilir ve güvenli olmayabilir. Bunu yalnızca gerçekten ihtiyacınız varsa etkinleştirin.',

	// Part for SVG uploads via admin uploader
	'allow_svg_upload' => 'Yükleyici aracılığıyla SVG dosyaları yüklemeye izin verin (yalnızca güvenilir kullanıcılar).',
	'allowSvgUploadDsc' => 'Yönetici yükleyicisi aracılığıyla SVG dosyaları yüklemeye izin verir. SVG aktif içerik (örneğin komut dosyaları) içerebilir; bunu yalnızca yükleyicilere güveniyorsanız ve güvenilmeyen SVG\'leri yerleştirmiyorsanız etkinleştirin.',

	// PrettyURLs .htaccess düzenleme alanı için bölüm
	'allow_htaccess_edit' => '.htaccess dosyasının oluşturulmasına ve düzenlenmesine izin ver.',
	'allowPrettyURLEditDsc' => '.htaccess dosyasını oluşturmak veya düzenlemek için PrettyURLs eklentisinin düzenleme alanına erişime izin verir.',

	// Yüklenen görsellerdeki meta veriler için bölüm
	'allow_image_metadate' => 'Yüklenen görsellerde meta verileri ve orijinal görsel kalitesini koru.',
	'allowImageMetadataDsc' => 'Görseller yüklenirken meta veriler korunur. Bu, örneğin kamera bilgilerini ve coğrafi koordinatları içerir.',

	// FlatPress'teki ziyaretçi IP'si için bölüm
	'allow_visitor_ip' => 'FlatPress\'in ziyaretçinin anonimleştirilmemiş IP adresini kullanmasına izin ver.',
	'allowVisitorIpDsc' => 'FlatPress, anonimleştirilmemiş IP adresini, yorumlar gibi yerlerde kaydedecektir. ' . //
		'Akismet Antispam servisini kullanıyorsanız, Akismet de anonimleştirilmemiş IP adresini alacaktır.',

	// Part for Idle timeout for admin session
	'session_timeout_label' => 'Yönetici oturumu için boşta kalma zaman aşımı (dakika)',
	'session_timeout_desc' => 'Yönetici oturumu sona erene kadar hareketsiz kalınacak dakika. Boş veya 0 varsayılan olarak 60 dakika anlamına gelir.',

	'submit' => 'Ayarları kaydet',
		'msgs' => array(
		1 => 'Ayarlar başarıyla kaydedildi.',
		-1 => 'Ayarlar kaydedilirken bir hata oluştu.'
	),

	// Uyarı mesajı
	'warning_allowUnsafeInline' => 'Uyarı: Content-Security-Policy -> Bu politika "unsafe-inline" içeriyor, bu script-src politikasında tehlikelidir.',
	'warning_allowExternalIframe' => 'Uyarı: İçerik Güvenlik Politikası -> Harici iFrame yerleştirme etkinleştirildi. Yerleştirilen üçüncü taraf içerik ziyaretçileri izleyebilir ve güvenli olmayabilir.',
	'warning_allowSvgUpload' => 'Uyarı: SVG dosyaları aktif içerik içerebilir. Yalnızca güvenilir SVG\'leri yükleyin ve incelemeden yerleştirmeyin!',
	'warning_allowVisitorIp' => 'Uyarı: Ziyaretçinin anonimleştirilmemiş IP adresi kullanımı -> Lütfen FlatPress blogunuzun <a href="static.php?page=privacy-policy" title="statik sayfa düzenle">ziyaretçilerini</a> bu konuda bilgilendirdiğinizden emin olun!'
);
?>

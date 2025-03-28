<?php
$lang ['admin'] ['config'] ['default'] = array(
	'head' => 'Yapılandırma',
	'descr' => 'FlatPress kurulumunuzu özelleştirin ve yapılandırın.',
	'submit' => 'Değişiklikleri Kaydet',

	'sysfset' => 'Genel Sistem Bilgileri',
	'syswarning' => '<big>Uyarı!</big> Bu bilgiler kritik öneme sahiptir ve doğru olması gerekir, aksi takdirde FlatPress düzgün çalışmayabilir.',
	'blog_root' => '<strong>FlatPress kurulumunun tam adresi</strong>. Not: ' . //
		'genellikle bunu düzenlemeniz gerekmez, ancak dikkatli olun, çünkü bunun doğru olup olmadığını kontrol edemeyiz.',
	'www' => '<strong>Blog adresi</strong>. Blogunuzun URL\'si, alt dizinler dahil.<br>' . //
		'örneğin: http://www.mydomain.com/flatpress/ (sonundaki eğik çizgi gereklidir)',

	// ------
	'gensetts' => 'Genel ayarlar',
	'adminname' => 'Yönetici Adı',
	'adminpassword' => 'Yeni parola',
	'adminpasswordconfirm' => 'Parolayı tekrar girin',
	'blogtitle' => 'Blog başlığı',
	'blogsubtitle' => 'Blog alt başlığı',
	'blogfooter' => 'Blog alt bilgisi',
	'blogauthor' => 'Blog yazarı',
	'startpage' => 'Bu web sitesinin ana sayfası',
	'stdstartpage' => 'benim blogum (varsayılan)',
	'blogurl' => 'Blog URL\'si',
	'blogemail' => 'Blog e-posta adresi',
	'notifications' => 'Bildirimler',
	'mailnotify' => 'Yorumlar için e-posta bildirimini etkinleştir',
	'blogmaxentries' => 'Sayfa başına yazı sayısı',
	'langchoice' => 'Dil',

	'intsetts' => 'Uluslararası ayarlar',
	'utctime' => '<abbr title="Universal Coordinated Time">UTC</abbr> saat dilimi',
	'timeoffset' => 'Saat farkı',
	'hours' => 'saat',
	'timeformat' => 'Zaman için varsayılan format',
	'dateformat' => 'Tarih için varsayılan format',
	'dateformatshort' => 'Tarih için varsayılan format (kısa)',
	'output' => 'Çıktı',
	'charset' => 'Karakter seti',
	'charsettip' => 'Blogunuzda yazdığınız karakter seti (UTF-8 ' . //
		'<a class="hint" href="https://wiki.flatpress.org/doc:techfaq#character_encoding" target="_blank" title="FlatPress hangi karakter kodlama standartlarını destekler?"> göz atmanız tavsiye edilir</a>).'
);

$lang ['admin'] ['config'] ['default'] ['msgs'] = array(
	1 => 'Yapılandırma başarıyla kaydedildi.',
	2 => 'Yönetici değiştirildi. Şu anda çıkış yapacaksınız.',
	-1 => 'Yapılandırmayı kaydederken bir hata oluştu.'
);

$lang ['admin'] ['config'] ['default'] ['error'] = array(
	'www' => 'Blog adresi geçerli bir URL olmalıdır',
	'title' => 'Bir başlık belirtmelisiniz',
	'email' => 'E-posta geçerli bir formatta olmalıdır',
	'maxentries' => 'Geçerli bir yazı (gönderi) sayısı girmediniz',
	'timeoffset' => 'Geçerli bir zaman farkı girmediniz! Ondalık sayı kullanabilirsiniz (örneğin 2sa30" => 2.5)',
	'timeformat' => 'Zaman için bir format dizesi girmelisiniz',
	'dateformat' => 'Tarih için bir format dizesi girmelisiniz',
	'dateformatshort' => 'Tarih için bir format dizesi girmelisiniz (kısa)',
	'charset' => 'Bir karakter seti kodu girmelisiniz',
	'lang' => 'Seçtiğiniz dil mevcut değil',
	'admin' => 'Yönetici adı yalnızca harfler, rakamlar ve 1 alt çizgi içerebilir.',
	'password' => 'Parola en az 6 karakter olmalı ve boşluk içermemelidir.',
	'confirm_password' => 'Parolalar uyuşmuyor.'
);
?>

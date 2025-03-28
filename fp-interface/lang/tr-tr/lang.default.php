<?php
$lang = array();

$lang ['main'] = array(
	'nextpage' => 'Sonraki Sayfa &raquo;',
	'prevpage' => '&laquo; Önceki Sayfa',
	'entry' => 'Gönderi',
	'entries' => 'Gönderiler',
	'static' => 'Statik Sayfa',
	'preview' => 'Düzenle / Önizle',

	'filed_under' => 'Kategorisine eklendi ',

	'add_entry' => 'Gönderi Ekle',
	'add_comment' => 'Yorum Ekle',
	'add_static' => 'Statik Sayfa Ekle',

	'btn_edit' => 'Düzenle',
	'btn_delete' => 'Sil',

	'nocomments' => 'Yorum ekle',
	'comment' => '1 yorum',
	'comments' => 'yorumlar',

	'rss' => 'RSS beslemesine abone ol',
	'atom' => 'Atom beslemesine abone ol'
);

$lang ['search'] = array(
	'head' => 'Arama',
	'fset1' => 'Arama kriterlerini girin',
	'keywords' => 'Anahtar kelime',
	'onlytitles' => 'Sadece başlıklar',
	'fulltext' => 'Tam metin',

	'fset2' => 'Tarih',
	'datedescr' => 'Aramanızı belirli bir tarihe bağlayabilirsiniz. Bir yıl, bir yıl ve ay veya tam bir tarih seçebilirsiniz. ' . 
		'Bütün veritabanını aramak için boş bırakın.',

	'fset3' => 'Kategorilerde Ara',
	'catdescr' => 'Hepsini aramak için hiçbirini seçmeyin',

	'fset4' => 'Aramaya Başla',
	'submit' => 'Ara',

	'headres' => 'Arama Sonuçları',
	'descrres' => '<strong>%s</strong> için yapılan arama aşağıdaki sonuçları döndürdü:',
	'descrnores' => '<strong>%s</strong> için yapılan arama hiçbir sonuç döndürmedi.',

	'moreopts' => 'Daha fazla seçenek',

	'searchag' => 'Tekrar Ara'
);

$lang ['search'] ['error'] = array(
	'keywords' => 'En az bir anahtar kelime belirtmelisiniz'
);

$lang ['staticauthor'] = array(
	'published_by' => 'Yayımlayan',
	'on' => 'tarihinde'
);

$lang ['entryauthor'] = array(
	'posted_by' => 'Gönderen',
	'at' => 'tarihinde'
);

$lang ['entry'] = array();
$lang ['entry'] ['flags'] = array();

$lang ['entry'] ['flags'] ['long'] = array(
	'draft' => '<strong>Taslak gönderi</strong>: gizli, yayımlanmayı bekliyor',
	'commslock' => '<strong>Yorumlar kilitli</strong>: bu gönderi için yorumlar kapalı'
);

$lang ['entry'] ['flags'] ['short'] = array(
	'draft' => 'Taslak',
	'commslock' => 'Yorumlar kapalı'
);

$lang ['entry'] ['categories'] = array(
	'unfiled' => 'Sınıflandırılmamış'
);

$lang ['404error'] = array(
	'subject' => 'Bulunamadı',
	'content' => '<p>Üzgünüz, istediğiniz sayfa bulunamadı</p>'
);

// Login
$lang ['login'] = array(
	'head' => 'Giriş',
	'fieldset1' => 'Kullanıcı adı ve parolanızı girin',
	'user' => 'Kullanıcı adı:',
	'pass' => 'Parola:',
	'fieldset2' => 'Giriş yap',
	'submit' => 'Giriş',
	'forgot' => 'Parolanızı mı unuttunuz?'
);

$lang ['login'] ['success'] = array(
	'success' => 'Giriş yaptınız.',
	'logout' => 'Çıkış yaptınız.',
	'redirect' => '5 saniye içinde yönlendirileceksiniz.',
	'opt1' => 'Ana sayfaya geri dön',
	'opt2' => 'Yönetim Alanına git',
	'opt3' => 'Yeni gönderi ekle'
);

$lang ['login'] ['error'] = array(
	'user' => 'Bir kullanıcı adı girmeniz gerekmektedir.',
	'pass' => 'Bir parola girmeniz gerekmektedir.',
	'match' => 'Parola yanlış.',
	'timeout' => 'Lütfen 30 saniye bekleyin ve tekrar deneyin.'
);

$lang ['comments'] = array(
	'head' => 'Yorum ekle',
	'descr' => 'Aşağıdaki formu doldurarak yorumunuzu ekleyebilirsiniz',
	'fieldset1' => 'Kullanıcı bilgileri',
	'name' => 'Ad (*)',
	'email' => 'E-posta:',
	'www' => 'Web:',
	'cookie' => 'Beni hatırla',
	'fieldset2' => 'Yorumunuzu ekleyin',
	'comment' => 'Yorum (*):',
	'fieldset3' => 'Gönder',
	'submit' => 'Ekle',
	'reset' => 'Sıfırla',
	'success' => 'Yorumunuz başarıyla eklendi',
	'nocomments' => 'Bu gönderiye henüz yorum yapılmadı',
	'commslock' => 'Bu gönderi için yorumlar devre dışı bırakıldı'
);

$lang ['comments'] ['error'] = array(
	'name' => 'Bir ad girmeniz gerekmektedir',
	'email' => 'Geçerli bir e-posta girmeniz gerekmektedir',
	'www' => 'Geçerli bir URL girmeniz gerekmektedir',
	'comment' => 'Bir yorum girmeniz gerekmektedir'
);

$lang ['postviews'] = array(
	'views' => 'görüntülenme',
);

$lang ['date'] ['month'] = array(
	'Ocak',
	'Şubat',
	'Mart',
	'Nisan',
	'Mayıs',
	'Haziran',
	'Temmuz',
	'Ağustos',
	'Eylül',
	'Ekim',
	'Kasım',
	'Aralık'
);

$lang ['date'] ['month_abbr'] = array(
	'Oca',
	'Şub',
	'Mar',
	'Nis',
	'May',
	'Haz',
	'Tem',
	'Ağu',
	'Eyl',
	'Eki',
	'Kas',
	'Arl'
);

$lang ['date'] ['weekday'] = array(
	'Pazar',
	'Pazartesi',
	'Salı',
	'Çarşamba',
	'Perşembe',
	'Cuma',
	'Cumartesi'
);

$lang ['date'] ['weekday_abbr'] = array(
	'Paz',
	'Pts',
	'Sal',
	'Çar',
	'Per',
	'Cum',
	'Cmt'
);
?>

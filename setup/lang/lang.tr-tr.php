<?php
/*
 * LangId: Turkish
 */
$lang ['setup'] = array(
	'setup' => 'Kurulum'
);

$lang ['locked'] = array(
	'head' => 'Kurulum durduruldu',
	'descr' => 'Görünüşe göre kurulum işlemini zaten başlatmışsınız, çünkü <code>%s</code> adlı kilit dosyasını kurulum konumunda bulduk.

		Kurulum işlemini yeniden başlatmanız gerekliyse, lütfen ilk olarak bu dosyayı silin.

		<strong >Hatırlatma!</strong> <code>setup.php</code> dosyasını ve <code>setup/</code> dizinini sunucunuzda tutmanız güvenli değildir, bunları silmenizi öneririz!

		<ul>
		<li><a href="%s">Tamam, beni bloguma geri götür</a></li>
		<li><a href="%s">Dosyayı sildim, kurulumu yeniden başlat</a></li>
		</ul>'
);

$lang ['err'] = array(
	'setuprun1' => 'Kurulum sürüyor.',

	'setuprun2' => 'Kurulum işlemi başlamış durumda: Eğer yöneticiyseniz, yeniden başlatmak için ',
	'setuprun3' => ' adlı dosyayı silebilirsiniz.',
	'writeerror' => 'Yazım yanlışları',

	'fpuser1' => ' geçersiz bir kullanıcı adı. ' . //
		'Kullanıcı adı yalnızca harfler ve rakamlardan oluşmalı, boşluk içermemelidir.',
	'fpuser2' => ' geçersiz bir kullanıcı adı. ' . //
		'Kullanıcı adı yalnızca harfler, sayılar ve 1 adet altçizgi içerebilir.',
	'fppwd' => 'Parola en az 6 karakter olmalı ve boşluk içermemelidir.',
	'fppwd2' => 'Parolalar eşleşmiyor.',
	'email' => ' geçersiz bir e-posta adresi.',
	'www' => ' geçersiz bir URL.',
	'error' => '<p><big>Hata!</big> ' . //
		'Form işlenirken şu hatalar ortaya çıktı:</p><ul>'
);

$lang ['step1'] = array(
	'head' => 'Flatpress\'e hoşgeldiniz!',
	'descr' => '<strong>FlatPress</strong>\'i seçtiğiniz için teşekkür ederiz.

		Yepyeni blogunuz ile eğlenmeye başlamadan önce, sormamız gereken birkaç soru var.

		Merak etmeyin, çok kısa sürecek!',
	'descrl1' => 'Dilinizi seçin.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Listede yok mu?</a>',
	'descrlang' => 'Eğer bu listede dilinizi göremiyorsanız, bu sürüm için  <a href="https://wiki.flatpress.org/res:language" target="_blank" rel="external">bir dil paketi</a> olup olmadığına bakabilirsiniz:

		<pre>%s</pre>

		Dil paketini <code>flatpress/</code>\'e kurmak için, sunucunuza yükleyin ve tüm dil dosyalarının üzerine yazın, sonra <a href="./setup.php">bu kurulumu yeniden başlatın</a>.',
	'descrw' => 'FlatPress\in çalışması için gereken <strong>tek şey</strong>  <em>yazma izni olan</em> bir dizindir.

		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => 'Kullanıcı oluştur',
	'descr' => 'Neredeyse tamamlandı, şu bilgileri doldurun:',
	'fpuser' => 'Kullanıcı adı',
	'fppwd' => 'Parola',
	'fppwd2' => 'Parolayı yeniden girin',
	'www' => 'Anasayfa',
	'email' => 'E-Posta'
);

$lang ['step3'] = array(
	'head' => 'Tamamlandı',
	'descr' => '<strong>Bu öykünün sonu</strong>.

		İnanabiliyor musunuz?

		Ve evet, haklısınız: <strong>öykü daha yeni başlıyor</strong>, ama <strong>devamını yazıp yazmamak size kalıyor</strong>!

		<ul>
		<li>İsterseniz <a href="%s">anasayfanız nasıl görünüyor bir göz atın</a></li>
		<li>Haydi! <a href="%s">Hemen giriş yapın!</a></li>
		<li>Bizimle paylaşmak istediğiniz birşey mi var? <a href="https://www.flatpress.org/" target="_blank" rel="external">FlatPress.org\'a gidin!</a></li>
		</ul>

		Ve FlatPress\i seçtiğiniz için teşekkürler!'
);

$lang ['buttonbar'] = array(
	'next' => 'İleri >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Menü';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]Anasayfa[/url]
[*][url=?paged=1]Blog[/url]
[*][url=static.php?page=about]Hakkında[/url]
[*][url=contact.php]İletişim[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'FlatPress\'e hoşgeldiniz!';
$lang ['samplecontent'] ['entry'] ['content'] = 'Bu, [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url]\'in bazı özelliklerini göstermek için yayınlanmış örnek bir yazıdır.

Daha fazla etiketini kullanarak bir "bağantı" yaratabilir ve giriş alıntısından yazının tam halini içeren sayfaya gidebilirsiniz.

[more]


[h4]Biçimlendirme[/h4]

İçeriğinizi biçimlendirmek için kullanılan varsayılan yöntem [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]BBcode[/url]\'dur (forum kodu ya da tartışma panosu kodu). BBCode gönderilerinizi biçimlendirmenin kolay bir yoludur. Yaygın kodların çoğunu kullanabilirsiniz. Örneğin [b]kalın[/b] yapmak için [b] (html: strong), [i]italik[/i] yapmak için [i] (html: em) ve benzeri.

[quote]Ayrıca sevdiğiniz alıntıları göstermek için [b]alıntı[/b] blokları da vardır.[/quote]


[code]\'code\' kod parçalarınızı eşaralıklı yazıtipiyle biçimlendirilmiş halde gösterir.
Ayrıca
   girintili içerikleri de destekler.[/code]

Ayrıca img ve url etiketleri de özel seçeneklere sahiptir. Daha fazla bilgi edinmek için [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]FlatPress-Wiki\'yi[/url] inceleyebilirsiniz.


[h4]Yazılar (gönderiler) ve Statik Sayfalar[/h4]

Bu bir yazıdır, [url=static.php?page=about]Hakkında[/url] ise bir [b]statik sayfa[/b]dır. Statik sayfa, yorum yapılamayan ve blogun normal gönderileriyle birlikte görünmeyen bir yazıdır.

Statik sayfalar, genel bilgi sayfaları oluşturmak için kullanışlıdır. Bu sayfalardan birini ziyaretçileriniz için [b]başlangıç sayfası[/b] yapabilirsiniz. Yani FlatPress ile blog olmayan bir site de yapabilirsiniz. Statik sayfanızı başlangıç sayfası yapmak için [b]seçenekler paneli[/b] yönetim alanında [url=admin.php]yönetici paneli[/url]nde bulunmaktadır.


[h4]Eklentiler[/h4]

FlatPress gayet özelleştirilebilir ve [url=https://wiki.flatpress.org/doc:plugins:standard target=_blank rel=external]eklentiler[/url] ile gücünü arttırabilir. Hatta BBCode\'un kendisi bir eklenti olarak çalışır.

Size FP\'nin iyi gizlenmiş özellikleri ve püf noktalarını göstermek için daha fazla içerik yarattık :)

İşte içeriklerinizi kabul etmeye hazır iki [b]statik sayfa[/b]:
[list]
[*][url=static.php?page=about]Hakkımda[/url]
[*][url=static.php?page=menu]Menü[/url] (bu sayfadaki bağlantıların yan menünüzde de görüneceğini unutmayın - işte bu [b]blockparser widget[/b]ının büyüsü. Daha fazla bilgi için [url=https://wiki.flatpress.org/doc:faq target=_blank rel=external]SSS[/url]\'ları inceleyin!)
[/list]

[b]PhotoSwipe eklentisi[/b] ile artık resimlerinizi çok daha kolay bir şekilde yerleştirebilirsiniz. Resimler, metinle çevrelenmiş şekilde, ya da float="left" (sola hizalı) ya da float="right" (sağa hizalı) olarak tek bir resim olarak yerleştirilebilir.
Ayrıca, ziyaretçilerinize tüm galerileri sunmak için "galeri" öğesini de kullanabilirsiniz. Ne kadar kolay çalıştığını [url=https://wiki.flatpress.org/res:plugins:photoswipe target=_blank rel=external]buradan öğrenebilirsiniz[/url].

[h4]Widget\'lar[/h4]

Yan menülerde sabit bir öğe yoktur. Bu metni çevreleyen menülerde bulacağınız tüm öğeler konumlandırılabilir ve çoğu da özelleştirilebilir. Bazı temalar yönetim alanında bir panel arayüzü de sağlar.  

Bu öğelere [b]widget[/b] denir. Widget\'lar hakkında daha fazla bilgi ve [url=https://wiki.flatpress.org/doc:tips:widgets target=_blank rel=external]güzel efektler elde etmek için ipuçları[/url] için [url=https://wiki.flatpress.org/ target=_blank rel=external]wiki[/url]\'yi inceleyebilirsiniz.


[h4]Temalar[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
FlatPress-Leggero teması ile 3 farklı stil şablonuna sahip olacaksınız - klasik tarzdan modern tarza kadar. Bu şablonlar, kendinize ait bir şeyler yaratmak için harika bir başlangıçtır.


[h4]Daha fazlasını öğrenmek ister misiniz?[/h4]

Daha fazlasını öğrenmek ister misiniz?

[list]
[*][url=https://www.flatpress.org/?x target=_blank rel=external]Resmi blogu[/url]nu takip ederek FlatPress dünyasında neler olup bittiğini öğrenin.
[*][url=https://forum.flatpress.org/ target=_blank rel=external]Forum[/url]\'u ziyaret edin, destek alın ve sohbet edin.
[*][b]Harika temalar[/b] edinmek için [url=https://wiki.flatpress.org/res:themes target=_blank rel=external]diğer kullanıcıların gönderilerini[/url] keşfedin!
[*][url=https://wiki.flatpress.org/res:plugins target=_blank rel=external]Eklentiler[/url]i keşfedin.
[*][url=https://wiki.flatpress.org/res:language target=_blank rel=external]Dil paketi[/url] edinin.
[*]FlatPress\'i [url=https://twitter.com/FlatPress target=_blank rel=external]X (Twitter)[/url] ve [url=https://fosstodon.org/@flatpress target=_blank rel=external]Mastodon[/url] üzerinden takip edebilirsiniz.
[/list]


[h4]Nasıl destek olabilirim?[/h4]

[list]
[*]Projeye [url=https://www.flatpress.org/home/static.php?page=donate target=_blank rel=external]küçük bir bağışta[/url] bulunarak destek olun.
[*]Hataları bildirmek ya da iyileştirme önerileri sunmak için: [url=https://www.flatpress.org/contact/ target=_blank rel=external]Bize ulaşın[/url].
[*]FlatPress gelişimine [url=https://github.com/flatpressblog/flatpress target=_blank rel=external]GitHub[/url] üzerinden katkıda bulunun.
[*]FlatPress\'i ya da belgelerini [url=https://wiki.flatpress.org/res:language target=_blank rel=external]kendi dilinize çevirin[/url].
[*]Bilginizi paylaşın ve diğer FlatPress kullanıcılarıyla [url=https://forum.flatpress.org/ target=_blank rel=external]forum[/url]da bağlantı kurun.
[*]İnsanlara bahsedin ve hakkında konuşun! :)
[/list]


[h4]Şimdi ne yapmalı?[/h4]

Artık [url=login.php]giriş yapın[/url] yönetim alanına gidin ve yazı eklemeye başlayın!

Tadını çıkarın! :)

[i][url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url] Ekibi[/i]

';

$lang ['samplecontent'] ['about'] ['subject'] = 'Hakkında';
$lang ['samplecontent'] ['about'] ['content'] = 'Kendiniz hakkında bir şeyler yazın burada. ([url=admin.php?p=static&action=write&page=about]Beni düzenle![/url])';

$lang ['samplecontent'] ['privacy-policy'] ['subject'] = 'Gizlilik Politikası';
$lang ['samplecontent'] ['privacy-policy'] ['content'] = 'Kimi ülkelerde, örneğin Akismet Antispam hizmetini kullanıyorsanız, ziyaretçilerinize bir gizlilik politikası bilgilendirmesi sunmanız gerekebilir. Ayrıca, ziyaretçi iletişim formunu veya yorum fonksiyonunu kullanabiliyorsalar, başka bir gizlilik politikası bilgilendirmesi daha gerekebilir.

[b]İpucu:[/b] İnternette bu konu ile ilgili birçok şablon ve oluşturucu bulunmaktadır.

Bunları buraya ekleyebilirsiniz. ([url=admin.php?p=static&action=write&page=privacy-policy]Beni düzenle![/url])

CookieBanner eklentisini etkinleştirirseniz, ziyaretçileriniz iletişim formunda ve yorum fonksiyonunda doğrudan bu sayfaya gidebilecektir.
';
?>

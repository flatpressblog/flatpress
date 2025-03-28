<?php
$lang ['admin'] ['widgets'] ['submenu'] ['default'] = 'Widget\'ları Yönet';
$lang ['admin'] ['widgets'] ['submenu'] ['raw'] = 'Widget\'ları Yönet (düzmetin)';

/* default action */
$lang ['admin'] ['widgets'] ['default'] = array(
	'head' => 'Widget\'ları Yönet',

	'descr' => 'Bir <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="Widget nedir?">' . //
		'Widget</a>, veri görüntüleyebilen ve kullanıcı ile etkileşimde bulunabilen dinamik bir bileşendir.' . //
		'<strong>Temalar</strong>, blogunuzun görünümünü değiştirmek için tasarlanmışken, Widget\'lar ' . //
		'<strong>görünümü</strong> ve işlevselliği <strong>geliştirir</strong>.</p>' . //

		'<p>Widget\'lar, temanızda özel alanlara sürüklenebilir ve bu alanlara <strong>WidgetSet\'ler</strong> denir.' . //
		'WidgetSet\'lerin sayısı ve isimleri, seçtiğiniz temaya göre değişebilir.</p>' . //

		'<p>FlatPress, çeşitli widget\'lar ile gelir: giriş için yardımcı olan widget\'lar, arama kutusu widget\'ları ve benzeri.</p>' . //

		'<p>Her bir Widget, bir <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="Widget nedir?">plugin</a> tarafından tanımlanır.',

	'availwdgs' => 'Mevcut Widget\'lar',
	'trashcan' => 'Silmek için buraya bırakın',

	'themewdgs' => 'Bu tema için WidgetSet\'ler',
	'themewdgsdescr' => 'Şu anda seçtiğiniz tema, aşağıdaki widget setlerini kullanmanıza olanak tanır',
	'oldwdgs' => 'Diğer widget setleri',
	'oldwdgsdescr' => 'Aşağıdaki widget setleri, yukarıda listelenen widget setlerine ait gibi görünmüyor. Başka bir temadan geriye kalanlar olabilir.',

	'submit' => 'Değişiklikleri Kaydet',
	'drop_here' => 'Buraya sürükleyin'
);

$lang ['admin'] ['widgets'] ['default'] ['stdsets'] = array(
	'top' => 'Üst bar',
	'bottom' => 'Alt bar',
	'left' => 'Sol bar',
	'right' => 'Sağ bar'
);

$lang ['admin'] ['widgets'] ['default'] ['msgs'] = array(
	1 => 'Yapılandırma kaydedildi',
	-1 => 'Kaydetme sırasında bir hata oluştu, lütfen tekrar deneyin'
);

/* "raw" panel */
$lang ['admin'] ['widgets'] ['raw'] = array(
	'head' => 'Widget\'ları Yönet (<em>ham düzenleyici</em>)',
	'descr' => 'Bir <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="Widget nedir?">' . //
		'Widget</a>, <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="Plugin nedir?">' . //
		'Plugin</a> bir görsel elemandır ve blog sayfalarınızda bazı özel alanlarda (<em>widgetset\'ler</em>) kullanılabilir.</p>' . //
		'<p>Bu <strong>düzmetin</strong> düzenleyicidir; bazı ileri düzey kullanıcılar veya JavaScript kull(a)namayan kişiler bunu tercih edebilir.',

	'fset1' => 'Düzenleyici',
	'fset2' => 'Değişiklikleri Uygula',
	'submit' => 'Uygula'
);

$lang ['admin'] ['widgets'] ['raw'] ['msgs'] = array(
	1 => 'Yapılandırma kaydedildi',
	-1 => 'Kaydederken bir hata oluştu. Bunun birkaç nedeni olabilir: dosyanızda sözdizimi hataları olabilir.'
);

/* system errors */
$lang ['admin'] ['widgets'] ['errors'] = array(
	'generic' => '<strong>%s</strong> adlı widget kaydedilmedi ve atlandı. ' . //
 		'Plugin <a href="admin.php?p=plugin">plugin panelinde</a> etkin mi?'
);
?>

<?php
$lang ['admin'] ['plugin'] ['submenu'] = array (
	'default' => 'Eklentileri Yönet'
);

/* ana eklenti paneli */
$lang ['admin'] ['plugin'] ['default'] = array(
	'head' => 'Eklentileri Yönet',
	'enable' => 'Aktifleştir',
	'disable' => 'Pasifleştir',
	'descr' => 'Bir <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="Eklenti nedir?">Eklenti</a>, FlatPress\'in yeteneklerini genişletebilen bir bileşendir.</p>' . //
		'<p>Eklentileri <code>fp-plugins/</code> dizinine yükleyerek kurabilirsiniz.</p><p>Bu panel, eklentileri aktifleştirmenizi ve pasifleştirmenizi sağlar.',
	'name' => 'Ad',
	'description' => 'Açıklama',
	'author' => 'Yazar',
	'version' => 'Sürüm',
	'action' => 'Eylem'
);

$lang ['admin'] ['plugin'] ['default'] ['msgs'] = array(
	1 => 'Yapılandırma kaydedildi',
	-1 => 'Kaydedilirken bir hata oluştu. Bu, çeşitli nedenlerle olabilir: dosyanızda sözdizimi hataları olabilir.'
);

/* sistem hataları */
$lang ['admin'] ['plugin'] ['errors'] = array(
	'head' => 'Eklentileri yüklerken aşağıdaki hatalarla karşılaşıldı:',
	'notfound' => 'Eklenti bulunamadı. Atlandı.',
	'generic' => 'Hata kodu %d'
);
?>

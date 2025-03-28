<?php
$lang ['plugin'] ['qspam'] = array(
	'error' => 'HATA: Yorumda yasaklı kelimeler bulundu'
);

$lang ['admin'] ['plugin'] ['submenu'] ['qspam'] = 'QuickSpamFilter';
$lang ['admin'] ['plugin'] ['qspam'] = array(
	'head' => 'QuickSpam Yapılandırması',
	'desc1' => 'Bu kelimeleri içeren yorumlara izin verme (her birini bir satıra yazın):',
	'desc2' => '<strong>Uyarı:</strong> Bir kelime başka bir kelimenin içinde olsa bile yorum engellenecektir. (Örneğin, "old" kelimesi "<em>old</em>" içinde de eşleşir)',
	'options' => 'Diğer seçenekler',
	'desc3' => 'Kötü Kelime Sayısı',
	'desc3pre' => 'Şu kadar kötü kelime içeren yorumları engelle:',
	'desc3post' => ' kötü kelime.',
	'submit' => 'Yapılandırmayı kaydet',
	'msgs' => array(
		1 => 'Kötü kelimeler başarıyla kaydedildi.',
		-1 => 'Kötü kelimeler kaydedilemedi.'
	)
);
?>

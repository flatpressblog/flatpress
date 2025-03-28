<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Bakım';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Bakım',
	'descr' => 'Bir şeylerin ters gittiğini düşündüğünüzde buraya gelin, belki burada bir çözüm bulursunuz. Ancak bu her zaman işe yaramayabilir.',
	'opt0' => '&laquo; Ana menüye geri dön',
	'opt1' => 'İndeksi yeniden oluştur',
	'opt2' => 'Tema ve şablon önbelleğini temizle',
	'opt3' => 'Verimli çalışma için yetkilendirmeleri geri yükle',
	'opt4' => 'PHP hakkında bilgi göster',
	'opt5' => 'Güncellemeleri kontrol et',

	'chmod_info' => 'Eğer izinler <strong>geri yüklenemediyse</strong>, dosya/dizin sahipliği büyük ihtimalle web sunucusunun sahipliği ile farklıdır.<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>İzinler</th>
					<th>' . FP_CONTENT . '</th>
					<th>Çekirdek</th>
					<th>Diğerleri</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Dosyalar</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>Dizinler</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => 'Tüm yetkilendirmeler başarıyla güncellendi.',
	'opt3_error' => 'Yetkilendirmeleri güncellenirken hata oluştu:'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'İşlem tamamlandı',
	-1 => 'İşlem başarısız oldu'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Güncellemeler',
	'list' => '<ul>
		<li>FlatPress sürümünüz <big>%s</big></li>
		<li>FlatPress için son kararlı sürüm <big><a href="%s">%s</a></big></li>
		<li>FlatPress için son kararsız sürüm <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Bildirim:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'Güncellemeler mevcut!',
	2 => 'Zaten en son sürüme sahipsiniz',
	-1 => 'Güncellemeleri alırken hata oluştu'
);
?>

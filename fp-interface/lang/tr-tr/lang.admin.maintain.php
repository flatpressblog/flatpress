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
	'opt6' => 'APCu önbellek durumu',

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

$lang ['admin'] ['maintain'] ['apcu'] = array(
	'head' => 'APCu önbellek',
	'descr' => 'APCu paylaşılan bellek kullanımı ve önbellek verimliliğine genel bakış.',
	'status_heading' => 'Sezgisel durum',
	'status_good' => 'Önbellek, mevcut iş yükü için uygun boyutta görünüyor.',
	'status_bad' => 'Yüksek kaçırma oranı veya çok düşük boş bellek: APCu önbelleği çok küçük veya aşırı parçalanmış olabilir.',
	'hit_rate' => 'Isabet oranı',
	'free_mem' => 'Boş bellek',
	'total_mem' => 'Toplam paylaşılan bellek',
	'used_mem' => 'Kullanılan bellek',
	'avail_mem' => 'Kullanılabilir bellek',
	'memory_type' => 'Bellek türü',
	'memory_type_unknown' => 'n/a',
	'num_slots' => 'Yuva sayısı',
	'num_hits' => 'Isabet sayısı',
	'num_misses' => 'Kaçırma sayısı',
	'cache_type' => 'Önbellek türü',
	'cache_user_only' => 'Kullanıcı veri önbelleği',
	'legend_good' => 'Yeşil: yapılandırma sağlıklı görünüyor (yüksek isabet oranı, makul boş bellek).',
	'legend_bad' => 'Kırmızı: önbellek baskı altında (çok sayıda ıskalama veya neredeyse hiç boş bellek yok).',
	'no_apcu' => 'APCu bu sunucuda etkinleştirilmiş görünmüyor.',
	'back' => '&laquo; Bakım sayfasına geri dön',
	'clear_fp_button'=> 'FlatPress APCu girişlerini temizle',
	'clear_fp_confirm' => 'Tüm APCu girişlerini gerçekten silmek istiyor musunuz? Bu, FlatPress\'in APCu önbelleklerini temizleyecektir.',
	'clear_fp_result'=> '%d APCu girişi silindi.',
	'msgs' => array(
		1  => 'FlatPress APCu girişleri silindi.',
		2  => 'APCu girişi bulunamadı.',
		-1 => 'APCu kullanılamıyor veya erişilemiyor; hiçbir şey silinmedi.'
	)
);
?>

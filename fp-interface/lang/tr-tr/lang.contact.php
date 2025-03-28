<?php
$baseurl = BLOG_BASEURL;

$lang ['contact'] = array(
	'head' => 'Bize Ulaşın',
	'descr' => 'Aşağıdaki formu kullanarak bize geri bildirim gönderebilirsiniz. Yanıt almak isterseniz e-posta adresinizi eklemeyi unutmayın.',
	'fieldset1' => 'Kullanıcı bilgileri',
	'name' => 'Ad (*)',
	'email' => 'E-posta:',
	'www' => 'Web:',
	'cookie' => 'Beni hatırla',
	'fieldset2' => 'İletiniz',
	'comment' => 'İleti (*):',
	'fieldset3' => 'Gönder',
	'submit' => 'Gönder',
	'reset' => 'Sıfırla',
	'loggedin' => 'Giriş yapmışsınız 😉. <a href="' . $baseurl . 'login.php?do=logout">Çıkış yapın</a> veya <a href="' . $baseurl . 'admin.php">Yönetim alanına</a> gidin.'
);

$lang ['contact'] ['notification'] = array(
	'name' => 'Ad:',
	'email' => 'E-posta:',
	'www' => 'Web:',
	'content' => 'İleti:',
	'subject' => 'İleti gönderildi '
);

$lang ['contact'] ['error'] = array(
	'name' => 'Bir ad girmeniz gerekmektedir',
	'email' => 'Geçerli bir e-posta girmeniz gerekmektedir',
	'www' => 'Geçerli bir URL girmeniz gerekmektedir',
	'content' => 'Bir ileti girmeniz gerekmektedir'
);

$lang ['contact'] ['msgs'] = array(
	1 => 'İleti başarıyla gönderildi',
	-1 => 'İleti gönderilemedi'
);
?>

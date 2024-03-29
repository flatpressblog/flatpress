<?php

function check_step() {
	global $lang;

	$validate = validate();

	if ($validate) {

		$vl = & $lang ['samplecontent'];
		entry_save(array(
			'subject' => $vl ['entry'] ['subject'],
			'content' => $vl ['entry'] ['content'],
			'date' => date_time(),
			'version' => system_ver(),
			'author' => 'FlatPress'
		));

		if (!static_exists('menu')) {
			static_save(array(
				'subject' => $vl ['menu'] ['subject'],
				'content' => $vl ['menu'] ['content'],
				'date' => time(),
				'version' => system_ver(),
				'author' => 'FlatPress'
			), 'menu');
		}
		if (!static_exists('about')) {
			static_save(array(
				'subject' => $vl ['about'] ['subject'],
				'content' => $vl ['about'] ['content'],
				'date' => time(),
				'version' => system_ver(),
				'author' => 'FlatPress'
			), 'about');
		}
		if (!static_exists('privacy-policy')) {
			static_save(array(
				'subject' => $vl ['privacy-policy'] ['subject'],
				'content' => $vl ['privacy-policy'] ['content'],
				'date' => time(),
				'version' => system_ver(),
				'author' => 'FlatPress'
			), 'privacy-policy');
		}
	}

	return $validate;
}

?>

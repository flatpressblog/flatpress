<?php

function check_step() {
	global $lang;

	$validate = validate();

	if ($validate) {

		$vl = & $lang ['samplecontent'];
		entry_save(array(
			'subject' => $vl ['entry'] ['subject'],
			'content' => $vl ['entry'] ['content'],
			'date' => time(),
			'version' => system_ver()
		));

		if (!static_exists('menu')) {
			static_save(array(
				'subject' => $vl ['menu'] ['subject'],
				'content' => $vl ['menu'] ['content'],
				'date' => time(),
				'version' => system_ver()
			), 'menu');
		}
		if (!static_exists('about')) {
			static_save(array(
				'subject' => $vl ['about'] ['subject'],
				'content' => $vl ['about'] ['content'],
				'date' => time(),
				'version' => system_ver()
			), 'about');
		}
	}

	return $validate;
}

?>

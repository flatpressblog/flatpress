<?php

function migrate_old() {

	function create_defaults() {
		global $fp_config;

		if (!file_exists(SEOMETA_DEFAULT_DIR . 'metatags.ini')) {
			$metatags = "[meta]\n";
			$metatags .= "description=\n";
			$metatags .= "keywords=\n";
			$metatags .= "noindex=0\n";
			$metatags .= "nofollow=0\n";
			$metatags .= "noarchive=0\n";
			$metatags .= "nosnippet=0\n";
			@io_write_file(SEOMETA_DEFAULT_DIR . 'metatags.ini', $metatags);
		}

		if (!file_exists(SEOMETA_STATIC_DIR . 'blog_metatags.ini')) {
			$metatags = "[meta]\n";
			$metatags .= "description=" . $fp_config ['general'] ['subtitle'] . "\n";
			$metatags .= "keywords=blog\n";
			$metatags .= "noindex=0\n";
			$metatags .= "nofollow=0\n";
			$metatags .= "noarchive=0\n";
			$metatags .= "nosnippet=0\n";
			@io_write_file(SEOMETA_STATIC_DIR . 'blog_metatags.ini', $metatags);
		}

		if (!file_exists(SEOMETA_STATIC_DIR . 'contact_metatags.ini')) {
			$lang = lang_load('contact');
			$metatags = "[meta]\n";
			$metatags .= "description=" . $lang ['contact'] ['head'] . "\n";
			$metatags .= "keywords=" . $lang ['contact'] ['head'] . "\n";
			$metatags .= "noindex=0\n";
			$metatags .= "nofollow=0\n";
			$metatags .= "noarchive=0\n";
			$metatags .= "nosnippet=0\n";
			@io_write_file(SEOMETA_STATIC_DIR . 'contact_metatags.ini', $metatags);
		}
	}

	function rmigrate_entries($cur) {
		if (is_dir($cur)) {
			$files = scandir($cur);
			foreach ($files as $file) {
				if ($file != '.' && $file != '..') {
					if (is_dir("$cur/$file")) {
						rmigrate_entries("$cur/$file");
					}
					if ($file == 'metatags.ini') {
						$pi = pathinfo_filename($cur);
						$src = "$cur/$file";
						$dst = SEOMETA_ENTRY_DIR . $pi . "_metatags.ini";
						if (file_exists($src) && !file_exists($dst)) {
							echoPre($src . "\n" . $dst);
							copy($src, $dst);
							if (file_exists($dst)) {
								unlink($src);
							}
						}
					}
				}
			}
		}
	}

	// entries
	@io_write_file(SEOMETA_ENTRY_DIR . 'dummy', '');
	for($i = 05; $i < 20; $i++) {
		$cur = CONTENT_DIR . $i;
		rmigrate_entries($cur);
	}
	$cur = CONTENT_DIR . 'drafts';
	rmigrate_entries($cur);

	// statics
	@io_write_file(SEOMETA_STATIC_DIR . 'dummy', '');
	$cur = CONTENT_DIR . "static-meta";
	if (is_dir($cur)) {
		$files = scandir($cur);
		foreach ($files as $file) {
			if ($file != '.' && $file != '..' && is_file("$cur/$file")) {
				$src = "$cur/$file";
				$dst = SEOMETA_STATIC_DIR . $file;
				if (file_exists($src) && !file_exists($dst)) {
					$r = copy($src, $dst);
					if (file_exists($dst)) {
						unlink($src);
					}
				}
			}
		}
		if (is_empty_dir($cur)) {
			rrmdir($cur);
		}
	}
	// categories
	@io_write_file(SEOMETA_CATEGORY_DIR . 'dummy', '');
	$cur = CONTENT_DIR . "cat-meta";
	if (is_dir($cur)) {
		$files = scandir($cur);
		foreach ($files as $file) {
			if ($file != '.' && $file != '..' && is_file("$cur/$file")) {
				$src = "$cur/$file";
				$dst = SEOMETA_CATEGORY_DIR . $file;
				if (file_exists($src) && !file_exists($dst)) {
					copy($src, $dst);
					if (file_exists($dst)) {
						unlink($src);
					}
				}
			}
		}
		if (is_empty_dir($cur)) {
			rrmdir($cur);
		}
	}

	create_defaults();
}
?>

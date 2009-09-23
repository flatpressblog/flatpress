<?php

$fp_config = array (
  'general' => 
  array (
    'www' => 'http://localhost',
    'title' => 'FlatPress',
    'subtitle' => 'My FlatPress blog',
    'footer' => '',
    'author' => 'FlatPress Team',
    'email' => 'webmaster@localhost.com',
    'startpage' => NULL,
    'maxentries' => '5',
    'notify' => true,
    'theme' => 'leggero',
    'style' => 'leggero',
    'blogid' => 'fpdefid',
    'charset' => 'utf-8',
  ),
  'locale' => 
  array (
    'timeoffset' => '2',
    'timeformat' => '%H:%M:%S',
    'dateformat' => '%A, %B %e, %Y',
    'dateformatshort' => '%Y-%m-%d',
    'charset' => 'utf-8',
    'lang' => 'en-us',
  ),
  'plugins' =>
  array (
	'blockparser' =>
	array (
		'pages' =>
			array(
				'menu',
				'about',
			),
	),
  ),
);

?>

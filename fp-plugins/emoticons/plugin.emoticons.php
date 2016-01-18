<?php
/*
Plugin Name: Emoticons
Plugin URI: http://www.flatpress.org/
Description: Adds emoticons to FlatPress
Author: NoWhereMan, Marc Thibeault
Version: 1.0
Author URI: http://www.nowhereland.it/
*/ 
 
/*
	put your imgs in FP_CONTENT/emoticons/
	(tipically FP_CONTENT is fp-content/ )
*/
 
define('EMOTICONS_DIR', BLOG_BASEURL . FP_CONTENT . 'emoticons/');
 
function plugin_emoticons_filter($string) {
	static $EMOTICONS = array(
		":)"	=> "smile.png",
		":-)"	=> "smile.png",
		":("	=> "sad.png",
		":-("	=> "sad.png",
		";)"	=> "wink.png",
		";-)"	=> "wink.png",
		":P"	=> "tongue.png",
		":cool:"	=> "cool.png",
		":D"	=> "mrgreen.png",
		":-D"	=> "mrgreen.png",
		":S"	=> "confused.png",
		":|" => "neutral.png",
		":-|" => "neutral.png",
		":blush:" => "blush.png",
		":confused:"	=> "confused.png",
		":rage:" => "rage.png",
		":cookie:" => "cookie.png",
	);
 
	$ed = EMOTICONS_DIR;
 
	foreach ($EMOTICONS as $emo => $img) {
		$string = str_replace(
			"$emo",
			"<img src=\"{$ed}{$img}\" id=\"emoticon\" alt=\"{$emo}\" />",
			$string
		);
	}
        return $string;
 
}

//Hooks
//For entry content
add_filter(	'the_content',	'plugin_emoticons_filter'	);
//For comment content
add_filter(	'comment_text',	'plugin_emoticons_filter'		);
 
?>
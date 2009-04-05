<?php
/*
Plugin Name: ReadMore
Plugin URI: http://www.nowhereland.it/
Description: ReadMore plugin. Chops a lengthy entry and appends a "read more" link :)
Author: NoWhereMan
Version: 0.703
Author URI: http://www.nowhereland.it/
*/ 


	// $MODE specifies when you want to chop your entry
	
	// 'auto' will chop your entry at the value
	// specified in $CHOP_AT
	
	// 'manual' will chop your entry only when a [more] tag is found in
	// the content
	
	// 'semiauto' will chop your entry at the [more] tag. If no such a tag
	// is found, the entry is chopped at the value specified in $CHOP_AT
	
	// 'sentence' will chop your entry after $CHOP_AT sentences
	
	// WARNING! 'auto' and 'semiauto' modes need improvements! unclosed tags 
	// at the chop point will probably result in validation errors!
	// If you're willing to improve it (using a quick but efficient algorithm
	// feel free and then let us know :) )
	
	// we recommend using $MODE = 'manual' (SPB legacy behaviour :) )
	
	

function plugin_readmore_main($string) {
	
	global $fp_params;
	
	$MODE = 'manual'; 
			
	$CHOP_AT = 4; // characters or sentences
	
		global $fpdb;
		$q =& $fpdb->getQuery();
	
	
	if (($q && !$q->single) && !isset($_GET['page'])) {
		if ($q)
			list($id) = $q->getLastEntry();
		else 
			$id ='';
		
		if ($MODE == 'auto' || $MODE == 'semiauto' ) {
			if (strlen($string) > $CHOP_AT) {
				
				return substr($string, 0, $CHOP_AT).
				"&hellip; <span class=\"readmore\"><a href=\"".
					get_permalink($id)."#readmore-{$id}\">[Read More...]</a></span>";
			}
		} 
		
		if ($MODE == 'manual' || $MODE == 'semiauto' ) {
			if (($p = strpos($string, '[more]'))!==false){
				return substr($string, 0, $p).
				"<span class=\"readmore\"><a href=\""
					.get_permalink($id)."#readmore-{$id}\">[Read More...]</a></span>";
			}
		} elseif ($MODE == 'sentence') {
			$matches = array();
			if ($v=preg_match_all('|[.!?]\s|', $string, $matches, PREG_OFFSET_CAPTURE)) {
				
				if (count($matches[0]) > $CHOP_AT) {
					$string = substr($string, 0, $matches[0][$CHOP_AT-1][1]).
					". <span class=\"readmore\"><a href=\""
					.get_permalink($id)."#readmore-{$id}\">[Read More...]</a></span>";
				}
			}
		}
		
	}
	
	if (($q && $q->single) || isset($fp_params['entry'])) {
		$string = str_replace('[more]', "<a id=\"readmore-{$fp_params['entry']}\"></a>", $string);
	}
	
	return $string;
}

add_filter('the_content', 'plugin_readmore_main', 1);


?>

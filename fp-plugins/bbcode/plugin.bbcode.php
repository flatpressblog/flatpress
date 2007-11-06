<?php
/*
Plugin Name: BBCode
Version: 1.0
Plugin URI: http://flatpress.sf.net
Description: Allows using <a href="http://www.phpbb.com/phpBB/faq.php?mode=bbcode">BBCode</a> markup; provides automatic integration with lightbox
Author: Hydra, NoWhereMan
Author URI: http://flatpress.sf.net
*/

define('BBCODE_ESCAPE_HTML', true);
define('BBCODE_ENABLE_COMMENTS', false);
define('BBCODE_USE_EDITOR', true);
define('BBCODE_URL_MAXLEN', 40);



require plugin_getdir('bbcode') . '/inc/stringparser_bbcode.class.php';

function plugin_bbcode_defines() {

	$funcs = explode(',', ini_get('disable_functions'));
	
	if (!file_exists('getfile.php')) {
		define('BBCODE_USE_WRAPPER', false);
		return;
	}
	
	if (in_array('readfile',$funcs)) {
		define('BBCODE_USE_WRAPPER', false);
	} else {
		define('BBCODE_USE_WRAPPER', true);
	}
	
}

plugin_bbcode_defines();



function plugin_bbcode_style() {
	echo "\n<!-- bbcode plugin -->\n";
	echo '<link rel="stylesheet" type="text/css" href="'.plugin_geturl('bbcode') . '/res/bbcode.css" />', "\n";
	if (BBCODE_USE_EDITOR)
		echo '<script type="text/javascript" src="'.plugin_geturl('bbcode') . '/res/editor.js"></script>', "\n";
	echo "<!-- end of bbcode plugin -->\n";
}
add_action('wp_head', 'plugin_bbcode_style');


function bbcode_remap_url(&$d) {

	// NWM: "attachs/" is interpreted as a keyword, and it is translated to the actual path of ATTACHS_DIR
	// CHANGE! we use the getfile.php script to mask the actual path of the attachs dir!
	
	
	if (strpos($d, ':')===false) { // if is relative url
		
		// absolute path, relative to this server
		if ($d{0} == '/') {
			// BLOG_BASEURL contains a trailing slash in the end
			// if $d begins with a slash, we first strip it
			// otherwise the string would look like 
			// http://mysite.com/flatpress//path/you/entered
			//                           ^^^ ugly double slash :P
			$d = BLOG_BASEURL . substr($d, 1);
		}
		
		if (substr($d, 0, 8) == 'attachs/') {
				
				if (BBCODE_USE_WRAPPER) {
					$d = "getfile.php?f=" . basename($d) . "&amp;dl=true";
				} else {
					$d = substr_replace ($d, ATTACHS_DIR, 0, 8 );
				}
				
				return true;
				
		}
		
		if (substr($d, 0, 7) == 'images/') {
		
			$d = substr_replace ($d, IMAGES_DIR, 0, 7 );
			$d = BBCODE_USE_WRAPPER? ("getfile.php?f=" . basename($d)) : $d;
			
		}
		
		
		return true;
		
	}
	
	
	if (strpos($d, 'www.')===0) {
		$d = 'http://' . $d;
	}
		
	return false;
}



function do_bbcode_url ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') {
		return true;
	}
	
	// the code was specified as follows: [url]http://.../[/url]
	
	
    if (!isset ($attributes['default'])) {
    
    		/* cut url if longer than > BBCODE_URL_MAXLEN */
    		
    		$url = $content;
    		
            if (($l = strlen($url))>BBCODE_URL_MAXLEN) {
            	$t = (int)(BBCODE_URL_MAXLEN/2);
                $content = substr($url, 0, $t) . 
                		' &hellip; ' . 
                		substr($url, $l-$t);
            }
            
            
    } else  {
	// else the code was specified as follows: [url=http://.../]Text[/url]
		$url = $attributes['default'];
	}

	$the_url = ($local = bbcode_remap_url($url))? (BLOG_BASEURL . $url) : $url ;
	$content = $content; 
	
	
	
   	return '<a ' .
		(!$local? 'class="externlink" title="Go to '.$the_url.'" ' : '')   	
   	 . 'href="'. ($the_url).'"'.
	(isset($attributes['rel'])? ' rel="' . $attributes['rel'] . '"' : '') 
	.'>'.$content.'</a>';
}

// Function to include images
function do_bbcode_img ($action, $attributes, $content, $params, $node_object) {
	
	if ($action == 'validate') {
		return true;
	}
	
	if (!isset($attributes['default'])) return '[No valid img specified]';
	
	$absolutepath = $actualpath = $attributes['default'];
	$image_is_local = false;
	
	
	// NWM: "images/" is interpreted as a keyword, and it is translated to the actual path of IMAGES_DIR
	$image_is_local = bbcode_remap_url($actualpath);		
	
	
	
	$float = '';
	$popup_start = '';
	$popup_end = '';
	
	$img_size = array();
	
	// let's disable socket functions for remote files
	// slow remote servers may otherwise lockup the system
	if ($image_is_local) {
		$img_size = @getimagesize($actualpath);
		$absolutepath = BLOG_BASEURL . $actualpath;
	}
	
		
	$orig_w = $width = isset($img_size[0])? $img_size[0] : 0;
	$orig_h = $height = isset($img_size[1])? $img_size[1] : 0;
	$thumbpath =  null;
	
	
	
	$scalefact = 0; // default: resize to 0, which means leaving it as it is, as width and hiehgt will be ignored ;)
	// scale attribute has priority over width and height
	// if scale is set popup is set to true automatically, unless it is explicitly set to false
	if (isset($attributes['scale'])) {
		
		if  (substr($attributes['scale'], -1, 1) == '%') {
				$val = substr($attributes['scale'], 0,-1); // NN%, we ignore %
			} else { $val = $attributes['scale'] ; }
			
		$scalefact = $val / 100.0;
		
		$width = (int)($scalefact*$width); 
		$height= (int)($scalefact*$height);
		
	// if both width and height are set, we assume proportions are ok
	}elseif (isset($attributes['width']) && isset($attributes['height'])) {
		$width = (int)$attributes['width']; 
		$height = (int)$attributes['height'];
	// if width or height are set we calc proportions
	}elseif (isset($attributes['width'])) { 
		$scalefact = ($attributes['width'] / $orig_w);
		$width = (int) $attributes['width'];
		$height = (int) ($scalefact*$orig_h);
		
	}elseif (isset($attributes['height'])) {
		$scalefact = ($attributes['height'] / $orig_h);
		$height = (int) $attributes['height'];
		$width = (int) ($scalefact*$orig_w);
	}
	
	
	if ($height<$orig_h) $attributes['popup']=true;



	if ($height != $orig_h) {
		/**
		 * bbcode_img_scale_filter($actualpath, $img_props, $newsize)
		 *
		 */
	
		$thumbpath = apply_filters(
					'bbcode_img_scale', 
					$actualpath, 
					$img_size, 
					array($width,$height)
		);	
	}

	
	
	$alt = $title = basename($actualpath);
	
	
	if (isset($attributes['popup']) && ($attributes['popup'])) {
		$pop_width = ($orig_w) ? $orig_w : 800;
		$pop_height = ($orig_h) ? $orig_h : 600;
		
		$popup = ' onclick="' . 
			"Popup=window.open('$absolutepath','Popup','toolbar=no,location=no,status=no,".
			"menubar=no,scrollbars=yes,resizable=yes,width=$pop_width,height=$pop_height');"
			.' return false;"';
		
		/**
		 *	plugin hook, here lightbox attachs
		 */
		
		$popup = apply_filters('bbcode_img_popup', $popup, $absolutepath);
		
		$popup_start = ($attributes['popup']=='true') ?  
			'<a title="View image \''.$title.'\'" href="'. /* BLOG_BASEURL . $actualpath.*/
				$absolutepath .
				'"'.$popup.'>' : '';
		$popup_end = ($attributes['popup']=='true') ?  '</a>' : '';
	}
	

		
	
	$img_width = ($width)? ' width="'.$width.'"' : '';
	$img_height = ($height) ? ' height="'.$height.'"' : '' ;
	
	if (isset ($attributes['float'])) {
		$float = ($attributes['float']=='left' || $attributes['float']=='right') ? 
				 ' class="float'.$attributes['float'].'"' : ' class="center"';
	}
	
	
	return $popup_start.'<img src="' .
			($thumbpath ? (BLOG_BASEURL . $thumbpath) : ($absolutepath)). // $attributes['default'])
		'" alt="'.$alt. '" '. ($popup_start? '' : ' title="'.$title.'" ' ).
		$float.$img_width.$img_height.' />'.$popup_end;
}



// Function for embedding videos
function do_bbcode_video($action, $attr, $content, $params, $node_object) {

	if ($action=='validate') {
		return true;
	}
	
	
	$vurl = parse_url($attr['default']);
	if (isset($attr['type'])) {
		$type = $attr['type'];
	} else {
	
		// is it http://www.MYSITE.com  or http://MYSITE.com ?
		$web = explode('.', $vurl['host']);
		array_pop($web);
		$type = isset($web[1])? $web[1] : $web[0];		

	}
	
	$query = utils_kexplode($vurl['query'], '=&');
	
	$the_url = null;
	$others = '';
	switch ($type) {
		case 'google':
			$the_url = "http://video.google.com/googleplayer.swf?docid={$query['docid']}";
			$others = '<param name="FlashVars" value="playerMode=embedded" />';
			break;
		case 'youtube':
			$the_url = "http://youtube.com/v/{$query['v']}";
			break;
		case 'default':
			$the_url = null;
	}
	
	if ($the_url) {
	
		$width = isset($attr['width'])? $attr['width'] : '400';
		$height = isset($attr['height'])? $attr['height'] : '326';
		$float = isset($attr['float'])? "float: {$attr['float']}" : '';
			
	
		return
			'<object type="application/x-shockwave-flash" '.
			"height=\"$height\" width=\"$width\" ".
			($float? ('style="' . $float . '" ') : '').
			'data="' . $the_url . '"><param name="movie" value="' . $the_url . '" />'
			. $others . '</object>';
	}

	return '[unsupported video]';
	
}




// Function to return code
function do_bbcode_code ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') {
		return true;
	}
	
	$temp_str = $content;
	$temp_str = str_replace( '<br />', chr(10), $temp_str );
	$temp_str = str_replace( chr(10).chr(10), chr(10), $temp_str );
	$temp_str = str_replace( chr(32), '&nbsp;', $temp_str );
	
	$a = '';
	
	if (function_exists('plugin_syntaxhighlighter_foot')) {
		if (isset($attributes['default'])) {
			$a = $attributes['default'];
			
			$p = explode(':', $a);
			plugin_syntaxhighlighter_add($p[0]);
			
		}
	}
	
	return "<pre". ($a? " class=\"{$a}\" " : '') .">$temp_str</pre>";
}

// Function to return html

function do_bbcode_html ($action, $attributes, $content, $params, $node_object) {
	
	if ($action == 'validate') {
		return true;
	}
	
	static $count = 0;
	
	// NWM: life is hell -___-'
	
	if(!isset($GLOBALS['BBCODE_TEMP_HTML']))
		$GLOBALS['BBCODE_TEMP_HTML'] = array();
	
	$GLOBALS['BBCODE_TEMP_HTML'][$count] = $content;
	
	$str = "<!-- #HTML_BLOCK_{$count}# -->";
	$count++;
	
	return $str;
}

function do_bbcode_color ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') {
		return true;
	}
	return "<span style=\"color: {$attributes['default']}\">$content</span>";
	
}
function do_bbcode_size ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') {
		return true;
	}
	return "<span style=\"font-size: {$attributes['default']}\">$content</span>";
	
}


function do_bbcode_align($action, $attr, $content, $params, $node_object) {
	return "<div style=\"text-align: {$attr['default']};\">{$content}</div>";
}

function do_bbcode_list ($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') {
		return true;
	}
	
	if (isset($attributes['default']) &&
		$attributes['default']=='#') {
		
		$list = 'ol';
		
	} else {
		$list = 'ul';
	}
		
	return "<$list>$content</$list>";
}


function &plugin_bbcode_init() {

	static $bbcode = null;
	
	if (!defined('BBCODE_INIT_DONE')) {
	
		$bbcode = new StringParser_BBCode ();
		
		// If you set it to false the case-sensitive will be ignored for all codes
		$bbcode->setGlobalCaseSensitive (false);
		$bbcode->setMixedAttributeTypes(true);
		
		$BBCODE_TAGS_SIMPLE = array(
		
			'b'=>'strong',
			'i'=>'em',
			'quote'=>'blockquote',
			'blockquote',
			'strong', 
			'em', 
			'ins',
			'del',
			'hr','h2','h3','h4','h5','h6'
			// u for underlined: see below
		
		);
		
		foreach ($BBCODE_TAGS_SIMPLE as $key => $val) {
		
			if (!is_numeric($key)) {
				$bbtag = $key;
				$htmltag = $val;
			} else {
				$htmltag = $bbtag = $val;
			}
			
			$bbcode->addCode (	$bbtag, 
						'simple_replace', 
						null, 
						array ('start_tag' => "<$htmltag>", 
							'end_tag' => "</$htmltag>"),
						'inline', 
						array ('listitem', 
							'block', 
							'inline', 
							'link'), 
						array ()
					);
						
			$bbcode->setCodeFlag (	$bbtag, 
						'closetag', 
						BBCODE_CLOSETAG_MUSTEXIST
					);
		
		}
		
		/* other tags */
		
		$bbcode->addCode ('u', 
						'simple_replace', 
						null, 
						array ('start_tag' => "<span style=\"text-decoration: underline\">", 
							'end_tag' => "</span>"),
						'inline', 
						array ('listitem', 
							'block', 
							'inline', 
							'link'), 
						array ()
					);
			
		
		
		$bbcode->addCode (
					'color', 
					'callback_replace', 
					'do_bbcode_color', 
					array('usecontent_param' => array ('default')),
					'inline', 
					array ('listitem', 'block', 'inline', 'link'), 
					array ());
					
		$bbcode->setCodeFlag ('color', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);
		
		
		
		
		$bbcode->addCode (
					'code', 
					'usecontent', 
					'do_bbcode_code', 
					array (),
					'inline', 
					array ('listitem', 'block', 'inline', 'link'), 
					array ());
					
		$bbcode->setCodeFlag ('code', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);

		
		
		$bbcode->addCode (	
					'html', 
					'usecontent', 
					'do_bbcode_html', 
					array (),
					'inline', 
					array ('listitem', 'block', 'inline', 'link'), 
					array ());
					
		$bbcode->setCodeFlag ('html', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);
		
		
		
		$bbcode->addCode (
					'url', 
					'callback_replace', 
					'do_bbcode_url', 
					array('usecontent_param' => array ('default', 'new')), 
					'link', array ('listitem', 'block', 'inline'), 
					array ('link')
		);
	
		
		$bbcode->addCode (
					'img', 
					'callback_replace_single', 
					'do_bbcode_img', 
					array ('usecontent_param' => 
						array ('default', 'float', 'alt', 
						'popup', 'width', 'height', 'title')
						),
					'image', array ('listitem', 'block', 
							'inline', 'link'), 
					array ());
		$bbcode->setCodeFlag ('img', 'closetag', 'BBCODE_CLOSETAG_FORBIDDEN');
		
		
		$bbcode->addCode (
						'video', 
						'callback_replace_single', 
						'do_bbcode_video', 
						array ('usecontent_param' => 
							array ('default', 'float', 'width', 'height')),
					  	'image', array ('listitem', 'block', 'inline', 'link'), 
					  	array ());
		$bbcode->setCodeFlag ('video', 'closetag', 'BBCODE_CLOSETAG_FORBIDDEN');
		
		
		
		$bbcode->addCode (	'list', 
					'callback_replace', 
					'do_bbcode_list', 
					array ('start_tag' => '<ul>', 'end_tag' => '</ul>'),
					'list', 
					array ('block', 'listitem'), 
					array ());
		$bbcode->setCodeFlag ('list', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);
		$bbcode->setCodeFlag ('list', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
		$bbcode->setCodeFlag ('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
		$bbcode->setCodeFlag ('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);
		
		
				
		$bbcode->addCode ('*', 'simple_replace', null, array ('start_tag' => '<li>', 'end_tag' => '</li>'),
						'listitem', array ('list'), array ());
		$bbcode->setCodeFlag ('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
		$bbcode->setCodeFlag ('*', 'paragraphs', false);
		
		
		$bbcode->addCode (
					'align', 
					'callback_replace', 
					'do_bbcode_align', 
					array ('usecontent_param' => 
						array ('default')
						),
					'block', array ('listitem', 'block', 
							'inline', 'link'), 
					array ());
		
		
			
		
		define('BBCODE_INIT_DONE', true);
		
		$bbcode = apply_filters('bbcode_init', $bbcode);
		
	} 
	
	return $bbcode;
	
}



function BBCode($text) {
	
	$bbcode =& plugin_bbcode_init();
	
	return $bbcode->parse($text);
}





function plugin_bbcode_toolbar() {

	$indexer = new fs_filelister(IMAGES_DIR);
	$imageslist = $indexer->getList();
	array_unshift($imageslist, '--');

	$indexer = new fs_filelister(ATTACHS_DIR);
	$attachslist = $indexer->getList();
	array_unshift($attachslist, '--');

	global $_FP_SMARTY;

	$_FP_SMARTY->assign('images_list', $imageslist);
	$_FP_SMARTY->assign('attachs_list', $attachslist);

	echo $_FP_SMARTY->fetch('plugin:bbcode/toolbar');


}


function plugin_bbcode_comment($text) {
	
	$bbcode = new StringParser_BBCode ();
	
	// If you set it to false the case-sensitive will be ignored for all codes
	$bbcode->setGlobalCaseSensitive (false);
	$bbcode->setMixedAttributeTypes(true);
	
	
	// [B]
	$bbcode->addCode ('b', 'simple_replace', null, array ('start_tag' => '<strong>', 'end_tag' => '</strong>'),
					  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
	// [STRONG]
	$bbcode->addCode ('strong', 'simple_replace', null, array ('start_tag' => '<strong>', 'end_tag' => '</strong>'),
					  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
	
	// [I]
	$bbcode->addCode ('i', 'simple_replace', null, array ('start_tag' => '<em>', 'end_tag' => '</em>'),
					  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
	// [EM]
	$bbcode->addCode ('em', 'simple_replace', null, array ('start_tag' => '<em>', 'end_tag' => '</em>'),
					  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
	// [INS]
	$bbcode->addCode ('ins', 'simple_replace', null, array ('start_tag' => '<ins>', 'end_tag' => '</ins>'),
					  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
	// [U]	
	$bbcode->addCode ('u', 'simple_replace', null, array ('start_tag' => '<ins>', 'end_tag' => '</ins>'),
					  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
	// [DEL]
	$bbcode->addCode ('del', 'simple_replace', null, array ('start_tag' => '<del>', 'end_tag' => '</del>'),
					  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
	// [STRIKE]
	$bbcode->addCode ('strike', 'simple_replace', null, array ('start_tag' => '<del>', 'end_tag' => '</del>'),
					  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
	// [BLOCKQUOTE]
	$bbcode->addCode ('blockquote', 'simple_replace', null, array ('start_tag' => '<blockquote><p>', 'end_tag' => '</p></blockquote>'),
					  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
	// [QUOTE]
	$bbcode->addCode ('quote', 'simple_replace', null, array ('start_tag' => '<blockquote><p>', 'end_tag' => '</p></blockquote>'),
					  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
	// [PRE]
	$bbcode->addCode ('pre', 'simple_replace', null, array ('start_tag' => '<pre>', 'end_tag' => '</pre>'),
					  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
	// [CODE]
	$bbcode->addCode ('code', 'usecontent', 'do_bbcode_code', array (),
					  'inline', array ('listitem', 'block', 'inline', 'link'), array ());

	return $bbcode->parse($text);

}

// this is for [html] tag postprocess

function plugin_bbcode_undoHtmlCallback($match) {
 	return clean_pre($match[1]); //builtin function (see core.wp-formatting)
	
}

function plugin_bbcode_undoHtml($text) {

	//return preg_replace_callback('|<!-- BEGOFHTML -->(.*)<!-- EOFHTML -->|sU', 'plugin_bbcode_undoHtmlCallback', $text);
	if (isset($GLOBALS['BBCODE_TEMP_HTML'])) {
		foreach ($GLOBALS['BBCODE_TEMP_HTML'] as $n => $content) {
			
			// html_entity_decode($content)
			$content = str_replace('&lt;', '<', $content);
			$content = str_replace('&gt;', '>', $content);
		
			$text = str_replace("<!-- #HTML_BLOCK_{$n}# -->", $content, $text);
		}
		
		$GLOBALS['BBCODE_TEMP_HTML'] = array();
		
	}
		
	return $text;
	
}


//add_filter('comment_text', 'plugin_bbcode_comment');

add_filter('title_save_pre', 'wp_specialchars',1);

if (BBCODE_ESCAPE_HTML)
	add_filter('content_save_pre', 'wp_specialchars',1);

add_filter('pre_comment_author_name', 'wp_specialchars');
add_filter('pre_comment_content', 'wp_specialchars');

add_filter('the_content', 'BBCode', 1);
add_filter('the_excerpt', 'BBCode', 1);

add_filter('the_content', 'plugin_bbcode_undoHtml', 30);

if (BBCODE_USE_EDITOR) {
	add_filter('editor_toolbar', 'plugin_bbcode_toolbar'); 
}

if (BBCODE_ENABLE_COMMENTS) {
	add_filter('comment_text', 'plugin_bbcode_comment', 1);
}

?>

<?php
/*
Plugin Name: Protected Mail Links
Version: 1.0
Plugin URI: http://software.azett.com
Description: Makes your visitors write you mails - but not the spam bots.
Author: azett
Author URI: http://software.azett.com
*/

/*
Adds a "mail" tag to FlatPress. Mail adress will be obfuscated for spam protection but mail link will work normally. Without JavaScript!
*/


// this will tell FlatPress to load the new tags at the very beginning 
add_filter('init', 'plugin_protectedmaillinks_tags');

function plugin_protectedmaillinks_tags() {
    $bbcode = plugin_bbcode_init(); //import the "global" bbcode object into current function
                                    // this way 
                                    // a) parsing is done only once, and by the official plugin
                                    // b) you create only ONE object, and therefore computation is quicker

    $bbcode->addCode (
        'mail',  // tag name: this will go between square brackets
        'callback_replace', // type of action: we'll use a callback function
        'plugin_custombbcode_mail', // name of the callback function
        array('usecontent_param' => array ('default')), // supported parameters: "default" is [acronym=valore]
        'inline', // type of the tag, inline or block, etc
        array ('listitem', 'block', 'inline', 'link'), // type of elements in which you can use this tag
        array ()); // type of elements where this tag CAN'T go (in this case, none, so it can go everywhere)
    $bbcode->setCodeFlag ('mail', 'closetag', BBCODE_CLOSETAG_MUSTEXIST); // a closing tag must exist [/tag]

}

function plugin_custombbcode_mail($action, $attributes, $content, $params, $node_object) { 
    if ($action == 'validate') {
       // not used for now
       return true;
    }	
    
    // the code was specified as follows: [mail]user@example.org[/mail]
    if (!isset ($attributes['default'])) {
        return "<a href=\"".obfuscateAdress("mailto:".$content, 3)."\" class=\"maillink\">".obfuscateAdress($content, 3)."</a>";
    } else  {
		// else the code was specified as follows: [mail=user@example.org]link text[/url]
        return "<a href=\"".obfuscateAdress("mailto:".$attributes['default'], 3)."\" class=\"maillink\">".$content."</a>";
	}
}

// ------------------------------------------------------------------------------
// obfuscate mail adresses
// ------------------------------------------------------------------------------
// Thanks for spam-me-not.php to Rolf Offermanns!
// Spam-me-not in JavaScript: http://www.zapyon.de
    function obfuscateAdress($originalString, $mode) {
        // $mode == 1            decimal ASCII
        // $mode == 2            hexadecimal ASCII
        // $mode == 3            decimal/hexadecimal ASCII randomly mixed
        $encodedString = "";
        $nowCodeString = "";
        $randomNumber = -1;

        $originalLength = strlen($originalString);
        $encodeMode = $mode;

        for ( $i = 0; $i < $originalLength; $i++) {
            if ($mode == 3) $encodeMode = rand(1,2);
            switch ($encodeMode) {
                case 1: // Decimal code
                    $nowCodeString = "&#" . ord($originalString[$i]) . ";";
                    break;
                case 2: // Hexadecimal code
                    $nowCodeString = "&#x" . dechex(ord($originalString[$i])) . ";";
                    break;
                default:
                    return "ERROR: wrong encoding mode.";
            }
            $encodedString .= $nowCodeString;
        }
        return $encodedString;
    }
?>
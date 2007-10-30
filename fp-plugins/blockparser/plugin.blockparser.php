<?php

/*
Plugin Name: BlockParser
Plugin URI: http://www.nowhereland.it/
Type: Block
Description: BlockParser plugin. Part of the standard distribution ;) This allow you to use simple non-plugin custom blocks :) 
Author: NoWhereMan real_nowhereman at user dot sf dot net
Version: 1.0
Author URI: http://www.nowhereland.it/
*/ 

	//define('BLOCKS_DIR', CONTENT_DIR . 'blocks/');
	
	// as a default blocks_dir == static_dir
	// so you can edit blocks using the static editor!!
	define('BLOCKS_DIR', STATIC_DIR);


function plugin_blockparser_parse($blockid) {
	
	if ($f_contents = io_load_file(BLOCKS_DIR . $blockid . EXT)) {
		$contents = utils_kexplode($f_contents);
		return array_change_key_case($contents, CASE_LOWER);
	}
	// else:
	return false;
}

register_widget('blockparser', 'BlockParser', 'plugin_blockparser_widget', 1);

function plugin_blockparser_widget($blockid) {
	
	if ($contents = plugin_blockparser_parse($blockid)) {
		$contents['subject'] = apply_filters('the_title', $contents['subject']);
		$contents['content'] = apply_filters('the_content', $contents['content']);
		$contents['id'] = "widget-bp-$blockid";
		return $contents;
	}
	
	return array(	'subject' => 'BlockParser::Error',
			'content' => "<ul><li>Error parsing block $blockid; file may not exist</li></ul>" );
}


function plugin_blockparser_init() {
	global $fp_config;
	
	
	// for instance: 
	// $fp_config['plugins']['blockparser']['pages'] = array('menu');
	// (these will) be registered from the panel
	
	// in this case functions are just a convenient way
	// to create new instances of the plugin_blockparser_widget() function... 
	
	// this would suggest to use an object, though :B
	// anyway the result is the same...
	
	/*
	if (isset($fp_config['plugins']['blockparser'])) {
		$pgs = $fp_config['plugins']['blockparser']['pages'];
		foreach ($pgs as $page) {
			register_widget($page, create_function('', "return plugin_blockparser_widget('$page');"));		
		}
	}
	*/
	
}

add_action('init', 'plugin_blockparser_init');

if (false and class_exists('AdminPanelAction')){

	class admin_widgets_blockparser extends AdminPanelAction { 
		
		var $langres = 'plugin:blockparser';
		
		function setup() {
			$this->smarty->assign('admin_resource', "plugin:blockparser/admin.plugin.blockparser");
		}
		
		function main() {
			global $fp_config;
			$this->smarty->assign_by_ref('enabledpages', $fp_config['plugins']['blockparser']);
			$this->smarty->assign('statics', $assign = static_getlist());
		}
		
		
		
		function onsubmit() {
			global $fp_config;
			
			if ($_POST['wp-apikey']){
				
				$fp_config['plugins']['akismet']['apikey'] = $_POST['wp-apikey'];
				
				config_save();
				
				$this->smarty->assign('success', 1);
			} else {
			 	$this->smarty->assign('success', -1);
			}
			
			
			// redirect to this panel (clears POSTDATA; 
			// 1 would redirect to the default 'action' for widget panel)
						
			return 2;
		}
		
	}

	admin_addpanelaction('widgets', 'blockparser', true);

}


?>

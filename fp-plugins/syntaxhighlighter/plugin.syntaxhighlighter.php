<?php
/*
Plugin Name: SyntaxHighlighter
Version: 1.0
Plugin URI: http://flatpress.sf.net
Description: <a href="http://www.dreamprojections.com/syntaxhighlighter/">dp.SyntaxHighlighter 1.4.0</a> (edited to work with pre, thanks to <a href="https://www.gertthiel.de/blog/archive/2005/11/25/dp-syntaxhighlighter-pre">Gert Thiel </a>)
Author: NoWhereMan
Author URI: http://flatpress.sf.net
*/


function plugin_syntaxhighlighter_add($lang=null) {
	static $scripts = array();
	
	$pdir=plugin_geturl('syntaxhighlighter');
	
	if ($lang) {
		switch ($lang) {
			case 'c': 
			case 'cpp': 
			case 'c++': 
				$scripts[] = "<script type=\"text/javascript\" src=\"{$pdir}res/shBrushCpp.js\"></script>\n"; break;
			case 'css': 
				$scripts[] = "<script type=\"text/javascript\" src=\"{$pdir}res/shBrushCss.js\"></script>\n"; break;
			case 'c#':
			case 'c-sharp':
			case 'csharp':
				$scripts[] = "<script type=\"text/javascript\" src=\"{$pdir}res/shBrushCSharp.js\"></script>\n"; break;
			case 'vb':
			case 'vb.net':
				$scripts[] = "<script type=\"text/javascript\" src=\"{$pdir}res/shBrushVb.js\"></script>\n"; break;
			case 'delphi':
			case 'pascal':
				$scripts[] = "<script type=\"text/javascript\" src=\"{$pdir}res/shBrushDelphi.js\"></script>\n"; break;

			case 'js':
			case 'jscript':
			case	'javascript':
				$scripts[] = "<script type=\"text/javascript\" src=\"{$pdir}res/shBrushJScript.js\"></script>\n"; break;
			case	'php':
				$scripts[] = "<script type=\"text/javascript\" src=\"{$pdir}res/shBrushPhp.js\"></script>\n"; break;
			case 'py':
			case	'python':
				$scripts[] = "<script type=\"text/javascript\" src=\"{$pdir}res/shBrushPython.js\"></script>\n"; break;
			case	'ruby':
				$scripts[] = "<script type=\"text/javascript\" src=\"{$pdir}res/shBrushRuby.js\"></script>\n"; break;
			case	'sql':
				$scripts[] = "<script type=\"text/javascript\" src=\"{$pdir}res/shBrushSql.js\"></script>\n"; break;
			case 'xml':
			case 'xhtml':
			case 'xslt':
			case 'html':
			case 'xhtml':
				"<script type=\"text/javascript\" src=\"{$pdir}res/shBrushXml.js\"></script>\n";
	
		}
		
		$scripts = array_unique($scripts);
		
	}
	return $scripts;

}


function plugin_syntaxhighlighter_head() {
	$pdir=plugin_geturl('syntaxhighlighter');
echo <<<SHL
	<!-- start of SHL -->
		
	<link rel="stylesheet" type="text/css" href="{$pdir}res/SyntaxHighlighter.css" />

	<!-- end of SHL -->
SHL;
	
}
add_action('wp_head', 'plugin_syntaxhighlighter_head');


function plugin_syntaxhighlighter_foot() {

	$syntaxes = implode(plugin_syntaxhighlighter_add(), "\n");

	$pdir=plugin_geturl('syntaxhighlighter');
	echo <<<SHLBOX
	<!-- start of SHL -->
		<script type="text/javascript" src="{$pdir}res/shCore.js"></script>
	
		$syntaxes
	
		<script type="text/javascript">  
			dp.SyntaxHighlighter.HighlightAll('code');  
		</script>  
	
	
	<!-- end of SHL -->
SHLBOX;
}
add_action('wp_footer', 'plugin_syntaxhighlighter_foot');



?>

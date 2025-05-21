<?php

	$lang['admin']['plugin']['submenu'] = array (
		'default'		=> 'Správa Pluginů'
	);
	
	/* main plugin panel */

	$lang['admin']['plugin']['default'] = array(
	
		'head'		=> 'Správa Pluginů',
		'enable'	=> 'Zapnout',
		'disable'	=> 'Vypnout',
		'descr'		=> '<a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins:standard" title="Co je Plugin?">'.
						'Plugin</a> je součást, která umožňuje rozšířit možnosti FlatPressu.</p>'.
						'<p>Pluginy je možno instalovat nahráním do složky <code>fp-plugins/</code></p> '.
						'<p>Tento panel umožňuje zapnout a vypnout pluginy.',
		'name'		=> 'Název',
		'description'	=> 'Popis',
		'author'	=> 'Autor',
		'version'	=> 'Verze',
		'action'	=> 'Akce',
	);
	
	$lang['admin']['plugin']['default']['msgs'] = array(
		1	=> 'Nastavení uložené',
		-1	=> 'Při pokuse uložit nastala chyba. Může být pro to několik důvodů: pravděpodobně váš soubor obsahuje chyby v syntaxi.',
	);
	
	/* system errors */
	
	$lang['admin']['plugin']['errors'] = array(
		'head'		=> 'Při nahrávání pluginů se vyskytli nasledující chyby:',
		'notfound'	=> 'Plugin se nenašel. Přeskočit.',
		'generic'	=> 'Chyba číslo %d',
	);
	
?>

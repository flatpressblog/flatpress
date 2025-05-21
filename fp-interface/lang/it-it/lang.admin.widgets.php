<?php

	$lang['admin']['widgets']['submenu']['default'] = 'Gestione Widget';
	$lang['admin']['widgets']['submenu']['raw'] 	= 'Gestione Widget (grezza)';

	/* default action */
	
	$lang['admin']['widgets']['default'] = array(
		'head'		=> 'Gestione Widget',
		
		'descr'		=> 	'Un <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:widgets" title="What is a Widget?">'.
						'Widget</a> è un componente dinamico che può visualizzare dati e interagire con l\'utente.
						Mentre i <strong>Temi</strong> sono fatti per cambiare l\'aspetto del blog, i Widgets 
						ne <strong>estendono</strong> le funzionalità e ne cambiano l\'aspetto.</p>

						<p>I Widget possono essere trascinati in aree specifiche del tuo tema chiamate 
						<strong>WidgetSets</strong>. Il numero e il nome dei WidgetSets possono variare a seconda 
						del tema che hai scelto.</p>

						<p>FlatPress viene fornito con parecchi widget: ci sono widget che aiutano con la connessione, per 
						visualizzare una casella di ricerca, ecc.</p>
						
						<p>Ogni Widget è definito da un <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a Widget?">plugin</a>.',
						
		'availwdgs'	=> 'Widget disponibili',
		'trashcan'	=> 'Trascina qui per eliminare',
		
		'themewdgs' => 'Widgetset per questo tema',
		'themewdgsdescr' => 'Il tema che hai attualmente selezionato ti consente di avere i seguenti widgetset',
		'oldwdgs'	=> 'Altri widgetset',
		'oldwdgsdescr' =>'I seguenti widgetset sembrano non appartenere ad alcuno dei '.
						'widgetset elencati qui sopra. Potrebbero essere dei rimasugli di un altro tema.',
		
		'submit'	=> 'Salva le modifiche',

	);
	
	$lang['admin']['widgets']['default']['stdsets'] = array(
		'top'		=> 'Barra superiore',
		'bottom'	=> 'Barra inferiore',
		'left'		=> 'Barra a sinistra',
		'right'		=> 'Barra a destra',
	);
	
	$lang['admin']['widgets']['default']['msgs'] = array(
		1	=> 'La configurazione è stata salvata',
		-1	=> 'Si è verficato un errore durante il salvataggio, riprova',
	);


	
	/* "raw" panel */	
	
	$lang['admin']['widgets']['raw'] = array(
		'head'		=> 'Gestione Widget (<em>editor grezzo</em>)',
		'descr'		=> 'Un <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a Widget?">'.
						'Widget</a> è un elemento visuale di un <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a plugin?">'.
						'Plugin</a> che puoi inserire in alcune aree speciali (the <em>widgetsets</em>) sulle pagine del tuo blog. </p>'.
						'<p>Questo è l\'editor <strong>grezzo</strong>; alcuni utenti avanzati o persone che '.
						'non possono installare JavaScript potrebbero preferirlo.',
						
		'fset1'		=> 'Editor',
		'fset2'		=> 'Applica le modifiche',
		'submit'	=> 'Applica',

	);
	
	
	$lang['admin']['widgets']['raw']['msgs'] = array(
		1	=> 'La configurazione è stata salvata',
		-1	=> 'Si è verificato un errore durante il salvataggio. Questo può succedere per varie ragioni: forse il tuo file contiene degli errori di sintassi.',
	);

		

	/* system errors */
		
	$lang['admin']['widgets']['errors'] = array(
		'generic'	=> 'Il widget chiamato <strong>%s</strong> non è registrato e sarà tralasciato. '.
 				'Si tratta del plugin abilitato nel <a href="admin.php?p=plugin">pannello dei plugin</a>?'

	);
	
?>

<?php

	$lang['admin']['plugin']['submenu'] = array (
		'default'		=> 'Gestione Plugin'
	);
	
	/* main plugin panel */

	$lang['admin']['plugin']['default'] = array(
	
		'head'		=> 'Gestione Plugin',
		'enable'	=> 'Abilita',
		'disable'	=> 'Disabilita',
		'descr'		=> 'Un <a class="hint" '.
						'href="http://wiki.flatpress.org/doc:plugins" title="What is a plugin?">'.
						'Plugin</a> è un componente che può espandere le capacità di FlatPress.</p>'.
						'<p>Puoi installare i plugin caricandoli nella cartella <code>fp-plugins/</code> '.
						'<p>Questo pannello ti consente di abilitare e disabilitare i plugin',
		'name'		=> 'Nome',
		'description'=>'Descrizione',
		'author'	=> 'Autore',
		'version'	=> 'Versione',
		'action'	=> 'Azione',
	);
	
	$lang['admin']['plugin']['default']['msgs'] = array(
		1	=> 'La configurazione è stata salvata con successo',
		-1	=> 'Si è verificato un errore durante il salvataggio. Questo può succedere per molte ragioni: forse il tuo file contiene errori di sintassi.',
	);
	
	/* system errors */
	
	$lang['admin']['plugin']['errors'] = array(
		'head'		=> 'Si sono verificati i seguenti errori durante il caricamento dei plugin:',
		'notfound'	=> 'Il plugin non è stato trovato. Lo tralascio.',
		'generic'	=> 'Errore numero %d',
	);
	
?>

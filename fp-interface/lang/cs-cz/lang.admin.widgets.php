<?php

	$lang['admin']['widgets']['submenu']['default'] = 'Správa Widgetů';
	$lang['admin']['widgets']['submenu']['raw'] 	= 'Správa Widgetů (raw)';

	/* default action */
	
	$lang['admin']['widgets']['default'] = array(
		'head'		=> 'Správa Widgetů',
		
		'descr'		=> 	'<a class="hint" '.
						'href="http://wiki.flatpress.org/doc:tips:widgets" title="Co je Widget?">'.
						'Widget</a> je dynamická položka, která může zobrazovat data a působí na užívatele.
						Zatím co <strong>motivy vzhledu</strong> jsou určené pro změnu vzhledu Vašeho blogu, widgety 
						<strong>rozšiřují</strong> vzhled a funkce.</p>

						<p>Widget lze přetáhnout do speciální oblasti, která se může lišit podle tématu vzhledu.</p>

						<p>FlatPress příchází s několika widgety, které pomáhají s přihlášením, zobrazením, vyhledáváním, atd. </p>',
						
		'availwdgs'	=> 'Dostupné Widgety',
		'trashcan'	=> 'Přetáhněte sem pro smazání',
		
		'themewdgs' => 'Widgety pro tento motiv',
		'themewdgsdescr' => 'K motivu, který jste právě vybrali, patří tyto widgety',
		'oldwdgs'	=> 'Salší widgety',
		'oldwdgsdescr' =>'Tento widget zřejmě nepatří mezi widgety uvedené výše.'.
						'Může to být pozůstatek z jiného motivu.',
		'submit'	=> 'Uložit změny',

	);
	
	$lang['admin']['widgets']['default']['stdsets'] = array(
		'top'		=> 'Horní lišta',
		'bottom'	=> 'Spodní lišta',
		'left'		=> 'Levá lišta',
		'right'		=> 'Pravá lišta',
	);
	
	$lang['admin']['widgets']['default']['msgs'] = array(
		1	=> 'Nastavení uložené',
		-1	=> 'Při pokusu uložit nastala chyba, zkuste to prosím znovu',
	);


	
	/* "raw" panel */	
	
	$lang['admin']['widgets']['raw'] = array(
		'head'		=> 'Správa Widgetů (<em>raw editor</em>)',
		'descr'		=> '<a class="hint" '.
						'href="http://wiki.flatpress.org/doc:tips:widgets" title="Co je Widget?">'.
						'Widget je vizuální prvek, který se vkládá ve speciálních oblastech (<em>widgetsets</em>) Vašeho blogu.'.
						'Jedná se o  <strong>surový</strong> editor; mohou ho preferovat někteří zkušení uživatelé nebo lidé, '.
						'kteří nechtějí (nemohou) používat JavaScript.',
						
		'fset1'		=> 'Editor',
		'fset2'		=> 'Použít změny',
		'submit'	=> 'Použít',

	);
	
	
	$lang['admin']['widgets']['raw']['msgs'] = array(
		1	=> 'Nastavení uložené',
		-1	=> 'Při pokusu uložit nastala chyba. Může být pro to několik důvodů: soubor pravděpodobně obsahuje chyby syntaxe.',
	);

		

	/* system errors */
		
	$lang['admin']['widgets']['errors'] = array(
		'generic'	=> 'Tento widget <strong>%s</strong> není registrovaný, a bude vynechaný. '.
 				'Je tento plugin zapnutý v <a href="admin.php?p=plugin">Správě pluginů?</a>?'

	);
	
?>

<?php

	$lang['admin']['static']['submenu'] = array(
		'list'		=> 'Správa statických stránek',
		'write'		=> 'Vytvořit statickou stránku'
	);

	
	/* main panel */
		
	$lang['admin']['static']['list'] = array(
	
		'head'		=> 'Statické stránky ',
		'descr'		=> 'Prosím vyberte stránku, kterou chcete upravit nebo <a href="admin.php?p=static&amp;action=write">přidejte novou</a>.',
	
		'sel'		=> 'Sel', // checkbox
		'date'		=> 'Datum',
		'name'		=> 'Stránka',
		'title'		=> 'Nadpis',
		'author'	=> 'Autor',
		
		'action'	=> 'Akce',
		'act_view'	=> 'Ukázat',
		'act_del'	=> 'Smazat',
		'act_edit'	=> 'Upravit'	
	);
	
	$lang['admin']['static']['list']['msgs'] = array(
		1	=> 'Stránka byla úspěšně uložena',
		-1	=> 'Při pokusu uložit stránku nastala chyba',
		2	=> 'Stránka byla úspěšně odstraněná',
		-2	=> 'Při pokusu smazat stránku nastala chyba',
	);

	/* write panel */

	$lang['admin']['static']['write'] = 
	array(
		'head'		=> 'Publikovať statickou stránku',
		'descr'		=> 'Upravit podobu publikované statické stránky',
		'fieldset1'	=> 'Upravit',
		'subject'	=> 'Předmět (*):',
		'content'	=> 'Obsah (*):',
		'fieldset2'	=> 'Potvrdit',
		'pagename'	=> 'Název stránky (*):',
		'submit'	=> 'Publikovat',
		'preview'	=> 'Zobrazit',

		'delfset'	=> 'Smazat',
		'deletemsg'	=> 'Smazat stránku',
		'del'		=> 'Smazat',
		'success'	=> 'Vaša stránka byla úspěšně publikovaná',
		'otheropts'	=> 'Další nastavení',
	);
	
	$lang['admin']['static']['write']['error'] = array(
		'subject'	=> 'Nemůžete poslat prázdný předmět',
		'content'	=> 'Nemůžete poslat prázdný příspěvek',
		'id'		=> 'Musíte poslat platné id'
	);
	
	
	/* delete action */	
	$lang['admin']['static']['delete'] = array(
		'head'		=> "Smazat stránku", 
		'descr'		=> 'Chystáte se smazat tyto stránky:',
		'preview'	=> 'Zobrazit',
		'confirm'	=> 'Opravdu chcete pokračovat?',
		'fset'		=> 'Smazat',
		'ok'		=> 'Ano, smazat stránku',
		'cancel'	=> 'Ne, zpět na statickou stránku',
		'err'		=> 'Uvedená stránka neexistuje',
	
	);
	
	
		
?>

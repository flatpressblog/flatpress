   <?php


	$lang['admin']['entry']['submenu'] = 
	array (
		'list'		=> 'Spravovat příspěvky',
		'write'		=> 'Přidat nový příspěvek',
		'cats'		=> 'Spravovat kategorie'
	);


	/* default action */
	
	$lang['admin']['entry']['list'] = 
	array(
		'head'		=> 'Správa příspěvků',
		'descr'		=> 'Prosím vyberte příspěvek, který chcete upravit nebo <a href="admin.php?p=entry&amp;action=write">přidejte nový</a>'.
					'<br /><a href="admin.php?p=entry&amp;action=cats">Upravit kategorie</a>',
		'filter'	=> 'Filtr: ',
		'nofilter'	=> 'Zobrazit vše',
		'filterbtn'	=> 'Použít filtr',
		'sel'		=> 'Sel', // checkbox
		'date'		=> 'Datum',
		'title'		=> 'Nadpis',
		'author'	=> 'Autor',
		'comms'		=> '#Komentářů', // comments
		'action'	=> 'Akce',
		'act_del'	=> 'Smazat',
		'act_view'	=> 'Zobrazit',
		'act_edit'	=> 'Upravit'
	);
	
	/* write action */
	$lang['admin']['entry']['write'] = 
	array(
		'head'		=> 'Napsat příspěvek',
		'descr'		=> 'Upravit formulář k napsání příspěvku',
		'uploader'	=> 'Nahrát soubor(y)',
		'fieldset1'	=> 'Upravit',
		'subject'	=> 'Předmět (*):',
		'content'	=> 'Obsah (*):',
		'fieldset2'	=> 'Odeslat',
		'submit'	=> 'Publikovat',
		'preview'	=> 'Zobrazit',
		'savecontinue'	=> 'Uložit&amp;Pokračovat',
		'categories'	=> 'Kategorie',
		'nocategories'	=> 'Kategorie nenastavená. <a href="admin.php?p=entry&amp;action=cats">Vytvořte si vlastní '. 
					'kategorii</a> v Správě kategorií. '.
					'<a href="#save">Uložit</a> nejdřív Váš příspěvek.',
		'saveopts'	=> 'Uložit nastavení',
		'success'	=> 'Váš příspěvek byl úspěšně publikovaný',
		'otheropts'	=> 'Ostatní nastavení',
		'commmsg'	=> 'Správa komentářů tohoto příspěvku',
		'delmsg'	=> 'Smazat tento příspěvek',
		//'back'		=> 'Back discarding changes',
	);
	

	$lang['admin']['entry']['list']['msgs'] = array(
		1	=> 'Příspěvek byl úspěšně uložený',
		-1	=> 'Při pokusu uložit příspěvek nastala chyba',
		2	=> 'Příspěvek byl úspěšně odstraněný',
		-2	=> 'Při pokusu smazat příspěvek nastala chyba',
	);

	
	$lang['admin']['entry']['write']['error'] = array(
		'subject'	=> 'Memůžete odeslat pokud je předmět prázdný',
		'content'	=> 'Memůžete odeslat prázdný příspěvek',
	);
	
	$lang['admin']['entry']['write']['msgs'] = array(
		1	=> 'Příspěvek byl úspěšně uložený',
		-1	=> 'Při pokuse uložit příspěvek nastala chyba',
		-2	=> 'Vyskytla se chyba: příspěvek nebyl uložený; index může být poškozen',
		-3	=> 'Vyskytla se chyba: příspěvek byl uložený jako návrh',
		-4	=> 'Vyskytla se chyba: příspěvek byl uložený jako návrh; index může být poškozen',
		'draft'=> 'Upravujete <strong>návrh</strong> příspěvku'
	);

	
	/* comments */
	
	$lang['admin']['entry']['commentlist'] = 
	array(
		'head'		=> "Komentáře k přispěvkům ", 
		'descr'		=> 'Vybrat komentář na vymazání',
		'sel'		=> 'Sel',
		'content'	=> 'Obsah',
		'date'		=> 'Datum',
		'author'	=> 'Autor',
		'email'		=> 'Email',
		'ip'		=> 'IP',
		'actions'	=> 'Akce',
		'act_edit'	=> 'Upravit',
		'act_del'	=> 'Smazat',
		'act_del_confirm' => 'Opravdu chceš smazat tento komentář?',
		'nocomments'	=> 'Tento příspěvek ještě nebyl okomentovaný.',
		
	
	);

	$lang['admin']['entry']['commentlist']['msgs'] =
	array(
		1	=> 'Komentář byl úspěšně odstraněný',
		-1	=> 'Při pokusu smazat příspěvek nastala chyba',	
	);

	$lang['admin']['entry']['commedit'] = 
	array(
		'head'		=> "Uprav komentář příspěvku", 
		'content'	=> 'Obsah',
		'date'		=> 'Datum',
		'author'	=> 'Autor',
		'www'		=> 'WWW',
		'email'		=> 'Email',
		'ip'		=> 'IP',
		'loggedin'	=> 'Registrovaný užívatel',
		'submit'	=> 'Ulož'
		
	
	);

	$lang['admin']['entry']['commedit']['msgs'] =
	array(
		1	=> 'Komentář byl upravený',
		-1	=> 'Nastala chyba při úpravě příspěvku',
	);
	
	/* delete action */
	
	$lang['admin']['entry']['delete'] = 
	array(
		'head'		=> 'Smazat příspěvek', 
		'descr'		=> 'Chystáte sa smazat tyto příspěvky:',
		'preview'	=> 'Zobrazit',
		'confirm'	=> 'Opravdu chcete pokračovat?',
		'fset'		=> 'Smazat',
		'ok'		=> 'Ano, smazat tento příspěvek',
		'cancel'	=> 'Ne, zpět do Správy příspěvků',
		'err'		=> 'Uvedená stránka neexistuje',
	
	);
	
	/* category mgmt */
	
	$lang['admin']['entry']['cats'] =
	array(
		'head'		=> 'Upravit kategorie',
		'descr'		=> '<p>Použijte formulář (dole) pro úpravu kategorií. </p><p>Každá kategorie by měla být v tomto formátu - "jméno kategorie: <em>id_number</em>". Položky odsazené pomlčkami tvoří hierarchii.</p>
		
	<p>Příklad:</p>
	<pre>
Hlavní :1
-Novinky :2
--Oznamení :3
---Události :4
Různé :5
-Technologie :6
	</pre>',
		'clear'		=> 'Smazat všechna data',
	
		'fset1'		=> 'Úprava',
		'fset2'		=> 'Použít změny',
		'submit'	=> 'Uložit'
	);
	
	$lang['admin']['entry']['cats']['msgs'] = array(
		
		1	=> 'Kategorie byli úspěšně uložené',
		-1	=> 'Při pokusu uložit kategorie nastala chyba',
		2	=> 'Kategorie byly smazané',
		-2	=> 'Při pokusu smazat kategorie nastala chyba.',
		-3 	=> 'ID kategorií musí být KLADNÉ!!'

	);
	
	
		
?>

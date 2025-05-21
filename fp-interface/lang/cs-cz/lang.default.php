<?php

	$lang = array();

	$lang['main'] = array(
		
		'nextpage'		=> 'Další strana &raquo;',
		'prevpage'		=> '&laquo; Předcházející',
		'entry'      	=> 'Příspěvek',
		'static'     	=> 'Statická stránka',
		'comment'    	=> 'Komentář',
		'preview'    	=> 'Upravit/Náhled',
		
		'filed_under'	=> 'Pole pod ',	
		
		'add_entry'  	=> 'Přidat příspěvek',
		'add_comment'  	=> 'Přidat komentář',
		'add_static'  	=> 'Přidat statickou stránku',
		
		'btn_edit'     	=> 'Upravit',
		'btn_delete'   	=> 'Smazat',
		
		'nocomments'	=> 'Přidej komentář',
		'comment'	=> '1 komentář',
		'comments'	=> 'komentáře',
		
	);
	
	$lang['search'] = array(
		
		'head'	=> 'Hledat',
		'fset1'	=> 'Vložit kritéria hledání',
		'keywords'	=> 'Výraz',
		'onlytitles'	=> 'Jen nadpisy',
		'fulltext'	=> 'Full-text',
		
		'fset2'	=> 'Datum',
		'datedescr'	=> 'Můžete si přiřadit Vaše hledání ke konkrétnímu datu. Můžete zvolit rok, rok a měsíc, nebo přesné datum. '.
					'Nechat prázdné pro hledání v celé databází.',
		
		'fset3' 	=> 'Hleda v kategoriích',
		'catdescr'	=> 'Nechat prázdné pro hledání v celé databází.',
		
		'fset4'	=> 'Začit hledání',
		'submit'	=> 'Hledej',
		
		'headres'	=> 'Výsledky hledání',
		'descrres'	=> 'Vyhledávání <strong>%s</strong> nalezlo tyto výsledky:',
		'descrnores'=> 'Vyhledávání <strong>%s</strong> nenalezlo žádný výsledek.',
		
		'moreopts'	=> 'Další možnosti',
		
		
		'searchag'	=> 'Hledej znovu',
		
	);
	
	$lang['search']['error'] = array(
	
		'keywords'	=> 'Musíš zadat aspoň jedno klíčové slovo'
	
	);
	
	
	
	
	
	$lang['entry'] = array();
	$lang['entry']['flags'] = array();
	
	$lang['entry']['flags']['long'] = array(
		'draft' => '<strong>Rozepsané:</strong>: Skryté, čakající na publikování',
		//'static' => '<strong>Statický příspěvek</strong>: normálně skrytý, uvede zápis příspěvku takto ?stránka=nadpis-příspěvku v url (experimentalní)',
		'commslock' => '<strong>Komentáře uzamčené</strong>: Komentáře nejsou povolené pro tuto položku'
	);
	
	$lang['entry']['flags']['short'] = array(
		'draft' => 'Návrh',
		//'static' => 'Static',
		'commslock' => 'Komentáře zamknuté'
	);

	$lang['404error'] = array(
		'subject'	=> 'Nenalezeno',
		'content'	=> '<p>Stránka kterou hledáte, nebyla nalezena</p>'
	);
		
	// Login
	$lang['login'] = array(
		
	'head'		=> 'Přihlásit',
		'fieldset1'	=> 'Zadej svoje přihlašovací jméno a heslo',
		'user'		=> 'Přihlašovací jméno:',
		'pass'		=> 'Heslo:',
		'fieldset2'	=> 'Přihlaš',
		'submit'	=> 'Přihlásit',
		'forgot'	=> 'Zapomenuté heslo'
	);
		
	$lang['login']['success'] = array(
		'success'	=> 'Přihlášení bylo úspěšné.',
		'logout'	=> 'Odhlášení bylo úspěšné.',
		'redirect'	=> 'Budete přesměrováni do 5 sekund.',
		'opt1'		=> 'Zpáky na hlavní stránku',
		'opt2'		=> 'Jdi do Administrace',
		'opt3'		=> 'Přidat nový příspěvek'
	);
	
	$lang['login']['error'] = array(
		'user'		=> 'Musíte vložit přihlašovací jméno.',
		'pass'		=> 'Musíte vložit heslo.',
		'match'		=> 'Nesprávné heslo.'
	);
	
	
	$lang['comments'] = array(
		'head'		=> 'Přidat komentář',
		'descr'		=> 'Pro přidání komentáře vyplňte formulář níže',
		'fieldset1'	=> 'Uživatelova data',
		'name'		=> 'Jméno (*)',
		'email'		=> 'Email:',
		'www'		=> 'Web:',
		'cookie'	=> 'Pamatovat si',
		'fieldset2'	=> 'Přidat komentář',
		'comment'	=> 'Komentář (*):',
		'fieldset3'	=> 'Poslat',
		'submit'	=> 'Přidat',
		'reset'		=> 'Reset',
		'success'	=> 'Váš komentář byl úspěšně přidaný',
		'nocomments'	=> 'Tento příspěvek ještě nebyl okomentovaný',
		'commslock'	=> 'Pro tento příspěvek byli komentáře vypnuté.'
	);
	
	$lang['comments']['error'] = array(
		'name'		=> 'Musíte vložit jméno',
		'email'		=> 'Musíte vložit správný email',
		'www'		=> 'Musíte vložit správné URL',
		'comment'	=> 'Musíte vložit komentář',
	);
	
	$lang['date']['month'] = array(
		
		'Leden',
		'Únor',
		'Březen',
		'Duben',
		'Květen',
		'Červen',
		'Červenec',
		'Srpen',
		'Září',
		'Říjen',
		'Listopad',
		'Prosinec'
		
	);

	$lang['date']['month_abbr'] = array(
		
		'Jan',
		'Feb',
		'Mar',
		'Apr',
		'May',
		'Jun',
		'Jul',
		'Aug',
		'Sep',
		'Oct',
		'Nov',
		'Dec'
		
	);

	$lang['date']['weekday'] = array(
		
		'Neděle',
		'Pondělí',
		'Úterý',
		'Středa',
		'Čtvrtek',
		'Pátek',
		'Sobota',
		
	);

	$lang['date']['weekday_abbr'] = array(
		
		'Ne',
		'Po',
		'Út',
		'St',
		'Čt',
		'Pá',
		'So',
		
	);



?>

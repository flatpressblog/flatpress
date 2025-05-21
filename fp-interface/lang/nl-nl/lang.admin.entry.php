<?php


	$lang['admin']['entry']['submenu'] = 
	array (
		'list'		=> 'Vermeldingen beheren',
		'write'		=> 'Schrijf vermelding',
		'cats'		=> 'Categorie beheren'
	);


	/* default action */
	
	$lang['admin']['entry']['list'] = 
	array(
		'head'		=> 'Vermeldingen beheren',
		'descr'		=> 'Selecteer een vermelding om te bewerken of<a href="admin.php?p=entry&amp;action=write"> voeg nieuw toe</a>'.
					'<br /><a href="admin.php?p=entry&amp;action=cats">De categorie bewerken</a>',
		'filter'	=> 'Filter: ',
		'nofilter'	=> 'Alles weergeven',
		'filterbtn'	=> 'Filter toepassen',
		'sel'		=> 'Selecteer', // checkbox
		'date'		=> 'Datum',
		'title'		=> 'Titel',
		'author'	=> 'Auteur',
		'comms'		=> '#Comm', // comments
		'action'	=> 'Actie',
		'act_del'	=> 'Verwijder',
		'act_view'	=> 'Toon',
		'act_edit'	=> 'Bewerk'
	);
	
	/* write action */
	$lang['admin']['entry']['write'] = 
	array(
		'head'		=> 'Schrijf vermelding',
		'descr'		=> 'Het formulier bewerken om de vermelding te schrijven',
		'uploader'	=> 'Uploader',
		'fieldset1'	=> 'Bewerk',
		'subject'	=> 'Onderwerp (*):',
		'content'	=> 'Inhoud (*):',
		'fieldset2'	=> 'Opslaan',
		'submit'	=> 'Publiceren',
		'preview'	=> 'Voorbeeld',
		'savecontinue'	=> 'Opslaan en doorgaan',
		'categories'	=> 'Categorie',
		'nocategories'	=> 'Geen categorie set. <a href="admin.php?p=entry&amp;action=cats">Maak je eigen '. 
					'categorie</a> vanuit het hoofdinvoerpaneel. '.
					'<a href="#save">Bewaar</a> uw vermelding eerst.',
		'saveopts'	=> 'Opties voor opslaan',
		'success'	=> 'Uw bericht is succesvol gepubliceerd',
		'otheropts'	=> 'Andere opties',
		'commmsg'	=> 'Opmerkingen voor dit item beheren',
		'delmsg'	=> 'Deeze vermelding verwijderen',
		//'back'	=> 'Wijzigingen terug draaien',
	);
	

	$lang['admin']['entry']['list']['msgs'] = array(
		1	=> 'Vermelding is opgeslagen',
		-1	=> 'Er is een fout opgetreden tijdens het opslaan 
					van de vermelding',
		2	=> 'Vermelding is verwijderd',
		-2	=>	 'Er is een fout opgetreden tijdens het verwijderen 
					van de vermelding',
	);

	
	$lang['admin']['entry']['write']['error'] = array(
		'subject'	=> 'U kunt geen leeg onderwerp verzenden',
		'content'	=> 'U kunt geen lege vermelding plaatsen',
	);
	
	$lang['admin']['entry']['write']['msgs'] = array(
		1	=> 'Vermelding is opgeslagen',
		-1	=> 'Er is een fout opgetreden: uw vermelding kan niet worden opgeslagen',
		-2	=> 'Er is een fout opgetreden: uw vermelding is niet opgeslagen; index is mogelijk beschadigd',
		-3	=> 'Er is een fout opgetreden: uw vermelding is opgeslagen als concept',
		-4	=> 'Er is een fout opgetreden: uw vermelding is opgeslagen als concept; index kan corrup zijn geworden',
		'draft'=> 'U bewerkt een <strong>ontwerp</strong> vermelding'
	);

	
	/* comments */
	
	$lang['admin']['entry']['commentlist'] = 
	array(
		'head'		=> 'Commentaar voor vermelding', 
		'descr'		=> 'Selecteer een commentaar die je wilt verwijderen',
		'sel'		=> 'Sel',
		'content'	=> 'Inhoud',
		'date'		=> 'Datum',
		'author'	=> 'Auteur',
		'email'		=> 'Email',
		'ip'		=> 'IP',
		'actions'	=> 'Acties',
		'act_edit'	=> 'Bewerk',
		'act_del'	=> 'Verwijder',
		'act_del_confirm' => 'Wilt u deze opmerking echt verwijderen?',
		'nocomments'	=> 'Dit bericht is nog niet becommentarieerd.',
		
	
	);

	$lang['admin']['entry']['commentlist']['msgs'] =
	array(
		1	=> 'Commentaar is verwijderd',
		-1	=> 'Er is een fout opgetreden tijdens het verwijderen 
					van het commentaar',
		
	);

	$lang['admin']['entry']['commedit'] = 
	array(
		'head'		=> 'Commentaar bewerken voor invoer', 
		'content'	=> 'Inhoud',
		'date'		=> 'Datum',
		'author'	=> 'Auteur',
		'www'		=> 'Web Site',
		'email'		=> 'Email',
		'ip'		=> 'IP',
		'loggedin'	=> 'Geregistreerde gebruiker',
		'submit'	=> 'Bewaar'
		
	
	);

	$lang['admin']['entry']['commedit']['msgs'] =
	array(
		1	=> 'Commentaar is bewerkt',
		-1	=> 'Er is een fout opgetreden tijdens het bewerken van het commentaar',
	);
	
	/* delete action */
	
	$lang['admin']['entry']['delete'] = 
	array(
		'head'		=> 'Verwijder vermelding', 
		'descr'		=> 'U staat op het punt de volgende vermelding te verwijderen:',
		'preview'	=> 'Voorbeeld',
		'confirm'	=> 'Weet u zeker dat u door wilt gaan?',
		'fset'		=> 'Verwijder',
		'ok'		=> 'Ja, verwijder deze vermelding',
		'cancel'	=> 'Nee, breng me terug naar het menu.',
		'err'		=> 'De opgegeven vermelding bestaat niet',
	
	);
	
	/* category mgmt */
	
	$lang['admin']['entry']['cats'] =
	array(
		'head'		=> 'Categorie bewerken',
		'descr'		=> '<p>Gebruik het onderstaande formulier om uw categorie toe te voegen en te bewerken. </p><p>Elk categorie-item moet in deze indeling zijn "categorie naam: <em>id_number</em>". Items met streepjes springen in.</p>
		
	<p>Example:</p>
	<pre>
General :1
News :2
--Announcements :3
--Events :4
----Misc :5
Technology :6
	</pre>',
		'clear'		=> 'Verwijder alle categorie data',
	
		'fset1'		=> 'Editor',
		'fset2'		=> 'Bewaar veranderingen',
		'submit'	=> 'Bewaar'
	);
	
	$lang['admin']['entry']['cats']['msgs'] = array(
		
		1	=> 'Categorie bewaard',
		-1	=> 'Er is een fout opgetreden tijdens het opslaan van categorie',
		2	=> 'Categorie gewist',
		-2	=> 'Er is een fout opgetreden tijdens het wissen van categorie',
		-3 	=> 'Categorie ID moeten strikt positief zijn (0 is niet toegestaan)'

	);
	
	
		
?>

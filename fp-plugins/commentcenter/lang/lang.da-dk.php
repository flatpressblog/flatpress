<?php
$lang ['admin'] ['entry'] ['submenu'] ['commentcenter'] = 'Comment Center';
$lang ['admin'] ['entry'] ['commentcenter'] = array(
	// Header of the panel
	'title' => 'Comment Center',
	'desc1' => 'Dette panel giver dig mulighed for at administrere kommentarerne på din blog.',
	'desc2' => 'Her kan du gøre flere ting:',

	// Links
	'lpolicies' => 'Politisk ledelse',
	'lapprove' => 'Vis blokerede kommentarer',
	'lmanage' => 'Administrer kommentarer',
	'lconfig' => 'Konfigurer plugin\'et',
	'faq_spamcomments' => 'Få hjælp til at håndtere spamkommentarer',

	// Policies
	'policies' => 'Retningslinjer',
	'desc_pol' => 'Her kan du redigere retningslinjerne for kommentarer.',
	'select' => 'Vælg',
	'criteria' => 'Kriterier',
	'behavoir' => 'Adfærd',
	'options' => 'Indstillinger',
	'entry' => 'Indgang',
	'entries' => 'Indgange',
	'categories' => 'Kategorier',
	'nopolicies' => 'Der er ingen retningslinjer',
	'all_entries' => 'Alle poster',
	'fol_entries' => 'Politikken gælder for følgende poster:',
	'fol_cats' => 'Politikken gælder for bidrag i følgende kategorier:',
	'older' => 'Politikken gælder for poster, der er ældre end %d dag (e).',
	'allow' => 'Tillad kommentarer',
	'block' => 'Forbyd kommentarer',
	'approvation' => 'Kommentarer skal godkendes',
	'up' => 'Opad',
	'down' => 'Ned',
	'edit' => 'Rediger',
	'delete' => 'Slet',
	'newpol' => 'Tilføj en ny politik',
	'del_selected' => 'Slet valgt(e) politik(ker)',
	'select_all' => 'Vælg alle',
	'deselect_all' => 'Vælg ingen',

	// Configuration page
	'configure' => 'Konfigurer plugin\'et',
	'desc_conf' => 'Her kan du ændre indstillingerne for pluginet.',
	'log_all' => 'Loggede blokerede kommentarer',
	'log_all_long' => 'Aktivér denne indstilling, hvis du også vil logge blokerede kommentarer.',
	'email_alert' => 'Meddelelse via e-mail',
	'email_alert_long' => 'Hvis du skal kontrollere en kommentar for godkendelse, kan du ' . 'blive informeret via e-mail.',
	'akismet' => 'Akismet',
	'akismet_use' => 'Kontrol af kommentarer med Akismet',
	'akismet_use_long' => 'Med <a href="https://akismet.com/" target="_blank">Akismet</a> kan du reducere spam i kommentarer.',
	'akismet_key' => 'Akismet-nøgle',
	'akismet_key_long' => 'Tjenesten <a href="https://akismet.com/signup/" target="_blank">Akismet</a> giver dig en <a class="hint externlink" href="https://akismet.com/support/getting-started/api-key/" target="_blank">nøgle</a>. Indsæt den her.',
	'akismet_url' => 'Blog-URL til Akismet',
	'akismet_url_long' => 'Du bør kun bruge ét domæne til den gratis Akismet-tjeneste. ' . 'Du kan lade dette felt stå tomt. <code>%s</code> vil så blive brugt.',
	'save_conf' => 'Gem indstillinger',

	// Edit policy page
	'apply_to' => 'Ansøg til',
	'editpol' => 'Redigering af en politik',
	'createpol' => 'Opret en politik',
	'some_entries' => 'Visse poster',
	'properties' => 'Indgang med visse egenskaber',
	'se_desc' => 'Hvis du har valgt %s, bedes du indsætte de poster, du ønsker at anvende på denne police.',
	'se_fill' => 'Udfyld venligst felterne med posternes <a href="admin.php?p=entry">ID</a> (<code>entryYYMMDD-HHMMSS</code>).',
	'po_title' => 'Ejendomme',
	'po_desc' => 'Hvis du har valgt %s -indstillingen, skal du udfylde egenskaberne.',
	'po_comp' => 'Felterne er ikke obligatoriske, men du skal udfylde mindst ét, ellers vil politikken ' . ' vil gælde for alle indtastninger.',
	'po_time' => 'Tidsindstillinger',
	'po_older' => 'Gælder for poster, der er ældre end ',
	'days' => 'dage.',
	'save_policy' => 'Gem politik',

	// Delete policies page
	'del_policies' => 'Slet politikker',
	'del_descs' => 'Du vil slette denne politik: ',
	'del_descm' => 'Du vil slette disse politikker: ',
	'sure' => 'Er du sikker?',
	'del_subs' => 'Ja, slet venligst',
	'del_subm' => 'Ja, slet det venligst',
	'del_cancel' => 'Nej, tilbage til indstillingerne.',

	// Approve comments page
	'app_title' => 'Godkend kommentaren',
	'app_desc' => 'Her kan du godkende kommentarer.',
	'app_date' => 'Dato',
	'app_content' => 'Kommentar',
	'app_author' => 'Forfatter',
	'app_email' => 'Email',
	'app_ip' => 'IP',
	'app_actions' => 'Foranstaltninger',
	'app_publish' => 'Udgivelse',
	'app_delete' => 'Slet',
	'app_nocomms' => 'Der er ingen kommentarer.',
	'app_pselected' => 'Udgiv udvalgte kommentarer',
	'app_dselected' => 'Fjern valgte kommentarer',
	'app_other' => 'Andre bemærkninger',
	'app_akismet' => 'Opdaget som spam',
	'app_spamdesc' => 'Disse kommentarer blev blokeret af Akismet.',
	'app_hamsubmit' => 'Når du publicerer, skal du også rapportere som Ham til Akismet.',
	'app_pubnotham' => 'Udgiv, men send ikke til Akismet',

	// Delete comments page
	'delc_title' => 'Slet kommentarer',
	'delc_descs' => 'Du skal slette denne kommentar: ',
	'delc_descm' => 'Du vil slette disse kommentarer: ',

	// Manage comments page
	'man_searcht' => 'Søg efter en post',
	'man_searchd' => 'Indsæt <a href="admin.php?p=entry">ID</a>\'et (<code>entryYYMMDD-HHMMSS</code>) for den post, hvis kommentarer du vil administrere.',
	'man_search' => 'Søgning',
	'man_commfor' => 'Bemærkninger til %s',
	'man_spam' => 'Rapporter som spam til Akismet',

	// The simple edit
	'simple_pre' => 'Kommentarerne til dette indlæg ',
	'simple_1' => 'er tilladt.',
	'simple_0' => 'kræver dit samtykke.',
	'simple_-1' => 'er blokeret.',
	'simple_manage' => 'Administrer kommentarerne til dette indlæg.',
	'simple_edit' => 'Rediger retningslinjer',

	// Akismet warnings
	'akismet_errors' => array(
		-1 => 'DAkismet-nøglen er tom. Indtast venligst denne.',
		-2 => 'Akismet-serveren er ikke tilgængelig.',
		-3 => 'Svaret fra Akismet mislykkedes.',
		-4 => 'Akismet-nøglen er ugyldig.'
	),

	// Messages
	'msgs' => array(
		1 => 'Konfiguration gemt.',
		-1 => 'Der opstod en fejl, da konfigurationen blev gemt.',

		2 => 'Politik gemt.',
		-2 => 'Der opstod en fejl, da politikken blev gemt (måske er dine indstillinger forkerte).',

		3 => 'Direktivet er udskudt.',
		-3 => 'Der opstod en fejl under forsøget på at flytte politikken (eller den kan ikke flyttes).',

		4 => 'Politik (n) fjernet.',
		-4 => 'Der opstod en fejl under forsøget på at fjerne politikken/politikkerne (eller du valgte ikke en politik).',

		5 => 'Kommentar (e) offentliggjort.',
		-5 => 'Der opstod en fejl under forsøget på at offentliggøre kommentarerne.',

		6 => 'Kommentar (e) fjernet.',
		-6 => 'Der opstod en fejl under forsøget på at fjerne kommentarerne (eller du valgte ikke en kommentar).',

		7 => 'Kommentar indsendt.',
		-7 => 'Der opstod en fejl under afsendelsen af kommentaren.'
	),

	// Errors
	'errors' => array(
		'pol_nonex' => 'Den politik, du vil redigere, findes ikke.',
		'entry_nf' => 'Den valgte post findes ikke.'
	)
);

$lang ['plugin'] ['commentcenter'] = array(
	'akismet_error' => 'Beklager, vi oplever tekniske problemer.',
	'lock' => 'Beklager, dette indlæg kan ikke kommenteres.',
	'approvation' => 'Kommentaren er blevet gemt, men administratoren skal låse den op, før den kan vises.',

	// Mail for comments
	'mail_subj' => 'Ny kommentar til godkendelse %s'
);

$lang ['plugin'] ['commentcenter'] ['mail_text'] = 'Hej %toname%,

"%fromname%" %frommail% skrev en kommentar til indlægget med titlen "%entrytitle%"
Men denne skal godkendes af dig, før den offentliggøres.

Det følgende blev skrevet som en kommentar:
___________________________________________
%content%
___________________________________________

Genereres automatisk af
%blogtitle%

';
?>

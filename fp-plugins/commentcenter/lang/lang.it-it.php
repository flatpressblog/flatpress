<?php
$lang ['admin'] ['entry'] ['submenu'] ['commentcenter'] = 'Centro commenti';
$lang ['admin'] ['entry'] ['commentcenter'] = array(
	// Header of the panel
	'title' => 'Centro commenti',
	'desc1' => 'Questo pannello ti consente di gestire i commenti del tuo blog.',
	'desc2' => 'Qui puoi fare numerose cose:',

	// Links
	'lpolicies' => 'Gestire le regole',
	'lapprove' => 'Elencare i commenti bloccati',
	'lmanage' => 'Gestire i commenti',
	'lconfig' => 'Configurare il plugin',

	// Policies
	'policies' => 'Regole',
	'desc_pol' => 'Qui puoi modificare le regole sui commenti.',
	'select' => 'Seleziona',
	'criteria' => 'Criteri',
	'behavoir' => 'Comportamento',
	'options' => 'Opzioni',
	'entry' => 'Articolo',
	'entries' => 'Articoli',
	'categories' => 'Categorie',
	'nopolicies' => 'Non c\'è nessuna regola.',
	'all_entries' => 'Tutti i post',
	'fol_entries' => 'La regola è applicata ai seguenti post:',
	'fol_cats' => 'La regola è applicata ai post nelle seguenti categorie:',
	'older' => 'La regola è applicata ai post più vecchi di %d giorno/i.',
	'allow' => 'Permetti di commentare',
	'block' => 'Blocca i commenti',
	'approvation' => 'I commenti devono essere approvati',
	'up' => 'Sposta in su',
	'down' => 'Sposta in giù',
	'edit' => 'Modifica',
	'delete' => 'Elimina',
	'newpol' => 'Aggiungi una nuova regola',
	'del_selected' => 'Elimina le regole selezionate',
	'select_all' => 'Seleziona tutto',
	'deselect_all' => 'Deseleziona tutto',

	// Configuration page
	'configure' => 'Configura il plugin',
	'desc_conf' => 'Qui puoi modificare le impostazioni del plugin.',
	'log_all' => 'Registra i commenti bloccati',
	'log_all_long' => 'Selezionala se vuoi registrare anche i commenti che sono bloccati.',
	'email_alert' => 'Notifica commenti via email',
	'email_alert_long' => 'Selezionala se vuoi essere informato via email quando c\'è un nuovo commento ' . 'da approvare.',
	'akismet' => 'Akismet',
	'akismet_use' => 'Abilita il controllo di Akismet',
	'akismet_key' => 'Chiave di Akismet',
	'akismet_key_long' => 'Per usare Akismet ti viene fornita una chiave. Inseriscila qui.',
	'akismet_url' => 'Indirizzo di base per Akismet',
	'akismet_url_long' => 'Penso che per il servizio gratuito di Akismet si possa usare un solo indirizzo. ' . 'Puoi anche lasciare vuoto questo campo, al suo posto si utilizzerà <code>%s</code>.',
	'save_conf' => 'Salva configurazione',

	// Edit policy page
	'apply_to' => 'Applica a',
	'editpol' => 'Modifica una regola',
	'createpol' => 'Crea una regola',
	'some_entries' => 'Alcuni articoli',
	'properties' => 'Articoli con precise caratteristiche',
	'se_desc' => 'Se hai selezionato l\'opzione %s, per favore inserisci gli articoli ai quali la vuoi applicare.',
	'se_fill' => 'Per favore riempi i campi con gli id degli articoli (<code>entryYYMMDD-HHMMSS</code>).',
	'po_title' => 'Caratteristiche',
	'po_desc' => 'Se hai selezionato l\'opzione %s, per seleziona le caratteristiche.',
	'po_comp' => 'I campi non sono obbligatori ma ne devi selezionare almeno uno, altrimenti la regola ' . 'sarà applicata a tutti gli articoli.',
	'po_time' => 'Opzioni sulle date',
	'po_older' => 'Applica agli articoli più vecchi di ',
	'days' => 'giorni.',
	'save_policy' => 'Salva regola',

	// Delete policies page
	'del_policies' => 'Elimina regole',
	'del_descs' => 'Stai per eliminare la seguente regola: ',
	'del_descm' => 'Stai per eliminare la seguenti regola: ',
	'sure' => 'Sei sicuro?',
	'del_subs' => 'Sì, continua l\'eliminazione',
	'del_subm' => 'Sì, continua l\'eliminazione',
	'del_cancel' => 'No, riportami al pannello',

	// Approve comments page
	'app_title' => 'Approva commenti',
	'app_desc' => 'Qui puoi approvare i commenti.',
	'app_date' => 'Data',
	'app_content' => 'Commento',
	'app_author' => 'Autore',
	'app_email' => 'Email',
	'app_ip' => 'IP',
	'app_actions' => 'Azioni',
	'app_publish' => 'Pubblica',
	'app_delete' => 'Elimina',
	'app_nocomms' => 'Non c\'è nessun commento.',
	'app_pselected' => 'Pubblica i commenti selezionati',
	'app_dselected' => 'Elimina i commenti selezionati',
	'app_other' => 'Alti Commenti',
	'app_akismet' => 'Segnalati come spam',
	'app_spamdesc' => 'Questi commenti sono stati bloccati da Akismet.',
	'app_hamsubmit' => 'Inviali ad Akismet come ham quando li pubblichi.',
	'app_pubnotham' => 'Pubblica senza inviarlo come ham',

	// Delete comments page
	'delc_title' => 'Elimina Commenti',
	'delc_descs' => 'Stai per eliminare questo commento: ',
	'delc_descm' => 'Stai per eliminare questi commenti: ',

	// Manage comments page
	'man_searcht' => 'Cerca un post',
	'man_searchd' => 'Inserisci l\'id dell\'articolo di cui vuoi gestire i commenti.',
	'man_search' => 'Cerca',
	'man_commfor' => 'Commenti di %s',
	'man_spam' => 'Segnala come spam ad Akismet',

	// The simple edit
	'simple_pre' => 'In questo articolo i commenti ',
	'simple_1' => 'sono ammessi.',
	'simple_0' => 'richiedono la tua approvazione.',
	'simple_-1' => 'sono bloccati.',
	'simple_manage' => 'Gestisci i commenti di questo articolo.',
	'simple_edit' => 'Modifica le regole',

	// Akismet warnings
	'akismet_errors' => array(
		-1 => 'La chiave di Akismet è vuota. Per favore inseriscila.',
		-2 => 'Non abbiamo potuto chiamare i server di Akismet.',
		-3 => 'La risposta di Akismet è fallita.',
		-4 => 'La chiave di Akismet non è valida.'
	),

	// Messages
	'msgs' => array(
		1 => 'Configurazione salvata.',
		-1 => 'Si è verificato un errore durante il salvataggio della configurazione.',

		2 => 'Regola salvata.',
		-2 => 'Si è verificato un errore durante il salvataggio della regola (forse le tue opzioni sono scorrette).',

		3 => 'Regola spostata.',
		-3 => 'Si è verificato un errore nello spostamento della regola (o non la si può muovere).',

		4 => 'Regole rimosse.',
		-4 => 'Si è verificato un errore durante la rimozione delle regole (o non hai selezionato nessuna regola).',

		5 => 'Commenti pubblicati.',
		-5 => 'Si è verificato un errore durante la pubblicazione del commento.',

		6 => 'Commenti rimossi.',
		-6 => 'Si è verificato un errore durante la rimozione dei commenti (o non hai selezionato nessun commento).',

		7 => 'Commento segnalato.',
		-7 => 'Si è verificato un errore durante la segnalazione del commento.'
	),

	// Errors
	'errors' => array(
		'pol_nonex' => 'La regola che vuoi modificare non esiste.',
		'entry_nf' => 'L\'articolo da te selezionato non esiste.'
	)
);
$lang ['plugin'] ['commentcenter'] = array(
	'akismet_error' => 'Akismet ha rilevato il tuo commento come SPAM.',
	'lock' => 'Siamo spiacenti ma i commenti per questo articolo sono chiusi.',
	'approvation' => 'Il commento è stato salvato ma l\'Amministratore lo deve approvare prima di farlo vedere.',

	// Mail for comments
	'mail_subj' => 'Nuovo commento da approvare su %s'
);

$lang ['plugin'] ['commentcenter'] ['mail_text'] = 'Caro %toname%,

"%fromname%" %frommail% ha appena postato un commento nel post intitolato "%entrytitle%"
ma tu devi approvarlo.

Questo è il suo contenuto:
***************
%content%
***************

Cordiali saluti,
%blogtitle%

';

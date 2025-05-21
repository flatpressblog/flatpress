<?php

$lang['admin']['entry']['submenu']['commentcenter']='Centrum komentářů';
$lang['admin']['entry']['commentcenter']=array(
	# Header of the panel
	'title'=>'Centrum komentářů',
	'desc1'=>'Tento panel umožňuje zpravovat komentáře k tvému blogu.',
	'desc2'=>'Zde můžeš dělat několik věcí:',

	# Links
	'lpolicies'=>'Nastavovat pravidla',
	'lapprove'=>'Zobrazit blokované komentáře',
	'lmanage'=>'Zpravovat komentáře',
	'lconfig'=>'Nastavovat plugin',

	# Policies
	'policies'=>'Pravidla',
	'desc_pol'=>'Zde můžeš nastavovat pravidla ke komentářům.',
	'select'=>'Označit',
	'criteria'=>'Kritéria',
	'behavoir'=>'Chování',
	'options'=>'Možnosti',
	'entry'=>'Záznam',
	'entries'=>'Záznamy',
	'categories'=>'Kategorie',
	'nopolicies'=>'Zde nejsou žádná pravidla.',
	'all_entries'=>'Všechny záznamy',
	'fol_entries'=>'Pravidla se použijí na následující položky:',
	'fol_cats'=>'Tyto pravidla se použijí na položky v následujících kategoriích:',
	'older'=>'Tyto pravidla se použijí na položky starší než %d dní.',
	'allow'=>'Povolit komentáře',
	'block'=>'Blokovat komentáře',
	'approvation'=>'Komentáře je třeba schválit',
	'up'=>'Posunout nahoru',
	'down'=>'Posunout dolů',
	'edit'=>'Editovat',
	'delete'=>'Smazat',
	'newpol'=>'Přidat nové pravidlo',
	'del_selected'=>'Smazat označená pravidla',
	'select_all'=>'Označit vše',
	'deselect_all'=>'Zrušit označení',

	# Configuration page
	'configure'=>'Nastavit plugin',
	'desc_conf'=>'Zde můžete upravit možnosti pluginu.',
	'log_all'=>'Logovat blokované komentáře',
	'log_all_long'=>'Zaškrtněte, pokud chcete protokolovat také komentáře, které jsou blokovány.',
	'email_alert'=>'Upozornit na komentáře e-mailem',
	'email_alert_long'=>'Zaškrtněte, pokud chcete být informováni emailem o komentáři ke schválení.',
	'akismet'=>'Akismet',
	'akismet_use'=>'Povolit Akismet',
	'akismet_key'=>'Akismet klíč',
	'akismet_key_long'=>'Služba Akismet vám poskytne klíč. Vložte jej sem.',
	'akismet_url'=>'URL blogu',
	'akismet_url_long'=>'Pro bezplatný Akismet byste měli používat pouze doménu. '.
		'Toto pole můžete nechat prázdné, <code>%s</code> bude použito.',
	'save_conf'=>'Uložit nastavení',

	# Edit policy page
	'apply_to'=>'Aplikovat',
	'editpol'=>'Editovat pravidla',
	'createpol'=>'Vytvořit pravidla',
	'some_entries'=>'Nějaké záznamy',
	'properties'=>'Záznam s určitými vlastnostmi',
	'se_desc'=>'Když vyberete %s, vložte prosím záznamy, které chcete použít pro toto pravidlo.',
	'se_fill'=>'Vyplňte pole s identifikací záznamu (<code>entryYYMMDD-HHMMSS</code>).',
	'po_title'=>'Vlastnosti',
	'po_desc'=>'Když vyberete %s, vyplňte prosím vlastnosti.',
	'po_comp'=>'Pole nejsou povinná, ale musíte vyplnit alespoň jedno pravidlo, '.
		'které bude použito na všechny záznamy.',
	'po_time'=>'Nastavení času',
	'po_older'=>'Použito pro záznamy starší než ',
	'days'=>'dní.',
	'save_policy'=>'Uložit pravidla',

	# Delete policies page
	'del_policies'=>'Smazat pravidla',
	'del_descs'=>'Chystáte se smazat toto pravidlo: ',
	'del_descm'=>'Chystáte se smazat tyto pravidla: ',
	'sure'=>'Jste si jistý?',
	'del_subs'=>'Ano, prosím smažte jej',
	'del_subm'=>'Ano, prosím smažte je',
	'del_cancel'=>'Ne, vrátit se zpět na panel',

	# Approve comments page
	'app_title'=>'Schválit komentáře',
	'app_desc'=>'Zde můžete schválit komentáře.',
	'app_date'=>'Datum',
	'app_content'=>'Kommentář',
	'app_author'=>'Autor',
	'app_email'=>'Email',
	'app_ip'=>'IP',
	'app_actions'=>'Akce',
	'app_publish'=>'Publikovat',
	'app_delete'=>'Smazat',
	'app_nocomms'=>'Zde není žádný komentář.',
	'app_pselected'=>'Publikovat označené komentáře',
	'app_dselected'=>'Vyjmout označené komentáře',
	'app_other'=>'Ostatní komentáře',
	'app_akismet'=>'Označit jako spam',
	'app_spamdesc'=>'Tyto komentáře zablokoval Akismet.',
	'app_hamsubmit'=>'Informovat Akismet pokud je zveřejníte.',
	'app_pubnotham'=>'Publikujte je, ale nepředávejte je Akismet',

	# Delete comments page
	'delc_title'=>'Smazat komentáře',
	'delc_descs'=>'Chystáte se smazat tento komentář: ',
	'delc_descm'=>'Chystáte se smazat tyto komentáře: ',

	# Manage comments page
	'man_searcht'=>'Najít záznam',
	'man_searchd'=>'Vložte id záznamu, u kterého chcete editovat komentáře.',
	'man_search'=>'Najít',
	'man_commfor'=>'Komentáře k %s',
	'man_spam'=>'Odeslat jako spam do Akismet',

	# The simple edit
	'simple_pre'=>'Komentář k tomuto záznamu ',
	'simple_1'=>'je povolen.',
	'simple_0'=>'vyžaduje váš souhlas.',
	'simple_-1'=>'je blokován.',
	'simple_manage'=>'Spravovat komentáře k tomuto záznamu.',
	'simple_edit'=>'Editovat pravidla',

	# Akismet warnings
	'akismet_errors'=>array(
		-1=>'Akismet klíč je prázdný. Prosím vložte jej.',
		-2=>'Nemůžeme se spojit s Akismet serverem.',
		-3=>'Odpověď od Akismet nepřišla.',
		-4=>'Akismet klíč je špatný.',
	),

	# Messages
	'msgs'=>array(
		1=>'Nastavení uloženo.',
		-1=>'Při pokusu o uložení nastavení došlo k chybě.',

		2=>'Pravidla uložena.',
		-2=>'Při pokusu o uložení pravidel došlo k chybě (možná je špatné nastavení).',

		3=>'Pravidla přesunuta.',
		-3=>'Při pokusu o přesun pravidel došlo k chybě (nebo nemůže být přesunuto).',

		4=>'Pravidla vyjmuta.',
		-4=>'Při pokusu o vyjmutí pravidel došlo k chybě (nebo není vybráno žádné pravidlo).',

		5=>'Komentář zveřejněn.',
		-5=>'Při pokusu o zveřejnění komentáře došlo k chybě.',

		6=>'Komentář vyjmut.',
		-6=>'Při pokusu o vyjmutí komentáře došlo k chybě. (nebo není vybrán žádný komentář).',

		7=>'Komentář odeslán.',
		-7=>'Při pokusu o odeslání komentáře došlo k chybě.',
	),

	# Errors
	'errors'=>array(
		'pol_nonex'=>'Pravidlo které chcete upravit, neexistuje.',
		'entry_nf'=>'Záznam který jste vybral neexistuje.',
	),
);
$lang['plugin']['commentcenter']=array(
	'akismet_error'=>'Je nám líto, ale máme technické potíže.',
	'lock'=>'Komentáře k tomuto zýznamu jsou blokovány.',
	'approvation'=>'Komentář byl uložen, ale správce jej musí před zobrazením schválit.',

	# Mail for comments
	'mail_subj'=>'Nový komentář ke schválení %s',
);

$lang['plugin']['commentcenter']['mail_text']=<<<MAIL
Vážený %toname%,

"%fromname%" %frommail% právě přidal komentář k záznamu s názvem "%entrytitle%"
ale před zobrazením je vyžadován váš souhlas.

Zde je komentář, který byl právě odeslán:
***************
%content%
***************

S pozdravem,
%blogtitle%

MAIL;

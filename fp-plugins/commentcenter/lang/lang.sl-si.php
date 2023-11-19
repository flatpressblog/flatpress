<?php
/**
 * Admin area phrases for Comment Center plugin
 */

$lang['admin']['entry']['submenu']['commentcenter'] = 'Center za komentarje';
$lang['admin']['entry']['commentcenter'] = array(
    'title' => 'Center za komentarje',
    'desc1' => 'Ta plošča vam omogoča upravljanje komentarjev na vašem blogu.',
    'desc2' => 'Tukaj lahko izvedete več stvari:',

    'lpolicies' => 'Upravljanje pravil',
    'lapprove' => 'Prikaži blokirane komentarje',
    'lmanage' => 'Upravljanje komentarjev',
    'lconfig' => 'Nastavi vtičnik',

    'policies' => 'Pravila',
    'desc_pol' => 'Tu lahko uredite pravila komentarjev.',
    'select' => 'Izberi',
    'criteria' => 'Merila',
    'behavoir' => 'Vedenje',
    'options' => 'Možnosti',
    'entry' => 'Vnos',
    'entries' => 'Vnosi',
    'categories' => 'Kategorije',
    'nopolicies' => 'Ni nobenega pravila.',
    'all_entries' => 'Vsi vnosi',
    'fol_entries' => 'Pravilo se uporablja za naslednje vnose:',
    'fol_cats' => 'Pravilo se uporablja za vnose v naslednjih kategorijah:',
    'older' => 'Pravilo se uporablja za vnose starejše od %d dni(za).',
    'allow' => 'Dovoli komentiranje',
    'block' => 'Blokiraj komentarje',
    'approvation' => 'Komentarji morajo biti odobreni',
    'up' => 'Premakni gor',
    'down' => 'Premakni dol',
    'edit' => 'Uredi',
    'delete' => 'Izbriši',
    'newpol' => 'Dodaj novo pravilo',
    'del_selected' => 'Izbriši izbrano pravilo(-a)',
    'select_all' => 'Izberi vse',
    'deselect_all' => 'Odznači vse',

    'configure' => 'Nastavi vtičnik',
    'desc_conf' => 'Tu lahko spremenite možnosti vtičnika.',
    'log_all' => 'Beleži blokirane komentarje',
    'log_all_long' => 'Označite to možnost, če želite beležiti tudi komentarje, ki so blokirani.',
    'email_alert' => 'Obvesti o komentarjih preko e-pošte',
    'email_alert_long' => 'Označite to možnost, če želite biti obveščeni preko e-pošte, ko je potrebno odobriti komentar.',
    'akismet' => 'Akismet',
    'akismet_use' => 'Omogoči preverjanje z Akismet',
    'akismet_key' => 'Ključ Akismet',
    'akismet_key_long' => 'Storitev Akismet vam zagotovi ključ za uporabo. Vnesite ga tukaj.',
    'akismet_url' => 'Osnovni URL bloga za Akismet',
    'akismet_url_long' => 'Za brezplačno storitev Akismet verjetno potrebujete samo domeno. ' . 'To polje lahko pustite prazno, uporabil se bo <code>%s</code>.',
    'save_conf' => 'Shrani nastavitve',

    'apply_to' => 'Uporabi za',
    'editpol' => 'Uredi pravilo',
    'createpol' => 'Ustvari pravilo',
    'some_entries' => 'Nekateri vnosi',
    'properties' => 'Vnosi s specifičnimi lastnostmi',
    'se_desc' => 'Če ste izbrali možnost %s, prosim, vnesite vnose, za katere želite uporabiti to pravilo.',
    'se_fill' => 'Prosimo, izpolnite polja z ID-jem vnosov (<code>entryYYMMDD-HHMMSS</code>).',
    'po_title' => 'Lastnosti',
    'po_desc' => 'Če ste izbrali možnost %s, prosim, izpolnite lastnosti.',
    'po_comp' => 'Polja niso obvezna, vendar morate izpolniti vsaj eno, sicer se bo pravilo uporabilo za vse vnose.',
    'po_time' => 'Možnosti časa',
    'po_older' => 'Uporabi za vnose, starejše od ',
    'days' => 'dni.',
    'save_policy' => 'Shrani pravilo',

    'del_policies' => 'Izbriši pravila',
    'del_descs' => 'Izbrisali boste to pravilo: ',
    'del_descm' => 'Izbrisali boste ta pravila: ',
    'sure' => 'Ste prepričani?',
    'del_subs' => 'Da, prosim izbriši to',
    'del_subm' => 'Da, prosim izbriši ta',
    'del_cancel' => 'Ne, vrni me nazaj na ploščo',

    'app_title' => 'Odobri komentar',
    'app_desc' => 'Tu lahko odobrite komentarje.',
    'app_date' => 'Datum',
    'app_content' => 'Komentar',
    'app_author' => 'Avtor',
    'app_email' => 'E-pošta',
    'app_ip' => 'IP',
    'app_actions' => 'Dejanja',
    'app_publish' => 'Objavi',
    'app_delete' => 'Izbriši',
    'app_nocomms' => 'Ni komentarjev.',
    'app_pselected' => 'Objavi izbrane komentarje',
    'app_dselected' => 'Odstrani izbrane komentarje',
    'app_other' => 'Drugi komentarji',
    'app_akismet' => 'Označeno kot spam',
    'app_spamdesc' => 'Ti komentarji so bili blokirani s strani Akismet.',
    'app_hamsubmit' => 'Pošlji na Akismet kot "ham", ko jih objavite.',
    'app_pubnotham' => 'Objavi, vendar jih ne pošiljaj kot "ham"',

    'delc_title' => 'Izbriši komentarje',
    'delc_descs' => 'Izbrisali boste ta komentar: ',
    'delc_descm' => 'Izbrisali boste te komentarje: ',

    'man_searcht' => 'Išči vnos',
    'man_searchd' => 'Vnesite ID vnosa, ki ga želite upravljati komentarje.',
    'man_search' => 'Išči',
    'man_commfor' => 'Komentarji za %s',
    'man_spam' => 'Označi kot spam za Akismet',

    'simple_pre' => 'Komentarji za ta vnos bodo ',
    'simple_1' => 'dovoljeni.',
    'simple_0' => 'zahtevali vaše odobritve.',
    'simple_-1' => 'blokirani.',
    'simple_manage' => 'Upravljanje komentarjev tega vnosa.',
    'simple_edit' => 'Uredi pravila',

    'akismet_errors' => array(
        -1 => 'Ključ Akismet je prazen. Prosimo, vnesite ga.',
        -2 => 'Ne moremo klicati strežnikov Akismet.',
        -3 => 'Odgovor Akismet ni uspel.',
        -4 => 'Ključ Akismet ni veljaven.'
    ),

    'msgs' => array(
        1 => 'Nastavitve shranjene.',
        -1 => 'Pri shranjevanju nastavitev je prišlo do napake.',

        2 => 'Pravilo shranjeno.',
        -2 => 'Pri shranjevanju pravila je prišlo do napake (morda so napačne nastavitve).',

        3 => 'Pravilo premaknjeno.',
        -3 => 'Pri premikanju pravila je prišlo do napake (ali se ne more premakniti).',

        4 => 'Pravilo(-a) odstranjeno(-a).',
        -4 => 'Pri odstranjevanju pravila(-a) je prišlo do napake (ali niste izbrali nobenega pravila).',

        5 => 'Komentar(ji) objavljen(i).',
        -5 => 'Pri objavljanju komentarja(jev) je prišlo do napake.',

        6 => 'Komentar(ji) odstranjen(i).',
        -6 => 'Pri odstranjevanju komentarja(jev) je prišlo do napake (ali niste izbrali nobenega komentarja).',

        7 => 'Komentar poslan.',
        -7 => 'Pri pošiljanju komentarja je prišlo do napake.'
    ),

    'errors' => array(
        'pol_nonex' => 'Pravilo, ki ga želite urediti, ne obstaja.',
        'entry_nf' => 'Vnos, ki ste ga izbrali, ne obstaja.'
    )
);

$lang['plugin']['commentcenter'] = array(
    'akismet_error' => 'Opravičujemo se, prihaja do tehničnih težav.',
    'lock' => 'Komentarji za ta vnos so blokirani, žal.',
    'approvation' => 'Komentar je bil shranjen, vendar ga mora administrator odobriti, preden se prikaže.',

    'mail_subj' => 'Nov komentar za odobritev na %s'
);

$lang['plugin']['commentcenter']['mail_text'] = 'Spoštovani %toname%,

"%fromname%" %frommail% je pravkar objavil komentar na vnos z naslovom "%entrytitle%"
vendar ga je potrebno odobriti, preden se prikaže na strani.

Tukaj je komentar, ki je bil pravkar objavljen:
***************
%content%
***************

Vse najboljše,
%blogtitle%

';
?>
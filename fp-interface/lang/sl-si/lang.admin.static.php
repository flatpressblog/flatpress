<?php
$lang['admin']['static']['submenu'] = array(
    'list' => 'Upravljaj statične strani',
    'write' => 'Napiši statično stran'
);

/* Glavna plošča */

$lang['admin']['static']['list'] = array(
    'head' => 'Statične strani',
    'descr' => 'Prosimo, izberite stran za urejanje ali <a href="admin.php?p=static&amp;action=write">dodajte novo</a>.',
    'sel' => 'Izbira', // checkbox
    'date' => 'Datum',
    'name' => 'Stran',
    'title' => 'Naslov',
    'author' => 'Avtor',
    'action' => 'Dejanje',
    'act_view' => 'Pogled',
    'act_del' => 'Izbriši',
    'act_edit' => 'Uredi'
);

$lang['admin']['static']['list']['msgs'] = array(
    1 => 'Stran je bila uspešno shranjena',
    -1 => 'Prišlo je do napake med poskusom shranjevanja strani',
    2 => 'Stran je bila uspešno izbrisana',
    -2 => 'Prišlo je do napake med poskusom brisanja strani',
);

/* Plošča za pisanje */

$lang['admin']['static']['write'] = array(
    'head' => 'Objavi statično stran',
    'descr' => 'Uredi obrazec za objavo strani',
    'fieldset1' => 'Uredi',
    'subject' => 'Zadeva (*):',
    'content' => 'Vsebina (*):',
    'fieldset2' => 'Pošlji',
    'pagename' => 'Ime strani (*):',
    'submit' => 'Objavi',
    'preview' => 'Predogled',
    'delfset' => 'Izbriši',
    'deletemsg' => 'Izbriši to stran',
    'del' => 'Izbriši',
    'success' => 'Vaša stran je bila uspešno objavljena',
    'otheropts' => 'Druge možnosti',
);

$lang['admin']['static']['write']['error'] = array(
    'subject' => 'Ne morete poslati prazne zadeve',
    'content' => 'Ne morete objaviti praznega vnosa',
    'id' => 'Morate poslati veljavno ID-številko'
);

/* Dejanje brisanja */

$lang['admin']['static']['delete'] = array(
    'head' => "Izbriši stran",
    'descr' => 'Opozarjamo vas, da boste izbrisali naslednjo stran:',
    'preview' => 'Predogled',
    'confirm' => 'Ali ste prepričani, da želite nadaljevati?',
    'fset' => 'Izbriši',
    'ok' => 'Da, izbriši to stran',
    'cancel' => 'Ne, vrni me nazaj na ploščo',
    'err' => 'Določena stran ne obstaja',
);
?>

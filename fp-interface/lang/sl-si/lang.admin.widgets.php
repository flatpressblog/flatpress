<?php

$lang['admin']['widgets']['submenu']['default'] = 'Upravljanje gradniki';
$lang['admin']['widgets']['submenu']['raw'] = 'Upravljanje gradniki (surovo)';

/* privzeta možnost */

$lang['admin']['widgets']['default'] = array(
    'head' => 'Upravljanje gradniki',

    'descr' => ' <a class="hint" '.
        'href="http://wiki.flatpress.org/doc:widgets" title="Kaj je gradnik?">'.
        'Gradnik</a> je dinamična komponenta, ki lahko prikaže podatke in interakcijo z uporabnikom.
						Čeprav <strong>Teme</strong> spreminjajo, kako izgleda vaš blog, gradniki 
						<strong>razširijo</strong> videz in funkcionalnosti.</p>

						<p>Gradnike lahko povlečete na posebna območja vaše teme, imenovana 
						<strong>GradnikSets</strong>. Število in ime GradnikSets se lahko razlikujeta glede na 
						izbrano temo.</p>

						<p>FlatPress vključuje več gradnikov: obstajajo gradniki, ki pomagajo pri prijavi, prikazujejo iskalno polje itd.</p>
						
						<p>Vsak gradnik je določen z <a class="hint" '.
        'href="http://wiki.flatpress.org/doc:plugins" title="Kaj je gradnik?">vstavkom</a>.',
						
    'availwdgs' => 'Dostopni gradniki',
    'trashcan' => 'Povlecite sem za brisanje',

    'themewdgs' => 'GradnikSets za to temo',
    'themewdgsdescr' => 'Tema, ki jo trenutno imate izbrano, vam omogoča naslednje gradniksets',
    'oldwdgs' => 'Druge gradniksets',
    'oldwdgsdescr' =>' Naslednji gradnikSets se zdi, da ne pripadajo nobenemu od zgoraj navedenih '.
        'gradniksets. Morda so ostanek od druge teme.',

    'submit' => 'Shrani Spremembe',

);

$lang['admin']['widgets']['default']['stdsets'] = array(
    'top' => 'Vrh traku',
    'bottom' => 'Spodnji trak',
    'left' => 'Levi trak',
    'right' => 'Desni trak',
);

$lang['admin']['widgets']['default']['msgs'] = array(
    1 => 'Nastavitve shranjene',
    -1 => 'Prišlo je do napake med poskusom shranjevanja, poskusite znova',
);

/* "surovi" panel */

$lang['admin']['widgets']['raw'] = array(
    'head' => 'Upravljanje gradniki (<em>surov urejevalnik</em>)',
    'descr' => ' <a class="hint" '.
        'href="http://wiki.flatpress.org/doc:plugins" title="Kaj je gradnik?">'.
        'Gradnik</a> je vizualni element <a class="hint" '.
        'href="http://wiki.flatpress.org/doc:plugins" title="Kaj je vstavek?">'.
        'Vstavka</a>, ki ga lahko postavite na posebna območja (gradniksets) na straneh vašega bloga. </p>'.
        '<p>To je <strong>surov</strong> urejevalnik; nekateri napredni uporabniki ali ljudje, ki ne '.
        'morejo uporabljati JavaScripta, ga morda raje uporabljajo.',

    'fset1' => 'Urejevalnik',
    'fset2' => 'Uporabi spremembe',
    'submit' => 'Uporabi',

);


$lang['admin']['widgets']['raw']['msgs'] = array(
    1 => 'Nastavitve shranjene',
    -1 => 'Prišlo je do napake med poskusom shranjevanja. To se lahko zgodi iz več razlogov: morda datoteka vsebuje sintaktične napake.',
);


/* sistemske napake */

$lang['admin']['widgets']['errors'] = array(
    'generic' => 'Gradnik z imenom <strong>%s</strong> ni registriran in bo izpuščen. '.
        'Ali je vtičnik omogočen v <a href="admin.php?p=plugin">panelu vtičnikov</a>?'

);

?>

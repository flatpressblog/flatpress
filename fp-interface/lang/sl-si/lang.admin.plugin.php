<?php
$lang['admin']['plugin']['submenu'] = array(
    'default' => 'Upravljaj vtičnike'
);

/* glavna plošča za vtičnike */

$lang['admin']['plugin']['default'] = array(
    'head' => 'Upravljanje vtičnikov',
    'enable' => 'Omogoči',
    'disable' => 'Onemogoči',
    'descr' => 'Vtičnik je komponenta, ki lahko razširi zmogljivosti FlatPressa.</p>'.
               '<p>Vtičnike lahko namestite tako, da jih naložite v svojo <code>fp-plugins/</code> '.
               'mapo.</p>'.
               '<p>Ta plošča vam omogoča omogočanje in onemogočanje vtičnikov',
    'name' => 'Ime',
    'description' => 'Opis',
    'author' => 'Avtor',
    'version' => 'Različica',
    'action' => 'Dejanje',
);

$lang['admin']['plugin']['default']['msgs'] = array(
    1 => 'Nastavitve shranjene',
    -1 => 'Prišlo je do napake med poskusom shranjevanja. To se lahko zgodi iz več razlogov: morda vaša datoteka vsebuje sintaktične napake.',
);

/* sistemske napake */

$lang['admin']['plugin']['errors'] = array(
    'head' => 'Med nalaganjem vtičnikov so se pojavile naslednje napake:',
    'notfound' => 'Vtičnik ni bil najden. Preskočeno.',
    'generic' => 'Napaka številka %d',
);
?>

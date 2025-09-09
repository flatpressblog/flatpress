<?php
$lang ['admin'] ['widgets'] ['submenu'] ['default'] = 'Kudeatu trepetak';
$lang ['admin'] ['widgets'] ['submenu'] ['raw'] = 'Kudeatu trepetak (editore gordina)';

/* default action */
$lang ['admin'] ['widgets'] ['default'] = array(
	'head' => 'Kudeatu trepetak',

	'descr' => '<a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="Zer da trepeta bat?">' . //
		'Trepeta</a> bat datuak bistaratu eta erabiltzailearekin elkarreragin dezakeen osagai dinamikoa da. ' . //
		'<strong>Gaiak</strong> zure blogaren itxura aldatzeko diren bitartean, trepetek ' . //
		'itxura eta funtzionaltasunak <strong>zabaltzen</strong> dituzte.</p>' . //

		'<p>Trepetak zure gaiaren eremu berezietara arrastatu daitezke, ' . //
		'<strong>trepeta-sorta</strong> izenekoetara. Trepeta-sorten kopurua eta izena alda daitezke aukeratzen duzun gaiaren arabera.</p>' . //

		'<p>FlatPressek hainbat widget ditu: saioa hasteko laguntza, bilaketa-koadroak bistaratzeko trepetak, etab. ' . //

		'Trepeta bakoitza <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="Zer da plugin bat?">plugin</a> batek definitzen du.',

	'availwdgs' => 'Eskuragarri dauden trepetak',
	'trashcan' => 'Arrastatu hona ezabatzeko',

	'themewdgs' => 'Gai honen trepeta-sortak',
	'themewdgsdescr' => 'Une honetan aktibatuta duzun gaiak trepeta-sorta hauek ditu',
	'oldwdgs' => 'Beste trepeta-sorta batzuk',
	'oldwdgsdescr' => 'Badirudi trepeta-sorta honek ez duela goian zerrendatutako ' . //
		'trepeta-sortetan kiderik. Beste gai bateko hondarrak izan daitezke.',

	'submit' => 'Gorde aldaketak',
	'drop_here' => 'Arrastatu hona'
);
	
$lang ['admin'] ['widgets'] ['default'] ['stdsets'] = array(
	'top' => 'Goiko barra',
	'bottom' => 'Oin barra',
	'left' => 'Ezkerreko barra',
	'right' => 'Eskuineko barra'
);

$lang ['admin'] ['widgets'] ['default'] ['msgs'] = array(
	1 => 'Ezarpenak ondo gorde dira.',
	-1 => 'Errore bat gertatu da gordetzen saiatzean.'
);

/* "raw" panel */
$lang ['admin'] ['widgets'] ['raw'] = array(
	'head' => 'Kudeatu trepetak (<em>editore gordina</em>)',
	'descr' => '<a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#widgets" target="_blank" title="Zer da trepeta bat?">' . //
		'Trepeta</a> bat <a class="hint" ' . //
		'href="https://wiki.flatpress.org/doc:techfaq#plugins" target="_blank" title="Zer da plugin bat?">' . //
		'plugin</a> baten elementu bisuala da, zure blogaren orrien gune berezi batzuetan' . //
    '(<em>trepeta-sortetan</em>) jar dezakezuna.</p>' . //
		'<p>Hau editore <strong>gordina</strong> da; erabiltzaile aurreratu batzuek edo JavaScript erabiltzen ez dutenek nahiago izan dezakete.',

	'fset1' => 'Editorea',
	'fset2' => 'Aplikatu aldaketak',
	'submit' => 'Aplikatu'
);

$lang ['admin'] ['widgets'] ['raw'] ['msgs'] = array(
	1 => 'Ezarpenak ondo gorde dira.',
	-1 => 'Errore bat gertatu da ezarpenak gordetzen saiatzean. Hainbat arrazoirengatik gerta daiteke; zure fitxategiak sintaxi akatsak izan ditzake.'
);

/* system errors */
$lang ['admin'] ['widgets'] ['errors'] = array(
	'generic' => '<strong>%s</strong> izeneko trepeta ez dago erregistratuta eta ez da kontuan hartuko. ' . //
 		'Plugina gaituta al dago <a href="admin.php?p=plugin">pluginen panelean</a>??'
);
?>

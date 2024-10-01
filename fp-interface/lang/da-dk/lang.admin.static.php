<?php
$lang ['admin'] ['static'] ['submenu'] = array(
	'list' => 'Administration af statiske sider',
	'write' => 'Opret statiske sider'
);

/* main panel */
$lang ['admin'] ['static'] ['list'] = array(
	'head' => 'Administrer statiske sider',
	'descr' => 'Denne menu bruges til at redigere statiske sider eller til at oprette en <a href="admin.php?p=static&amp;action=write">ny statisk side</a>.',

	'sel' => 'Sel', // checkbox
	'date' => 'Dato',
	'name' => 'Sidens navn',
	'title' => 'Titel',
	'author' => 'Forfatter',

	'action' => 'Handling',
	'act_view' => 'Se',
	'act_del' => 'Slet',
	'act_edit' => 'Rediger',

	'natural' => 'Naturlig rækkefølge',
	'submit' => 'Omorganiser sidenavne'
);

$lang ['admin'] ['static'] ['list'] ['msgs'] = array(
	1 => 'Statisk side gemt med succes',
	-1 => 'Der opstod en fejl under lagring af siden',
	2 => 'Siden blev slettet med succes',
	-2 => 'Der opstod en fejl under sletning af siden',
);

/* write panel */
$lang ['admin'] ['static'] ['write'] = array(
	'head' => 'Opret statisk side',
	'descr' => 'Rediger parametrene for at oprette denne side',
	'fieldset1'	=> 'Rediger',
	'subject' => 'Titel (*):',
	'content' => 'Indhold (*):',
	'fieldset2' => 'Indtast sidens navn',
	'pagename' => 'Gem statisk side som (*):',
	'submit' => 'Gem side',
	'preview' => 'Forhåndsvisning',

	'delfset' => 'Slet',
	'deletemsg' => 'Slet siden',
	'del' => 'Slet',
	'success' => 'Siden blev gemt med succes',
	'otheropts' => 'Andre muligheder'
);

$lang ['admin'] ['static'] ['write'] ['error'] = array(
	'subject' => 'Der blev ikke givet nogen titel',
	'content' => 'Der er intet indhold',
	'id' => 'Intet sidenavn angivet for den statiske side'
);

/* delete action */
$lang ['admin'] ['static'] ['delete'] = array(
	'head' => 'Slet side', 
	'descr' => 'Vil du virkelig slette denne side?',
	'preview' => 'Forhåndsvisning',
	'confirm' => 'Er du sikker?',
	'fset' => 'Slet',
	'ok' => 'Ja, slet denne side',
	'cancel' => 'Nej, tilbage til administrationen',
	'err' => 'Den valgte side findes ikke'
);
?>

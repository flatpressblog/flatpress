<?php
$lang ['admin'] ['entry'] ['submenu'] ['commentcenter'] = 'Iruzkin-zentroa';
$lang ['admin'] ['entry'] ['commentcenter'] = array(
	// Header of the panel
	'title' => 'Iruzkin-zentroa',
	'desc1' => 'Panel honek zure blogeko iruzkinak kudeatzeko aukera ematen dizu.',
	'desc2' => 'Hemen hainbat gauza egin ditzakezu:',

	// Links
	'lpolicies' => 'Kudeatu politikak',
	'lapprove' => 'Ikusi blokeatutako iruzkinak',
	'lmanage' => 'Kudeatu iruzkinak',
	'lconfig' => 'Konfiguratu plugina',
	'faq_spamcomments' => 'Lortu laguntza spam iruzkinak kudeatzeko',

	// Policies
	'policies' => 'Politikak',
	'desc_pol' => 'Hemen iruzkinen politikak edita ditzakezu.',
	'select' => 'Hautatu',
	'criteria' => 'Irizpideak',
	'behavoir' => 'Portaera',
	'options' => 'Ekintzak',
	'entry' => 'Sarrera',
	'entries' => 'Sarrerak',
	'categories' => 'Kategoriak',
	'nopolicies' => 'Ez dago politikarik.',
	'all_entries' => 'Sarrera guztiak',
	'fol_entries' => 'Politika sarrera hauei aplikatzen zaie:',
	'fol_cats' => 'Politika kategoria hauetako sarrerei aplikatzen zaie:',
	'older' => 'Politika %d egun baino zaharragoak diren sarreretan aplikatzen da.',
	'allow' => 'Baimendu iruzkin guztiak',
	'block' => 'Blokeatu iruzkin guztiak',
	'approvation' => 'Iruzkinak onartu behar dira',
	'up' => 'Mugitu gora',
	'down' => 'Mugitu behera',
	'edit' => 'Editatu',
	'delete' => 'Ezabatu',
	'newpol' => 'Gehitu politika berria',
	'del_selected' => 'Ezabatu hautatutako politika(k)',
	'select_all' => 'Hautatu guztiak',
	'deselect_all' => 'Desautatu guztiak',

	// Configuration page
	'configure' => 'Konfiguratu plugina',
	'desc_conf' => 'Hemen pluginaren aukerak alda ditzakezu.',
	'log_all' => 'Blokeatutako iruzkinak erregistratu',
	'log_all_long' => 'Hautatu blokeatuta dauden iruzkinak ere erregistratu nahi badituzu.',
	'email_alert' => 'Jakinarazi iruzkin berriak posta elektronikoz',
	'email_alert_long' => 'Hautatu onartzeko iruzkin bat dagoenean posta elektronikoz jakinarazi nahi izatea nahi baduzu.',
	'akismet' => 'Akismet',
	'akismet_use' => 'Gaitu Akismet egiaztapena',
	'akismet_use_long' => '<a href="https://akismet.com/" target="_blank">Akismet</a>ekin iruzkinetako spama murriztu dezakezu.',
	'akismet_key' => 'Akismet gakoa',
	'akismet_key_long' => '<a href="https://akismet.com/signup/" target="_blank">Akismet zerbitzuak</a> <a class="hint externlink" href="https://akismet.com/support/getting-started/api-key/" target="_blank">API gako</a> bat eskaintzen dizu. Txertatu hemen.',
	'akismet_url' => 'Akismeterako blogaren oinarrizko URLa',
	'akismet_url_long' => 'Akismeten doako zerbitzurako domeinu bat bakarrik erabili beharko zenuke. Eremu hau hutsik utz dezakezu, <code>%s</code> erabiliko da.',
	'save_conf' => 'Gorde konfigurazioa',

	// Edit policy page
	'apply_to' => 'Hauei aplikatu',
	'editpol' => 'Politika editatu',
	'createpol' => 'Sortu politika berria',
	'some_entries' => 'Sarrera batzuk',
	'properties' => 'Ezaugarri jakin batzuk dituzten sarrerak',
	'se_desc' => '%s aukera hautatu baduzu, sartu politika hau aplikatu nahi dizkiozun sarrerak.',
	'se_fill' => 'Mesedez, bete eremuak sarreren <a href="admin.php?p=entry">ID</a>arekin (<code>entryYYMMDD-HHMMSS</code>).',
	'po_title' => 'Ezaugarriak',
	'po_desc' => '%s aukera hautatu baduzu, bete propietateak.',
	'po_comp' => 'Eremuak ez dira derrigorrezkoak baina gutxienez bat bete behar duzu, bestela politika sarrera guztiei aplikatuko zaie.',
	'po_time' => 'Denborarekin lotutako aukerak',
	'po_older' => 'Aplikatu ',
	'days' => 'egun baino gehiagoko sarrerei.',
	'save_policy' => 'Gorde politika',

	// Delete policies page
	'del_policies' => 'Ezabatu politikak',
	'del_descs' => 'Politika hau ezabatuko duzu: ',
	'del_descm' => 'Politika hauek ezabatuko dituzu: ',
	'sure' => 'Ziur zaude?',
	'del_subs' => 'Bai, mesedez ezabatu',
	'del_subm' => 'Bai, mesedez ezabatu itzazu',
	'del_cancel' => 'Ez, eraman nazazu atzera',

	// Approve comments page
	'app_title' => 'Kudeatu iruzkinak',
	'app_desc' => 'Hemen iruzkinak onartu edo ezabatu ditzakezu.',
	'app_date' => 'Data',
	'app_content' => 'Iruzkina',
	'app_author' => 'Egilea',
	'app_email' => 'E-maila',
	'app_ip' => 'IPa',
	'app_actions' => 'Ekintzak',
	'app_publish' => 'Argitaratu',
	'app_delete' => 'Ezabatu',
	'app_nocomms' => 'Ez dago iruzkin berririk.',
	'app_pselected' => 'Argitaratu hautatutako iruzkina(k)',
	'app_dselected' => 'Ezabatu hautatutako iruzkina(k)',
	'app_other' => 'Beste iruzkin batzuk',
	'app_akismet' => 'Markatu spam gisa',
	'app_spamdesc' => 'Akismetek blokeatu ditu iruzkin hauek.',
	'app_hamsubmit' => 'Argitaratzean spama ez delaren berri eman Akismeteri',
	'app_pubnotham' => 'Argitaratu baina spam ez delaren berririk ez eman Akismeteri',

	// Delete comments page
	'delc_title' => 'Ezabatu iruzkinak',
	'delc_descs' => 'Iruzkin hau ezabatuko duzu: ',
	'delc_descm' => 'Iruzkin hauek ezabatuko dituzu: ',

	// Manage comments page
	'man_searcht' => 'Bilatu sarrera bat',
	'man_searchd' => 'Sartu kudeatu nahi duzun sarreraren <a href="admin.php?p=entry">ID</a>a (<code>entryYYMMDD-HHMMSS</code>).',
	'man_search' => 'Bilatu',
	'man_commfor' => '%s sarreraren iruzkinak',
	'man_spam' => 'Bidali spam gisa Akismetera',

	// The simple edit
	'simple_pre' => 'Sarrera honen iruzkinak/ek ',
	'simple_1' => 'onartuko dira.',
	'simple_0' => 'zure onarpena beharko dute.',
	'simple_-1' => 'blokeatuko dira.',
	'simple_manage' => 'Kudeatu sarrera honen iruzkinak.',
	'simple_edit' => 'Editatu politikak',

	// Akismet warnings
	'akismet_errors' => array(
		-1 => 'Akismet gakoa hutsik dago. Sartu ezazu, mesedez.',
		-2 => 'Ezin izan dugu Akismet zerbitzariekin kontaktatu.',
		-3 => 'Akismeten erantzunak huts egin du.',
		-4 => 'Akismet gakoa ez da baliozkoa.'
	),

	// Messages
	'msgs' => array(
		1 => 'Ezarpenak ondo gorde dira.',
		-1 => 'Errore bat gertatu da ezarpenak gordetzen saiatzean.',

		2 => 'Politika ondo gorde da.',
		-2 => 'Errore bat gertatu da politika gordetzen saiatzean.',

		3 => 'Politika ondo mugitu da.',
		-3 => 'Errore bat gertatu da politika mugitzen saiatzean (edo ezin da mugitu).',

		4 => 'Politika(k) ondo ezabatu da/dira.',
		-4 => 'Errore bat gertatu da politika(k) ezabatzen saiatzean (edo ez duzu politikarik hautatu).',

		5 => 'Iruzkina(k) ondo argitaratu da/dira.',
		-5 => 'Errore bat gertatu da iruzkina(k) argitaratzen saiatzean.',

		6 => 'Iruzkina(k) ondo ezabatu da/dira.',
		-6 => 'Errore bat gertatu da iruzkina(k) ezabatzen saiatzean (edo ez duzu iruzkinik hautatu).',

		7 => 'Iruzkina ondo bidali da.',
		-7 => 'Errore bat gertatu da iruzkina bidaltzen saiatzean.'
	),

	// Errors
	'errors' => array(
		'pol_nonex' => 'Editatu nahi duzun politika ez da existitzen.',
		'entry_nf' => 'Hautatu duzun sarrera ez da existitzen.'
	)
);

$lang ['plugin'] ['commentcenter'] = array(
	'akismet_error' => 'Barkatu, arazo teknikoak ditugu.',
	'akismet_spam'  => 'Zoritxarrez, zure iruzkina spam gisa identifikatu da.',
	'lock' => 'Sarrera honetako iruzkinak blokeatuta daude, barkatu.',
	'approvation' => 'Iruzkinak gorde dira, baina Administratzaileak onartu behar ditu erakutsi aurretik.',

	// Mail for comments
	'mail_subj' => 'Iruzkin berriak onartzeko %s sarreran'
);

$lang ['plugin'] ['commentcenter'] ['mail_text'] = '%toname%,

%fromname%-(e)k (%frommail%) iruzkin berri bat idatzi du "%entrytitle%" sarreran baina zure onarpena behar du argitaratu aurretik.

Hona hemen %fromname%-(e)k idatzi berri duen iruzkina:
__________________________________________
%content%
__________________________________________

Hasi saioa zure FlatPress blogaren kontrol-panelean eta onartu edo ezabatu blokeatutako iruzkina iruzkin-zentroan.

Agur,
%blogtitle%

';
?>

<?php
/*
 * LangId: Euskara (ES)
 */
$lang ['setup'] = array(
	'setup' => 'Instalazioa'
);

$lang ['locked'] = array(
	'head' => 'Instalazioa blokeatu da',
	'descr' => 'Badirudi instalazioa jada exekutatu duzula, <code>%s</code> blokeo-fitxategia aurkitu baitugu.

		Instalazioa berrabiarazi behar baduzu, lehenik fitxategi hau ezabatu.

		<strong >Gogoratu!</strong> Ez da segurua <code>setup.php</code> eta <code>setup/</code> direktorioa zure zerbitzarian gordetzea, ezabatzea gomendatzen dizugu!

		<ul>
		<li><a href="%s">Ados, eraman nazazu nire blogera.</a></li>
		<li><a href="%s">Fitxategia ezabatu dut, berrabiarazi instalazioa.</a></li>
		</ul>'
);

$lang ['err'] = array(
	'setuprun1' => 'Instalazioa abian da.',

	'setuprun2' => 'Instalazioa abian da dagoeneko. Administratzailea bazara ',
	'setuprun3' => ' ezabatu dezakezu instalazioa berrabiarazteko.',
	'writeerror' => 'Idazketa akatsak',

	'fpuser1' => ' ez da baliozko erabiltzaile bat. ' . //
		'Erabiltzaile-izenak alfanumerikoa izan behar du eta ez du hutsunerik izan behar.',
	'fpuser2' => ' ez da baliozko erabiltzaile bat. ' . //
		'Erabiltzaile-izenak letrak, zenbakiak eta azpimarra bat bakarrik izan ditzake.',
	'fppwd' => 'Pasahitzak gutxienez 6 karaktere izan behar ditu eta ez du hutsunerik izan behar.',
	'fppwd2' => 'Pasahitzak ez datoz bat.',
	'email' => ' ez da baliozko helbide elektroniko bat.',
	'www' => ' ez da baliozko URL bat.',
	'error' => '<p><big>Errorea!</big> ' . //
		'Honako errore hauek gertatu dira formularioa prozesatzean:</p><ul>'
);

$lang ['step1'] = array(
	'head' => 'Ongi etorri FlatPressera!',
	'descr' => 'Eskerrik asko <strong>FlatPress</strong> aukeratzeagatik.

		Zure blog berri-berriarekin ondo pasatzen hasi aurretik, galdera batzuk egin behar dizkizugu.

		Ez kezkatu, ez gara asko luzatuko!',
	'descrl1' => 'Hautatu zure hizkuntza.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Ez dago zerrendan?</a>',
	'descrlang' => 'Zure hizkuntza zerrenda honetan ikusten ez baduzu, komeni da bertsio honetarako <a href="https://wiki.flatpress.org/res:language" target="_blank" rel="external">hizkuntza paketerik</a> dagoen ikustea:

		<pre>%s</pre>

		Hizkuntza paketea instalatzeko, paketearen edukia zure <code>flatpress/</code> fitxategira igo, eta dena gainidatzi, eta ondoren <a href="./setup.php">berrabiarazi konfigurazio hau</a>.',
	'descrw' => 'FlatPressek funtziona dezan behar duzun <strong>gauza bakarra idazgarria</strong> den direktorio bat da..

		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => 'Erabiltzailea sortu',
	'descr' => 'Ia amaitu duzu, bete hurrengo xehetasunak:',
	'fpuser' => 'Erabiltzaile-izena',
	'fppwd' => 'Pasahitza',
	'fppwd2' => 'Idatzi berriro pasahitza',
	'www' => 'Hasierako orria',
	'email' => 'E-maila'
);

$lang ['step3'] = array(
	'head' => 'Instalazioa ondo burutu da',
	'descr' => '<strong>Listo!</strong>.

		Ez al zaizu sinestezina iruditzen??

		Arrazoi duzu: <strong>istorioa hasi besterik ez da egin</strong> eta <strong>idaztea zure esku dago</strong>!

		<ul>
		<li>Ikusi nolako itxura duen <a href="%s">hasierako orrialdeak</a></li>
		<li>Ondo pasa! <a href="%s">Hasi saioa orain!</a></li>
		<li>Idatzi nahi diguzu? <a href="https://www.flatpress.org/" target="_blank" rel="external">Joan FlatPress.org-era!</a></li>
		</ul>

		Eta eskerrik asko FlatPress aukeratzeagatik!'
);

$lang ['buttonbar'] = array(
	'next' => 'Jarraitu >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Menua';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]Hasiera[/url]
[*][url=?paged=1]Bloga[/url]
[*][url=static.php?page=about]Honi buruz[/url]
[*][url=contact.php]Harremanetan jarri[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'Ongi etorri FlatPressera!';
$lang ['samplecontent'] ['entry'] ['content'] = 'Sarrera baten adibide bat da hau, [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url]en ezaugarri batzuk erakusteko argitaratua.

"more" etiketak pasarte baten eta artikulu osoaren artean "jauzi" bat sortzeko aukera ematen dizu.

[more]


[h4]Estiloa[/h4]

Zure edukiari formatua esta estiloa emateko modu lehenetsia [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]BBcode[/url] (bulletin board code) da. BBCode zure argitalpenen estiloa aldatzeko modu erraza da. Kode ohikoenak onartzen dira. Adibidez: [b] hizki [b]lodiak[/b] idazteko (html: strong), [i] hizki [i]etzanak[/i] idazteko (html: em), etab.

[quote]Zure aipamen gogokoenak erakusteko [b]aipamen[/b] blokeak ere badaude.[/quote]

[code]Eta \'code\'k zure hizki kateak modu monotartekatuan erakusten ditu.
Eduki indentatua ere
   onartzen du.[/code]

"img" eta "url" etiketek ere aukera bereziak dituzte. Informazio gehiago aurki dezakezu [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]FlatPressen wikian[/url].


[h4]Sarrerak (argitalpenak) eta orrialde estatikoak[/h4]

Hau sarrera bat da, eta [url=static.php?page=about]Honi buruz[/url] [b]orrialde estatiko[/b] bat da. Orrialde estatiko bat komentatu ezin den eta blogaren ohiko argitalpenekin batera agertzen ez den sarrera (argitalpen) bat da.

Orrialde estatikoak informazio orokorreko orrialdeak sortzeko baliagarriak dira. Orrialde horietako bat bisitarientzako [b]hasiera orrialde[/b] bihur dezakezu. Horrek esan nahi du FlatPressekin blogik gabeko gune oso bat ere exekutatu dezakezula. Orrialde estatiko bat hasierako orrialde bihurtzeko aukera [url=admin.php]kontrol-paneleko[/url] [b]ezarpenen eremuan[/b] dago.


[h4]Pluginak[/h4]

FlatPress oso pertsonalizagarria da, eta [url=https://wiki.flatpress.org/doc:plugins:standard target=_blank rel=external]pluginak[/url] onartzen ditu bere potentzia handitzeko. BBCode bera plugin bat da.

FlatPressen beste funtzio eta harribitxi ezkutu batzuk erakusteko, eduki adibide gehiago sortu ditugu :)
Bi [b]orrialde estatiko[/b] aurki ditzakezu zure edukia onartzeko prest:
[list]
[*][url=static.php?page=about]Honi buruz[/url]
[*][url=static.php?page=menu]Menua[/url] (kontuan izan orrialde honetako estekak alboko barran ere agertuko direla - hau [b]BlockParser trepetaren[/b] magia baino ez da. Ikusi [url=https://wiki.flatpress.org/doc:faq target=_blank rel=external]FAQ[/url] hau eta gehiagorako!)
[/list]

[b]PhotoSwipe plugin[/b]arekin irudiak are errazago jar ditzakezu orain, float="left" edo float="right" lerrokatutako irudi bakar gisa, testuz inguratuta.
\'gallery\' elementua ere erabil dezakezu argazki galeria osoak bisitariei aurkezteko. [url=https://wiki.flatpress.org/res:plugins:photoswipe target=_blank rel=external]Hemen ikas dezakezu[/url]  zein erraza den.


[h4]Trepetak (widgetak)[/h4]

Ez dago elementu finko bakar bat ere alboko barran(etan). Testu hau inguratzen duten barretan aurki ditzakezun elementu guztien kokalekua aldatu daiteke, eta gehienak pertsonalizagarriak ere badira. Gai batzuek ezarpen-panel berezia ere eskaintzen dute kontrol-panelean.

Elementu hauei [b]trepeta[/b] deitzen zaie. Trepetei buruz gehiago jakiteko eta zure blogean efektu ederrak lortzeko bisitatu [url=https://wiki.flatpress.org/doc:tips:widgets target=_blank rel=external]trepetei buruzko aholkuak wikian[/url] edo bisitatu [url=https://wiki.flatpress.org/ target=_blank rel=external]FlatPressen wikia[/url].


[h4]Gaiak[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
FlatPress-Leggero gaiarekin 3 estilo txantiloi dituzu eskura - klasikotik modernora. Txantiloi hauek abiapuntu bikaina dira zure estilo propioa sortzeko.


[h4]Ikusi gehiago[/h4]

Gehiago ikusi nahi duzu?

[list]
[*]Jarrai ezazu [url=https://www.flatpress.org/?x target=_blank rel=external]blog ofiziala[/url] FlatPress munduan gertatzen ari dena jakiteko.
[*]Bisitatu [url=https://forum.flatpress.org/ target=_blank rel=external]foroa[/url] laguntza jasotzeko.
[*]Lortu beste [url=https://wiki.flatpress.org/res:themes target=_blank rel=external]erabiltzaile batzuek[/url] sortutako [b]gai ederrak[/b]!
[*]Aurkitu [url=https://wiki.flatpress.org/res:plugins target=_blank rel=external]plugin berriak[/url].
[*]Deskargatu bloga zure hizkuntzan erakusteko [url=https://wiki.flatpress.org/res:language target=_blank rel=external]hizkuntza paketea[/url].
[*]FlatPress ere [url=https://fosstodon.org/@flatpress target=_blank rel=external]Mastodon[/url]en jarrai dezakezu.
[/list]


[h4]Nola lagundu dezaket?[/h4]

[list]
[*]Lagundu proiektua dohaintza txiki batekin[url=https://www.flatpress.org/home/static.php?page=donate target=_blank rel=external][/url].
[*][url=https://www.flatpress.org/contact/ target=_blank rel=external]Jarri gurekin harremanetan[/url] akatsen berri emateko edo hobekuntzak iradokitzeko.
[*]Lagundu FlatPressen garapenean [url=https://github.com/flatpressblog/flatpress target=_blank rel=external]GitHub[/url]en.
[*]Itzuli FlatPress edo dokumentazioa [url=https://wiki.flatpress.org/res:language target=_blank rel=external]zure hizkuntzara[/url].
[*]Partekatu zure ezagutza eta konektatu beste FlatPress erabiltzaileekin [url=https://forum.flatpress.org/ target=_blank rel=external]foroan[/url].
[*]Zabaldu mezua! :)
[/list]


[h4]Eta orain zer?[/h4]

Orain [url=login.php]saioa hasi[/url] egin dezakezu [url=admin.php]kontrol-panelera[/url] sartzeko eta argitaratzen hasteko!

Ondo pasa! :)

[i][url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url] Taldea[/i]

';

$lang ['samplecontent'] ['about'] ['subject'] = 'Honi buruz';
$lang ['samplecontent'] ['about'] ['content'] = 'Idatzi zerbait zure buruari buruz hemen. ([url=admin.php?p=static&action=write&page=about]Editatu nazazu![/url])';

$lang ['samplecontent'] ['privacy-policy'] ['subject'] = 'Pribatutasun-politika';
$lang ['samplecontent'] ['privacy-policy'] ['content'] = 'Herrialde batzuetan Akismet Antispam zerbitzua erabiltzen baduzu, beharrezkoa izaten da bisitariei pribatutasun-politikan honen berri ematea. Pribatutasun-politika bat ere beharrezkoa izan daiteke bisitariak harremanetarako formularioa edo iruzkinen funtzioa erabil dezaten.

[b]Tip:[/b] Txantiloi eta sorgailu ugari daude Interneten.

Hemen txerta ditzakezu. ([url=admin.php?p=static&action=write&page=privacy-policy]Editatu nazazu![/url])

CookieBanner plugina aktibatzen baduzu, zure bisitariek zuzenean joan ahal izango dira orrialde honetara harremanetarako formulariotik eta iruzkin editoreatik.
';
?>

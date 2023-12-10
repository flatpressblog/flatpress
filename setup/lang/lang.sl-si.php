<?php
/*
 * LangId: Slovenian
 */
$lang ['setup'] = array(
	'setup' => 'Nastavitev'
);

$lang ['locked'] = array(
	'head' => 'Namestitev je zaklenjena',
	'descr' => 'Zdi se, da se namestitev že izvaja: Datoteka zaklepa <code>%s</code> že obstaja.
		
		Če želite znova zagnati namestitev, najprej izbrišite to datoteko.
		
		<strong >Pozor!</strong> Datoteka <code>setup.php</code> in imenik <code>setup/</code> ne smeta ostati na strežniku, zato ju po končani namestitvi izbrišite!
		
		<ul>
		<li><a href="%s">Ok, pelji me na moj blog</a></li>
		<li><a href="%s">Datoteko sem izbrisal, ponovno zaženite namestitev</a></li>
		</ul>'
);

$lang ['err'] = array(
	'setuprun1' => 'Namestitev se izvaja.',
	
	'setuprun2' => 'Namestitev je že v teku: Če ste skrbnik, lahko izbrišete ',
	'setuprun3' => ' za ponovni zagon.',
	'writeerror' => 'Napake pri pisanju',

	'fpuser1' => ' ni veljaven uporabnik. ' . //
		'Uporabniško ime mora biti alfanumerično in ne sme vsebovati presledkov.',
	'fpuser2' => ' ni veljaven uporabnik. ' . //
		'Uporabniško ime lahko vsebuje samo črke, številke in en podčrtaj.',
	'fppwd' => 'Geslo mora vsebovati vsaj 6 znakov in ne sme vsebovati presledkov.',
	'fppwd2' => 'Gesla se ne ujemajo.',
	'email' => ' ni veljaven e-poštni naslov.',
	'www' => ' ni veljaven URL.',
	'error' => '<p><big>Napaka!</big> ' . //
		'Med obdelavo obrazca je prišlo do naslednjih napak:</p><ul>'
);

$lang ['step1'] = array(
	'head' => 'Dobrodošli v FlatPress!',
	'descr' => 'Hvala, ker ste izbrali <strong>FlatPress</strong>.
		
		Preden lahko začnete z novim blogom, morate določiti nekaj malenkosti.
		
		Toda brez skrbi, to ne bo trajalo dolgo!',
	'descrl1' => 'Izberite jezik.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Ni na seznamu?</a>',
	'descrlang' => 'Če na seznamu ne najdete svojega jezika, preverite, ali je na voljo ustrezen <a href="https://wiki.flatpress.org/res:language">jezikovni paket</a> :
		
		<pre>%s</pre>
		
		Če želite namestiti jezikovni paket, preprosto naložite njegovo vsebino v imenik <code>flatpress/</code>. Nato <a href="./setup.php">znova zaženite namestitev</a>.',
	'descrw' => 'Za zagon programa FlatPress potrebujete le imenik, v katerega je mogoče pisati.
		
		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => 'Ustvari uporabnika',
	'descr' => 'Skoraj pripravljen! Ostale so le še naslednje podrobnosti:',
	'fpuser' => 'Uporabniško ime',
	'fppwd' => 'Geslo',
	'fppwd2' => 'Geslo (ponavljanje)',
	'www' => 'Domača stran',
	'email' => 'E-naslov'
);

$lang ['step3'] = array(
	'head' => 'Pripravljen',
	'descr' => '<strong>To je to.</strong>
		
		Ne gre verjeti?
		
		Ne, pravzaprav se šele začenja! Toda bloganje je zdaj vaše delo. ;)
		
		<p style="color:#cc0000">Opozorilo: Zaradi večjega udobja in varnosti priporočamo, da navodila za strežnik konfigurirate v območju za upravljanje z vtičnikom PrettyURL.</p>
		
		<ul>
		<li>Na <a href="%s">glavno stran vašega bloga</a></li>
		<li>Zabavajte se z bloganjem! <a href="%s">Prijavite se zdaj</a></li>
		<li>Želite pohvaliti ali kritizirati? Obiščite nas na <a href="https://www.flatpress.org/">FlatPress.org</a>!</li>
		</ul>
		
		Hvala, ker ste izbrali FlatPress!'
);

$lang ['buttonbar'] = array(
	'next' => 'Dodatno >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Meni';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]Domača stran[/url]
[*][url=?paged=1]Blog[/url]
[*][url=static.php?page=about]O[/url]
[*][url=contact.php]Pišite na[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'Dobrodošli v FlatPress!';
$lang ['samplecontent'] ['entry'] ['content'] = 'To je vzorčna objava, ki vam prikazuje nekatere funkcije [url=https://www.flatpress.org]FlatPress[/url].

Element "more" vam omogoča preskok z osnutka članka na celoten članek.

[more]


[h4]Oblikovanje besedila[/h4]

V programu FlatPress vsebino oblikujete s kodo [url=http://wiki.flatpress.org/doc:plugins:bbcode]BBcode[/url] (Bulletin-Board-Code). To je zelo enostavno z uporabo kode BBCode. Primeri? [b] naredi [b]krepko besedilo[/b], [i] [i]besedilo v poševnem tisku[/i].

[quote]Element [b]quote[/b] lahko uporabite za označevanje citatov. [/quote]

[code]Element \'code\' ustvari razdelek s fiksno širino znakov.
Lahko tudi
   predstavlja vdolbine.[/code]

Elementa \'img\' (slike) in \'url\' (povezave) imata posebne možnosti. Več o tem si lahko preberete v [url=https://wiki.flatpress.org/doc:plugins:bbcode]FlatPress-Wiki[/url].


[h4]Vnosi (blog članki) in statične strani[/h4]

To je vnos, medtem ko je [url=static.php?page=about]O[/url][b]statična stran[/b]. Statične strani za razliko od zapisa ni mogoče komentirati in ni prikazana v seznamu blogovskih zapisov.

Statične strani so uporabne za splošne informacije, na primer fiksna začetna stran ali odtis. Lahko se celo popolnoma odpoveste funkcijam bloga in uporabite FlatPress za ustvarjanje spletnega mesta samo s statičnimi stranmi.

V [url=admin.php]upravnem območju[/url] lahko ustvarite vnose in statične strani ter določite, ali naj bo začetna stran vašega bloga FlatPress statična stran ali pregled bloga.


[h4]Vtičniki[/h4]

FlatPress lahko v veliki meri prilagodite svojim potrebam tako, da ga razširite z [url=https://wiki.flatpress.org/doc:plugins:standard]vtičniki[/url]. BBCode je na primer vtičnik.

Tukaj je še nekaj vzorčne vsebine, ki vam pokaže še več funkcij FlatPress :)

Za vas sta že pripravljeni dve statični strani:
[list]
[*][url=static.php?page=about]O[/url]
[*][url=static.php?page=menu]Meni[/url] (Vsebina te statične strani se prikaže tudi v stranski vrstici vašega bloga - v tem je čar gradnika [b]Blockparser-Widgets[/b]. Na [url=http://wiki.flatpress.org/]FlatPress-Wiki[/url] najdete informacije o tem in še veliko več!)
[/list]

Z vtičnikom [b]PhotoSwipe-Plugin[/b] lahko zdaj še lažje postavite svoje slike, bodisi kot float="left"-  ali  float="right" poravnano posamezno sliko, obdano z besedilom.
Element \'gallery\' lahko uporabite tudi za predstavitev celotnih galerij obiskovalcem. Kako enostavno je to [url="https://wiki.flatpress.org/res:plugins:photoswipe"]tukaj[/url].


[h4]Gradniki[/h4]

Nobeden od elementov v stranski vrstici vašega bloga ni fiksiran, lahko jih premikate, odstranjujete in dodajate nove v območju za upravljanje.

Ti elementi se imenujejo [b]Gradniki[/b]. Seveda je tudi na FlatPress Wiki veliko koristnih informacij [url=https://wiki.flatpress.org/doc:tips:widgets]o tej temi[/url].


[h4]Teme[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
S temo FlatPress Leggero imate na voljo 3 slogovne predloge - od klasične do moderne. Te predloge so odličen začetek za ustvarjanje nečesa lastnega.

[h4]Še več[/h4]

Želite izvedeti več o platformi FlatPress?

[list]
[*]V [url=https://www.flatpress.org/?x]projektnem blogu[/url] lahko izveste, kaj se trenutno dogaja v projektu FlatPress.
[*]Obiščite [url=https://forum.flatpress.org/]podporni forum[/url] za podporo in stik z drugimi uporabniki FlatPress.
[*]Iz [url=https://wiki.flatpress.org/res:themes]wikija[/url] lahko prenesete odlične [b]teme[/b], ki jih je ustvarila skupnost.
[*]Obstajajo tudi odlični [url=https://wiki.flatpress.org/res:plugins]vtičniki[/url] tam.
[*]FlatPressu lahko sledite tudi na [url=https://twitter.com/FlatPress]X (Twitterju)[/url] in [url=https://fosstodon.org/@flatpress]Mastodon[/url].
[/list]


[h4]Kako lahko podpiram FlatPress?[/h4]

[list]
[*]Podprite projekt z [url=http://www.flatpress.org/home/static.php?page=donate]majhno donacijo[/url].
[*][url=https://www.flatpress.org/contact/]Prijavite[/url] nastale napake ali nam pošljite predloge za izboljšave.
[*]Programerji nas lahko podpirajo na [url="https://github.com/flatpressblog/flatpress"]GitHub[/url].
[*]Prevedite FlatPress in njegovo dokumentacijo v [url=https://wiki.flatpress.org/res:language]vaš jezik[/url].
[*]Bodite del skupnosti FlatPress na [url=https://forum.flatpress.org/]forumu podpore[/url].
[*]Povejte svetu, kako odličen je FlatPress! :)
[/list]


[h4]Kaj zdaj?[/h4]

[url=login.php]Prijavite se[/url] in začnite pisati blog v [url=admin.php]Administrativno poročilo[/url].

Zabavajte se! :)

[i]Ekipa podjetja [url=https://www.flatpress.org]FlatPress[/url][/i]

';

$lang ['samplecontent'] ['about'] ['subject'] = 'O';
$lang ['samplecontent'] ['about'] ['content'] = 'Tukaj napišite nekaj o sebi in tem blogu. ([url=admin.php?p=static&action=write&page=about]Delajte z mano![/url])';
?>

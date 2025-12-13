<?php
/*
 * LangId: Dansk
 */
$lang ['setup'] = array(
	'setup' => 'Opsætning'
);

$lang ['locked'] = array(
	'head' => 'Opsætningen er låst',
	'descr' => 'Det ser ud til, at opsætningen allerede kører: Låsefilen <code>%s</code> findes allerede.

		Hvis du vil genstarte opsætningen, skal du slette denne fil først.

		<strong >Giv agt!</strong> Filen <code>setup.php</code> og mappen <code>setup/</code> bør ikke forblive på serveren. Slet dem, når du er færdig med opsætningen!

		<ul>
		<li><a href="%s">Ok, tag mig til min blog</a></li>
		<li><a href="%s">Jeg har slettet filen. Genstart opsætningen.</a></li>
		</ul>'
);

$lang ['err'] = array(
	'setuprun1' => 'Opsætningen er i gang.',

	'setuprun2' => 'Opsætningen kører allerede: Hvis du er administrator, kan du slette ',
	'setuprun3' => ' for at genstarte.',
	'writeerror' => 'Skrivefejl',

	'fpuser1' => ' er ikke en gyldig bruger. ' . //
		'Brugernavnet skal være alfanumerisk og må ikke indeholde mellemrum.',
	'fpuser2' => ' er ikke en gyldig bruger. ' . //
		'Brugernavnet må kun indeholde bogstaver, tal og 1 understregning.',
	'fppwd' => 'Adgangskoden skal indeholde mindst 6 tegn og ingen mellemrum.',
	'fppwd2' => 'Adgangskoderne stemmer ikke overens.',
	'email' => ' er ikke en gyldig e-mailadresse.',
	'www' => ' er ikke en gyldig URL.',
	'error' => '<p><big>Fejl!</big> ' . //
		'Følgende fejl opstod under behandlingen af formularen:</p><ul>'
);

$lang ['step1'] = array(
	'head' => 'Velkommen til FlatPress!',
	'descr' => 'Tak, fordi du valgte <strong>FlatPress</strong>.

		Før du kan komme i gang med din helt nye blog, er du nødt til at specificere et par småting.

		Men bare rolig, det tager ikke lang tid!',
	'descrl1' => 'Vælg dit sprog.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Ikke på listen?</a>',
	'descrlang' => 'Hvis du ikke kan finde dit sprog på listen, kan du se, om der findes <a href="https://wiki.flatpress.org/res:language" target="_blank" rel="external">en passende sprogpakke</a>:

		<pre>%s</pre>

		For at installere en sprogpakke skal du blot indlæse dens indhold i din <code>flatpress/</code>-mappe. Derefter <a href="./setup.php">kør opsætningen igen</a>.',
	'descrw' => '<strong>Det eneste</strong>, du skal bruge for at køre FlatPress, er en <em>skrivbar</em> mappe.

		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => 'Opret bruger',
	'descr' => 'Næsten klar! Der mangler kun følgende detaljer:',
	'fpuser' => 'Brugernavn',
	'fppwd' => 'Adgangskode',
	'fppwd2' => 'Adgangskode (gentagelse)',
	'www' => 'Hjemmeside',
	'email' => 'E-Mail'
);

$lang ['step3'] = array(
	'head' => 'Klar',
	'descr' => '<strong>Det var det.</strong>

		Skal man ikke tro på det?

		Nej, faktisk er <strong>det kun lige begyndt</strong>! Men blogging er nu <em>dit</em> job. ;)
		
		<ul>
		<li>Til <a href="%s">hovedsiden på din blog</a></li>
		<li>God fornøjelse med bloggen! <a href="%s">Log ind nu</a></li>
		<li>Vil du rose eller kritisere os? Besøg os på <a href="https://www.flatpress.org/" target="_blank" rel="external">FlatPress.org</a>!</li>
		</ul>
		
		Tak, fordi du valgte FlatPress!'
);

$lang ['buttonbar'] = array(
	'next' => 'Næste >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Menu';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]Hjemmeside[/url]
[*][url=?paged=1]Blog[/url]
[*][url=static.php?page=about]Om[/url]
[*][url=contact.php]Kontakt[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'Velkommen til FlatPress!';
$lang ['samplecontent'] ['entry'] ['content'] = 'Dette er et eksempel på et indlæg. Det viser dig nogle af [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url]\' funktioner.

"more"-elementet giver dig mulighed for at springe fra artikeloversigten til den komplette artikel.

[more]


[h4]Formatering af tekst[/h4]

I FlatPress formaterer du dit indhold med [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]BBcode[/url] (bulletin board code). Det er meget nemt med BBCode. Vil du have nogle eksempler? [b] laver [b]fed tekst[/b], [i] [i]kursiv[/i].

[quote]Elementet [b]quote[/b] kan bruges til at markere citater.[/quote]

[code]Elementet \'code\' opretter en sektion med en fast tegnbredde.
Den kan også
   repræsentere indrykninger.[/code]

Elementerne \'img\' (billeder) og \'url\' (links) har særlige muligheder. Du kan finde ud af mere om dette i [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]FlatPress-Wiki.[/url].


[h4]Indlæg (blogartikler) og statiske sider[/h4]

Dette er en post, mens [url=static.php?page=about]Om[/url] er en [b]statisk side[/b]. En statisk side kan, i modsætning til et indlæg, ikke kommenteres og vises ikke i oversigterne over blogindlæg.

Statiske sider er nyttige til generel information, f.eks. en fast startside eller et aftryk. Du kan endda helt undvære blogfunktionerne og bruge FlatPress til at oprette en hjemmeside kun med statiske sider.

I [url=admin.php]administrationsområdet[/url] kan du oprette poster og statiske sider - og definere, om startsiden på din FlatPress-blog skal være en statisk side eller blogoversigten.


[h4]Plugins[/h4]

Du kan i vid udstrækning tilpasse FlatPress til dine behov ved at udvide det med [url=https://wiki.flatpress.org/doc:plugins:standard target=_blank rel=external]Plugins[/url]. BBCode er for eksempel et plugin.

Her er nogle flere eksempler på indhold, der viser dig endnu flere FlatPress-funktioner :)

To statiske sider er allerede forberedt til dig:
[list]
[*][url=static.php?page=about]Om[/url]
[*][url=static.php?page=menu]Menu[/url] (Indholdet af denne statiske side vises også i sidebjælken på din blog - det er magien ved [b]blockparser-widget[/b]. [url=https://wiki.flatpress.org/ target=_blank rel=external]FlatPress-Wiki[/url] har oplysninger om dette og meget mere!)
[/list]

Med [b]PhotoSwipe-pluginet[/b] kan du nu placere dine billeder endnu nemmere, enten som et float="left"- eller float="right"-justeret enkeltbillede, omgivet af teksten.
Du kan endda præsentere hele gallerier for dine besøgende med elementet \'gallery\'. Du kan finde ud af, hvor nemt det er [url=https://wiki.flatpress.org/doc:plugins:photoswipe target=_blank rel=external]her.[/url].


[h4]Widgets[/h4]

Ingen af elementerne i sidepanelet på din blog er faste, du kan flytte dem, fjerne dem og tilføje nye i administrationsområdet.

Disse elementer kaldes [b]widgets[/b]. Selvfølgelig har [url=https://wiki.flatpress.org/doc:tips:widgets target=_blank rel=external]FlatPress Wiki[/url] også en masse nyttige oplysninger om dette emne.


[h4]Temaer[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
Med FlatPress Leggero-temaet har du 3 stilskabeloner til din rådighed - fra klassisk til moderne. Disse skabeloner er en vidunderlig start på at skabe noget af dit eget.


[h4]Endnu mere[/h4]

Vil du gerne vide mere om FlatPress?

[list]
[*]I [url=https://www.flatpress.org/?x target=_blank rel=external]projektbloggen[/url] kan du finde ud af, hvad der i øjeblikket foregår i FlatPress-projektet.
[*]Besøg [url=https://forum.flatpress.org/ target=_blank rel=external]support forum[/url] for support og kontakt med andre FlatPress-brugere.
[*]Download fantastiske [b]temaer[/b] skabt af fællesskabet fra [url=https://wiki.flatpress.org/res:themes target=_blank rel=external]Wiki[/url].
[*]Der er også gode [url=https://wiki.flatpress.org/res:plugins target=_blank rel=external]plugins[/url] der.
[*]Få [url=https://wiki.flatpress.org/res:language]oversættelsespakken[/url] til dit sprog.
[*]Du kan også følge FlatPress på [url=https://fosstodon.org/@flatpress target=_blank rel=external]Mastodon[/url].
[/list]


[h4]Hvordan kan jeg støtte FlatPress?[/h4]

[list]
[*]Støt projektet med en [url=https://www.flatpress.org/home/static.php?page=donate target=_blank rel=external]lille donation[/url].
[*][url=https://www.flatpress.org/contact/ target=_blank rel=external]Rapporter[/url] fejl, der er opstået, eller send os forslag til forbedringer.
[*]Programmører er velkomne til at støtte os på [url=https://github.com/flatpressblog/flatpress target=_blank rel=external]GitHub[/url].
[*]Oversæt FlatPress og dens dokumentation til [url=https://wiki.flatpress.org/res:language target=_blank rel=external]dit sprog[/url].
[*]Vær en del af FlatPress-fællesskabet i [url=https://forum.flatpress.org/ target=_blank rel=external]supportforummet[/url].
[*]Fortæl verden, hvor fantastisk FlatPress er! :)
[/list]


[h4]Så hvad nu?[/h4]

[url=login.php]Log ind[/url] for at begynde at blogge i [url=admin.php]Administrationsrapporten[/url].

God fornøjelse! :)

[i][url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url]-teamet[/i]

';

$lang ['samplecontent'] ['about'] ['subject'] = 'Om';
$lang ['samplecontent'] ['about'] ['content'] = 'Skriv noget om dig selv og denne blog her. ([url=admin.php?p=static&action=write&page=about]Arbejd med mig![/url])';

$lang ['samplecontent'] ['privacy-policy'] ['subject'] = 'Privatlivspolitik';
$lang ['samplecontent'] ['privacy-policy'] ['content'] = 'I nogle lande, f.eks. hvis du bruger Akismet Antispam-tjenesten, er det nødvendigt at give dine besøgende en privatlivspolitik. En privatlivspolitik kan også være nødvendig, hvis den besøgende kan bruge kontaktformularen eller kommentarfunktionen.

[b]Tip:[/b] Der findes masser af skabeloner og generatorer på internettet.

Du kan indsætte dem her. ([url=admin.php?p=static&action=write&page=privacy-policy]Rediger mig![/url])

Hvis du aktiverer CookieBanner-pluginet, vil dine besøgende kunne gå direkte til denne side i kontaktformularen og i kommentarfunktionen.
';
?>

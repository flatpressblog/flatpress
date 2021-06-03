<?php
/*
 * LangId: Nederlands
 */
$lang ['locked'] = array(
	'head' => 'Setup is vergrendeld',
	'descr' => 'Het lijkt erop dat je de installatie al hebt uitgevoerd, omdat 
		we een lockfile hebben gevonden <code>%s</code>.
		
		Als je de installatie opnieuw moet starten, verwijder dan eerst dit bestand.
		
		<strong >Denk eraan!</strong> Het is niet veilig om <code>setup.php</code> te bewaren en ook de <code>setup/</code> directorie op jouw server, we adviseren om dit te verwijderen!
		
		<ul>
		<li><a href="%s">Breng me terug naar mijn blog</a></li>
		<li><a href="%s">Ik heb het bestand verwijderd, start de installatie opnieuw</a></li>
		</ul>'
);

$lang ['step1'] = array(
	'head' => 'Welkom bij FlatPress!',
	'descr' => 'Bedankt dat je gekozen hebt voor <strong>FlatPress</strong>.
		
		Voordat je plezier kunt maken met je gloednieuwe blog, moeten we je een paar vragen stellen. 
		
		Maak je geen zorgen, het zal niet lang duren!',
	'descrl1' => 'Kies je taal.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Niet in de lijst?</a>',
	'descrlang' => 'Als je jouw taal niet in deze lijst ziet, wilt je misschien zien of er <a href="https://wiki.flatpress.org/res:language"> een taal pack is</a> voor deze versie:
		
		<pre>%s</pre>
		
		Als je het taalpakket wilt installeren, uploadt de inhoud van het pakket in jouw <code>flatpress/</code>, en overschrijf alles, dan <a href="./setup.php">herstart de setup</a>.',
	'descrw' => 'Het <strong>enige</strong> wat je nodig hebt voor FlatPress om mee te werken <em>beschrijfbare</em> directorie. 
		
		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => 'Maak gebruiker',
	'descr' => 'Je bent bijna klaar, vul de volgende details:',
	'fpuser' => 'Gebruikersnaam',
	'fppwd' => 'Wachtwoord',
	'fppwd2' => 'Herhaal wachtwoord',
	'www' => 'Start pagina',
	'email' => 'E-Mail'
);

$lang ['step3'] = array(
	'head' => 'Klaar',
	'descr' => '<strong>Einde van dit verhaal</strong>. 
		
		Ongelofelijk? 
		
		En je hebt gelijk: <strong>het verhaal is net begonnen</strong>, maar <strong>schrijven is aan jou</strong>!
		
		<ul>
		<li>Zie <a href="%s">hoe de startpagina eruit ziet</a></li>
		<li>Veel pleier! <a href="%s">Log nu in!</a></li>
		<li>Heb je zin om ons een bericht te sturen? <a href="https://www.flatpress.org/">Ga naar FlatPress.org!</a></li>
		</ul>
		
		En bedankt dat je gekoen hebt voor FlatPress!'
);

$lang ['buttonbar'] = array(
	'next' => 'Volgende >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Menu';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]Home[/url]
[*][url=?paged=1]Blog[/url]
[*][url=static.php?page=about]About[/url]
[*][url=contact.php]Contact[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'Welkom bij FlatPress!';
$lang ['samplecontent'] ['entry'] ['content'] = 'Dit is een voorbeeld vermelding, om je enkele functies te tonen van [url=https://www.flatpress.org]FlatPress[/url].

Hoe met een tag je in staat stelt om een "sprong" te maken tussen een fragment en het volledige artikel.

[more] 


[h4]Styling[/h4]

De standaard manier om jouw inhoud te stijlen en op te maken is [url=http://wiki.flatpress.org/doc:plugins:bbcode]BBcode[/url] (bulletin board code). BBCode is een eenvoudige manier om je berichten te stijlen. De meest voorkomende codes zijn toegestaan. Zoals [b] voor [b]bold[/b] (html: strong), [i] voor [i]italics[/i] (html: em), etc.

[quote]Er zijn ook [b]citaten[/b] blocks om je favoriete citaten te tonen. [/quote]

[code]And \'code\' geeft jouw fragmenten op een monospaced manier weer.
Het ondersteunt ook ingesprongen inhoud.[/code]

img en url tag hebben ook speciale opties. Je kunt meer te weten komen over de [url=https://wiki.flatpress.org/doc:plugins:bbcode]FP wiki[/url].


[h4]Vermeldingen (berichten) en statische pagina[/h4]

Dit is een vermelding, terwijl [url=static.php?page=about]About[/url] is een [b]statisce pagina[/b]. Een statische pagina is een vermelding (een bericht) waar geen commentaar op kan worden gegeven, en die niet samen met de normale vermeldingen van de blog verschijnt.

Statische pagina zijn nuttig om algemene informatie te geven. Je kunt van een van deze paginas ook de [b]openingspagina[/b] maken voor jouw bezoekers. Dit betekent dat je met FlatPress ook een complete niet-blogsite kunt draaien. De optie om een statische pagina van jouw startpagina te maken, bevindt zich in de [b]optie menu[/b] van de [url=admin.php]admin area[/url].


[h4]Plugins[/h4]

FlatPress is zeer aanpasbaar en ondersteunt [url=https://wiki.flatpress.org/doc:plugins:standard]plugins[/url] om zijn kracht uit te breiden. BBCode is een plugin op zich zelf.

We hebben wat meer voorbeeld inhoud gemaakt, om je enkele van de FP goed verborgen functies en bouwstenen te laten zien :) 
Je kunt twee [b]statische paginas[/b] vinden die klaar zijn om in je inhoud te accepteren:
[list]
[*][url=static.php?page=about]About me[/url]
[*][url=static.php?page=menu]Menu[/url] (merk dat de links op deze pagina ook in jouw zijbalk verschijnen - dit is de magie van de [b]blockparser widget[/b]. Zie de [url=http://wiki.flatpress.org/doc:faq]FAQ[/url] voor dit en meer!)
[/list]


[h4]Widgets[/h4]

Er is geen enkel vast element in de zijbalk(en). Alle elementen die je kunt vinden in de balken die deze tekst aansturen, zijn volledig positioneerbaar en de meeste zijn ook aanpasbaar. Sommige thema\'s bieden zelfs een paneelinterface in het beheergebied.  

Deze elementen worden genoemd [b]widgets[/b]. Voor meer over widgets en [url=https://wiki.flatpress.org/doc:tips:widgets]some tips[/url] om leuke effecten te krijgen, kijk dan eens naar de [url=https://wiki.flatpress.org/]wiki[/url].


[h4]Zie meer[/h4]

Wil je meer zien?

[list]
[*]Volg de [url=https://www.flatpress.org/?x]official blog[/url] om te weten wat is gaande in de FlatPress wereld
[*]Bezoek de [url=https://forum.flatpress.org/]forum[/url] voor ondersteuning
[*]Krijg [b]mooie themas[/b] van [url=https://wiki.flatpress.org/res:themes]other users\' submissions[/url]!
[*]Bekijk de [url=https://wiki.flatpress.org/res:plugins]unofficial plugins[/url]
[*]Krijg [url=https://wiki.flatpress.org/res:language]translation pack[/url] voor jouw taal 
[/list]


[h4]Hoe kan ik helpen?[/h4]

[list]
[*][url=https://www.flatpress.org/contact/]Contact ons[/url] om bugs en aanpassingen te melden.
[*]Draag bij aan de ontwikkeling van Flatpress op [url="https://github.com/flatpressblog/flatpress"]GitHub[/url].
[*]Vertaal FlatPress in de documentatie in [url=https://wiki.flatpress.org/res:language]your language[/url].
[*]Deel jouw kennis en maak contact met andere FlatPress-gebruikers op de [url=https://forum.flatpress.org/]forum[/url].
[*]Verspreid het woord! :)
[/list]


[h4]En wat nu?[/h4]

Nu kan je [url=login.php]Login[/url] om bij de [url=admin.php]Administration Area[/url] en begin met vermeldingen maken!

Veel plezier! :) 

[i]The [url=https://www.flatpress.org]FlatPress[/url] Team[/i]
	
';

$lang ['samplecontent'] ['about'] ['subject'] = 'About';
$lang ['samplecontent'] ['about'] ['content'] = "Schrijf hier iets over jezelf. ([url=admin.php?p=static&action=write&page=about]Edit me![/url])";

?>

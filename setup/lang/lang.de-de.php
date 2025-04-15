<?php
/*
 * LangId: Deutsch
 */
$lang ['setup'] = array(
	'setup' => 'Einrichtung'
);

$lang ['locked'] = array(
	'head' => 'Die Einrichtung ist gesperrt',
	'descr' => 'Sieht so aus, als würde die Einrichtung bereits laufen: Die Sperr-Datei <code>%s</code> existiert bereits.

		Wenn du die Einrichtung noch einmal neu starten möchtest, lösche bitte zuerst diese Datei.

		<strong >Achtung!</strong> Die Datei <code>setup.php</code> und das Verzeichnis <code>setup/</code> sollten nicht auf dem Server bleiben. Bitte lösche sie nach Abschluss der Einrichtung!

		<ul>
		<li><a href="%s">Ok, bring mich zu meinem Blog</a></li>
		<li><a href="%s">Ich habe die Datei gelöscht. Einrichtung neu starten.</a></li>
		</ul>'
);

$lang ['err'] = array(
	'setuprun1' => 'Die Einrichtung läuft.',

	'setuprun2' => 'Die Einrichtung läuft bereits: Wenn du der Administrator bist, kannst du ',
	'setuprun3' => ' löschen, um neu zu starten.',
	'writeerror' => 'Fehler beim Schreiben',

	'fpuser1' => ' ist kein gültiger Benutzer. ' . //
	'Der Benutzername muss alphanumerisch sein und darf keine Leerzeichen enthalten.',
	'fpuser2' => ' ist kein gültiger Benutzer. ' . //
	'Der Benutzername darf nur Buchstaben, Zahlen und 1 Unterstrich enthalten.',
	'fppwd' => 'Das Passwort muss mindestens 6 Zeichen und darf keine Leerzeichen enthalten.',
	'fppwd2' => 'Die Passwörter stimmen nicht überein.',
	'email' => ' ist keine gültige E-Mail Adresse.',
	'www' => ' ist keine gültige URL.',
	'error' => '<p><big>Fehler!</big> ' . //
	'Bei der Bearbeitung des Formulars sind die folgenden Fehler aufgetreten:</p><ul>'
);

$lang ['step1'] = array(
	'head' => 'Willkommen bei FlatPress!',
	'descr' => 'Danke, dass du dich für <strong>FlatPress</strong> entschieden hast.

		Bevor du mit deinem brandneuen Blog loslegen kannst, musst du noch ein paar Kleinigkeiten angeben.

		Aber keine Sorge, es dauert nicht lange!',
	'descrl1' => 'Wähle deine Sprache.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Nicht in der Liste?</a>',
	'descrlang' => 'Wenn du deine Sprache nicht in der Liste findest, schau einmal nach, ob es <a href="https://wiki.flatpress.org/res:language" target="_blank" rel="external">ein passendes Sprachpaket</a> gibt:

		<pre>%s</pre>

		Für die Installation eines Sprachpakets lade dessen Inhalt einfach in dein <code>flatpress/</code>-Verzeichnis. Dann <a href="./setup.php">starte die Installation erneut</a>.',
	'descrw' => '<strong>Das Einzige</strong>, was du für den Betrieb von FlatPress benötigst, ist ein <em>beschreibbares</em> Verzeichnis.

		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => 'Benutzer anlegen',
	'descr' => 'Fast fertig! Nur noch die folgenden Details:',
	'fpuser' => 'Benutzername',
	'fppwd' => 'Passwort',
	'fppwd2' => 'Passwort (Wiederholung)',
	'www' => 'Homepage',
	'email' => 'E-Mail'
);

$lang ['step3'] = array(
	'head' => 'Fertig',
	'descr' => '<strong>Das war\'s schon.</strong>

		Nicht zu glauben?

		Nein, tatsächlich <strong>geht es jetzt erst richtig los</strong>! Aber das Bloggen ist nun <em>deine</em> Aufgabe ;)

		<ul>
		<li>Zur <a href="%s">Hauptseite deines Blogs</a></li>
		<li>Viel Spaß beim Bloggen! <a href="%s">Jetzt einloggen</a></li>
		<li>Du möchtest Lob oder Kritik loswerden? Besuche uns auf <a href="https://www.flatpress.org/" target="_blank" rel="external">FlatPress.org</a>!</li>
		</ul>

		Danke, dass du dich für FlatPress entschieden hast!'
);

$lang ['buttonbar'] = array(
	'next' => 'Weiter >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Menü';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]Startseite[/url]
[*][url=?paged=1]Blog[/url]
[*][url=static.php?page=about]Über[/url]
[*][url=contact.php]Kontakt[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'Willkommen bei FlatPress!';
$lang ['samplecontent'] ['entry'] ['content'] = 'Das ist ein Beispiel-Beitrag. Er zeigt dir einige Funktionen von [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url].

Das "more"-Element erlaubt es dir, vom Anriss des Beitrags zum kompletten Artikel zu springen.

[more]


[h4]Textformatierung[/h4]

In FlatPress formatierst du deine Inhalte mit [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]BBcode[/url] (Bulletin-Board-Code). Mit BBCode geht das sehr einfach. Beispiele gefällig? [b] macht [b]fetten Text[/b], [i] [i]kursiven[/i].

[quote]Mit dem [b]quote[/b]-Element lassen sich Zitate auszeichnen.[/quote]

[code]Das \'code\'-Element erzeugt einen Abschnitt mit fester Zeichenbreite.
Es kann auch
   Einrückungen darstellen.[/code]

Die Elemente \'img\' (Bilder) und \'url\' (Links) haben spezielle Optionen. Mehr darüber erfährst du im [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]FlatPress-Wiki[/url].


[h4]Einträge (Blogartikel) und statische Seiten[/h4]

Das hier ist ein Eintrag, während [url=static.php?page=about]Über[/url] eine [b]statische Seite[/b] ist. Eine statische Seite kann im Gegensatz zum Eintrag nicht kommentiert werden und taucht auch nicht in den Auflistungen der Blogeinträge auf.

Statische Seiten lassen sich prima für generelle Informationen einsetzen, zum Beispiel eine feste Startseite oder das Impressum. Man könnte sogar komplett auf die Blog-Funktionen verzichten und mit FlatPress eine Website mit ausschließlich statischen Seiten erstellen.

Im [url=admin.php]Administrationsbereich[/url] kannst du Einträge und statische Seiten erstellen - und festlegen, ob die Startseite deines FlatPress-Blogs eine statische Seite oder die Blog-Übersicht sein soll.


[h4]Plugins[/h4]

Du kannst FlatPress umfassend an deine Bedürfnisse anpassen, indem du es mit [url=https://wiki.flatpress.org/doc:plugins:standard target=_blank rel=external]Plugins[/url] erweiterst. BBCode ist z.B. ein Plugin.

Es folgt noch etwas mehr Beispiel-Inhalt, der dir noch mehr FlatPress-Funktionen zeigt :)

Zwei statische Seiten sind für dich schon vorbereitet:
[list]
[*][url=static.php?page=about]Über[/url]
[*][url=static.php?page=menu]Menü[/url] (Der Inhalt dieser statischen Seite taucht auch in der Seitenleiste deines Blogs auf - das ist die Magie des [b]Blockparser-Widgets[/b]. Das [url=https://wiki.flatpress.org/ target=_blank rel=external]FlatPress-Wiki[/url] hat Informationen dazu, und noch viel mehr!)
[/list]

Mit dem [b]PhotoSwipe-Plugin[/b] platzierst du jetzt noch einfacher deine Bilder, wahlweise als float="left" oder float="right" ausgerichtetes Einzelbild, vom Text umschlossen.
Du kannst sogar mit dem Element \'gallery\' deinen Besuchern ganze Galerien präsentieren. Wie einfach es funktioniert, [url=https://wiki.flatpress.org/res:plugins:photoswipe target=_blank rel=external]erfährst du hier[/url].


[h4]Widgets[/h4]

Keines der Elemente in der Seitenleiste deines Blogs ist fest vorgegeben, du kannst sie im Administrationsbereich verschieben, entfernen und neue hinzufügen.

Diese Elemente werden [b]Widgets[/b] genannt. Natürlich hat das FlatPress-Wiki auch zu diesem Thema [url=https://wiki.flatpress.org/doc:tips:widgets target=_blank rel=external]viele hilfreiche Informationen[/url].


[h4]Themes[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
Mit dem FlatPress-Leggero-Theme stehen dir 3 Stil-Vorlagen zur Verfügung - von Klassisch bis Modern. Diese Vorlagen sind ein wunderbarer Start, etwas eigenes zu kreieren.


[h4]Noch mehr[/h4]

Du möchtest gern mehr über FlatPress wissen?

[list]
[*]Im [url=https://www.flatpress.org/?x target=_blank rel=external]Projekt-Blog[/url] erfährst du, was im FlatPress-Projekt aktuell los ist.
[*]Besuche das [url=https://forum.flatpress.org/ target=_blank rel=external]Supportforum[/url] für Unterstützung und den Kontakt zu anderen FlatPress-Benutzern.
[*]Lade dir großartige von der Community erstellte [b]Themes[/b] aus dem [url=https://wiki.flatpress.org/res:themes target=_blank rel=external]Wiki[/url] herunter.
[*]Dort gibt es auch tolle [url=https://wiki.flatpress.org/res:plugins target=_blank rel=external]Plugins[/url].
[*]Hole dir das [url=https://wiki.flatpress.org/res:language target=_blank rel=external]Übersetzungspaket[/url] für deine Sprache.
[*]FlatPress kannst du auch auf [url=https://fosstodon.org/@flatpress target=_blank rel=external]Mastodon[/url] folgen.
[/list]


[h4]Wie kann ich FlatPress unterstützen?[/h4]

[list]
[*]Unterstütze das Projekt mit einer [url=https://www.flatpress.org/home/static.php?page=donate target=_blank rel=external]kleinen Spende[/url].
[*][url=https://www.flatpress.org/contact/ target=_blank rel=external]Melde[/url] aufgetretene Fehler oder schick uns Verbesserungsvorschläge.
[*]Programmierer sind herzlich eingeladen, uns auf [url=https://github.com/flatpressblog/flatpress target=_blank rel=external]GitHub[/url] zu unterstützen.
[*]Übersetze FlatPress und seine Dokumentation in [url=https://wiki.flatpress.org/res:language target=_blank rel=external]deine Sprache[/url].
[*]Sei ein Teil der FlatPress-Gemeinschaft im [url=https://forum.flatpress.org/ target=_blank rel=external]Supportforum[/url].
[*]Erzähl der Welt, wie toll FlatPress ist! :)
[/list]


[h4]So, und nun?[/h4]

[url=login.php]Logge dich ein[/url], um im [url=admin.php]Administrationsbereich[/url] mit dem Bloggen zu beginnen.

Viel Spaß! :)

[i]Das [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url]-Team[/i]

';

$lang ['samplecontent'] ['about'] ['subject'] = 'Über';
$lang ['samplecontent'] ['about'] ['content'] = 'Schreib hier etwas über dich und diesen Blog. ([url=admin.php?p=static&action=write&page=about]Bearbeite mich![/url])';

$lang ['samplecontent'] ['privacy-policy'] ['subject'] = 'Datenschutzerklärung';
$lang ['samplecontent'] ['privacy-policy'] ['content'] = 'In einigen Ländern ist es notwendig, wenn du zum Beispiel den Dienst von Akismet Antispam nutzt, deinen Besucherns eine Datenschutzerklärung bereitzustellen. Eine Datenschutzerklärung kann ebenfalls notwendig sein, wenn der Besucher das Kontaktformular oder die Kommentarfunktion nutzen kann.

[b]Tipp:[/b] Es gibt im Internet jede Menge Vorlagen und Generatoren.

An dieser Stelle kannst du diese dann einfügen. ([url=admin.php?p=static&action=write&page=privacy-policy]Bearbeite mich![/url])

Wenn du das CookieBanner Plugin aktivierst, wird im Kontaktformular und in der Kommentarfunktion deinen Besuchern es möglich sein, direkt zu dieser Seite zu gelangen.
';
?>

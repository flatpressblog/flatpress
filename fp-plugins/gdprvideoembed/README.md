# DSGVO Video-Embed

Einfache Zwei-Klick-Lösung zur DSGVO-konformen Einbettung von YouTube- Facebook und Vimeo- Videos.

Dieses Skript ersetzt alle eingebetteten Videos von YouTube, Facebook und Vimeo durch den Hinweis, dass es sich um eingebettete Videos handelt, welche beim Abspielen Daten an den Video-Betreiber senden. Es wird ein externer Link zum Video auf der Betreiberseite angezeigt sowie ein Button, mit dem das Video trotzdem eingebettet abgespielt werden kann.  
Normal eingebettete YouTube-Videos werden durch solche mit „erweitertem Datenschutzmodus“ ersetzt.

**Wichtig:**
Das Script funktioniert ohne weitere Anpassungen der iframes nur, wenn im Browser des Seitenbesuchers JavaScript aktiviert ist. Sollte JavaScript deaktiviert sein, wird eine Verbindung zu YouTube/Vimeo hergestellt. Das Video kann dann zwar nicht abgespielt werden, es werden aber Daten übertragen und u.a. auch Google-Fonts geladen. *Um dieses Problem zu umgehen, müssen die eingebundenen iframes angepasst werden!* (siehe nächster Abschnitt) Bei deaktiviertem JavaScript wird ein Hinweis angezeigt, dass das Video nur mit aktiviertem JavaScript abgesipielt werden kann.

## How-To

### 1. Einbinden des Scripts
Die beiden Scripts `dsgvo-video-embed.css` und `dsgvo-video-embed.js` in das `<head>` Element einfügen.

*Hinweis:* Das Skript sollte wirklich in das `<head>` Element eingefügt werden und nicht (wie auch üblich) vor den schließenden `<body>`-Tag, da die `iframes` sonst nicht rechtzeitig vom Verbindungsaufbau zu YouTube bzw. Vimeo abgehalten werden können!

```html
<head>
  …
  <link rel="stylesheet" href="dsgvo-video-embed.css">
  <script src="dsgvo-video-embed.js"></script>
</head>
```

### 2. Anpassen der iframes
Von YouTube bzw. Vimeo wird ein `<iframe>` zum einbetten des Videos angebotenen. Dieser hat ein `src`-Attribut, welches auf die abzuspielende Videodatei auf den Servern der Anbieter verweist. Um sicher zu gehen, dass auch bei deaktiviertem JavaScript keine Verbindung zum Anbieter ohne Zustimmung aufgebaut wird, muss das `src`-Attribut in `data-src` umbenannt werden:

Aus
```html
  <!-- Beispiel YouTube -->
  <iframe width="560" height="315" src="https://www.youtube.com/embed/hZ3w5VMr8gw?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
  <!-- Beispiel Vimeo -->
  <iframe src="https://player.vimeo.com/video/10149605" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
```
wird
```html
  <!-- Beispiel YouTube -->
  <iframe width="560" height="315" data-src="https://www.youtube.com/embed/hZ3w5VMr8gw?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
  <!-- Beispiel Vimeo -->
  <iframe data-src="https://player.vimeo.com/video/10149605" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
```
Lediglich `src` wird zu `data-src`, sonst bleibt alles unverändert.

Dieser zweite Schritt ist optional, wird aber dringend empfohlen. Es gab wohl schon Abmahnungen wegen einem Verbindungsaufbau zu Google-Fonts ohne Einwilligung.

## Konfiguration

Das Skript ist jeweils mit einem Standard-Text für YouTube und Vimeo ausgestattet. Wer damit zufrieden ist, muss nichts weiter ändern!

Wer den Text jedoch anpassen will, findet ihn in der jweiligen Sprachdatei `lang/lang.{langId}.php`.

Natürlich kann dieser Text beliebig verändert werden (wie das CSS auch). Wenn man jedoch nur inhaltliche Anpassungen vornehmen will, kann man sich an das vorhandene HTML-Gerüst halten:

```html
<strong>Titel</strong>
<div>
    <p><b>Hinweis:</b> Text</p>
</div>
<a class="video-link" href="https://youtu.be/%id%">Link</a>
<button>Video abspielen</button>
```

Der Platzhalter `%id%` wird durch die Video-ID ersetzt.
Das Element `<button>` wird mit einem Eventlistener versehen, der den Platzhalter wieder mit dem ursprünglichen `<iframe>` ersetzt.

## Rechtlicher Hinweis / Disclaimer

Der Autor dieses Skripts ist kein Jurist und bietet das Skript frei von jeder Haftung an. Wer Videos einbettet, wird hierzu auf jeden Fall einen eigenen Abschnitt in die Datenschutzerklärung der eigenen Website aufnehmen müssen. Ob der Einsatz dieses Skriptes vollkommen rechtssicher ist, kann nicht garantiert werden.

*Der Gebrauch erfolgt auf eigenes Risiko!*

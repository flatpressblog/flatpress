<?php
/*
 * LangId: English
 */
$lang ['locked'] = array(
	'head' => 'L\'installazione è stata bloccata',
	'descr' => 'Sembra che tu abbia già avviato l\'installazione, perché 
		abbiamo trovato il lockfile <code>%s</code>.
		
		Se vuoi far ripartire l\'installazione, elimina prima questo file.
		
		<strong >Ricorda!</strong> Non è sicuro mantenere il file <code>setup.php</code> e la cartella <code>setup/</code> sul tuo server, ti consigliamo di eliminarli!
		
		<ul>
		<li><a href="%s">Ok, fammi tornare al mio blog</a></li>
		<li><a href="%s">Ho eliminato il file, riavvia l\'installazione</a></li>
		</ul>'
);

$lang ['step1'] = array(
	'head' => 'Benvenuto in FlatPress!',
	'descr' => 'Grazie per aver scelto <strong>FlatPress</strong>.
		
		Prima che tu ti diverta con il tuo blog nuovo di zecca, dobbiamo farti qualche domanda. 
		
		Non preoccuparti, non ci vorrà molto!',
	'descrl1' => 'Seleziona la tua lingua.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Non è in elenco?</a>',
	'descrlang' => 'Se non vedi la tua lingua in questo elenco, potresti vedere se qui c\'è <a href="https://wiki.flatpress.org/res:language">un pacchetto di lingua</a> per questa versione:
		
		<pre>%s</pre>
		
		Per installare il pacchetto di lingua, carica il contenuto del pacchetto nela tua copia di <code>flatpress/</code>, e sovrascrivi tutto, poi <a href="./setup.php">riavvia questa installazione</a>.',
	'descrw' => 'L\'<strong>unica cosa</strong> di cui ha bisogno FlatPress per funzionare è una cartella <em>scrivibile</em>. 
		
		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => 'Crea utente',
	'descr' => 'Hai quasi finito, inserisci i seguenti dettagli:',
	'fpuser' => 'Nome utente',
	'fppwd' => 'Password',
	'fppwd2' => 'Ridigita la password',
	'www' => 'Home Page',
	'email' => 'E-Mail'
);

$lang ['step3'] = array(
	'head' => 'Finito',
	'descr' => '<strong>Fine della storia</strong>. 
		
		Non ci credi? 
		
		E hai ragione: <strong>la storia è appena cominciata</strong>, ma <strong>sta a te scriverla</strong>!
		
		<ul>
		<li>Guarda <a href="%s">come appare la home page</a></li>
		<li>Buon divertimento! <a href="%s">Connettiti ora!</a></li>
		<li>Ti va di scriverci un messaggio? <a href="https://www.flatpress.org/">Vai su FlatPress.org!</a></li>
		</ul>
		
		E grazie per aver scelto FlatPress!'
);

$lang ['buttonbar'] = array(
	'next' => 'Avanti >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Menu';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]Home[/url]
[*][url=?paged=1]Blog[/url]
[*][url=static.php?page=about]Informazioni sul sito[/url]
[*][url=contact.php]Contatti[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'Benvenuto su FlatPress!';
$lang ['samplecontent'] ['entry'] ['content'] = 'Questo è un articolo di prova, inserito mer mostrarti alcune delle funzioni di [url=https://www.flatpress.org]FlatPress[/url].

Il tag more ti consente di crare un "salto" tra un estratto e l\'articolo completo.

[more] 


[h4]Aspetto[/h4]

Il modo predefinito dell\'aspetto e del contenuto dell\'articolo è [url=http://wiki.flatpress.org/doc:plugins:bbcode]BBcode[/url] (bulletin board code). BBCode è un modo facile per dare un aspetto elegante ai tuoi articoli. Sono consentiti i codici più comuni, come [b] per [b]grassetto[/b] (html: strong), [i] per [i]corsivo[/i] (html: em), ecc.

[quote]Ci sono anche i blocchi [b]citazione[/b] per mostrare le tue citazioni preferite. [/quote]

[code]E il tag \'code\' mmostra dei pezzetti di codice in uno stile monospaziale.
Inoltre supporta
   il contenuto indentato.[/code]

I tag img e url tag hanno inoltre delle opzioni speciali. Puoi saperne di più sul [url=https://wiki.flatpress.org/doc:plugins:bbcode]Wiki di FlatPress[/url].


[h4]Articoli e Pagine statiche[/h4]

Queto è un articolo, mentre [url=static.php?page=about]Informazioni su[/url] è una [b]pagina statica[/b]. Una pagina statica è un articolo che non può essere commentato e che non compare insieme agli altri articoli del blog.

Le pagine statiche sono utili per creare pagine di informazioni generali. Puoi anche rendere una di queste pagine la [b]pagina di apertura[/b] per i tuoi visitatori. Questo vuol dire che con FlatPress puoi anche costruire un sito completo che non sia un blog. L\'opzione per rendere una pagina statica la pagina iniziale del tuo sito si trova nel [b]pannello delle opzioni[/b] del [url=admin.php]pannello di controllo[/url].


[h4]Plugins[/h4]

FlatPress è molto personalizzabile e supporta dei [url=https://wiki.flatpress.org/doc:plugins:standard]plugins[/url] per estenderne le funzioni. BBCode stesso è un plugin.

Abbiamo creato altri contenuti di esempio per mostrarvi alcune delle funzioni ben nascoste di FP e chicche :) 
Puoi trovare due [b]pagine statiche[/b] pronte per accettare i tuoi contenuti:
[list]
[*][url=static.php?page=about]Chi sono[/url]
[*][url=static.php?page=menu]Menu[/url] (nota che i collegamenti in questa pagine appariranno anche sulla barra laterale - questa è una magia del [b]widget blockparser[/b]. Consulta le [url=http://wiki.flatpress.org/doc:faq]FAQ[/url] per questo e altro!)
[/list]


[h4]Widget[/h4]

Non c\'è un singolo elemento fisso nelle barre laterali. Tutti gli elementi che si trovano nelle barre circondando questo testo sono completamente riposizionabili, e molti di loro sono personalizzabili. Alcun temi forniscono anche uno specifico pannello nel pannello di controllo.  

Questi elementi si chiamano [b]widget[/b]. Per saperne di più sui widget e [url=https://wiki.flatpress.org/doc:tips:widgets]alcuni consigli[/url] per ottenere dei bellissimi effetti, dai un\'occhiata sul [url=https://wiki.flatpress.org/]wiki[/url].


[h4]Saperne di più[/h4]

Vuoi saperne di più?

[list]
[*]Segui il [url=https://www.flatpress.org/?x]blog ufficiale[/url] per sapere cosa succede nel mondo di FlatPress
[*]Visita il [url=https://forum.flatpress.org/]forum[/url] per asistenza e chiacchierare un po\'
[*]Scarica [b]magnifici temi[/b] dagli [url=https://wiki.flatpress.org/res:themes]invii di altri utenti[/url]!
[*]Dai un\'occhiata ai [url=https://wiki.flatpress.org/res:plugins]plugin non ufficiali[/url]
[*]Scarica [url=https://wiki.flatpress.org/res:language]il pacchetto di traduzione[/url] per la tua lingua 
[/list]


[h4]Come posso aiutare?[/h4]

[list]
[*][url=https://www.flatpress.org/contact/]Contattaci[/url] per segnalare dei bug o suggerirci dei miglioramenti.
[*]Contribuisci allo sviluppo di Flatpress su [url="https://github.com/flatpressblog/flatpress"]GitHub[/url].
[*]Traduci FlatPress o la documentazione nella [url=https://wiki.flatpress.org/res:language]tua lingua[/url].
[*]Condividi la tua conoscenza e rimani in contatto con altri utenti di FlatPress sul [url=https://forum.flatpress.org/]forum[/url].
[*]Parlane con chi vuoi! :)
[/list]


[h4]E ora che faccio?[/h4]

Ora puoi [url=login.php]connetterti[/url]per andare al [url=admin.php]Pannello di Controllo[/url] e inizire a scrivere!

Buon divertimento! :) 

[i]Il Team di [url=https://www.flatpress.org]FlatPress[/url][/i]
	
';

$lang ['samplecontent'] ['about'] ['subject'] = 'Chi sono';
$lang ['samplecontent'] ['about'] ['content'] = "Scrivi qui qualcosa su di te. ([url=admin.php?p=static&action=write&page=about]Modificami![/url])";

?>

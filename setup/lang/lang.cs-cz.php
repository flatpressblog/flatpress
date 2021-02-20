<?php
/*
 * LangId: English
 */
$lang ['locked'] = array(
	'head' => 'Setup je uzamčen',
	'descr' => 'Vypadá to, že jste již spustili instalaci, protože 
		jsme našli zamčený soubor <code>%s</code>.
		
		Pokud chcete znovu spustit setup, prosím smažte nejdříve tento soubor.
		
		<strong >Upozornění!</strong> Není bezpečné ponechávat <code>setup.php</code> a <code>setup/</code> adresář na serveru, doporučujeme je smazat!
		
		<ul>
		<li><a href="%s">Ok, zpět na můj blog</a></li>
		<li><a href="%s">Po smazání souboru znovu spustit setup</a></li>
		</ul>'
);

$lang ['step1'] = array(
	'head' => 'Vítejte ve FlatPressu!',
	'descr' => 'Děkujeme, že jste si vybrali <strong>FlatPress</strong>.
		
		Než se začnete bavit na svém zbrusu novém blogu, položíme Vám několik otázek.  
		
		Nebojte se, nezabere to moc času.',
	'descrl1' => 'Vyberte Váš jazyk.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Není v seznamu?</a>',
	'descrlang' => 'Pokud v tomto seznamu svůj jazyk nevidíte, podívejte se na <a href="https://wiki.flatpress.org/res:language">jazykový balíček</a> pro verzi:
		
		<pre>%s</pre>
		
		Chcete-li nainstalovat jazykovou sadu, nahrajte obsah balíčku do <code>flatpress/</code>, vše přepište a <a href="./setup.php">spusťte znovu instalaci.</a>.',
	'descrw' => '<strong>Jediná věc</strong> kterou FlatPress potřebuje k práci, je <em>zapisovatelný</em> adresář. 
		
		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => 'Vytvořit uživatele',
	'descr' => 'Již jste téměř hotovi, vyplňte následující podrobnosti:',
	'fpuser' => 'Uživatelské jméno',
	'fppwd' => 'Heslo',
	'fppwd2' => 'Zopakujte heslo',
	'www' => 'WWW stránka',
	'email' => 'E-Mail'
);

$lang ['step3'] = array(
	'head' => 'Hotovo',
	'descr' => '<strong>Jsme na konci</strong>. 
		
		Nevěříte?
		
		A máte pravdu: <strong>příběh právě začíná</strong>, ale <strong>psaní je jen na vás</strong>!
		
		<ul>
		<li>Podívejte se <a href="%s"> jak vypadá vaše stránka.</a></li>
		<li>Bavte se! <a href="%s">Nyní se přihlašte!</a></li>
		<li>Máte chuť nám poslat zprávu? <a href="https://www.flatpress.org/">Jděte na FlatPress.org!</a></li>
		</ul>
		
		A děkujeme že jste si vybrali FlatPress!'
);

$lang ['buttonbar'] = array(
	'next' => 'Další >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Menu';
$lang ['samplecontent'] ['menu'] ['content'] = <<<MENU
[list]
[*][url=?]Home[/url]
[*][url=?paged=1]Blog[/url]
[*][url=static.php?page=about]About[/url]
[*][url=contact.php]Contact[/url]
[/list]
MENU;

$lang ['samplecontent'] ['entry'] ['subject'] = 'Vítejte ve FlatPressu!';
$lang ['samplecontent'] ['entry'] ['content'] = <<<CONT
Tento článek vám ukáže některé z možností [url=https://www.flatpress.org]FlatPressu[/url].

Tag "more" zobrazí odkaz "Číst dál...", po kliknutí na něj se zobrazí celý článek.

[more] 


[h4]Úprava vzhledu[/h4]

K formátování textu se používají tzv. [url=http://wiki.flatpress.org/doc:plugins:bbcode]BBcode[/url] (bulletin board code). BBCode je snadný způsob, jak stylovat své příspěvky a vkládat obrázky nebo videa. Nejběžnější kódy jsou [b] pro [b]tučné písmo[/b], [i] pro [i]šikmé písmo[/i], atd.

[quote]K dispozici je tag "quote" k zobrazení vašich oblíbených citátů.[/quote]

[code]A také tag "code" k zobrazení zdrojových kódů programu.[/code]

[b]img a url[/b] tagy mají speciální parametry, jejich podrobný popis najdete na [url=https://wiki.flatpress.org/doc:plugins:bbcode]FP wiki[/url].


[h4]Příspěvky a statické stránky[/h4]

Toto je příspěvek, zatímco [url=static.php?page=about]O mně[/url] je [b]statická stránka[/b]. Statická stránka je text, který nelze komentovat a který se neobjevuje společně s běžnými příspěvky blogu.

Statické stránky jsou užitečné při vytváření stránek s obecnými informacemi. Jednu z těchto stránek můžete také nastavit jako úvodní stránku pro své návštěvníky. To znamená, že s FlatPress můžete provozovat také web, který není blogem. Možnost vytvořit ze své statické stránky úvodní stránku je v panelu možností v oblasti [url=admin.php]administrace webu[/url].


[h4]Pluginy[/h4]

FlatPress je velmi přizpůsobitelný a podporuje [url=https://wiki.flatpress.org/doc:plugins:standard]pluginy[/url] pro rozšíření jeho výkonu. BBCode je jeden z pluginů.

Vytvořili jsme další ukázkový obsah, abychom vám ukázali některé skryté funkce a vychytávky FlatPressu :) 
Můžete zde najít dvě statické stránky připravené pro váš obsah:
[list]
[*][url=static.php?page=about]O mně[/url]
[*][url=static.php?page=menu]Menu[/url] (všimněte si, že odkazy na této stránce se objeví také na vašem bočním panelu - to je kouzlo [b]blockparser widgetu[/b]. Podívejte se na [url=http://wiki.flatpress.org/doc:faq]FAQ[/url] pro podrobnosti!)
[/list]


[h4]Widgety[/h4]

V postranním panelu není jediný pevný prvek. Všechny prvky jsou zcela polohovatelné a většina z nich je také přizpůsobitelná. 
Tyto prvky se nazývají widgety. Další informace o [url=https://wiki.flatpress.org/doc:tips:widgets]widgetech[/url] a několik tipů, jak získat pěkné efekty, najdete na [url=https://wiki.flatpress.org/]wiki[/url].


[h4]Chcete vědět víc?[/h4]

[list]
[*]Jděte na [url=https://www.flatpress.org/?x]oficiální blog[/url] dozvědět se, co se děje ve světě FlatPressu
[*]Navštivte [url=https://forum.flatpress.org/]fórum[/url] kde vám poradíme a pomůžeme
[*]Stáhněte si [b]šablony vzhledu[/b] od [url=https://wiki.flatpress.org/res:themes]našich uživatelů[/url]!
[*]Podívejte se na [url=https://wiki.flatpress.org/res:plugins]neoficiální pluginy[/url]
[*]Stáhněte si [url=https://wiki.flatpress.org/res:language]překlady[/url] do dalších jazyků 
[/list]


[h4]Jak můžu pomoci?[/h4]

[list]
[*][url=https://www.flatpress.org/contact/]Kontaktujte nás[/url] a nahlašte chyby nebo navrhněte vylepšení.
[*]Přispějte k vývoji Flatpressu na [url="https://github.com/flatpressblog/flatpress"]GitHub[/url].
[*]Přeložte FlatPress nebo dokumentaci [url=https://wiki.flatpress.org/res:language]do svého jazyka[/url].
[*]Sdílejte své zkušenosti a spojte se s ostatními uživateli [url=https://forum.flatpress.org/]na fóru[/url].
[*]Šiřte jej dál! :)
[/list]


[h4]A co teď?[/h4]

Nyní se můžete [url=login.php]Přihlásit[/url] nebo jít do [url=admin.php]Administrace webu[/url] a začít publikovat!
                
Bavte se! :) 

[i]Váš [url=https://www.flatpress.org]FlatPress[/url] Team[/i]
	
CONT;

$lang ['samplecontent'] ['about'] ['subject'] = 'O mně';
$lang ['samplecontent'] ['about'] ['content'] = "Sem napište něco o sobě. ([url=admin.php?p=static&action=write&page=about]Editovat![/url])";

?>

<?php
/*
 * LangId: Czech
 */
$lang ['setup'] = array(
	'setup' => '- Nastavení aplikace'
);

$lang ['locked'] = array(
	'head' => 'Setup je uzamčen',
	'descr' => 'Vypadá to, že jste již spustili instalaci, protože jsme našli zamčený soubor <code>%s</code>.

		Pokud chcete znovu spustit setup, prosím smažte nejdříve tento soubor.
		
		<strong >Upozornění!</strong> Není bezpečné ponechávat <code>setup.php</code> a <code>setup/</code> adresář na serveru, doporučujeme je smazat!

		<ul>
		<li><a href="%s">Ok, zpět na můj blog</a></li>
		<li><a href="%s">Po smazání souboru znovu spustit setup</a></li>
		</ul>'
);

$lang ['err'] = array(
	'setuprun1' => 'Instalace probíhá.',

	'setuprun2' => 'Nastavení je spuštěno: Pokud jste správce, můžete odstranit ',
	'setuprun3' => ' restartovat.',
	'writeerror' => 'Chyba při psaní',

	'fpuser1' => ' není platný uživatel. ' . //
		'Uživatelské jméno musí být alfanumerické a nesmí obsahovat žádné mezery.',
	'fpuser2' => ' není platný uživatel. ' . //
		'Uživatelské jméno může obsahovat pouze písmena, číslice a 1 podtržítko.',
	'fppwd' => 'Heslo musí obsahovat alespoň 6 znaků a žádné mezery.',
	'fppwd2' => 'Hesla se neshodují.',
	'email' => ' není platná e-mailová adresa.',
	'www' => ' není platná adresa URL.',
	'error' => '<p><big>Chyba!</big> ' . //
		'Při zpracování formuláře došlo k následujícím chybám:</p><ul>'
);

$lang ['step1'] = array(
	'head' => 'Vítejte ve FlatPressu!',
	'descr' => 'Děkujeme, že jste si vybrali <strong>FlatPress</strong>.

		Než se začnete bavit na svém zbrusu novém blogu, položíme Vám několik otázek.

		Nebojte se, nezabere to moc času.',
	'descrl1' => 'Vyberte Váš jazyk.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Není v seznamu?</a>',
	'descrlang' => 'Pokud v tomto seznamu svůj jazyk nevidíte, podívejte se na <a href="https://wiki.flatpress.org/res:language" target="_blank" rel="external">jazykový balíček</a> pro verzi:

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
		<li>Máte chuť nám poslat zprávu? <a href="https://www.flatpress.org/" target="_blank" rel="external">Jděte na FlatPress.org!</a></li>
		</ul>
		
		A děkujeme že jste si vybrali FlatPress!'
);

$lang ['buttonbar'] = array(
	'next' => 'Další >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Menu';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]Domů[/url]
[*][url=?paged=1]Blog[/url]
[*][url=static.php?page=about]O mně[/url]
[*][url=contact.php]Kontakt[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'Vítejte ve FlatPressu!';
$lang ['samplecontent'] ['entry'] ['content'] = 'Tento článek vám ukáže některé z možností [url=https://www.flatpress.org target=_blank rel=external]FlatPressu[/url].

Tag "more" zobrazí odkaz "Číst dál...", po kliknutí na něj se zobrazí celý článek.

[more] 


[h4]Úprava vzhledu[/h4]

K formátování textu se používají tzv. [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]BBcode[/url] (bulletin board code). BBCode je snadný způsob, jak stylovat své příspěvky a vkládat obrázky nebo videa. Nejběžnější kódy jsou [b] pro [b]tučné písmo[/b], [i] pro [i]šikmé písmo[/i], atd.

[quote]K dispozici je tag "quote" k zobrazení vašich oblíbených citátů.[/quote]

[code]Element \'code\' vytvoří sekci s pevnou šířkou znaku.
Může také
   zobrazit odsazení.[/code]

[b]img a url[/b] tagy mají speciální parametry, jejich podrobný popis najdete na [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]FlatPress-Wiki[/url].


[h4]Příspěvky a statické stránky[/h4]

Toto je příspěvek, zatímco [url=static.php?page=about]O mně[/url] je [b]statická stránka[/b]. Statická stránka je text, který nelze komentovat a který se neobjevuje společně s běžnými příspěvky blogu.

Statické stránky jsou užitečné při vytváření stránek s obecnými informacemi. Jednu z těchto stránek můžete také nastavit jako úvodní stránku pro své návštěvníky. To znamená, že s FlatPress můžete provozovat také web, který není blogem. Možnost vytvořit ze své statické stránky úvodní stránku je v panelu možností v oblasti [url=admin.php]administrace webu[/url].


[h4]Pluginy[/h4]

FlatPress je velmi přizpůsobitelný a podporuje [url=https://wiki.flatpress.org/doc:plugins:standard target=_blank rel=external]pluginy[/url] pro rozšíření jeho výkonu. BBCode je jeden z pluginů.

Vytvořili jsme další ukázkový obsah, abychom vám ukázali některé skryté funkce a vychytávky FlatPressu :)
Můžete zde najít dvě statické stránky připravené pro váš obsah:
[list]
[*][url=static.php?page=about]O mně[/url]
[*][url=static.php?page=menu]Menu[/url] (všimněte si, že odkazy na této stránce se objeví také na vašem bočním panelu - to je kouzlo [b]blockparser widgetu[/b]. Podívejte se na [url=https://wiki.flatpress.org/doc:faq target=_blank rel=external]FAQ[/url] pro podrobnosti!)
[/list]

Pomocí pluginu [b]PhotoSwipe[/b] můžete nyní své obrázky umístit ještě snadněji, a to buď jako float="left"- nebo float="right" zarovnané jednotlivé obrázky obklopené textem.
Pomocí prvku \'gallery\' můžete dokonce návštěvníkům prezentovat celé galerie. Jak je to snadné, se můžete přesvědčit [url=https://wiki.flatpress.org/res:plugins:photoswipe target=_blank rel=external]zde[/url].


[h4]Widgety[/h4]

V postranním panelu není jediný pevný prvek. Všechny prvky jsou zcela polohovatelné a většina z nich je také přizpůsobitelná.
Tyto prvky se nazývají widgety. Další informace o [url=https://wiki.flatpress.org/doc:tips:widgets target=_blank rel=external]widgetech[/url] a několik tipů, jak získat pěkné efekty, najdete na [url=https://wiki.flatpress.org/ target=_blank rel=external]wiki[/url].


[h4]Témata[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
S tématem FlatPress-Leggero máte k dispozici 3 šablony stylů - od klasických po moderní. Tyto šablony jsou skvělým začátkem pro vytvoření něčeho vlastního.


[h4]Chcete vědět víc?[/h4]

Chcete se o FlatPressu dozvědět více?

[list]
[*]Jděte na [url=https://www.flatpress.org/?x target=_blank rel=external]oficiální blog[/url] dozvědět se, co se děje ve světě FlatPressu
[*]Navštivte [url=https://forum.flatpress.org/ target=_blank rel=external]fórum[/url] kde vám poradíme a pomůžeme
[*]Stáhněte si [b]šablony vzhledu[/b] od [url=https://wiki.flatpress.org/res:themes target=_blank rel=external]našich uživatelů[/url]!
[*]Podívejte se na [url=https://wiki.flatpress.org/res:plugins target=_blank rel=external]neoficiální pluginy[/url]
[*]Stáhněte si [url=https://wiki.flatpress.org/res:language target=_blank rel=external]překlady[/url] do dalších jazyků
[*]FlatPress můžete sledovat také na [url=https://fosstodon.org/@flatpress target=_blank rel=external]Mastodon[/url].
[/list]


[h4]Jak můžu pomoci?[/h4]

[list]
[*]Podpořte projekt [url=https://www.flatpress.org/home/static.php?page=donate target=_blank rel=external]malým příspěvkem[/url].
[*][url=https://www.flatpress.org/contact/ target=_blank rel=external]Kontaktujte nás[/url] a nahlašte chyby nebo navrhněte vylepšení.
[*]Přispějte k vývoji Flatpressu na [url=https://github.com/flatpressblog/flatpress target=_blank rel=external]GitHub[/url].
[*]Přeložte FlatPress nebo dokumentaci [url=https://wiki.flatpress.org/res:language target=_blank rel=external]do svého jazyka[/url].
[*]Sdílejte své zkušenosti a spojte se s ostatními uživateli [url=https://forum.flatpress.org/ target=_blank rel=external]na fóru[/url].
[*]Šiřte jej dál! :)
[/list]


[h4]A co teď?[/h4]

Nyní se můžete [url=login.php]Přihlásit[/url] nebo jít do [url=admin.php]Administrace webu[/url] a začít publikovat!

Bavte se! :)

[i]Váš [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url] Team[/i]

';

$lang ['samplecontent'] ['about'] ['subject'] = 'O mně';
$lang ['samplecontent'] ['about'] ['content'] = 'Sem napište něco o sobě. ([url=admin.php?p=static&action=write&page=about]Editovat![/url])';

$lang ['samplecontent'] ['privacy-policy'] ['subject'] = 'Zásady ochrany osobních údajů';
$lang ['samplecontent'] ['privacy-policy'] ['content'] = 'V některých zemích, například pokud používáte službu Akismet Antispam, je nutné poskytnout návštěvníkům zásady ochrany osobních údajů. Zásady ochrany osobních údajů mohou být nezbytné také v případě, že návštěvník může použít kontaktní formulář nebo funkci komentáře.

[b]Tip:[/b] Na internetu existuje spousta šablon a generátorů.

Můžete je vložit zde. ([url=admin.php?p=static&action=write&page=privacy-policy]Upravte mě![/url])

Pokud aktivujete doplněk CookieBanner, budou moci vaši návštěvníci přejít přímo na tuto stránku v kontaktním formuláři a ve funkci komentáře.
';
?>

<?php
/*
 * LangId: Português (BR)
 */
$lang ['setup'] = array(
	'setup' => '- Configuração'
);

$lang ['locked'] = array(
	'head' => 'A instalação foi bloqueada.',
	'descr' => 'Parece que você já executou a instalação, porque encontramos o arquivo de bloqueio. <code>%s</code>.

		Se você precisar reiniciar a instalação, exclua este arquivo primeiro.

		<strong >Lembre-se!</strong> Não é seguro manter o arquivo <code>setup.php</code> e a pasta <code>setup/</code> no seu servidor, sugerimos que você os exclua!

		<ul>
		<li><a href="%s">Ok, leve-me de volta ao meu blog.</a></li>
		<li><a href="%s">Excluí o arquivo, reinicie a instalação.</a></li>
		</ul>'
);

$lang ['err'] = array(
	'setuprun1' => 'A instalação está sendo executada.',
	
	'setuprun2' => 'A instalação já está em execução: se você for o administrador, poderá excluir ',
	'setuprun3' => ' para reiniciar.',
	'writeerror' => 'Erro de escrita',

	'fpuser1' => ' não é um usuário válido. ' . //
		'O nome de usuário deve ser alfanumérico e não deve conter espaços.',
	'fpuser2' => ' não é um usuário válido. ' . //
		'O nome de usuário só pode conter letras, números e um sublinhado.',
	'fppwd' => 'A senha deve conter pelo menos 6 caracteres e nenhum espaço.',
	'fppwd2' => 'As senhas não correspondem.',
	'email' => ' não é um endereço de e-mail válido.',
	'www' => ' não é um URL válido.',
	'error' => '<p><big>Erro!</big> ' . //
		'Os seguintes erros ocorreram durante o processamento do formulário:</p><ul>'
);

$lang ['step1'] = array(
	'head' => 'Bem-vindo ao FlatPress!',
	'descr' => 'Obrigado por escolher <strong>o FlatPress</strong>.

		Antes de começar a se divertir com seu novo blog, precisamos fazer algumas perguntas. 

		Não se preocupe, não vai demorar muito!',
	'descrl1' => 'Selecione seu idioma.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">Não está na lista?</a>',
	'descrlang' => 'Se você não vê seu idioma nesta lista, convém verificar se há <a href="https://wiki.flatpress.org/res:language" target="_blank" rel="external">um pacote de idiomas</a> para esta versão:

		<pre>%s</pre>

		Para instalar o pacote de idiomas, faça o upload do conteúdo do pacote no seu <code>flatpress/</code>, e substitua tudo, depois <a href="./setup.php">reinicie esse setup</a>.',
	'descrw' => 'A <strong>única coisa</strong> que você precisa para o FlatPress funcionar é uma pasta <em>gravável</em>.

		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => 'Crie usário',
	'descr' => 'Você já está quase pronto, preencha os seguintes detalhes:',
	'fpuser' => 'Nome de usário',
	'fppwd' => 'Senha',
	'fppwd2' => 'Digite a senha de novo.',
	'www' => 'Website',
	'email' => 'E-Mail'
);

$lang ['step3'] = array(
	'head' => 'Pronto!',
	'descr' => '<strong>Fim da história!</strong>.

		Inacreditável? 

		E tá certo: <strong>A história apenas começou</strong>, mas <strong>a escrita depende de você!</strong>

		<ul>
		<li>Veja <a href="%s">como a página inicial se parece.</a></li>
		<li>Divirta-se! <a href="%s">Entre agora!</a></li>
		<li>Você sente vontade de falar conosco? <a href="https://www.flatpress.org/" target="_blank" rel="external">Vá para FlatPress.org!</a></li>
		</ul>

		E obrigado por escolher o FlatPress!'
);

$lang ['buttonbar'] = array(
	'next' => 'Próximo >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Menu';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]Início[/url]
[*][url=?paged=1]Blog[/url]
[*][url=static.php?page=about]Sobre[/url]
[*][url=contact.php]Contato[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'Bem vindo ao FlatPress!';
$lang ['samplecontent'] ['entry'] ['content'] = 'Esta é uma entrada de amostra, postada para mostrar alguns dos recursos do [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url].

A tag \'more\' permite criar um "salto" entre um trecho e o artigo completo.

[more]


[h4]Estilo[/h4]

A maneira padrão de estilizar e formatar seu conteúdo é [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]BBcode[/url] (bulletin board code). O BBCode é uma maneira fácil de estilizar suas postagens. Os códigos mais comuns são permitidos. Como [b] para [b]negrito[/b] (html: strong), [i] fpara [i]itálico[/i] (html: em), etc.

[quote]Também existem[b]quote[/b] blocos para exibir suas cotações favoritas. [/quote]

[code] E \'code\' exibe seus trechos de maneira monoespaçada. Também suporta conteúdo recuado. [/code]

img e url tag têm opções especiais. Pode descobrir mais no [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]FlatPress-Wiki[/url].


[h4]Posts e páginas estáticas[/h4]

Isso e um post, enquanto [url=static.php?page=about]Sobre[/url] é uma [b]página estática[/b]. Uma página estática é uma página que não pode ser comentada e que não aparece junto com os posts normais do blog.

Páginas estáticas são úteis para criar páginas de informações gerais. Você também pode tornar uma dessas páginas a [b]página inicial[/b] para seus visitantes. Isso significa que, com o FlatPress, você também pode executar um site completo que não seja de blog. A opção de tornar uma página estática sua página inicial está no [b]painel de opções[/b] da [url=admin.php]área de administração[/ url].


[h4]Plugins[/h4]

O FlatPress é muito personalizável e suporta [url=https://wiki.flatpress.org/doc:plugins:standard target=_blank rel=external]plugins[/url] para ampliar seu poder. BBCode é um plugin em si.

Criamos mais conteúdo de amostra, para mostrar algumas das funções e gemas bem escondidas do FP. :)
Pode encontrar duas [b]páginas estáticas[/b] prontas para aceitar seu conteúdo:
[list]
[*][url=static.php?page=about]Sobre[/url]
[*][url=static.php?page=menu]Menu[/url] (observe que os links nesta página também aparecerão na sua barra lateral - esta é a mágica do widget [b]blockparser[/b]. Consulte [url=https://wiki.flatpress.org/doc:faq target=_blank rel=external]o FAQ[/url] para isso e muito mais!)
[/list]

Com o plugin [b]PhotoSwipe[/b] você pode agora colocar suas imagens ainda mais facilmente, seja como float="left"- ou float="right" alinhado uma única imagem, rodeado pelo texto.
Você pode até mesmo usar o elemento "galeria" para apresentar galerias inteiras a seus visitantes. Você pode descobrir como é fácil [url=https://wiki.flatpress.org/doc:plugins:photoswipe target=_blank rel=external]aqui[/url].


[h4]Widgets[/h4]

Não há um único elemento fixo nos sidebar(s). Todos os elementos que você pode encontrar nos sidebar nesta página são completamente posicionáveis e a maioria deles também é personalizável. Alguns temas ainda fornecem uma interface de painel na área de administração.

Esses elementos são chamados de [b]widgets[/b]. Para mais informações sobre widgets e [url=https://wiki.flatpress.org/doc:tips:widgets target=_blank rel=external]algumas dicas[/url] para obter bons efeitos, dê uma olhada no [url=https://wiki.flatpress.org/ target=_blank rel=external]FlatPress-Wiki[/url].

[h4]Temas[/h4]

[galeria="images/Leggero-Themepreview/" width="140"]
Com o tema FlatPress-Leggero você tem à sua disposição 4 modelos de estilo - do clássico ao moderno. Estes modelos são um começo maravilhoso para criar algo próprio.


[h4]See more[/h4]

Want to see more?

[list]
[*]Siga [url=https://www.flatpress.org/?x target=_blank rel=external]o blog oficial[/url] para saber o que está acontecendo no mundo do FlatPress.
[*]Visite [url=https://forum.flatpress.org/ target=_blank rel=external]o forum[/url] para obter suporte e bate-papo.
[*]Obtenha [b]ótimos temas[/b] em [url=https://wiki.flatpress.org/res:themes target=_blank rel=external] enviadas de outros usuários[/url]!
[*]Confira [url=https://wiki.flatpress.org/res:plugins target=_blank rel=external]os plugins não oficiais.[/url]!
[*]Obtenha [url=https://wiki.flatpress.org/res:language target=_blank rel=external]o pacote de tradução[/url] para o seu idioma.
[*]Você também pode seguir o FlatPress em [url=https://fosstodon.org/@flatpress target=_blank rel=external]Mastodon[/url].
[/list]


[h4]Como possa ajudar?[/h4]

[list]
[*]Apoiar o projeto com uma pequena [url=https://www.flatpress.org/home/static.php?page=donate target=_blank rel=external]doação[/url].
[*][url=https://www.flatpress.org/contact/ target=_blank rel=external]Entre em contato conosco[/url] para relatar erros ou sugerir melhorias.
[*]Contribua para o desenvolvimento do FlatPress no [url=https://github.com/flatpressblog/flatpress target=_blank rel=external]GitHub[/url].
[*]Traduza o FlatPress ou a documentação para [url=https://wiki.flatpress.org/res:language target=_blank rel=external]o seu idioma[/url].
[*]Compartilhe seu conhecimento e conecte-se com outros usuários do FlatPress no [url=https://forum.flatpress.org/ target=_blank rel=external]forum[/url].
[*]Espalhe a palavra! :-)
[/list]


[h4]E agora?[/h4]

Agora pode [url=login.php]entrar[/url] para acessar [url=admin.php]o painel de controle[/url] e comece a postar!!

Diverta-se! :-)

[i]A Tripulação de [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url][/i]

';

$lang ['samplecontent'] ['about'] ['subject'] = 'Sobre';
$lang ['samplecontent'] ['about'] ['content'] = 'Escreva algo sobre você aqui. ([url=admin.php?p=static&action=write&page=about]Me edite![/url])';

$lang ['samplecontent'] ['privacy-policy'] ['subject'] = 'Política de privacidade';
$lang ['samplecontent'] ['privacy-policy'] ['content'] = 'Em alguns países, se você usar o serviço Akismet Antispam, por exemplo, é necessário fornecer aos visitantes uma política de privacidade. Uma política de privacidade também pode ser necessária se o visitante puder usar o formulário de contato ou a função de comentário.

[b]Dica:[/b] Há muitos modelos e geradores na Internet.

Você pode inseri-los aqui. ([url=admin.php?p=static&action=write&page=privacy-policy]Edite-me![/url])

Se você ativar o plug-in CookieBanner, seus visitantes poderão ir diretamente para essa página no formulário de contato e na função de comentários.
';
?>

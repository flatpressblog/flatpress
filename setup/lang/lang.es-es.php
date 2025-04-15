<?php
/*
 * LangId: Spanish
 */
$lang ['setup'] = array(
	'setup' => 'Configuración'
);

$lang ['locked'] = array(
	'head' => 'La instalación está bloqueada',
	'descr' => 'Parece que la instalación ya está en marcha: El fichero de bloqueo <code>%s</code> ya existe.

		Si desea reiniciar la instalación, borre primero este fichero.

		<strong >Atención!</strong> El fichero <code>setup.php</code> y el directorio <code>setup/</code> no deben permanecer en el servidor. Por favor, elimínelos después de completar la instalación.

		<ul>
		<li><a href="%s">Ok, llévame a mi blog</a></li>
		<li><a href="%s">He borrado el archivo. Reinicie la instalación.</a></li>
		</ul>'
);

$lang ['err'] = array(
	'setuprun1' => 'La instalación está en marcha.',

	'setuprun2' => 'La instalación ya está en marcha: Si eres el administrador, puedes borrar ',
	'setuprun3' => ' para reiniciar.',
	'writeerror' => 'Error de escritura',

	'fpuser1' => ' no es un usuario válido. ' . //
		'El nombre de usuario debe ser alfanumérico y no debe contener espacios.',
	'fpuser2' => ' no es un usuario válido. ' . //
		'El nombre de usuario sólo puede contener letras, números y un guión bajo.',
	'fppwd' => 'La contraseña debe contener al menos 6 caracteres y ningún espacio.',
	'fppwd2' => 'Las contraseñas no coinciden.',
	'email' => ' no es una dirección de correo electrónico válida.',
	'www' => ' no es una URL válida.',
	'error' => '<p><big>Error!</big> ' . // 
		'Se han producido los siguientes errores al procesar el formulario:</p><ul>'
);

$lang ['step1'] = array(
	'head' => 'Bienvenido a FlatPress!',
	'descr' => 'Gracias por haber elegido el <strong>FlatPress</strong>.

		Antes de empezar con su flamante blog, debe concretar algunas pequeñas cosas.

		Pero no se preocupe, no tardará mucho.',
	'descrl1' => 'Elige tu idioma.',
	'descrl2' => '<a class="hint" onclick="toggleinfo();">No está en la lista?</a>',
	'descrlang' => 'Si no encuentras tu idioma en la lista, comprueba si existe <a href="https://wiki.flatpress.org/res:language" target="_blank" rel="external">un paquete de idiomas adecuado</a> :

		<pre>%s</pre>

		Para instalar un paquete de idioma, simplemente carga su contenido en tu directorio <code>flatpress/</code>. A continuación, <a href="./setup.php">inicie de nuevo la instalación</a>.',
	'descrw' => '<strong>Lo único</strong> que necesita para ejecutar FlatPress es un directorio <em>escribible</em>.

		<pre>%s</pre>'
);

$lang ['step2'] = array(
	'head' => 'Crear usuario',
	'descr' => 'Casi listo! Sólo quedan los siguientes detalles:',
	'fpuser' => 'Nombre de usuario',
	'fppwd' => 'Contraseña',
	'fppwd2' => 'Contraseña (Repetición)',
	'www' => 'Página de inicio',
	'email' => 'Correo electrónico'
);

$lang ['step3'] = array(
	'head' => 'Fertig',
	'descr' => '<strong>Eso fue todo.</strong>

		No es creíble?

		No, en <strong>realidad acaba de empezar</strong>! Pero bloguear es <em>tu</em> trabajo ahora ;)

		<ul>
		<li>A la <a href="%s">página principal de su blog</a></li>
		<li>Diviértete blogueando! <a href="%s">Conectarse ahora</a></li>
		<li>Quiere alabar o criticar? Visítenos en el <a href="https://www.flatpress.org/" target="_blank" rel="external">FlatPress.org</a>!</li>
		</ul>

		Gracias por elegir FlatPress!'
);

$lang ['buttonbar'] = array(
	'next' => 'siguiente paso >'
);

$lang ['samplecontent'] = array();

$lang ['samplecontent'] ['menu'] ['subject'] = 'Menú';
$lang ['samplecontent'] ['menu'] ['content'] = '[list]
[*][url=?]Página de inicio[/url]
[*][url=?paged=1]Blog[/url]
[*][url=static.php?page=about]Acerca de[/url]
[*][url=contact.php]Contacto[/url]
[/list]';

$lang ['samplecontent'] ['entry'] ['subject'] = 'Bienvenido a FlatPress!';
$lang ['samplecontent'] ['entry'] ['content'] = 'Esta es una entrada de ejemplo. Esto muestra algunas de las funciones del [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url].

El elemento "more" le permite saltar del esquema del artículo al artículo completo.

[more]


[h4]Formato de texto[/h4]

En FlatPress usted formatea su contenido con [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]BBcode[/url] (Bulletin-Board-Code). Esto es muy fácil con BBCode. Ejemplos? [b] hace [b]texto en negrita[/b], [i] [i]cursiva[/i].

[quote]El elemento [b]quote[/b] puede utilizarse para marcar citas. [/quote]

[code]El elemento \'code\' crea una sección con un ancho de carácter fijo.
También puede
   representar hendiduras.[/code]

Los elementos \'img\' (imágenes) y \'url\' (Links) tienen opciones especiales. Encontrará más información al respecto en la [url=https://wiki.flatpress.org/doc:plugins:bbcode target=_blank rel=external]FlatPress-Wiki[/url].


[h4]Entradas (artículos de blog) y páginas estáticas[/h4]

Se trata de una entrada, mientras que [url=static.php?page=about]Acerca de[/url] es una [b]página estática[/b]. Una página estática, a diferencia de una entrada, no puede comentarse y no aparece en los listados de entradas del blog.

Las páginas estáticas son útiles para información general, por ejemplo una página de inicio fija o el pie de imprenta. Incluso podría prescindir por completo de las funciones de blog y utilizar FlatPress para crear un sitio web con sólo páginas estáticas.

En el [url=admin.php]área de administración[/url] puedes crear entradas y páginas estáticas - y definir si la página de inicio de tu blog FlatPress debe ser una página estática o la vista general del blog.


[h4]Plugins[/h4]

Puede personalizar ampliamente FlatPress según sus necesidades ampliándolo con [url=https://wiki.flatpress.org/doc:plugins:standard target=_blank rel=external]Plugins[/url]. BBCode, por ejemplo, es un Plugin.

Aquí tiene más ejemplos de contenido que le muestran aún más funciones de FlatPress :)

Dos páginas estáticas ya están preparadas para usted:
[list]
[*][url=static.php?page=about]Acerca de[/url]
[*][url=static.php?page=menu]Menú[/url] (El contenido de esta página estática también aparece en la barra lateral de tu blog: esa es la magia del [b]widget blockparser[/b]. El [url=https://wiki.flatpress.org/ target=_blank rel=external]FlatPress-Wiki[/url] tiene información sobre esto, ¡y mucho más!)
[/list]

Con el plugin [b]PhotoSwipe[/b] ahora puedes colocar tus imágenes aún más fácilmente, ya sea como una sola imagen alineada  float="left"-  o  float="right" rodeada por el texto.
Incluso puede presentar galerías enteras a sus visitantes con el elemento \'gallery\'. Puede comprobar lo fácil que es [url=https://wiki.flatpress.org/res:plugins:photoswipe target=_blank rel=external]aquí[/url].


[h4]Widgets[/h4]

Ninguno de los elementos de la barra lateral de tu blog es fijo, puedes moverlos, eliminarlos y añadir otros nuevos en el área de administración.

Estos elementos se denominan [b]Widgets[/b]. Por supuesto, la Wiki de FlatPress también tiene mucha información útil [url=https://wiki.flatpress.org/doc:tips:widgets target=_blank rel=external]sobre este tema[/url].


[h4]Temas[/h4]

[gallery="images/Leggero-Themepreview/" width="140"]
Con el tema Leggero de FlatPress tiene a su disposición 3 plantillas de estilo, desde clásico hasta moderno. Estas plantillas son un magnífico comienzo para crear algo propio.


[h4]Más información[/h4]

Desea obtener más información sobre FlatPress?

[list]
[*]En el [url=https://www.flatpress.org/?x target=_blank rel=external]blog del proyecto[/url] podrá enterarse de lo que ocurre actualmente en el proyecto FlatPress.
[*]Visite el [url=https://forum.flatpress.org/ target=_blank rel=external]foro de soporte[/url] para obtener soporte y contactar con otros usuarios de FlatPress.
[*]Descargue magníficos [b]temas[/b] creados por la comunidad desde la [url=https://wiki.flatpress.org/res:themes target=_blank rel=external]Wiki[/url].
[*]También hay grandes [url=https://wiki.flatpress.org/res:plugins target=_blank rel=external]Plugins[/url] allí.
[*]Consigue [url=https://wiki.flatpress.org/res:language target=_blank rel=external]paquete de traducción[/url] para tu idioma.
[*]También puede seguir a FlatPress en [url=https://fosstodon.org/@flatpress target=_blank rel=external]Mastodon[/url].
[/list]


[h4]Cómo puedo apoyar a FlatPress?[/h4]

[list]
[*]Apoye el proyecto con una [url=https://www.flatpress.org/home/static.php?page=donate target=_blank rel=external]pequeña donación[/url].
[*][url=https://www.flatpress.org/contact/ target=_blank rel=external]Informe[/url] de los errores que se hayan producido o envíenos sugerencias de mejora.
[*]Invitamos a los programadores a que nos apoyen en [url=https://github.com/flatpressblog/flatpress target=_blank rel=external]GitHub[/url].
[*]Traduzca FlatPress y su documentación a [url=https://wiki.flatpress.org/res:language target=_blank rel=external]su idioma[/url].
[*]Forme parte de la comunidad FlatPress en el [url=https://forum.flatpress.org/ target=_blank rel=external]Foro de soporte[/url].
[*]Cuéntale al mundo lo genial que es FlatPress! :)
[/list]


[h4]Y ahora qué?[/h4]

[url=login.php]Inicie sesión[/url] para empezar a bloguear en el [url=admin.php]área de administración[/url].

Que te diviertas! :)

[i]El equipo [url=https://www.flatpress.org target=_blank rel=external]FlatPress[/url][/i]

';

$lang ['samplecontent'] ['about'] ['subject'] = 'Acerca de';
$lang ['samplecontent'] ['about'] ['content'] = 'Escribe aquí algo sobre ti y sobre este blog. ([url=admin.php?p=static&action=write&page=about]Edítame![/url])';

$lang ['samplecontent'] ['privacy-policy'] ['subject'] = 'Política de privacidad';
$lang ['samplecontent'] ['privacy-policy'] ['content'] = 'En algunos países, si utiliza el servicio Akismet Antispam, por ejemplo, es necesario proporcionar a sus visitantes una política de privacidad. También puede ser necesaria una política de privacidad si el visitante puede utilizar el formulario de contacto o la función de comentarios.

[b]Consejo:[/b] Hay muchas plantillas y generadores en Internet.

Puede insertarlos aquí. ([url=admin.php?p=static&action=write&page=privacy-policy]Editar![/url])

Si activas el plugin CookieBanner, tus visitantes podrán ir directamente a esta página en el formulario de contacto y en la función de comentarios.
';
?>

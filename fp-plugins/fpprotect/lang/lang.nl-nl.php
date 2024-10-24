<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'FlatPress Instellingen Beschermen',
	'desc1' => 'Hier kun je de beveiligingsopties voor je FlatPress-blog wijzigen.',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => 'Sta onveilige Java-scripts toe (Niet aanbevolen)',

	'allowUnsafeInlineDsc' => '<p>Staat het laden van onveilige inline JavaScript-code toe.</p>' . //
		'<p><br>Opmerking voor plugin-ontwikkelaars: Voeg een nonce toe aan uw Java-script.</p>' . //
		'Een voorbeeld voor PHP:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Een voorbeeld voor het Smarty-sjabloon:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>Dit zorgt ervoor dat de browser van de bezoeker alleen Java scripts uitvoert die afkomstig zijn van je FlatPress blog.</p>',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Laat het aanmaken en bewerken van het .htaccess bestand toe.',
	'allowPrettyURLEditDsc' => 'Geeft toegang tot het .htaccess bewerkingsveld van de PrettyURLs plugin om het .htaccess bestand aan te maken of te wijzigen.',

	'submit' => 'Instellingen opslaan',
		'msgs' => array(
		1 => 'Instellingen succesvol opgeslagen.',
		-1 => 'Fout bij het opslaan van de instellingen.'
	),

	// Warning message for unsafe inline scripts
	'warning_allowUnsafeInline' => 'Waarschuwing: Content-Security-Policy -> Dit beleid bevat "unsafe-inline", wat gevaarlijk is in het script-src-policy.'
);
?>

<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'FlatPress Protect-indstillinger',
	'desc1' => 'Her kan du ændre sikkerhedsrelaterede indstillinger for din FlatPress-blog.',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => 'Tillad usikre Java-scripts (anbefales ikke)',

	'allowUnsafeInlineDsc' => '<p>Tillader indlæsning af usikker inline JavaScript-kode.</p>' . //
		'<p><br>Bemærkning til plugin-udviklere: Tilføj venligst en nonce til dit Java-script.</p>' . //
		'Et eksempel til PHP:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Et eksempel for Smarty-skabelonen:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>Dette sikrer, at den besøgendes browser kun udfører Java-scripts, der stammer fra din FlatPress-blog.</p>',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Tillad oprettelse og redigering af .htaccess-filen.',
	'allowPrettyURLEditDsc' => 'Giver adgang til .htaccess-redigeringsfeltet i PrettyURLs-plugin\'et for at oprette eller ændre .htaccess-filen.',

	// Part for metadate in images after upload
	'allow_image_metadate' => 'Bevar metadata og original billedkvalitet i uploadede billeder.',
	'allowImageMetadataDsc' => 'Når billeder er blevet uploadet med uploaderen, bevares metadataene. Dette omfatter f.eks. kameraoplysninger og geokoordinater.',

	'submit' => 'Gem indstillinger',
		'msgs' => array(
		1 => 'Indstillingerne er gemt med succes.',
		-1 => 'Fejl ved lagring af indstillingerne.'
	),

	// Warning message for unsafe inline scripts
	'warning_allowUnsafeInline' => 'Advarsel: Content-Security-Policy -> Denne politik indeholder "unsafe-inline", som er farlig i script-src-politikken.'
);
?>

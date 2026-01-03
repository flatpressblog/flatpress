<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'FlatPress Protect-indstillinger',
	'desc1' => 'Her kan du ændre sikkerhedsrelaterede indstillinger for din FlatPress-blog. ' . //
		'Den bedste beskyttelse for dine besøgende og din FlatPress-blog er, når alle indstillinger er deaktiveret.',

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

	// Part for external iFrame embedding
	'allow_external_iframe' => 'Tillad indlejring af eksternt indhold via iFrame (anbefales ikke).',
	'allowExternalIframeDsc' => 'Tillader indlejring af eksternt indhold via <code>&lt;iframe&gt;</code>-koden (f.eks. videoer, kort, widgets). ' . //
		'Indlejret tredjepartsindhold kan spore besøgende og kan være usikkert. Aktivér dette kun, hvis du virkelig har brug for det.',

	// Part for SVG uploads via admin uploader
	'allow_svg_upload' => 'Tillad upload af SVG-filer via uploader (kun betroede brugere).',
	'allowSvgUploadDsc' => 'Tillader upload af SVG-filer via admin-uploader. SVG kan indeholde aktivt indhold (f.eks. scripts); aktiver dette kun, hvis du stoler på uploaderne og ikke indlejrer upålidelige SVG\'er.',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Tillad oprettelse og redigering af .htaccess-filen.',
	'allowPrettyURLEditDsc' => 'Giver adgang til .htaccess-redigeringsfeltet i PrettyURLs-plugin\'et for at oprette eller ændre .htaccess-filen.',

	// Part for metadate in images after upload
	'allow_image_metadate' => 'Bevar metadata og original billedkvalitet i uploadede billeder.',
	'allowImageMetadataDsc' => 'Når billeder er blevet uploadet med uploaderen, bevares metadataene. Dette omfatter f.eks. kameraoplysninger og geokoordinater.',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => 'Tillad FlatPress at bruge den besøgendes ikke-anonymiserede IP-adresse.',
	'allowVisitorIpDsc' => 'FlatPress vil derefter gemme den ikke-anonymiserede IP-adresse i blandt andet kommentarer. ' . //
		'Hvis du bruger Akismet Antispam-tjenesten, vil Akismet også modtage den ikke-anonymiserede IP-adresse.',

	// Part for Idle timeout for admin session
	'session_timeout_label' => 'Idle-timeout for admin-session (minutter)',
	'session_timeout_desc' => 'Minutter med inaktivitet, indtil admin-sessionen udløber. Tomt eller 0 betyder standard 60 minutter.',

	'submit' => 'Gem indstillinger',
		'msgs' => array(
		1 => 'Indstillingerne er gemt med succes.',
		-1 => 'Fejl ved lagring af indstillingerne.'
	),

	// Warning message for unsafe inline scripts
	'warning_allowUnsafeInline' => 'Advarsel: Content-Security-Policy -> Denne politik indeholder "unsafe-inline", som er farlig i script-src-politikken.',
	'warning_allowExternalIframe' => 'Advarsel: Content-Security-Policy -> Ekstern iFrame-indlejring er aktiveret. Indlejret tredjepartsindhold kan spore besøgende og kan være usikkert.',
	'warning_allowSvgUpload' => 'Advarsel: SVG-filer kan indeholde aktivt indhold. Upload kun pålidelige SVG\'er, og indlejr dem ikke uden gennemgang!',
	'warning_allowVisitorIp' => 'Advarsel: Brug af ikke-anonymiserede besøgendes IP-adresser -> Glem ikke at informere <a href="static.php?page=privacy-policy" title="rediger statisk side">besøgende på din FlatPress-blog</a> om dette!'
);
?>

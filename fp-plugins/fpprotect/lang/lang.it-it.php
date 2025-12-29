<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Impostazioni di FlatPress Protect',
	'desc1' => 'Qui è possibile modificare le opzioni relative alla sicurezza del blog FlatPress. ' . //
		'La migliore protezione per i vostri visitatori e per il vostro blog FlatPress è quando tutte le opzioni sono disattivate.',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => 'Consenti script Java non sicuri (non consigliato)',

	'allowUnsafeInlineDsc' => '<p>Consente il caricamento di codice JavaScript in linea non sicuro.</p>' . //
		'<p><br>Nota per gli sviluppatori di plugin: aggiungete un nonce al vostro script Java.</p>' . //
		'Un esempio per PHP:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Un esempio per il template Smarty:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>Questo assicura che il browser del visitatore esegua solo gli script Java provenienti dal vostro blog FlatPress.</p>',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Consente la creazione e la modifica del file .htaccess.',
	'allowPrettyURLEditDsc' => 'Consente l\'accesso al campo di modifica .htaccess del plugin PrettyURLs per creare o modificare il file .htaccess.',

	// Part for metadate in images after upload
	'allow_image_metadate' => 'Conserva i metadati e la qualità originale delle immagini caricate.',
	'allowImageMetadataDsc' => 'Dopo che le immagini sono state caricate con il caricatore, i metadati vengono conservati. Questi includono, ad esempio, le informazioni sulla fotocamera e le coordinate geografiche.',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => 'Consentire a FlatPress di utilizzare l\'indirizzo IP non anonimo del visitatore.',
	'allowVisitorIpDsc' => 'FlatPress salverà quindi l\'indirizzo IP non anonimo nei commenti, tra le altre cose. ' . //
		'Se utilizzate il servizio antispam Akismet, anche Akismet riceverà l\'indirizzo IP non anonimo.',

	// Part for Idle timeout for admin session
	'session_timeout_label' => 'Timeout di inattività per sessione amministratore (minuti)',
	'session_timeout_desc' => 'Minuti di inattività prima della scadenza della sessione amministratore. Se il campo è vuoto o contiene 0, il valore predefinito è 60 minuti.',

	'submit' => 'Salva le impostazioni',
		'msgs' => array(
		1 => 'Impostazioni salvate con successo.',
		-1 => 'Errore nel salvataggio delle impostazioni.'
	),

	// Warning message
	'warning_allowUnsafeInline' => 'Attenzione: Content-Security-Policy -> Questa policy contiene "unsafe-inline", che è pericoloso nella script-src-policy.',
	'warning_allowVisitorIp' => 'Attenzione: Utilizzo di indirizzi IP dei visitatori non anonimizzati -> Non dimenticate di informare <a href="static.php?page=privacy-policy" title="modifica pagina statica">i visitatori del vostro blog FlatPress</a>!'
);
?>

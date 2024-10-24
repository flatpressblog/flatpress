<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Impostazioni di FlatPress Protect',
	'desc1' => 'Qui è possibile modificare le opzioni relative alla sicurezza del blog FlatPress.',

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

	'submit' => 'Salva le impostazioni',
		'msgs' => array(
		1 => 'Impostazioni salvate con successo.',
		-1 => 'Errore nel salvataggio delle impostazioni.'
	),

	// Warning message for unsafe inline scripts
	'warning_allowUnsafeInline' => 'Attenzione: Content-Security-Policy -> Questa policy contiene "unsafe-inline", che è pericoloso nella script-src-policy.'
);
?>

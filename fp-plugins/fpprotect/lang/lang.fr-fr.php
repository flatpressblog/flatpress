<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Paramètres de FlatPress Protect',
	'desc1' => 'Ici, tu peux modifier les options relatives à la sécurité de ton blog FlatPress.',

	// Part for unsafe inline scripts
	'allow_unsafe_inline' => 'Autoriser les scripts Java non sécurisés (Non recommandé)',

	'allowUnsafeInlineDsc' => '<p>Autorise le chargement de code JavaScript en ligne non sécurisé.</p>' . //
		'<p><br>Remarque aux développeurs de plugins : merci d\'équiper ton script Java d\'un nonce.</p>' . //
		'Un exemple pour PHP:
		<pre>$random_hex = RANDOM_HEX;
' . //
		'... script nonce="\' . $random_hex . \'" src=" ...' . //
		'</pre>' . //
		'Un exemple pour le modèle Smarty:
		<pre>' . //
		'... script nonce="{$smarty.const.RANDOM_HEX}" ...' . //
		'</pre>' . //
		'<p>Cela permet de garantir que le navigateur du visiteur n\'exécute que des scripts Java qui proviennent de ton blog FlatPress.</p>',

	// Part for the PrettyURLs .htaccess edit-field
	'allow_htaccess_edit' => 'Permet de créer et d\'éditer le fichier .htaccess.',
	'allowPrettyURLEditDsc' => 'Permet d\'accéder au champ d\'édition .htaccess du plugin PrettyURLs pour créer ou modifier le fichier .htaccess.',

	'submit' => 'Enregistrer les paramètres',
		'msgs' => array(
		1 => 'Paramètres enregistrés avec succès.',
		-1 => 'Erreur lors de l\'enregistrement des paramètres.'
	),

	// Warning message for unsafe inline scripts
	'warning_allowUnsafeInline' => 'Avertissement : Politique de sécurité du contenu -> Cette politique contient "unsafe-inline", ce qui est dangereux dans la politique script-src.'
);
?>

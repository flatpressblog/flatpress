<?php
$lang ['admin'] ['config'] ['submenu'] ['fpprotect'] = 'FlatPress Protect';

$lang ['admin'] ['config'] ['fpprotect'] = array(
	'head' => 'Paramètres de FlatPress Protect',
	'desc1' => 'Ici, tu peux modifier les options relatives à la sécurité de ton blog FlatPress. ' . //
		'La meilleure protection pour tes visiteurs et ton blog FlatPress est assurée lorsque toutes les options sont désactivées.',

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

	// Part for metadate in images after upload
	'allow_image_metadate' => 'Conserver les métadonnées et la qualité d\'origine des images téléchargées.',
	'allowImageMetadataDsc' => 'Une fois que les images ont été téléchargées avec l\'uploader, les métadonnées sont conservées. Il s\'agit par exemple des informations relatives à l\'appareil photo et des coordonnées géographiques.',

	// Part for the visitor-ip in FlatPress
	'allow_visitor_ip' => 'Permet à FlatPress d\'utiliser l\'adresse IP non anonymisée du visiteur.',
	'allowVisitorIpDsc' => 'FlatPress enregistre alors, entre autres dans les commentaires, l\'adresse IP non anonymisée. ' . //
		'Si tu utilises le service d\'Akismet Antispam, Akismet reçoit également l\'adresse IP non anonymisée.',

	'submit' => 'Enregistrer les paramètres',
		'msgs' => array(
		1 => 'Paramètres enregistrés avec succès.',
		-1 => 'Erreur lors de l\'enregistrement des paramètres.'
	),

	// Warning message
	'warning_allowUnsafeInline' => 'Avertissement: Politique de sécurité du contenu -> Cette politique contient "unsafe-inline", ce qui est dangereux dans la politique script-src.',
	'warning_allowVisitorIp' => 'Avertissement: utilisation d\'adresses IP non anonymisées du visiteur -> N\'oublie pas d\'en informer les <a href="static.php?page=privacy-policy" title="modifier une page statique">visiteurs de ton blog FlatPress</a>!'
);
?>

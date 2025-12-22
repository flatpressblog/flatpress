<?php
$lang ['admin'] ['maintain'] ['submenu'] ['support'] = 'Afficher les données de support';

$lang ['admin'] ['maintain'] ['support'] = array(
		'title' => 'Données de support',
		'intro' => 'Pour signaler un bogue ou demander de l’aide, visitez le <a href="https://forum.flatpress.org" target="_blank">forum FlatPress</a>, ' . //
		'ouvrez un ticket sur <a href="https://github.com/flatpressblog/flatpress/issues" target="_blank">GitHub</a> ' . //
		'ou <a href="mailto:hello@flatpress.org">envoyez un e-mail</a>.<br>Copiez ces informations en anglais ' . //
		'en ajoutant : description de l’erreur et étapes pour la reproduire.',

	// output "Setup"
	'h2_general' => 'Général',
	'h3_setup' => 'Configuration',

	'version' => '<p class="output"><strong>Version de FlatPress :</strong> ',
	'basedir' => '<p class="output"><strong>Dossier racine :</strong> ',
	'blogbaseurl' => '<p class="output"><strong>URL de base du blog :</strong> ',

	'pos_theme' => '<p class="output"><strong>Thème :</strong> ',
	'neg_theme' => '<p class="output"><strong>Thème :</strong> non défini (leggero par défaut)</p>',

	'pos_style' => '<p class="output"><strong>Style :</strong> ',
	'neg_style' => '<p class="output"><strong>Style :</strong> style par défaut</p>',

	'pos_plugins' => '<p class="output"><strong>Extensions activées :</strong> ',
	'neg_plugins' => '<p class="output"><strong>Extensions activées :</strong> impossible de déterminer.</p>',

	// output "International"
	'h3_international' => 'Internationalisation',

	'pos_LANG_DEFAULT' => '<p class="output"><strong>Langue (automatique) :</strong> ',
	'neg_LANG_DEFAULT' => '<p class="output"><strong>Langue (automatique) : &#8505;</strong> non reconnue</p>',

	'pos_lang' => '<p class="output"><strong>Langue (définie) :</strong> ',
	'neg_lang' => '<p class="output"><strong>Langue (définie) :</strong> non définie</p>',

	'pos_charset' => '<p class="output"><strong>Jeu de caractères :</strong> ',
	'neg_charset' => '<p class="output"><strong>Jeu de caractères :</strong> non défini (utf-8 par défaut)</p>',

	'global_date_time' => '<p class="output"><strong>Date et heure UTC :</strong> ',
	'neg_global_date_time' => 'Impossible à déterminer.</p>',

	'local_date_time' => '<p class="output"><strong>Date et heure locales :</strong> ',
	'neg_local_date_time' => 'Impossible à déterminer.</p>',

	'time_offset' => '<p class="output"><strong>Décalage horaire :</strong> ',

	// output "Core files"
	'h2_permissions' => 'Droits des fichiers et répertoires',
	'h3_core_files' => 'Noyau',

	'desc_setupfile' => '<p>Une fois l’installation terminée, supprimez le fichier setup.php avant la mise en production.</p>',
	'error_setupfile' => '<p class="error"><strong>&#33;</strong> Le fichier setup se trouve dans le répertoire principal&nbsp;!</p>',
	'success_setupfile' => '<p class="success"><strong>&#10003;</strong> Le fichier setup n’a pas été trouvé dans le répertoire principal.</p>',

	'desc_defaultsfile' => '<p>Le fichier defaults.php devrait être protégé en écriture en production.</p>',
	'attention_defaultsfile' => '<p class="attention"><strong>&#8505;</strong> Le fichier defaults.php peut être modifié&nbsp;!</p>',
	'success_defaultsfile' => '<p class="success"><strong>&#10003;</strong> Le fichier defaults.php ne peut pas être modifié.</p>',

	'desc_configdir' => '<p>Le répertoire config devrait être protégé en écriture en production.</p>',
	'error_configdir' => '<p class="error"><strong>&#33;</strong> Le répertoire de configuration est accessible en écriture&nbsp;!</p>',
	'success_configdir' => '<p class="success"><strong>&#10003;</strong> Le répertoire de configuration n’est pas accessible en écriture.</p>',

	'desc_admindir' => '<p>Le répertoire admin devrait être protégé en écriture en production.</p>',
	'attention_admindir' => '<p class="attention"><strong>&#8505;</strong> Le répertoire admin est accessible en écriture&nbsp;!</p>',
	'success_admindir' => '<p class="success"><strong>&#10003;</strong> Le répertoire admin n’est pas accessible en écriture.</p>',

	'desc_includesdir' => '<p>Le répertoire fp-includes devrait être protégé en écriture en production.</p>',
	'attention_includesdir' => '<p class="attention"><strong>&#8505;</strong> Le répertoire fp-includes est accessible en écriture&nbsp;!</p>',
	'success_includesdir' => '<p class="success"><strong>&#10003;</strong> Le répertoire fp-includes n’est pas accessible en écriture.</p>',

	// output "Configuration file for the webserver"
	'h3_configwebserver' => 'Fichier de configuration du serveur web',

	'note_configwebserver' => 'Le répertoire principal doit être accessible en écriture pour créer ou modifier un fichier .htaccess avec l’extension PrettyURLs.<br>' . //
		'<strong>Remarque :</strong> seuls les serveurs compatibles NCSA, comme Apache, utilisent les fichiers .htaccess.',
	'serversoftware' => 'Le logiciel serveur est <strong>' . $_SERVER ['SERVER_SOFTWARE'] . '</strong>.',

	'success_maindir' => '<p class="success"><strong>&#10003;</strong> Le répertoire principal de FlatPress est accessible en écriture.</p>',
	'attention_maindir' => '<p class="attention"><strong>&#8505;</strong> Le répertoire principal de FlatPress n’est pas accessible en écriture&nbsp;!</p>',

	'success_htaccessw' => '<p class="success"><strong>&#10003;</strong> Le fichier .htaccess est accessible en écriture.</p>',
	'attention_htaccessw' => '<p class="attention"><strong>&#8505;</strong> Le fichier .htaccess n’est pas accessible en écriture&nbsp;!</p>',

	'attention_htaccessn' => '<p class="attention"><strong>&#8505;</strong> Un fichier .htaccess existe déjà dans le répertoire principal&nbsp;!</p>',
	'success_htaccessn' => '<p class="success"><strong>&#10003;</strong> Aucun fichier .htaccess n’a été trouvé dans le répertoire principal.</p>',

	// output "Themes and plugins"
	'h3_themesplugins' => 'Thèmes et extensions',

	'desc_interfacedir' => 'Le répertoire fp-interface devrait être protégé en écriture en production.',
	'attention_interfacedir' => '<p class="attention"><strong>&#8505;</strong> Le répertoire fp-interface est accessible en écriture&nbsp;!</p>',
	'success_interfacedir' => '<p class="success"><strong>&#10003;</strong> Le répertoire fp-interface n’est pas accessible en écriture.</p>',

	'desc_themesdir' => 'Le répertoire themes devrait être protégé en écriture en production.',
	'attention_themesdir' => '<p class="attention"><strong>&#8505;</strong> Le répertoire themes est accessible en écriture&nbsp;!</p>',
	'success_themesdir' => '<p class="success"><strong>&#10003;</strong> Le répertoire themes n’est pas accessible en écriture.</p>',

	'desc_plugindir' => 'Le répertoire fp-plugins devrait être protégé en écriture en production.',
	'attention_plugindir' => '<p class="attention"><strong>&#8505;</strong> Le répertoire fp-plugins est accessible en écriture&nbsp;!</p>',
	'success_plugindir' => '<p class="success"><strong>&#10003;</strong> Le répertoire fp-plugins n’est pas accessible en écriture.</p>',

	// output "Content directory"
	'h3_contentdir' => 'Contenu',

	'desc_contentdir' => 'Le répertoire fp-content doit être accessible en écriture pour que FlatPress fonctionne.',
	'success_contentdir' => '<p class="success"><strong>&#10003;</strong> Le répertoire fp-content est accessible en écriture.</p>',
	'error_contentdir' => '<p class="error"><strong>&#33;</strong> Le répertoire fp-content n’est pas accessible en écriture&nbsp;!</p>',

	'desc_imagesdir' => 'Le répertoire images doit être accessible en écriture pour téléverser des images.',
	'success_imagesdir' => '<p class="success"><strong>&#10003;</strong> Le répertoire images est accessible en écriture.</p>',
	'error_imagesdir' => '<p class="error"><strong>&#33;</strong> Le répertoire images n’est pas accessible en écriture&nbsp;!</p>',
	'attention_imagesdir' => '<p class="attention"><strong>&#8505;</strong> Le répertoire images n’existe pas.</p>',

	'desc_thumbsdir' => 'Le répertoire thumbs doit être accessible en écriture pour créer des vignettes.',
	'success_thumbsdir' => '<p class="success"><strong>&#10003;</strong> Le répertoire images/.thumbs est accessible en écriture.</p>',
	'error_thumbsdir' => '<p class="error"><strong>&#33;</strong> Le répertoire images/.thumbs n’est pas accessible en écriture&nbsp;!</p>',
	'attention_thumbsdir' => '<p class="attention"><strong>&#8505;</strong> Le répertoire .thumbs n’existe pas, ' . //
		'il sera créé automatiquement dès qu’une vignette aura été générée avec l’extension Thumbnails.</p>',

	'desc_attachsdir' => 'Le répertoire d’upload doit être accessible en écriture pour envoyer des fichiers.',
	'success_attachsdir' => '<p class="success"><strong>&#10003;</strong> Le répertoire d’upload est accessible en écriture.</p>',
	'error_attachsdir' => '<p class="error"><strong>&#33;</strong> Le répertoire d’upload n’est pas accessible en écriture&nbsp;!</p>',
	'attention_attachsdir' => '<p class="attention"><strong>&#8505;</strong> Le répertoire d’upload n’existe pas, ' . //
		'il sera créé automatiquement lors du premier envoi.</p>',

	'desc_cachedir' => 'Le répertoire cache doit être accessible en écriture pour fonctionner correctement.',
	'success_cachedir' => '<p class="success"><strong>&#10003;</strong> Le répertoire cache est accessible en écriture.</p>',
	'error1_cachedir' => '<p class="error"><strong>&#33;</strong> Le répertoire cache n’est pas accessible en écriture&nbsp;!</p>',
	'error2_cachedir' => '<p class="error"><strong>&#33;</strong> Le répertoire cache n’existe pas&nbsp;!</p>',

	// output "PHP"
	'h2_php' => 'PHP',

	'php_ver' => '<strong>Version : </strong>',

	'php_timezone' => '<strong>Fuseau horaire : </strong>',
	'php_timezone_neg' => 'Non disponible. UTC est utilisé.',

	'h3_extensions' => 'Extensions',

	'desc_php_intl' => 'L’extension PHP Intl doit être activée.',
	'error_php_intl' => '<p class="error"><strong>&#33;</strong> L’extension intl n’est pas activée&nbsp;!</p>',
	'success_php_intl' => '<p class="success"><strong>&#10003;</strong> L’extension intl est activée.</p>',

	'desc_php_gdlib' => 'L’extension GDlib doit être activée pour créer des vignettes.',
	'error_php_gdlib' => '<p class="error"><strong>&#33;</strong> L’extension GD n’est pas activée&nbsp;!</p>',
	'success_php_gdlib' => '<p class="success"><strong>&#10003;</strong> L’extension GD est activée.</p>',

	'desc_php_mbstring' => 'Pour des performances optimales, l’extension PHP multibyte doit être activée pour Smarty.',
	'attention_php_mbstring' => '<p class="attention"><strong>&#8505;</strong> L’extension Multibyte n’est pas activée&nbsp;!</p>',
	'success_php_mbstring' => '<p class="success"><strong>&#10003;</strong> L’extension Multibyte est activée.</p>',

	// output "Other"
	'h2_other' => 'Divers',

	'desc_browser' => 'Le navigateur utilisé peut être utile en cas d’erreurs d’affichage.',
	'no_browser' => 'Non reconnu',
	'detect_browser' => '<p class="output"><strong>Navigateur : </strong>',

	'desc_cookie' => 'Si les visiteurs doivent être informés des cookies, voici celui utilisé.<br>' . //
		'<strong>Astuce :</strong> le nom du cookie change à chaque réinstallation de FlatPress.',
	'session_cookie' => '<p class="output"><strong>Cookie de session : </strong>',
	'no_session_cookie' => 'Impossible à déterminer.',

	'h3_completed' => 'Sortie terminée&nbsp;!',

	'symbols' => '<p class="output"><strong>Symboles :</strong></p>',
	'symbol_success' => '<p class="success"><strong>&#10003;</strong> Aucune action nécessaire</p>',
	'symbol_attention' => '<p class="attention"><strong>&#8505;</strong> Ne bloque pas le fonctionnement, mais requiert une attention</p>',
	'symbol_error' => '<p class="error"><strong>&#33;</strong> Action immédiate requise</p>',

	'close_btn' => 'Fermer'
);
?>

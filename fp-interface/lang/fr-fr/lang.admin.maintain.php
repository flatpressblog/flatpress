<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Maintenance';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Maintenance',
	'descr' => 'Venez ici lorsque vous croyez que quelque chose cloche. peut-&ecirc;tre trouverez vous une solution!',
	'opt0' => '&laquo; Retour au menu principal',
	'opt1' => 'Reconstruire les index',
	'opt2' => 'Purger le cache des th&egrave;mes et des templates',
	'opt3' => 'Rétablir les autorisations pour l\'exploitation productive',
	'opt4' => 'Afficher info.php',
	'opt5' => 'V&eacute;rifier les mises &agrave; jour',

	'chmod_info' => 'Si les autorisations <strong>n\'ont pas pu être réinitialisées</strong>, le propriétaire du fichier/répertoire n\'est probablement pas le même que celui du serveur web.<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>Autorisations</th>
					<th>' . FP_CONTENT . '</th>
					<th>Noyau</th>
					<th>Tous les autres</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Fichiers</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>Répertoires</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => 'Toutes les autorisations ont été mises à jour avec succès.',
	'opt3_error' => 'Erreur lors de la définition des autorisations:'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Op&eacute;ration effectu&eacute;e',
	-1 => '&Eacute;chec de l\'op&eacute;ration'
);
	
$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Mises &agrave; jour',
	'list' => '<ul>
		<li>Votre version de FlatPress <big>%s</big></li>
		<li>La derni&egrave;re version stable de FlatPress est <big><a href="%s">%s</a></big></li>
		<li>La derni&egrave;re version dev(beta, rc) de FlatPress est <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Note:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'Mises &agrave; jour disponibles!',
	2 => 'Vous avez d&eacute;j&agrave; la version la plus r&eacute;cente!',
	-1 => 'Impossible de V&eacute;rifier les mises &agrave; jour'
);
?>

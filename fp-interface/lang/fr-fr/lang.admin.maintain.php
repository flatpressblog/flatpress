<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Maintenance';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Maintenance',
	'descr' => 'Venez ici lorsque vous pensez que quelque chose ne fonctionne pas correctement. Peut-être trouverez-vous une solution !',

	'opt0' => '« Retour au menu principal',
	'opt1' => 'Reconstruire les index',
	'opt2' => 'Vider le cache des thèmes et des modèles',
	'opt3' => 'Réinitialiser les autorisations pour un fonctionnement correct',
	'opt4' => 'Afficher info.php',
	'opt5' => 'Vérifier les mises à jour',
	'opt6' => 'État du cache APCu',

	'chmod_info' => 'Si les autorisations <strong>n’ont pas pu être réinitialisées</strong>, le propriétaire du fichier/répertoire n’est probablement pas le même que celui du serveur web.<br>' . //
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
	'opt3_error' => 'Erreur lors de la définition des autorisations :'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Opération effectuée',
	-1 => 'Échec de l’opération'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Mises à jour',
	'list' => '<ul>
		<li>Votre version de FlatPress : %s</li>
		<li>La dernière version stable de FlatPress est <a href="%s">%s</a></li>
		<li>La dernière version dev (beta, rc) de FlatPress est <a href="%s">%s</a></li>
	</ul>',
	'notice' => 'Remarque :',
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'Mises à jour disponibles !',
	2 => 'Vous utilisez déjà la version la plus récente !',
	-1 => 'Impossible de vérifier les mises à jour.'
);

$lang ['admin'] ['maintain'] ['apcu'] = array(
	'head' => 'Cache APCu',
	'descr' => 'Aperçu de l’utilisation de la mémoire partagée APCu, et de l’efficacité du cache.',
	'status_heading' => 'État heuristique',
	'status_good' => 'Le cache semble correctement dimensionné pour la charge de travail actuelle.',
	'status_bad' => 'Taux d’échec élevé ou mémoire libre très faible : le cache APCu semble trop petit ou fortement fragmenté.',
	'hit_rate' => 'Taux de réussite',
	'free_mem' => 'Mémoire libre',
	'total_mem' => 'Mémoire partagée totale',
	'used_mem' => 'Mémoire utilisée',
	'avail_mem' => 'Mémoire disponible',
	'memory_type' => 'Type de mémoire',
	'memory_type_unknown' => 'n/a',
	'num_slots' => 'Nombre d’emplacements',
	'num_hits' => 'Nombre de réussites',
	'num_misses' => 'Nombre d’échecs',
	'cache_type' => 'Type de cache',
	'cache_user_only' => 'Cache des données utilisateur',
	'legend_good' => 'Vert : la configuration semble saine (taux de réussite élevé, mémoire libre suffisante).',
	'legend_bad' => 'Rouge : sous-dimensionnement du cache (nombreux échecs ou presque aucune mémoire libre).',
	'no_apcu' => 'APCu ne semble pas être activé sur ce serveur.',
	'back' => '« Retour à la maintenance',
	'clear_fp_button' => 'Effacer les entrées APCu de FlatPress',
	'clear_fp_confirm' => 'Voulez-vous vraiment supprimer toutes les entrées APCu ? Cela videra les caches APCu de FlatPress.',
	'clear_fp_result' => 'Suppression de %d entrées APCu.',
	'msgs' => array(
		1  => 'Les entrées APCu de FlatPress ont été effacées.',
		2  => 'Aucune entrée APCu n’a été trouvée.',
		-1 => 'APCu n’est pas disponible ou n’a pas pu être accédé ; aucune suppression n’a été effectuée.'
	)
);
?>

<?php
$lang ['admin'] ['panel'] ['maintain'] = 'Vzdrževanje';

$lang ['admin'] ['maintain'] ['default'] = array(
	'head' => 'Vzdrževanje',
	'descr' => 'Pridite sem, če mislite, da se je kaj zapletlo, in morda tukaj boste našli rešitev. Vendar to morda ne bo delovalo.',
	'opt0' => '&laquo; Nazaj na glavni meni',
	'opt1' => 'Ponovno zgradi indeks',
	'opt2' => 'Počisti predpomnilnik teme in predlog',
	'opt3' => 'Obnovitev dovoljenj za produktivno delovanje',
	'opt4' => 'Prikaži informacije o PHP',
	'opt5' => 'Preveri posodobitve',
	'opt6' => 'Stanje predpomnilnika APCu',

	'chmod_info' => 'Če dovoljenj <strong>ne morete ponastaviti</strong>, lastnik datoteke/imenikov verjetno ni enak lastniku spletnega strežnika.<br>' . //
		'
		<table>
			<thead>
				<tr>
					<th>Pooblastila</th>
					<th>' . FP_CONTENT . '</th>
					<th>Jedro</th>
					<th>Vsi drugi</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Datoteke</td>
					<td>' . decoct(FILE_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_FILE_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_FILE_PERMISSIONS) . '</td>
				</tr>
				<tr>
					<td>Imeniki</td>
					<td>' . decoct(DIR_PERMISSIONS) . '</td>
					<td>' . decoct(CORE_DIR_PERMISSIONS) . '</td>
					<td>' . decoct(RESTRICTED_DIR_PERMISSIONS) . '</td>
				</tr>
			</tbody>
		</table>
		',

	'opt3_success' => 'Vsa dovoljenja so bila uspešno posodobljena.',
	'opt3_error' => 'Napaka pri nastavljanju pooblastil:'
);

$lang ['admin'] ['maintain'] ['default'] ['msgs'] = array(
	1 => 'Operacija je končana',
	-1 => 'Operacija ni uspela'
);

$lang ['admin'] ['maintain'] ['updates'] = array(
	'head' => 'Posodobitve',
	'list' => '<ul>
		<li>Imate različico FlatPress <big>%s</big></li>
		<li>Zadnja stabilna različica za FlatPress je <big><a href="%s">%s</a></big></li>
		<li>Zadnja nestabilna različica za FlatPress je <big><a href="%s">%s</a></big></li>
		</ul>',
	'notice' => 'Opomba:'
);

$lang ['admin'] ['maintain'] ['updates'] ['msgs'] = array(
	1 => 'Na voljo so posodobitve!',
	2 => 'Ste že posodobljeni',
	-1 => 'Ni mogoče pridobiti posodobitev'
);

$lang ['admin'] ['maintain'] ['apcu'] = array(
	'head' => 'Predpomnilnik APCu',
	'descr' => 'Pregled uporabe deljenega pomnilnika APCu in učinkovitosti predpomnilnika.',
	'status_heading' => 'Heuristično stanje',
	'status_good' => 'Predpomnilnik se zdi primeren za trenutno delovno obremenitev.',
	'status_bad' => 'Visoka stopnja zgrešenih zadetkov ali zelo malo prostega pomnilnika: predpomnilnik APCu je morda premajhen ali močno fragmentiran.',
	'hit_rate' => 'Stopnja zadetkov',
	'free_mem' => 'Prosti pomnilnik',
	'total_mem' => 'Skupni skupni pomnilnik',
	'used_mem' => 'Porabljen pomnilnik',
	'avail_mem' => 'Razpoložljiv pomnilnik',
	'memory_type' => 'Vrsta pomnilnika',
	'memory_type_unknown' => 'n/a',
	'num_slots' => 'Število rež',
	'num_hits' => 'Število zadetkov',
	'num_misses' => 'Število zgrešenih zadetkov',
	'cache_type' => 'Vrsta predpomnilnika',
	'cache_user_only' => 'Predpomnilnik uporabniških podatkov',
	'legend_good' => 'Zelena: konfiguracija je videti zdrava (visoka stopnja zadetkov, razumno veliko prostega pomnilnika).',
	'legend_bad' => 'Rdeča: predpomnilnik je pod pritiskom (veliko zgrešenih ali skoraj ni prostega pomnilnika).',
	'no_apcu' => 'APCu na tem strežniku ni omogočen.',
	'back' => '&laquo; Nazaj na vzdrževanje',
	'clear_fp_button'=> 'Izbriši vnose FlatPress APCu',
	'clear_fp_confirm' => 'Ali res želite izbrisati vse vnose APCu? S tem boste izbrisali predpomnilnike APCu FlatPress.',
	'clear_fp_result'=> 'Izbrisanih %d vnosov APCu.',
	'msgs' => array(
		1  => 'Vnosi FlatPress APCu so bili izbrisani.',
		2  => 'Ni bilo najdenih vnosov APCu.',
		-1 => 'APCu ni na voljo ali ni mogoče dostopati do njega; nič ni bilo izbrisano.'
	)
);
?>

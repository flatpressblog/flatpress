<?php

$lang['plugin']['qspam'] = array(
    'error' => 'NAPAKA: Komentar vsebuje prepovedane besede'
);

$lang['admin']['plugin']['submenu']['qspam'] = 'Hitri Spam Filter';
$lang['admin']['plugin']['qspam'] = array(
    'head' => 'Nastavitve Hitrega Spam FIltra',
    'desc1' => 'Ne dovoli komentarjev, ki vsebujejo naslednje besede (napiši eno besedo na vrstico):',
    'desc2' => '<strong>Opozorilo:</strong> Komentar ne bo dovoljen, če vsebuje eno prepovedano besedo tudi kot del druge besede. 

    (npr. "stara" se ujema tudi z "pre<strong>stara</strong>")',
    'options' => 'Druge možnosti',
    'desc3' => 'Število Prepovedanih Besed',
    'desc3pre' => 'Prepreči komentarje, ki vsebujejo več kot ',
    'desc3post' => ' prepovedane besede.',
    'submit' => 'Shrani nastavitve',
    'msgs' => array(
        1 => 'Prepovedane besede uspešno shranjene.',
        -1 => 'Prepovedane besede niso shranjene.'
    )
);

?>

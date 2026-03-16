<?php
/**
 * @var callable $_
 */
?>
<?= MessageBox::error(
    $_('Etherpad-Konfigurationsfehler'),
    [
        $_('Die Verbindung zu Etherpad ist nicht möglich.'),
        $_('Wenden Sie sich bitte an ihre Stud.IP-Administration. Das Etherpad-Plugin ist nicht korrekt konfiguriert.'),
    ]
) ?>

<?php
/**
 * @var SettingsController $controller
 * @var array $settings
 * @var callable $_
 */
?>
<form action="<?= $controller->store() ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= $_('Wer darf Gruppen-Pads verwalten?') ?></legend>

        <label>
            <input type="radio" name="settings[statusgruppen_admin_permission]" value="tutor"
                <? if ($settings['statusgruppen_admin_permission'] === 'tutor') echo 'checked'; ?>>
            <?= $_('Lehrende und Tutor:innen') ?>
        </label>
        <label>
            <input type="radio" name="settings[statusgruppen_admin_permission]" value="autor"
                <? if ($settings['statusgruppen_admin_permission'] === 'autor') echo 'checked'; ?>>
            <?= $_('Alle Mitglieder der Gruppe') ?>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= \Studip\Button::createAccept(_('Speichern')) ?>
        <?= \Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('')) ?>
    </footer>
</form>

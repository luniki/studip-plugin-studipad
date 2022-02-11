<form action="<?= $controller->url_for('settings/store') ?>" method="post" class="default">
    <?= \CSRFProtection::tokenTag() ?>

    <fieldset>
        <label><?= dgettext('studipad', 'Wer darf Gruppen-Pads verwalten?') ?></label>

        <label>
            <input type="radio" name="settings[statusgruppen_admin_permission]" value="tutor"
                <? if ($settings['statusgruppen_admin_permission'] === 'tutor') echo 'checked'; ?>>
            <?= dgettext('studipad', 'Lehrende und Tutor:innen') ?>
        </label>
        <label>
            <input type="radio" name="settings[statusgruppen_admin_permission]" value="autor"
                <? if ($settings['statusgruppen_admin_permission'] === 'autor') echo 'checked'; ?>>
            <?= dgettext('studipad', 'Alle Mitglieder der Gruppe') ?>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= \Studip\Button::createAccept(_('Speichern')) ?>
        <?= \Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('')) ?>
    </footer>
</form>

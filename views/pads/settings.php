<form class="default studipad-settings" action="<?= $controller->url_for('pads/store_settings', $padid) ?>" method="POST">
    <fieldset>
        <legend><?= dgettext('studipad', 'Etherpad Toolbar') ?></legend>

        <label class="checkbox">
            <input type="checkbox" name="showChat" value="true"
                   <?= ($pad['showChat']) ? ' checked' : '' ?>>
            <?= dgettext('studipad', 'Chat anzeigen') ?>
        </label>

        <label class="checkbox">
            <input type="checkbox" name="showLineNumbers" value="true"
                   <?= ($pad['showLineNumbers']) ? ' checked' : '' ?>>
            <?= dgettext('studipad', 'Zeilennummern anzeigen') ?>
        </label>

    </fieldset>

    <footer data-dialog-button>
        <?= \Studip\Button::createAccept(dgettext('studipad', 'Speichern')) ?>
        <?= \Studip\LinkButton::createCancel(dgettext('studipad', 'Abbrechen'), $controller->url_for('')) ?>
    </footer>

    <? if ($toPage) { ?>
        <input type="hidden" name="page" value="1">
    <? } ?>
</form>

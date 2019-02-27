<form class="default studipad-settings" action="<?= $controller->url_for('pads/store_settings', $padid) ?>" method="POST">
    <fieldset>
        <legend><?= dgettext('studipad', 'Etherpad Toolbar') ?></legend>

        <label class="checkbox">
            <input type="checkbox" name="showControls" value="true" data-activates=".subcontrol"
                   <?= $pad['showControls'] ? ' checked' : '' ?>>
            <?= dgettext('studipad', 'Kontrollelemente anzeigen') ?>
        </label>

        <? $disabled = $pad['showControls'] ? '' : ' disabled ' ?>

        <label class="checkbox"<?= $disabled?>>
            <input type="checkbox" name="showColorBlock" value="true" class="subcontrol"
                   <?= ($pad['showColorBlock'] && $pad['showControls']) ? 'checked' : '' ?>
                   <?= $disabled?>>
            <?= dgettext('studipad', 'Farbsteuerelemente anzeigen') ?>
        </label>

        <label class="checkbox "<?= $disabled ?>>
            <input type="checkbox" name="showImportExportBlock" value="true" class="subcontrol"
                   <?= ($pad['showImportExportBlock'] && $pad['showControls']) ? ' checked' : '' ?>
                   <?= $disabled ?>>
            <?= dgettext('studipad', 'Import-/Exportelemente ein- oder ausblenden') ?>
        </label>

        <label class="checkbox">
            <input type="checkbox" name="showChat" value="true"
                   <?= ($pad['showChat'] && $pad['showControls']) ? ' checked' : '' ?>
                   <?= $disabled ?>>
            <?= dgettext('studipad', 'Chat anzeigen') ?>
        </label>

        <label class="checkbox">
            <input type="checkbox" name="showLineNumbers" value="true"
                   <?= ($pad['showLineNumbers'] && $pad['showControls']) ? ' checked' : '' ?>
                   <?= $disabled ?>>
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

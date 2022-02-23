<?php
    $controls = $pad->getControls();
?>
<form class="default studipad-settings" action="<?= $controller->url_for('pads/store_settings') ?>" method="POST">
    <fieldset>
        <legend><?= dgettext('studipad', 'Etherpad Toolbar') ?></legend>

        <label class="checkbox">
            <input type="checkbox" name="showControls" value="true" data-activates=".subcontrol"
                   <?= $controls['showControls'] ? ' checked' : '' ?>>
            <?= dgettext('studipad', 'Kontrollelemente anzeigen') ?>
        </label>

        <? $disabled = $controls['showControls'] ? '' : ' disabled ' ?>

        <label class="checkbox"<?= $disabled?>>
            <input type="checkbox" name="showColorBlock" value="true" class="subcontrol"
                   <?= ($controls['showColorBlock'] && $controls['showControls']) ? 'checked' : '' ?>
                   <?= $disabled?>>
            <?= dgettext('studipad', 'Farbsteuerelemente anzeigen') ?>
        </label>

        <label class="checkbox "<?= $disabled ?>>
            <input type="checkbox" name="showImportExportBlock" value="true" class="subcontrol"
                   <?= ($controls['showImportExportBlock'] && $controls['showControls']) ? ' checked' : '' ?>
                   <?= $disabled ?>>
            <?= dgettext('studipad', 'Import-/Exportelemente ein- oder ausblenden') ?>
        </label>

        <label class="checkbox">
            <input type="checkbox" name="showChat" value="true"
                   <?= ($controls['showChat'] && $controls['showControls']) ? ' checked' : '' ?>
                   <?= $disabled ?>>
            <?= dgettext('studipad', 'Chat anzeigen') ?>
        </label>

        <label class="checkbox">
            <input type="checkbox" name="showLineNumbers" value="true"
                   <?= ($controls['showLineNumbers'] && $controls['showControls']) ? ' checked' : '' ?>
                   <?= $disabled ?>>
            <?= dgettext('studipad', 'Zeilennummern anzeigen') ?>
        </label>

    </fieldset>

    <footer data-dialog-button>
        <?= \Studip\Button::createAccept(dgettext('studipad', 'Speichern')) ?>
        <?= \Studip\LinkButton::createCancel(dgettext('studipad', 'Abbrechen'), $controller->url_for('')) ?>
    </footer>

    <input type="hidden" name="pad" value="<?= htmlReady($pad->getName()) ?>">
    <input type="hidden" name="range" value="<?= htmlReady($pad->getGroup()->getRangeId()) ?>">

    <? if (\Request::submitted('list')) { ?>
        <input type="hidden" name="list" value="1">
    <? } ?>
</form>

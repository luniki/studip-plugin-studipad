<?php
/**
 * @var PadsController $controller
 * @var EtherpadPlugin\Pad $pad
 * @var callable $_
 */
?>
<?php
    $controls = $pad->getControls();
?>
<form class="default studipad-settings" action="<?= $controller->store_settings() ?>" method="POST">
    <fieldset>
        <legend><?= $_('Etherpad Toolbar') ?></legend>

        <label class="checkbox">
            <input type="checkbox" name="showControls" value="true" data-activates=".subcontrol"
                   <?= $controls['showControls'] ? ' checked' : '' ?>>
            <?= $_('Kontrollelemente anzeigen') ?>
        </label>

        <? $disabled = $controls['showControls'] ? '' : ' disabled ' ?>

        <label class="checkbox"<?= $disabled?>>
            <input type="checkbox" name="showColorBlock" value="true" class="subcontrol"
                   <?= $controls['showColorBlock'] && $controls['showControls'] ? 'checked' : '' ?>
                   <?= $disabled?>>
            <?= $_('Farbsteuerelemente anzeigen') ?>
        </label>

        <label class="checkbox "<?= $disabled ?>>
            <input type="checkbox" name="showImportExportBlock" value="true" class="subcontrol"
                   <?= $controls['showImportExportBlock'] && $controls['showControls'] ? ' checked' : '' ?>
                   <?= $disabled ?>>
            <?= $_('Import-/Exportelemente ein- oder ausblenden') ?>
        </label>

        <label class="checkbox">
            <input type="checkbox" name="showChat" value="true"
                   <?= $controls['showChat'] && $controls['showControls'] ? ' checked' : '' ?>
                   <?= $disabled ?>>
            <?= $_('Chat anzeigen') ?>
        </label>

        <label class="checkbox">
            <input type="checkbox" name="showLineNumbers" value="true"
                   <?= $controls['showLineNumbers'] && $controls['showControls'] ? ' checked' : '' ?>
                   <?= $disabled ?>>
            <?= $_('Zeilennummern anzeigen') ?>
        </label>

    </fieldset>

    <footer data-dialog-button>
        <?= \Studip\Button::createAccept($_('Speichern')) ?>
        <?= \Studip\LinkButton::createCancel($_('Abbrechen'), $controller->url_for('')) ?>
    </footer>

    <input type="hidden" name="pad" value="<?= htmlReady($pad->getName()) ?>">
    <input type="hidden" name="range" value="<?= htmlReady($pad->getGroup()->getRangeId()) ?>">

<? if (Request::submitted('list')): ?>
    <input type="hidden" name="list" value="1">
<? endif; ?>
</form>

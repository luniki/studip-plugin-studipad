<?php
/**
 * @var PadsController $controller
 * @var EtherpadPlugin\Group $group
 * @var callable $_
 */
?>
<form class="default studipad-new-pad" action="<?= $controller->create() ?>" method="POST">
    <fieldset>
        <label>
            <?= $_('Name des neuen Pads') ?>
            <input type="text" name="new_pad_name" value=""
                size="32" maxlength="32" pattern="[a-zA-Z0-9_-]{1,32}"
                required aria-describedby="new-pad-name-help">
        </label>
        <small id="new-pad-name-help">
            <?= $_('Erlaubte Zeichen: a-z, A-Z, 0-9, _ und -') ?>
        </small>

        <div>
            <?= \Studip\Button::createAccept($_('Neues Pad anlegen'), ['data-dialog-button' => '']) ?>
        </div>
    </fieldset>

    <input type="hidden" name="range" value="<?= htmlReady($group->getRangeId()) ?>">
</form>

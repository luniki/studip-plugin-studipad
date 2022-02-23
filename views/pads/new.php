<form class="default studipad-new-pad" action="<?= $controller->url_for('pads/create') ?>" method="POST">
    <fieldset>
        <label>
            <?= dgettext('studipad', 'Name des neuen Pads') ?>
            <input type="text" name="new_pad_name" value=""
                size="32" maxlength="32" pattern="[a-zA-Z0-9_-]{1,32}"
                required aria-describedby="new-pad-name-help">
        </label>
        <small id="new-pad-name-help">
            <?= dgettext('studipad', 'Erlaubte Zeichen: a-z, A-Z, 0-9, _ und -') ?>
        </small>

        <div>
            <?= \Studip\Button::createAccept(dgettext('studipad', 'Neues Pad anlegen'), ['data-dialog-button' => '']) ?>
        </div>
    </fieldset>

    <input type="hidden" name="range" value="<?= htmlReady($group->getRangeId()) ?>">
</form>

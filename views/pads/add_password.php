<form class="default" action="<?= $controller->url_for('pads/store_password', $padid) ?>" method="POST">
    <fieldset>
        <legend><?= dgettext('studipad', 'Etherpad-Passwort festlegen') ?></legend>

        <label>
            <?= dgettext('studipad', 'Passwort') ?>
            <input type="password" name="pad_password" value="" size="10" maxlength="32" aria-describedby="password-help-block">
        </label>

        <small id="password-help-block">
            <?= dgettext('studipad', 'Wenn ein Passwort gesetzt wird, kann das Pad nur nach Eingabe des Passwortes verwendet werden.') ?>
        </small>
    </fieldset>

    <footer data-dialog-button>
        <?= \Studip\Button::createAccept(dgettext('studipad', 'Passwort setzen')) ?>
        <?= \Studip\LinkButton::createCancel(dgettext('studipad', 'Abbrechen'), $controller->url_for('')) ?>
    </footer>

    <? if ($toPage) { ?>
        <input type="hidden" name="page" value="1">
    <? } ?>
</form>

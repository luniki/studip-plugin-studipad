<table class="default studipad-pads-index">
    <colgroup>
        <col width="50%" />
        <col width="30%" />
        <col width="10%" />
        <col width="10%" />
    </colgroup>

    <thead>
        <tr>
            <th><?= dgettext('studipad', 'Name des Pads') ?></th>
            <th><?= dgettext('studipad', 'öffentliche URL') ?></th>
            <th><?= dgettext('studipad', 'letzte Änderung') ?></th>
            <th class="actions"><?= dgettext('studipad', 'Aktionen') ?></th>
        </tr>
    </thead>

    <? foreach ($tpads as $padid => $pad) { ?>
        <tr>
            <td>
                <a href="<?= $controller->link_for('pads/iframe', $padid) ?>">

                    <?= htmlReady($pad['title']) ?>
                    <? if ($pad['new']) { ?>
                        <span class="studipad-new-content"><?= dgettext('studipad', 'neu') ?></span>
                    <? } ?>

                    <? if ($pad['readOnly']) { ?>
                        (<?= dgettext('studipad', 'schreibgeschützt') ?>)
                    <? } ?>

                    <? if ($pad['hasPassword']) { ?>
                        <?= Icon::create('lock-locked', Icon::ROLE_ATTENTION, ['title' => dgettext('studipad', 'Das Pad ist mit einem Passwort versehen.')]) ?>
                    <? } ?>
                </a>
            </td>

            <td>
                <? if ($pad['public']) { ?>
                    <a href="<?= htmlReady($pad['publicUrl']) ?>">
                        <?= htmlReady($pad['publicUrl']) ?>
                    </a>
                <? } else { ?>
                    —
                <? } ?>
            </td>

            <td>
                <? if ($pad['lastEdited']) { ?>
                    <?= strftime('%x, %H:%M', $pad['lastEdited']) ?>
                <? } ?>
            </td>

            <td class="actions">
                <? if ($padadmin) { ?>
                    <?=
                    \ActionMenu::get()
                               ->addLink(
                                   $controller->url_for('pads/open', $padid),
                                   dgettext('studipad', 'Öffnen'),
                                   Icon::create('link-extern'),
                                   ['data-dialog' => '']
                               )

                               ->addLink(
                                   $controller->url_for('pads/settings', $padid),
                                   dgettext('studipad', 'Einstellungen'),
                                   Icon::create('admin'),
                                   ['data-dialog' => '']
                               )

                               ->condition(false && !$pad['readOnly'])
                               ->addLink(
                                   $controller->url_for('pads/export_pdf', $padid),
                                   dgettext('studipad', 'Export als PDF'),
                                   Icon::create('file-pdf'),
                                   [
                                       'target' => '_blank',
                                       'rel' => 'noreferrer noopener'
                                   ]
                               )

                               ->condition(!$pad['readOnly'])
                               ->addLink(
                                   $controller->url_for('pads/snapshot', $padid),
                                   dgettext('studipad', 'Aktuellen Inhalt sichern'),
                                   Icon::create('cloud+export')
                               )

                               ->condition(!$pad['readOnly'])
                               ->addLink(
                                   $controller->url_for('pads/activate_write_protect', $padid),
                                   dgettext('studipad', 'Schreibschutz aktivieren'),
                                   Icon::create('lock-locked')
                               )
                               ->condition($pad['readOnly'])
                               ->addLink(
                                   $controller->url_for('pads/deactivate_write_protect', $padid),
                                   dgettext('studipad', 'Schreibschutz deaktivieren'),
                                   Icon::create('lock-unlocked')
                               )

                               ->condition(!$pad['hasPassword'])
                               ->addLink(
                                   $controller->url_for('pads/add_password', $padid),
                                   dgettext('studipad', 'Passwort festlegen'),
                                   Icon::create('key+add'),
                                   ['data-dialog' => '']
                               )
                               ->condition($pad['hasPassword'])
                               ->addLink(
                                   $controller->url_for('pads/remove_password', $padid),
                                   dgettext('studipad', 'Passwort löschen'),
                                   Icon::create('key+remove'),
                                   ['data-confirm' => dgettext('studipad', 'Wollen Sie das Passwort wirklich löschen?')]
                               )

                               ->condition(!$pad['public'])
                               ->addLink(
                                   $controller->url_for('pads/publish', $padid),
                                   dgettext('studipad', 'Veröffentlichen'),
                                   Icon::create('globe'),
                                   ['data-confirm' => dgettext('studipad', 'Wollen Sie das Pad wirklich öffentlich machen?')]
                               )
                               ->condition($pad['public'])
                               ->addLink(
                                   $controller->url_for('pads/unpublish', $padid),
                                   dgettext('studipad', 'Veröffentlichung beenden'),
                                   Icon::create('globe+decline')
                               )

                               ->addLink(
                                   $controller->url_for('pads/delete', $padid),
                                   dgettext('studipad', 'Pad löschen'),
                                   Icon::create('trash', Icon::ROLE_ATTENTION),
                                   ['data-confirm' => dgettext('studipad', 'Wollen Sie das Pad wirklich löschen?')]
                               )
                    ?>

                <? } ?>
            </td>
        </tr>
    <? } ?>
</table>

<? if ($padadmin) { ?>
    <form class="default studipad-new-pad" action="<?= $controller->url_for('pads/create')?>" method="POST">
        <fieldset>
            <label>
                <?= dgettext('studipad', 'Name des neuen Pads') ?>
                <input type="text" name="new_pad_name" value=""
                       size="32" maxlength="32" pattern="[a-zA-Z0-9_-]{1,32}"
                       required aria-describedby="new-pad-name-help">
            </label>
            <small id="new-pad-name-help"><?= dgettext('studipad', 'Erlaubte Zeichen: a-z, A-Z, 0-9, _ und -') ?></small>

            <div>
                <?= \Studip\Button::createAdd(dgettext('studipad', 'Neues Pad anlegen')) ?>
            </div>
        </fieldset>
    </form>
<? } ?>

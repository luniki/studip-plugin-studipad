<? $pads = $group->getPads(); ?>

<? if (count($pads)) { ?>

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
            <th class="actions"><?= $group->canAdmin() ? dgettext('studipad', 'Aktionen') : '' ?></th>
        </tr>
    </thead>

    <tbody>
        <?= $this->render_partial_collection('pads/groups/pad', $pads, 'pads/groups/spacer') ?>
    </tbody>
</table>

<? } else { ?>
    <section>
        <p> <?= dgettext('studipad', 'Diese Gruppe enthält noch keine Pads.') ?> </p>
        <?php if ($group->canAdmin()) { ?>
            <?= \Studip\LinkButton::createAdd(
                dgettext('studipad', 'Neues Pad anlegen'),
                $controller->url_for('pads/new', ['range' => $group->getRangeId()]),
                ['data-dialog' => 'reload-on-close;size=auto']
            ) ?>
        <? } ?>
    </section>
<? } ?>

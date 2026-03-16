<?php
/**
 * @var EtherpadPlugin\Group $group
 * @var PadsController $controller
 * @var callable $_
 */
?>
<? $pads = $group->getPads(); ?>

<? if (count($pads)) { ?>

<table class="default studipad-pads-index">
    <colgroup>
        <col style="width: 50%" />
        <col style="width: 30%" />
        <col style="width: 10%" />
        <col style="width: 10%" />
    </colgroup>

    <thead>
        <tr>
            <th><?= $_('Name des Pads') ?></th>
            <th><?= $_('öffentliche URL') ?></th>
            <th><?= $_('letzte Änderung') ?></th>
            <th class="actions"><?= $group->canAdmin() ? $_('Aktionen') : '' ?></th>
        </tr>
    </thead>

    <tbody>
        <?= $this->render_partial_collection('pads/groups/pad', $pads, 'pads/groups/spacer') ?>
    </tbody>
</table>

<? } else { ?>
    <section>
        <p> <?= $_('Diese Gruppe enthält noch keine Pads.') ?> </p>
        <?php if ($group->canAdmin()) { ?>
            <?= \Studip\LinkButton::createAdd(
                $_('Neues Pad anlegen'),
                $controller->url_for('pads/new', ['range' => $group->getRangeId()]),
                ['data-dialog' => 'reload-on-close;size=auto']
            ) ?>
        <? } ?>
    </section>
<? } ?>

<?php
    /** @var \Trails_Controller $controller */
    /** @var \EtherpadPlugin\Group $groupx */
    /** @var \EtherpadPlugin\Pad $pad */
?>
<tr>
    <td>
        <a href="<?= $controller->getPadLink('iframe', $pad) ?>">
            <span class="studipad-pad-name"> <?= htmlReady($pad->getName()) ?> </span>
            <? if ($pad->isNew()) { ?>
                <span class="studipad-badge studipad-bg-attention"><?= dgettext('studipad', 'neu') ?></span>
            <? } ?>

            <? if ($pad->isWriteProtected()) { ?>
                <span class="studipad-badge studipad-bg-secondary"><?= dgettext('studipad', 'schreibgeschützt') ?></span>
            <? } ?>
        </a>
    </td>

    <td>
        <? if ($pad->isPublic()) { ?>
            <a href="<?= htmlReady($pad->getPublicURL()) ?>"><?= htmlReady($pad->getPublicURL()) ?></a>
        <? } else { ?>
            —
        <? } ?>
    </td>

    <td>
        <? if ($pad->getLastEdit()) { ?>
            <?= strftime('%x, %H:%M', $pad->getLastEdit()) ?>
        <? } ?>
    </td>

    <td class="actions">
        <? if ($group->canAdmin()) { ?>
            <?= \ActionMenu::get()
                           ->addLink(
                               $controller->getPadURL('iframe', $pad),
                               dgettext('studipad', 'Öffnen'),
                               Icon::create('link-extern')
                           )

                           ->addLink(
                               $controller->getPadURL('settings', $pad, true),
                               dgettext('studipad', 'Einstellungen'),
                               Icon::create('admin'),
                               ['data-dialog' => '']
                           )

                           ->addLink(
                               $controller->getPadURL('snapshot', $pad, true),
                               dgettext('studipad', 'Aktuellen Inhalt sichern'),
                               Icon::create('cloud+export')
                           )

                           ->condition(!$pad->isWriteProtected())
                           ->addLink(
                               $controller->getPadURL('activate_write_protect', $pad, true),
                               dgettext('studipad', 'Schreibschutz aktivieren'),
                               Icon::create('lock-locked')
                           )
                           ->condition($pad->isWriteProtected())
                           ->addLink(
                               $controller->getPadURL('deactivate_write_protect', $pad, true),
                               dgettext('studipad', 'Schreibschutz deaktivieren'),
                               Icon::create('lock-unlocked')
                           )

                           ->condition(!$pad->isPublic())
                           ->addLink(
                               $controller->getPadURL('publish', $pad, true),
                               dgettext('studipad', 'Veröffentlichen'),
                               Icon::create('globe'),
                               ['data-confirm' => dgettext('studipad', 'Wollen Sie das Pad wirklich öffentlich machen?')]
                           )
                           ->condition($pad->isPublic())
                           ->addLink(
                               $controller->getPadURL('unpublish', $pad, true),
                               dgettext('studipad', 'Veröffentlichung beenden'),
                               Icon::create('globe+decline')
                           )

                           ->addLink(
                               $controller->getPadURL('delete', $pad, true),
                               dgettext('studipad', 'Pad löschen'),
                               Icon::create('trash', Icon::ROLE_ATTENTION),
                               ['data-confirm' => dgettext('studipad', 'Wollen Sie das Pad wirklich löschen?')]
                           ) ?>

        <? } ?>
    </td>
</tr>

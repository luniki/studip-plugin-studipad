<?php
/**
 * @var PadsController $controller
 * @var EtherpadPlugin\Group $group
 * @var EtherpadPlugin\Pad $pad
 * @var callable $_
 */
?>
<tr>
    <td>
        <a href="<?= $controller->getPadLink('iframe', $pad) ?>">
            <span class="studipad-pad-name"> <?= htmlReady($pad->getName()) ?> </span>
            <? if ($pad->isNew()) { ?>
                <span class="studipad-badge studipad-bg-attention"><?= $_('neu') ?></span>
            <? } ?>

            <? if ($pad->isWriteProtected()) { ?>
                <span class="studipad-badge studipad-bg-secondary"><?= $_('schreibgeschützt') ?></span>
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
    <? if ($group->canAdmin()): ?>
        <?= ActionMenu::get()
            ->addLink(
                $controller->getPadURL('iframe', $pad),
                $_('Öffnen'),
                Icon::create('link-extern')
            )

            ->addLink(
                $controller->getPadURL('settings', $pad, true),
                $_('Einstellungen'),
                Icon::create('admin'),
                ['data-dialog' => '']
            )

            ->addLink(
                $controller->getPadURL('snapshot', $pad, true),
                $_('Aktuellen Inhalt sichern'),
                Icon::create('cloud+export')
            )

            ->condition(!$pad->isWriteProtected())
            ->addLink(
                $controller->getPadURL('activate_write_protect', $pad, true),
                $_('Schreibschutz aktivieren'),
                Icon::create('lock-locked')
            )
            ->condition($pad->isWriteProtected())
            ->addLink(
                $controller->getPadURL('deactivate_write_protect', $pad, true),
                $_('Schreibschutz deaktivieren'),
                Icon::create('lock-unlocked')
            )

            ->condition(!$pad->isPublic())
            ->addLink(
                $controller->getPadURL('publish', $pad, true),
                $_('Veröffentlichen'),
                Icon::create('globe'),
                ['data-confirm' => $_('Wollen Sie das Pad wirklich öffentlich machen?')]
            )
            ->condition($pad->isPublic())
            ->addLink(
                $controller->getPadURL('unpublish', $pad, true),
                $_('Veröffentlichung beenden'),
                Icon::create('globe+decline')
            )

            ->addLink(
                $controller->getPadURL('delete', $pad, true),
                $_('Pad löschen'),
                Icon::create('trash', Icon::ROLE_ATTENTION),
                ['data-confirm' => $_('Wollen Sie das Pad wirklich löschen?')]
            )
        ?>
    <? endif; ?>
    </td>
</tr>

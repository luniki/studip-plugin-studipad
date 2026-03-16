<?php
/**
 * @var EtherpadPlugin\Group[] $groups
 * @var callable $_
 */
?>
<? if (count($groups)): ?>
    <?= $this->render_partial('pads/list-of-groups') ?>
<? else: ?>
    <?= MessageBox::info(
        $_('In dieser Veranstaltung wurden noch keine Gruppen von Teilnehmenden definiert.')
    ) ?>
<? endif; ?>

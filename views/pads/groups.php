<? if (count($groups)) { ?>
    <?= $this->render_partial('pads/list-of-groups') ?>
<? } else { ?>
    <?= MessageBox::info(
        dgettext('studipad', 'In dieser Veranstaltung wurden noch keine Gruppen von Teilnehmenden definiert.')
    ) ?>
<? } ?>

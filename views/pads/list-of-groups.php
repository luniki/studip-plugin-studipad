<? foreach ($groups as $group) { ?>
    <article class="studip <?= count($groups) === 1 ? '' : 'toggle open' ?>" style="margin-bottom: 3rem;">
        <header>
            <h1>
                <a name="studipad-group-<?= htmlReady($group->getId()) ?>">
                    <?= htmlReady($group->getName()) ?>
                </a>
            </h1>
            <nav>
                <?=
                    \ActionMenu::get()
                               ->condition($group->canAdmin())
                               ->addLink(
                                   $controller->url_for('pads/new', ['range' => $group->getRangeId()]),
                                   dgettext('studipad', 'Pad hinzufügen'),
                                   Icon::create('add'),
                                   ['data-dialog' => 'reload-on-close;size=auto']
                               )
                ?>
            </nav>
        </header>

        <?= $this->render_partial('pads/groups/list', ['group' => $group]) ?>

    </article>
<? } ?>

<?php

use EtherpadLite\Client;
use EtherpadPlugin\Group;
use EtherpadPlugin\Pad;

/**
 * @property ?Pad           $pad
 * @property ?Group         $group
 * @property Group[]        $groups
 * @property EtherpadPlugin $plugin
 */
// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class PadsController extends PluginController
{
    public ?Client $client;

    /**
     * @param string   $action
     * @param string[] $args
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!Context::getId()) {
            throw new AccessDeniedException();
        }

        $this->setPageTitle();

        PageLayout::setHelpKeyword('Basis.EtherpadPlugin');
        PageLayout::setBodyElementId('etherpad-plugin');

        $this->client = $this->plugin->getClient();
        if (!$this->client) {
            $action = 'setuperror';
        }
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function setuperror_action(): void
    {
        if (Navigation::hasItem('/course/studipad/index')) {
            Navigation::activateItem('/course/studipad/index');
        }

        // now just render template
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function index_action(): void
    {
        if (Navigation::hasItem('/course/studipad/index')) {
            Navigation::activateItem('/course/studipad/index');
        }

        $this->groups = $this->getCourseGroups();
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function groups_action(): void
    {
        if (Navigation::hasItem('/course/studipad/groups')) {
            Navigation::activateItem('/course/studipad/groups');
        }

        $this->groups = $this->getStatusgruppenGroups();
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function new_action(): void
    {
        if (Navigation::hasItem('/course/studipad/index')) {
            Navigation::activateItem('/course/studipad/index');
        }

        $rangeId = Request::get('range');
        $this->group = $this->findGroup($rangeId);

        if (!$this->group) {
            PageLayout::postError($this->_('Diese Gruppe gefunden werden.'));
        }
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function open_action(): void
    {
        $this->requirePad();

        $url = $this->redirectToEtherpad($this->pad) . $this->pad->getHtmlControlString();
        $this->redirect($url);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function iframe_action(): void
    {
        $this->requirePad();

        $navItem = $this->group->isStatusgruppenGroup() ? '/course/studipad/groups' : '/course/studipad/index';
        if (Navigation::hasItem($navItem)) {
            Navigation::activateItem($navItem);
        }

        $this->setPageTitle($this->pad);

        if ($this->group->canAdmin()) {
            $this->prepareSidebar($this->pad);
        }
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function settings_action(): void
    {
        $this->requirePad();
        $this->requireGroupAdmin();
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function store_settings_action(): void
    {
        $this->requirePad();
        $this->requireGroupAdmin();

        $controls = [];
        foreach (Pad::getControlsKeys() as $key) {
            $controls[$key] = Request::get($key) ? 1 : 0;
        }
        $this->pad->setControls($controls);

        PageLayout::postInfo($this->_('Einstellungen gespeichert.'));
        $this->redirectBack();
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function activate_write_protect_action(): void
    {
        $this->requirePad();
        $this->requireGroupAdmin();

        $this->pad->setWriteProtection(true);
        PageLayout::postInfo($this->_('Schreibschutz aktiviert.'));

        $this->redirectBack();
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function deactivate_write_protect_action(): void
    {
        $this->requirePad();
        $this->requireGroupAdmin();

        $this->pad->setWriteProtection(false);
        PageLayout::postInfo($this->_('Schreibschutz deaktiviert.'));

        $this->redirectBack();
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function publish_action(): void
    {
        $this->requirePad();
        $this->requireGroupAdmin();

        try {
            $this->pad->setPublic(true);
            PageLayout::postInfo($this->_('Veröffentlicht.'));
        } catch (Exception $e) {
            PageLayout::postError($this->_('Pad konnte nicht veröffentlicht werden.'));
        }

        $this->redirectBack();
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function unpublish_action(): void
    {
        $this->requirePad();
        $this->requireGroupAdmin();

        try {
            $this->pad->setPublic(false);
            PageLayout::postInfo($this->_('Veröffentlichung aufgehoben.'));
        } catch (Exception $e) {
            PageLayout::postError($this->_('Veröffentlichung des Pads konnte nicht aufgehoben werden.'));
        }

        $this->redirectBack();
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_action(): void
    {
        // find group
        $rangeId = Request::get('range');
        $this->group = $this->findGroup($rangeId);

        $this->requireGroupAdmin();

        // validate pad name
        $name = trim(Request::get('new_pad_name', ''));
        if ('' === $name || mb_strlen($name) > 32) {
            PageLayout::postError(
                $this->_('Es muss ein Name angegeben werden der aus maximal 32 Zeichen besteht.')
            );
        } elseif (!preg_match('/^[A-Za-z0-9_-]+$/', $name)) {
            PageLayout::postError(
                $this->_(
                    'Namen neuer Pads dürfen nur aus Buchstaben, Zahlen, Binde- und Unterstrichen bestehen.'
                )
            );
        } else {
            // create pad
            try {
                $this->group->createPad($name);
                PageLayout::postInfo($this->_('Das Pad wurde erfolgreich angelegt.'));
                $this->plugin->expireLastEditCache();
            } catch (InvalidArgumentException $e) {
                PageLayout::postInfo($this->_('Dieses Pad ist bereits angelegt.'));
            } catch (Exception $e) {
                PageLayout::postError($this->_('Das Pad konnte nicht angelegt werden.'));
            }
        }

        $this->redirect($this->group->isStatusgruppenGroup() ? 'pads/groups' : 'pads/index');
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function delete_action(): void
    {
        $this->requirePad();
        $this->requireGroupAdmin();

        try {
            $this->pad->delete();
            PageLayout::postInfo($this->_('Das Pad wurde gelöscht.'));
            $this->plugin->expireLastEditCache();
        } catch (Exception $e) {
            PageLayout::postError($this->_('Das Pad konnte nicht gelöscht werden.'));
        }

        $this->redirect($this->group->isStatusgruppenGroup() ? 'pads/groups' : 'pads/index');
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function snapshot_action(): void
    {
        $this->requirePad();
        $this->requireGroupAdmin();

        $fileRef = $this->storeSnapshot();
        if (!$fileRef) {
            // Fehler werden schon beim Methodenaufruf über PageLayout::postError notiert.
            $this->redirect($this->group->isStatusgruppenGroup() ? 'pads/groups' : 'pads/index');
            return;
        }

        $url = URLHelper::getLink(
            sprintf('dispatch.php/course/files/index/%s#fileref_%s', $fileRef['folder_id'], $fileRef->getId()),
            ['cid' => Context::getId()],
            true
        );

        PageLayout::postInfo(
            sprintf(
                dgettext(
                    'studipad',
                    'Der aktuelle Inhalt des Etherpad-Dokuments wurde <a href="%s">im Dateibereich</a> gesichert.'
                ),
                $url
            )
        );

        $this->redirectBack();
    }

    // #########################################################################

    /**
     * @param ?string $suffix
     *
     * @return string
     *
     * @throws Trails_Exception
     */
    protected function requireGroup($suffix = null)
    {
        $cid = Context::getId();
        $groupId = 'subdomain:' . $cid . ($suffix ? '/' . $suffix : '');
        try {
            $eplGmap = $this->client->createGroupIfNotExistsFor('subdomain:' . $groupId);
        } catch (Exception $e) {
            throw new Trails_Exception(500, $e->getMessage());
        }

        $eplGroupId = $eplGmap->groupID;
        if (!$eplGroupId) {
            throw new Trails_Exception(500, $this->_('Es ist ein Verbindungsfehler aufgetreten!'));
        }

        return $eplGroupId;
    }

    protected function redirectToEtherpad(Pad $pad): string
    {
        $user = User::findCurrent();
        $author = $this->client->createAuthorIfNotExistsFor($user->id, $user->getFullName());
        $authorID = $author->authorID;

        $until = strtotime('tomorrow');
        $eplSid = $this->client->createSession($pad->getGroup()->getId(), $authorID, $until);

        return sprintf(
            '%s/auth_session?sessionID=%s&padName=%s',
            dirname(rtrim(Config::get()->getValue('STUDIPAD_PADBASEURL'), '/')),
            $eplSid->sessionID,
            $pad->getPadCallId()
        );
    }

    protected function storeSnapshot(): ?FileRef
    {
        $user = User::findCurrent();
        $course = Context::get();

        try {
            $html = $this->pad->getHTML();

            return $this->saveAsPDF($user, $course, $this->pad, $html);
        } catch (Exception $ex) {
            PageLayout::postError($ex->getMessage());

            return null;
        }
    }

    /**
     * @throws RuntimeException
     */
    private function saveAsPDF(User $user, Course $course, Pad $pad, string $html): FileRef
    {
        /** @var ?Folder $folder */
        $folder = Folder::findTopFolder($course->getId());
        if (!$folder) {
            throw new RuntimeException('Could not find top folder.');
        }

        $filename = FileManager::cleanFileName(sprintf('%s.%s.pdf', $pad->getName(), date('Y-m-d-H-m-s')));
        /** @var ?File $file */
        $file = File::create([
            'user_id' => $user->getId(),
            'mime_type' => 'application/pdf',
            'name' => $filename,
            'storage' => 'disk',
        ]);
        if (!$file) {
            throw new RuntimeException('Could not store file.');
        }

        /** @var ?FileRef $fileRef */
        $fileRef = FileRef::create([
            'file_id' => $file->getId(),
            'folder_id' => $folder->getId(),
            'user_id' => $user->getId(),
            'name' => $file['name'],
        ]);
        if (!$fileRef) {
            throw new RuntimeException('Could not store file ref.');
        }

        $tmp = $this->createPDF($course, $pad, $html);
        $stored = $file->connectWithDataFile($tmp);
        @unlink($tmp);
        if (!$stored) {
            throw new RuntimeException('Could not store PDF.');
        }

        $file->size = filesize($file->getPath());
        $file->store();

        return $fileRef;
    }

    /**
     * @return string|false
     */
    private function createPDF(Course $course, Pad $pad, string $html): bool|string
    {
        $doc = new ExportPDF();
        $doc->setHeaderTitle('Etherpad-Dokument: ' . $pad->getName());
        $doc->setHeaderSubtitle($GLOBALS['UNI_NAME_CLEAN'] . ' » ' . $course->getFullname() . ' » Pad');
        $doc->addPage();
        $doc->writeHTML($html);

        $tmpPath = tempnam($GLOBALS['TMP_PATH'], 'studipad');
        $doc->Output($tmpPath, 'F');

        return $tmpPath;
    }

    /**
     * @return Group[]
     */
    private function getCourseGroups(): iterable
    {
        return [new Group($this->client, Context::get())];
    }

    /**
     * @return Group[]
     */
    private function getStatusgruppenGroups(): iterable
    {
        $groups = [];
        foreach ($this->getStatusgruppen() as $statusgruppe) {
            $groups[] = new Group($this->client, $statusgruppe);
        }

        return $groups;
    }

    /**
     * @return Statusgruppen[]
     */
    private function getStatusgruppen(): iterable
    {
        $cid = Context::getId();
        $isTutor = $GLOBALS['perm']->have_studip_perm('tutor', $cid);
        $groups = Statusgruppen::findBySeminar_id($cid);

        return $isTutor
            ? $groups
            : array_filter($groups, function ($group) {
                return $group->isMember();
            });
    }

    /**
     * @param string $rangeId
     *
     * @return ?Group
     */
    private function findGroup($rangeId): ?Group
    {
        if ($rangeId === Context::getId()) {
            return new Group($this->client, Context::get());
        } else {
            $statusgruppen = $this->getStatusgruppen();
            foreach ($statusgruppen as $gruppe) {
                if ($rangeId === $gruppe->getId()) {
                    return new Group($this->client, $gruppe);
                }
            }
        }

        return null;
    }

    private function prepareSidebar(Pad $pad): void
    {
        $sidebar = Sidebar::get();

        $actions = $sidebar->hasWidget('actions') ? $sidebar->getWidget('actions') : new ActionsWidget();

        if (!$sidebar->hasWidget('actions')) {
            $sidebar->addWidget($actions);
        }

        if ($pad->isPublic()) {
            $publicURL = $pad->getPublicURL();
            $urlWidget = new ActionsWidget();
            $urlWidget->setTitle($this->_('Öffentliche URL'));
            $urlWidget->addLink($publicURL, $publicURL, Icon::create('globe+move_right'));
            $sidebar->addWidget($urlWidget);
        }

        $actions->addLink($this->_('Einstellungen'), $this->getPadURL('settings'), Icon::create('admin'), [
            'data-dialog' => '',
        ]);

        $actions->addLink(
            $this->_('Aktuellen Inhalt sichern'),
            $this->getPadURL('snapshot'),
            Icon::create('cloud+export')
        );

        if (!$pad->isWriteProtected()) {
            $actions->addLink(
                $this->_('Schreibschutz aktivieren'),
                $this->getPadURL('activate_write_protect'),
                Icon::create('lock-locked')
            );
        } else {
            $actions->addLink(
                $this->_('Schreibschutz deaktivieren'),
                $this->getPadURL('deactivate_write_protect'),
                Icon::create('lock-unlocked')
            );
        }

        if (!$pad->isPublic()) {
            $actions->addLink(
                $this->_('Veröffentlichen'),
                $this->getPadURL('publish'),
                Icon::create('globe'),
                ['data-confirm' => $this->_('Wollen Sie das Pad wirklich öffentlich machen?')]
            );
        } else {
            $actions->addLink(
                $this->_('Veröffentlichung beenden'),
                $this->getPadURL('unpublish'),
                Icon::create('globe+decline')
            );
        }

        $actions->addLink(
            $this->_('Pad löschen'),
            $this->getPadURL('delete'),
            Icon::create('trash', Icon::ROLE_ATTENTION),
            ['data-confirm' => $this->_('Wollen Sie das Pad wirklich löschen?')]
        );
    }

    private function setPageTitle(?Pad $pad = null): void
    {
        if ($pad) {
            if ($pad->isWriteProtected()) {
                $title = $this->_('%1$s - Etherpad: %2$s (schreibgeschützt)');
            } else {
                $title = $this->_('%1$s - Etherpad: %2$s');
            }
        } else {
            $title = $this->_('%1$s - Etherpad');
        }

        PageLayout::setTitle(sprintf($title, Context::getHeaderLine(), $pad ? $pad->getName() : ''));
    }

    /**
     * @throws Trails_Exception
     */
    private function forceErrorRedirect(string $reason): never
    {
        PageLayout::postError($reason);
        throw new Trails_Exception(302, $reason, ['Location' => $this->url_for('')]);
    }

    /**
     * @throws Trails_Exception
     */
    private function requirePad(): void
    {
        $padName = Request::get('pad');
        if (!Pad::validateName($padName)) {
            $this->forceErrorRedirect($this->_('Ungültiger Pad-Name.'));
        }

        $rangeId = Request::get('range');
        $this->group = $this->findGroup($rangeId);
        if (!$this->group) {
            $this->forceErrorRedirect($this->_('Ungültige Gruppe.'));
        }

        $this->pad = $this->group->getPad($padName);
        if (!$this->pad) {
            $this->forceErrorRedirect($this->_('Dieses Pad konnte nicht gefunden werden.'));
        }
    }

    /**
     * @return array<string, string|int>
     */
    private function getPadURLParameters(Pad $pad = null, bool $backToList = false): array
    {
        if ($pad) {
            $padURLParameters = ['pad' => $pad->getName(), 'range' => $pad->getGroup()->getRangeId()];
        } else {
            if (!$this->pad || !$this->group) {
                throw new RuntimeException('Calling #getPadURLParameters without proper context.');
            }

            $padURLParameters = ['pad' => $this->pad->getName(), 'range' => $this->group->getRangeId()];
        }

        if ($backToList) {
            $padURLParameters['list'] = 1;
        }

        return $padURLParameters;
    }

    private function redirectBack(): void
    {
        $backToList = Request::submitted('list');

        if ($backToList) {
            $this->redirect($this->group && $this->group->isStatusgruppenGroup() ? 'pads/groups' : 'pads/index');
        } else {
            $this->redirect($this->getPadURL('iframe'));
        }
    }

    /**
     * @throws Trails_Exception if `$this->group` is not set yet
     * @throws AccessDeniedException if the current user is not a group admin
     */
    private function requireGroupAdmin(): void
    {
        if (!$this->group) {
            throw new Trails_Exception(500);
        }

        if (!$this->group->canAdmin()) {
            throw new AccessDeniedException();
        }
    }

    ////////////////////////
    // Controller helpers //
    ////////////////////////

    public function getPadLink(string $action, Pad $pad = null, bool $backToList = false): string
    {
        return $this->link_for('pads/' . $action, $this->getPadURLParameters($pad, $backToList));
    }

    public function getPadURL(string $action, Pad $pad = null, bool $backToList = false): string
    {
        return $this->url_for('pads/' . $action, $this->getPadURLParameters($pad, $backToList));
    }
}

<?php

use EtherpadPlugin\Group;
use EtherpadPlugin\Pad;

/**
 * @property ?Pad          $pad
 * @property ?Group        $group
 * @property array<Group> $groups
 */
// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class PadsController extends StudipController
{
    /** @var \EtherpadPlugin */
    public $plugin;

    /** @var ?\EtherpadLite\Client */
    public $client;

    public function __construct(Trails_Dispatcher $dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
    }

    /**
     * @param string   $action
     * @param string[] $args
     *
     * @return void|bool
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!\Context::getId()) {
            throw new \AccessDeniedException();
        }

        $this->set_layout($GLOBALS['template_factory']->open(\Request::isXhr() ? 'layouts/dialog' : 'layouts/base'));
        $this->setPageTitle();
        \PageLayout::setHelpKeyword('Basis.EtherpadPlugin');
        \PageLayout::addStylesheet($this->plugin->getPluginURL() . '/stylesheets/studipad.css');
        \PageLayout::setBodyElementId('etherpad-plugin');

        if (!($this->client = $this->plugin->getClient())) {
            $action = 'setuperror';
        }
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function setuperror_action()
    {
        if (\Navigation::hasItem('/course/studipad/index')) {
            \Navigation::activateItem('/course/studipad/index');
        }

        // now just render template
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function index_action()
    {
        if (\Navigation::hasItem('/course/studipad/index')) {
            \Navigation::activateItem('/course/studipad/index');
        }

        $this->groups = $this->getCourseGroups();
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function groups_action()
    {
        if (\Navigation::hasItem('/course/studipad/groups')) {
            \Navigation::activateItem('/course/studipad/groups');
        }

        $this->groups = $this->getStatusgruppenGroups();
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function new_action()
    {
        if (\Navigation::hasItem('/course/studipad/index')) {
            \Navigation::activateItem('/course/studipad/index');
        }

        $rangeId = \Request::get('range');
        $this->group = $this->findGroup($rangeId);

        if (!$this->group) {
            PageLayout::postError(dgettext('studipad', 'Diese Gruppe gefunden werden.'));
        }
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function open_action()
    {
        $this->requirePad();

        $url = $this->redirectToEtherpad($this->pad) . $this->pad->getHtmlControlString();
        $this->redirect($url);
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function iframe_action()
    {
        $this->requirePad();

        $navItem = $this->group->isStatusgruppenGroup() ? '/course/studipad/groups' : '/course/studipad/index';
        if (\Navigation::hasItem($navItem)) {
            \Navigation::activateItem($navItem);
        }

        $this->setPageTitle($this->pad);

        if ($this->group->canAdmin()) {
            $this->prepareSidebar($this->pad);
        }
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function settings_action()
    {
        $this->requirePad();
        $this->requireGroupAdmin();
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function store_settings_action()
    {
        $this->requirePad();
        $this->requireGroupAdmin();

        $controls = [];
        foreach (Pad::getControlsKeys() as $key) {
            $controls[$key] = \Request::get($key) ? 1 : 0;
        }
        $this->pad->setControls($controls);

        PageLayout::postInfo(dgettext('studipad', 'Einstellungen gespeichert.'));
        $this->redirectBack();
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function activate_write_protect_action()
    {
        $this->requirePad();
        $this->requireGroupAdmin();

        $this->pad->setWriteProtection(true);
        PageLayout::postInfo(dgettext('studipad', 'Schreibschutz aktiviert.'));

        $this->redirectBack();
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function deactivate_write_protect_action()
    {
        $this->requirePad();
        $this->requireGroupAdmin();

        $this->pad->setWriteProtection(false);
        PageLayout::postInfo(dgettext('studipad', 'Schreibschutz deaktiviert.'));

        $this->redirectBack();
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function publish_action()
    {
        $this->requirePad();
        $this->requireGroupAdmin();

        try {
            $this->pad->setPublic(true);
            PageLayout::postInfo(dgettext('studipad', 'Veröffentlicht.'));
        } catch (Exception $e) {
            PageLayout::postError(dgettext('studipad', 'Pad konnte nicht veröffentlicht werden.'));
        }

        $this->redirectBack();
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function unpublish_action()
    {
        $this->requirePad();
        $this->requireGroupAdmin();

        try {
            $this->pad->setPublic(false);
            PageLayout::postInfo(dgettext('studipad', 'Veröffentlichung aufgehoben.'));
        } catch (Exception $e) {
            PageLayout::postError(dgettext('studipad', 'Veröffentlichung des Pads konnte nicht aufgehoben werden.'));
        }

        $this->redirectBack();
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_action()
    {
        // find group
        $rangeId = \Request::get('range');
        $this->group = $this->findGroup($rangeId);

        $this->requireGroupAdmin();

        // validate pad name
        $name = trim(\Request::get('new_pad_name', ''));
        if ('' === $name || mb_strlen($name) > 32) {
            PageLayout::postError(
                dgettext('studipad', 'Es muss ein Name angegeben werden der aus maximal 32 Zeichen besteht.')
            );
        } elseif (!preg_match('/^[A-Za-z0-9_-]+$/', $name)) {
            PageLayout::postError(
                dgettext(
                    'studipad',
                    'Namen neuer Pads dürfen nur aus Buchstaben, Zahlen, Binde- und Unterstrichen bestehen.'
                )
            );
        } else {
            // create pad
            try {
                $this->group->createPad($name);
                PageLayout::postInfo(dgettext('studipad', 'Das Pad wurde erfolgreich angelegt.'));
                $this->plugin->expireLastEditCache();
            } catch (\InvalidArgumentException $e) {
                PageLayout::postInfo(dgettext('studipad', 'Dieses Pad ist bereits angelegt.'));
            } catch (\Exception $e) {
                PageLayout::postError(dgettext('studipad', 'Das Pad konnte nicht angelegt werden.'));
            }
        }

        $this->redirect($this->group->isStatusgruppenGroup() ? 'pads/groups' : 'pads/index');
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function delete_action()
    {
        $this->requirePad();
        $this->requireGroupAdmin();

        try {
            $this->pad->delete();
            PageLayout::postInfo(dgettext('studipad', 'Das Pad wurde gelöscht.'));
            $this->plugin->expireLastEditCache();
        } catch (Exception $e) {
            PageLayout::postError(dgettext('studipad', 'Das Pad konnte nicht gelöscht werden.'));
        }

        $this->redirect($this->group->isStatusgruppenGroup() ? 'pads/groups' : 'pads/index');
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function snapshot_action()
    {
        $this->requirePad();
        $this->requireGroupAdmin();

        $fileRef = $this->storeSnapshot();
        if (!$fileRef) {
            // Fehler werden schon beim Methodenaufruf über PageLayout::postError notiert.
            $this->redirect($this->group->isStatusgruppenGroup() ? 'pads/groups' : 'pads/index');
        }

        $url = \URLHelper::getLink(
            sprintf('dispatch.php/course/files/index/%s#fileref_%s', $fileRef['folder_id'], $fileRef->getId()),
            ['cid' => \Context::getId()],
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
     * @param string $to
     * @return string
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function url_for($to = '')
    {
        $args = func_get_args();

        // find params
        $params = [];
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        // urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return \PluginEngine::getURL($this->dispatcher->plugin, $params, implode('/', $args));
    }

    /**
     * @param ?string $suffix
     *
     * @return string
     *
     * @throws \Trails_Exception
     */
    protected function requireGroup($suffix = null)
    {
        $cid = \Context::getId();
        $groupId = 'subdomain:' . $cid . ($suffix ? '/' . $suffix : '');
        try {
            $eplGmap = $this->client->createGroupIfNotExistsFor('subdomain:' . $groupId);
        } catch (\Exception $e) {
            throw new \Trails_Exception(500, $e->getMessage());
        }

        if (!($eplGroupId = $eplGmap->groupID)) {
            throw new \Trails_Exception(500, dgettext('studipad', 'Es ist ein Verbindungsfehler aufgetreten!'));
        }

        return $eplGroupId;
    }

    /**
     * @param Pad $pad
     * @return string
     */
    protected function redirectToEtherpad(Pad $pad)
    {
        $user = \User::findCurrent();
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

    /**
     * @return ?\FileRef
     */
    protected function storeSnapshot()
    {
        $user = \User::findCurrent();
        $course = \Context::get();

        try {
            $html = $this->pad->getHTML();

            return $this->saveAsPDF($user, $course, $this->pad, $html);
        } catch (Exception $ex) {
            PageLayout::postError($ex->getMessage());

            return null;
        }
    }

    /**
     * @param string $html
     * @return \FileRef
     *
     * @throws \RuntimeException
     */
    private function saveAsPDF(User $user, Course $course, Pad $pad, $html)
    {
        /** @var ?\Folder $folder */
        $folder = \Folder::findTopFolder($course->getId());
        if (!$folder) {
            throw new \RuntimeException('Could not find top folder.');
        }

        $filename = \FileManager::cleanFileName(sprintf('%s.%s.pdf', $pad->getName(), date('Y-m-d-H-m-s')));
        /** @var ?\File $file */
        $file = \File::create([
            'user_id' => $user->getId(),
            'mime_type' => 'application/pdf',
            'name' => $filename,
            'storage' => 'disk',
        ]);
        if (!$file) {
            throw new \RuntimeException('Could not store file.');
        }

        /** @var ?\FileRef $fileRef */
        $fileRef = \FileRef::create([
            'file_id' => $file->getId(),
            'folder_id' => $folder->getId(),
            'user_id' => $user->getId(),
            'name' => $file['name'],
        ]);
        if (!$fileRef) {
            throw new \RuntimeException('Could not store file ref.');
        }

        $tmp = $this->createPDF($course, $pad, $html);
        $stored = $file->connectWithDataFile($tmp);
        @unlink($tmp);
        if (!$stored) {
            throw new \RuntimeException('Could not store PDF.');
        }

        $file->size = filesize($file->getPath());
        $file->store();

        return $fileRef;
    }

    /**
     * @param Course $course
     * @param Pad $pad
     * @param string $html
     * @return string|false
     */
    private function createPDF(Course $course, Pad $pad, $html)
    {
        $doc = new \ExportPDF();
        $doc->setHeaderTitle('Etherpad-Dokument: ' . $pad->getName());
        $doc->setHeaderSubtitle($GLOBALS['UNI_NAME_CLEAN'] . ' » ' . $course->getFullname() . ' » Pad');
        $doc->addPage();
        $doc->writeHTML($html);

        $tmpPath = tempnam($GLOBALS['TMP_PATH'], 'studipad');
        $doc->Output($tmpPath, 'F');

        return $tmpPath;
    }

    /**
     * @return \EtherpadPlugin\Group[]
     */
    private function getCourseGroups(): iterable
    {
        $groups = [new Group($this->client, \Context::get())];

        return $groups;
    }

    /**
     * @return \EtherpadPlugin\Group[]
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
     * @return \Statusgruppen[]
     */
    private function getStatusgruppen()
    {
        $cid = \Context::getId();
        $isTutor = $GLOBALS['perm']->have_studip_perm('tutor', $cid);
        $groups = \Statusgruppen::findBySeminar_id($cid);

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
    private function findGroup($rangeId)
    {
        if ($rangeId === \Context::getId()) {
            return new Group($this->client, \Context::get());
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

    /**
     * @return void
     */
    private function prepareSidebar(Pad $pad)
    {
        $sidebar = \Sidebar::get();

        $actions = $sidebar->hasWidget('actions') ? $sidebar->getWidget('actions') : new \ActionsWidget();

        if (!$sidebar->hasWidget('actions')) {
            $sidebar->addWidget($actions);
        }

        if ($pad->isPublic()) {
            $publicURL = $pad->getPublicURL();
            $urlWidget = new \ActionsWidget();
            $urlWidget->setTitle(dgettext('studipad', 'Öffentliche URL'));
            $urlWidget->addLink($publicURL, $publicURL, \Icon::create('globe+move_right'));
            $sidebar->addWidget($urlWidget);
        }

        $actions->addLink(dgettext('studipad', 'Einstellungen'), $this->getPadURL('settings'), Icon::create('admin'), [
            'data-dialog' => '',
        ]);

        $actions->addLink(
            dgettext('studipad', 'Aktuellen Inhalt sichern'),
            $this->getPadURL('snapshot'),
            Icon::create('cloud+export')
        );

        if (!$pad->isWriteProtected()) {
            $actions->addLink(
                dgettext('studipad', 'Schreibschutz aktivieren'),
                $this->getPadURL('activate_write_protect'),
                Icon::create('lock-locked')
            );
        } else {
            $actions->addLink(
                dgettext('studipad', 'Schreibschutz deaktivieren'),
                $this->getPadURL('deactivate_write_protect'),
                Icon::create('lock-unlocked')
            );
        }

        if (!$pad->isPublic()) {
            $actions->addLink(
                dgettext('studipad', 'Veröffentlichen'),
                $this->getPadURL('publish'),
                Icon::create('globe'),
                ['data-confirm' => dgettext('studipad', 'Wollen Sie das Pad wirklich öffentlich machen?')]
            );
        } else {
            $actions->addLink(
                dgettext('studipad', 'Veröffentlichung beenden'),
                $this->getPadURL('unpublish'),
                Icon::create('globe+decline')
            );
        }

        $actions->addLink(
            dgettext('studipad', 'Pad löschen'),
            $this->getPadURL('delete'),
            Icon::create('trash', Icon::ROLE_ATTENTION),
            ['data-confirm' => dgettext('studipad', 'Wollen Sie das Pad wirklich löschen?')]
        );
    }

    /**
     * @param ?Pad $pad
     *
     * @return void
     */
    private function setPageTitle(Pad $pad = null)
    {
        if ($pad) {
            if ($pad->isWriteProtected()) {
                $title = dgettext('studipad', '%1$s - Etherpad: %2$s (schreibgeschützt)');
            } else {
                $title = dgettext('studipad', '%1$s - Etherpad: %2$s');
            }
        } else {
            $title = dgettext('studipad', '%1$s - Etherpad');
        }

        PageLayout::setTitle(sprintf($title, \Context::getHeaderLine(), $pad ? $pad->getName() : ''));
    }

    /**
     * @throws \Trails_Exception
     * @param string $reason
     * @return never
     */
    private function forceErrorRedirect($reason)
    {
        PageLayout::postError($reason);
        throw new \Trails_Exception(302, $reason, ['Location' => $this->url_for('')]);
    }

    /**
     * @return void
     * @throws \Trails_Exception
     */
    private function requirePad()
    {
        $padName = \Request::get('pad');
        if (!Pad::validateName($padName)) {
            $this->forceErrorRedirect(dgettext('studipad', 'Ungültiger Pad-Name.'));
        }

        $rangeId = \Request::get('range');
        $this->group = $this->findGroup($rangeId);
        if (!$this->group) {
            $this->forceErrorRedirect(dgettext('studipad', 'Ungültige Gruppe.'));
        }

        $this->pad = $this->group->getPad($padName);
        if (!$this->pad) {
            $this->forceErrorRedirect(dgettext('studipad', 'Dieses Pad konnte nicht gefunden werden.'));
        }
    }

    /**
     * @param bool $backToList
     * @return array<string, string|int>
     */
    private function getPadURLParameters(Pad $pad = null, $backToList = false)
    {
        if ($pad) {
            $padURLParameters = ['pad' => $pad->getName(), 'range' => $pad->getGroup()->getRangeId()];
        } else {
            if (!$this->pad || !$this->group) {
                throw new \RuntimeException('Calling #getPadURLParameters without proper context.');
            }

            $padURLParameters = ['pad' => $this->pad->getName(), 'range' => $this->group->getRangeId()];
        }

        if ($backToList) {
            $padURLParameters['list'] = 1;
        }

        return $padURLParameters;
    }

    /**
     * @return void
     */
    private function redirectBack()
    {
        $backToList = \Request::submitted('list');

        if ($backToList) {
            $this->redirect($this->group && $this->group->isStatusgruppenGroup() ? 'pads/groups' : 'pads/index');
        } else {
            $this->redirect($this->getPadURL('iframe'));
        }
    }

    /**
     * @throws \Trails_Exception if `$this->group` is not set yet
     * @return void
     */
    private function requireGroupAdmin()
    {
        if (!$this->group) {
            throw new \Trails_Exception(500);
        }

        if (!$this->group->canAdmin()) {
            throw new \AccessDeniedException();
        }
    }

    ////////////////////////
    // Controller helpers //
    ////////////////////////

    /**
     * @param string $action
     * @param ?Pad $pad
     * @param bool $backToList
     * @return string
     */
    public function getPadLink($action, Pad $pad = null, $backToList = false)
    {
        return $this->link_for('pads/' . $action, $this->getPadURLParameters($pad, $backToList));
    }

    /**
     * @param string $action
     * @param ?Pad $pad
     * @param bool $backToList
     * @return string
     */
    public function getPadURL($action, Pad $pad = null, $backToList = false)
    {
        return $this->url_for('pads/' . $action, $this->getPadURLParameters($pad, $backToList));
    }
}

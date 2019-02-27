<?php

class PadsController extends StudipController
{
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!\Context::getId()) {
            throw new \AccessDeniedException();
        }

        $this->set_layout(
            $GLOBALS['template_factory']->open(\Request::isXhr() ? 'layouts/dialog' : 'layouts/base')
        );
        $this->setDefaultPageTitle();

        if (!$this->client = $this->plugin->getClient()) {
            $action = 'setuperror';
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function index_action()
    {
        \PageLayout::setHelpKeyword('Basis.StudiPad');
        if (\Navigation::hasItem('/course/studipad/index')) {
            \Navigation::activateItem('/course/studipad/index');
        }
        \PageLayout::addStylesheet($this->plugin->getPluginURL().'/stylesheets/studipad.css');

        $cid = \Context::getId();
        $this->newPadName = '';
        $this->padadmin = $GLOBALS['perm']->have_studip_perm('tutor', $cid);

        $eplGroupId = $this->requireGroup();

        try {
            $grouppads = $this->client->listPads($eplGroupId);
            $pads = $grouppads->padIDs;

            if (!count($pads)) {
                $this->message = dgettext(
                    'studipad',
                    'Zur Zeit sind keine Stud.IPads für diese Veranstaltung vorhanden.'
                );
            }

            $this->tpads = $this->getPads($cid, $eplGroupId, $pads);
        } catch (Exception $ex) {
            $this->error = '7:'.$ex->getMessage();
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function setuperror_action()
    {
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function export_pdf_action($pad)
    {
        $exportFn = function ($padCallId) {
            return \Config::get()->getValue('STUDIPAD_PADBASEURL').'/'.$padCallId.'/export/pdf';
        };
        $this->redirectToEtherpad($pad, $exportFn);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function open_action($pad)
    {
        $eplGroupId = $this->requireGroup();
        $padCallId = $eplGroupId.'$'.$pad;
        $url = $this->redirectToEtherpad($pad).$this->getHtmlControlString($padCallId);
        $this->redirect($url);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function iframe_action($padid)
    {
        try {
            $cid = \Context::getId();
            $pad = $this->getPad($cid, $padid);
        } catch (\Exception $e) {
            \PageLayout::postError($e->getMessage());

            return $this->redirect('');
        }

        if (!isset($pad)) {
            \PageLayout::postError(dgettext('studipad', 'Dieses Pad konnte nicht gefunden werden.'));

            return $this->redirect('');
        }

        $this->padid = $padid;
        $this->pad = $pad;

        if (\Navigation::hasItem('/course/studipad')) {
            \Navigation::activateItem('/course/studipad');
        }

        $title = \Context::getHeaderLine().' - Pad: '.$padid;
        if ($pad['readOnly']) {
            $title .= ' ('.dgettext('studipad', 'schreibgeschützt').')';
        }

        \PageLayout::setTitle($title);

        if ($GLOBALS['perm']->have_studip_perm('tutor', $cid)) {

            $sidebar = \Sidebar::get();

            $actions = $sidebar->hasWidget('actions')
                     ? $sidebar->getWidget('actions')
                     : new \ActionsWidget();

            if (!$sidebar->hasWidget('actions')) {
                $sidebar->addWidget($actions);
            }

            if ($pad['public']) {
                $urlWidget = new \ActionsWidget();
                $urlWidget->setTitle('Öffentliche URL');
                $urlWidget->addLink(
                    $pad['publicUrl'],
                    $pad['publicUrl'],
                    \Icon::create('globe+move_right')
                );
                $sidebar->addWidget($urlWidget);
            }

            $actions->addLink(
                dgettext('studipad', 'Einstellungen'),
                $this->url_for('pads/settings', $padid, ['page'=>1]),
                Icon::create('admin'),
                ['data-dialog' => '']
            );

            if (!$pad['readOnly']) {
                $actions->addLink(
                    dgettext('studipad', 'Aktuellen Inhalt sichern'),
                    $this->url_for('pads/snapshot', $padid, ['page'=>1]),
                    Icon::create('cloud+export')
                );

                $actions->addLink(
                    dgettext('studipad', 'Schreibschutz aktivieren'),
                    $this->url_for('pads/activate_write_protect', $padid, ['page'=>1]),
                    Icon::create('lock-locked')
                );
            } else {
                $actions->addLink(
                    dgettext('studipad', 'Schreibschutz deaktivieren'),
                    $this->url_for('pads/deactivate_write_protect', $padid, ['page'=>1]),
                    Icon::create('lock-unlocked')
                );
            }

            if (!$pad['hasPassword']) {
                $actions->addLink(
                    dgettext('studipad', 'Passwort festlegen'),
                    $this->url_for('pads/add_password', $padid, ['page'=>1]),
                    Icon::create('key+add'),
                    ['data-dialog' => '']
                );
            } else {
                $actions->addLink(
                    dgettext('studipad', 'Passwort löschen'),
                    $this->url_for('pads/remove_password', $padid, ['page'=>1]),
                    Icon::create('key+remove'),
                    ['data-confirm' => dgettext('studipad', 'Wollen Sie das Passwort wirklich löschen?')]
                );
            }

            if (!$pad['public']) {
                $actions->addLink(
                    dgettext('studipad', 'Veröffentlichen'),
                    $this->url_for('pads/publish', $padid, ['page'=>1]),
                    Icon::create('globe'),
                    ['data-confirm' => dgettext('studipad', 'Wollen Sie das Pad wirklich öffentlich machen?')]
                );
            } else {
                $actions->addLink(
                    dgettext('studipad', 'Veröffentlichung beenden'),
                    $this->url_for('pads/unpublish', $padid, ['page'=>1]),
                    Icon::create('globe+decline')
                );
            }

            $actions->addLink(
                dgettext('studipad', 'Pad löschen'),
                $this->url_for('pads/delete', $padid, ['page'=>1]),
                Icon::create('trash', Icon::ROLE_ATTENTION),
                ['data-confirm' => dgettext('studipad', 'Wollen Sie das Pad wirklich löschen?')]
            );
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function settings_action($padid)
    {
        $this->requireTutor();

        try {
            $pad = $this->getPad(\Context::getId(), $padid);
        } catch (\Exception $e) {
            \PageLayout::postError($e->getMessage());

            return $this->redirect('');
        }

        if (!isset($pad)) {
            \PageLayout::postError(dgettext('studipad', 'Dieses Pad konnte nicht gefunden werden.'));

            return $this->redirect('');
        }

        $this->padid = $padid;
        $this->pad = $pad;

        $this->toPage = \Request::submitted('page');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function store_settings_action($pad)
    {
        $this->requireTutor();
        $eplGroupId = $this->requireGroup();
        $padid = $eplGroupId.'$'.$pad;

        $controls = [];
        foreach (self::getControlsKeys() as $key) {
            $controls[$key] = \Request::get($key) ? 1 : 0;
        }
        $this->setControls($padid, $controls);
        \PageLayout::postInfo(dgettext('studipad', 'Einstellungen gespeichert.'));

        $this->redirect(\Request::submitted('page') ? 'pads/iframe/'.$pad  : '');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function add_password_action($padid)
    {
        $this->requireTutor();
        $this->padid = $padid;
        $this->toPage = \Request::submitted('page');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function store_password_action($padid)
    {
        $this->requireTutor();
        $eplGroupId = $this->requireGroup();

        try {
            $padpassword = \Request::get('pad_password');
            $this->client->setPassword($eplGroupId.'$'.$padid, $padpassword);
            \PageLayout::postInfo(dgettext('studipad', 'Passwort gesetzt.'));
        } catch (Exception $e) {
            \PageLayout::postError(dgettext('studipad', 'Das Passwort des Pads konnte nicht gesetzt werden.'));
        }

        $this->redirect(\Request::submitted('page') ? 'pads/iframe/'.$padid : '');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function remove_password_action($padid)
    {
        $this->requireTutor();
        $eplGroupId = $this->requireGroup();

        try {
            $this->client->setPassword($eplGroupId.'$'.$padid, null);
            \PageLayout::postInfo(dgettext('studipad', 'Passwort entfernt.'));
        } catch (Exception $e) {
            \PageLayout::postError(dgettext('studipad', 'Das Passwort des Pads konnte nicht entfernt werden.'));
        }

        $this->redirect(\Request::submitted('page') ? 'pads/iframe/'.$padid : '');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function activate_write_protect_action($padid)
    {
        $this->requireTutor();
        $eplGroupId = $this->requireGroup();

        $this->setWriteProtection($eplGroupId.'$'.$padid, 1);
        \PageLayout::postInfo(dgettext('studipad', 'Schreibschutz aktiviert.'));
        $this->redirect(\Request::submitted('page') ? 'pads/iframe/'.$padid  : '');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function deactivate_write_protect_action($padid)
    {
        $this->requireTutor();
        $eplGroupId = $this->requireGroup();

        $this->setWriteProtection($eplGroupId.'$'.$padid, 0);
        \PageLayout::postInfo(dgettext('studipad', 'Schreibschutz deaktiviert.'));
        $this->redirect(\Request::submitted('page') ? 'pads/iframe/'.$padid  : '');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function publish_action($padid)
    {
        $this->requireTutor();
        $eplGroupId = $this->requireGroup();

        try {
            $this->client->setPublicStatus($eplGroupId.'$'.$padid, 'true');
            \PageLayout::postInfo(dgettext('studipad', 'Veröffentlicht.'));
        } catch (Exception $e) {
            \PageLayout::postError(dgettext('studipad', 'Pad konnte nicht veröffentlicht werden.'));
        }

        $this->redirect(\Request::submitted('page') ? 'pads/iframe/'.$padid : '');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function unpublish_action($padid)
    {
        $this->requireTutor();
        $eplGroupId = $this->requireGroup();

        try {
            $this->client->setPublicStatus($eplGroupId.'$'.$padid, 'false');
            \PageLayout::postInfo(dgettext('studipad', 'Veröffentlichung aufgehoben.'));
        } catch (Exception $e) {
            \PageLayout::postError(dgettext('studipad', 'Veröffentlichung des Pads konnte nicht aufgehoben werden.'));
        }

        $this->redirect(\Request::submitted('page') ? 'pads/iframe/'.$padid : '');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function create_action()
    {
        $this->requireTutor();
        $eplGroupId = $this->requireGroup();

        $name = trim(\Request::get('new_pad_name', ''));
        if ('' === $name || mb_strlen($name) > 32) {
            \PageLayout::postError(dgettext(
                                       'studipad',
                                       'Es muss ein Name angegeben werden der aus maximal 32 Zeichen besteht.'
                                   ));
        } elseif (!preg_match('/^[A-Za-z0-9_-]+$/', $name)) {
            \PageLayout::postError(dgettext(
                                       'studipad',
                                       'Namen neuer Pads dürfen nur aus Buchstaben, Zahlen, Binde- und Unterstrichen bestehen.'
                                   ));
        } else {
            try {
                $result = $this->client->createGroupPad($eplGroupId, $name, \Config::get()->getValue('STUDIPAD_INITEXT'));
                $this->createControls($result->padID);
                \PageLayout::postInfo(dgettext('studipad', 'Das Pad wurde erfolgreich angelegt.'));
            } catch (\Exception $e) {
                \PageLayout::postError(dgettext('studipad', 'Das Pad konnte nicht angelegt werden.'));
            }
        }

        $this->redirect('');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function delete_action($padid)
    {
        $this->requireTutor();
        $eplGroupId = $this->requireGroup();

        try {
            $this->client->deletePad($eplGroupId.'$'.$padid);
            \PageLayout::postInfo(dgettext('studipad', 'Das Pad wurde gelöscht.'));
        } catch (Exception $e) {
            \PageLayout::postError(dgettext('studipad', 'Das Pad konnte nicht gelöscht werden.'));
        }

        $this->redirect('');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function snapshot_action($pad)
    {
        $this->requireTutor();
        $pad = $this->requirePad($pad);

        $fileRef = $this->storeSnapshot($this->getCurrentUser(), \Context::get(), $pad);

        $url = URLHelper::getLink(sprintf('dispatch.php/course/files/index/%s#fileref_%s', $fileRef->folder_id, $fileRef->id), ['cid' => \Context::getId()], true);

        \PageLayout::postInfo(
            sprintf(
                dgettext(
                    'studipad',
                    'Der aktuelle Inhalt des Etherpad-Dokuments wurde <a href="%s">im Dateibereich</a> gesichert.'
                ),
                $url
            )
        );

        $this->redirect(\Request::submitted('page') ? 'pads/iframe/'.$pad  : '');
    }

    protected function getPad($contextId, $padid)
    {
        $eplGroupId = $this->requireGroup();

        $grouppads = $this->client->listPads($eplGroupId);
        $pads = $grouppads->padIDs;
        $tpads = $this->getPads($contextId, $eplGroupId, $pads);

        if (!isset($tpads[$padid])) {
            \PageLayout::postError(dgettext('studipad', 'Dieses Pad konnte nicht gefunden werden.'));

            return $this->redirect('');
        }

        return $tpads[$padid];
    }

    protected function getPads($cid, $eplGroupId, $pads)
    {
        $tpads = [];
        if (count($pads)) {
            foreach ($pads as $pval) {
                $padparts = explode('$', $pval);
                $pad = $padparts[1];
                $tpads[$pad] = [];

                $padid = $eplGroupId.'$'.$pad;

                if (!strlen($tpads[$pad]['title'])) {
                    $tpads[$pad]['title'] = $pad;
                }

                $getPublicStatus = $this->client->getPublicStatus($padid);
                $tpads[$pad]['public'] = isset($getPublicStatus) ? $getPublicStatus->publicStatus : false;
                $tpads[$pad]['publicUrl'] = isset($getPublicStatus)
                                          ? $this->shorten(
                                              \Config::get()->getValue('STUDIPAD_PADBASEURL').
                                              '/'.
                                              $this->getPadCallId($eplGroupId, $pad)
                                          )
                                          : false;

                $isPasswordProtected = $this->client->isPasswordProtected($padid);
                $tpads[$pad]['hasPassword'] = isset($isPasswordProtected)
                                            ? $isPasswordProtected->isPasswordProtected
                                            : false;

                $tpads[$pad]['readOnly'] = $this->isWriteProtected($padid);

                $tpads[$pad] = array_merge($tpads[$pad], $this->getControls($padid));

                $lastVisit = object_get_visit($cid, 'sem', 'last');
                $clientLastEdited = $this->client->getLastEdited($padid);
                $padLastEdited = floor($clientLastEdited->lastEdited / 1000);
                $tpads[$pad]['new'] = $padLastEdited > $lastVisit;
                $tpads[$pad]['lastEdited'] = $padLastEdited;
            }
        }

        return $tpads;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getCurrentUser()
    {
        $currentUser = \User::findCurrent();

        return $currentUser;
    }

    protected function setDefaultPageTitle()
    {
        \PageLayout::setTitle(Context::getHeaderLine().' - Pad');
    }

    //////// OLD STUFF

    protected function createControls($padid)
    {
        $stmt = \DBManager::get()->prepare(
            'INSERT INTO plugin_StudIPad_controls '.
            '(pad_id, controls, readonly) VALUES (?, ?, ?)'
        );
        $stmt->execute([$padid, self::getControlsDefaultString(), 0]);
    }

    protected function getControlSet($padid, $control)
    {
        $db = \DBManager::get();

        switch ($control) {
            case 'showControls':
                $id = '0';
                break;
            case 'showColorBlock':
                $id = '1';
                break;
            case 'showImportExportBlock':
                $id = '2';
                break;
            case 'showChat':
                $id = '3';
                break;
            case 'showLineNumbers':
                $id = '4';
                break;
        }

        $sql = "SELECT controls FROM plugin_StudIPad_controls WHERE pad_id = '$padid'";

        $result = $db->query($sql)->fetchColumn();
        $setting = explode(';', $result);

        return $setting[$id];
    }

    private static function getControlsKeys()
    {
        return ['showControls', 'showColorBlock', 'showImportExportBlock', 'showChat', 'showLineNumbers'];
    }

    private static function getControlsDefaultValue()
    {
        return \Config::get()->getValue('STUDIPAD_CONTROLS_DEFAULT') ? 1 : 0;
    }

    private static function getControlsDefaultString()
    {
        return join(';', array_fill(0, count(self::getControlsKeys()), self::getControlsDefaultValue()));
    }

    private function getControls($padid)
    {
        $stmt = \DBManager::get()->prepare('SELECT controls FROM plugin_StudIPad_controls WHERE pad_id = ? LIMIT 1');
        $stmt->execute([$padid]);

        $controls = $stmt->fetch(PDO::FETCH_COLUMN);
        if (false === $controls) {
            $controls = self::getControlsDefaultString();
        }

        return array_combine(self::getControlsKeys(), explode(';', $controls));
    }

    private function setControls($padid, $controls)
    {
        $stmt = \DBManager::get()->prepare(
            'UPDATE plugin_StudIPad_controls SET controls = ? WHERE pad_id = ?'
        );

        $defaultValue = self::getControlsDefaultValue();
        $controlsString = join(';', array_map(function ($key) use ($controls, $defaultValue) {
            return isset($controls[$key]) ? ($controls[$key] ? 1 : 0) : $defaultValue;
        }, self::getControlsKeys()));

        $stmt->execute([$controlsString, $padid]);
    }

    private function isWriteProtected($padid)
    {
        $stmt = \DBManager::get()->prepare('SELECT readonly FROM plugin_StudIPad_controls WHERE pad_id = ? LIMIT 1');
        $stmt->execute([$padid]);

        return (bool) $stmt->fetch(PDO::FETCH_COLUMN);
    }

    private function setWriteProtection($padid, $protect)
    {
        $stmt = \DBManager::get()->prepare(
            'UPDATE plugin_StudIPad_controls SET readonly = ? WHERE pad_id = ?'
        );

        $stmt->execute([$protect ? 1 : 0, $padid]);
    }

    protected function setControlSet($padid, $padname, $controlset, $readonly)
    {
        $result = \DBManager::get()->prepare('REPLACE INTO plugin_StudIPad_controls (pad_id, controls, readonly) VALUES (:pid, :controls, :readonly)');
        $control = $result->execute(array('pid' => $padid, 'controls' => $controlset, 'readonly' => $readonly));

        return $control
            ? sprintf(dgettext('studipad', 'Die Einstellungen für das Pad "%s" wurden gespeichert!'), $padname)
            : sprintf(dgettext('studipad', 'Die Einstellungen für das Pad "%s" konnten nicht gespeichert werden!'), $padname);
    }

    protected function getHtmlControlString($padid)
    {
        $controls = $this->getControls($padid);
        $result = '&showControls='.($controls['showControls'] ? 'true' : 'false');

        if ($controls['showControls']) {
            foreach (['showColorBlock', 'showImportExportBlock', 'showChat', 'showLineNumbers'] as $key) {
                $result .= sprintf('&%s=%s', $key, $controls[$key] ? 'true' : 'false');
            }
        }

        return $result;
    }

    protected function getReadOnlyId($padid)
    {
        try {
            $padRO = $this->client->getReadOnlyID($padid);
            $result[0] = true;
        } catch (Excepion $e) {
            $result[0] = false;
            $result[2] = $e->getMessage();
        }

        $result[1] = $padRO->readOnlyID;

        return $result;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
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
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function requireTutor()
    {
        $cid = \Context::getId();
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $cid)) {
            throw new \AccessDeniedException();
        }
    }

    protected function requireGroup()
    {
        $cid = \Context::getId();
        try {
            $eplGmap = $this->client->createGroupIfNotExistsFor('subdomain:'.$cid);
        } catch (\Exception $e) {
            throw new \Trails_Exception(500, $e->getMessage());
        }

        if (!$eplGroupId = $eplGmap->groupID) {
            throw new \Trails_Exception(500, dgettext('studipad', 'Es ist ein Verbindungsfehler aufgetreten!'));
        }

        return $eplGroupId;
    }

    protected function requirePad($pad)
    {
        if (!preg_match('|^[A-Za-z0-9_-]+$|i', $pad)) {
            throw new \Trails_Exception(400, dgettext('studipad', 'Dieses Pad existiert nicht.'));
        }

        return $pad;
    }

    protected function getPadCallId($eplGroupId, $pad)
    {
        if (!$this->isWriteProtected($eplGroupId.'$'.$pad)) {
            return $eplGroupId.'$'.$pad;
        }
        list($success, $padCallId, $error) = $this->getReadOnlyId($eplGroupId.'$'.$pad);
        if (!$success) {
            throw new \Trails_Exception(
                sprintf(
                    dgettext('studipad', 'Fehler beim Ermitteln der padCallId: %s'),
                    $error
                )
            );
        }

        return $padCallId;
    }

    protected function redirectToEtherpad($pad)
    {
        $eplGroupId = $this->requireGroup();

        if (!preg_match('|^[A-Za-z0-9_-]+$|i', $pad)) {
            \PageLayout::postError(dgettext('studipad', 'Dieses Pad existiert nicht.'));

            return $this->redirect('');
        }

        $user = $this->getCurrentUser();
        $author = $this->client->createAuthorIfNotExistsFor($user->id, $user->getFullName());
        $authorID = $author->authorID;

        $until = strtotime('tomorrow');
        $eplSid = $this->client->createSession($eplGroupId, $authorID, $until);

        return sprintf(
            '%s/auth_session?sessionID=%s&padName=%s',
            dirname(Config::get()->getValue('STUDIPAD_PADBASEURL')),
            $eplSid->sessionID,
            $this->getPadCallId($eplGroupId, $pad)
        );
    }

    protected function storeSnapshot(\User $user, \Course $course, $pad)
    {
        try {
            $eplGroupId = $this->requireGroup();

            return $this->saveAsPDF(
                $user,
                $course,
                $pad,
                $this->client->getHTML($eplGroupId.'$'.$pad)->html
            );
        } catch (Exception $ex) {
            \PageLayout::postError($ex->getMessage());

            return $this->redirect('');
        }
    }

    private function saveAsPDF(\User $user, \Course $course, $pad, $html)
    {
        if (!$folder = \Folder::findTopFolder($course->id)) {
            throw new \RuntimeException('Could not find top folder.');
        }

        $filename = \FileManager::cleanFileName(sprintf('%s.%s.pdf', $pad, date('Y-m-d-H-m-s')));
        if (!$file = \File::create(['user_id' => $user->id, 'mime_type' => 'application/pdf', 'name' => $filename, 'storage' => 'disk'])) {
            throw new \RuntimeException('Could not store file.');
        }

        if (!$fileRef = \FileRef::create(['file_id' => $file->id, 'folder_id' => $folder->id, 'user_id' => $user->id, 'name' => $file->name])) {
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
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function createPDF(\Course $course, $pad, $html)
    {
        $doc = new \ExportPDF();
        $doc->setHeaderTitle('Etherpad-Dokument: '.$pad);
        $doc->setHeaderSubtitle($GLOBALS['UNI_NAME_CLEAN'].' » '.$course->getFullname().' » Pad');
        $doc->addPage();
        $doc->writeHTML($html);

        $tmpPath = tempnam($GLOBALS['TMP_PATH'], 'studipad');
        $doc->Output($tmpPath, 'F');

        return $tmpPath;
    }

    private function shorten($url)
    {
        $cache = \StudipCacheFactory::getCache();
        $cacheKey = 'pad/basicshortener/'.md5($url);

        $result = unserialize($cache->read($cacheKey));
        if (!$result) {
            $apiUrl = 'https://vt.uos.de/shorten.php?longurl='.urlencode($url);
            $curlHandle = \curl_init($apiUrl);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, 5);
            $result = curl_exec($curlHandle);
            $cache->write($cacheKey, serialize($result));
        }

        return $result;
    }
}

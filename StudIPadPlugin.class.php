<?php
/**
* @author               Oliver Oster <oster@zmml.uni-bremen.de>
*/

// +---------------------------------------------------------------------------+
// This file is NOT part of Stud.IP
// Copyright (C) 2011 Oliver Oster <oster@zmml.uni-bremen.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+
include 'etherpad-lite-client.php';

class StudIPadPlugin extends StudipPlugin implements StandardPlugin
{
    /**
     * plugin template factory.
     */
    protected $template_factory;
    protected $epl_client = null;

    /**
     * Initialize a new instance of the plugin.
     */
    public function __construct()
    {
        parent::__construct();
        bindtextdomain('studipad', dirname(__FILE__).'/locale');
        $template_path = $this->getPluginPath().'/templates';
        $this->template_factory = new Flexi_TemplateFactory($template_path);

        $navigation = new Navigation('StudIPad', PluginEngine::getURL($this, array(), 'show'));
        $navigation->setImage($this->getPluginURL().'/images/icons/ep_white.png', array('title' => 'StudIPad'));

        if (method_exists($navigation, 'setActiveImage')) {
            $navigation->setActiveImage($this->getPluginURL().'/images/icons/ep_black.png', array('title' => 'StudIPad'));
        }

        if (Navigation::hasItem('/course') && $this->isActivated()) {
            Navigation::addItem('/course/studipad', $navigation);
        }
    }

    public function eplclientInit()
    {
        if ($this->epl_client == null) {
            $ret = false;

            if (!($this->epl_client)) {
                try {
                    $this->epl_client = new EtherpadLiteClient(Config::get()->getValue('STUDIPAD_APIKEY'), Config::get()->getValue('STUDIPAD_APIURL'));
                    $ret = true;
                } catch (Exception $ex) {
                    $ret = false;
                }
            } else {
                $ret = true;
            }
        } else {
            $ret = true;
        }

        return $ret;
    }

    /**
     * Return a navigation object representing this plugin in the
     * course overview table or return NULL if you want to display
     * no icon for this plugin (or course). The navigation object’s
     * title will not be shown, only the image (and its associated
     * attributes like ’title’) and the URL are actually used.
     */
    public function getIconNavigation($course_id, $last_visit, $user_id = null)
    {
        $icon_navigation = null;
        $last_visit = $last_visit * 1000;

        if ($this->eplclientInit()) {
            try {
                $epl_gmap = $this->epl_client->createGroupIfNotExistsFor('subdomain:'.$course_id);
                $epl_groupid = $epl_gmap->groupID;

                if ($epl_groupid) {
                    $grouppads = $this->epl_client->listPads($epl_groupid);
                    $pads = $grouppads->padIDs;
                    $num_pads = count($pads);

                    if ($num_pads) {
                        $icon_title = sprintf(dgettext('studipad', '%d Pad(s)'), $num_pads);
                        $icon_navigation = new Navigation('StudIPad Icon', PluginEngine::getURL($this, array(), 'show'));
                        $icon_navigation->setImage($this->getPluginURL().'/images/icons/ep_grey.png', array('title' => $icon_title));

                        $new_count = 0;

                        foreach ($pads as $pad) {
                            $last_edit = 0;

                            try {
                                $le = $this->epl_client->getLastEdited($pad);
                                $last_edit = $le->lastEdited;
                            } catch (Exception $e) {
                            }

                            if ($last_edit > $last_visit) {
                                ++$new_count;
                            }
                        }

                        if ($new_count > 0) {
                            $icon_title = sprintf(dgettext('studipad', '%d Pad(s), %d neue'), $num_pads, $new_count);
                            $icon_navigation->setImage($this->getPluginURL().'/images/icons/ep_red.png', array('title' => $icon_title));
                        }
                    }
                }
            } catch (Exception $ex) {
            }
        }

        return $icon_navigation;
    }

    public function getTabNavigation($course_id)
    {
    }

    public function getNotificationObjects($course_id, $since, $user_id)
    {
        return null;
    }

    /**
     * Return a template (an instance of the Flexi_Template class)
     * to be rendered on the course summary page. Return NULL to
     * render nothing for this plugin.
     */
    public function getInfoTemplate($course_id)
    {
        return null;
    }

    public function getDisplayTitle()
    {
        return $_SESSION['SessSemName']['header_line'].' - '.'StudIPad';
    }

    /**
     * Display the plugin view template.
     */
    public function show_action()
    {
        error_log(print_r($_REQUEST, true));
        $semid = $GLOBALS['SessSemName'][1];
        $uid = $GLOBALS['auth']->auth['uid'];

        $last_visit = object_get_visit($semid, 'sem', 'last');

        PageLayout::setHelpKeyword('Basis.StudiPad');
        $layout = $GLOBALS['template_factory']->open('layouts/base');
        $template = $this->template_factory->open('studipad');
        $template->set_layout($layout);
        $template->newPadName = '';

        $padadmin = false;
        if ($GLOBALS['perm']->have_studip_perm('tutor', $semid)) {
            $padadmin = true;
        }

        $template->padadmin = $padadmin;
        $template->iframe = '';
        PageLayout::setTitle($this->getDisplayTitle());

        Navigation::activateItem('/course/studipad');

        if ($this->eplclientInit()) {
            try {
                $epl_gmap = $this->epl_client->createGroupIfNotExistsFor('subdomain:'.$semid);
                $epl_groupid = $epl_gmap->groupID;

                if (Request::submitted('action') && Request::submitted('pad')) {
                    $pad = Request::get('pad');
                    $action = Request::get('action');

                    if ($action == 'open' || $action == 'openi') {
                        //INSERTED EL 02.12.2014
                        if ($this->getReadOnlyStatus($epl_groupid.'$'.$pad)) {
                            $result = $this->getReadOnlyId($epl_groupid.'$'.$pad);

                            if (!$result[0]) {
                                $template->error = $result[2];
                            } else {
                                $padCallId = $result[1];
                            }
                        } else {
                            $padCallId = $epl_groupid.'$'.$pad;
                        }
                        //INSERTED END

                        if (preg_match('|^[A-Za-z0-9_-]+$|i', $pad)) {
                            if ($epl_groupid) {
                                // $padurl=Config::get()->getValue('STUDIPAD_PADBASEURL').'/'.$epl_groupid.'$'.$pad;

                                //Lösung für Hochschule RheinMain
                                $padurl = Config::get()->getValue('STUDIPAD_PADBASEURL').'/'.$padCallId.'?studip=true'.$this->getHtmlControlString($epl_groupid.'$'.$pad); //Hier könnte man die Schalter entsprechend ILIAS-Vorgabe reinposten!
                                $author = $this->epl_client->createAuthorIfNotExistsFor($uid, get_fullname_from_uname());
                                $authorID = $author->authorID;

                                $validUntil = mktime(0, 0, 0, date('m'), date('d') + 1, date('y')); // One day in the future
                                $epl_sid = $this->epl_client->createSession($epl_groupid, $authorID, $validUntil);

                                $sessionID = $epl_sid->sessionID;

                                // this hack is necessary to disable the standard Stud.IP layout

                                ob_end_clean();
                                setcookie('sessionID', $sessionID, $validUntil, '/', Config::get()->getValue('STUDIPAD_COOKIE_DOMAIN'), false, false);
                                $template->padname = $pad;
                                if ($action == 'openi') {
                                    $template->padurl = $padurl;
                                }

                                if ($action == 'open') {
                                    header('Content-Type: text/html; charset=ISO-8859-1'); // wenn man HTML ausgeben möchte...
                                    header('Location: '.$padurl);

                                    ob_start(create_function('$buffer', 'return "";'));
                                    $template->error = dgettext('studipad', 'Dies sollte nie zu sehen sein..');
                                }
                            }
                        }
                    }

                    if ($action == 'set_public' && $padadmin) {
                        try {
                            $this->epl_client->setPublicStatus($epl_groupid.'$'.$pad, 'true');
                        } catch (Exception $e) {
                            $template->error = $e->getMessage();
                        }
                    }

                    if ($action == 'unset_public' && $padadmin) {
                        try {
                            $this->epl_client->setPublicStatus($epl_groupid.'$'.$pad, 'false');
                        } catch (Exception $e) {
                            $template->error = $e->getMessage();
                        }
                    }

                    if ($action == 'unset_password' && $padadmin) {
                        try {
                            $this->epl_client->setPassword($epl_groupid.'$'.$pad, null);
                        } catch (Exception $e) {
                            $template->error = $e->getMessage();
                        }
                    }

                    if ($action == 'delete' && $padadmin) {
                        $pad_title = $pad;

                        $msg = sprintf(dgettext('studipad', 'Soll das Pad "%s" wirklich gel&ouml;scht werden?'), $pad_title);
                        $msg .= '<p align="center">'.Studip\LinkButton::create('Ja', PluginEngine::getURL($this, array('action' => 'confirm_delete', 'pad' => $pad))).'&nbsp;'.Studip\LinkButton::create('Nein', PluginEngine::getURL($this, array('action' => 'show'))).'</a></p>';
                        $template->message = $msg;
                    }

                    if ($action == 'confirm_delete' && $padadmin) {
                        $pad_title = $pad;
                        $del_ok = true;

                        try {
                            $this->epl_client->deletePad($epl_groupid.'$'.$pad);
                        } catch (Exception $e) {
                            $del_ok = false;
                            $template->error = $e->getMessage();
                        }

                        /*
                            //INSERTED EL 05.12.2014
                            if($del_ok) {
                                $db = DBManager::get();

                                $sql = "DELETE FROM plugin_StudIPad_controls
                                    WHERE pad_id = :padid
                                    LIMIT 1
                                   ";

                                $prepared = $db->prepare($sql);
                                $result = $prepared->execute(array('padid' => $epl_groupid.'$'.$pad));

                                if($result) {
                                } else {
                                $template->error = dgettext('studipad',"Beim L&ouml;schen in der Datenbank ist ein Fehler aufgetreten!<br />Bitte wenden Sie sich an Ihren Systemadministrator!");
                                }
                            }
                            //INSERTED END
                            */
                        if ($del_ok) {
                            $msg = sprintf(dgettext('studipad', 'Das Pad "%s" wurde gel&ouml;scht.'), $pad);
                            $template->message = $msg;
                        }
                    }
                }

                if (Request::submitted('set_pad_password') && $padadmin) {
                    /* Durch Änderungen an der HSRM ist das umgangen! EL 04.12.2014
                    foreach(Request::getArray('pad_password') as $pwpadid => $padidpw){
                        if(strlen($padidpw)>0){
                            $padpassword=$padidpw;
                            $pwpadid=$pwpadid;
                            break;
                        }
                    }*/

                    //INSERTED BY EL 04.12.2014
                    $padid = Request::get('padid');
                    $padpassword = Request::get('pad_password');
                    //INSERTED END

                    try {
                        $template->msg = dgettext('studipad', 'Passwort wurde gesetzt.');
                        $this->epl_client->setPassword($epl_groupid.'$'.$padid, $padpassword);
                    } catch (Exception $e) {
                        unset($template->msg);
                        $template->error = $e->getMessage();
                    }
                }

                //INSERTED BY EL 13.11.2014
                if ((Request::submitted('pad_controls_toggle')) && $padadmin) {
                    $padname = Request::get('padid');
                    $padid = $epl_groupid.'$'.$padname;

                    if ($this->getControlSet($padid, 'showControls') == '0' && Request::get('showControls') == true) {
                        $controlset = '1;1;1;1;1';
                    } else {
                        $controlset = ((Request::get('showControls')) ? 1 : 0).';'.
                              ((Request::get('showColorBlock')) ? 1 : 0).';'.
                              ((Request::get('showImportExportBlock')) ? 1 : 0).';'.
                              ((Request::get('showChat')) ? 1 : 0).';'.
                              ((Request::get('showLineNumbers')) ? 1 : 0);
                    }

                    if (Request::get('ReadOnly') == '1') {
                        $readonly = 1;
                    } else {
                        $readonly = 0;
                    }

                    $template->message = $this->setControlSet($padid, $padname, $controlset, $readonly);
                } //INSERTED END

                if (Request::submitted('new_pad') && $padadmin) {
                    $newName = Request::get('new_pad_name');
                    $do_new_pad = true;

                    if (strlen($newName) < 1 || strlen($newName) > 32) {
                        $template->error = dgettext('studipad', 'Es muss ein Name angegeben werden der aus maximal 32 Zeichen besteht.');
                        $do_new_pad = false;
                    } else {
                        if (!preg_match('|^[A-Za-z0-9_-]+$|i', $newName)) {
                            $template->error = dgettext('studipad', 'Pad Namen d&uuml;rfen nur aus Buchstaben und Zahlen bestehen.');
                            $template->newPadName = $newName;
                            $do_new_pad = false;
                        }
                    }

                    if ($do_new_pad) {
                        if ($epl_groupid) {
                            try {
                                $this->epl_client->createGroupPad($epl_groupid, $newName, Config::get()->getValue('STUDIPAD_INITEXT'));
                                //INSERTED EL 02.12.2014
                                $new_pad = true;
                                //INSERTED END
                            } catch (Exception $e) {
                                $template->error = dgettext('studipad', 'Das Pad konnte nicht angelegt werden.');
                                $template->error .= ' '.$e->getMessage();
                                //INSERTED EL 02.12.2014
                                $new_pad = false;
                                //INSERTED END
                            }
                        } else {
                            $template->error = dgettext('studipad', 'StudIPad Group konnte nicht angelegt werden.');
                            //INSERTED EL 02.12.2014
                            $new_pad = false;
                            //INSERTED END
                        }

                        //INSERTED EL 02.12.2014
                        if ($new_pad) {
                            if (Config::get()->getValue('STUDIPAD_CONTROLS_DEFAULT')) {
                                $this->setControlSet($epl_groupid.'$'.$newName, $newName, '1;1;1;1;1', 0);
                            } else {
                                $this->setControlSet($epl_groupid.'$'.$newName, $newName, '0;0;0;0;0', 0);
                            }
                        }
                        //INSERTED END
                    }
                }

                if ($epl_groupid) {
                    $grouppads = $this->epl_client->listPads($epl_groupid);
                    $pads = $grouppads->padIDs;
                }

                if (!$epl_groupid || !count($pads)) {
                    $template->message = dgettext('studipad', 'Zur Zeit sind keine Stud.IPads f&uuml;r diese Veranstaltung vorhanden.');
                }

                if (count($pads)) {
                    $tpads = array();
                    foreach ($pads as $padid => $pval) {
                        $padparts = explode('$', $pval);
                        $pad = $padparts[1];
                        $tpads[$pad] = array();

                        if (Config::get()->getValue('STUDIPAD_CONTROLS_DEFAULT')) {
                            $this->fixNewControls($epl_groupid.'$'.$pad);
                        }

                        if (!strlen($tpads[$pad]['title'])) {
                            $tpads[$pad]['title'] = $pad;
                        }

                        $getPublicStatus = $this->epl_client->getPublicStatus($epl_groupid.'$'.$pad);

                        if (isset($getPublicStatus)) {
                            $tpads[$pad]['public'] = $getPublicStatus->publicStatus;
                        } else {
                            $tpads[$pad]['public'] = false;
                        }

                        $isPasswordProtected = $this->epl_client->isPasswordProtected($epl_groupid.'$'.$pad);

                        if (isset($isPasswordProtected)) {
                            $tpads[$pad]['hasPassword'] = $isPasswordProtected->isPasswordProtected;
                        } else {
                            $tpads[$pad]['hasPassword'] = false;
                        }

                        //INSERTED EL 13.11.2014
                        //ReadOnly Status
                        $tpads[$pad]['readOnly'] = $this->getReadOnlyStatus($epl_groupid.'$'.$pad);
                        $tpads[$pad]['showControls'] = $this->getControlSet($epl_groupid.'$'.$pad, 'showControls');
                        $tpads[$pad]['showColorBlock'] = $this->getControlSet($epl_groupid.'$'.$pad, 'showColorBlock');
                        $tpads[$pad]['showImportExportBlock'] = $this->getControlSet($epl_groupid.'$'.$pad, 'showImportExportBlock');
                        $tpads[$pad]['showChat'] = $this->getControlSet($epl_groupid.'$'.$pad, 'showChat');
                        $tpads[$pad]['showLineNumbers'] = $this->getControlSet($epl_groupid.'$'.$pad, 'showLineNumbers');
                        //INSERTED END

                        $pad_le = $this->epl_client->getLastEdited($epl_groupid.'$'.$pad);
                        $pad_lastEdited = (int) (($pad_le->lastEdited / 1000));
                        $tpads[$pad]['new'] = ($pad_lastEdited > $last_visit);

                        $tpads[$pad]['lastEdited'] = $pad_lastEdited;
                    }

                    $template->tpads = $tpads;
                }
            } catch (Exception $ex) {
                $template->error = $ex->getMessage();
            }
        } else {
            $template->error = dgettext('studipad', 'StudIPad Client Setup Fehler');
        }

        echo $template->render();
    }

    public function fixNewControls($padid)
    {
        $db = DBManager::get();

        $sql = "SELECT COUNT(controls) FROM plugin_StudIPad_controls WHERE pad_id = '$padid'";

        $result = $db->query($sql)->fetchColumn();

        if (!$result) {
            $result = $db->query("REPLACE INTO plugin_StudIPad_controls (pad_id, controls, readonly) VALUES ('$padid','1;1;1;1;1',0)");
        }
    }

    //INSERTED EL 02.12.2014
    public function getReadOnlyStatus($padid)
    {
        $db = DBManager::get();

        $sql = "SELECT readonly 
			FROM plugin_StudIPad_controls 
			WHERE pad_id = '$padid'";

        $result = $db->query($sql)->fetchColumn();

        return $result['0'];
    }

    public function getControlSet($padid, $control)
    {
        $db = DBManager::get();

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

        $sql = "SELECT controls
			FROM plugin_StudIPad_controls 
			WHERE pad_id = '$padid'";

        $result = $db->query($sql)->fetchColumn();
        $setting = explode(';', $result);

        return $setting[$id];
    }

    public function setControlSet($padid, $padname, $controlset, $readonly)
    {
        $db = DBManager::get();

        $result = $db->prepare('REPLACE INTO plugin_StudIPad_controls (pad_id, controls, readonly) VALUES (:pid, :controls, :readonly)');
        $control = $result->execute(array('pid' => $padid, 'controls' => $controlset, 'readonly' => $readonly));

        if (!($control)) {
            $msg = sprintf(dgettext('studipad', 'Die Einstellungen f&uuml;r das Pad "%s" konnten nicht gespeichert werden!'), $padname);
        } else {
            $msg = sprintf(dgettext('studipad', 'Die Einstellungen f&uuml;r das Pad "%s" wurden gespeichert!'), $padname);
        }

        return $msg;
    }

    public function getHtmlControlString($padid)
    {
        if (!$this->getControlSet($padid, 'showControls')) {
            $result = '&showControls=false';
        } else {
            $result = '&showControls=true';

            if ($this->getControlSet($padid, 'showColorBlock')) {
                $result .= '&showColorBlock=true';
            } else {
                $result .= '&showColorBlock=false';
            }

            if ($this->getControlSet($padid, 'showImportExportBlock')) {
                $result .= '&showImportExportBlock=true';
            } else {
                $result .= '&showImportExportBlock=false';
            }

            if ($this->getControlSet($padid, 'showChat')) {
                $result .= '&showChat=true';
            } else {
                $result .= '&showChat=false';
            }

            if ($this->getControlSet($padid, 'showLineNumbers')) {
                $result .= '&showLineNumbers=true';
            } else {
                $result .= '&showLineNumbers=false';
            }
        }

        return $result;
    }

    public function getReadOnlyId($padid)
    {
        try {
            $padRO = $this->epl_client->getReadOnlyID($padid);
            $result[0] = true;
        } catch (Excepion $e) {
            $result[0] = false;
            $result[2] = $e->getMessage();
        }

        $result[1] = $padRO->readOnlyID;

        return $result;
    }

    //INSERTED END
}

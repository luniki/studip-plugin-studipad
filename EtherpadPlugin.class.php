<?php

/**
 * @author Oliver Oster <oster@zmml.uni-bremen.de>
 * @author <lunzenauer@elan-ev.de>
 */

// require composer autoloader
require __DIR__.'/vendor/autoload.php';

class EtherpadPlugin extends StudipPlugin implements StandardPlugin
{
    /**
     * plugin template factory.
     */
    protected $templateFactory;
    protected $eplClient = null;

    /**
     * Initialize a new instance of the plugin.
     */
    public function __construct()
    {
        parent::__construct();
        bindtextdomain('studipad', dirname(__FILE__).'/locale');
    }

    public function getClient()
    {
        return $this->eplclientInit() ? $this->eplClient : null;
    }

    protected function eplclientInit()
    {
        if (!$this->eplClient) {
            try {
                $this->eplClient = new \EtherpadLite\Client(
                    \Config::get()->getValue('STUDIPAD_APIKEY'),
                    \Config::get()->getValue('STUDIPAD_APIURL')
                );
            } catch (Exception $ex) {
            }
        }

        return isset($this->eplClient);
    }

    /**
     * Return a navigation object representing this plugin in the
     * course overview table or return NULL if you want to display
     * no icon for this plugin (or course). The navigation object’s
     * title will not be shown, only the image (and its associated
     * attributes like ’title’) and the URL are actually used.
     */
    public function getIconNavigation($courseId, $lastVisit, $userId = null)
    {
        if (!$this->eplclientInit()) {
            return null;
        }

        try {
            $iconNavigation = null;
            $lastVisit = $lastVisit * 1000;

            $eplGmap = $this->eplClient->createGroupIfNotExistsFor('subdomain:'.$courseId);
            $eplGroupid = $eplGmap->groupID;

            if ($eplGroupid) {
                $grouppads = $this->eplClient->listPads($eplGroupid);
                $pads = $grouppads->padIDs;
                $numPads = count($pads);

                if ($numPads) {
                    $iconTitle = sprintf(dgettext('studipad', '%d Pad(s)'), $numPads);
                    $iconNavigation = new Navigation('Etherpad', PluginEngine::getURL($this, [], ''));
                    $iconNavigation->setImage(Icon::create($this->getPluginURL().'/images/icons/EPedit.svg', \Icon::ROLE_INACTIVE, ['title' => $iconTitle]));
                    $newCount = 0;

                    foreach ($pads as $pad) {
                        $lastEdit = 0;

                        try {
                            $le = $this->eplClient->getLastEdited($pad);
                            $lastEdit = $le->lastEdited;
                        } catch (Exception $e) {
                        }

                        if ($lastEdit > $lastVisit) {
                            ++$newCount;
                        }
                    }

                    if ($newCount > 0) {
                        $iconTitle = sprintf(dgettext('studipad', '%d Pad(s), %d neue'), $numPads, $newCount);
                        $iconNavigation->setImage(Icon::create($this->getPluginURL().'/images/icons/EPedit-new.svg', ICON::ROLE_ATTENTION, ['title' => $iconTitle]));
                    }
                }
            }
        } catch (Exception $ex) {
        }

        return $iconNavigation;
    }

    public function getTabNavigation($courseId)
    {
        $url = PluginEngine::getURL($this, ['cid' => $courseId], '', true);
        $navigation = new Navigation('Etherpad', $url);

        $icon = Icon::create(
            $this->getPluginURL().'/images/icons/EPedit.svg',
            \Icon::ROLE_INACTIVE,
            ['title' => 'Etherpad']
        );
        $navigation->setImage($icon);
        $navigation->setActiveImage($icon->copyWithRole(ICON::ROLE_ATTENTION));

        $navigation->addSubNavigation('index', new Navigation(_('Übersicht'), $url));

        return ['studipad' => $navigation];
    }

    public function getNotificationObjects($courseId, $since, $userId)
    {
        return null;
    }

    /**
     * Return a template (an instance of the Flexi_Template class)
     * to be rendered on the course summary page. Return NULL to
     * render nothing for this plugin.
     */
    public function getInfoTemplate($courseId)
    {
        return null;
    }

    /**
     * This method dispatches all actions.
     *
     * @param string   part of the dispatch path that was not consumed
     */
    public function perform($unconsumedPath)
    {
        $args = explode('/', $unconsumedPath);

        $trailsRoot = $this->getPluginPath();
        $trailsUri = rtrim(PluginEngine::getLink($this, [], null, true), '/');

        $dispatcher = new Trails_Dispatcher($trailsRoot, $trailsUri, 'pads');
        $dispatcher->plugin = $this;
        try {
            $dispatcher->dispatch($unconsumedPath);
        } catch (Trails_UnknownAction $exception) {
            if (count($args) > 0) {
                throw $exception;
            } else {
                throw new Exception(_('unbekannte Plugin-Aktion: ').$unconsumedPath);
            }
        }
    }
}

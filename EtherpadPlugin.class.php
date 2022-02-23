<?php

/**
 * @author Oliver Oster <oster@zmml.uni-bremen.de>
 * @author <lunzenauer@elan-ev.de>
 */
class EtherpadPlugin extends StudIPPlugin implements StandardPlugin
{
    /** @var ?\Flexi_TemplateFactory */
    protected $templateFactory;
    /** @var ?\EtherpadLite\Client */
    protected $eplClient = null;

    /**
     * Initialize a new instance of the plugin.
     */
    public function __construct()
    {
        parent::__construct();

        require __DIR__ . '/vendor/autoload.php';
        bindtextdomain('studipad', dirname(__FILE__) . '/locale');
    }

    /**
     * @return ?\EtherpadLite\Client
     */
    public function getClient()
    {
        return $this->eplclientInit() ? $this->eplClient : null;
    }

    /**
     * @return bool
     */
    protected function eplclientInit()
    {
        if (!$this->eplClient) {
            try {
                $this->eplClient = new \EtherpadLite\Client(
                    \Config::get()->getValue('STUDIPAD_APIKEY'),
                    rtrim((string) \Config::get()->getValue('STUDIPAD_APIURL'), '/')
                );
            } catch (Exception $ex) {
            }
        }

        return isset($this->eplClient);
    }

    public const CACHE_KEY_SUFFIX = 'plugins/etherpadplugin/';

    /**
     * Return a navigation object representing this plugin in the
     * course overview table or return NULL if you want to display
     * no icon for this plugin (or course). The navigation object’s
     * title will not be shown, only the image (and its associated
     * attributes like ’title’) and the URL are actually used.
     *
     * @return ?object navigation item to render or NULL
     */
    public function getIconNavigation($courseId, $lastVisit, $userId = null)
    {
        if (!$lastVisit) {
            return $this->createIconNavigation(false);
        }

        $cache = \StudipCacheFactory::getCache();
        $cacheKey = self::CACHE_KEY_SUFFIX . $courseId;
        $lastEdit = $cache->read($cacheKey);

        if ($lastEdit === false) {
            try {
                if (!$this->eplclientInit()) {
                    return null;
                }

                $lastEdit = $this->getLastEdit($courseId);

                // problems getting $lastEdit, just pretend there aren't any
                if ($lastEdit === null) {
                    $lastEdit = $lastVisit;
                } else {
                    $cache->write($cacheKey, $lastEdit, 5 * 60);
                }
            } catch (Exception $ex) {
                return null;
            }
        }

        return $this->createIconNavigation($lastEdit >= $lastVisit);
    }

    public function getTabNavigation($courseId)
    {
        $url = PluginEngine::getURL($this, ['cid' => $courseId], '', true);
        $navigation = new Navigation('Etherpad', $url);

        $icon = Icon::create($this->getPluginURL() . '/images/icons/EPedit.svg', \Icon::ROLE_INACTIVE, [
            'title' => 'Etherpad',
        ]);
        $navigation->setImage($icon);
        $navigation->setActiveImage($icon->copyWithRole(Icon::ROLE_ATTENTION));

        $navigation->addSubNavigation('index', new Navigation(_('Übersicht'), $url));

        $groupURL = PluginEngine::getURL($this, ['cid' => $courseId], 'pads/groups', true);
        $navigation->addSubNavigation('groups', new Navigation(_('Gruppen'), $groupURL));

        if ($GLOBALS['perm']->have_studip_perm('tutor', $courseId)) {
            $settingsURL = PluginEngine::getURL($this, ['cid' => $courseId], 'settings', true);
            $navigation->addSubNavigation('settings', new Navigation(_('Einstellungen'), $settingsURL));
        }

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
     *
     * @return ?object navigation item to render or NULL
     */
    public function getInfoTemplate($courseId)
    {
        return null;
    }

    /**
     * This method dispatches all actions.
     *
     * @param string $unconsumedPath part of the dispatch path that was not consumed
     */
    public function perform($unconsumedPath)
    {
        $args = explode('/', $unconsumedPath);

        $trailsRoot = $this->getPluginPath();
        /** @var string $link */
        $link = PluginEngine::getLink($this, [], null, true);
        $trailsUri = rtrim($link, '/');

        $dispatcher = new Trails_Dispatcher($trailsRoot, $trailsUri, 'pads');
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumedPath);
    }

    public function getMetadata()
    {
        $metadata = parent::getMetadata();
        $metadata['pluginname'] = dgettext('studipad', 'EtherpadPlugin');
        $metadata['displayname'] = dgettext('studipad', 'Etherpad');
        $metadata['description'] = dgettext(
            'studipad',
            'Gemeinsam Texte erstellen und die Texterstellung koordinieren'
        );
        $metadata['descriptionlong'] = dgettext(
            'studipad',
            'Mit diesem Plugin können Sie allein oder mit vielen Menschen gleichzeitig Texte bearbeiten. Alle Teilnehmende der Veranstaltung können lesen und schreiben.'
        );
        $metadata['keywords'] = dgettext(
            'studipad',
            'Echt gleichzeitiges Arbeiten: Mehrere Personen können zur gleichen Zeit bearbeiten, alle sehen die Änderungen sofort.;Versionshistorie: Keine Änderung geht verloren, benutzen Sie das Uhr-Symbol oben.;Zwischenstände speichern: Im Menu links können sie den "aktuellen Inhalt sichern", der dann als PDF-Datei im Dateibereich landet.;Beliebig viele Pads pro Veranstaltung;Weltweiter Zugriff möglich: Im Menü links können Sie das Pad veröffentlichen und die dann angezeigte URL weitergeben.'
        );
        return $metadata;
    }

    /**
     * @param string|null $courseId
     * @return void
     */
    public function expireLastEditCache($courseId = null)
    {
        if ($courseId === null) {
            $courseId = \Context::getId();
        }
        $cache = \StudipCacheFactory::getCache();
        $cacheKey = self::CACHE_KEY_SUFFIX . $courseId;
        $cache->expire($cacheKey);
    }

    private function createIconNavigation($isFresh)
    {
        $iconTitle = $isFresh
            ? dgettext('studipad', 'Etherpad-Plugin mit neuen Inhalten')
            : dgettext('studipad', 'Etherpad-Plugin');
        $iconNavigation = new \Navigation('Etherpad', \PluginEngine::getURL($this, [], ''));
        $iconNavigation->setImage(
            \Icon::create(
                $this->getPluginURL() . ($isFresh ? '/images/icons/EPedit-new.svg' : '/images/icons/EPedit.svg'),
                $isFresh ? \Icon::ROLE_ATTENTION : \Icon::ROLE_INACTIVE,
                [
                    'title' => $iconTitle,
                ]
            )
        );
        $iconNavigation->setBadgeNumber('1');

        return $iconNavigation;
    }

    /**
     * @param string $courseId the ID of a course
     * @return ?integer timestamp of last edit, null if not able to compute it
     */
    private function getLastEdit($courseId)
    {
        $group = $this->eplClient->createGroupIfNotExistsFor('subdomain:' . $courseId);
        if (!isset($group->groupID) || !$group->groupID) {
            return null;
        }

        $pads = $this->eplClient->listPads($group->groupID);
        if (!isset($pads->padIDs)) {
            return null;
        }
        if (!count($pads->padIDs)) {
            return null;
        }

        $latest = null;
        foreach ($pads->padIDs as $padId) {
            $lastEdit = $this->eplClient->getLastEdited($padId);
            $timestamp = intval($lastEdit->lastEdited / 1000);
            if (!$latest || $latest < $timestamp) {
                $latest = $timestamp;
            }
        }

        return $latest;
    }
}

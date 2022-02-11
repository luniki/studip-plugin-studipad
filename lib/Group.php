<?php

namespace EtherpadPlugin;

use Course;
use EtherpadLite\Client;
use Statusgruppen;
use User;

class Group
{
    /** @var Client */
    private $client;

    /** @var Course|Statusgruppen */
    private $range;

    /** @var string|null */
    private $id = null;

    /**
     * @param Client $client
     * @param Course|Statusgruppen $range
     */
    public function __construct($client, $range)
    {
        $this->client = $client;
        $this->range = $range;
    }

    /**
     * @param ?User $user
     * @return bool
     */
    public function canAdmin(User $user = null)
    {
        $userId = $user ? $user->getId() : $GLOBALS['user']->id;
        $cid = $this->getCid();

        if ($this->range instanceof Course) {
            return $GLOBALS['perm']->have_studip_perm('tutor', $cid, $userId);
        } elseif ($this->range instanceof Statusgruppen) {
            $config = \CourseConfig::get($cid);
            $adminPermission = $config->getValue('STUDIPAD_STATUSGRUPPEN_ADMIN_PERMISSION');

            return $GLOBALS['perm']->have_studip_perm($adminPermission, $cid, $userId);
        }
    }

    /**
     * @param string $padName
     * @return Pad
     */
    public function createPad($padName)
    {
        $result = $this->client->createGroupPad(
            $this->getId(),
            $padName,
            \Config::get()->getValue('STUDIPAD_INITEXT')
        );

        $pad = new Pad($this->client, $this, $result->padID);
        $pad->createControls();

        return $pad;
    }

    /**
     * @return Course|Statusgruppen
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * @return string
     */
    public function getRangeId()
    {
        return $this->range->getId();
    }


    /**
     * @return string
     */
    public function getCid()
    {
        if ($this->range instanceof Course) {
            return $this->range->getId();
        } elseif ($this->range instanceof Statusgruppen) {
            return $this->range['range_id'];
        }
    }


    /**
     * @return string
     */
    public function getId()
    {
        if (!isset($this->id)) {
            $groupId = $this->createDomain();
            $eplGmap = $this->client->createGroupIfNotExistsFor($groupId);
            $eplGroupId = $eplGmap->groupID;
            if (!$eplGroupId) {
                throw new \RuntimeException(dgettext('studipad', 'Es ist ein Verbindungsfehler aufgetreten!'));
            }

            $this->id = $eplGroupId;
        }

        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if ($this->range instanceof Course) {
            return sprintf(dgettext('studipad', 'Pads der Veranstaltung: %s'), $this->range['name']);
        } elseif ($this->range instanceof Statusgruppen) {
            return sprintf(dgettext('studipad', 'Pads der Gruppe: %s'), $this->range['name']);
        }
    }

    /**
     * @param string $padName
     * @return ?Pad $pad
     */
    public function getPad($padName)
    {
        try {
            $matchingPads = array_filter($this->getPads(), function ($pad) use ($padName) {
                return $pad->getName() === $padName;
            });

            return current($matchingPads) ?: null;
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @return Pad[]
     */
    public function getPads()
    {
        $grouppads = $this->client->listPads($this->getId());

        return array_map(function ($padId) {
            return new Pad($this->client, $this, $padId);
        }, $grouppads->padIDs);
    }

    /**
     * @return bool
     */
    public function isCourseGroup()
    {
        return $this->range instanceof Course;
    }

    /**
     * @return bool
     */
    public function isStatusgruppenGroup()
    {
        return $this->range instanceof Statusgruppen;
    }

    // #########################################################################

    /**
     * @return string
     */
    private function createDomain()
    {
        if ($this->range instanceof Course) {
            return 'subdomain:' . $this->range->getId();
        } elseif ($this->range instanceof Statusgruppen) {
            return 'subdomain:' . $this->range['range_id'] . '/' . $this->range->getId();
        }
    }

    // #########################################################################

    /**
     * @param string $groupId
     * @return bool
     */
    public static function validateId($groupId)
    {
        return 18 === strlen($groupId) && 'g.' === substr($groupId, 0, 2);
    }
}

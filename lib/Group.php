<?php

namespace EtherpadPlugin;

use Course;
use EtherpadLite\Client;
use Statusgruppen;
use User;

class Group
{
    private ?string $id = null;

    public function __construct(
        private readonly Client $client,
        private readonly Statusgruppen|Course $range
    ) {
    }

    public function canAdmin(?User $user = null): bool
    {
        $userId = $user?->getId() ?? User::findCurrent()->id;
        $cid = $this->getCid();

        if ($this->range instanceof Course) {
            return $GLOBALS['perm']->have_studip_perm('tutor', $cid, $userId);
        } elseif ($this->range instanceof Statusgruppen) {
            $config = \CourseConfig::get($cid);
            $adminPermission = $config->getValue('STUDIPAD_STATUSGRUPPEN_ADMIN_PERMISSION');

            return $GLOBALS['perm']->have_studip_perm($adminPermission, $cid, $userId);
        }
        return false;
    }

    public function createPad(string $padName): Pad
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

    public function getRange(): Statusgruppen|Course
    {
        return $this->range;
    }

    public function getRangeId(): string
    {
        return $this->range->getId();
    }


    public function getCid(): string
    {
        if ($this->range instanceof Course) {
            return $this->range->getId();
        } elseif ($this->range instanceof Statusgruppen) {
            return $this->range['range_id'];
        }
        throw new \RuntimeException('Invalid range');
    }


    public function getId(): ?string
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

    public function getName(): string
    {
        if ($this->range instanceof Course) {
            return sprintf(dgettext('studipad', 'Pads der Veranstaltung: %s'), $this->range['name']);
        } elseif ($this->range instanceof Statusgruppen) {
            return sprintf(dgettext('studipad', 'Pads der Gruppe: %s'), $this->range['name']);
        }
        throw new \RuntimeException('Invalid range');
    }

    public function getPad(string $padName): ?Pad
    {
        try {
            $matchingPads = array_filter(
                $this->getPads(),
                fn($pad) => $pad->getName() === $padName
            );

            return current($matchingPads) ?? null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @return Pad[]
     */
    public function getPads(): iterable
    {
        $grouppads = $this->client->listPads($this->getId());

        return array_map(
            fn($padId) => new Pad($this->client, $this, $padId),
            $grouppads->padIDs
        );
    }

    public function isCourseGroup(): bool
    {
        return $this->range instanceof Course;
    }

    public function isStatusgruppenGroup(): bool
    {
        return $this->range instanceof Statusgruppen;
    }

    // #########################################################################

    private function createDomain(): string
    {
        if ($this->range instanceof Course) {
            return 'subdomain:' . $this->range->getId();
        } elseif ($this->range instanceof Statusgruppen) {
            return 'subdomain:' . $this->range['range_id'] . '/' . $this->range->getId();
        }
        return '';
    }

    // #########################################################################

    public static function validateId(string $groupId): bool
    {
        return 18 === strlen($groupId) && 'g.' === substr($groupId, 0, 2);
    }
}

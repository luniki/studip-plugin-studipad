<?php

namespace EtherpadPlugin;

use EtherpadLite\Client;
use Statusgruppen;

class Pad
{
    /** @var Client */
    private $client;

    /** @var Group */
    private $group;

    /** @var string */
    private $id;

    /**
     * @param Client $client
     * @param Group $group
     * @param string $id
     */
    public function __construct($client, $group, $id)
    {
        if (!self::validateId($id)) {
            throw new \InvalidArgumentException('Invalid pad ID: "' . $id . '".');
        }

        $this->client = $client;
        $this->id = $id;
        $this->group = $group;
    }

    /**
     * @return void
     */
    public function createControls()
    {
        $stmt = \DBManager::get()->prepare(
            'INSERT INTO plugin_StudIPad_controls ' . '(pad_id, controls, readonly) VALUES (?, ?, ?)'
        );
        $stmt->execute([$this->getId(), self::getControlsDefaultString(), 0]);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function delete()
    {
        $this->client->deletePad($this->getId());
    }

    /**
     * @return string[]
     */
    public function getControls()
    {
        $stmt = \DBManager::get()->prepare('SELECT controls FROM plugin_StudIPad_controls WHERE pad_id = ? LIMIT 1');
        $stmt->execute([$this->getId()]);

        $controls = $stmt->fetch(\PDO::FETCH_COLUMN);
        if (false === $controls) {
            $controls = self::getControlsDefaultString();
        }

        return array_combine(self::getControlsKeys(), explode(';', $controls));
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getHTML()
    {
        return $this->client->getHTML($this->getId())->html;
    }

    /**
     * @return string
     */
    public function getHtmlControlString()
    {
        $controls = $this->getControls();
        $result = '&showControls=' . ($controls['showControls'] ? 'true' : 'false');

        if ($controls['showControls']) {
            foreach (['showColorBlock', 'showImportExportBlock', 'showChat', 'showLineNumbers'] as $key) {
                $result .= sprintf('&%s=%s', $key, $controls[$key] ? 'true' : 'false');
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return number
     */
    public function getLastEdit()
    {
        $clientLastEdited = $this->client->getLastEdited($this->getId());

        return floor($clientLastEdited->lastEdited / 1000);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return substr($this->getId(), 19);
    }


    /**
     * @return string
     */
    public function getPadCallId()
    {
        if (!$this->isWriteProtected()) {
            return $this->getId();
        }

        // TODO: Fehlerbehandlung
        $padRO = $this->client->getReadOnlyID($this->getId());

        return $padRO->readOnlyID;
    }

    /**
     * @return string|null
     */
    public function getPublicURL()
    {
        if (!$this->isPublic()) {
            return null;
        }

        $padBaseURL = rtrim((string) \Config::get()->getValue('STUDIPAD_PADBASEURL'), '/');

        return $this->shorten($padBaseURL . '/' . $this->getPadCallId());
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        $lastVisit = object_get_visit($this->group->getCid(), 'sem', 'last');

        return $this->getLastEdit() >= $lastVisit;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        $result = $this->client->getPublicStatus($this->getId());

        return isset($result) ? $result->publicStatus : false;
    }

    /**
     * @return bool
     */
    public function isWriteProtected()
    {
        $stmt = \DBManager::get()->prepare('SELECT readonly FROM plugin_StudIPad_controls WHERE pad_id = ? LIMIT 1');
        $stmt->execute([$this->getId()]);

        return (bool) $stmt->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * @param int[] $controls
     * @return void
     */
    public function setControls(array $controls)
    {
        $stmt = \DBManager::get()->prepare('UPDATE plugin_StudIPad_controls SET controls = ? WHERE pad_id = ?');

        $defaultValue = self::getControlsDefaultValue();
        $controlsString = join(
            ';',
            array_map(function ($key) use ($controls, $defaultValue) {
                return isset($controls[$key]) ? ($controls[$key] ? 1 : 0) : $defaultValue;
            }, self::getControlsKeys())
        );

        $stmt->execute([$controlsString, $this->getId()]);
    }

    /**
     * @param bool $public
     * @return void
     */
    public function setPublic($public)
    {
        $this->client->setPublicStatus($this->getId(), $public ? 'true' : 'false');
    }

    /**
     * @param bool $protect
     * @return void
     */
    public function setWriteProtection($protect)
    {
        $stmt = \DBManager::get()->prepare('UPDATE plugin_StudIPad_controls SET readonly = ? WHERE pad_id = ?');

        $stmt->execute([$protect ? 1 : 0, $this->getId()]);
    }

    ################################################################################

    /**
     * @return string[]
     */
    public static function getControlsKeys()
    {
        return ['showControls', 'showColorBlock', 'showImportExportBlock', 'showChat', 'showLineNumbers'];
    }

    /**
     * @return integer
     */
    public static function getControlsDefaultValue()
    {
        /** @var bool $controlsDefault */
        $controlsDefault = (bool) \Config::get()->getValue('STUDIPAD_CONTROLS_DEFAULT');

        return $controlsDefault ? 1 : 0;
    }

    /**
     * @return string
     */
    public static function getControlsDefaultString()
    {
        return join(';', array_fill(0, count(self::getControlsKeys()), self::getControlsDefaultValue()));
    }

    /**
     * @param string $padId
     * @return bool
     */
    public static function validateId($padId)
    {
        $groupId = substr($padId, 0, 18);
        $divider = substr($padId, 18, 1);
        $padName = substr($padId, 19);

        return '$' === $divider && Group::validateId($groupId) && self::validateName($padName);
    }

    /**
     * @param string $padName
     *
     * @return bool
     */
    public static function validateName($padName)
    {
        return preg_match('|^[A-Za-z0-9_-]+$|i', $padName);
    }

    ################################################################################

    /**
     * @param string $url
     * @return string
     */
    private function shorten($url)
    {
        $cache = \StudipCacheFactory::getCache();
        $cacheKey = 'pad/basicshortener/' . md5($url);

        $result = unserialize($cache->read($cacheKey));
        if (!$result) {
            $apiUrl = 'https://vt.uos.de/shorten.php?longurl=' . urlencode($url);
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

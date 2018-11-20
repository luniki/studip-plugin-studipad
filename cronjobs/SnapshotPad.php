<?php

require_once 'lib/classes/CronJob.class.php';

class SnapshotPad extends \CronJob
{
    public static function getName()
    {
        return 'StudIPad - Aktuellen Snapshot eines Etherpad-Dokuments abspeichern';
    }

    public static function getDescription()
    {
        return 'Der Export eines Etherpad-Dokuments wird heruntergeladen und im Dateibereich der Veranstaltung abgelegt.';
    }

    public static function getParameters()
    {
        return [
            'userid' => [
                'type' => 'string',
                'status' => 'mandatory',
                'description' => 'ID des Nutzers, der das Pad importiert.',
            ],
            'cid' => [
                'type' => 'string',
                'status' => 'mandatory',
                'description' => 'ID der Veranstaltung, der das Pad zugeordnet ist.',
            ],
            'pad' => [
                'type' => 'string',
                'status' => 'mandatory',
                'description' => 'Name des Pads.',
            ],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($lastResult, $parameters = [])
    {
        require_once __DIR__.'/../vendor/autoload.php';

        try {
            $course = $this->getCourse($parameters['cid']);
            $client = $this->getClient();
            $this->saveAsPDF(
                $course,
                $this->getUser($parameters['userid']),
                $parameters['pad'],
                $client->getHTML($this->getPadCallId($client, $course, $parameters['pad']))->html
            );
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }

        return true;
    }

    private function getClient()
    {
        return new \EtherpadLite\Client(
            \Config::get()->getValue('STUDIPAD_APIKEY'),
            \Config::get()->getValue('STUDIPAD_APIURL')
        );
    }

    private function getPadCallId($client, \Course $course, $pad)
    {
        if (!preg_match('|^[A-Za-z0-9_-]+$|i', $pad)) {
            throw new \RuntimeException('Bad pad name.');
        }

        if (!$eplGroupId = $client->createGroupIfNotExistsFor('subdomain:'.$course->id)->groupID) {
            throw \RuntimeException('Could not retrieve group.');
        }

        return $eplGroupId.'$'.$pad;
    }

    private function getCourse($cid)
    {
        if (!$course = \Course::find($cid)) {
            throw \RuntimeException('Bad cid.');
        }

        return $course;
    }

    private function getUser($userid)
    {
        if (!$user = \User::find($userid)) {
            throw \RuntimeException('Bad userid.');
        }

        return $user;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function createPDF(\Course $course, $pad, $html)
    {
        $doc = new \ExportPDF();
        $doc->setHeaderTitle('Etherpad-Dokument: '.$pad);
        $doc->setHeaderSubtitle($course->getFullname().' – StudIPad');
        $doc->addPage();
        $doc->writeHTML($html);

        $tmpPath = tempnam($GLOBALS['TMP_PATH'], 'studipad');
        $doc->Output($tmpPath, 'F');

        return $tmpPath;
    }

    private function saveAsPDF(\Course $course, \User $user, $pad, $html)
    {
        if (!$folder = \Folder::findTopFolder($course->id)) {
            throw new \RuntimeException('Could not find top folder.');
        }

        $filename = \FileManager::cleanFileName(sprintf('%s.%s.pdf', $pad, date('c')));
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
}

<?php

class RegisterSnapshotCronjob extends Migration
{
    const SNAPSHOTTER = 'public/plugins_packages/ZMML/StudIPadPlugin/cronjobs/SnapshotPad.php';

    public function description()
    {
        return 'register cronjob for storing snapshots of pads';
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function up()
    {
        CronjobScheduler::registerTask(self::SNAPSHOTTER, true);
    }

    public function down()
    {
        if ($taskId = CronjobTask::findOneByFilename(self::SNAPSHOTTER)->task_id) {
            CronjobScheduler::unregisterTask($taskId);
        }
    }
}

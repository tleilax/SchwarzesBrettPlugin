<?php
class AddCronjob extends Migration
{
    public function description ()
    {
        return 'Fügt den Cronjob zum Entfernen abgelaufener Anzeigen hinzu..';
    }

    // Schedule removement of expired items every day at 3:00
    public function up()
    {
        if (CronjobTask::countBySql('filename = ?', [$this->getCronjobFilename()]) === 0) {
            $task_id = CronjobScheduler::registerTask($this->getCronjobFilename());
            $schedule = CronjobScheduler::schedulePeriodic($task_id, 0, 3);

            $schedule->active = true;
            $schedule->store();
        }
    }

    public function down()
    {
        $task_id = CronjobTask::findByFilename($this->getCronjobFilename())->task_id;
        CronjobScheduler::unregisterTask($task_id);
    }

    private function getCronjobFilename()
    {
        return str_replace(
            realpath($GLOBALS['STUDIP_BASE_PATH'] . '/'),
            '',
            realpath(__DIR__ . '/../classes/Cronjob.class.php')
        );
    }
}

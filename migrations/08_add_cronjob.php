<?php
class AddCronjob extends DBMigration
{
    function description ()
    {
        return 'F�gt den Cronjob zum Entfernen abgelaufener Anzeigen hinzu..';
    }

    // Schedule removement of expired items every day at 3:00
    function up()
    {
        $task_id = CronjobScheduler::registerTask($this->getCronjobFilename());
        $schedule = CronjobScheduler::schedulePeriodic($task_id, 0, 3);

        $schedule->active = true;
        $schedule->store();
    }

    function down()
    {
        $task_id = CronjobTask::findByFilename($this->getCronjobFilename())->task_id;
        CronjobScheduler::unregisterTask($task_id);
    }

    private function getCronjobFilename()
    {
        return str_replace($GLOBALS['STUDIP_BASE_PATH'] . '/', '',
                           realpath(__DIR__ . '/../classes/SchwarzesBrett_Cronjob.class.php'));
    }
}
<?php
class FixCronjob extends Migration
{
    public function description()
    {
        return 'Fixes cronjob that was forgotten in migration 22';
    }

    public function up()
    {
        $query = "UPDATE `cronjob_tasks`
                  SET `filename` = 'public/plugins_packages/UOL/SchwarzesBrettPlugin/classes/Cronjob.php'
                  WHERE `filename` = 'public/plugins_packages/IBIT/SchwarzesBrettPlugin/classes/Cronjob.php'";
        DBManager::get()->exec($query);
    }
}

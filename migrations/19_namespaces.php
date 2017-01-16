<?php
class Namespaces extends Migration
{
    public function up()
    {
        $query = "UPDATE `cronjobs_tasks`
                  SET `filename` = REPLACE(`filename`, 'SchwarzesBrett_Cronjob.class.php', 'Cronjob.php'),
                      `class` = 'SchwarzesBrett\\\\Cronjob'
                  WHERE `class` = 'SchwarzesBrettCronjob'";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "UPDATE `cronjobs_tasks`
                  SET `filename` = REPLACE(`filename`, 'Cronjob.php', 'SchwarzesBrett_Cronjob.class.php'),
                      `class` = 'SchwarzesBrettCronjob'
                  WHERE `class` = 'SchwarzesBrett\\\\Cronjob'";
        DBManager::get()->exec($query);
    }
}

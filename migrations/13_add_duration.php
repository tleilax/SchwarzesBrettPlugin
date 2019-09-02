<?php
class AddDuration extends Migration
{
    public function description()
    {
        return 'Adds database columns for chdate and duration';
    }

    public function up()
    {
        $query = "ALTER TABLE `sb_artikel`
                  ADD COLUMN `duration` TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER `mkdate`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `sb_artikel`
                  ADD COLUMN `chdate` INT(11) UNSIGNED NOT NULL AFTER `mkdate`";
        DBManager::get()->exec($query);

        $query = "UPDATE `sb_artikel`
                  SET `duration` = ROUND((`expires` - `mkdate`) / (24 * 60 * 60)),
                      `chdate` = `mkdate`";
        DBManager::get()->exec($query);

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $query = "ALTER TABLE `sb_artikel`
                  DROP COLUMN `duration`,
                  DROP COLUMN `chdate`";
        DBManager::get()->exec($query);

        SimpleORMap::expireTableScheme();
    }
}

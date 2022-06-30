<?php
final class AddReadStatusForCategories extends Migration
{
    protected function up()
    {
        $query = "ALTER TABLE `sb_themen`
                  CHANGE COLUMN `perm` `perm_create` CHAR(8) COLLATE `latin1_bin` DEFAULT 'autor',
                  ADD COLUMN `perm_access_min` CHAR(8) COLLATE `latin1_bin` DEFAULT NULL AFTER `perm_create`,
                  ADD COLUMN `perm_access_max` CHAR(8) COLLATE `latin1_bin` DEFAULT NULL AFTER `perm_access_min`";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "ALTER TABLE `sb_themen`
                  DROP COLUMN `perm_access_max`,
                  DROP COLUMN `perm_access_min`,
                  CHANGE COLUMN `perm_create` `perm` CHAR(8) COLLATE `latin1_bin` DEFAULT 'autor'";
        DBManager::get()->exec($query);
    }
}

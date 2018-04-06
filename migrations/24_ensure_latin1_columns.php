<?php
class EnsureLatin1Columns extends Migration
{
    public function up()
    {
        $query = "ALTER TABLE `sb_artikel`
                  MODIFY COLUMN `artikel_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  MODIFY COLUMN `thema_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  MODIFY COLUMN `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `sb_blacklist`
                  MODIFY COLUMN `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `sb_themen`
                  MODIFY COLUMN `thema_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  MODIFY COLUMN `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  MODIFY COLUMN `perm` CHAR(8) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'autor'";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `sb_visits`
                  MODIFY COLUMN `object_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  MODIFY COLUMN `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                  MODIFY COLUMN `type` ENUM('thema', 'artikel') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'thema'";
        DBManager::get()->exec($query);
    }
}

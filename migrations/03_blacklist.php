<?php
class Blacklist extends Migration
{
    public function description()
    {
        return 'es wird eine neue db für benutzer auf einer schwarzen liste erstellst.';
    }

    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `sb_blacklist` (
                    `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `mkdate` INT(20) NOT NULL default '0',
                    PRIMARY KEY (`user_id`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DROP TABLE IF EXISTS `sb_blacklist`";
        DBManager::get()->exec($query);
    }
}

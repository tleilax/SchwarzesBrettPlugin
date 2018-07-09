<?php
class Blacklist extends Migration
{
    public function description ()
    {
        return 'es wird eine neue db für benutzer auf einer schwarzen liste erstellst.';
    }

    public function up ()
    {
        $db = DBManager::get();
        $db->exec("CREATE TABLE IF NOT EXISTS `sb_blacklist` (
                      `user_id` varchar(32) NOT NULL,
                      `mkdate` int(20) NOT NULL default '0',
                      PRIMARY KEY (`user_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC");
    }

    public function down ()
    {
    }
}
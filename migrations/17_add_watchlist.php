<?php
class AddWatchlist extends Migration
{
    public function description()
    {
        return 'Adds a watchlist option';
    }

    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `sb_watchlist` (
                      `user_id` CHAR(32) NOT NULL,
                      `artikel_id` CHAR(32) NOT NULL,
                      `mkdate` INT(11) UNSIGNED NOT NULL,
                      PRIMARY KEY (`user_id`, `artikel_id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DROP TABLE IF EXISTS `sb_watchlist`";
        DBManager::get()->exec($query);
    }
}

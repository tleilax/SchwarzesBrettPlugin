<?php
class GarbageCollectUsers extends Migration
{
    public function up()
    {
        $query = "DELETE FROM `sb_blacklist`
                  WHERE `user_id` NOT IN (
                      SELECT `user_id` FROM `auth_user_md5`
                  )";
        DBManager::get()->exec($query);

        $query = "DELETE FROM `sb_watchlist`
                  WHERE `user_id` NOT IN (
                      SELECT `user_id` FROM `auth_user_md5`
                  )";
        DBManager::get()->exec($query);
    }
}

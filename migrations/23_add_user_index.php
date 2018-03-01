<?php
class AddUserIndex extends Migration
{
    public function up()
    {
        $query = "ALTER TABLE `sb_artikel`
                  ADD INDEX `user_id` (`user_id`)";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `sb_artikel`
                  DROP INDEX `user_id`";
        DBManager::get()->exec($query);
    }
}

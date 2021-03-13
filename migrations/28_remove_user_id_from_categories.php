<?php
/**
 * Removes the user id from the table "sb_themen". It's never displayed
 * anywhere and is not really neccessary. It even becomes a problem when the
 * user is deleted. The category should not be removed but we also don't want
 * an orphaned user id in the table.
 */
class RemoveUserIdFromCategories extends Migration
{
    public function up()
    {
        $query = "ALTER TABLE `sb_themen`
                  DROP COLUMN `user_id`";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `sb_themen`
                  ADD COLUMN `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL AFTER `titel`";
        DBManager::get()->exec($query);
    }
}

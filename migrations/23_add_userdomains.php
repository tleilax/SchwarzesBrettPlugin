<?php
class AddUserdomains extends Migration
{
    public function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `sb_themen`
            ADD COLUMN `domains` VARCHAR(200) NOT NULL DEFAULT 'all'
        ");
        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $query = "ALTER TABLE `sb_themen`
                    DROP COLUMN `domains`";
        DBManager::get()->exec($query);
        SimpleORMap::expireTableScheme();
    }
}

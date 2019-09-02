<?php
class Rss extends Migration
{
    public function description()
    {
        return 'eintraege fuer rss werden angelegt';
    }

    public function up()
    {
        $query = "ALTER TABLE `sb_artikel`
                  ADD `publishable` TINYINT(2) NOT NULL DEFAULT 0";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `sb_artikel`
                  DROP `publishable`";
        DBManager::get()->exec($query);
    }
}

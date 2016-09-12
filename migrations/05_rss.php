<?php
class Rss extends Migration
{
    public function description ()
    {
        return 'eintraege fuer rss werden angelegt';
    }

    public function up ()
    {
        $db = DBManager::get();
        $db->exec("ALTER IGNORE TABLE `sb_artikel` ADD `publishable` tinyint(2) NOT NULL default '0'");
    }

    public function down ()
    {
        $db = DBManager::get();
        $db->exec("ALTER IGNORE TABLE `sb_artikel` DROP `publishable`");
    }
}

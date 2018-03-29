<?php
class Rss2 extends Migration
{
    public function description ()
    {
        return _('Einträge für Themen-RSS werden angelegt');
    }

    public function up ()
    {
        DBManager::get()->exec("ALTER TABLE `sb_themen` ADD `publishable` tinyint(1) NOT NULL default 0");
    }

    public function down ()
    {
        DBManager::get()->exec("ALTER TABLE `sb_themen` DROP `publishable`");
    }
}

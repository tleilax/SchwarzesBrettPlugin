<?php
class Rss2 extends Migration
{
    public function description()
    {
        return _('Einträge für Themen-RSS werden angelegt');
    }

    public function up()
    {
        $query = "ALTER TABLE `sb_themen`
                  ADD `publishable` TINYINT(1) NOT NULL DEFAULT 0";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `sb_themen`
                  DROP `publishable`";
        DBManager::get()->exec($query);
    }
}

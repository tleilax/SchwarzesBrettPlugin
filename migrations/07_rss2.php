<?
class Rss2 extends DBMigration
{
    function description ()
    {
        return _('Einträge für Themen-RSS werden angelegt');
    }

    function up ()
    {
        DBManager::get()->exec("ALTER IGNORE TABLE `sb_themen` ADD `publishable` tinyint(1) NOT NULL default 0");
    }

    function down ()
    {
        DBManager::get()->exec("ALTER IGNORE TABLE `sb_then` DROP `publishable`");
    }
}

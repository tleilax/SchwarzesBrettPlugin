<?
class Optimizedb extends Migration
{
    public function description ()
    {
        return 'es werden neue indexe für die datenbank-tabellen angelegt.';
    }

    public function up ()
    {
        $db = DBManager::get();
        try {
            $db->exec("ALTER TABLE `sb_visits` DROP INDEX `user_id`");
            $db->exec("ALTER TABLE `sb_visits` ADD INDEX ( `user_id` , `last_visitdate` )");
        } catch (PDOException $e) {}
        try {
            $db->exec("ALTER TABLE `sb_artikel` DROP INDEX `visible`");
            $db->exec("ALTER TABLE `sb_artikel` ADD INDEX ( `visible` , `mkdate` )");
        } catch (PDOException $e) {}
        try {
            $db->exec("ALTER TABLE `sb_artikel` DROP INDEX `thema_id`");
            $db->exec("ALTER TABLE `sb_artikel` ADD INDEX ( `thema_id` )");
        } catch (PDOException $e) {}
    }

    public function down ()
    {
    }
}

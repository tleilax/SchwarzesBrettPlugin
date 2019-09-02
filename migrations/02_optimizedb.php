<?
class Optimizedb extends Migration
{
    public function description()
    {
        return 'es werden neue indexe für die datenbank-tabellen angelegt.';
    }

    public function up()
    {
        try {
            DBManager::get()->exec("ALTER TABLE `sb_visits` DROP INDEX `user_id`");
            DBManager::get()->exec("ALTER TABLE `sb_visits` ADD INDEX (`user_id` , `last_visitdate`)");
        } catch (PDOException $e) {}

        try {
            DBManager::get()->exec("ALTER TABLE `sb_artikel` DROP INDEX `visible`");
            DBManager::get()->exec("ALTER TABLE `sb_artikel` ADD INDEX (`visible` , `mkdate`)");
        } catch (PDOException $e) {}

        try {
            DBManager::get()->exec("ALTER TABLE `sb_artikel` DROP INDEX `thema_id`");
            DBManager::get()->exec("ALTER TABLE `sb_artikel` ADD INDEX (`thema_id`)");
        } catch (PDOException $e) {}
    }

    public function down()
    {
    }
}

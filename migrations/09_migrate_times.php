<?php
class MigrateTimes extends DBMigration
{
    public function description()
    {
        return 'Stores expiration time directly with entries.';
    }
    
    public function up()
    {
        $query = "ALTER TABLE sb_artikel ADD COLUMN expires INT(11) UNSIGNED NOT NULL AFTER mkdate";
        DBManager::get()->exec($query);
        
        $query = "SELECT value  FROM config WHERE field = 'BULLETIN_BOARD_DURATION' ORDER BY is_default ASC";
        $expiration_days = DBManager::get()->query($query)->fetchColumn() ?: 30;
        $expiration_time = $expiration_days * 24 * 60 * 60;
        
        $query = "UPDATE sb_artikel SET expires = mkdate + :expiration_time";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':expiration_time', $expiration_time);
        $statement->execute();
    }
    
    public function down()
    {
        $query = "ALTER TABLE sb_artikel DROP COLUMN expires";
        DBManager::get()->exec($query);
    }
}

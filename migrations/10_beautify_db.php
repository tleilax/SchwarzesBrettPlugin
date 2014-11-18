<?php
class BeautifyDb extends DBMigration
{
    public function description()
    {
        return 'Let\'s clean this mess up a little bit...';
    }

    public function up()
    {
        $query = "UPDATE sb_themen SET publishable = 1";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE sb_artikel
                        MODIFY COLUMN artikel_id CHAR(32) NOT NULL,
                        MODIFY COLUMN thema_id CHAR(32) NOT NULL,
                        MODIFY COLUMN user_id CHAR(32) NOT NULL,
                        MODIFY COLUMN mkdate INT(11) UNSIGNED NOT NULL,
                        MODIFY COLUMN visible TINYINT(1) NOT NULL DEFAULT 1,
                        MODIFY COLUMN publishable TINYINT(1) NOT NULL DEFAULT 1";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE sb_blacklist
                        MODIFY COLUMN user_id CHAR(32) NOT NULL,
                        MODIFY COLUMN mkdate INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE sb_themen
                        MODIFY COLUMN thema_id CHAR(32) NOT NULL,
                        MODIFY COLUMN user_id CHAR(32) NOT NULL,
                        MODIFY COLUMN mkdate INT(11) UNSIGNED NOT NULL,
                        MODIFY COLUMN perm CHAR(8) NOT NULL DEFAULT 'autor',
                        MODIFY COLUMN visible TINYINT(1) NOT NULL DEFAULT 1";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE sb_visits
                        MODIFY COLUMN last_visitdate INT(11) UNSIGNED NOT NULL";
        DBManager::get()->exec($query);
    }
}

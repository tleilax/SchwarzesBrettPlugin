<?php
class InitPlugin extends Migration
{
    public function description()
    {
        return 'first things first';
    }

    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `sb_themen` (
                    `thema_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `titel` VARCHAR(255) NOT NULL,
                    `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `mkdate` INT(20) NOT NULL default '0',
                    `beschreibung` TEXT,
                    `perm` VARCHAR(255) NOT NULL,
                    `visible` TINYINT(2) NOT NULL default '0',
                    PRIMARY KEY  (`thema_id`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        $query = "CREATE TABLE IF NOT EXISTS `sb_artikel` (
                    `artikel_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `thema_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    `titel` VARCHAR(255) NOT NULL,
                    `beschreibung` TEXT NOT NULL,
                    `mkdate` INT(20) NOT NULL,
                    `visible` TINYINT(2) NOT NULL default '0',
                    PRIMARY KEY (`artikel_id`),
                    KEY `visible` (`visible`,`mkdate`),
                    KEY `thema_id` (`thema_id`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        $query = "CREATE TABLE IF NOT EXISTS `sb_visits` (
                    `object_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL default '',
                    `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL default '',
                    `type` ENUM('thema','artikel') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL default 'thema',
                    `last_visitdate` INT(20) NOT NULL default '0',
                    PRIMARY KEY (`object_id`,`user_id`),
                    KEY `user_id` (`user_id`,`last_visitdate`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        Config::get()->create('BULLETIN_BOARD_DURATION', [
            'value'       => 30,
            'type'        => 'integer',
            'range'       => 'global',
            'section'     => 'SchwarzesBrettPlugin',
            'description' => 'Wie lange dürfen Anzeigen auf dem schwarzen Brett erscheinen (in Tagen)',
        ]);
        Config::get()->create('BULLETIN_BOARD_ANNOUNCEMENTS', [
            'value'       => 20,
            'type'        => 'integer',
            'range'       => 'global',
            'section'     => 'SchwarzesBrettPlugin',
            'description' => 'Wieviele Anzeigen sollen in der Übersicht angezeigt werden?',
        ]);
    }

    public function down()
    {
        $query = "DROP TABLE IF EXSTS `sb_themen`, `sb_artikel`, `sb_visits`";
        DBManager::get()->exec($query);

        Config::get()->delete('BULLETIN_BOARD_DURATION');
        Config::get()->delete('BULLETIN_BOARD_ANNOUNCEMENTS');
    }
}

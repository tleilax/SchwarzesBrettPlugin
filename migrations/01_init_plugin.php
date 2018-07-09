<?php
class InitPlugin extends Migration
{
    public function description ()
    {
        return 'first things first';
    }

    public function up ()
    {
        $db = DBManager::get();
        $exists = $db->query("SHOW TABLES LIKE 'sb_artikel'")->fetchColumn();
        if ($exists === 'sb_artikel') {
            return;
        }
        $db->exec("CREATE TABLE IF NOT EXISTS `sb_themen` (
                  `thema_id` varchar(32) NOT NULL,
                  `titel` varchar(255) NOT NULL,
                  `user_id` varchar(32) NOT NULL,
                  `mkdate` int(20) NOT NULL default '0',
                  `beschreibung` text,
                  `perm` varchar(255) NOT NULL,
                  `visible` tinyint(2) NOT NULL default '0',
                  PRIMARY KEY  (`thema_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC");
        $db->exec("CREATE TABLE IF NOT EXISTS `sb_artikel` (
                  `artikel_id` varchar(32) NOT NULL,
                  `thema_id` varchar(32) NOT NULL,
                  `user_id` varchar(32) NOT NULL,
                  `titel` varchar(255) NOT NULL,
                  `beschreibung` text NOT NULL,
                  `mkdate` int(20) NOT NULL,
                  `visible` tinyint(2) NOT NULL default '0',
                  PRIMARY KEY  (`artikel_id`),
                  KEY `visible` (`visible`,`mkdate`),
                  KEY `thema_id` (`thema_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC");
        $db->exec("CREATE TABLE IF NOT EXISTS `sb_visits` (
                  `object_id` char(32) NOT NULL default '',
                  `user_id` char(32) NOT NULL default '',
                  `type` enum('thema','artikel') NOT NULL default 'thema',
                  `last_visitdate` int(20) NOT NULL default '0',
                  PRIMARY KEY  (`object_id`,`user_id`),
                  KEY `user_id` (`user_id`,`last_visitdate`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC");

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

    public function down ()
    {

    }
}

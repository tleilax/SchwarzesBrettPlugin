<?php
class DisplayBadge extends Migration
{
    public function description ()
    {
        return _('Config-Eintrag für die Anzeige der ungesehenen Anzeigen in der Navigation anlegen');
    }

    public function up ()
    {
        $query = "INSERT IGNORE INTO `config`
                  (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`,
                   `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`)
                  VALUES (MD5(:config_id), '', :config_id, :value, '1', :type, 'global', 'SchwarzesBrettPlugin', '0', '0', UNIX_TIMESTAMP(), :comment, '', '')";
        $statement = DBManager::get()->prepare($query);

        $statement->bindValue(':config_id', 'BULLETIN_BOARD_DISPLAY_BADGE');
        $statement->bindValue(':value', 0);
        $statement->bindValue(':type', 'boolean');
        $statement->bindValue(':comment', 'Anzahl der ungelesen Anzeigen in der Navigation anzeigen');
        $statement->execute();
    }

    public function down ()
    {
        $query = "DELETE FROM `config` WHERE `config_id` IN (MD5('BULLETIN_BOARD_DISPLAY_BADGE'))";
        DBManager::get()->exec($query);
    }
}

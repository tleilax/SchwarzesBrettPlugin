<?php
class Blame extends Migration
{
    public function description ()
    {
        return 'config eintraege fuer blame funktion werden angelegt';
    }

    public function up ()
    {
        $query = "INSERT IGNORE INTO `config`
                  (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`,
                   `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`)
                  VALUES (MD5(:config_id), '', :config_id, :value, '1', :type, 'global', 'SchwarzesBrettPlugin', '0', '0', UNIX_TIMESTAMP(), :comment, '', '')";
        $statement = DBManager::get()->prepare($query);

        $statement->bindValue(':config_id', 'BULLETIN_BOARD_BLAME_RECIPIENTS');
        $statement->bindValue(':value', $GLOBALS['UNI_CONTACT']);
        $statement->bindValue(':type', 'string');
        $statement->bindValue(':comment', 'Mailadressen, an die die Nachricht geschickt werden soll');
        $statement->execute();

        $statement->bindValue(':config_id', 'BULLETIN_BOARD_ENABLE_BLAME');
        $statement->bindValue(':value', '1');
        $statement->bindValue(':type', 'boolean');
        $statement->bindValue(':comment', 'Blame Funktion aktivieren (Nutzer können Anzeigen melden)');
        $statement->execute();
    }

    public function down ()
    {
        $query = "DELETE FROM `config` WHERE `config_id` IN (MD5('BULLETIN_BOARD_BLAME_RECIPIENTS'), MD5('BULLETIN_BOARD_ENABLE_BLAME'))";
        DBManager::get()->exec($query);
    }
}

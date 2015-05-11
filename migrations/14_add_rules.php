<?php
class AddRules extends Migration
{
    public function description()
    {
        return 'Adds the config entry for the now editable rules of the bulletin board.';
    }
    
    public function up()
    {
        $query = "INSERT IGNORE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`,
                                               `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`)
                  VALUES (MD5(:id), '', :id, :value, '1', 'string',
                          'global', 'SchwarzesBrettPlugin', '0', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description, '', '')";
        $statement = DBManager::get()->prepare($query);

        $statement->bindValue(':id', 'BULLETIN_BOARD_RULES');
        $statement->bindValue(':value', $this->getRules());
        $statement->bindValue('description', 'Die angezeigten Regeln des Schwarzen Bretts');
        $statement->execute();
    }
    
    public function down()
    {
        DBManager::get()->exec("DELETE FROM `config` WHERE `config_id` = MD5('BULLETIN_BOARD_RULES')");
    }
    
    private function getRules()
    {
        $rules  = '!!!Allgemeine Hinweise:' . "\n";
        $rules .= '- Sie können nur in Themen eine Anzeige erstellen, in denen Sie die nötigen Rechte haben.' . "\n";
        $rules .= '- Mit der Suche werden sowohl Titel als auch Beschreibung aller Anzeigen durchsucht.' . "\n";
        $rules .= '- Sie können Ihre eigenen Anzeigen jederzeit nachträglich %%bearbeiten%% oder %%löschen%%.' . "\n";
        $rules .= '- Bitte stellen Sie Ihre Anzeigen in die richtige Kategorie ein. Damit das Schwarze Brett übersichtlich bleibt, %%löschen%% Sie bitte Ihre Anzeigen umgehend nach Abschluss/Verkauf.' . "\n";
        $rules .= '- **Bitte Artikel nur in %%eine%% Kategorie einstellen!**' . "\n";
        $rules .= '- **Kommerzielle Angebote sind __nicht__ erlaubt (dazu gehören auch solche Anzeigen die einen Link enthalten, der dem Einstellenden eine Provision verspricht). Sie werden gelöscht!**' . "\n";

        return $rules;
    }
}

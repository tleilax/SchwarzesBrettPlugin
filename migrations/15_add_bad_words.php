<?php
class AddBadWords extends Migration
{
    public function description()
    {
        return 'Adds the config entry for bad words that will lead to an '
             . 'information email to the defined email in '
             . 'BULLETIN_BOARD_BLAME_RECIPIENTS';
    }
    
    public function up()
    {
        $query = "INSERT IGNORE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`,
                                               `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`)
                  VALUES (MD5(:id), '', :id, :value, '1', 'string',
                          'global', 'SchwarzesBrettPlugin', '0', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description, '', '')";
        $statement = DBManager::get()->prepare($query);

        $statement->bindValue(':id', 'BULLETIN_BOARD_BAD_WORDS');
        $statement->bindValue(':value', '');
        $statement->bindValue('description', 'Liste von "verbotenen" Wörtern, bei denen der Support informiert wird, falls diese in einer Anzeige auftauchen (komma-separiert)');
        $statement->execute();
    }
    
    public function down()
    {
        DBManager::get()->exec("DELETE FROM `config` WHERE `config_id` = MD5('BULLETIN_BOARD_BAD_WORDS')");
    }
}

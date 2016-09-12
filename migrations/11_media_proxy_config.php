<?php
class MediaProxyConfig extends Migration
{
    public function description ()
    {
        return 'Config-Einträge für den Media-Proxy werden angelegt';
    }

    public function up ()
    {
        $query = "INSERT IGNORE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`,
                                               `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`)
                  VALUES (MD5(:id), '', :id, '0', '1', 'boolean',
                          'global', 'SchwarzesBrettPlugin', '0', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description, '', '')";
        $statement = DBManager::get()->prepare($query);

        $statement->bindValue(':id', 'BULLETIN_BOARD_MEDIA_PROXY');
        $statement->bindValue('description', 'Eigenen Media-Proxy aktivieren (bei Problemen mit http-Inhalten in https-Umgebungen)');
        $statement->execute();

        $statement->bindValue(':id', 'BULLETIN_BOARD_MEDIA_PROXY_CACHED');
        $statement->bindValue('description', 'Media-Proxy-Inhalte cachen');
        $statement->execute();
    }

    public function down ()
    {
        DBManager::get()->exec("DELETE FROM `config` WHERE `config_id` = MD5('BULLETIN_BOARD_MEDIA_PROXY')");
        DBManager::get()->exec("DELETE FROM `config` WHERE `config_id` = MD5('BULLETIN_BOARD_MEDIA_PROXY_CACHED')");
    }
}

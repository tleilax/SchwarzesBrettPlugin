<?php
class ConvertUserConfigToJson extends Migration
{
    public function up()
    {
        $query = "SELECT `userconfig_id`, `value`
                  FROM `user_config`
                  WHERE `field` = 'SCHWARZESBRETT_WIDGET_SETTINGS'";
        $statement = DBManager::get()->query($query);
        $data = $statement->fetchGrouped(PDO::FETCH_COLUMN);

        $query = "UPDATE `user_config`
                  SET `value` = :value
                  WHERE `userconfig_id` = :id";
        $statement = DBManager::get()->prepare($query);

        foreach ($data as $id => $value) {
            $value = unserialize($value);
            $value = json_encode($value);

            $statement->bindValue(':id', $id);
            $statement->bindValue(':value', $value);
            $statement->execute();
        }

        $query = "INSERT IGNORE INTO `config` (
                    `config_id`, `field`, `value`, `is_default`,
                    `type`, `range`, `description`, `comment`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    :id, 'SCHWARZESBRETT_WIDGET_SETTINGS', '[]', 1,
                    'array', 'user', 'Einstellungen für das Schwarze Brett-Widget', '',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', md5('SCHWARZESBRETT_WIDGET_SETTINGS'));
        $statement->execute();
    }

    public function down()
    {
        $query = "SELECT `userconfig_id`, `value`
                  FROM `user_config`
                  WHERE `field` = 'SCHWARZESBRETT_WIDGET_SETTINGS'";
        $statement = DBManager::get()->query($query);
        $data = $statement->fetchGrouped(PDO::FETCH_COLUMN);

        $query = "UPDATE `user_config`
                  SET `value` = :value
                  WHERE `userconfig_id` = :id";
        $statement = DBManager::get()->prepare($query);

        foreach ($data as $id => $value) {
            $value = json_decode($value);
            $value = serialize($value);

            $statement->bindValue(':id', $id);
            $statement->bindValue(':value', $value);
            $statement->execute();
        }

        $query = "DELETE FROM `config` WHERE `field` = 'SCHWARZESBRETT_WIDGET_SETTINGS'";
        DBManager::get()->exec($query);
    }
}

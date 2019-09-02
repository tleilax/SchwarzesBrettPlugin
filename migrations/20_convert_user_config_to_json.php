<?php
class ConvertUserConfigToJson extends Migration
{
    public function up()
    {
        if (class_exists('StudipVersion') && StudipVersion::newerThan('4.0')) {
            $query = "SELECT `range_id`, `value`
                      FROM `config_values`
                      WHERE `field` = 'SCHWARZESBRETT_WIDGET_SETTINGS'";
            $statement = DBManager::get()->query($query);
            $data = $statement->fetchGrouped(PDO::FETCH_COLUMN);

            $query = "UPDATE `config_values`
                      SET `value` = :value
                      WHERE `range_id` = :id";
            $statement = DBManager::get()->prepare($query);
        } else {
            $query = "SELECT `userconfig_id`, `value`
                      FROM `user_config`
                      WHERE `field` = 'SCHWARZESBRETT_WIDGET_SETTINGS'";
            $statement = DBManager::get()->query($query);
            $data = $statement->fetchGrouped(PDO::FETCH_COLUMN);

            $query = "UPDATE `user_config`
                      SET `value` = :value
                      WHERE `userconfig_id` = :id";
            $statement = DBManager::get()->prepare($query);
        }

        foreach ($data as $id => $value) {
            $value = unserialize($value);
            $value = json_encode($value);

            $statement->bindValue(':id', $id);
            $statement->bindValue(':value', $value);
            $statement->execute();
        }

        try {
            Config::get()->create('SCHWARZESBRETT_WIDGET_SETTINGS', [
                'value' => json_encode([]),
                'type' => 'array',
                'range' => 'user',
                'description' => 'Einstellungen für das Schwarze Brett-Widget',
            ]);
        } catch (Exception $e) {}
    }

    public function down()
    {
        if (class_exists('StudipVersion') && StudipVersion::newerThan('4.0')) {
            $query = "SELECT `range_id`, `value`
                      FROM `config_values`
                      WHERE `field` = 'SCHWARZESBRETT_WIDGET_SETTINGS'";
            $statement = DBManager::get()->query($query);
            $data = $statement->fetchGrouped(PDO::FETCH_COLUMN);

            $query = "UPDATE `config_values`
                      SET `value` = :value
                      WHERE `range_id` = :id";
            $statement = DBManager::get()->prepare($query);
        } else {
            $query = "SELECT `userconfig_id`, `value`
                      FROM `user_config`
                      WHERE `field` = 'SCHWARZESBRETT_WIDGET_SETTINGS'";
            $statement = DBManager::get()->query($query);
            $data = $statement->fetchGrouped(PDO::FETCH_COLUMN);

            $query = "UPDATE `user_config`
                      SET `value` = :value
                      WHERE `userconfig_id` = :id";
            $statement = DBManager::get()->prepare($query);
        }

        foreach ($data as $id => $value) {
            $value = json_decode($value);
            $value = serialize($value);

            $statement->bindValue(':id', $id);
            $statement->bindValue(':value', $value);
            $statement->execute();
        }

        Config::get()->delete('SCHWARZESBRETT_WIDGET_SETTINGS');
    }
}

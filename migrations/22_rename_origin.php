<?php
class RenameOrigin extends Migration
{
    public function up()
    {
        $query = "UPDATE `plugins`
                  SET `pluginpath` = 'UOL/SchwarzesBrettPlugin'
                  WHERE `pluginpath` = 'IBIT/SchwarzesBrettPlugin'";
        DBManager::get()->exec($query);

        $old_path = $GLOBALS['PLUGINS_PATH'] . '/IBIT/SchwarzesBrettPlugin';
        $uol_path = $GLOBALS['PLUGINS_PATH'] . '/UOL';
        $new_path = $GLOBALS['PLUGINS_PATH'] . '/UOL/SchwarzesBrettPlugin';

        if (file_exists($old_path)) {
            if (file_exists($new_path)) {
                // Plugin was uploaded and currently exists twice
                rmdirr($old_path);
            } else {
                // Plugin was updated in filesystem and exists only once
                if (!file_exists($uol_path)) {
                    mkdir($uol_path);
                }
                rename($old_path, $new_path);
            }
        }

        $query = "UPDATE `cronjobs_tasks`
                  SET `filename` = 'public/plugins_packages/UOL/SchwarzesBrettPlugin/classes/Cronjob.php'
                  WHERE `filename` = 'public/plugins_packages/IBIT/SchwarzesBrettPlugin/classes/Cronjob.php'";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "UPDATE `plugins`
                  SET `pluginpath` = 'IBIT/SchwarzesBrettPlugin'
                  WHERE `pluginpath` = 'UOL/SchwarzesBrettPlugin'";
        DBManager::get()->exec($query);

        $old_path = $GLOBALS['PLUGINS_PATH'] . '/UOL/SchwarzesBrettPlugin';
        $uol_path = $GLOBALS['PLUGINS_PATH'] . '/IBIT';
        $new_path = $GLOBALS['PLUGINS_PATH'] . '/IBIT/SchwarzesBrettPlugin';

        if (file_exists($old_path)) {
            if (!file_exists($uol_path)) {
                mkdir($uol_path);
            }
            rename($old_path, $new_path);
        }

        $query = "UPDATE `cronjobs_tasks`
                  SET `filename` = 'public/plugins_packages/IBIT/SchwarzesBrettPlugin/classes/Cronjob.php'
                  WHERE `filename` = 'public/plugins_packages/UOL/SchwarzesBrettPlugin/classes/Cronjob.php'";
        DBManager::get()->exec($query);
    }
}

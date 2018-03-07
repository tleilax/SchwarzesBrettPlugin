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
            if (!file_exists($uol_path)) {
                mkdir($uol_path);
            }
            rename($old_path, $new_path);
        }
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
    }
}

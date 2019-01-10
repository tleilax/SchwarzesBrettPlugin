<?php
class Localizations extends Migration
{
    public function up()
    {
        $query = "INSERT IGNORE INTO `i18n` (
                    `object_id`, `table`, `field`, `lang`, `value`
                  ) VALUES (
                    'rules', 'config', 'bulletinboard', 'en_GB', ?
                  )";
        DBManager::get()->execute($query, [implode("\n", [
            "!!!General information:",
            "- **Please place article only in %%one%% category!**",
            "- The search searches both in title and description of all advertisements.",
            "- You can %%edit%% or %%delete%% your own ads at any time.",
            "- Please place your ads in the right category. To keep the bulletin board clear, please %%delete%% your ads immediately after conclusion/sale.",
        ])]);
    }

    public function down()
    {
        $query = "DELETE FROM `i18n`
                  WHERE `object_id` = 'rules'
                    AND `table` = 'config'
                    AND `field` = 'bulletinboard'";
        DBManager::get()->exec($query);
    }
}

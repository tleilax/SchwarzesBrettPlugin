<?php
/**
 * @author  Jan-Hendrik Willms
 * @license GPL2 or any later version
 */
class AddTermsOfCategory extends Migration
{
    public function description()
    {
        return 'Adds disclaimer and terms to categories';
    }

    public function up()
    {
        $query = "ALTER TABLE `sb_themen`
                    ADD COLUMN `terms` TEXT NOT NULL DEFAULT '',
                    ADD COLUMN `display_terms_in_article` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                    ADD COLUMN `disclaimer` TEXT NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        $query = "ALTER TABLE `sb_themen`
                    DROP COLUMN `terms`,
                    DROP COLUMN `display_terms_in_article`,
                    DROP COLUMN `disclaimer`";
        DBManager::get()->exec($query);

        SimpleORMap::expireTableScheme();
    }
}

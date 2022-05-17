<?php
final class ExtendedDomainBlacklist extends Migration
{
    protected function up()
    {
        $query = "ALTER TABLE `sb_domain_blacklist`
                  ADD COLUMN `restriction` ENUM('usage', 'complete') COLLATE latin1_bin NOT NULL DEFAULT 'complete' AFTER `userdomain_id`";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "ALTER TABLE `sb_domain_blacklist`
                  DROP COLUMN `restriction`";
        DBManager::get()->exec($query);
    }
}

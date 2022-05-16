<?php
final class AddDomainBlacklist extends Migration
{
    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `sb_domain_blacklist` (
                      `userdomain_id` VARCHAR(32) COLLATE latin1_bin NOT NULL,
                      `mkdate` INT(11) UNSIGNED NOT NULL,
                      PRIMARY KEY (`userdomain_id`)
                  )";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "DROP TABLE IF EXISTS `sb_domain_blacklist`";
        DBManager::get()->exec($query);
    }
}

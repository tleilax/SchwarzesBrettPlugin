<?php
class AddUserdomains extends Migration
{
    public function up()
    {
        $query = "CREATE TABLE `sb_themen_userdomains` (
            `thema_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `userdomain_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            PRIMARY KEY (`thema_id`, `userdomain_id`)
        )";
        DBManager::get()->exec($query);

        $query = "CREATE OR REPLACE VIEW `sb_visible_topics` AS
                  SELECT aum.`user_id`, t.`thema_id`
                  FROM `auth_user_md5` AS aum
                  JOIN `sb_themen` AS t
                  LEFT JOIN `sb_themen_userdomains` AS stu
                    USING (`thema_id`)
                  LEFT JOIN `user_userdomains` AS uu0
                    ON uu0.`user_id` = aum.`user_id`
                  LEFT JOIN `user_userdomains` AS uu1
                    ON uu1.`user_id` = aum.`user_id`
                       AND uu1.`userdomain_id` = stu.`userdomain_id`
                  WHERE
                    -- Root may see everything
                    aum.`perms` = 'root'
                    -- No domains assigned to category
                    OR stu.`userdomain_id` IS NULL
                    -- User has no domains and null domain is assigned to category
                    OR (
                        uu0.`userdomain_id` IS NULL AND stu.`userdomain_id` = 'null'
                    )
                    -- User domain matches category domain
                    OR uu1.userdomain_id IS NOT NULL";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DROP VIEW `sb_visible_topics`";
        DBManager::get()->exec($query);

        $query = "DROP TABLE `sb_themen_userdomains`";
        DBManager::get()->exec($query);
    }
}

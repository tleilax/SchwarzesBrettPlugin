<?php
class ImageUploads extends Migration
{
    public function up()
    {
        Config::get()->create('BULLETIN_BOARD_ALLOW_FILE_UPLOADS', [
            'value'       => (int) false,
            'type'        => 'boolean',
            'range'       => 'global',
            'section'     => 'SchwarzesBrettPlugin',
            'description' => 'Bilder-Upload erlauben',
        ]);

        $query = "CREATE TABLE IF NOT EXISTS `sb_artikel_images` (
                    `image_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                    `artikel_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                    `position` SMALLINT(5) UNSIGNED DEFAULT 0,
                    `mkdate` INT(11) UNSIGNED NOT NULL,
                    `chdate` INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (`image_id`, `artikel_id`)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        Config::get()->delete('BULLETIN_BOARD_ALLOW_FILE_UPLOADS');

        $query = "DROP TABLE IF EXISTS `sb_artikel_images`";
    }
}

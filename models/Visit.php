<?php
namespace SchwarzesBrett;

use DBManager;
use SimpleORMAP;

/**
 * @property array $id
 * @property string $object_id
 * @property string $user_id
 * @property string $type
 * @property int $last_visitdate
 */
class Visit extends SimpleORMAP
{
    public static function configure($config = [])
    {
        $config['db_table'] = 'sb_visits';

        parent::configure($config);
    }

    public static function gc()
    {
        $query = "DELETE FROM sb_visits
                  WHERE user_id NOT IN (
                      SELECT user_id FROM auth_user_md5
                  )";
        DBManager::get()->exec($query);

        $query = "DELETE FROM sb_visits
                  WHERE type = 'artikel' AND object_id NOT IN (
                      SELECT artikel_id FROM sb_artikel
                  )";
        DBManager::get()->exec($query);

        $query = "DELETE FROM sb_visits
                  WHERE type = 'thema' AND object_id NOT IN (
                      SELECT thema_id FROM sb_themen
                  )";
        DBManager::get()->exec($query);
    }
}

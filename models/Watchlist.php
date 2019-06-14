<?php
namespace SchwarzesBrett;

use DBManager;
use PDO;
use SimpleORMap;

class Watchlist extends SimpleORMap
{
    use SORMAllowAccessTrait;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'sb_watchlist';

        $config['has_one']['user'] = [
            'class_name'  => 'SchwarzesBrett\\User',
            'foreign_key' => 'user_id',
        ];

        $config['has_one']['article'] = [
            'class_name'  => 'SchwarzesBrett\\Article',
            'foreign_key' => 'artikel_id',
        ];

        $config['registered_callbacks']['before_store'][] = 'checkUserRights';
        $config['registered_callbacks']['before_delete'][] = 'checkUserRights';

        parent::configure($config);
    }

    public static function getWatchedIds($user_id)
    {
        $query = "SELECT `artikel_id`
                  FROM `sb_watchlist`
                  WHERE `user_id` = :user_id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $user_id);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    public function checkUserRights()
    {
        if (self::$allow_access) {
            return;
        }

        if ($this->user_id === $GLOBALS['user']->id) {
            return true;
        }

        if (!is_object($GLOBALS['perm'])
            || !$GLOBALS['perm']->have_perm('root'))
        {
            throw new \AccessDeniedException('You may not alter this watchlist');
        }
    }
}

<?php
namespace SchwarzesBrett;

use AccessDeniedException;
use SimpleORMap;
use StudipLog;

/**
 * @property string $id
 * @property string $user_id
 * @property int $mkdate
 *
 * @property User $user
 */
class Blacklist extends SimpleORMap
{
    public static function configure($config = [])
    {
        $config['db_table'] = 'sb_blacklist';
        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id',
        ];

        $config['registered_callbacks']['after_create'][] = function ($item) {
            StudipLog::SB_BLACKLISTED($item->user_id);
        };
        $config['registered_callbacks']['after_delete'][] = function ($item) {
            StudipLog::SB_UNBLACKLISTED($item->user_id);
        };

        $config['registered_callbacks']['before_store'][] = 'checkUserRights';
        $config['registered_callbacks']['before_delete'][] = 'checkUserRights';

        parent::configure($config);
    }

    public function checkUserRights()
    {
        if (!is_object($GLOBALS['perm']) || !$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException('You may not alter the blacklist');
        }
    }
}

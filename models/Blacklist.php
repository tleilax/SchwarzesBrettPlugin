<?php
namespace SchwarzesBrett;

use SimpleORMap;

class Blacklist extends SimpleORMap
{
    public static function configure($config = [])
    {
        $config['db_table'] = 'sb_blacklist';
        $config['belongs_to']['user'] = [
            'class_name'  => 'SchwarzesBrett\\User',
            'foreign_key' => 'user_id',
        ];

        $config['registered_callbacks']['before_store'][] = 'checkUserRights';
        $config['registered_callbacks']['before_delete'][] = 'checkUserRights';

        parent::configure($config);
    }

    public function checkUserRights()
    {
        if (!is_object($GLOBALS['perm']) || !$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException('You may not alter this category');
        }
    }
}

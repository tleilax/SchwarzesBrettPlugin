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

        parent::configure($config);
    }
}

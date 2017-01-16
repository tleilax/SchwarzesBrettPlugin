<?php
namespace SchwarzesBrett;

use SimpleORMap;

class Blacklist extends SimpleORMap
{
    public static function configure($config = array())
    {
        $config['db_table'] = 'sb_blacklist';
        $config['belongs_to']['user'] = array(
            'class_name'  => 'SchwarzesBrett\\User',
            'foreign_key' => 'user_id',
        );

        parent::configure($config);
    }
}

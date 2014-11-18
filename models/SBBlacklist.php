<?php
class SBBlacklist extends SimpleORMap
{
    public static function configure($config = array())
    {
        $config['db_table'] = 'sb_blacklist';
        $config['belongs_to']['user'] = array(
            'class_name'  => 'SBUser',
            'foreign_key' => 'user_id',
        );

        parent::configure($config);
    }
}

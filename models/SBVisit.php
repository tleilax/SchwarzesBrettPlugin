<?php
class SBVisit extends SimpleORMAP
{
    public static function configure($config = array())
    {
        $config['db_table'] = 'sb_visits';

        parent::configure($config);
    }
}
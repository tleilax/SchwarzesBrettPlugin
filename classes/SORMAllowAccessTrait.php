<?php
namespace SchwarzesBrett;

trait SORMAllowAccessTrait
{
    protected static $allow_access = false;

    public static function allowAccess($state = true)
    {
        self::$allow_access = (bool) $state;
    }
}

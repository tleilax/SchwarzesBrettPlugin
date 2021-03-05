<?php
namespace SchwarzesBrett;

use NotificationCenter;
use PageLayout;
use StudIPPlugin;

abstract class Plugin extends StudIPPlugin
{
    public function __construct()
    {
        parent::__construct();

        foreach (get_class_methods($this) as $method) {
            if (!preg_match('/^on\w+(Did|Will)\w+$/', $method)) {
                continue;
            }

            $trigger = mb_substr($method, 2);
            NotificationCenter::addObserver($this, $method, $trigger);
        }
    }
}

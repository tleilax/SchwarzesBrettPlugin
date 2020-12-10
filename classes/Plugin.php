<?php
namespace SchwarzesBrett;

use NotificationCenter;
use PageLayout;
use StudIPPlugin;

abstract class Plugin extends StudIPPlugin
{
    use \TranslatablePluginTrait;

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

    public static function getTranslationDomain()
    {
        return 'schwarzes-brett';
    }

    public function getTranslationPath()
    {
        return $this->getPluginPath() . '/locale';
    }

    /**
     * Returns the plugin version from manifest.
     *
     * @return string version
     */
    public function getPluginVersion()
    {
        static $manifest = null;
        if ($manifest === null) {
            $manifest = $this->getMetadata();
        }
        return $manifest['version'];
    }
}

<?php
namespace SchwarzesBrett;

use PageLayout;
use UOL\Plugin as StudIPPlugin;

abstract class Plugin extends StudIPPlugin
{
    const GETTEXT_DOMAIN = 'schwarzes-brett';

    protected function legacyAssets()
    {
        // OpenGraphURLCollection was introduced in Stud.IP 3.4 which is the
        // first version that does not need it's own og handling
        if (class_exists('OpenGraphURLCollection')) {
            return;
        }

        $this->addStylesheet('assets/opengraph.less');
        PageLayout::addScript($this->getPluginURL() . '/assets/opengraph.js');
    }
}

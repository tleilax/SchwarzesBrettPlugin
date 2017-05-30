<?php
namespace SchwarzesBrett;

use PageLayout;
use StudIPPlugin;

abstract class Plugin extends StudIPPlugin
{
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

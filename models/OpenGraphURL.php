<?php
namespace SchwarzesBrett;

use OpenGraphURL as GlobalOpenGraphURL;
use URLHelper;

class OpenGraphURL extends GlobalOpenGraphURL
{
    protected static $proxy_url = null;

    public static function setProxyURL($url)
    {
        self::$proxy_url = $url;
    }

    public function render()
    {
        if (self::$proxy_url !== null) {
            $old_base = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
            $this['image'] = URLHelper::getURL(self::$proxy_url, array('url' => $this['image']));
            URLHelper::setBaseURL($old_base);
        }

        return parent::render();
    }
}

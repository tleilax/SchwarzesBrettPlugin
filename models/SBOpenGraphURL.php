<?php
class SBOpenGraphURL extends OpenGraphURL
{
    protected static $proxy_url = null;
    
    public static function setProxyURL($url)
    {
        self::$proxy_url = $url;
    }
    
    public function render()
    {
        if (self::$proxy_url !== null) {
            $this['image'] = URLHelper::getURL(self::$proxy_url, array('url' => $this['image']));
        }
            
        return parent::render();
    }
}

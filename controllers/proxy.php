<?php
class ProxyController extends StudipController
{
    CONST CACHE_GC_PROPABILITY = 1;
    const CACHE_DURATION = 604800; // 1 week = 7 * 24 * 60 * 60

    protected static $echo_headers = [
        'ETag', 'Cache-Control', 'Last-Modified', 'Expires',
    ];
    protected static $cache = null;

    public function index_action()
    {
        $url = Request::get('url');

        if (self::$cache === null) {
            if (Config::get()->BULLETIN_BOARD_MEDIA_PROXY_CACHED) {
                $dir = $GLOBALS['TMP_PATH'] . '/schwarzes-brett-proxy';
                self::$cache = new StudipFileCache(compact('dir'));

                if (mt_rand(0, 100) < self::CACHE_GC_PROPABILITY) {
                    self::$cache->purge();
                }
            } else {
                self::$cache = new StudipNullCache();
            }
        }

        $cache_key = md5($url);
        $cached    = self::$cache->read($cache_key);
        if ($cached === false) {
            $response = parse_link($url);
            if ($response['response_code'] == 200) {
                $response['content']       = file_get_contents($url);
                $response['last-modified'] = gmdate('D, d M Y H:i:s') . ' GMT';
                $response['expires']       = gmdate('D, d M Y H:i:s', time() + self::CACHE_DURATION) . ' GMT';
                $response['etag']          = $cache_key;
                $response['cache-control'] = 'no-transform,public,max-age=' . self::CACHE_DURATION;

                self::$cache->write($cache_key, serialize($response), self::CACHE_DURATION);
            }
        } else {
            $response = unserialize($cached);
        }

        $modified = true;
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $cache_key) {
            $modified = false;
        } elseif ($_SERVER['HTTP_IF_MODIFIED_SINCE'] && $_SERVER['HTTP_IF_MODIFIED_SINCE'] === $response['last-modified']) {
            $modified = false;
        }

        header_remove('Pragma');
        header_remove('Content-Length');
        foreach (self::$echo_headers as $header) {
            header_remove($header);
        }

        if ($modified) {
            $this->set_content_type($response['content-type']);
            $this->response->set_status($response['response_code']);
            foreach (self::$echo_headers as $header) {
                $key = strtolower($header);
                if (isset($response[$key])) {
                    $this->response->add_header($header, $response[$key]);
                }
            }
            $this->response->add_header('Content-Length', strlen($response['content']));
            $this->render_text($response['content']);
        } else {
            $this->response->set_status(304);
            $this->render_nothing();
        }
    }
}

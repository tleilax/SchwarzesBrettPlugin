<?php
/**
 * still a bit beta.
 */
class SchwarzesBrettAPI extends StudIPPlugin implements APIPlugin
{
    protected static $mapping = array(
        'user_id'              => ':SBCategory',
        'artikel_id'           => 'article_id',
        'thema_id'             => 'category_id',
        'titel'                => 'title',
        'beschreibung'         => 'description_original',
        'id'                   => false,
        'publishable'          => '#bool',
        'visible'              => '#bool',
        'mkdate'               => '#int',
        'chdate'               => '#int',
        'expires'              => '#int',
        'views'                => '#int',
        'description_original' => '#formatReady:description',
    );

    public function describeRoutes()
    {
        return array();
    }

    public static function before()
    {
        require_once 'bootstrap.inc.php';
    }

    public function routes(&$router)
    {
        $plugin = $this;

        $router->get('/schwarzes-brett/categories', function () use ($router, $plugin) {
            $categories = SBCategory::findByVisible(1, 'ORDER BY titel COLLATE latin1_german1_ci ASC');
            $categories = $plugin->flatten($categories);

            $router->render(compact('categories'));
        });

        $router->get('/schwarzes-brett/category/:id', function ($id) use ($router, $plugin) {
            $category = SBCategory::find($id);
            if (!$category) {
                $router->halt(404);
            }
            $category = $plugin->flatten($category);

            $router->render(compact('category'));
        });

        $router->get('/schwarzes-brett/category/:id/articles', function ($id) use ($router, $plugin) {
            $category = SBCategory::find($id);
            if (!$category) {
                $router->halt(404);
            }

            $offset = Request::int('offset', 0);
            $limit  = Request::int('limit', 20) ?: 20;

            $total    = $category->articles->count();
            $articles = $plugin->flatten($category->articles->limit($offset, $limit));

            $pagination = $router->paginate($total, $offset, $limit, '/schwarzes-brett/category', $id, 'articles');

            $router->render(compact('articles', 'pagination'));
        });

        $router->post('/schwarzes-brett/articles', function () use ($router) {
            if (SBBlacklist::find($GLOBALS['user']->id)) {
                $router->halt(403, 'You are blacklisted');
            }
            // TODO Create article
        });

        $router->get('/schwarzes-brett/article/:id', function ($id) use ($router, $plugin) {
            $article = SBArticle::find($id);
            if (!$article) {
                $router->halt(404);
            }
            $article = $plugin->flatten($article);

            $router->render(compact('article'));
        });

        $router->post('/schwarzes-brett/article/:id', function ($id) use ($router, $plugin) {
            $article = SBArticle::find($id);
            if (!$article) {
                $router->halt(404);
            }
            if ($article->user_id !== $GLOBALS['user']->id && !$GLOBALS['user']->perms === 'root') {
                $router->halt(403);
            }
            // TODO Update article
        });

        $router->delete('/schwarzes-brett/article/:id', function ($id) use ($router) {
            $article = SBArticle::find($id);
            if (!$article) {
                $router->halt(404);
            }
            if ($article->user_id !== $GLOBALS['user']->id && !$GLOBALS['user']->perms === 'root') {
                $router->halt(403);
            }
            $article->delete();
            $this->halt(204);
        });

        $router->post('/schwarzes-brett/article/:id/visit', function ($id) use ($router) {
            $article = SBArticle::find($id);
            if (!$article) {
                $router->halt(404);
            }
            $article->visit();
            $router->halt(204);
        });
    }

    public function flatten($item)
    {
        if (is_object($item) && $item instanceof SimpleCollection) {
            $item = $item->getArrayCopy();
        }
        if (is_array($item)) {
            return array_map(__METHOD__, $item);
        }
        $type  = get_class($item);
        $array = $item->toArray();
        
        foreach (self::$mapping as $key => $value) {
            list($value, $target) = explode(':', $value);
            $value = $value ?: false;

            if (!isset($array[$key])) {
                continue;
            }

            if ($value === '#bool') {
                $array[$key] = (bool)$array[$key];
            } elseif ($value === '#int') {
                $array[$key] = (int)$array[$key];
            } elseif ($value === '#formatReady') {
                $array[$target ?: $key] = formatReady($array[$key]);
            } elseif (!$target || $target === $type) {
                if ($value !== false) {
                    $array[$value] = $array[$key];
                    unset($array[$key]);
                } else {
                    unset($array[$key]);
                }
            }
        }
        
        return $array;
    }
}
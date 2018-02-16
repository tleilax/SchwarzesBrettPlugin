<?php
namespace SchwarzesBrett;

use Request;
use RESTAPI\RouteMap as GlobalRouteMap;
use SimpleCollection;

class RouteMap extends GlobalRouteMap
{
    protected static $mapping = [
        'user_id'              => ':SchwarzesBrett\\Category',
        'artikel_id'           => 'article_id',
        'thema_id'             => 'category_id',
        'titel'                => 'title',
        'beschreibung'         => 'description_original',
        'id'                   => false,
        'publishable'          => '#bool',
        'visible'              => '#bool',
        'duration'             => '#int',
        'views'                => '#int',

        'mkdate'               => '#date',
        'chdate'               => '#date',
        'expires'              => '#date',

        'description_original'     => '#formatReady:description',
        'display_terms_in_article' => '#bool',
    ];

    /**
     * @get /schwarzes-brett/categories
     */
    public function getCategories()
    {
        $categories = Category::findByVisible(1, 'ORDER BY titel ASC');
        $categories = $this->flatten($categories);
        return compact('categories');
    }

    /**
     * @get /schwarzes-brett/category/:id
     */
    public function getCategory($id)
    {
        $category = Category::find($id);
        if (!$category) {
            $this->notFound();
        }
        $category = $this->flatten($category);

        return compact('category');
    }

    /**
     * @get /schwarzes-brett/category/:id/articles
     */
    public function getArticlesByCategory($id)
    {
        $category = Category::find($id);
        if (!$category) {
            $this->notFound();
        }

        $offset = Request::int('offset', 0);
        $limit  = Request::int('limit', 20) ?: 20;

        $total    = $category->articles->count();
        $articles = $this->flatten($category->articles->limit($this->offset, $this->limit));

        return $this->paginated($articles, $total, compact('id'));
    }

    /**
     * @post /schwarzes-brett/articles
     */
    public function createArticle()
    {
        if (Blacklist::find($GLOBALS['user']->id)) {
            $this->halt(403, 'You are blacklisted');
        }
        // TODO Create article
    }

    /**
     * @get /schwarzes-brett/article/:id
     */
    public function getArticle($id)
    {
        $article = Article::find($id);
        if (!$article) {
            $this->notFound();
        }
        $article = $this->flatten($article);

        return compact('article');
    }

    /**
     * @patch /schwarzes-brett/article/:id
     */
    public function updateArticle($id)
    {
        $article = Article::find($id);
        if (!$article) {
            $this->notFound();
        }
        if ($article->user_id !== $GLOBALS['user']->id
            && !$GLOBALS['user']->perms === 'root')
        {
            $this->halt(403);
        }
        // TODO Update article
    }

    /**
     * @delete /schwarzes-brett/article/:id
     */
    public function removeArticle($id)
    {
        $article = Article::find($id);
        if (!$article) {
            $this->notFound();
        }
        if ($article->user_id !== $GLOBALS['user']->id
            && !$GLOBALS['user']->perms === 'root')
        {
            $this->halt(403);
        }

        $article->delete();
        $this->halt(204);
    }

    /**
     * @post /schwarzes-brett/article/:id/visit
     */
    public function visitArticle($id)
    {
        $article = Article::find($id);
        if (!$article) {
            $this->notFound();
        }

        $article->visit();
        $this->halt(204);
    }

    protected function flatten($item)
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
            } elseif ($value === '#date') {
                $array[$key] = date('c', $array[$key]);
            } elseif ($value === '#formatReady') {
                $array[$target ?: $key] = formatReady($array[$key]);
            } elseif (!$target || $target === $type) {
                if ($value !== false) {
                    $array[$value] = $array[$key];
                }
                unset($array[$key]);
            }
        }

        return $array;
    }
}

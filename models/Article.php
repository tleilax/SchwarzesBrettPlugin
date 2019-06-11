<?php
namespace SchwarzesBrett;

use AccessDeniedException;
use Config;
use DBManager;
use FileRef;
use PDO;
use SimpleORMap;
use SimpleORMapCollection;
use StudipCacheFactory;
use StudipFormat;
use StudipLog;

class Article extends SimpleORMap
{
    use SORMAllowAccessTrait;

    private static $watched = [];

    public static function configure($config = [])
    {
        $config['db_table'] = 'sb_artikel';
        $config['has_many']['visits'] = [
            'class_name'        => 'SchwarzesBrett\\Visit',
            'assoc_foreign_key' => 'object_id',
            'on_delete'         => 'delete',
        ];
        $config['belongs_to']['category'] = [
            'class_name'  => 'SchwarzesBrett\\Category',
            'foreign_key' => 'thema_id',
        ];
        $config['belongs_to']['user'] = [
            'class_name'  => 'SchwarzesBrett\\User',
            'foreign_key' => 'user_id',
        ];

        $config['additional_fields']['views'] = [
            'get' => function ($object) {
                $query = "SELECT COUNT(*)
                          FROM sb_visits
                          WHERE object_id = :id AND type = 'artikel'";
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':id', $object->id);
                $statement->execute();
                return $statement->fetchColumn() ?: 0;
            }
        ];
        $config['additional_fields']['new'] = [
            'get' => function ($object) {
                $query = "SELECT 1
                          FROM sb_artikel AS a
                          LEFT JOIN sb_visits AS v0 ON v0.object_id = a.artikel_id AND v0.user_id = :user_id
                          LEFT JOIN sb_visits AS v1 ON a.thema_id = v1.object_id AND v1.user_id = :user_id
                          WHERE a.artikel_id = :id
                            AND (v0.object_id IS NOT NULL
                             OR a.user_id = :user_id
                             OR a.mkdate < v1.last_visitdate)";
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':id', $object->id);
                $statement->bindValue(':user_id', $GLOBALS['user']->id);
                $statement->execute();
                return $statement->fetchColumn() === false;
            },
        ];

        $config['additional_fields']['watched'] = [
            'get' => function (Article $article) {
                $user_id = $GLOBALS['user']->id;

                if (!isset(self::$watched['user_id'])) {
                    self::$watched[$user_id] = Watchlist::getWatchedIds($user_id);
                }

                return in_array($article->id, self::$watched[$user_id]);
            },
        ];

        $config['additional_fields']['images'] = [
            'get' => function (Article $article) {
                return SimpleORMapCollection::createFromArray(
                    ArticleImage::findByArtikel_id($article->id, 'ORDER BY position ASC')
                );
            },
        ];

        $config['registered_callbacks']['before_store'][] = 'checkUserRights';
        $config['registered_callbacks']['before_delete'][] = 'checkUserRights';

        $config['registered_callbacks']['after_create'][] = function (Article $article) {
            StudipLog::log('SB_ARTICLE_CREATED', $article->category->id, null, $article->titel);
        };
        $config['registered_callbacks']['after_delete'][] = function (Article $article) {
            ArticleImage::deleteBySQL('artikel_id = ?', [$article->id]);

            WatchList::allowAccess(true);
            Watchlist::deleteBySQL('artikel_id = ?', [$article->id]);
            WatchList::allowAccess(false);

            StudipLog::log('SB_ARTICLE_DELETED', $article->category->id, null, $article->titel);
        };

        $config['default_values']['duration'] = Config::Get()->BULLETIN_BOARD_DURATION;

        $config['registered_callbacks']['before_store'][] = function ($article) {
            if (!User::get()->mayPostTo($article->category)) {
                throw new AccessDeniedException('You may not post to this category');
            }
        };

        parent::configure($config);
    }

    public static function countNew($category_id = null)
    {
        $cache_hash = "/schwarzes-brett/counts/new/{$GLOBALS['user']->id}";
        if ($category_id) {
            $cache_hash .= "/{$category_id}";
        }

        $cache = StudipCacheFactory::getCache();
        $count = $cache->read($cache_hash);
        if ($count === false) {
            $query = "SELECT COUNT(*)
                      FROM sb_artikel AS a
                      JOIN auth_user_md5 USING (user_id)
                      LEFT JOIN sb_visits AS v0 ON v0.object_id = a.artikel_id AND v0.user_id = :user_id
                      LEFT JOIN sb_visits AS v1 ON v1.object_id = a.thema_id AND v1.user_id = :user_id
                      WHERE a.expires > UNIX_TIMESTAMP()
                         AND a.visible = 1
                         AND a.user_id != :user_id
                         AND v0.object_id IS NULL
                         AND (v1.last_visitdate IS NULL OR a.mkdate > v1.last_visitdate)
                         AND a.thema_id = IFNULL(:category_id, thema_id)";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':user_id', $GLOBALS['user']->id);
            $statement->bindValue(':category_id', $category_id);
            $statement->execute();
            $count = $statement->fetchColumn();

            $cache->write($cache_hash, $count ?: 0, 60); // Store in cache for a minute
        }

        return $count;
    }

    public static function findValidByCategoryId($category_id)
    {
        $condition = "JOIN auth_user_md5 USING (user_id)
                      WHERE sb_artikel.thema_id = :category_id
                        AND sb_artikel.expires > UNIX_TIMESTAMP()
                      ORDER BY sb_artikel.mkdate DESC";
        return self::findBySQL($condition, [':category_id' => $category_id]);
    }

    public static function findValidByUserId($user_id)
    {
        return self::findBySQL("user_id = :user_id AND expires > UNIX_TIMESTAMP() ORDER BY mkdate DESC", [':user_id' => $user_id]);
    }

    public static function findVisibleByUserId($user_id)
    {
        return self::findBySQL("user_id = :user_id AND visible = 1 AND expires > UNIX_TIMESTAMP() ORDER BY mkdate DESC", [':user_id' => $user_id]);
    }

    public static function findVisibleByCategoryId($category_id)
    {
        $condition = "JOIN auth_user_md5 USING (user_id)
                      WHERE sb_artikel.thema_id = :category_id
                        AND (sb_artikel.visible = 1 OR sb_artikel.user_id = :user_id)
                        AND sb_artikel.expires > UNIX_TIMESTAMP()
                      ORDER BY sb_artikel.mkdate DESC";
        return self::findBySQL($condition, [':category_id' => $category_id, ':user_id' => $GLOBALS['user']->id]);
    }

    public static function findNewByCategoryId($category_id)
    {
        $visit = Visit::findOneBySQL(
            "object_id = :category_id AND user_id = :user_id AND type = 'thema'",
            [':category_id' => $category_id, ':user_id' => $GLOBALS['user']->id]
        );
        $last_visit = $visit
                    ? $visit->last_visitdate
                    : 0;

        $query = "SELECT a.artikel_id
                  FROM sb_artikel AS a
                  JOIN auth_user_md5 AS aum USING (user_id)
                  LEFT JOIN sb_visits AS v ON v.object_id = a.artikel_id AND v.user_id = :user_id
                  WHERE v.object_id IS NULL
                    AND a.thema_id = :category_id
                    AND a.visible = 1
                    AND a.mkdate > :last_visit
                  ORDER BY mkdate DESC";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $GLOBALS['user']->id);
        $statement->bindValue(':category_id', $category_id);
        $statement->bindValue(':last_visit', $last_visit);
        $statement->execute();

        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        return self::findMany($ids);
    }

    public static function findNewest($limit, $categories = false)
    {
        $query  = "JOIN auth_user_md5 USING (user_id)
                   WHERE sb_artikel.visible = 1
                     AND sb_artikel.expires > UNIX_TIMESTAMP()
                   ORDER BY sb_artikel.mkdate DESC
                   LIMIT " . (int)$limit;
        $params = [];

        if ($categories !== false && !empty($categories)) {
            $query  = "JOIN auth_user_md5 USING (user_id)
                       WHERE sb_artikel.visible = 1
                         AND expires > UNIX_TIMESTAMP()
                         AND sb_artikel.thema_id IN (:categories)
                      ORDER BY sb_artikel.mkdate DESC
                      LIMIT " . (int)$limit;
            $params = [':categories' => $categories];
        }

        return self::findBySQL($query, $params);
    }

    public static function findPublishable($category_id = null)
    {
        $query = "SELECT a.artikel_id
                  FROM sb_artikel AS a
                  JOIN auth_user_md5 USING (user_id)
                  JOIN sb_themen AS t USING (thema_id)
                  WHERE thema_id = IFNULL(:category_id, thema_id)
                    AND expires > UNIX_TIMESTAMP()
                    AND a.visible = 1
                    AND t.visible = 1
                    AND a.publishable = 1
                    AND t.publishable = 1";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':category_id', $category_id);
        $statement->bindValue(':expire', Config::get()->BULLETIN_BOARD_DURATION * 24 * 60 * 60);
        $statement->execute();

        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        return self::findMany($ids, "ORDER BY mkdate DESC");
    }

    public function visit()
    {
        $visit = Visit::find([$this->id, $GLOBALS['user']->id]);
        if (!$visit) {
            $visit = new Visit();
            $visit->object_id = $this->id;
            $visit->user_id   = $GLOBALS['user']->id;
            $visit->type      = 'artikel';
        }
        $visit->last_visitdate = time();
        $visit->store();
    }

    public function resetCreation()
    {
        $query = "UPDATE `sb_artikel`
                  SET `mkdate` = UNIX_TIMESTAMP(),
                      `chdate` = UNIX_TIMESTAMP(),
                      `expires` = UNIX_TIMESTAMP(NOW() + INTERVAL `duration` DAY)
                  WHERE `artikel_id` = ?";
        DBManager::get()->execute($query, [$this->id]);
    }

    public function findDuplicates()
    {
        $query = "SELECT DISTINCT artikel_ids FROM (
                      SELECT GROUP_CONCAT(artikel_id) AS artikel_ids, COUNT(*) AS dupe_count
                      FROM sb_artikel
                      WHERE expires >= UNIX_TIMESTAMP()
                      GROUP BY titel
                      HAVING COUNT(*) > 1 AND COUNT(DISTINCT user_id) = 1

                      UNION

                      SELECT GROUP_CONCAT(artikel_id) AS artikel_ids, COUNT(*) AS dupe_count
                      FROM sb_artikel
                      WHERE expires >= UNIX_TIMESTAMP()
                      GROUP BY beschreibung
                      HAVING COUNT(*) > 1 AND COUNT(DISTINCT user_id) = 1
                  ) AS temp
                  ORDER BY dupe_count DESC";
        $statement = DBManager::get()->query($query);

        $duplicates = [];
        while ($ids = $statement->fetchColumn()) {
            $ids = explode(',', $ids);

            $articles = Article::findMany($ids, 'ORDER BY mkdate DESC');
            $user_id  = $articles[0]->user_id;

            if (!isset($duplicates[$user_id])) {
                $duplicates[$user_id] = [];
            }

            foreach ($articles as $article) {
                if (!isset($duplicates[$user_id][$article->id])) {
                    $duplicates[$user_id][$article->id] = $article;
                }
            }
        }
        foreach ($duplicates as $user_id => $articles) {
            $duplicates[$user_id] = array_values($articles);
        }

        return $duplicates;
    }

    public function addImage(FileRef $ref, $position = null)
    {
        $thru = new ArticleImage();
        $thru->artikel_id = $this->id;
        $thru->image_id   = $ref->id;
        $thru->position   = $position;
        $thru->store();

        return $thru;
    }

    public function removeImage(FileRef $ref)
    {
        $thru = ArticleImage::deleteBySQL(
            'artikel_id = ? AND image_id = ?',
            [$this->id, $ref->id]
        );

        ArticleImage::gc($this->id);
    }

    public static function search($needle)
    {
        $query = "SELECT artikel_id
                  FROM sb_artikel
                  JOIN auth_user_md5 USING (user_id)
                  WHERE (sb_artikel.visible = 1 OR user_id = :user_id)
                    AND (
                        sb_artikel.titel LIKE CONCAT('%', :needle, '%')
                        OR sb_artikel.beschreibung LIKE CONCAT('%', :needle, '%')
                    )";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':needle', $needle);
        $statement->bindValue(':user_id', $GLOBALS['user']->id);
        $statement->execute();

        $article_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        return Article::findMany($article_ids, 'ORDER BY mkdate DESC');
    }

    public static function groupByCategory($articles)
    {
        $categories = [];
        foreach ($articles as $article) {
            $category = $article->category;
            if (!isset($categories[$category->id])) {
                $categories[$category->id] = [
                    'titel'    => $category->titel,
                    'articles' => [],
                ];
            }
            $categories[$category->id]['articles'][] = $article;
        }
        return $categories;
    }

    public static function markup($needle, $subject = null)
    {
        if (!$needle) {
            return $subject;
        }

        $replacer = function ($matches) {
            return sprintf('<span class="sb-highlighted">%s</span>', $matches[0]);
        };

        $needle = preg_quote($needle, '/');
        $needle = preg_replace_callback('/[a-z]/i', function ($match) {
            return sprintf('[%s%s]', strtolower($match[0]), strtoupper($match[0]));
        }, $needle);

        if ($subject) {
            return preg_replace_callback("/{$needle}/", $replacer, $subject);
        } else {
            StudipFormat::addStudipMarkup('sb-highlight', $needle, false, function ($markup, $matches, $contents) use ($replacer) {
                return $replacer($matches);
            });
        }
    }

    public function mayEdit($user_or_id = null)
    {
        if (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('root')) {
            return true;
        }

        if ($this->isNew()) {
            return true;
        }

        if ($user_or_id === null) {
            $user_or_id = $GLOBALS['user']->id;
        }
        if (is_object($user_or_id)) {
            $user_or_id = $user_or_id->id;
        }

        return $this->user_id === $user_or_id;
    }

    public function checkUserRights()
    {
        if (self::$allow_access) {
            return;
        }

        if (!$this->mayEdit()) {
            throw new AccessDeniedException('You may not alter this article');
        }
    }
}

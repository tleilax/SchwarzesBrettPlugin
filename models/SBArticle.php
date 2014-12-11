<?php
class SBArticle extends SimpleORMap
{
    public static function configure($config = array())
    {
        $config['db_table'] = 'sb_artikel';
        $config['has_many']['visits'] = array(
            'class_name'  => 'SBVisit',
            'foreign_key' => 'object_id',
            'on_delete'   => 'delete',
        );
        $config['belongs_to']['category'] = array(
            'class_name'  => 'SBCategory',
            'foreign_key' => 'thema_id',
        );
        $config['belongs_to']['user'] = array(
            'class_name'  => 'SBUser',
            'foreign_key' => 'user_id',
        );
        $config['additional_fields']['views'] = array(
            'get' => function ($object) {
                $query = "SELECT COUNT(*)
                          FROM sb_visits
                          WHERE object_id = :id AND type = 'artikel'";
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':id', $object->id);
                $statement->execute();
                return $statement->fetchColumn() ?: 0;
            }
        );
        $config['additional_fields']['new'] = array(
            'get' => function ($object) {
                $query = "SELECT 1
                          FROM sb_artikel AS a
                          LEFT JOIN sb_visits AS v0 ON v0.object_id = a.artikel_id AND v0.user_id = :user_id
                          LEFT JOIN sb_visits AS v1 ON a.thema_id = v1.object_id AND v1.user_id = :user_id
                          WHERE a.artikel_id = :id
                            AND (v0.object_id IS NOT NULL
                             OR a.mkdate < v1.last_visitdate)";
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':id', $object->id);
                $statement->bindValue(':user_id', $GLOBALS['user']->id);
                $statement->execute();
                return $statement->fetchColumn() === false;
            }
        );

        parent::configure($config);
    }
    
    public static function findValidByCategoryId($category_id)
    {
        return self::findBySQL("thema_id = :category_id AND expires > UNIX_TIMESTAMP() ORDER BY mkdate DESC", array(':category_id' => $category_id));
    }
    
    public static function findValidByUserId($user_id)
    {
        return self::findBySQL("user_id = :user_id AND expires > UNIX_TIMESTAMP() ORDER BY mkdate DESC", array(':user_id' => $user_id));
    }
    
    public static function findVisibleByCategoryId($category_id)
    {
        return self::findBySQL("thema_id = :category_id AND (visible = 1 OR user_id = :user_id) AND expires > UNIX_TIMESTAMP() ORDER BY mkdate DESC", array(':category_id' => $category_id, ':user_id' => $GLOBALS['user']->id));
    }

    public static function findNewByCategoryId($category_id)
    {
        $visit = SBVisit::findOneBySQL("object_id = :category_id AND user_id = :user_id AND type = 'thema'", 
                                       array(':category_id' => $category_id, ':user_id' => $GLOBALS['user']->id));
        $last_visit = $visit
                    ? $visit->last_visitdate
                    : 0;

        $query = "SELECT a.artikel_id
                  FROM sb_artikel AS a
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
        $query  = 'visible = 1 AND expires > UNIX_TIMESTAMP() ORDER BY mkdate DESC LIMIT ' . (int)$limit;
        $params = array();
        
        if ($categories !== false && !empty($categories)) {
            $query  = 'visible = 1 AND expires > UNIX_TIMESTAMP() AND thema_id IN (:categories) ORDER BY mkdate DESC LIMIT ' . (int)$limit;
            $params = array(':categories' => $categories);
        }

        return self::findBySQL($query, $params);
    }
    
    public static function findPublishable($category_id = null)
    {
        $query = "SELECT a.artikel_id
                  FROM sb_artikel AS a
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
        $visit = SBVisit::find(array($this->id, $GLOBALS['user']->id));
        if (!$visit) {
            $visit = new SBVisit();
            $visit->object_id = $this->id;
            $visit->user_id   = $GLOBALS['user']->id;
            $visit->type      = 'artikel';
        }
        $visit->last_visitdate = time();
        $visit->store();
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

        $duplicates = array();
        while ($ids = $statement->fetchColumn()) {
            $ids = explode(',', $ids);

            $articles = SBArticle::findMany($ids, 'ORDER BY mkdate DESC');
            $user_id  = $articles[0]->user_id;
            
            if (!isset($duplicates[$user_id])) {
                $duplicates[$user_id] = array();
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
    
    public static function search($needle)
    {
        $query = "SELECT artikel_id
                  FROM sb_artikel
                  WHERE titel LIKE CONCAT('%', :needle, '%')";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':needle', $needle);
        $statement->execute();

        $article_ids = $statement->fetchAll(PDO::FETCH_COLUMN);
        
        return SBArticle::findMany($article_ids, 'ORDER BY mkdate DESC');
    }
    
    public static function groupByCategory($articles)
    {
        $categories = array();
        foreach ($articles as $article) {
            $category = $article->category;
            if (!isset($categories[$category->id])) {
                $categories[$category->id] = array(
                    'titel'    => $category->titel,
                    'articles' => array(),
                );
            }
            $categories[$category->id]['articles'][] = $article;
        }
        return $categories;
    }
}
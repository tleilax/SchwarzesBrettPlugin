<?php
namespace SchwarzesBrett;

use DBManager;
use PDO;
use SimpleORMap;
use UserDomain;

class Category extends SimpleORMap
{
    public static function configure($config = [])
    {
        $config['db_table'] = 'sb_themen';
        $config['has_many']['articles'] = [
            'class_name' => 'SchwarzesBrett\\Article',
            'assoc_func' => 'findValidByCategoryId',
            'assoc_foreign_key' => 'thema_id',
        ];
        $config['has_many']['visible_articles'] = [
            'class_name' => 'SchwarzesBrett\\Article',
            'assoc_func' => 'findVisibleByCategoryId',
            'assoc_foreign_key' => 'thema_id',
        ];
        $config['has_many']['new_articles'] = [
            'class_name' => 'SchwarzesBrett\\Article',
            'assoc_func' => 'findNewByCategoryId',
            'assoc_foreign_key' => 'thema_id',
        ];
        $config['additional_fields']['new'] = [
            'get' => function ($object) {
                return count($object->new_articles) > 0;
            }
        ];

        $config['additional_fields']['domains'] = true;

        parent::configure($config);
    }

    public static function VisitAll($category_id = null, $user_id = null)
    {
        $query = "INSERT INTO sb_visits (object_id, user_id, type, last_visitdate)
                  SELECT thema_id, :user_id, 'thema', UNIX_TIMESTAMP()
                    FROM sb_themen
                  WHERE thema_id = IFNULL(:category_id, thema_id)
                  ON DUPLICATE KEY UPDATE last_visitdate = UNIX_TIMESTAMP()";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $user_id ?: $GLOBALS['user']->id);
        $statement->bindValue(':category_id', $category_id);
        $statement->execute();
    }

    private $domains = null;

    public function getDomains()
    {
        if ($this->domains === null) {
            $query = "SELECT `userdomain_id`
                      FROM `sb_themen_userdomains`
                      WHERE `thema_id` = :id";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':id', $this->id);
            $statement->execute();

            $this->domains = $statement->fetchAll(PDO::FETCH_COLUMN);
        }

        return $this->domains;
    }

    public function setDomains($domains)
    {
        $this->domains = $domains;
    }

    public function storeDomains($id, array $domains)
    {
        $query = "INSERT IGNORE INTO `sb_themen_userdomains` (
                    `thema_id`, `userdomain_id`
                  ) VALUES (
                    :category_id, :domain_id
                  )";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':category_id', $id);

        foreach ($domains as $domain_id) {
            $statement->bindValue(':domain_id', $domain_id);
            $statement->execute();
        }

        $query = "DELETE FROM `sb_themen_userdomains`
                  WHERE `thema_id` = :category_id
                    AND `userdomain_id` NOT IN (:domain_ids)";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':category_id', $id);
        $statement->bindValue(':domain_ids', $domains ?: '');
        $statement->execute();
    }

    public function removeDomains($id)
    {
        $query = "DELETE FROM `sb_themen_userdomains`
                  WHERE `thema_id` = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $id);
        $statement->execute();
    }

    public function store()
    {
        if ($result = parent::store()) {
            $this->storeDomains($this->id, $this->domains);
        }
        return $result;
    }

    public function delete()
    {
        $id = $this->id;
        if ($result = parent::delete()) {
            $this->removeDomains($id);
        }

        return $result;
    }

    public function isVisible($user_id = null)
    {
        $user_id = $user_id ?: $GLOBALS['user']->id;

        // Permission is not sufficient
        if (!$GLOBALS['perm']->have_perm($this->perm, $user_id)) {
            return false;
        }

        $query = "SELECT 1
                  FROM `sb_visible_topics`
                  WHERE `user_id` = :user_id
                    AND `thema_id` = :category_id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':category_id', $this->id);
        $statement->execute();

        return (bool) $statement->fetchColumn();
    }
}

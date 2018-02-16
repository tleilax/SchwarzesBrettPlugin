<?php
namespace SchwarzesBrett;

use DBManager;
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

    public function isVisible($user_id = null)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        if (!$GLOBALS['perm']->have_perm($this->perm, $user_id)) {
            return false;
        }
        if ($GLOBALS['perm']->have_perm("root", $user_id)) {
            return true;
        } elseif ($this->domains === "all") {
            return true;
        } else {
            $domains = explode(",", $this->domains);
            $mydomains = UserDomain::getUserDomainsForUser($user_id);
            if (!$mydomains && in_array("null", $domains)) {
                return true;
            }
            foreach ($mydomains as $mydomain) {
                if (in_array($mydomain->getID(), $domains)) {
                    return true;
                }
            }
        }
        return false;
    }
}

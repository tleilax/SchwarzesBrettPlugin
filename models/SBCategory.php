<?php
class SBCategory extends SimpleORMap
{
    public static function configure($config = array())
    {
        $config['db_table'] = 'sb_themen';
        $config['has_many']['articles'] = array(
            'class_name' => 'SBArticle',
            'assoc_func' => 'findValidByCategoryId',
            'assoc_foreign_key' => 'thema_id',
        );
        $config['has_many']['visible_articles'] = array(
            'class_name' => 'SBArticle',
            'assoc_func' => 'findVisibleByCategoryId',
            'assoc_foreign_key' => 'thema_id',
        );
        $config['has_many']['new_articles'] = array(
            'class_name' => 'SBArticle',
            'assoc_func' => 'findNewByCategoryId',
            'assoc_foreign_key' => 'thema_id',
        );
        $config['additional_fields']['new'] = array(
            'get' => function ($object) {
                return count($object->new_articles) > 0;
            }
        );

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
}
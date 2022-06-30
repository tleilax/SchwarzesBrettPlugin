<?php
namespace SchwarzesBrett;

use AccessDeniedException;
use DBManager;
use SimpleORMap;
use StudipLog;

/**
 * @property string $id
 * @property string $thema_id
 * @property \I18NString $titel
 * @property int $mkdate
 * @property \I18NString $beschreibung
 * @property string $perm_create
 * @property string $perm_access_min
 * @property string $perm_access_max
 * @property bool $visible
 * @property bool $publishable
 * @property \I18NString $terms
 * @property bool $display_terms_in_article
 * @property \I18NString $disclaimer
 *
 * @property-read int $new
 */
class Category extends SimpleORMap
{
    public static function configure($config = [])
    {
        $config['db_table'] = 'sb_themen';
        $config['has_many']['articles'] = [
            'class_name'        => Article::class,
            'assoc_func'        => 'findValidByCategoryId',
            'assoc_foreign_key' => 'thema_id',
            'on_delete' => 'delete',
        ];
        $config['has_many']['visible_articles'] = [
            'class_name'        => Article::class,
            'assoc_func'        => 'findVisibleByCategoryId',
            'assoc_foreign_key' => 'thema_id',
        ];
        $config['has_many']['new_articles'] = [
            'class_name'        => Article::class,
            'assoc_func'        => 'findNewByCategoryId',
            'assoc_foreign_key' => 'thema_id',
        ];
        $config['additional_fields']['new'] = [
            'get' => function (Category $category) {
                return count($category->new_articles) > 0;
            }
        ];

        $config['registered_callbacks']['after_create'][] = function (Category $category) {
            StudipLog::SB_CATEGORY_CREATED(null, null, (string) $category->titel);
        };
        $config['registered_callbacks']['after_delete'][] = function (Category $category) {
            StudipLog::SB_CATEGORY_DELETED(null, null, (string) $category->titel);
        };

        $config['registered_callbacks']['before_store'][] = 'checkUserRights';
        $config['registered_callbacks']['before_delete'][] = 'checkUserRights';

        $config['i18n_fields']['titel'] = true;
        $config['i18n_fields']['beschreibung'] = true;
        $config['i18n_fields']['terms'] = true;
        $config['i18n_fields']['disclaimer'] = true;

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

    public static function findVisible(string $order = 'ORDER BY titel ASC'): array
    {
        $categories = self::findBySQL("visible = 1 {$order}");
        $categories = array_filter($categories, function (Category $category) {
            return $category->isAccessibleByUser();
        });
        return $categories;
    }

    public function mayEdit($user_or_id = null)
    {
        return is_object($GLOBALS['perm'])
            && $GLOBALS['perm']->have_perm('root');
    }

    public function isAccessibleByUser(User $user = null): bool
    {
        $user = $user ?? User::findCurrent();

        if ($user->perms === 'root') {
            return true;
        }

        return (!$this->perm_access_min || $GLOBALS['perm']->have_perm($this->perm_access_min, $user->id))
            && (!$this->perm_access_max || !$GLOBALS['perm']->have_perm($this->perm_access_max, $user->id));
    }

    public function checkUserRights()
    {
        if (!$this->mayEdit()) {
            throw new AccessDeniedException('You may not alter this category');
        }
    }
}

<?php
namespace SchwarzesBrett;

use DBManager;
use User as GlobalUser;

class User extends GlobalUser
{
    public static function configure($config = [])
    {
        $config['has_many']['articles'] = [
            'class_name'        => Article::class,
            'assoc_func'        => 'findValidByUserId',
            'assoc_foreign_key' => 'user_id',
            'foreign_key'       => 'user_id',
            'on_delete'         => 'delete',
        ];

        $config['has_many']['visible_articles'] = [
            'class_name'        => Article::class,
            'assoc_func'        => 'findVisibleByUserId',
            'assoc_foreign_key' => 'user_id',
            'foreign_key'       => 'user_id',
            'on_delete'         => 'delete',
        ];

        $config['has_and_belongs_to_many']['watched_articles'] = [
            'class_name'     => Article::class,
            'thru_table'     => 'sb_watchlist',
            'thru_key'       => 'user_id',
            'thru_assoc_key' => 'artikel_id',
            'order_by'       => 'ORDER BY mkdate DESC',
        ];

        parent::configure($config);
    }

    public static function Get($id = null)
    {
        return new self($id ?? $GLOBALS['user']->id);
    }

    public function isBlackListed(bool $only_direct = false): bool
    {
        $query = "SELECT 1 FROM sb_blacklist WHERE user_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $this->id);
        $statement->execute();
        $blocked_directly =  (bool) $statement->fetchColumn();

        if ($only_direct) {
            return $blocked_directly;
        }

        return $blocked_directly
            || DomainBlacklist::isUserBlacklisted($this, DomainBlacklist::RESTRICTION_USAGE);
    }

    public function mayPostTo($category)
    {
        if (!$category) {
            return true;
        }

        if (is_string($category)) {
            $category = Category::find($category);
        }

        return $category->isAccessibleByUser($this)
            && $GLOBALS['perm']->have_perm($category->perm_create, $this->id);
    }
}

<?php
class SBUser extends User
{
    public static function configure($config = [])
    {
        $config['has_many']['articles'] = [
            'class_name'        => 'SBArticle',
            'assoc_func'        => 'findValidByUserId',
            'assoc_foreign_key' => 'user_id',
            'foreign_key'       => 'user_id',
            'on_delete'         => 'delete',
        ];

        $config['has_many']['visible_articles'] = [
            'class_name'        => 'SBArticle',
            'assoc_func'        => 'findVisibleByUserId',
            'assoc_foreign_key' => 'user_id',
            'foreign_key'       => 'user_id',
            'on_delete'         => 'delete',
        ];

        $config['has_and_belongs_to_many']['watched_articles'] = [
            'class_name'     => 'SBArticle',
            'thru_table'     => 'sb_watchlist',
            'thru_key'       => 'user_id',
            'thru_assoc_key' => 'artikel_id',
            'order_by'       => 'ORDER BY mkdate DESC',
        ];

        parent::configure($config);
    }

    public static function Get($id = null)
    {
        return new self($id ?: $GLOBALS['user']->id);
    }

    public function isBlackListed()
    {
        $query = "SELECT 1 FROM sb_blacklist WHERE user_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $this->id);
        $statement->execute();
        return (bool)$statement->fetchColumn();
    }
}
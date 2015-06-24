<?php
class SBUser extends User
{
    public static function configure($config = array())
    {
        $config['has_many']['articles'] = array(
            'class_name'        => 'SBArticle',
            'assoc_func'        => 'findValidByUserId',
            'assoc_foreign_key' => 'user_id',
            'foreign_key'       => 'user_id',
            'on_delete'         => 'delete',
        );

        $config['has_many']['visible_articles'] = array(
            'class_name'        => 'SBArticle',
            'assoc_func'        => 'findVisibleByUserId',
            'assoc_foreign_key' => 'user_id',
            'foreign_key'       => 'user_id',
            'on_delete'         => 'delete',
        );

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
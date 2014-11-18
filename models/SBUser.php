<?php
class SBUser extends User
{
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
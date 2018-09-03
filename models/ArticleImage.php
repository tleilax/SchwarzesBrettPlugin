<?php
namespace SchwarzesBrett;

use DBManager;
use PDO;
use SimpleORMap;

class ArticleImage extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'sb_artikel_images';

        $config['belongs_to']['article'] = [
            'class_name'  => 'SchwarzesBrett\\Article',
            'foreign_key' => 'artikel_id',
        ];
        $config['belongs_to']['image'] = [
            'class_name'        => 'FileRef',
            'assoc_foreign_key' => 'id',
        ];

        $config['additional_fields']['thumbnail'] = [
            'get' => function (ArticleImage $item) {
                return Thumbnail::create($item->image);
            },
        ];

        $config['registered_callbacks']['before_create'][] = function (ArticleImage $model) {
            if ($model->position) {
                return;
            }

            $query = "SELECT MAX(`position`)
                      FROM `sb_artikel_images`
                      WHERE `artikel_id` = :article_id";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':article_id', $model->artikel_id);
            $statement->execute();
            $position = 1 + $statement->fetchColumn();

            $model->position = $position;
        };

        parent::configure($config);
    }

    public static function gc($article_id = null)
    {
        if ($article_id !== null) {
            $position = 0;
            $images = static::findByArtikel_id($article_id, 'ORDER BY position ASC');

            foreach ($images as $image) {
                $image->position = $position++;
                $image->store();
            }

            return;
        }

        $query = "DELETE FROM `sb_artikel_images`
                  WHERE `image_id` NOT IN (
                      SELECT `id`
                      FROM `file_refs`
                  ) OR `artikel_id` NOT IN (
                      SELECT `artikel_id`
                      FROM `sb_artikel`
                  )";
        DBManager::get()->exec($query);

        $query = "SELECT DISTINCT `artikel_id`
                  FROM `sb_artikel_images`";
        $ids = DBManager::get()->query($query)->fetchAll(PDO::FETCH_COLUMN);

        foreach ($ids as $id) {
            static::gc($id);
        }
    }
}

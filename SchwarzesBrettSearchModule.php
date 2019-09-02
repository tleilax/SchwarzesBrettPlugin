<?php
class SchwarzesBrettSearchModule extends GlobalSearchModule
{
    public static function getName()
    {
        return _('Schwarzes Brett');
    }

    public static function getSQL($search, $filter, $limit)
    {
        if (!$search) {
            return '';
        }

        $needle = DBManager::get()->quote("%{$search}%");

        return "SELECT SQL_CALC_FOUND_ROWS
                    `artikel_id`, a.`titel`, a.`beschreibung`, a.`mkdate`, a.`user_id`,
                    t.`titel` AS thema
                FROM `sb_artikel` AS a
                JOIN `sb_themen` AS t USING (`thema_id`)
                WHERE `expires` > UNIX_TIMESTAMP()
                  AND (
                    a.`titel` LIKE {$needle}
                    OR a.`beschreibung` LIKE {$needle}
                  )";
    }

    public static function filter($data, $search)
    {
        return [
            'name'        => self::mark($data['titel'], $search) . " ({$data['thema']})",
            'url'         => URLHelper::getURL("plugins.php/schwarzesbrettplugin/article/view/{$data['artikel_id']}"),
            'img'         => Icon::create('billboard')->asImagePath(),
            'date'        => strftime('%x %R', $data['mkdate']),
            'description' => self::mark($data['beschreibung'], $search, true),
            'expand'      => self::getSearchURL($search),
            'additional'  => htmlReady(sprintf(_('Anzeige von %s'), User::find($data['user_id'])->getFullName())),
        ];
    }

    public static function getSearchURL($searchterm)
    {
        return URLHelper::getURL('dispatch.php/search/globalsearch', [
            'q'        => $searchterm,
            'category' => self::class,
        ]);
    }
}

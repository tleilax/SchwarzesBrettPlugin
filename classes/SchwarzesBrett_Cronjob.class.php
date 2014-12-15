<?php
class SchwarzesBrettCronjob extends CronJob
{
    public static function getName()
    {
        return _('Schwarzes Brett-Cronjob');
    }

    public static function getDescription()
    {
        return _('Cronjob für das Schwarze Brett, der abgelaufene Anzeigen entfernt.');
    }

    public function setUp()
    {
        require_once __DIR__ . '/../models/SBArticle.php';
        require_once __DIR__ . '/../models/SBVisit.php';
    }

    public function execute($last_result, $parameters = array())
    {
        $articles = SBArticle::findBySQL('expires < UNIX_TIMESTAMP()');

        foreach ($articles as $article) {
            $article->delete();
        }

        if (count($artikel) > 0) {
            printf('Removed %u items' . "\n", count($artikel));
        }
    }
}

/*
DELETE FROM sb_visits WHERE user_id NOT IN (SELECT user_id FROM auth_user_md5);
DELETE FROM sb_visits WHERE type = 'artikel' AND object_id NOT IN (SELECT artikel_id FROM sb_artikel);
DELETE FROM sb_visits WHERE type = 'thema' AND object_id NOT IN (SELECT thema_id FROM sb_themen);
*/
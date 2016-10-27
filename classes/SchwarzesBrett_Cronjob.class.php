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
        require_once __DIR__ . '/../models/SBWatchlist.php';
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

        // Do big garbage collection with a chance of 5%
        if (mt_rand() / PHP_INT_MAX >= 0.95) {
            SBVisit::gc();
        }
    }
}

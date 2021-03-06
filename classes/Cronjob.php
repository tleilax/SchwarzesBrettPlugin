<?php
namespace SchwarzesBrett;

use CronJob as GlobalCronjob;

class Cronjob extends GlobalCronjob
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
        require_once __DIR__ . '/../classes/SORMAllowAccessTrait.php';

        require_once __DIR__ . '/../models/Article.php';
        require_once __DIR__ . '/../models/ArticleImage.php';
        require_once __DIR__ . '/../models/Category.php';
        require_once __DIR__ . '/../models/Visit.php';
        require_once __DIR__ . '/../models/Watchlist.php';
    }

    public function execute($last_result, $parameters = [])
    {
        Article::allowAccess(true);

        $deleted = Article::deleteBySQL('expires < UNIX_TIMESTAMP()');

        Article::allowAccess(false);

        if ($deleted > 0) {
            printf('Removed %u items' . "\n", $deleted);
        }

        // Do big garbage collection with a chance of 5%
        if (mt_rand() / PHP_INT_MAX >= 0.95) {
            Visit::gc();
            Thumbnail::gc();
        }
    }
}

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
        require 'Artikel.class.php';
    }

    public function execute($last_result, $parameters = array())
    {
        $expiration_time = Config::get()->BULLETIN_BOARD_DURATION * 24 * 60 * 60;
        $artikel = Artikel::getExpired($expiration_time);

        foreach ($artikel as $id) {
            $a = new Artikel($id);
            $a->delete();
        }

        if (count($artikel) > 0) {
            printf('Removed %u items' . "\n", count($artikel));
        }
    }
}

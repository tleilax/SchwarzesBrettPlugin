<?php
class AddMoreLogging extends Migration
{
    public function up()
    {
        StudipLog::registerActionPlugin(
            'SB_CATEGORY_CREATED',
            'Schwarzes Brett: Kategorie erstellt',
            'Schwarzes Brett: %user hat eine neue Kategorie mit dem Titel "%title"',
            'SchwarzesBrettPlugin'
        );

        StudipLog::registerActionPlugin(
            'SB_CATEGORY_DELETED',
            'Schwarzes Brett: Kategorie gelöscht',
            'Schwarzes Brett: %user hat die Kategorie mit dem Titel "%title" gelöscht',
            'SchwarzesBrettPlugin'
        );

        StudipLog::registerActionPlugin(
            'SB_BLACKLISTED',
            'Schwarzes Brett: Nutzer gesperrt',
            'Schwarzes Brett: %user hat den Nutzer %user(%affected) gesperrt',
            'SchwarzesBrettPlugin'
        );
        StudipLog::registerActionPlugin(
            'SB_UNBLACKLISTED',
            'Schwarzes Brett: Nutzer entsperrt',
            'Schwarzes Brett:%user hat den Nutzer %user(%affected) entsperrt',
            'SchwarzesBrettPlugin'
        );
    }

    public function down()
    {
        StudipLog::unregisterAction('SB_CATEGORY_CREATED');
        StudipLog::unregisterAction('SB_CATEGORY_DELETED');
    }
}

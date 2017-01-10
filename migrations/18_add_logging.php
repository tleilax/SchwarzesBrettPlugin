<?php
class AddLogging extends Migration
{
    public function up()
    {
        StudipLog::registerActionPlugin(
            'SB_ARTICLE_CREATED',
            'Schwarzes Brett: Anzeige erstellt',
            '%user hat eine Anzeige mit dem Titel "%title" in der Kategorie %category(%affected) erstellt',
            'SchwarzesBrettPlugin'
        );

        StudipLog::registerActionPlugin(
            'SB_ARTICLE_DELETED',
            'Schwarzes Brett: Anzeige gelöscht',
            '%user hat die Anzeige %user(%coaffected) mit dem Titel "%title" in der Kategorie %category(%affected) gelöscht',
            'SchwarzesBrettPlugin'
        );
    }

    public function down()
    {
        StudipLog::unregisterAction('SB_ARTICLE_CREATED');
        StudipLog::unregisterAction('SB_ARTICLE_DELETED');
    }
}

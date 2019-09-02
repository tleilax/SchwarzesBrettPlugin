<?php
class Blame extends Migration
{
    public function description()
    {
        return 'config eintraege fuer blame funktion werden angelegt';
    }

    public function up()
    {

        Config::get()->create('BULLETIN_BOARD_BLAME_RECIPIENTS', [
            'value'       => $GLOBALS['UNI_CONTACT'],
            'type'        => 'string',
            'range'       => 'global',
            'section'     => 'SchwarzesBrettPlugin',
            'description' => 'Mailadressen, an die die Nachricht geschickt werden soll',
        ]);
        Config::get()->create('BULLETIN_BOARD_ENABLE_BLAME', [
            'value'       => (int) true,
            'type'        => 'boolean',
            'range'       => 'global',
            'section'     => 'SchwarzesBrettPlugin',
            'description' => 'Blame Funktion aktivieren (Nutzer können Anzeigen melden)',
        ]);
    }

    public function down()
    {
        Config::get()->delete('BULLETIN_BOARD_BLAME_RECIPIENTS');
        Config::get()->delete('BULLETIN_BOARD_ENABLE_BLAME');
    }
}

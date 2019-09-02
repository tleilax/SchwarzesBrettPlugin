<?php
class DisplayBadge extends Migration
{
    public function description()
    {
        return _('Config-Eintrag für die Anzeige der ungesehenen Anzeigen in der Navigation anlegen');
    }

    public function up()
    {
        Config::get()->create('BULLETIN_BOARD_DISPLAY_BADGE', [
            'value'       => (int) false,
            'type'        => 'boolean',
            'range'       => 'global',
            'section'     => 'SchwarzesBrettPlugin',
            'description' => 'Anzahl der ungelesen Anzeigen in der Navigation anzeigen',
        ]);
    }

    public function down()
    {
        Config::get()->delete('BULLETIN_BOARD_DISPLAY_BADGE');
    }
}

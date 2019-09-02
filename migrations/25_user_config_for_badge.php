<?php
class UserConfigForBadge extends Migration
{
    public function up()
    {
        Config::get()->create('BULLETIN_BOARD_SHOW_BADGE', [
            'value'       => (int) true,
            'type'        => 'boolean',
            'range'       => 'user',
            'section'     => 'SchwarzesBrettPlugin',
            'description' => 'Anzeige der Badge am Navigationspunkt des Schwarzen Bretts',
        ]);
    }

    public function down()
    {
        Config::get()->delete('BULLETIN_BOARD_SHOW_BADGE');
    }
}

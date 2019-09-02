<?php
class RssOptional extends Migration
{
    public function description()
    {
        return 'config eintraege fuer rss werden angelegt';
    }

    public function up()
    {
        Config::get()->create('BULLETIN_BOARD_ENABLE_RSS', [
            'value'       => (int) true,
            'type'        => 'boolean',
            'range'       => 'global',
            'section'     => 'SchwarzesBrettPlugin',
            'description' => 'RSS Feeds aktivieren',
        ]);
    }

    public function down()
    {
        Config::get()->delete('BULLETIN_BOARD_ENABLE_RSS');
    }
}

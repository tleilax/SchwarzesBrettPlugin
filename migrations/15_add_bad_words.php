<?php
class AddBadWords extends Migration
{
    public function description()
    {
        return 'Adds the config entry for bad words that will lead to an '
             . 'information email to the defined email in '
             . 'BULLETIN_BOARD_BLAME_RECIPIENTS';
    }

    public function up()
    {
        Config::get()->create('BULLETIN_BOARD_BAD_WORDS', [
            'value'       => '',
            'type'        => 'string',
            'range'       => 'global',
            'section'     => 'SchwarzesBrettPlugin',
            'description' => 'Liste von "verbotenen" Wörtern, bei denen der Support informiert wird, falls diese in einer Anzeige auftauchen (komma-separiert)',
        ]);
    }

    public function down()
    {
        Config::get()->delete('BULLETIN_BOARD_BAD_WORDS');
    }
}

<?php
class MediaProxyConfig extends Migration
{
    public function description()
    {
        return 'Config-Einträge für den Media-Proxy werden angelegt';
    }

    public function up()
    {
        Config::get()->create('BULLETIN_BOARD_MEDIA_PROXY', [
            'value'       => (int) false,
            'type'        => 'boolean',
            'range'       => 'global',
            'section'     => 'SchwarzesBrettPlugin',
            'description' => 'Eigenen Media-Proxy aktivieren (bei Problemen mit http-Inhalten in https-Umgebungen',
        ]);
        Config::get()->create('BULLETIN_BOARD_MEDIA_PROXY_CACHED', [
            'value'       => (int) false,
            'type'        => 'boolean',
            'range'       => 'global',
            'section'     => 'SchwarzesBrettPlugin',
            'description' => 'Media-Proxy-Inhalte cachen',
        ]);
    }

    public function down()
    {
        Config::get()->delete('BULLETIN_BOARD_MEDIA_PROXY');
        Config::get()->delete('BULLETIN_BOARD_MEDIA_PROXY_CACHED');
    }
}

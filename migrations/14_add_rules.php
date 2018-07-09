<?php
class AddRules extends Migration
{
    public function description()
    {
        return 'Adds the config entry for the now editable rules of the bulletin board.';
    }

    public function up()
    {

        Config::get()->create('BULLETIN_BOARD_RULES', [
            'value'       => $this->getRules(),
            'type'        => 'string',
            'range'       => 'global',
            'section'     => 'SchwarzesBrettPlugin',
            'description' => 'Die angezeigten Regeln des Schwarzen Bretts',
        ]);
    }

    public function down()
    {
        Config::get()->delete('BULLETIN_BOARD_RULES');
    }

    private function getRules()
    {
        $rules  = '!!!Allgemeine Hinweise:' . "\n";
        $rules .= '- Sie können nur in Themen eine Anzeige erstellen, in denen Sie die nötigen Rechte haben.' . "\n";
        $rules .= '- Mit der Suche werden sowohl Titel als auch Beschreibung aller Anzeigen durchsucht.' . "\n";
        $rules .= '- Sie können Ihre eigenen Anzeigen jederzeit nachträglich %%bearbeiten%% oder %%löschen%%.' . "\n";
        $rules .= '- Bitte stellen Sie Ihre Anzeigen in die richtige Kategorie ein. Damit das Schwarze Brett übersichtlich bleibt, %%löschen%% Sie bitte Ihre Anzeigen umgehend nach Abschluss/Verkauf.' . "\n";
        $rules .= '- **Bitte Artikel nur in %%eine%% Kategorie einstellen!**' . "\n";
        $rules .= '- **Kommerzielle Angebote sind __nicht__ erlaubt (dazu gehören auch solche Anzeigen die einen Link enthalten, der dem Einstellenden eine Provision verspricht). Sie werden gelöscht!**' . "\n";

        return $rules;
    }
}

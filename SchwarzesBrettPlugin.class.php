<?php
/**
 * SchwarzesBrettPlugin.class.php
 *
 * Plugin zum Verwalten von Schwarzen Brettern (Angebote und Gesuche)
 *
 * Diese Datei enthält die Hauptklasse des Plugins
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author      Jan Kulmann <jankul@zmml.uni-bremen.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @author      Daniel Kabel <daniel.kabel@me.com>
 * @package     IBIT_SchwarzesBrettPlugin
 * @copyright   2008-2014 IBIT und ZMML
 * @license     http://www.gnu.org/licenses/gpl.html GPL Licence 3
 * @version     2.5
 */

// IMPORTS
require_once 'bootstrap.inc.php';

/**
 * SchwarzesBrettPlugin Hauptklasse
 *
 */
class SchwarzesBrettPlugin extends StudIPPlugin implements SystemPlugin
{
    public $zeit, $announcements;
    private $template_factory, $layout, $layout_infobox;

    const THEMEN_CACHE_KEY = 'plugins/SchwarzesBrettPlugin/themen';
    const ARTIKEL_CACHE_KEY = 'plugins/SchwarzesBrettPlugin/artikel/';
    const ARTIKEL_PUBLISHABLE_CACHE_KEY = 'plugins/SchwarzesBrettPlugin/artikel/publishable';

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        StudipAutoloader::addAutoloadPath(__DIR__ . '/models');

        $this->template_factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates');

        // Holt die Laufzeit aus der Config. Default: 30Tage
        $this->zeit = (int)Config::get()->BULLETIN_BOARD_DURATION * 24 * 60 * 60;
        // Holt Anzahl anzuzeigende neuste Anzeigen. Default: 20
        $this->announcements = (int)Config::get()->BULLETIN_BOARD_ANNOUNCEMENTS;

        //menu nur anzeigen, wenn eingeloggt
        if ($GLOBALS['perm']->have_perm('user')) {
            $this->buildMenu();
        }
    }
    
    protected function buildMenu()
    {
        // Hauptmenüpunkt
        $nav = new Navigation(_('Schwarzes Brett'), PluginEngine::getURL($this, array(), 'category'));
        $nav->setImage('icons/28/lightblue/billboard.png', tooltip2(_('Schwarzes Brett')));
        Navigation::addItem('/schwarzesbrettplugin', $nav);

        // Untermenü
        $nav = new Navigation(_('Schwarzes Brett'), PluginEngine::getURL($this, array(), 'category'));
        Navigation::addItem('/schwarzesbrettplugin/show', $nav);
        
        $nav = new Navigation(_('Übersicht'), PluginEngine::getURL($this, array(), 'category'));
        Navigation::addItem('/schwarzesbrettplugin/show/all', $nav);
        if (Artikel::hasOwn($GLOBALS['user']->id, $this->zeit)) {
            $nav = new Navigation(_('Meine Anzeigen'), PluginEngine::getURL($this, array(), 'article/own'));
            Navigation::addItem('/schwarzesbrettplugin/show/own', $nav);
        }

        //zusatzpunkte für root
        if ($GLOBALS['perm']->have_perm('root')) {
            $nav = new Navigation(_('Administration'), PluginEngine::getURL($this, array(), 'admin/settings'));
            Navigation::addItem('/schwarzesbrettplugin/root', $nav);
            
            $nav = new Navigation(_('Grundeinstellungen'), PluginEngine::getURL($this, array(), 'admin/settings'));
            Navigation::addItem('/schwarzesbrettplugin/root/settings', $nav);

            $nav = new Navigation(_('Benutzer-Blacklist'), PluginEngine::getURL($this, array(), 'admin/blacklist'));
            Navigation::addItem('/schwarzesbrettplugin/root/blacklist', $nav);

            $nav = new Navigation(_('Doppelte Einträge suchen'), PluginEngine::getURL($this, array(), 'admin/duplicates'));
            Navigation::addItem('/schwarzesbrettplugin/root/duplicates', $nav);

            if (!$this->hasActiveCronjob() && $expired = SBArticle::countBySQL('expires < UNIX_TIMESTAMP()')) {
                $title = sprintf(_('Datenbank bereinigen') . ' (' . _('%u alte Einträge') . ')', $expired);
                $nav = new Navigation($title, PluginEngine::getURL($this, array(), 'article/purge'));
                Navigation::addItem('/schwarzesbrettplugin/root/gc', $nav);
            }
        }
    }

    public function initialize()
    {
        require_once 'controllers/sb_controller.php';
        
        PageLayout::setTitle(_('Schwarzes Brett'));
        $this->addStylesheet('assets/schwarzesbrett.less');
        PageLayout::addScript($this->getPluginURL() . '/assets/schwarzesbrett.js');
    }

    public function getPluginname()
    {
        return _('Schwarzes Brett');
    }

    /**
     * Führt die Suche nach Anzeigen durch und zeigt die Ergebnisse an.
     *
     * @param String $search_text Suchwort
     */
    private function search()
    {
        if (Request::get('search_user') && $GLOBALS['perm']->get_perm($GLOBALS['user']->id) == 'root') {
            //Datenbankabfrage
            $user = Request::get('search_user');
            $query = "SELECT a.thema_id, a.artikel_id, a.titel, t.titel AS t_titel "
                   . "FROM sb_artikel AS a, sb_themen AS t "
                   . "WHERE t.thema_id = a.thema_id AND a.user_id = ? "
                   . "ORDER BY t.titel, a.titel";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($user));
        } else {
            $search_text = Request::get('search_text');
            //Benutzereingaben abfangen (Wörter kürzer als 3 Zeichen)
            if ((empty($search_text) || strlen($search_text) < 3) && !Request::get('search_user'))
            {
                PageLayout::postMessage(MessageBox::error("Ihr Suchwort ist zu kurz, bitte versuchen Sie es erneut!"));
                $this->showThemen();
                return;
            }

            $query = "SELECT a.thema_id, a.artikel_id, a.titel, t.titel AS t_titel "
                   . "FROM sb_artikel AS a, sb_themen AS t "
                   . "WHERE t.thema_id = a.thema_id "
                   .   "AND (UPPER(a.titel) LIKE CONCAT('%', UPPER(?), '%') "
                   .     "OR UPPER(a.beschreibung) LIKE CONCAT('%', UPPER(?), '%')) "
                   .   "AND UNIX_TIMESTAMP() < a.mkdate + ? "
                   .   "AND (a.visible = 1 OR (a.user_id = ? OR 'root' = ?)) "
                   . "ORDER BY t.titel, a.titel";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array(
                $search_text, $search_text,
                $this->zeit, $GLOBALS['user']->id,
                $GLOBALS['perm']->get_perm($GLOBALS['user']->id),
            ));

        }

        $dbresults = $statement->fetchAll(PDO::FETCH_ASSOC);

        // keine Ergebnisse vorhanden
        if(count($dbresults) == 0) {
            PageLayout::postMessage(MessageBox::error("Es wurden für <em>" . htmlReady($search_text) . "</em> keine Ergebnisse gefunden."));
            $this->showThemen();
            return;
        }

        //Ergebnisse anzeigen
        $results = array();
        $thema = array();
        foreach ($dbresults as $result) {
            $a = new Artikel($result['artikel_id']);
            if(empty($thema['thema_id'])) {
                $thema['thema_id'] = $result['thema_id'];
                $thema['thema_titel'] = htmlReady($result['t_titel']);
                $thema['artikel'] = array();
            } elseif($result['thema_id'] != $thema['thema_id']) {
                $results[] = $thema;

                $thema = array();
                $thema['thema_id'] = $result['thema_id'];
                $thema['thema_titel'] = htmlReady($result['t_titel']);
                $thema['artikel'] = array();
            }
            $thema['artikel'][] = $this->showArtikel($a);
        }
        $results[] = $thema;

        //Ausgabe erzeugen
        $template = $this->template_factory->open('search_results');
        $template->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
        $template->zeit        = $this->zeit;
        $template->pluginpfad  = $this->getPluginURL();
        $template->link_search = PluginEngine::getURL($this, array("modus"=>"show_search_results"));
        $template->link_back   = PluginEngine::getURL($this, array(), 'show');
        $template->results     = $results;
        echo $template->render();
    }

    protected function hasActiveCronjob()
    {
        return Config::get()->CRONJOBS_ENABLE
            && ($tasks = CronjobTask::findByClass('SchwarzesBrettCronjob'))
            && $tasks[0]->active
            && $tasks[0]->schedules->findOneBy('active', '1');
    }

    
    public function perform($unconsumed_path)
    {
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null), '/'),
            'category'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }
}

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
        if (count(SBUser::Get()->articles) > 0) {
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

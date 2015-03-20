<?php
/**
 * Schwarzes Brett
 *
 * Plugin zum Verwalten von Schwarzen Brettern (Angebote und Gesuche)
 *
 * Diese PluginVersion ist mehr oder eniger ein kompletter Rewrite des alten
 * Plugins. Daher neue Lizent und nur ein Autor. Aber:
 *
 * Give credit where credit is due. Das eigentliche Plugin stammt von diesen
 * Autoren:
 *
 * - Jan Kulmann <jankul@zmml.uni-bremen.de>
 * - Daniel Kabel <daniel.kabel@me.com>
 * - Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @copyright   2014 UOL
 * @license     GPL2 or any later version
 * @version     3.0
 */

require_once 'bootstrap.inc.php';

/**
 * SchwarzesBrettPlugin Hauptklasse
 */
class SchwarzesBrettPlugin extends StudIPPlugin implements SystemPlugin
{
    public function __construct()
    {
        parent::__construct();

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
        if (Config::get()->BULLETIN_BOARD_DISPLAY_BADGE) {
            $nav->setBadgeNumber(SBArticle::countNew());
        }
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
        URLHelper::removeLinkParam('cid');

        if (Config::get()->BULLETIN_BOARD_MEDIA_PROXY) {
            SBOpenGraphURL::setProxyURL(PluginEngine::getURL($this, array(), 'proxy'));
        }

        if ($unconsumed_path === 'show/all') {
            $unconsumed_path = 'category/list';
        }

        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array('cid' => null), null), '/'),
            'category'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

    public function url_for($to)
    {
        $args = func_get_args();
        $last = end($args);

        if (is_array($last)) {
            $params = array_pop($args);
        } else {
            $params = array();
        }

        return PluginEngine::getURL($this, $params, join('/', $args));
    }

    public static function onEnable($plugin_id)
    {
        $manager = PluginManager::getInstance();

        $plugin = $manager->getPlugin('RestipPlugin');
        if (!$plugin) {
            return;
        }

        $info = $manager->getPluginInfo('SchwarzesBrettAPI');
        if ($info !== null) {
            return;
        }

        $info = $manager->getPluginInfo('SchwarzesBrettPlugin');
        $manager->registerPlugin('SchwarzesBrettAPI', 'SchwarzesBrettAPI', $info['path'], $plugin_id);
    }
    
    public static function onDisable($plugin_id)
    {
        $manager = PluginManager::getInstance();

        $info = $manager->getPluginInfo('SchwarzesBrettAPI');
        if ($info === null || !$info['enabled']) {
            return;
        }

        $manager->setPluginEnabled($info['id'], false);
    }
}

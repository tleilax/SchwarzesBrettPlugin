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
class SchwarzesBrettPlugin extends StudIPPlugin implements SystemPlugin, HomepagePlugin, Loggable
{
    public function __construct()
    {
        parent::__construct();

        //menu nur anzeigen, wenn eingeloggt
        if ($GLOBALS['perm']->have_perm('user')) {
            $this->buildMenu();

            NotificationCenter::addObserver($this, 'onDelete', 'UserWillDelete');
        }
    }

    protected function buildMenu()
    {
        // Hauptmenüpunkt
        $nav = new Navigation(_('Schwarzes Brett'), $this->url_for('category'));
        $nav->setImage(Icon::create('billboard', 'navigation', tooltip2(_('Schwarzes Brett'))));
        if (Config::get()->BULLETIN_BOARD_DISPLAY_BADGE) {
            $nav->setBadgeNumber(SBArticle::countNew());
        }
        Navigation::addItem('/schwarzesbrettplugin', $nav);

        // Untermenü
        $nav = new Navigation(_('Schwarzes Brett'), $this->url_for('category'));
        Navigation::addItem('/schwarzesbrettplugin/show', $nav);

        $nav = new Navigation(_('Übersicht'), $this->url_for('category'));
        Navigation::addItem('/schwarzesbrettplugin/show/all', $nav);

        $nav = new Navigation(_('Merkliste'), $this->url_for('watchlist'));
        Navigation::addItem('/schwarzesbrettplugin/show/watchlist', $nav);

        $title = _('Meine Anzeigen');
        $count = count(SBUser::Get()->articles);
        if ($count > 0) {
            $title .= sprintf(' (%u)', $count);
        }
        $nav = new Navigation($title, $this->url_for('article/own'));
        Navigation::addItem('/schwarzesbrettplugin/show/own', $nav);

        //zusatzpunkte für root
        if ($GLOBALS['perm']->have_perm('root')) {
            $nav = new Navigation(_('Administration'), $this->url_for('admin/settings'));
            Navigation::addItem('/schwarzesbrettplugin/root', $nav);

            $nav = new Navigation(_('Grundeinstellungen'), $this->url_for('admin/settings'));
            Navigation::addItem('/schwarzesbrettplugin/root/settings', $nav);

            $nav = new Navigation(_('Benutzer-Blacklist'), $this->url_for('admin/blacklist'));
            Navigation::addItem('/schwarzesbrettplugin/root/blacklist', $nav);

            $nav = new Navigation(_('Doppelte Einträge suchen'), $this->url_for('admin/duplicates'));
            Navigation::addItem('/schwarzesbrettplugin/root/duplicates', $nav);

            if (!$this->hasActiveCronjob() && $expired = SBArticle::countBySQL('expires < UNIX_TIMESTAMP()')) {
                $title = sprintf(_('Datenbank bereinigen') . ' (' . _('%u alte Einträge') . ')', $expired);
                $nav = new Navigation($title, $this->url_for('article/purge'));
                Navigation::addItem('/schwarzesbrettplugin/root/gc', $nav);
            }
        }
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
        require_once 'controllers/sb_controller.php';

        PageLayout::setTitle(_('Schwarzes Brett'));

        $this->addStylesheet('assets/schwarzesbrett.less');
        PageLayout::addScript($this->getPluginURL() . '/assets/schwarzesbrett.js');

        if (Config::get()->BULLETIN_BOARD_MEDIA_PROXY) {
            SBOpenGraphURL::setProxyURL(PluginEngine::getURL($this, [], 'proxy', true));
        }

        if ($unconsumed_path === 'show/all') {
            $unconsumed_path = 'category/list';
        }

        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array('cid' => null), null, true), '/'),
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
            $params = [];
        }

        return PluginEngine::getURL($this, $params, join('/', $args));
    }

    public function onDelete($user)
    {
        SBArticle::deleteBySQL("user_id = ?", [$user->id]);
        SBBlacklist::deleteBySQL("user_id = ?", [$user->id]);
        SBVisit::deleteBySQL("user_id = ?", [$user->id]);
        SBWatchlist::deleteBySQL("user_id = ?", [$user->id]);
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

    public function getHomepageTemplate($user_id)
    {
        if (!PluginManager::getInstance()->isPluginActivatedForUser($this->getPluginId(), $user_id)) {
            return null;
        }

        $this->addStylesheet('assets/schwarzesbrett.less');

        $own_profile = $user_id === $GLOBALS['user']->id;

        $user  = SBUser::find($user_id);

        $title = $own_profile
               ? _('Meine aktuellen Anzeigen im Schwarzen Brett')
               : sprintf(_('Aktuelle Anzeigen von %s im Schwarzen Brett'), $user->getFullname());

        $factory  = new Flexi_TemplateFactory(__DIR__ . '/views/');
        $template = $factory->open('homepage/plugin.php');
        $template->title       = $title;
        $template->icon_url    = Icon::create('billboard', 'info');
        $template->categories  = SBArticle::groupByCategory($own_profile ? $user->articles : $user->visible_articles);
        $template->controller  = $this;
        return count($template->categories) ? $template : null;
    }

    public static function logFormat(LogEvent $event)
    {
        $replaces = [
            '%title'               => $event->info,
            '%category(%affected)' => _('Unbekannt'),
            '%user(%coaffected)'   => '',
        ];

        if ($category = SBCategory::find($event->affected_range_id)) {
            $replaces['%category(%affected)'] = sprintf(
                '<a href="%s">%s</a>',
                URLHelper::getLink('plugins.php/schwarzesbrettplugin/category/view/' . $category->id),
                htmlReady($category->titel)
            );
        }

        if ($event->coaffected_range_id && $event->coaffected_range_id !== $GLOBALS['user']->id) {
            $user = User::find($event->coaffected_range_id);
            $replaces['%user(%coaffected)'] = sprintf(
                ' von <a href="%s">%s</a>',
                URLHelper::getLink('dispatch.php/profile?username=' . $user->username),
                htmlReady($user->getFullName())
            );
        }

        return str_replace(
            array_keys($replaces),
            array_values($replaces),
            $event->action->info_template
        );
    }

    public static function logSearch($needle, $action_name = null)
    {
        return [];
    }
}

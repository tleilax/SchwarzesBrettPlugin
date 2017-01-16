<?php
namespace SchwarzesBrett;

use AccessDeniedException;
use ActionsWidget;
use Config;
use ExportWidget;
use NotificationCenter;
use PageLayout;
use Request;
use SearchWidget;
use SelectElement;
use SelectWidget;
use Sidebar;
use StudipController;
use Trails_Flash;
use URLHelper;

require_once 'app/controllers/studip_controller.php'; 

class Controller extends StudipController
{
    protected $allow_nobody = false;
    protected $utf8decode_xhr = true;
    protected $temp_storage;

    public function before_filter(&$action, &$args)
    {
        $this->flash = Trails_Flash::instance();
        
        parent::before_filter($action, $args);

        if (!$this->allow_nobody) {
            $GLOBALS['perm']->check('user');
        }

        if (isset($this->flash['send_headers'])) {
            $headers = $this->flash['send_headers'];
            $this->response->add_header($headers[0], $headers[1]);
        }

        // Setup mandatory variables
        $config = Config::get();
        $this->rss_enabled   = (bool)($config->BULLETIN_BOARD_ENABLE_RSS ?: false);
        $this->blame_enabled = (bool)($config->BULLETIN_BOARD_ENABLE_BLAME ?: false);
        $this->expire_days   = (int)($config->BULLETIN_BOARD_DURATION ?: 30);
        $this->expire_time   = $this->expire_days * 24 * 60 * 60;
        $this->newest_limit  = (int)($config->BULLETIN_BOARD_ANNOUNCEMENTS ?: 20);

        $this->is_admin = is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('root');

        // Setup sidebar
        $this->setup_sidebar(get_class($this), $action, $args);
    }

    public function after_filter($action, $args)
    {
        if (Request::isXhr() && $title = PageLayout::getTitle()) {
            $this->response->add_header('X-Title', $title);
        }

        parent::after_filter($action, $args);
    }

    public function redirect($to)
    {
        if (Request::isXhr()) {
            $messages = PageLayout::getMessages();
            if (!empty($messages)) {
                $this->response->add_header('X-Messages', json_encode(join('', $messages)));
            }
        }

        return parent::redirect($to);
    }

    public function absolute_url_for($to)
    {
        $old_base = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $url = call_user_func_array('parent::url_for', func_get_args());
        URLHelper::setBaseURL($old_base);

        return $url;
    }

    protected function inject_rss($category_id = null)
    {
        if (!$this->rss_enabled) {
            return;
        }

        if ($category_id === null) {
            PageLayout::addHeadElement('link', array(
                'rel'   => 'alternate',
                'type'  => 'application/rss+xml',
                'title' => _('RSS-Feed'),
                'href'  => $this->absolute_url_for('rss'),
            ));
        } else {
            $category = Category::find($category_id);
            PageLayout::addHeadElement('link', array(
                'rel'   => 'alternate',
                'type'  => 'application/rss+xml',
                'title' => _('RSS-Feed') . ': ' . $category->titel,
                'href'  => $this->absolute_url_for('rss/' . $category->id),
            ));
        }
    }

    private function setup_sidebar($class, $action, $args)
    {
        $category_id = ($class === 'CategoryController' && $action === 'view' && !empty($args))
                     ? reset($args)
                     : false;

        $search = new SearchWidget($this->url_for('search'));
        $search->addNeedle(_('Suchbegriff'), 'needle', true);
        if ($category_id) {
            $search->addFilter(_('Nur in dieser Kategorie'), 'restrict[' . $category_id . ']');
        }
        Sidebar::get()->addWidget($search);

        $actions = new ActionsWidget();
        if ($category_id && count(Category::find($category_id)->new_articles) > 0) {
            $actions->addLink(_('Dieses Thema als besucht markieren'),
                              $this->url_for('category/visit/' . $category_id),
                              'icons/blue/check-circle.svg')
                    ->asDialog();
        }
        if (!$category_id /* TODO || $newArticles*/) {
            $actions->addLink(_('Alle Themen als besucht markieren'),
                              $this->url_for('category/visit'),
                              'icons/blue/accept.svg')
                    ->asDialog();
        }
        if (!User::get()->isBlacklisted()) {
            //wenn auf der blacklist, darf man keine artikel mehr erstellen
            $actions->addLink(_('Neue Anzeige erstellen'),
                              $this->url_for('article/create/' . ($category_id ?: '')),
                              'icons/blue/add/billboard.svg')->asDialog();
        }
        if ($this->is_admin) {
            $actions->addLink(_('Neues Thema anlegen'),
                              $this->url_for('category/create'),
                              'icons/blue/add/folder-empty.svg')->asDialog();
        }
        if ($category_id && $this->is_admin) {
            $actions->addLink(_('Dieses Thema bearbeiten'),
                              $this->url_for('category/edit/' . $category_id),
                              'icons/blue/edit.svg')->asDialog();
            $actions->addLink(_('Dieses Thema l�schen'),
                              $this->url_for('category/delete/' . $category_id),
                              'icons/blue/trash.svg',
                              array('data-confirm' => _('Wollen Sie dieses Thema wirklich inklusive aller darin enthaltener Anzeigen l�schen?')));
        }

        Sidebar::get()->addWidget($actions);

        if ($this->rss_enabled) {
            $export = new ExportWidget();
            $export->addLink($category_id ? _('RSS-Feed dieser Kategorie') : _('RSS-Feed'),
                             $this->url_for('rss/' . $category_id),
                             'icons/blue/rss.svg');
            Sidebar::get()->addWidget($export);
        }

        if ($category_id) {
            $this->temp_storage = $category_id;
            NotificationCenter::addObserver($this, 'addCategorySelector', 'SidebarWillRender');
        }
    }

    public function addCategorySelector()
    {
        $selected = $this->temp_storage;

        $widget = new SelectWidget(_('Kategorie'), $this->url_for('category/choose'), 'id');
        $widget->setSelection($selected);

        $categories = Category::findByVisible(1, 'ORDER BY titel COLLATE latin1_german1_ci ASC');
        foreach ($categories as $category) {
            $title = $category->titel;
            if ($count = count($category->visible_articles)) {
                $title .= ' (' . $count . ')';
            }
            $widget->addElement(new SelectElement($category->id, $title, $category->id == $selected));
        }

        Sidebar::get()->insertWidget($widget, ':first');
    }

    protected function checkTicket($force_ticket = true)
    {
        $ticket = Request::get('studip_ticket');
        if (!$ticket && !$force_ticket) {
            return true;
        }

        if (!$ticket || !check_ticket($ticket)) {
            throw new AccessDeniedException('Invalid ticket');
        }

        return true;
    }
}
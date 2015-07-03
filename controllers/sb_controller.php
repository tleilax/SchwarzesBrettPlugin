<?php
class SchwarzesBrettController extends StudipController
{
    protected $allow_nobody = false;
    protected $temp_storage;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!$this->allow_nobody) {
            $GLOBALS['perm']->check('user');
        }

        if (Request::isXhr()) {
            $this->response->add_header('Content-Type', 'text/html;charset=windows-1252');
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
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
            $this->response->add_header('X-Dialog-Close', 1);

            $messages = PageLayout::getMessages();
            if (!empty($messages)) {
                $this->response->add_header('X-Messages', json_encode(join('', $messages)));
            }

            if (!$this->performed) {
                $this->render_nothing();
            }
            return;
        }

        return parent::redirect($to);
    }

    public function absolute_url_for($to)
    {
        $url = call_user_func_array('parent::url_for', func_get_args());
        return ($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'] !== '/')
            ? str_replace($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'], '', $GLOBALS['ABSOLUTE_URI_STUDIP']) . $url
            : $GLOBALS['ABSOLUTE_URI_STUDIP'] . $url;
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
            $category = SBCategory::find($category_id);
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
        if ($category_id && count(SBCategory::find($category_id)->new_articles) > 0) {
            $actions->addLink(_('Dieses Thema als besucht markieren'),
                              $this->url_for('category/visit/' . $category_id),
                              'icons/16/blue/check-circle.png')
                    ->asDialog();
        }
        if (!$category_id /* TODO || $newArticles*/) {
            $actions->addLink(_('Alle Themen als besucht markieren'),
                              $this->url_for('category/visit'),
                              'icons/16/blue/accept.png')
                    ->asDialog();
        }
        if (!SBUser::get()->isBlacklisted()) {
            //wenn auf der blacklist, darf man keine artikel mehr erstellen
            $actions->addLink(_('Neue Anzeige erstellen'),
                              $this->url_for('article/create/' . ($category_id ?: '')),
                              $this->dispatcher->plugin->getPluginURL() . '/assets/billboard-add-blue-16.png')->asDialog();
        }
        if ($this->is_admin) {
            $actions->addLink(_('Neues Thema anlegen'),
                              $this->url_for('category/create'),
                              'icons/16/blue/add/folder-empty.png')->asDialog();
        }
        if ($category_id && $this->is_admin) {
            $actions->addLink(_('Dieses Thema bearbeiten'),
                              $this->url_for('category/edit/' . $category_id),
                              'icons/16/blue/edit.png')->asDialog();
            $actions->addLink(_('Dieses Thema löschen'),
                              $this->url_for('category/delete/' . $category_id),
                              'icons/16/blue/trash.png',
                              array('data-confirm' => _('Wollen Sie dieses Thema wirklich inklusive aller darin enthaltener Anzeigen löschen?')));
        }

        Sidebar::get()->addWidget($actions);

        if ($this->rss_enabled) {
            $export = new ExportWidget();
            $export->addLink($category_id ? _('RSS-Feed dieser Kategorie') : _('RSS-Feed'),
                             $this->url_for('rss/' . $category_id),
                             'icons/16/blue/rss.png');
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

        $categories = SBCategory::findByVisible(1, 'ORDER BY titel COLLATE latin1_german1_ci ASC');
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
<?php
namespace SchwarzesBrett;

use AccessDeniedException;
use ActionsWidget;
use Config;
use ExportWidget;
use FileManager;
use Folder;
use FolderType;
use Icon;
use NotificationCenter;
use OptionsWidget;
use PageLayout;
use Request;
use RuntimeException;
use SearchWidget;
use SelectElement;
use SelectWidget;
use Sidebar;
use Trails_Flash;
use URLHelper;

class Controller extends \PluginController
{
    protected $allow_nobody = false;
    protected $temp_storage;

    /**
     * Constructs the controller and provide translations methods.
     *
     * @param object $dispatcher
     * @see https://stackoverflow.com/a/12583603/982902 if you need to overwrite
     *      the constructor of the controller
     */
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
    }

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
            PageLayout::addHeadElement('link', [
                'rel'   => 'alternate',
                'type'  => 'application/rss+xml',
                'title' => $this->_('RSS-Feed'),
                'href'  => $this->absolute_url_for('rss'),
            ]);
        } else {
            $category = Category::find($category_id);
            PageLayout::addHeadElement('link', [
                'rel'   => 'alternate',
                'type'  => 'application/rss+xml',
                'title' => "{$this->_('RSS-Feed')}: {$category->titel}",
                'href'  => $this->absolute_url_for("rss/{$category->id}"),
            ]);
        }
    }

    private function setup_sidebar($class, $action, $args)
    {
        $category_id = ($class === 'CategoryController' && $action === 'view' && !empty($args))
                     ? reset($args)
                     : false;
        if (is_object($category_id)) {
            $category_id = $category_id->id;
        } elseif ($category_id) {
            PageLayout::postInfo('object id');
        }

        $search = new SearchWidget($this->url_for('search'));
        $search->addNeedle($this->_('Suchbegriff'), 'needle', true);
        if ($category_id) {
            $search->addFilter($this->_('Nur in dieser Kategorie'), "restrict[{$category_id}]");
        }
        Sidebar::get()->addWidget($search);

        $actions = new ActionsWidget();
        if ($category_id && count(Category::find($category_id)->new_articles) > 0) {
            $actions->addLink(
                $this->_('Dieses Thema als besucht markieren'),
                $this->url_for("category/visit/{$category_id}"),
                Icon::create('check-circle.svg')
            )->asDialog();
        }
        if (!$category_id /* TODO || $newArticles*/) {
            $actions->addLink(
                $this->_('Alle Themen als besucht markieren'),
                $this->url_for('category/visit'),
                Icon::create('accept')
            )->asDialog();
        }
        if (!User::get()->isBlacklisted() && User::get()->mayPostTo($category_id)) {
            //wenn auf der blacklist, darf man keine artikel mehr erstellen
            $actions->addLink(
                $this->_('Neue Anzeige erstellen'),
                $this->url_for('article/create/' . ($category_id ?: '')),
                Icon::create('billboard+add')
            )->asDialog();
        }
        if ($this->is_admin) {
            $actions->addLink(
                $this->_('Neues Thema anlegen'),
                $this->url_for('category/create'),
                Icon::create('folder-empty+add')
            )->asDialog();
        }
        if ($category_id && $this->is_admin) {
            $actions->addLink(
                $this->_('Dieses Thema bearbeiten'),
                $this->url_for("category/edit/{$category_id}"),
                Icon::create('edit')
            )->asDialog();
            $actions->addLink(
                $this->_('Dieses Thema löschen'),
                $this->url_for("category/delete/{$category_id}"),
                Icon::create('trash'),
                ['data-confirm' => $this->_('Wollen Sie dieses Thema wirklich inklusive aller darin enthaltener Anzeigen löschen?')]
            );
        }

        Sidebar::get()->addWidget($actions);

        if ($this->rss_enabled) {
            $export = new ExportWidget();
            $export->addLink(
                $category_id ? $this->_('RSS-Feed dieser Kategorie') : $this->_('RSS-Feed'),
                $this->url_for("rss/{$category_id}"),
                Icon::create('rss')
            );
            Sidebar::get()->addWidget($export);
        }

        if ($category_id) {
            $this->temp_storage = $category_id;
            NotificationCenter::addObserver($this, 'addCategorySelector', 'SidebarWillRender');
        } elseif (Config::get()->BULLETIN_BOARD_DISPLAY_BADGE) {
            $options = new OptionsWidget();
            $options->addCheckbox(
                $this->_('Anzahl der neuen Einträge in der Navigation einblenden'),
                $GLOBALS['user']->cfg->BULLETIN_BOARD_SHOW_BADGE,
                $this->url_for('config/toggle_badge')
            );
            Sidebar::get()->addWidget($options);
        }
    }

    public function addCategorySelector()
    {
        $selected = $this->temp_storage;

        $widget = new SelectWidget($this->_('Kategorie'), $this->url_for('category/choose'), 'id');
        $widget->setSelection($selected);

        $categories = Category::findByVisible(1, 'ORDER BY titel ASC');
        foreach ($categories as $category) {
            $title = $category->titel;
            if ($count = count($category->visible_articles)) {
                $title .= " ({$count})";
            }
            $widget->addElement(new SelectElement($category->id, $title, $category->id == $selected));
        }

        Sidebar::get()->insertWidget($widget, ':first');
    }

    protected function checkTicket($force_ticket = true)
    {
        if (\Studip\ENV === 'development') {
            return true;
        }

        $ticket = Request::get('studip_ticket');
        if (!$ticket && !$force_ticket) {
            return true;
        }

        if (!$ticket || !check_ticket($ticket)) {
            throw new AccessDeniedException('Invalid ticket');
        }

        return true;
    }

    protected function getFolder()
    {
        static $folder = null;

        if ($folder === null) {
            $root = Folder::findTopFolder($GLOBALS['user']->id)->getTypedFolder();

            $condition = implode(' AND ', [
                "parent_id = :parent_id",
                "folder_type = 'PublicFolder'",
                "data_content = :content",
            ]);
            $f = Folder::findOneBySQL($condition, [
                ':parent_id' => $root->id,
                ':content'   => json_encode(['SchwarzesBrett']),
            ]);

            if ($f) {
                $folder = $f->getTypedFolder();
            } else {
                $folder = FileManager::createSubFolder(
                    $root,
                    $GLOBALS['user']->getAuthenticatedUser(),
                    'PublicFolder',
                    _('Schwarzes Brett'),
                    _('Bilder zu Ihren Anzeigen im Schwarzen Brett')
                );

                if (!$folder instanceof FolderType) {
                    throw new Exception($folder[0]);
                }

                $folder->data_content = ['SchwarzesBrett'];
                $folder->store();
            }
        }
       return $folder;
    }

    protected function addHeader($key, $value, $replace = true)
    {
        if ($replace) {
            header_remove($key);
        }
        $this->response->add_header($key, $value);
    }

    public function get_template_factory()
    {
        $factory = parent::get_template_factory();
        $factory->add_handler('twig', TwigTemplate::class);
        return $factory;
    }
}

<?php
use SchwarzesBrett\Article;
use SchwarzesBrett\Category;
use SchwarzesBrett\Plugin;

class SchwarzesBrettWidget extends Plugin implements PortalPlugin
{
    public function getPluginName()
    {
        return $this->_('Schwarzes Brett');
    }

    public function getPortalTemplate()
    {
        $this->addStylesheet('assets/schwarzesbrett.less');
        $this->addScript('assets/schwarzesbrett.js');

        if (Config::get()->BULLETIN_BOARD_ALLOW_FILE_UPLOADS) {
            $this->addScript('assets/sb-upload.js');

            $this->addStylesheet('assets/lazy-load.scss');
            $this->addScript('assets/lazy-load.js');
        }

        $widget = $GLOBALS['template_factory']->open('shared/string');
        $widget->content = $this->getContent();
        $widget->icons   = $this->getNavigation();
        $widget->title   = $this->getPluginName();
        return $widget;
    }

    public function settings_action()
    {
        PageLayout::setTitle($this->_('Einstellung für das Schwarze Brett Widget'));

        if (Request::isPost()) {
            $selection = Request::getArray('categories');
            $count     = Request::int('count');
            $this->storeConfig($selection, $count);

            if (Request::isXhr()) {
                header('X-Location: ' . URLHelper::getLink('dispatch.php/start'));
            } else {
                header('Location: ' . URLHelper::getLink('dispatch.php/start'));
            }
            return;
        }

        if (true || Request::isXhr()) {
            header('X-Title: ' . rawurlencode(PageLayout::getTitle()));
            header('Content-Type: text/html;charset=utf-8');
        }

        $template = $this->getTemplate('widget/settings.php', true);
        $template->url        = PluginEngine::getLink($this, [], 'settings');
        $template->categories = Category::findBySQL('1 ORDER BY titel ASC');
        $template->config   = $this->getConfig();
        echo $template->render();
    }

    protected function getNavigation()
    {
        $navigation = [];

        $nav = new Navigation('', PluginEngine::getLink($this, [], 'settings'));
        $nav->setImage(Icon::create('admin'), tooltip2($this->_('Einstellungen')));
        $nav->setLinkAttributes(['data-dialog' => '']);
        $navigation[] = $nav;

        return $navigation;
    }

    protected function getContent()
    {
        $config = $this->getConfig();

        $template = $this->getTemplate('widget/index.php');
        $template->articles = Article::findNewest($config['count'], $config['selected']);
        return $template->render();
    }

    protected function getTemplate($template, $layout = false)
    {
        $factory  = new Flexi_TemplateFactory(__DIR__ . '/views');
        $template = $factory->open($template);
        $template->_ = function ($string) { return $this->_($string); };
        $template->controller = PluginEngine::getPlugin('SchwarzesBrettPlugin');
        if ($layout && !Request::isXhr()) {
            $template->set_layout($GLOBALS['template_factory']->open('layouts/base.php'));
        }
        return $template;
    }

    protected function getConfig()
    {
        return $GLOBALS['user']->cfg->SCHWARZESBRETT_WIDGET_SETTINGS
            ?: ['selected' => false, 'count' => Config::get()->ENTRIES_PER_PAGE];
    }

    protected function storeConfig($selected, $count)
    {
        $GLOBALS['user']->cfg->store('SCHWARZESBRETT_WIDGET_SETTINGS', compact(['selected', 'count']));
    }
}

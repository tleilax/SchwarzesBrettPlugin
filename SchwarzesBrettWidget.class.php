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
        PageLayout::addScript($this->getPluginURL() . '/assets/schwarzesbrett.js');

        $this->legacyAssets();

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

        if (Request::isXhr()) {
            header('X-Title: ' . PageLayout::getTitle());
        }

        $template = $this->getTemplate('widget/settings.php', true);
        $template->url        = PluginEngine::getLink($this, array(), 'settings');
        $template->categories = Category::findBySQL('1 ORDER BY titel COLLATE latin1_german1_ci ASC');
        $template->config   = $this->getConfig();
        echo $template->render();
    }

    protected function getNavigation()
    {
        $navigation = array();

        $nav = new Navigation('', PluginEngine::getLink($this, [], 'settings'));
        $nav->setImage(Icon::create('admin', 'clickable', tooltip2($this->_('Einstellungen'))));
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
        if (Request::isXhr()) {
            header('Content-Type: text/html;charset=windows-1252');
        }

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
